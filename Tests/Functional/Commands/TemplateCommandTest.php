<?php
declare(strict_types=1);

namespace GeorgRinger\Templatedmail\Tests\Functional\Commands;

use GeorgRinger\Templatedmail\Commands\TemplateCommand;
use GeorgRinger\Templatedmail\Mail\TemplatedEmail;
use GeorgRinger\Templatedmail\Tests\Classes\FakeMailer;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\NamedAddress;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class TemplateCommandTest extends FunctionalTestCase
{
    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/templatedmail',
    ];

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function setUp(): void
    {
        parent::setUp();

        Bootstrap::initializeLanguageObject();

        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport'] = 'null';
    }

    /**
     * @test
     */
    public function noModeCommandTest(): void
    {
        /** @var SendEmailCommand|\PHPUnit\Framework\MockObject\MockObject $command */
        $command = $this->getMockBuilder(TemplateCommand::class)
            ->setConstructorArgs(['mail:template'])
            ->onlyMethods(['getSiteByName'])
            ->getMock();

        $tester = new CommandTester($command);
        $tester->execute([]);

        $this->assertTrue(strpos($tester->getDisplay(), 'No mail sent') > 0);
    }

    /**
     * @test
     */
    public function simpleModeCommandTest(): void
    {
        $site = $this->createTestProxy(Site::class, ['site', 1, []]);

        /** @var SendEmailCommand|\PHPUnit\Framework\MockObject\MockObject $command */
        $command = $this->getMockBuilder(TemplateCommand::class)
            ->setConstructorArgs(['mail:template'])
            ->onlyMethods(['getSiteByName'])
            ->getMock();

        $command
            ->expects($this->atLeast(1))
            ->method('getSiteByName')
            ->with('master')
            ->willReturn($site);

        $tester = new CommandTester($command);
        $tester->execute(['mode' => 'simple']);

        $this->assertTrue(strpos($tester->getDisplay(), 'Done') > 0);

        $mailer = GeneralUtility::makeInstance(FakeMailer::class);
        $mailer->assertSent(TemplatedEmail::class, 1);
        $mailer->assertSent(TemplatedEmail::class, function (TemplatedEmail $mail) {
            return $mail->getSubject() === 'A mail'
                   && strpos($mail->getTextBody(), 'Hello' . LF . 'an example')
                   && strpos($mail->getHtmlBody(), 'Hello</h1> an example')
                   && in_array(Address::create('dummy@example.org'), $mail->getTo(), false)
                   && in_array(new NamedAddress('noreply@example.org', 'TYPO3'), $mail->getFrom(), false);
        });
    }

    /**
     * @test
     */
    public function multiLangModeCommandTest(): void
    {
        $site = $this->createTestProxy(Site::class, ['site', 1, []]);

        /** @var SendEmailCommand|\PHPUnit\Framework\MockObject\MockObject $command */
        $command = $this->getMockBuilder(TemplateCommand::class)
            ->setConstructorArgs(['mail:template'])
            ->onlyMethods(['getSiteByName'])
            ->getMock();

        $command
            ->expects($this->atLeast(1))
            ->method('getSiteByName')
            ->with('master')
            ->willReturn($site);

        $tester = new CommandTester($command);
        $tester->execute(['mode' => 'multilang']);

        $this->assertTrue(strpos($tester->getDisplay(), 'Done') > 0);

        $mailer = GeneralUtility::makeInstance(FakeMailer::class);
        $mailer->assertSent(TemplatedEmail::class, 2);
        $mailer->assertSent(TemplatedEmail::class, function (TemplatedEmail $mail) {
            return in_array(Address::create('dummy@example.org'), $mail->getTo(), false)
                && in_array(new NamedAddress('noreply@example.org', 'TYPO3'), $mail->getFrom(), false);
        });
    }

    /**
     * @test
     */
    public function layoutModeCommandTest(): void
    {
        $site = $this->createTestProxy(Site::class, ['site', 1, []]);

        /** @var SendEmailCommand|\PHPUnit\Framework\MockObject\MockObject $command */
        $command = $this->getMockBuilder(TemplateCommand::class)
            ->setConstructorArgs(['mail:template'])
            ->onlyMethods(['getSiteByName'])
            ->getMock();

        $command
            ->expects($this->atLeast(1))
            ->method('getSiteByName')
            ->with('master')
            ->willReturn($site);

        $tester = new CommandTester($command);
        $tester->execute(['mode' => 'layout']);

        $this->assertTrue(strpos($tester->getDisplay(), 'Done') > 0);

        $mailer = GeneralUtility::makeInstance(FakeMailer::class);
        $mailer->assertSent(TemplatedEmail::class, 1);
        $mailer->assertSent(TemplatedEmail::class, function (TemplatedEmail $mail) {
            return $mail->getSubject() === 'Different template layout'
                   && strpos($mail->getTextBody(), '-----------------------------------')
                   && strpos($mail->getHtmlBody(), 'New TYPO3 site</h1>')
                   && in_array(Address::create('dummy@example.org'), $mail->getTo(), false)
                   && in_array(new NamedAddress('noreply@example.org', 'TYPO3'), $mail->getFrom(), false);
        });
    }
}
