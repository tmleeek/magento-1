<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-helpdesk
 * @version   1.1.25
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Helpdesk\Block\Adminhtml\Field\Edit;

class Form extends \Magento\Backend\Block\Widget\Form
{
    /**
     * @var \Mirasvit\Helpdesk\Model\Config\Source\Field\Type
     */
    protected $configSourceFieldType;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * @var \Mirasvit\Helpdesk\Helper\Storeview
     */
    protected $helpdeskStoreview;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Block\Widget\Context
     */
    protected $context;

    /**
     * @param \Mirasvit\Helpdesk\Model\Config\Source\Field\Type $configSourceFieldType
     * @param \Magento\Store\Model\System\Store                 $systemStore
     * @param \Mirasvit\Helpdesk\Helper\Storeview               $helpdeskStoreview
     * @param \Magento\Framework\Data\FormFactory               $formFactory
     * @param \Magento\Framework\Registry                       $registry
     * @param \Magento\Backend\Block\Widget\Context             $context
     * @param array                                             $data
     */
    public function __construct(
        \Mirasvit\Helpdesk\Model\Config\Source\Field\Type $configSourceFieldType,
        \Magento\Store\Model\System\Store $systemStore,
        \Mirasvit\Helpdesk\Helper\Storeview $helpdeskStoreview,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->configSourceFieldType = $configSourceFieldType;
        $this->systemStore = $systemStore;
        $this->helpdeskStoreview = $helpdeskStoreview;
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->context = $context;
        parent::__construct($context, $data);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @return $this
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $form = $this->formFactory->create()->setData(
            [
                'id' => 'edit_form',
                'action' => $this->getUrl(
                    '*/*/save',
                    [
                        'id' => $this->getRequest()->getParam('id'),
                        'store' => (int) $this->getRequest()->getParam('store'),
                    ]
                ),
                'method' => 'post',
                'enctype' => 'multipart/form-data',
            ]
        );

        /** @var \Mirasvit\Helpdesk\Model\Field $field */
        $field = $this->registry->registry('current_field');

        $fieldset = $form->addFieldset('edit_fieldset', ['legend' => __('General Information')]);
        if ($field->getId()) {
            $fieldset->addField('field_id', 'hidden', [
                'name' => 'field_id',
                'value' => $field->getId(),
            ]);
        }
        $fieldset->addField('store_id', 'hidden', [
            'name' => 'store_id',
            'value' => (int) $this->getRequest()->getParam('store'),
        ]);

        $fieldset->addField('name', 'text', [
            'label' => __('Title'),
            'required' => true,
            'name' => 'name',
            'value' => $field->getName(),
            'scope_label' => __('[STORE VIEW]'),
        ]);
        $fieldset->addField('code', 'text', [
            'label' => __('Code'),
            'required' => true,
            'name' => 'code',
            'value' => $field->getCode(),
            'note' => 'Internal field. Can contain only letters, digits and underscore.',
            'disabled' => $field->getId(), '', 'disabled',
        ]);
        $fieldset->addField('type', 'select', [
            'label' => __('Type'),
            'required' => true,
            'name' => 'type',
            'value' => $field->getType(),
            'values' => $this->configSourceFieldType->toOptionArray(),
        ]);
        $fieldset->addField('description', 'textarea', [
            'label' => __('Description'),
            'name' => 'description',
            'value' => $field->getDescription(),
            'scope_label' => __('[STORE VIEW]'),
        ]);
        $fieldset->addField('values', 'textarea', [
            'label' => __('Options list'),
            'name' => 'values',
            'value' => $this->helpdeskStoreview->getStoreViewValue($field, 'values'),
            'note' => __(
                'Only for drop-down list. <br>Enter each value from the new line using format:
                    <br>value1 | label1
                    <br>value2 | label2'
            ),
            'scope_label' => __('[STORE VIEW]'),
        ]);
        $fieldset->addField('is_active', 'select', [
            'label' => __('Active'),
            'name' => 'is_active',
            'value' => $field->getIsActive(),
            'values' => [0 => __('No'), 1 => __('Yes')],
        ]);
        $fieldset->addField('sort_order', 'text', [
            'label' => __('Sort Order'),
            'name' => 'sort_order',
            'value' => $field->getSortOrder(),
        ]);
        $fieldset->addField('is_visible_customer', 'select', [
            'label' => __('Show value in customer account'),
            'name' => 'is_visible_customer',
            'value' => $field->getIsVisibleCustomer(),
            'values' => [0 => __('No'), 1 => __('Yes')],
        ]);
        $fieldset->addField('is_editable_customer', 'select', [
            'label' => __('Show in create ticket form'),
            'name' => 'is_editable_customer',
            'value' => $field->getIsEditableCustomer(),
            'values' => [0 => __('No'), 1 => __('Yes')],
        ]);
        $fieldset->addField('is_visible_contact_form', 'select', [
            'label' => __('Show in contact us form'),
            'name' => 'is_visible_contact_form',
            'value' => $field->getIsVisibleContactForm(),
            'values' => [0 => __('No'), 1 => __('Yes')],
        ]);
        $fieldset->addField('is_required_customer', 'select', [
            'label' => __('Required for customers'),
            'name' => 'is_required_customer',
            'value' => $field->getIsRequiredCustomer(),
            'values' => [0 => __('No'), 1 => __('Yes')],
        ]);
        $fieldset->addField('is_required_staff', 'select', [
            'label' => __('Required for staff'),
            'name' => 'is_required_staff',
            'value' => $field->getIsRequiredStaff(),
            'values' => [0 => __('No'), 1 => __('Yes')],
        ]);
        $fieldset->addField('store_ids', 'multiselect', [
            'label' => __('Stores'),
            'required' => true,
            'name' => 'store_ids[]',
            'value' => $field->getStoreIds(),
            'values' => $this->systemStore->getStoreValuesForForm(false, false),
        ]);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
    /************************/
}
