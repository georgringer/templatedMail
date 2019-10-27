<?php
declare(strict_types=1);

namespace GeorgRinger\Templatedmail\Tests\Classes;

use PHPUnit\Framework\Assert as PHPUnit;
use SimonSchaufi\TYPO3Support\Collection;
use Symfony\Component\Mailer\DelayedSmtpEnvelope;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\SmtpEnvelope;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\NamedAddress;
use Symfony\Component\Mime\RawMessage;
use TYPO3\CMS\Core\Mail\Mailer;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\MailUtility;

/**
 * Class FakeMailer
 *
 */
class FakeMailer extends Mailer implements SingletonInterface
{
    /**
     * All of the mails that have been sent.
     *
     * @var array
     */
    protected $mails = [];

    /** @noinspection PhpMissingParentConstructorInspection */

    /**
     * @param \Symfony\Component\Mailer\Transport\TransportInterface|null $transport
     */
    public function __construct(TransportInterface $transport = null)
    {
    }

    /**
     * Assert if a mail was sent based on a truth-test callback.
     *
     * @param  string $mail
     * @param  callable|int|null $callback
     */
    public function assertSent(string $mail, $callback = null): void
    {
        if (is_numeric($callback)) {
            $this->assertSentTimes($mail, $callback);
            return;
        }

        $message = "The expected [{$mail}] mail was not sent.";

        PHPUnit::assertTrue(
            $this->sent($mail, $callback)->count() > 0,
            $message
        );
    }

    /**
     * Assert if a mailable was sent a number of times.
     *
     * @param  string  $mail
     * @param  int  $times
     * @return void
     */
    protected function assertSentTimes($mail, $times = 1): void
    {
        PHPUnit::assertSame(
            $times,
            ($count = $this->sent($mail)->count()),
            "The expected [{$mail}] mail was sent {$count} times instead of {$times} times."
        );
    }

    /**
     * Determine if a mail was not sent based on a truth-test callback.
     *
     * @param  string  $mail
     * @param  callable|null  $callback
     * @return void
     */
    public function assertNotSent($mail, $callback = null): void
    {
        PHPUnit::assertSame(
            0,
            $this->sent($mail, $callback)->count(),
            "The unexpected [{$mail}] mail was sent."
        );
    }

    /**
     * Assert that no mails were sent.
     *
     * @return void
     */
    public function assertNothingSent(): void
    {
        PHPUnit::assertEmpty($this->mails, 'Mails were sent unexpectedly.');
    }

    /**
     * Get all of the mails matching a truth-test callback.
     *
     * @param  string  $mail
     * @param  callable|null  $callback
     * @return \SimonSchaufi\TYPO3Support\Collection
     */
    public function sent($mail, $callback = null): Collection
    {
        if (! $this->hasSent($mail)) {
            return new Collection();
        }

        $callback = $callback ?: function () {
            return true;
        };

        return $this->mailsOf($mail)->filter(function ($mail) use ($callback) {
            return $callback($mail);
        });
    }

    /**
     * Determine if the given mail has been sent.
     *
     * @param  string  $mail
     * @return bool
     */
    public function hasSent($mail): bool
    {
        return $this->mailsOf($mail)->count() > 0;
    }

    /**
     * Get all of the mailed mails for a given type.
     *
     * @param  string  $type
     * @return \SimonSchaufi\TYPO3Support\Collection
     */
    protected function mailsOf($type): Collection
    {
        return (new Collection($this->mails))->filter(function (RawMessage $mail) use ($type) {
            return $mail instanceof $type;
        });
    }

    /**
     * @param \Symfony\Component\Mime\RawMessage $message
     * @param \Symfony\Component\Mailer\SmtpEnvelope|null $envelope
     */
    public function send(RawMessage $message, SmtpEnvelope $envelope = null): void
    {
        if ($message instanceof Email) {
            // Ensure to always have a From: header set
            if (empty($message->getFrom())) {
                $address = MailUtility::getSystemFromAddress();
                if ($address) {
                    $name = MailUtility::getSystemFromName();
                    if ($name) {
                        $from = new NamedAddress($address, $name);
                    } else {
                        $from = new Address($address);
                    }
                    $message->from($from);
                }
            }
            if (empty($message->getReplyTo())) {
                $replyTo = MailUtility::getSystemReplyTo();
                if (!empty($replyTo)) {
                    $address = key($replyTo);
                    if ($address === 0) {
                        $replyTo = new Address($replyTo[$address]);
                    } else {
                        $replyTo = new NamedAddress(reset($replyTo), $address);
                    }
                    $message->replyTo($replyTo);
                }
            }
            $message->getHeaders()->addTextHeader('X-Mailer', $this->mailerHeader);
        }

        if ($envelope === null) {
            try {
                $envelope = new DelayedSmtpEnvelope($message);
            } catch (\Exception $e) {
                throw new TransportException('Cannot send message without a valid envelope.', 0, $e);
            }
        }

        $this->sentMessage = new SentMessage($message, $envelope);
        $this->mails[] = $message;
    }
}
