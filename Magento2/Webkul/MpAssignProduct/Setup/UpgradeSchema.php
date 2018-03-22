<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAssignProduct\Setup;

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
        $setup->getConnection()->addColumn(
            $setup->getTable('marketplace_assignproduct_quote'),
            'child_assign_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'Child Assign Id'
            ]
        );
        /*
         * Create table 'marketplace_assignproduct_associated_products'
         */
        $table = $setup->getConnection()
                ->newTable($setup->getTable('marketplace_assignproduct_associated_products'))
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    ['identity' => true, 'nullable' => false, 'primary' => true],
                    'Id'
                )
                ->addColumn(
                    'product_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    ['nullable' => false],
                    'Product Id'
                )
                ->addColumn(
                    'parent_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    ['nullable' => false],
                    'Parent Assign Id'
                )
                ->addColumn(
                    'parent_product_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    ['nullable' => false],
                    'Parent Product Id'
                )
                ->addColumn(
                    'qty',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    ['nullable' => false],
                    'Qty'
                )
                ->addColumn(
                    'price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    ['nullable' => false, 'default' => '0.0000'],
                    'Price'
                )
                ->addColumn(
                    'options',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '2M',
                    ['nullable' => false],
                    'Config Options'
                )
                ->setComment('Marketplace AssignProduct Associated Product Table');
        $setup->getConnection()->createTable($table);

        /*
         * Create table 'marketplace_assignproduct_data'
         */
        $table = $setup->getConnection()
            ->newTable($setup->getTable('marketplace_assignproduct_data'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'type',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['nullable' => false],
                'Type'
            )
            ->addColumn(
                'assign_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['nullable' => false],
                'Assign Product Id'
            )
            ->addColumn(
                'value',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Value'
            )
            ->addColumn(
                'date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                [],
                'Date'
            )
            ->addColumn(
                'is_default',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['nullable' => false],
                'Default'
            )
            ->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['nullable' => false, 'default' => '0'],
                'Status'
            )
            ->setComment('Marketplace AssignProduct Data Table');
        $setup->getConnection()->createTable($table);

        $setup->endSetup();
    }
}
