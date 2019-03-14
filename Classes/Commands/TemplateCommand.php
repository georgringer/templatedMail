<?php

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
use TYPO3\CMS\Core\Core\Bootstrap;
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
     * Executes the command for importing a t3d/xml file into the TYPO3 system
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $templatedMail = GeneralUtility::makeInstance(TemplatedEmail::class);
        $templatedMail->addTo('dummy@example.org')
            ->addFrom('noreply@fo.com', 'Test')
            ->setSubject('A mail')
//            ->setTemplateRootPaths(
//                ['']
//            )
//            ->addContentAsFluidTemplate('Simple', ['title' => 'My title'], TemplatedEmail::FORMAT_HTML)
//            ->addContentAsFluidTemplateFile('EXT:templatedmail/Resources/Private/Templates/Examples/Example.html', ['title' => 'My title'], TemplatedEmail::FORMAT_HTML)
//            ->addContentAsFluidTemplate('Simple', ['title' => 'My title'], TemplatedEmail::FORMAT_PLAIN)
//            ->addContentAsRaw('das is mir wurscht', TemplatedEmail::FORMAT_PLAIN)
//            ->addContentAsRaw('<h1>das</h1> is mir wurscht', TemplatedEmail::FORMAT_HTML)
//        ->setBody('<html><body>html test content</body></html>', 'text/html')
//        ->addPart('plain test content', 'text/plain')
            ->send();

        $io = new SymfonyStyle($input, $output);
        $io->success('Done');
    }
}
