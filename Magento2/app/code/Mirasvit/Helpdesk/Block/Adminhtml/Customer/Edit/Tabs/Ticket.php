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



namespace Mirasvit\Helpdesk\Block\Adminhtml\Customer\Edit\Tabs;

class Ticket extends \Magento\Backend\Block\Widget implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Block\Widget\Context
     */
    protected $context;

    /**
     * @param \Magento\Framework\Registry           $registry
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array                                 $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->context = $context;
        parent::__construct($context, $data);
    }

    /**
     * @var \Mirasvit\Helpdesk\Block\Adminhtml\Ticket\Grid $grid
     */
    protected $grid;

    /**
     * @var string
     */
    protected $gridHtml;

    /**
     * @return void
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        $customer = $this->registry->registry('current_customer');
        if (!$this->getId() || !$customer) {
            return;
        }
        $id = $this->getId();
        $grid = $this->getLayout()->createBlock('\Mirasvit\Helpdesk\Block\Adminhtml\Ticket\Grid');
        // $grid->addCustomFilter('customer_id', $id);
        $grid->addCustomFilter('customer_email = "'.addslashes($customer->getEmail()).'" OR customer_id='.(int) $id);
        $grid->setId('helpdesk_grid_customer');
        $grid->setFilterVisibility(false);
        $grid->setPagerVisibility(0);
        $grid->setTabMode(true);
        $grid->setActiveTab('tickets');
        $this->grid = $grid;
        $this->gridHtml = $this->grid->toHtml();

        return parent::_prepareLayout();
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabLabel()
    {
        return __('Help Desk Tickets (%1)', $this->grid->getFormattedNumberOfTickets());
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabTitle()
    {
        return __('Help Desk Tickets');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return $this->getId() ? true : false;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->getRequest()->getParam('id');
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return string
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _toHtml()
    {
        $customer = $this->registry->registry('current_customer');
        if (!$this->getId() || !$customer) {
            return '';
        }
        $id = $this->getId();
        $ticketNewUrl = $this->getUrl('helpdesk/ticket/add', ['customer_id' => $id]);

        $button = $this->getLayout()->createBlock('\Magento\Backend\Block\Widget\Button')
            ->setClass('add')
            ->setType('button')
            ->setOnClick('window.location.href=\''.$ticketNewUrl.'\'')
            ->setLabel(__('Create ticket for this customer'));

        return '<div>'.$button->toHtml().'<br><br>'.$this->gridHtml.'</div>';
    }
}
