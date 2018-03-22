<?php
/**
 * @category   Webkul
 * @package    Webkul_MpTwilioSMSNotification
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */ 
namespace Webkul\MpTwilioSMSNotification\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\MpTwilioSMSNotification\Helper\Data;
use Webkul\Marketplace\Helper\Data as MpHelper;
use Magento\Framework\Message\ManagerInterface;
use Magento\Catalog\Model\Product;

/**
 * Webkul MpTwilioSMSNotification MpApproveSellerObserver Observer.
 */
class MpApproveSellerObserver implements ObserverInterface
{

    /**
     * @var Webkul\MpTwilioSMSNotification\Helper\Data
     */
    protected $_helperData;
    /**
     * @var Webkul\Marketplace\Helper\Data
     */
    protected $_helperMarketplace;
    /**
     * @var Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @param Data                                        $helperData
     * @param MpHelper                                    $helperMarketplace
     * @param ManagerInterface                            $messageManager
     */
    public function __construct(
        Data $helperData,
        MpHelper $helperMarketplace,
        ManagerInterface $messageManager
    ) {
        $this->_messageManager = $messageManager;
        $this->_helperMarketplace = $helperMarketplace;
        $this->_helperData = $helperData;
    }

    /**
     * mp_approve_seller event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_helperData->getTwilioStatus()) {
            $customer = $observer->getSeller();
            $sellerId = $observer->getSeller()->getId();
            $accountSid = $this->_helperData->getTwilioAccountSid();
            $authToken = $this->_helperData->getTwilioAuthToken();
            $twilioPhoneNumber = $this->_helperData
                                ->getTwilioPhoneNumber();
            $client = new \Twilio\Rest\Client($accountSid, $authToken);
            $mpSellerCollection = $this->_helperMarketplace
                                ->getSellerDataBySellerId($sellerId);
            foreach ($mpSellerCollection as $mpSeller) {
                $sellerName=$customer->getFirstname();
                $toNumber = str_replace(
                    " ",
                    "",
                    $mpSeller->getContactNumber()
                );
                $fromNumber = $twilioPhoneNumber;
                $body = __(
                    "Hi %1, your seller account approved by admin on %2",
                    $sellerName,
                    $this->_helperData->getSiteUrl()
                );
                try {

                        $client->account->messages->create(
                            $toNumber,

                            [
                                'from' => $fromNumber, 
                                'body' => $body
                            ]
                        );
                } catch (\Exception $e) {
                        $this->_messageManager->addError($e->getMessage());
                }
            }
        }
    }
}
