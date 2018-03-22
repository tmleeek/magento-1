<?php
/**
 * Webkul MpDailyDeal Data Setup
 * @category  Webkul
 * @package   Webkul_MpDailyDeal
 * @author    Webkul
 * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpDailyDeal\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $_eavSetupFactory;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->_eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->_eavSetupFactory->create(['setup' => $setup]);

        /**
         * Add attributes to the eav/attribute
         */
        $statusSource = 'Webkul\MpDailyDeal\Model\Config\Source\StatusOptions';
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'deal_from_date');
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'deal_to_date');
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'deal_status',
            [
                'group' => 'Daily Deals',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Deal Status',
                'input' => 'select',
                'class' => '',
                'source' => $statusSource,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'is_used_in_grid' => true,
                'unique' => false
            ]
        );

        $typeSource = 'Webkul\MpDailyDeal\Model\Config\Source\DiscountOptions';
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'deal_discount_type',
            [
                'group' => 'Daily Deals',
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'Discount Type',
                'input' => 'select',
                'class' => '',
                'source' => $typeSource,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'deal_discount_percentage',
            [
                'group' => 'Daily Deals',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'input' => 'hidden',
                'class' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false
            ]
        );

        /**
         * Add attributes to the eav/attribute
         */
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'deal_value',
            [
                'group'        => 'Daily Deals',
                'type'         => 'varchar',
                'backend'      => '',
                'frontend'     => '',
                'label'        => 'Deal Value',
                'input'        => 'text',
                'frontend_class' => 'required-entry validate-zero-or-greater',
                'global'       => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible'      => true,
                'required'     => false,
                'user_defined' => false,
                'default'      => '',
                'searchable'   => false,
                'filterable'   => false,
                'comparable'   => false,
                'unique'       => false,
                'visible_on_front'        => false,
                'used_in_product_listing' => true
            ]
        );

        /**
         * Add attributes to the eav/attribute
         */
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'deal_from_date',
            [
                'group'        => 'Daily Deals',
                'type'         => 'datetime',
                'backend'      => '',
                'frontend'     => '',
                'label'        => '',
                'input'        => 'hidden',
                'frontend_class' => '',
                'global'       => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible'      => true,
                'required'     => false,
                'user_defined' => false,
                'default'      => '',
                'searchable'   => false,
                'filterable'   => false,
                'comparable'   => false,
                'unique'       => false,
                'visible_on_front'        => false,
                'used_in_product_listing' => true
            ]
        );

        /**
         * Add attributes to the eav/attribute
         */
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'deal_to_date',
            [
                'group'        => 'Daily Deals',
                'type'         => 'datetime',
                'backend'      => '',
                'frontend'     => '',
                'label'        => '',
                'input'        => 'hidden',
                'frontend_class' => '',
                'global'       => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible'      => true,
                'required'     => false,
                'user_defined' => false,
                'default'      => '',
                'searchable'   => false,
                'filterable'   => false,
                'comparable'   => false,
                'unique'       => false,
                'visible_on_front'        => false,
                'used_in_product_listing' => true
            ]
        );
    }
}
