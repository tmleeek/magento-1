<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Webkul\Marketplace\Model\ControllersRepository;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;

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
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @param ControllersRepository $controllersRepository
     * @param EavSetupFactory       $eavSetupFactory
     */
    public function __construct(
        ControllersRepository $controllersRepository,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->controllersRepository = $controllersRepository;
        $this->eavSetupFactory = $eavSetupFactory;
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
         * insert marketplace controller's data
         */
        $data = [];
        if (!count($this->controllersRepository->getByPath('marketplace/account/dashboard'))) {
            $data[] = [
                'module_name' => 'Webkul_Marketplace',
                'controller_path' => 'marketplace/account/dashboard',
                'label' => 'Marketplace Dashboard',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }
        if (!count($this->controllersRepository->getByPath('marketplace/account/editprofile'))) {
            $data[] = [
                'module_name' => 'Webkul_Marketplace',
                'controller_path' => 'marketplace/account/editprofile',
                'label' => 'Seller Profile',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }
        if (!count($this->controllersRepository->getByPath('marketplace/product_attribute/new'))) {
            $data[] = [
                'module_name' => 'Webkul_Marketplace',
                'controller_path' => 'marketplace/product_attribute/new',
                'label' => 'Create Attribute',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }
        if (!count($this->controllersRepository->getByPath('marketplace/product/add'))) {
            $data[] = [
                'module_name' => 'Webkul_Marketplace',
                'controller_path' => 'marketplace/product/add',
                'label' => 'New Products',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }
        if (!count($this->controllersRepository->getByPath('marketplace/product/productlist'))) {
            $data[] = [
                'module_name' => 'Webkul_Marketplace',
                'controller_path' => 'marketplace/product/productlist',
                'label' => 'My Products List',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }
        if (!count($this->controllersRepository->getByPath('marketplace/transaction/history'))) {
            $data[] = [
                'module_name' => 'Webkul_Marketplace',
                'controller_path' => 'marketplace/transaction/history',
                'label' => 'My Transaction List',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }
        if (!count($this->controllersRepository->getByPath('marketplace/order/shipping'))) {
            $data[] = [
                'module_name' => 'Webkul_Marketplace',
                'controller_path' => 'marketplace/order/shipping',
                'label' => 'Manage Print PDF Header Info',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }
        if (!count($this->controllersRepository->getByPath('marketplace/order/history'))) {
            $data[] = [
                'module_name' => 'Webkul_Marketplace',
                'controller_path' => 'marketplace/order/history',
                'label' => 'My Order History',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }
        if (count($data)) {
            $setup->getConnection()
                ->insertMultiple($setup->getTable('marketplace_controller_list'), $data);
        }

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'mp_product_cart_limit',
            [
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'Product Purchase Limit for Customer',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to'     => 'simple,configurable,bundle',
                'frontend_class'=>'validate-zero-or-greater',
                'note' => 'Not applicable on downloadable and virtual product.'
            ]
        );

        $setup->endSetup();
    }
}
