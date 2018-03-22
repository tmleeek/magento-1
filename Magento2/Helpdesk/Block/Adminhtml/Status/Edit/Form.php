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



namespace Mirasvit\Helpdesk\Block\Adminhtml\Status\Edit;

class Form extends \Magento\Backend\Block\Widget\Form
{
    /**
     * @var \Mirasvit\Helpdesk\Model\Config\Source\Color
     */
    protected $configSourceColor;

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
     * @param \Mirasvit\Helpdesk\Model\Config\Source\Color $configSourceColor
     * @param \Magento\Store\Model\System\Store            $systemStore
     * @param \Magento\Framework\Data\FormFactory          $formFactory
     * @param \Magento\Framework\Registry                  $registry
     * @param \Magento\Backend\Block\Widget\Context        $context
     * @param array                                        $data
     */
    public function __construct(
        \Mirasvit\Helpdesk\Model\Config\Source\Color $configSourceColor,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->configSourceColor = $configSourceColor;
        $this->systemStore = $systemStore;
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
                'id'      => 'edit_form',
                'action'  => $this->getUrl('*/*/save', [
                    'id'    => $this->getRequest()->getParam('id'),
                    'store' => (int)$this->getRequest()->getParam('store'),
                ]),
                'method'  => 'post',
                'enctype' => 'multipart/form-data',
            ]
        );

        /** @var \Mirasvit\Helpdesk\Model\Status $status */
        $status = $this->registry->registry('current_status');

        $fieldset = $form->addFieldset('edit_fieldset', ['legend' => __('General Information')]);
        if ($status->getId()) {
            $fieldset->addField('status_id', 'hidden', [
                'name'  => 'status_id',
                'value' => $status->getId(),
            ]);
        }
        $fieldset->addField('store_id', 'hidden', [
            'name'  => 'store_id',
            'value' => (int)$this->getRequest()->getParam('store'),
        ]);

        $fieldset->addField('name', 'text', [
            'label'       => __('Title'),
            'required'    => true,
            'name'        => 'name',
            'value'       => $status->getName(),
            'scope_label' => __('[STORE VIEW]'),
        ]);
        $field = $fieldset->addField('code', 'text', [
            'label'    => __('Code'),
            'required' => true,
            'name'     => 'code',
            'value'    => $status->getCode(),
        ]);
        if ($status->getId()) {
            $field->setReadonly(true);
        }
        $fieldset->addField('sort_order', 'text', [
            'label' => __('Sort Order'),
            'name'  => 'sort_order',
            'value' => $status->getSortOrder(),
        ]);
        $element = $fieldset->addField('color', 'select', [
            'label'    => __('Color'),
            'name'     => 'color',
            'value'    => $status->getColor(),
            'values'   => $this->configSourceColor->toOptionArray(),
            'onchange' => "jQuery('#example')[0].className = '';jQuery('#example').addClass(this.value)",
        ]);
        $element->setAfterElementHtml(
            '
                <br><br><div class="status_id color">
                    <span id="example" class=" ' . $status->getColor() . '">Label example</span>
                </div>
            '
        );
        $fieldset->addField('store_ids', 'multiselect', [
            'label'    => __('Stores'),
            'required' => true,
            'name'     => 'store_ids[]',
            'value'    => $status->getStoreIds(),
            'values'   => $this->systemStore->getStoreValuesForForm(false, true),
        ]);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
    /************************/
}
