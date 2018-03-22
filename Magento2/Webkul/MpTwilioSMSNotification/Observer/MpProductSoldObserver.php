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

/**
 * Webkul MpTwilioSMSNotification MpProductSoldObserver Observer.
 */
class MpProductSoldObserver implements ObserverInterface
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
     * @param Data             $helperData
     * @param Customer         $customerModel
     * @param MpHelper         $helperMarketplace
     * @param Product          $productModel
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Data $helperData,
        Customer $customerModel,
        MpHelper $helperMarketplace,
        Product $productModel,
        ManagerInterface $messageManager
    ) {
        $this->_customerModel = $customerModel;
        $this->_productModel = $productModel;
        $this->_messageManager = $messageManager;
        $this->_helperMarketplace = $helperMarketplace;
        $this->_helperData = $helperData;
    }

    /**
     * mp_product_sold event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_helperData->getTwilioStatus()) {
            $sellerWholeData = $observer->getItemwithseller();
            $accountSid = $this->_helperData->getTwilioAccountSid();
            $authToken = $this->_helperData->getTwilioAuthToken();
            $twilioPhoneNumber = $this->_helperData
                                ->getTwilioPhoneNumber();
            $fromNumber = $twilioPhoneNumber;
          
            $client = new \Twilio\Rest\Client($accountSid, $authToken);
            foreach ($sellerWholeData as $sellerId => $orderDetails) {
                foreach ($orderDetails as $order) {
                    $invoiceObject = $order['invoice'];
                    $orderData = $invoiceObject->getOrder();
                    $mpSellerCollection = $this->_helperMarketplace
                                        ->getSellerDataBySellerId($sellerId);
                    foreach ($mpSellerCollection as $sellerData) {
                        $sellerName = $this->_helperData
                                ->getCustomer($sellerId)->getFirstname();
                        $toNumber = str_replace(
                            " ",
                            "",
                            $sellerData->getContactNumber()
                        );
                        $body = __(
                            "Hi %1, The Invoice of the products '%2' has been generated. The order id is %3",
                            $sellerName,
                            substr($order['name'], 0, 9),
                            $orderData->getIncrementId()
                        );
                        $content = __(
                            "Hi %1, %2 Quantity of your product %3 has been sold from your store %4 .The order id is %5",
                            $sellerName,
                            (int)$order['qty'],
                            substr($order['name'], 0, 9),
                            substr(
                                $this->_helperData->getSiteUrl(),
                                0,
                                25
                            ),
                            $orderData->getIncrementId()
                        );
                        try {
                            $client->account->messages->create(
                                $toNumber,

                                [
                                    'from' => $fromNumber, 
                                    'body' => $content
                                ]
                            );

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
                        break;
                    }
                }
            }
        }
    }
}
