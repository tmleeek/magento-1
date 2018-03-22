<?php

/**
 * @Author: Ngo Quang Cuong
 * @Date:   2017-06-29 09:31:49
 * @Last Modified by:   nquangcuong
 * @Last Modified time: 2017-06-29 09:32:06
 * @website: http://giaphugroup.com
 */

namespace PHPCuong\CustomerPoints\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    /**
     * {@inheritdoc}
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        // add the new data to the customer_points table.
        $value = [
            'customer_entity_id' => '1',
            'current_points' => '100',
            'total_points' => '500',
            'spent_points' => '400'
        ];

        $setup->getConnection()->insertOnDuplicate(
            $setup->getTable('customer_points'),
            $value
        );

        // add the new data to the customer_points_transaction table.
        $transactions = [];

        $transactions[] = [
            'customer_entity_id' => '1',
            'points' => '500',
            'status' => '1',
            'description' => 'The points added by Admin',
            'category_points' => ''
        ];

        $transactions[] = [
            'customer_entity_id' => '1',
            'points' => '-200',
            'status' => '1',
            'description' => 'Spent points to purchase the order #0000013',
            'category_points' => ''
        ];

        $transactions[] = [
            'customer_entity_id' => '1',
            'points' => '-200',
            'status' => '1',
            'description' => 'Spent points to purchase the order #0000012',
            'category_points' => ''
        ];

        foreach ($transactions as $transaction) {
            $setup->getConnection()->insertOnDuplicate(
                $setup->getTable('customer_points_transaction'),
                $transaction
            );
        }

        $setup->endSetup();
    }
}
