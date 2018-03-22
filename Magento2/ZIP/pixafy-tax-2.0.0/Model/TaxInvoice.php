<?php
/**
 * Created by PhpStorm.
 * User: EETIENNE
 * Date: 9/6/2016
 * Time: 3:18 PM
 */

namespace VertexSMB\Tax\Model;

class TaxInvoice
{

    /**
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     *
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     *
     * @var \VertexSMB\Tax\Helper\Data
     */
    protected $vertexSMBHelper;

    /**
     *
     * @var \VertexSMB\Tax\Model\VertexSMB
     */
    protected $vertexSMB;

    /**
     *
     * @var \VertexSMB\Tax\Helper\Config
     */
    protected $vertexSMBConfigHelper;

    /**
     *
     * @var string
     */
    protected $requestTypeInvoice = 'invoice';

    /**
     *
     * @var string
     */
    protected $requestTypeInvoiceRefund = 'invoice_refund';

    /**
     *
     * @var \VertexSMB\Tax\Helper\Request\Customer
     */
    protected $vertexSMBCustomerHelper;

    /**
     *
     * @var \VertexSMB\Tax\Model\RequestItem
     */
    protected $vertexSMBRequestItemHelper;

    /**
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;



    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \VertexSMB\Tax\Helper\Data $vertexSMBHelper,
        \VertexSMB\Tax\Model\VertexSMB $vertexSMB,
        \VertexSMB\Tax\Helper\Config $vertexConfigHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \VertexSMB\Tax\Helper\Request\Customer $vertexSMBCustomerHelper,
        \VertexSMB\Tax\Model\RequestItem $vertexSMBRequestItemHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->logger = $logger;
        $this->objectManager = $objectManager;
        $this->vertexSMBHelper = $vertexSMBHelper;
        $this->vertexSMB = $vertexSMB;
        $this->vertexSMBConfigHelper = $vertexConfigHelper;
        $this->messageManager = $messageManager;
        $this->vertexSMBCustomerHelper = $vertexSMBCustomerHelper;
        $this->vertexSMBRequestItemHelper = $vertexSMBRequestItemHelper;
        $this->dateTime = $dateTime;
        $this->moduleManager = $moduleManager;
        //parent::__construct($data);
    }



    /**
     * @param unknown $entityItem
     * @param string  $event
     * @return unknown
     */
    public function prepareInvoiceData($entityItem, $event = null)
    {
        try {
            $info = [];
            $info = $this->vertexSMBHelper->addSellerInformation($info);
            $order = $entityItem;
            $typeId = 'ordered';
            if ($entityItem instanceof \Magento\Sales\Model\Order\Invoice || $entityItem instanceof \Magento\Sales\Model\Order\Creditmemo) {
                $order = $entityItem->getOrder();
                $typeId = 'invoiced';
            }
            $info['order_id'] = $order->getIncrementId();
            $info['document_number'] = $order->getIncrementId();
            $info['document_date'] = date("Y-m-d", strtotime($order->getCreatedAt()));
            $info['posting_date'] = date("Y-m-d", $this->dateTime->timestamp(time()));

            $customerClass = $this->vertexSMBCustomerHelper->taxClassNameByCustomerGroupId($order->getCustomerGroupId());
            $customerCode = $this->vertexSMBCustomerHelper->getCustomerCodeById($order->getCustomerId());

            $info['customer_class'] = $customerClass;
            $info['customer_code'] = $customerCode;

            if ($order->getIsVirtual()) {
                $address = $order->getBillingAddress();
            } else {
                $address = $order->getShippingAddress();
            }

            $info = $this->vertexSMBHelper->addAddressInformation($info, $address);

            $orderItems = [];
            $orderedItems = $entityItem->getAllItems();
            $giftWrappingEnabled = $this->moduleManager->isEnabled('Magento_GiftWrapping');
            foreach ($orderedItems as $item) {
                if ($item->getParentItem()) {
                    continue;
                }
                $this-> _getOrderItem($entityItem, $typeId, $event, $giftWrappingEnabled, $item, $orderItems);
            }

            $shippingInfo = $this->vertexSMBHelper->addShippingInfo($order, $entityItem, $event);
            if (! $order->getIsVirtual() && count($shippingInfo)) {
                $orderItems[] = $shippingInfo;
            }

            if ($entityItem instanceof \Magento\Sales\Model\Order\Creditmemo) {
                $orderItems = $this->vertexSMBHelper->addRefundAdjustments($orderItems, $entityItem);
            }

            if ($giftWrappingEnabled) {
                if (count($this->vertexSMBHelper->addOrderGiftWrap($order, $entityItem, $event))) {
                    $orderItems[] = $this->vertexSMBHelper->addOrderGiftWrap($order, $entityItem, $event);
                }
                if (count($this->vertexSMBHelper->addOrderPrintCard($order, $entityItem, $event))) {
                    $orderItems[] = $this->vertexSMBHelper->addOrderPrintCard($order, $entityItem, $event);
                }
            }

            $info['request_type'] = 'InvoiceRequest';
            $info['order_items'] = $orderItems;
            $request = $this->vertexSMBRequestItemHelper->setData($info)->exportAsArray();
        //var_dump($request);exit;
            return $request;
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }


    protected function _getOrderItem($entityItem, $typeId, $event, $giftWrappingEnabled, $item, &$orderItems)
    {
         $originalItem = $item;
        if ($entityItem instanceof \Magento\Sales\Model\Order\Invoice || $entityItem instanceof \Magento\Sales\Model\Order\Creditmemo) {
              $item = $item->getOrderItem();
        }

        if ($item->getHasChildren() && $item->getProduct()->getPriceType() !== null && (int) $item->getProduct()->getPriceType() === \Magento\Catalog\Model\Product\Type\AbstractType::CALCULATE_CHILD) {
            foreach ($item->getChildrenItems() as $child) {
                if ($entityItem instanceof \Magento\Sales\Model\Order\Invoice || $entityItem instanceof \Magento\Sales\Model\Order\Creditmemo) {
                    $orderItems[] = $this->vertexSMBHelper->prepareItem($child, $typeId, $originalItem, $event);
                } elseif ($entityItem instanceof \Magento\Sales\Model\Order) {
                    $orderItems[] = $this->vertexSMBHelper->prepareItem($child, $typeId, $originalItem, $event);
                }
                if ($giftWrappingEnabled && $child->getGwId()) {
                        $orderItems[] = $this->vertexSMBHelper->prepareGiftWrapItem($child, $typeId, $originalItem, $event, $entityItem->getStore());
                }
            }
        } else {
            $orderItems[] = $this->vertexSMBHelper->prepareItem($item, $typeId, $originalItem, $event);
            if ($giftWrappingEnabled && $item->getGwId()) {
                    $orderItems[] = $this->vertexSMBHelper->prepareGiftWrapItem($item, $typeId, $originalItem, $event, $entityItem->getStore());
            }
        }
    }

    /**
     * @param unknown $data
     * @param string  $order
     * @return boolean
     */
    public function sendInvoiceRequest($data, $order = null)
    {
        $requestResult = $this->vertexSMB->sendApiRequest($data, $this->requestTypeInvoice, $order);
        if ($requestResult instanceof \Exception) {
            $this->logger->error("Invoice Request Error: " . $requestResult->getMessage());
            $this->messageManager->addError($requestResult->getMessage());
            return false;
        }
        //var_dump($requestResult);exit;
        if (is_array($requestResult["TotalTax"])) {
            $totalTax = $requestResult["TotalTax"]["_"];
        } else {
            $totalTax = $requestResult["TotalTax"];
        }
        $order->addStatusHistoryComment('Vertex SMB Invoice sent successfully. Amount: $' . $totalTax, false)->save();
        return true;
    }

    /**
     * @param unknown $data
     * @param string  $order
     * @return boolean
     */
    public function sendRefundRequest($data, $order = null)
    {
        $requestResult = $this->vertexSMB->sendApiRequest($data, $this->requestTypeInvoiceRefund, $order);
        if ($requestResult instanceof \Exception) {
            $this->logger->error("Refund Request Error: " . $requestResult->getMessage());
            $this->messageManager->addError($requestResult->getMessage());
            return false;
        }
        if (is_array($requestResult["TotalTax"])) {
            $totalTax = $requestResult["TotalTax"]["_"];
        } else {
            $totalTax = $requestResult["TotalTax"];
        }
        $order->addStatusHistoryComment('Vertex SMB Invoice refunded successfully. Amount: $' . $totalTax, false)->save();
        return true;
    }
}
