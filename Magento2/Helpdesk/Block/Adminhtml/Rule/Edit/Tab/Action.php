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

class Action extends \Magento\Backend\Block\Widget\Form
{
    /**
     * @var \Mirasvit\Helpdesk\Model\ResourceModel\Status\CollectionFactory
     */
    protected $statusCollectionFactory;

    /**
     * @var \Mirasvit\Helpdesk\Model\ResourceModel\Priority\CollectionFactory
     */
    protected $priorityCollectionFactory;

    /**
     * @var \Mirasvit\Helpdesk\Model\ResourceModel\Department\CollectionFactory
     */
    protected $departmentCollectionFactory;

    /**
     * @var \Mirasvit\Helpdesk\Model\Config\Source\Is\Archive
     */
    protected $configSourceIsArchive;

    /**
     * @var \Mirasvit\Helpdesk\Helper\Html
     */
    protected $helpdeskHtml;

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
     * @param \Mirasvit\Helpdesk\Model\ResourceModel\Status\CollectionFactory     $statusCollectionFactory
     * @param \Mirasvit\Helpdesk\Model\ResourceModel\Priority\CollectionFactory   $priorityCollectionFactory
     * @param \Mirasvit\Helpdesk\Model\ResourceModel\Department\CollectionFactory $departmentCollectionFactory
     * @param \Mirasvit\Helpdesk\Model\Config\Source\Is\Archive                   $configSourceIsArchive
     * @param \Mirasvit\Helpdesk\Helper\Html                                      $helpdeskHtml
     * @param \Magento\Framework\Data\FormFactory                                 $formFactory
     * @param \Magento\Framework\Registry                                         $registry
     * @param \Magento\Backend\Block\Widget\Context                               $context
     * @param array                                                               $data
     */
    public function __construct(
        \Mirasvit\Helpdesk\Model\ResourceModel\Status\CollectionFactory $statusCollectionFactory,
        \Mirasvit\Helpdesk\Model\ResourceModel\Priority\CollectionFactory $priorityCollectionFactory,
        \Mirasvit\Helpdesk\Model\ResourceModel\Department\CollectionFactory $departmentCollectionFactory,
        \Mirasvit\Helpdesk\Model\Config\Source\Is\Archive $configSourceIsArchive,
        \Mirasvit\Helpdesk\Helper\Html $helpdeskHtml,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->statusCollectionFactory = $statusCollectionFactory;
        $this->priorityCollectionFactory = $priorityCollectionFactory;
        $this->departmentCollectionFactory = $departmentCollectionFactory;
        $this->configSourceIsArchive = $configSourceIsArchive;
        $this->helpdeskHtml = $helpdeskHtml;
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

        $fieldset = $form->addFieldset('action_fieldset', ['legend' => __('Actions')]);
        if ($rule->getId()) {
            $fieldset->addField('rule_id', 'hidden', [
                'name' => 'rule_id',
                'value' => $rule->getId(),
            ]);
        }
        $fieldset->addField('status_id', 'select', [
            'label' => __('Set Status'),
            'name' => 'status_id',
            'value' => $rule->getStatusId(),
            'values' => $this->statusCollectionFactory->create()->toOptionArray(true),
        ]);
        $fieldset->addField('priority_id', 'select', [
            'label' => __('Set Priority'),
            'name' => 'priority_id',
            'value' => $rule->getPriorityId(),
            'values' => $this->priorityCollectionFactory->create()->toOptionArray(true),
        ]);
        $fieldset->addField('department_id', 'select', [
            'label' => __('Set Department'),
            'name' => 'department_id',
            'value' => $rule->getDepartmentId(),
            'values' => $this->departmentCollectionFactory->create()->toOptionArray(true),
        ]);
        $fieldset->addField('user_id', 'select', [
            'label' => __('Set Owner'),
            'name' => 'user_id',
            'value' => $rule->getUserId(),
            'values' => $this->helpdeskHtml->toAdminUserOptionArray(true),
        ]);
        $fieldset->addField('is_archive', 'select', [
            'label' => __('Archive'),
            'name' => 'is_archive',
            'value' => $rule->getIsArchive(),
            'values' => $this->configSourceIsArchive->toOptionArray(true),
        ]);
        $fieldset->addField('add_tags', 'text', [
            'label' => __('Add Tags'),
            'name' => 'add_tags',
            'value' => $rule->getAddTags(),
            'note' => __('comma-separated list'),
        ]);
        $fieldset->addField('remove_tags', 'text', [
            'label' => __('Remove Tags'),
            'name' => 'remove_tags',
            'value' => $rule->getRemoveTags(),
            'note' => __('comma-separated list'),
        ]);

        return parent::_prepareForm();
    }

    /************************/
}
