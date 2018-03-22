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



namespace Mirasvit\Helpdesk\Block\Adminhtml\Department\Edit;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Form extends \Magento\Backend\Block\Widget\Form
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

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
     * @param \Magento\Store\Model\System\Store     $systemStore
     * @param \Mirasvit\Helpdesk\Helper\Html        $helpdeskHtml
     * @param \Magento\Framework\Data\FormFactory   $formFactory
     * @param \Magento\Framework\Registry           $registry
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array                                 $data
     */
    public function __construct(
        \Magento\Store\Model\System\Store $systemStore,
        \Mirasvit\Helpdesk\Helper\Html $helpdeskHtml,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
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

        /** @var \Mirasvit\Helpdesk\Model\Department $department */
        $department = $this->registry->registry('current_department');

        $fieldset = $form->addFieldset('edit_fieldset', ['legend' => __('General Information')]);
        if ($department->getId()) {
            $fieldset->addField('department_id', 'hidden', [
                'name' => 'department_id',
                'value' => $department->getId(),
            ]);
        }
        $fieldset->addField('store_id', 'hidden', [
            'name' => 'store_id',
            'value' => (int) $this->getRequest()->getParam('store'),
        ]);

        $fieldset->addField('name', 'text', [
            'label' => __('Title'),
            'name' => 'name',
            'value' => $department->getName(),
            'scope_label' => __('[STORE VIEW]'),
        ]);
        $fieldset->addField('is_active', 'select', [
            'label' => __('Is Active'),
            'name' => 'is_active',
            'value' => $department->getIsActive(),
            'values' => [0 => __('No'), 1 => __('Yes')],

        ]);
        $fieldset->addField('is_show_in_frontend', 'select', [
            'label' => __('Is Show in Frontend'),
            'name' => 'is_show_in_frontend',
            'value' => $department->getIsShowInFrontend(),
            'values' => [0 => __('No'), 1 => __('Yes')],
        ]);
        $fieldset->addField('sort_order', 'text', [
            'label' => __('Sort Order'),
            'name' => 'sort_order',
            'value' => $department->getSortOrder(),
        ]);
        $fieldset->addField('sender_email', 'text', [
            'label' => __('Sender Email'),
            'name' => 'sender_email',
            'value' => $department->getSenderEmail(),
        ]);
        $fieldset->addField('user_ids', 'multiselect', [
            'label' => __('Members of Department'),
            'required' => true,
            'name' => 'user_ids[]',
            'value' => $department->getUserIds(),
            'values' => $this->helpdeskHtml->toAdminUserOptionArray(),
        ]);
        $fieldset->addField('store_ids', 'multiselect', [
            'label' => __('Stores'),
            'required' => true,
            'name' => 'store_ids[]',
            'value' => $department->getStoreIds(),
            'values' => $this->systemStore->getStoreValuesForForm(false, true),
        ]);
        $fieldset = $form->addFieldset('notification_fieldset', ['legend' => __('Notification')]);
        $fieldset->addField('is_members_notification_enabled', 'select', [
            'label' => __('If ticket is unassigned, send notifications to all department members'),
            'name' => 'is_members_notification_enabled',
            'value' => $department->getIsMembersNotificationEnabled(),
            'values' => [0 => __('No'), 1 => __('Yes')],

        ]);
        $fieldset->addField('notification_email', 'text', [
            'label' => __('If ticket is unassigned, send notifications to email'),
            'name' => 'notification_email',
            'value' => $department->getNotificationEmail(),
            'scope_label' => __('[STORE VIEW]'),
        ]);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
    /************************/
}
