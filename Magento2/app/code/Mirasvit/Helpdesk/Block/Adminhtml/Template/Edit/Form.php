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



namespace Mirasvit\Helpdesk\Block\Adminhtml\Template\Edit;

class Form extends \Magento\Backend\Block\Widget\Form
{
    public function __construct(
        \Mirasvit\Helpdesk\Model\Config $config,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Store\Model\ResourceModel\Store\CollectionFactory $storeCollectionFactory,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->config                 = $config;
        $this->wysiwygConfig          = $wysiwygConfig;
        $this->storeCollectionFactory = $storeCollectionFactory;
        $this->formFactory            = $formFactory;
        $this->registry               = $registry;
        $this->context                = $context;

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

        /** @var \Mirasvit\Helpdesk\Model\Template $template */
        $template = $this->registry->registry('current_template');

        $fieldset = $form->addFieldset('edit_fieldset', ['legend' => __('General Information')]);
        if ($template->getId()) {
            $fieldset->addField('template_id', 'hidden', [
                'name' => 'template_id',
                'value' => $template->getId(),
            ]);
        }
        $fieldset->addField('name', 'text', [
            'label' => __('Internal Title'),
            'name' => 'name',
            'value' => $template->getName(),
        ]);
        $fieldset->addField('is_active', 'select', [
            'label' => __('Is Active'),
            'name' => 'is_active',
            'value' => $template->getIsActive(),
            'values' => [0 => __('No'), 1 => __('Yes')],
        ]);
        if ($this->config->getGeneralIsWysiwyg()) {
            $fieldset->addField('template', 'editor', [
                'label'   => __('Template'),
                'name'    => 'template',
                'value'   => $template->getTemplate(),
                'wysiwyg' => true,
                'config'  => $this->wysiwygConfig->getConfig(),
                'style'   => 'height:25em;width: 500px',
                'note'    => $this->getNotes(),
            ]);
        } else {
            $fieldset->addField('template', 'textarea', [
                'label' => __('Template'),
                'name'  => 'template',
                'value' => $template->getTemplate(),
                'style' => 'height:25em;width: 500px',
                'note'  => $this->getNotes(),
            ]);
        }

        $fieldset->addField('store_ids', 'multiselect', [
            'label' => __('Stores'),
            'required' => true,
            'name' => 'store_ids[]',
            'value' => $template->getStoreIds(),
            'values' => $this->storeCollectionFactory->create()->toOptionArray(),
        ]);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return string
     */
    private function getNotes()
    {
        return 'You can use variables:
                     [ticket_customer_name],
                     [ticket_customer_email],
                     [ticket_code],
                     [store_name],
                     [user_firstname],
                     [user_lastname],
                     [user_email]';
    }
    /************************/
}
