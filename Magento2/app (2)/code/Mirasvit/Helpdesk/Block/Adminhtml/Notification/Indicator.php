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



namespace Mirasvit\Helpdesk\Block\Adminhtml\Notification;

class Indicator extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var \Magento\Backend\Model\Auth
     */
    protected $auth;

    /**
     * @var \Mirasvit\Helpdesk\Helper\DesktopNotification
     */
    protected $desktopNotificationHelper;

    /**
     * @var \Mirasvit\Helpdesk\Model\Config
     */
    protected $config;

    /**
     * @param \Magento\Backend\Model\Auth                   $auth
     * @param \Mirasvit\Helpdesk\Helper\DesktopNotification $desktopNotificationHelper
     * @param \Mirasvit\Helpdesk\Model\Config               $config
     * @param \Magento\Backend\Block\Widget\Context         $context
     * @param array                                         $data
     */
    public function __construct(
        \Magento\Backend\Model\Auth $auth,
        \Mirasvit\Helpdesk\Helper\DesktopNotification $desktopNotificationHelper,
        \Mirasvit\Helpdesk\Model\Config $config,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->config = $config;
        $this->auth = $auth;
        $this->desktopNotificationHelper = $desktopNotificationHelper;

        parent::__construct($context, $data);
    }

    /**
     * @return \Mirasvit\Helpdesk\Model\Config
     */
    protected function getConfig()
    {
        return $this->config;
    }

    /**
     * @return int
     */
    public function getNotificationInterval()
    {
        return $this->getConfig()->getDesktopNotificationCheckPeriod();
    }

    /**
     * @return string
     */
    public function getCheckNotificationUrl()
    {
        return $this->getUrl('helpdesk/ticket/checknotification');
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getConfig()->getDesktopNotificationIsActive()) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * @return int
     */
    public function getNewTicketsNumber()
    {
        return $this->desktopNotificationHelper->getNewTicketsNumber();
    }

    /**
     * @return int
     */
    public function getUserMessagesNumber()
    {
        return $this->desktopNotificationHelper->getUserMessagesNumber($this->auth->getUser());
    }
}
