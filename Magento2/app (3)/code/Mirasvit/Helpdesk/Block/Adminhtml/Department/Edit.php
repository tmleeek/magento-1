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



namespace Mirasvit\Helpdesk\Block\Adminhtml\Department;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $wysiwygConfig;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Block\Widget\Context
     */
    protected $context;

    /**
     * @param \Magento\Cms\Model\Wysiwyg\Config     $wysiwygConfig
     * @param \Magento\Framework\Registry           $registry
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array                                 $data
     */
    public function __construct(
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->wysiwygConfig = $wysiwygConfig;
        $this->registry = $registry;
        $this->context = $context;
        parent::__construct($context, $data);
    }

    /**
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_objectId = 'department_id';
        $this->_controller = 'adminhtml_department';
        $this->_blockGroup = 'Mirasvit_Helpdesk';

        $this->buttonList->update('save', 'label', __('Save'));
        $this->buttonList->update('delete', 'label', __('Delete'));

        $this->buttonList->add('saveandcontinue', [
            'label' => __('Save And Continue Edit'),
            'class' => 'save',
            'data_attribute' => [
                'mage-init' => [
                    'button' => [
                        'event' => 'saveAndContinueEdit',
                        'target' => '#edit_form',
                        'eventData' => ['action' => ['args' => ['auto_apply' => 1]]],
                    ],
                ],
            ],
        ], -100);
    }

    /**
     *
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->wysiwygConfig->isEnabled()) {
        }
    }

    /**
     * @return \Mirasvit\Helpdesk\Model\Department
     */
    public function getDepartment()
    {
        if ($this->registry->registry('current_department')
            && $this->registry->registry('current_department')->getId()) {
            return $this->registry->registry('current_department');
        }
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getHeaderText()
    {
        if ($department = $this->getDepartment()) {
            return __("Edit Department '%1'", $this->escapeHtml($department->getName()));
        } else {
            return __('Create New Department');
        }
    }

    /************************/
}
