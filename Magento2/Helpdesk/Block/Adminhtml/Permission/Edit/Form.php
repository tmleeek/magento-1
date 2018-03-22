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



namespace Mirasvit\Helpdesk\Block\Adminhtml\Permission\Edit;

class Form extends \Magento\Backend\Block\Widget\Form
{
    /**
     * @var \Mirasvit\Helpdesk\Model\ResourceModel\Department\CollectionFactory
     */
    protected $departmentCollectionFactory;

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
     * @param \Mirasvit\Helpdesk\Model\ResourceModel\Department\CollectionFactory $departmentCollectionFactory
     * @param \Mirasvit\Helpdesk\Helper\Html                                      $helpdeskHtml
     * @param \Magento\Framework\Data\FormFactory                                 $formFactory
     * @param \Magento\Framework\Registry                                         $registry
     * @param \Magento\Backend\Block\Widget\Context                               $context
     * @param array                                                               $data
     */
    public function __construct(
        \Mirasvit\Helpdesk\Model\ResourceModel\Department\CollectionFactory $departmentCollectionFactory,
        \Mirasvit\Helpdesk\Helper\Html $helpdeskHtml,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->departmentCollectionFactory = $departmentCollectionFactory;
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
                'action' => $this->getUrl('*/*/save', ['id' => $this->getRequest()->getParam('id')]),
                'method' => 'post',
                'enctype' => 'multipart/form-data',
            ]
        );

        /** @var \Mirasvit\Helpdesk\Model\Permission $permission */
        $permission = $this->registry->registry('current_permission');

        $fieldset = $form->addFieldset('edit_fieldset', ['legend' => __('General Information')]);
        if ($permission->getId()) {
            $fieldset->addField('permission_id', 'hidden', [
                'name' => 'permission_id',
                'value' => $permission->getId(),
            ]);
        }
        $values = $this->helpdeskHtml->toAdminRoleOptionArray(__('All Roles'));
        $fieldset->addField('role_id', 'select', [
            'label' => __('Role'),
            'name' => 'role_id',
            'value' => $permission->getRoleId(),
            'values' => $values,
        ]);

        $values = $this->departmentCollectionFactory->create()->toOptionArray();
        array_unshift($values, ['value' => 0, 'label' => __('All Departments')]);

        $fieldset->addField('department_ids', 'multiselect', [
            'label' => __('Allows access to tickets of departments'),
            'name' => 'department_ids[]',
            'value' => $permission->getDepartmentIds(),
            'values' => $values,
        ]);
        $fieldset->addField('is_ticket_remove_allowed', 'select', [
            'label' => __('Can Delete Tickets'),
            'name' => 'is_ticket_remove_allowed',
            'value' => $permission->getIsTicketRemoveAllowed(),
            'values' => [0 => __('No'), 1 => __('Yes')],
        ]);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
    /************************/
}
