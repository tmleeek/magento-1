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

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->moveDirToMediaDir();
        $installer = $setup;
        $installer->startSetup();
        /*
         * Create table 'marketplace_assignproduct_items'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('marketplace_assignproduct_items'))
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
                'owner_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['nullable' => false],
                'Owner Id'
            )
            ->addColumn(
                'seller_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['nullable' => false],
                'Seller Id'
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
                'description',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                ['nullable' => false],
                'Description'
            )
            ->addColumn(
                'options',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                ['nullable' => false],
                'Config Options'
            )
            ->addColumn(
                'image',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Image'
            )
            ->addColumn(
                'condition',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['nullable' => false],
                'Condition'
            )
            ->addColumn(
                'type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Product Type'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                [],
                'Created Time'
            )
            ->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['nullable' => false, 'default' => '0'],
                'Status'
            )
            ->setComment('Marketplace AssignProduct Product Table');
        $installer->getConnection()->createTable($table);
        /*
         * Create table 'marketplace_assignproduct_quote'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('marketplace_assignproduct_quote'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'item_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['nullable' => false],
                'Item Id'
            )
            ->addColumn(
                'owner_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['nullable' => false],
                'Owner Id'
            )
            ->addColumn(
                'seller_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['nullable' => false],
                'Seller Id'
            )
            ->addColumn(
                'qty',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['nullable' => false],
                'Qty'
            )
            ->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['nullable' => false],
                'Product Id'
            )
            ->addColumn(
                'quote_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['nullable' => false],
                'Quote Id'
            )
            ->addColumn(
                'assign_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['nullable' => false],
                'Assign Id'
            )
            ->setComment('Marketplace AssignProduct Quote Table');
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }

    private function moveDirToMediaDir()
    {
        try {
            $objManager = \Magento\Framework\App\ObjectManager::getInstance();
            $reader = $objManager->get('Magento\Framework\Module\Dir\Reader');
            $filesystem = $objManager->get('Magento\Framework\Filesystem');
            $fileDriver = $objManager->get('Magento\Framework\Filesystem\Driver\File');
            $type = \Magento\Framework\App\Filesystem\DirectoryList::MEDIA;
            $path = $filesystem->getDirectoryRead($type)
                                ->getAbsolutePath()."marketplace/assignproduct/product/";
            $modulePath = $reader->getModuleDir('', 'Webkul_MpAssignProduct');
            $mediaFile =  $modulePath.'/pub/media/marketplace/assignproduct/product/noimage.jpg';

            if (!$fileDriver->isExists($path)) {
                $fileDriver->createDirectory($path, 0777);
            }
            $filePath = $path."noimage.jpg";
            if (!$fileDriver->isExists($filePath)) {
                if ($fileDriver->isExists($mediaFile)) {
                    $fileDriver->copy($mediaFile, $filePath);
                }
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }
}
