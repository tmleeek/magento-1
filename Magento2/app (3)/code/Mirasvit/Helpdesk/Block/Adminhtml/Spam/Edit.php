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



namespace Mirasvit\Helpdesk\Block\Adminhtml\Spam;

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
     * @return $this
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_objectId = 'ticket_id';
        $this->_controller = 'adminhtml_spam';
        $this->_blockGroup = 'Mirasvit_Helpdesk';

        $this->buttonList->remove('save');
        $this->buttonList->remove('delete');
        $this->buttonList->remove('reset');

        $this->buttonList->add('approve', [
            'label' => __('Approve'),
            'onclick' => 'setLocation(\''.$this->getApproveUrl().'\')',
        ]);

        return $this;
    }

    /**
     * @return string
     */
    public function getApproveUrl()
    {
        return $this->getUrl('*/*/approve', ['id' => $this->getModel()->getId()]);
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
     * @return object
     */
    public function getModel()
    {
        if ($this->registry->registry('current_ticket') && $this->registry->registry('current_ticket')->getId()) {
            return $this->registry->registry('current_ticket');
        }
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getHeaderText()
    {
        return __($this->escapeHtml($this->registry->registry('current_ticket')->getName()));
    }

    /**
     * @return string
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _toHtml()
    {
        $messages = $this->getLayout()->createBlock(
            '\Mirasvit\Helpdesk\Block\Adminhtml\Ticket\Edit\Tab\Messages'
        )->toHtml();

        return parent::_toHtml().$messages;
    }
}
