<?php

/**
 * @Author: Ngo Quang Cuong
 * @Date:   2017-06-29 09:44:40
 * @Last Modified by:   nquangcuong
 * @Last Modified time: 2017-06-29 09:47:02
 * @website: http://giaphugroup.com
 */

namespace PHPCuong\CustomerPoints\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * Upgrades data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.1.2', '<')) {

            $value = [
                'customer_entity_id' => '1',
                'current_points' => '50',
                'spent_points' => '450'
            ];

            $setup->getConnection()->update(
                $setup->getTable('customer_points'),
                $value,
                $setup->getConnection()->quoteInto('pid = ?', '1')
            );

            $transaction = [
                'customer_entity_id' => '1',
                'points' => '-50',
                'status' => '1',
                'description' => 'Spent points to purchase the order #0000014',
                'category_points' => ''
            ];

            $setup->getConnection()->insertOnDuplicate(
                $setup->getTable('customer_points_transaction'),
                $transaction
            );
        }

        $setup->endSetup();
    }
}
