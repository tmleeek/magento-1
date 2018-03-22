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



namespace Mirasvit\Helpdesk\Block\Adminhtml\Rule\Edit\Tab;

class General extends \Magento\Backend\Block\Widget\Form
{
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
     * @param \Magento\Framework\Data\FormFactory   $formFactory
     * @param \Magento\Framework\Registry           $registry
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array                                 $data
     */
    public function __construct(
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->context = $context;
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $form = $this->formFactory->create();
        $this->setForm($form);
        /** @var \Mirasvit\Helpdesk\Model\Rule $rule */
        $rule = $this->registry->registry('current_rule');

        $fieldset = $form->addFieldset('edit_fieldset', ['legend' => __('General Information')]);
        if ($rule->getId()) {
            $fieldset->addField('rule_id', 'hidden', [
                'name' => 'rule_id',
                'value' => $rule->getId(),
            ]);
        }
        $fieldset->addField('name', 'text', [
            'label' => __('Rule Name'),
            'required' => true,
            'name' => 'name',
            'value' => $rule->getName(),
        ]);
        $fieldset->addField('is_active', 'select', [
            'label' => __('Is Active'),
            'required' => true,
            'name' => 'is_active',
            'value' => $rule->getIsActive(),
            'values' => [0 => __('No'), 1 => __('Yes')],
        ]);
        $fieldset->addField('sort_order', 'text', [
            'label' => __('Priority'),
            'name' => 'sort_order',
            'value' => $rule->getSortOrder() ? $rule->getSortOrder() : 10,
            'note' => __('Arranged in the ascending order. 0 is the highest.'),
        ]);
        $fieldset->addField('is_stop_processing', 'select', [
            'label' => __('Stop Further Rules Processing'),
            'name' => 'is_stop_processing',
            'value' => $rule->getIsStopProcessing(),
            'values' => [0 => __('No'), 1 => __('Yes')],
        ]);

        return parent::_prepareForm();
    }

    /************************/
}
