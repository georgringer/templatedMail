<?php

if (\TYPO3\CMS\Core\Utility\GeneralUtility::getApplicationContext()->isTesting()) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][TYPO3\CMS\Core\Mail\Mailer::class] = [
        'className' => \GeorgRinger\Templatedmail\Tests\Classes\FakeMailer::class
    ];
}
