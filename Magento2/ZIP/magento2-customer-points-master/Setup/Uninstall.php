<?php

/**
 * @Author: Ngo Quang Cuong
 * @Date:   2017-06-29 09:48:45
 * @Last Modified by:   nquangcuong
 * @Last Modified time: 2017-06-29 09:50:03
 * @website: http://giaphugroup.com
 */

namespace PHPCuong\CustomerPoints\Setup;

use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class Uninstall implements UninstallInterface
{
    /**
     * Drop two tables customer_points and customer_points_transaction
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $installer->getConnection()->dropTable($installer->getTable('customer_points'));

        $installer->getConnection()->dropTable($installer->getTable('customer_points_transaction'));

        $installer->endSetup();
    }
}
