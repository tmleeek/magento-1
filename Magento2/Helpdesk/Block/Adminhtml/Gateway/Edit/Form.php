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



namespace Mirasvit\Helpdesk\Block\Adminhtml\Gateway\Edit;

class Form extends \Magento\Backend\Block\Widget\Form
{
    /**
     * @var \Mirasvit\Helpdesk\Model\ResourceModel\Department\CollectionFactory
     */
    protected $departmentCollectionFactory;

    /**
     * @var \Mirasvit\Helpdesk\Model\Config\Source\Protocol
     */
    protected $configSourceProtocol;

    /**
     * @var \Mirasvit\Helpdesk\Model\Config\Source\Encryption
     */
    protected $configSourceEncryption;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

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
     * @param \Mirasvit\Helpdesk\Model\Config\Source\Protocol                     $configSourceProtocol
     * @param \Mirasvit\Helpdesk\Model\Config\Source\Encryption                   $configSourceEncryption
     * @param \Magento\Store\Model\System\Store                                   $systemStore
     * @param \Magento\Framework\Data\FormFactory                                 $formFactory
     * @param \Magento\Framework\Registry                                         $registry
     * @param \Magento\Backend\Block\Widget\Context                               $context
     * @param array                                                               $data
     */
    public function __construct(
        \Mirasvit\Helpdesk\Model\ResourceModel\Department\CollectionFactory $departmentCollectionFactory,
        \Mirasvit\Helpdesk\Model\Config\Source\Protocol $configSourceProtocol,
        \Mirasvit\Helpdesk\Model\Config\Source\Encryption $configSourceEncryption,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->departmentCollectionFactory = $departmentCollectionFactory;
        $this->configSourceProtocol = $configSourceProtocol;
        $this->configSourceEncryption = $configSourceEncryption;
        $this->systemStore = $systemStore;
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->context = $context;
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     *
     * @throw \Magento\Framework\Exception\LocalizedException
     *  @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $form = $this->formFactory->create()->setData(
            [
                'id'      => 'edit_form',
                'action'  => $this->getUrl('*/*/save', ['id' => $this->getRequest()->getParam('id')]),
                'method'  => 'post',
                'enctype' => 'multipart/form-data',
            ]
        );

        /** @var \Mirasvit\Helpdesk\Model\Gateway $gateway */
        $gateway = $this->registry->registry('current_gateway');

        $fieldset = $form->addFieldset('edit_fieldset', ['legend' => __('General Information')]);
        if ($gateway->getId()) {
            $fieldset->addField('gateway_id', 'hidden', [
                'name'  => 'gateway_id',
                'value' => $gateway->getId(),
            ]);
        }
        $fieldset->addField('name', 'text', [
            'label' => __('Title'),
            'name'  => 'name',
            'value' => $gateway->getName(),
        ]);
        $fieldset->addField('email', 'text', [
            'label' => __('Email'),
            'name'  => 'email',
            'value' => $gateway->getEmail(),
        ]);
        $fieldset->addField('login', 'text', [
            'label' => __('Login'),
            'name'  => 'login',
            'value' => $gateway->getLogin(),
        ]);
        $fieldset->addField('password', 'password', [
            'label' => __('Password'),
            'name'  => 'password',
            'value' => '*****',
            'class' => 'admin__control-text',
        ]);
        $fieldset->addField('is_active', 'select', [
            'label'  => __('Is Active'),
            'name'   => 'is_active',
            'value'  => $gateway->getIsActive(),
            'values' => [0 => __('No'), 1 => __('Yes')],

        ]);
        $fieldset->addField('host', 'text', [
            'label' => __('Host'),
            'name'  => 'host',
            'value' => $gateway->getHost(),
        ]);
        $fieldset->addField('folder', 'text', [
            'label' => __('Folder'),
            'name'  => 'folder',
            'value' => $gateway->getFolder(),
        ]);
        $fieldset->addField('protocol', 'select', [
            'label'  => __('Protocol'),
            'name'   => 'protocol',
            'value'  => $gateway->getProtocol(),
            'values' => $this->configSourceProtocol->toOptionArray(),

        ]);
        $fieldset->addField('encryption', 'select', [
            'label'  => __('Encryption'),
            'name'   => 'encryption',
            'value'  => $gateway->getEncryption(),
            'values' => $this->configSourceEncryption->toOptionArray(),

        ]);
        $fieldset->addField('port', 'text', [
            'label' => __('Port'),
            'name'  => 'port',
            'value' => $gateway->getPort(),
        ]);
        $fieldset->addField('fetch_frequency', 'text', [
            'label' => __('Fetch Frequency (minutes)'),
            'name'  => 'fetch_frequency',
            'value' => $gateway->getFetchFrequency() ? $gateway->getFetchFrequency() : 5,
        ]);
        $fieldset->addField('fetch_max', 'text', [
            'label' => __('Fetch Max'),
            'name'  => 'fetch_max',
            'value' => $gateway->getFetchMax() ? $gateway->getFetchMax() : 10,
        ]);
        $fieldset->addField('fetch_limit', 'text', [
            'label' => __('Fetch Only X Last Emails'),
            'name'  => 'fetch_limit',
            'value' => $gateway->getFetchLimit() ? $gateway->getFetchLimit() : '',
            'note'  => __('Can be useful for some mailboxes. Leave empty to disable this feature.'),
        ]);
        $fieldset->addField('is_delete_emails', 'select', [
            'label'  => __('Remove emails after fetching'),
            'name'   => 'is_delete_emails',
            'value'  => $gateway->getIsDeleteEmails(),
            'values' => [0 => __('No'), 1 => __('Yes')],
        ]);
        if (!$this->context->getStoreManager()->isSingleStoreMode()) {
            $fieldset->addField('store_id', 'select', [
                'name'     => 'store_id',
                'label'    => __('Auto assign tickets to Store View'),
                'title'    => __('Auto assign tickets to Store View'),
                'required' => true,
                'values'   => $this->systemStore->getStoreValuesForForm(false, false),
                'value'    => $gateway->getStoreId(),
            ]);
        } else {
            $fieldset->addField('store_id', 'hidden', [
                'name'  => 'store_id',
                'value' => $this->context->getStoreManager()->getStore(true)->getId(),
            ]);
            $gateway->setStoreId($this->context->getStoreManager()->getStore(true)->getId());
        }
        $fieldset->addField('department_id', 'select', [
            'label'  => __('Auto assign tickets to department'),
            'name'   => 'department_id',
            'value'  => $gateway->getDepartmentId(),
            'values' => $this->departmentCollectionFactory->create()->toOptionArray(),

        ]);
        $fieldset->addField('notes', 'textarea', [
            'label' => __('Notes'),
            'name'  => 'notes',
            'value' => $gateway->getNotes(),
        ]);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
    /************************/
}
