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



namespace Mirasvit\Helpdesk\Test\Unit\Mocks\Magento\User;

use Mirasvit\Helpdesk\Test\Unit\Mocks\Magento\UserMock;

class CollectionMock extends \Magento\User\Model\ResourceModel\User\Collection
{
    use \Mirasvit\Helpdesk\Test\Unit\Mocks\Lib\CollectionTrait;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $testCase
     * @param bool|false                                                $items
     */
    public function __construct($testCase, $items = false)
    {
        if (!$items) {
            $items = [
                UserMock::create(
                    $testCase,
                    [
                        'user_id' => 1,
                        'firstname' => 'John',
                        'lastname' => 'Doe',

                    ]
                ),
                UserMock::create(
                    $testCase,
                    [
                        'user_id' => 2,
                        'firstname' => 'Bill',
                        'lastname' => 'Gates',

                    ]
                ),
                UserMock::create(
                    $testCase,
                    [
                        'user_id' => 3,
                        'firstname' => 'Jim',
                        'lastname' => 'White',

                    ]
                ),
            ];
        }
        $this->items = $items;
    }
}
