<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-helpdesk
 * @version   1.1.25
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Helpdesk\Test\Unit\Mocks\Magento;

class StoreMock
{
    /**
     * @param \PHPUnit_Framework_TestCase $testCase
     * @param array                       $data
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public static function create(\PHPUnit_Framework_TestCase $testCase, $data = [])
    {
        $mock = $testCase->getMock('Magento\Store\Model\Store', ['load', 'save', 'delete'], [], '', false);
        $defaultData = [
            'id' => 1,
            'code' => 'store_en',
            'name' => 'English Store',
            'sort_order' => '10',
            'is_active' => true,
        ];
        $data = array_merge($defaultData, $data);
        $mock->setData($data);

        return $mock;
    }
}
