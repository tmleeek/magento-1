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

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Webkul\Marketplace\Model\ControllersRepository;

/**
 * Upgrade Data script
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var ControllersRepository
     */
    private $controllersRepository;

    /**
     * @param ControllersRepository $controllersRepository
     * @param EavSetupFactory       $eavSetupFactory
     */
    public function __construct(
        ControllersRepository $controllersRepository
    ) {
        $this->controllersRepository = $controllersRepository;
    }
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /**
         * insert MpAssignProduct controller's data
         */
        $data = [];

        if (!count($this->controllersRepository->getByPath('mpassignproduct/product/view'))) {
            $data[] = [
                'module_name' => 'Webkul_MpAssignProduct',
                'controller_path' => 'mpassignproduct/product/view',
                'label' => 'Assign Product',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }

        if (!count($this->controllersRepository->getByPath('mpassignproduct/product/export'))) {
            $data[] = [
                'module_name' => 'Webkul_MpAssignProduct',
                'controller_path' => 'mpassignproduct/product/productlist',
                'label' => 'Assign Product List',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }

        $setup->getConnection()
            ->insertMultiple($setup->getTable('marketplace_controller_list'), $data);

        $setup->endSetup();
    }
}
