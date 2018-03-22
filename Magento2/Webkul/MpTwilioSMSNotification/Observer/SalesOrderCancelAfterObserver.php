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
use Magento\Customer\Model\Customer;
use Webkul\Marketplace\Model\Orders;

/**
 * Webkul MpTwilioSMSNotification SalesOrderCancelAfterObserver Observer.
 */
class SalesOrderCancelAfterObserver implements ObserverInterface
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
     * @var Magento\Catalog\Model\Product
     */
    protected $_productModel;
    /**
     * @var Magento\Customer\Model\Customer
     */
    protected $_customerModel;
    /**
     * @var Webkul\Marketplace\Model\Orders
     */
    protected $_orderModelMp;

    /**
     * @param Data             $helperData
     * @param Customer         $customerModel
     * @param Orders           $orderModelMp
     * @param MpHelper         $helperMarketplace
     * @param Product          $productModel
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Data $helperData,
        Customer $customerModel,
        Orders $orderModelMp,
        MpHelper $helperMarketplace,
        Product $productModel,
        ManagerInterface $messageManager
    ) {
        $this->_orderModelMp = $orderModelMp;
        $this->_customerModel = $customerModel;
        $this->_productModel = $productModel;
        $this->_messageManager = $messageManager;
        $this->_helperMarketplace = $helperMarketplace;
        $this->_helperData = $helperData;
    }

    /**
     * order_cancel_after event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_helperData->getTwilioStatus()) {
            $accountSid = $this->_helperData->getTwilioAccountSid();
            $authToken = $this->_helperData->getTwilioAuthToken();
            $twilioPhoneNumber = $this->_helperData
                                ->getTwilioPhoneNumber();
            $fromNumber = $twilioPhoneNumber;
            $client = new \Twilio\Rest\Client($accountSid, $authToken);
            $order = $observer->getEvent()->getOrder();
            $canceledOrderId = $order->getEntityId();
            $sellerOrder = $this->_orderModelMp->getCollection()
                        ->addFieldToFilter(
                            'order_id',
                            $canceledOrderId
                        );
            foreach ($sellerOrder as $info) {
                if ($info->getSellerId()!=0) {
                    $userData = $this->_helperData
                                ->getCustomer($info->getSellerId());
                    $mpSellerCollection = $this->_helperMarketplace
                                        ->getSellerDataBySellerId(
                                            $info->getSellerId()
                                        );
                    foreach ($mpSellerCollection as $sellerData) {
                        $sellerName = $userData->getFirstname();
                        $toNumber = str_replace(
                            " ",
                            "",
                            $sellerData->getContactNumber()
                        );
                        $content = __(
                            "Hi %1, the Order has been canceled,please check your mail for more details at %2",
                            $sellerName,
                            $userData->getEmail()
                        );
                        try {

                            $client->account->messages->create(
                                $toNumber,

                                [
                                    'from' => $fromNumber, 
                                    'body' => $content
                                ]
                            );
                        } catch (\Exception $e) {
                            $this->_messageManager->addError($e->getMessage());
                        }
                        break;
                    }
                }
            }
        }
    }
}
