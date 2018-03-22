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



namespace Mirasvit\Helpdesk\Block\Email;

/**
 * @method $this setTicket(\Mirasvit\Helpdesk\Model\Ticket $ticket)
 * @method \Mirasvit\Helpdesk\Model\Ticket getTicket()
 */
class History extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Mirasvit\Helpdesk\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    protected $context;

    /**
     * @param \Mirasvit\Helpdesk\Model\Config                  $config
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array                                            $data
     */
    public function __construct(
        \Mirasvit\Helpdesk\Model\Config $config,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->config = $config;
        $this->context = $context;
        parent::__construct($context, $data);
    }

    /**
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setData('area', 'frontend');
    }

    /**
     * @return object
     */
    public function getLimit()
    {
        return $this->config->getNotificationHistoryRecordsNumber();
    }

    /**
     * @return \Mirasvit\Helpdesk\Model\Message[]|\Mirasvit\Helpdesk\Model\Resource\Message\Collection
     */
    public function getMessages()
    {
        $collection = $this->getTicket()->getMessages();
        $collection->getSelect()->limit($this->getLimit(), 1); //don't show first message
        return $collection;
    }
}
