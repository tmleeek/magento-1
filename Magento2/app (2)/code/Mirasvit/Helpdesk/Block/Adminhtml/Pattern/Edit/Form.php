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



namespace Mirasvit\Helpdesk\Block\Adminhtml\Pattern\Edit;

class Form extends \Magento\Backend\Block\Widget\Form
{
    /**
     * @var \Mirasvit\Helpdesk\Model\Config\Source\Scope
     */
    protected $configSourceScope;

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
     * @param \Mirasvit\Helpdesk\Model\Config\Source\Scope $configSourceScope
     * @param \Magento\Framework\Data\FormFactory          $formFactory
     * @param \Magento\Framework\Registry                  $registry
     * @param \Magento\Backend\Block\Widget\Context        $context
     * @param array                                        $data
     */
    public function __construct(
        \Mirasvit\Helpdesk\Model\Config\Source\Scope $configSourceScope,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->configSourceScope = $configSourceScope;
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
        $form = $this->formFactory->create()->setData(
            [
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/save', ['id' => $this->getRequest()->getParam('id')]),
                'method' => 'post',
                'enctype' => 'multipart/form-data',
            ]
        );

        /** @var \Mirasvit\Helpdesk\Model\Pattern $pattern */
        $pattern = $this->registry->registry('current_pattern');

        $fieldset = $form->addFieldset('edit_fieldset', ['legend' => __('General Information')]);
        if ($pattern->getId()) {
            $fieldset->addField('pattern_id', 'hidden', [
                'name' => 'pattern_id',
                'value' => $pattern->getId(),
            ]);
        }
        $fieldset->addField('name', 'text', [
            'label' => __('Title'),
            'name' => 'name',
            'value' => $pattern->getName(),
        ]);
        $fieldset->addField('is_active', 'select', [
            'label' => __('Is Active'),
            'name' => 'is_active',
            'value' => $pattern->getIsActive(),
            'values' => [0 => __('No'), 1 => __('Yes')],
        ]);
        $fieldset->addField('scope', 'select', [
            'label' => __('Scope'),
            'name' => 'scope',
            'value' => $pattern->getScope(),
            'values' => $this->configSourceScope->toOptionArray(),
        ]);
        $fieldset->addField('pattern', 'textarea', [
            'label' => __('Pattern'),
            'name' => 'pattern',
            'value' => $pattern->getPattern(),
            'note' => __('e.g. /special proposal/i')
        ]);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
    /************************/
}
