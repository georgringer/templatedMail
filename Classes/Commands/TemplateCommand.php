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
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TemplateCommand extends Command
{
    /**
     * Configure the command by defining the name, options and arguments
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
        $templatedMail = GeneralUtility::makeInstance(TemplatedEmail::class);
        $site = $this->getSiteByName('master');
        if ($site) {
            $templatedMail->setSite($site);
        }
        $templatedMail->addTo('dummy@example.org')
            ->addFrom('noreply@fo.com', 'Test')
            ->setSubject('A mail')
            ->addContentAsRaw('Hello' . LF . 'an example', TemplatedEmail::FORMAT_PLAIN)
            ->addContentAsRaw('<h1>Hello</h1> an example', TemplatedEmail::FORMAT_HTML)
            ->send();

        $io = new SymfonyStyle($input, $output);
        $io->success('Done');
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
