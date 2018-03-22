<?php
/**
 * @category   Webkul
 * @package    Webkul_MpTwilioSMSNotification
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */ 
namespace Webkul\MpTwilioSMSNotification\Helper;

use Magento\Customer\Model\Customer;

/**
 * Webkul MpTwilioSMSNotification Helper Data.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Magento\Customer\Model\Customer
     */
    protected $_customerModel;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\App\Helper\Context      $context
     * @param \Magento\Framework\ObjectManagerInterface  $objectManager
     * @param \Magento\Customer\Model\Session            $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Customer $customerModel,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_customerModel = $customerModel;
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;
        $this->_scopeConfig = $context->getScopeConfig();
        parent::__construct($context);
        $this->_storeManager = $storeManager;
    }

    /**
     * get Status of twilio
     * @return bool
     */
    public function getTwilioStatus()
    {
        return $this->_scopeConfig->getValue(
            'marketplace/twiliosettings/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * get Twilio account SID
     * @return String
     */
    public function getTwilioAccountSid()
    {
        return $this->_scopeConfig->getValue(
            'marketplace/twiliosettings/accountsid',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * get Twilio Auth Token
     * @return string
     */
    public function getTwilioAuthToken()
    {
        return $this->_scopeConfig->getValue(
            'marketplace/twiliosettings/authtoken',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * get twilio phone number
     * @return int
     */
    public function getTwilioPhoneNumber()
    {
        return $this->_scopeConfig->getValue(
            'marketplace/twiliosettings/twiliophonenumber',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get website base url
     * @return string
     */
    public function getSiteUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }
    /**
     * return customer data
     * @param  int $sellerId contain seller id
     * @return object
     */
    public function getCustomer($sellerId)
    {
        return $this->_customerModel->load($sellerId);
    }
}
