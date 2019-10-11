<?php
declare(strict_types=1);

namespace GeorgRinger\Templatedmail\Tests\Unit\Mail;

use GeorgRinger\Templatedmail\Mail\TemplatedEmail;
use TYPO3\TestingFramework\Core\BaseTestCase;

class TemplatedEmailTest extends BaseTestCase
{
    /**
     * @test
     */
    public function defaultValuesAreReturned()
    {
        $subject = $this->getAccessibleMock(TemplatedEmail::class, ['dummy'], [], '', false);
        $defaults = [
            'sitename' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'],
            'clientIp' => '',
            'language' => '',
            'formats' => [
                'date' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'],
                'time' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm']
            ]
        ];
        $this->assertEquals($defaults, $subject->_call('getDefaultVariables'));
    }
}
