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
use Symfony\Component\Console\Input\InputArgument;
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
        $this->setDescription('Template mail')
            ->addArgument('mode', InputArgument::OPTIONAL, 'Test mode');
    }

    /**
     * Executes the command for sending an email
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $mailSent = false;
        if ($input->getArgument('mode')) {
            switch (strtolower($input->getArgument('mode'))) {
                case 'multilang':
                    $this->testMultiLanguage();
                    $mailSent = true;
                    break;
                case 'simple':
                    $this->testDefault();
                    $mailSent = true;
                    break;
                case 'layout':
                    $this->testDifferentTemplateLayouts();
                    $mailSent = true;
                    break;
                case 'all':
                    $this->testDefault();
                    $this->testMultiLanguage();
                    $this->testDifferentTemplateLayouts();
                    $mailSent = true;
                    break;
            }
        }

        $io = new SymfonyStyle($input, $output);
        if ($mailSent) {
            $io->success('Done');
        } else {
            $io->warning('No mail sent, use of the following modes: simple,multilang,layout,all');
        }
    }

    protected function testDefault(): void
    {
        $templatedEmail = $this->getTemplatedMail();
        $templatedEmail
            ->subject('A mail')
            ->htmlContent('<h1>Hello</h1> an example')
            ->textContent('Hello' . LF . 'an example')
            ->send();
    }

    protected function testMultiLanguage(): void
    {
        $languages = ['en', 'de'];
        foreach ($languages as $language) {
            $templatedEmail = $this->getTemplatedMail();
            $templatedEmail
                ->setLanguage($language)
                ->subject('Multilanguage mail in ' . $language)
                ->context([
                    'title' => 'T3DD'
                ])
                ->htmlTemplateFile('EXT:templatedmail/Resources/Private/Templates/Examples/MultiLanguage.html')
                ->send();
        }
    }

    protected function getTemplatedMail(): TemplatedEmail
    {
        $templatedEmail = GeneralUtility::makeInstance(TemplatedEmail::class);
        $templatedEmail
            ->setSite($this->getSiteByName('master'))
            ->to('dummy@example.org')
            ->from(new NamedAddress('noreply@example.org', 'TYPO3'));

        return $templatedEmail;
    }

    protected function testDifferentTemplateLayouts(): void
    {
        $templatedEmail = $this->getTemplatedMail();
        $templatedEmail
            ->subject('Different template layout')
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
