<?php
/**
 * Copyright © 2015 Bluethink. All rights reserved.
 */

namespace Bluethink\Checkdelivery\Setup;

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
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
	
        $installer = $setup;

        $installer->startSetup();
		
        $table = $installer->getConnection()->newTable(
            $installer->getTable('checkdelivery_pincode')
        )
        ->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'checkdelivery_pincode'
        )
        ->addColumn(
            'pincode',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'pincode'
        )
        
        
        ->setComment(
            'Bluethink Checkdeliver checkdelivery_pincode'
        );
        
        $installer->getConnection()->createTable($table);
        /*{{CedAddTable}}*/
        $installer->endSetup();

    }
}
