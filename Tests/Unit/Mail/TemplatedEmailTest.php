<?php


namespace GeorgRinger\Templatedmail\Tests\Unit\Mail;

/**
 * This file is part of the "tt_address" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

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
            'clientIp' => ''
        ];
        $this->assertEquals($defaults, $subject->_call('getDefaultVariables'));
    }

}
