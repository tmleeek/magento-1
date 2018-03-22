<?php
/**
 * Copyright © 2016 Pixafy Services LLC. All rights reserved.
 *
 * @license http://opensource.org/licenses/OSL-3.0  The Open Software License 3.0 (OSL 3.0)
 * @author  Alex Lukyanau <alukyanau@pixafy.com>
 */
namespace VertexSMB\Tax\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{

    /**
     * Create table 'vertexsmb_taxrequest'
     *
     * {@inheritDoc}
     *
     * @see \Magento\Framework\Setup\InstallSchemaInterface::install()
     */
    // @codingStandardsIgnoreStart
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        
        $table = $setup->getConnection()
            ->newTable($setup->getTable('vertexsmb_taxrequest'))
            ->addColumn(
                'request_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                [
                'identity' => true,
                'nullable' => false,
                'primary' => true
                ],
                'Request Id'
            )
            ->addColumn(
                'request_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [
                'nullable' => false
                ],
                'Request Type'
            )
            ->addColumn(
                'quote_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                [
                'nullable' => false,
                'default' => '0'
                ],
                'Quote ID'
            )
            ->addColumn(
                'order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                [
                'nullable' => false,
                'default' => '0'
                ],
                'Order ID'
            )
            ->addColumn(
                'total_tax',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [
                'nullable' => false
                ],
                'Total Tax Amount'
            )
            ->addColumn(
                'source_path',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [
                'nullable' => false
                ],
                'Source path controller_module_action'
            )
            ->addColumn(
                'tax_area_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [
                'nullable' => false
                ],
                'Tax Jurisdictions Id'
            )
            ->addColumn(
                'sub_total',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [
                'nullable' => false
                ],
                'Response Subtotal Amount'
            )
            ->addColumn(
                'total',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [
                'nullable' => false
                ],
                'Response Total Amount'
            )
            ->addColumn(
                'lookup_result',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [
                'nullable' => false
                ],
                'Tax Area Response Lookup Result'
            )
            ->addColumn(
                'request_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [
                'nullable' => false,
                'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT
                ],
                'Request create date'
            )
            ->addColumn(
                'request_xml',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [
                'nullable' => false
                ],
                'Request XML'
            )
            ->addColumn(
                'response_xml',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [
                'nullable' => false
                ],
                'Response XML'
            )
            
            ->addIndex(
                $setup->getIdxName(
                    'vertexsmb_taxrequest',
                    [
                    'request_id'
                    ],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                [
                'request_id'
                ],
                [
                'type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ]
            )
            
            ->addIndex(
                $setup->getIdxName(
                    'vertexsmb_taxrequest',
                    [
                    'request_type'
                    ]
                ),
                [
                'request_type'
                ]
            )
            ->addIndex(
                $setup->getIdxName(
                    'vertexsmb_taxrequest',
                    [
                    'order_id'
                    ]
                ),
                [
                'order_id'
                ]
            )
            ->setComment('Log of requests to VertexSMB');
        $setup->getConnection()->createTable($table);
 
        /**
         * Add TaxAreaId To Quote & Order Address Tables *
         */
        $setup->getConnection()->addColumn(
            $setup->getTable('quote_address'),
            'tax_area_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [
            'nullable' => false
            ],
            'Tax Jurisdiction Id'
        );
        
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order_address'),
            'tax_area_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [
            'nullable' => false
            ],
            'Tax Jurisdiction Id'
        );
        
        $setup->endSetup();
    }
}
