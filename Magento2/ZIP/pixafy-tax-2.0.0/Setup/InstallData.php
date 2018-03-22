<?php
/**
 * Copyright © 2016 Pixafy Services LLC. All rights reserved.
 *
 * @license http://opensource.org/licenses/OSL-3.0  The Open Software License 3.0 (OSL 3.0)
 * @author  Alex Lukyanau <alukyanau@pixafy.com>
 */
namespace VertexSMB\Tax\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{

    /**
     *
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     *
     * @var AttributeSetFactory
     */
    protected $attributeSetFactory;

    /**
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Init
     *
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory  $attributeSetFactory
     */
    public function __construct(CustomerSetupFactory $customerSetupFactory, AttributeSetFactory $attributeSetFactory, \Psr\Log\LoggerInterface $logger)
    {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->logger = $logger;
    }

    /**
     * Install Tax Classes & Customer Attribute
     *
     * {@inheritDoc}
     *
     * @see \Magento\Framework\Setup\InstallDataInterface::install()
     */
    // @codingStandardsIgnoreStart
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $data = [
            [
                'class_name' => 'Refund Adjustments',
                'class_type' => \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT
            ],
            [
                'class_name' => 'Gift Options',
                'class_type' => \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT
            ],
            [
                'class_name' => 'Order Gift Wrapping',
                'class_type' => \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT
            ],
            [
                'class_name' => 'Item Gift Wrapping',
                'class_type' => \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT
            ],
            [
                'class_name' => 'Printed Gift Card',
                'class_type' => \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT
            ],
            [
                'class_name' => 'Reward Points',
                'class_type' => \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT
            ]
        ];
        foreach ($data as $row) {
            $setup->getConnection()->insertForce($setup->getTable('tax_class'), $row);
        }
        
        /**
 * @var CustomerSetup $customerSetup
*/
        $customerSetup = $this->customerSetupFactory->create(
            [
            'setup' => $setup
            ]
        );
        
        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();
        
        /**
 * @var $attributeSet AttributeSet
*/
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
        
        $customerSetup->addAttribute(
            Customer::ENTITY,
            'customer_code',
            [
            'type' => 'varchar',
            'label' => 'VertexSMB Customer Code',
            'input' => 'text',
            'required' => false,
            'visible' => true,
            'user_defined' => true,
            'sort_order' => 90,
            'position' => 90,
            'system' => 0
            ]
        );
        
        $attribute = $customerSetup->getEavConfig()
            ->getAttribute(Customer::ENTITY, 'customer_code')
            ->addData(
                [
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => [
                'adminhtml_customer'
                ]
                ]
            );
        try{
            $attribute->save();
        }
        catch(\Exception $e)
        {
            $this->logger->critical($e);
        }
    }
}
