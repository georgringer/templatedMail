<?php
declare(strict_types=1);

namespace GeorgRinger\Templatedmail\Commands;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use GeorgRinger\Templatedmail\Mail\TemplatedEmail;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mime\NamedAddress;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TemplateCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setDescription('Template mail');
    }

    /**
     * Executes the command for sending an email
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $templatedEmail = GeneralUtility::makeInstance(TemplatedEmail::class);
        $site = $this->getSiteByName('master');
        if ($site) {
            $templatedEmail->setSite($site);
        }
        $templatedEmail
            ->to('dummy@example.org')
            ->from(new NamedAddress('noreply@example.org', 'TYPO3'))
            ->setLanguage('fr')
            ->subject('A mail')
            ->htmlContent('<h1>Hello</h1> an example')
            ->textContent('Hello' . LF . 'an example')
            ->send();

        $io = new SymfonyStyle($input, $output);
        $io->success('Done');
    }

    private function examples()
    {
        $templatedMail = GeneralUtility::makeInstance(TemplatedEmail::class);
        $templatedMail
            ->to('dummy@example.org')
            ->from(new NamedAddress('noreply@example.org', 'TYPO3'))
            ->subject('A mail')
            ->htmlContent('Hello' . LF . 'an example')
            ->textContent('<h1>Hello</h1> an example')
            ->send();

        $templatedEmail = GeneralUtility::makeInstance(TemplatedEmail::class);
        $templatedEmail
            ->to('dummy@example.org')
            ->from(new NamedAddress('noreply@example.org', 'TYPO3'))
            ->subject('A mail')
            ->context(['title' => 'My title'])
            ->htmlTemplateFile('EXT:templatedmail/Resources/Private/Templates/Examples/Example.html')
            ->send();

        $templatedEmail = GeneralUtility::makeInstance(TemplatedEmail::class);
        $templatedEmail
            ->to('dummy@example.org')
            ->from(new NamedAddress('noreply@example.org', 'TYPO3'))
            ->subject('A mail')
            ->setTemplateRootPaths(['EXT:dummy/Resources/Private/Templates/'])
            ->context(['title' => 'My title'])
            ->htmlTemplateName('Examples/Simple')
            ->textTemplateName('Examples/Simple')
            ->send();
    }

    protected function getSiteByName(string $identifier)
    {
        $site = null;
        try {
            $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByIdentifier($identifier);
        } catch (SiteNotFoundException $e) {
        }
        return $site;
    }
}
