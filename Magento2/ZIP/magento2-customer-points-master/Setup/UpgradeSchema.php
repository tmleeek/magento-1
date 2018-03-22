<?php

/**
 * @Author: Ngo Quang Cuong
 * @Date:   2017-06-29 09:38:25
 * @Last Modified by:   nquangcuong
 * @Last Modified time: 2017-06-29 09:41:03
 * @website: http://giaphugroup.com
 */

namespace PHPCuong\CustomerPoints\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.1.1', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('customer_points'),
                'created_at',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    'nullable' => true,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT,
                    'comment' => 'Created At'
                ]
            );
        }

        $setup->endSetup();
    }
}
