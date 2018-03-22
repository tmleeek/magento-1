<?php

/**
 * @Author: Ngo Quang Cuong
 * @Date:   2017-06-29 09:23:52
 * @Last Modified by:   nquangcuong
 * @Last Modified time: 2017-06-29 09:35:49
 * @website: http://giaphugroup.com
 */

namespace PHPCuong\CustomerPoints\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'customer_points'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('customer_points'))
            ->addColumn(
                'pid',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Points ID'
            )->addColumn(
                'customer_entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Customers ID'
            )->addColumn(
                'current_points',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => '0'],
                'Current Points'
            )->addColumn(
                'total_points',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => '0'],
                'Total Points'
            )->addColumn(
                'spent_points',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => '0'],
                'Spent Points'
            )->addColumn(
                'last_update',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'The last update'
            )->addForeignKey(
                $installer->getFkName('customer_points', 'customer_entity_id', 'customer_entity', 'entity_id'),
                'customer_entity_id',
                $installer->getTable('customer_entity'),
                'entity_id',
                // use this to delete the row on the customer_points table when deleting a entity_id on the customer_entity table.
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addIndex(
                $installer->getIdxName('customer_points', ['pid', 'customer_entity_id', 'current_points', 'total_points', 'spent_points', 'last_update']),
                ['pid', 'customer_entity_id', 'current_points', 'total_points', 'spent_points', 'last_update']
            )->setComment('Customer Points');

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'customer_points_transaction'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('customer_points_transaction'))
            ->addColumn(
                'tid',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Transaction ID'
            )->addColumn(
                'customer_entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Customers ID'
            )->addColumn(
                'points',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => '0'],
                'Current Points'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '0'],
                'Status: 0 -> Unapproved, 1 -> Approved, 2 -> Denied'
            )->addColumn(
                'description',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                // varchar(255)
                255,
                ['nullable' => true],
                'Description'
            )->addColumn(
                'category_points',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Category ID Points'
            )->addColumn(
                'creation_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Creation Time'
            )->addColumn(
                'modification_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Modification Time'
            )->addForeignKey(
                $installer->getFkName('customer_points_transaction', 'customer_entity_id', 'customer_entity', 'entity_id'),
                'customer_entity_id',
                $installer->getTable('customer_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addIndex(
                $installer->getIdxName('customer_points_transaction', ['tid', 'customer_entity_id', 'points', 'status', 'category_points']),
                ['tid', 'customer_entity_id', 'points', 'status', 'category_points']
            )->setComment('Customer Points Transaction');

        $installer->getConnection()->createTable($table);

        $installer->endSetup();

    }
}
