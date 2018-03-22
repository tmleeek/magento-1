<?php
namespace VertexSMB\Tax\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        $setup->startSetup();
        if (version_compare($context->getVersion(), '2.0.1') < 0) {
            $installer->getConnection()->changeColumn(
                $installer->getTable('vertexsmb_taxrequest'),
                'quote_id',
                'quote_id',
                [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                'length' => 20,
                ]
            );
        
            $installer->getConnection()->changeColumn(
                $installer->getTable('vertexsmb_taxrequest'),
                'order_id',
                'order_id',
                [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                'length' => 20,
                ]
            );

            $setup->endSetup();
        }
        
        if (version_compare($context->getVersion(), '2.0.2') < 0) {
            $installer->getConnection()->addColumn(
                $installer->getTable('sales_invoice'),
                'vertex_invoice_sent',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                [
                'nullable' => false,
                'default' => '0'
                ]
            );
        }
    }
}
