<?php
/**
 * Copyright © 2016 Pixafy Services LLC. All rights reserved.
 *
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL 3.0)
 * @author  Alex Lukyanau <alukyanau@pixafy.com>
 */
namespace VertexSMB\Tax\Helper;

/**
 * Default helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const VERTEX_SHIPPING_LINE_ITEM_ID = "shipping";
    const VERTEX_LINE_ITEM_TAX_KEY = "vertex_item_tax";
    const VERTEX_QUOTE_ITEM_ID_PREFIX= "quote_item_id_";

    protected $vertexSMBConfigHelper;
 
    protected $objectManager;
  
    protected $addressHelper;

    protected $directoryRegion;

    protected $directoryCountry;

    protected $taxClassRepository;
    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    protected $context;
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;
    /**
     * @var \Magento\GiftWrapping\Model\WrappingFactory
     */
    protected $wrappingFactory;
    /**
     * @var \Magento\GiftWrapping\Helper\Data
     */
    protected $giftWrappingData;
    
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        Config $vertexConfigHelper,
        \VertexSMB\Tax\Helper\Request\Address $addressHelper,
        \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassRepository,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Directory\Model\Region $directoryRegion,
        \Magento\Directory\Model\Country $directoryCountry,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Checkout\Model\Cart $cart
    ) {
        $this->vertexSMBConfigHelper = $vertexConfigHelper;
        $this->objectManager = $objectManager;
        $this->addressHelper = $addressHelper;
        $this->taxClassRepository = $taxClassRepository;
        $this->directoryRegion = $directoryRegion;
        $this->directoryCountry = $directoryCountry;
        $this->context = $context;
        $this->priceCurrency = $priceCurrency;
        $this->moduleManager = $context->getModuleManager();
        if ($this->isGiftWrappingEnabled()) {
            $this->wrappingFactor = $this->objectManager->get('\Magento\GiftWrapping\Model\WrappingFactory');
            $this->giftWrappingData = $this->objectManager->get('\Magento\GiftWrapping\Helper\Data');
        }
        $this->cart = $cart;
        parent::__construct($context);
    }

    /**
     *
     * @return string
     */
    public function checkCredentials($store = null)
    {
        if (!$this->vertexSMBConfigHelper->getVertexHost($store)) {
            return "Not Valid: Missing Api Url";
        }
        
        if (!$this->vertexSMBConfigHelper->getVertexAddressHost($store)) {
            return "Not Valid: Missing Address Validation Api Url";
        }
        
        if (!$this->vertexSMBConfigHelper->getTrustedId($store)) {
            return "Not Valid: Missing Trusted Id";
        }
                  
        if (!$this->vertexSMBConfigHelper->getCompanyRegionId($store)) {
            return "Not Valid: Missing Company State";
        }
        
        if (!$this->vertexSMBConfigHelper->getCompanyCountry($store)) {
            return "Not Valid: Missing Company Country";
        }
        
        if (!$this->vertexSMBConfigHelper->getCompanyStreet1($store)) {
            return "Not Valid: Missing Company Street Address";
        }
        
        if (!$this->vertexSMBConfigHelper->getCompanyCity($store) || !$this->vertexSMBConfigHelper->getCompanyPostalCode($store)) {
            return "Not Valid: Missing Company City or Company Postal Code";
        }
        
          
        $address = $this->addressHelper->formatAddress(
            [$this->vertexSMBConfigHelper->getCompanyStreet1($store),
            $this->vertexSMBConfigHelper->getCompanyStreet2($store)],
            $this->vertexSMBConfigHelper->getCompanyCity($store),
            $this->vertexSMBConfigHelper->getCompanyRegionId($store),
            $this->vertexSMBConfigHelper->getCompanyPostalCode($store),
            $this->vertexSMBConfigHelper->getCompanyCountry($store)
        );
                
        if ($address['Country'] != 'USA') {
            return "Valid";
        }
        
        $requestResult = $this->objectManager->create('\VertexSMB\Tax\Model\TaxArea\TaxAreaRequest')->taxAreaLookup(
            $address
        );
        if ($requestResult instanceof \Exception || $requestResult === false) {
            return "Address Validation Error: Please check settings";
        }
        return "Valid";
    }

    /**
     *
     * @return mixed
     */
    public function getSession()
    {
        /* If not admin */
        //        echo $this->_request->getControllerName()."\n";
        if ($this->_request->getControllerName() != 'order_create') {
            return $this->objectManager->get('\Magento\Checkout\Model\Session');
        } else {
            return $this->objectManager->get('\Magento\Backend\Model\Session\Quote');
        }
    }

    /**
     *
     * @return \Magento\Quote\Model\Quote\Address
     */
    public function getTaxAddress($quote = null)
    {
        $quote = !is_null($quote) ? $quote : $this->getSession()->getQuote();
        if ($quote->getBillingAddress() && $quote->isVirtual()) {
            $address = $quote->getBillingAddress();
        } elseif ($quote->getShippingAddress()) {
            $address = $quote->getShippingAddress();
        }
        return $address;
    }

    /**
     * @return mix
     */
    public function maxProductCodeOffset($code)
    {
        return substr($code, 0, $this->vertexSMBConfigHelper->maxAllowedProductCode());
    }

    /**
     *
     * @todo check if used where supose to be used
     * @todo cart page - not working
     * @todo checkout page - ?
     * @todo admin order - ? Action path used for log
     * @return string|unknown
     */
    public function getSourcePath()
    {
        $controller = $this->_request->getControllerName();
        $module = $this->_request->getModuleName();
        $action = $this->_request->getActionName();
        $source_path = "";
        
        if ($controller) {
            $source_path .= $controller;
        }
        if ($module) {
            $source_path .= "_" . $module;
        }
        if ($action) {
            $source_path .= "_" . $action;
        }
        
        if (!$source_path) {
            $source_path=$this->_request->getPathInfo();
        }
        return $source_path;
    }
    
    /**
     *
     * @param int $classId
     * @return string
     */
    public function taxClassNameById($classId)
    {
        $taxclassName = "None";
        if ($classId) {
            $taxclassName = $this->taxClassRepository->get($classId)->getClassName();
        }
        return $taxclassName;
    }

    /**
     * @param unknown $status
     * @return boolean
     */
    public function requestByOrderStatus($status, $store = null)
    {
        $vertexInvoiceEvent = $this->vertexSMBConfigHelper->getConfigValue(Config::CONFIG_XML_PATH_VERTEX_INVOICE_ORDER, $store);
        $vertexInvoiceOrderStatus = $this->vertexSMBConfigHelper->getConfigValue(Config::CONFIG_XML_PATH_VERTEX_INVOICE_ORDER_STATUS, $store);
        if ($vertexInvoiceEvent == 'order_status' && $vertexInvoiceOrderStatus == $status) {
            return true;
        }
        return false;
    }

    /**
     * Company Information
     *
     * @param  [] $data
     * @return unknown
     */
    public function addSellerInformation($data, $store = null)
    {
        $regionId = $this->vertexSMBConfigHelper->getCompanyRegionId($store);
        if (is_int($regionId)) {
            $regionModel = $this->directoryRegion->load($regionId);
            $companyState = $regionModel->getCode();
        } else {
            $companyState = $regionId;
        }
        $countryModel = $this->directoryCountry->load($this->vertexSMBConfigHelper->getCompanyCountry($store));
        $countryName = $countryModel->getData("iso3_code");
        $data['location_code'] = $this->vertexSMBConfigHelper->getLocationCode($store);
        $data['transaction_type'] = $this->vertexSMBConfigHelper->getTransactionType($store);
        $data['company_id'] = $this->vertexSMBConfigHelper->getCompanyCode($store);
        $data['company_street_1'] = $this->vertexSMBConfigHelper->getCompanyStreet1($store);
        $data['company_street_2'] = $this->vertexSMBConfigHelper->getCompanyStreet2($store);
        $data['company_city'] = $this->vertexSMBConfigHelper->getCompanyCity($store);
        $data['company_state'] = $companyState;
        $data['company_postcode'] = $this->vertexSMBConfigHelper->getCompanyPostalCode($store);
        $data['company_country'] = $countryName;
        $data['trusted_id'] = $this->vertexSMBConfigHelper->getTrustedId($store);
        return $data;
    }

    /**
     *
     * @param array   $data
     * @param unknown $address
     * @return unknown
     */
    public function addAddressInformation($data, $address)
    {
        $data['customer_street1'] = $address->getStreet1();
        $data['customer_street2'] = $address->getStreet2();
        $data['customer_city'] = $address->getCity();
        $data['customer_region'] = $address->getRegionCode();
        $data['customer_postcode'] = $address->getPostcode();
        $countryModel = $this->directoryCountry->load($address->getCountryId());
        $countryName = $countryModel->getData("iso3_code");
        $data['customer_country'] = $countryName;
        $data['tax_area_id'] = $address->getTaxAreaId();
        return $data;
    }

    /**
     * @param unknown $originalEntity
     * @return boolean
     */
    public function isFirstOfPartial($originalEntity)
    {
        if ($originalEntity instanceof \Magento\Sales\Model\Order\Invoice) {
            if (! $originalEntity->getShippingTaxAmount()) {
                return false;
            }
        }
        if ($this->vertexSMBConfigHelper->requestByInvoiceCreation($originalEntity->getStore()) && $originalEntity instanceof \Magento\Sales\Model\Order && $originalEntity->getShippingInvoiced()) {
            return false;
        }
        if ($originalEntity instanceof \Magento\Sales\Model\Order\Creditmemo) {
            if (! $originalEntity->getShippingAMount()) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param unknown $info
     * @param unknown $creditmemoModel
     * @return Ambigous <multitype:number NULL , multitype:number NULL string >
     */
    public function addRefundAdjustments($info, $creditmemoModel)
    {
        if ($creditmemoModel->getAdjustmentPositive()) {
            $itemData = [];
            $itemData['product_class'] = $this->maxProductCodeOffset($this->taxClassNameById($this->vertexSMBConfigHelper->getCreditmemoAdjustmentPositiveClass($creditmemoModel->getStoreId())));
            $itemData['product_code'] = $this->maxProductCodeOffset($this->vertexSMBConfigHelper->getCreditmemoAdjustmentPositiveCode($creditmemoModel->getStoreId()));
            $itemData['qty'] = 1;
            $itemData['price'] = - 1 * $creditmemoModel->getAdjustmentPositive();
            $itemData['extended_price'] = - 1 * $creditmemoModel->getAdjustmentPositive();
            $info[] = $itemData;
        }
        if ($creditmemoModel->getAdjustmentNegative()) {
            $itemData = [];
            $itemData['product_class'] = $this->maxProductCodeOffset($this->taxClassNameById($this->vertexSMBConfigHelper->getCreditmemoAdjustmentFeeClass($creditmemoModel->getStoreId())));
            $itemData['product_code'] = $this->maxProductCodeOffset($this->vertexSMBConfigHelper->getCreditmemoAdjustmentFeeCode($creditmemoModel->getStoreId()));
            $itemData['qty'] = 1;
            $itemData['price'] = $creditmemoModel->getAdjustmentNegative();
            $itemData['extended_price'] = $creditmemoModel->getAdjustmentNegative();
            $info[] = $itemData;
        }
        return $info;
    }

    /**
     * Common function for item preparation
     *
     * @uses   Always send discounted. Discount on TotalRowAmount
     * @param  unknown $item
     * @param  string  $type
     * @param  string  $originalEntityItem
     * @param  string  $event
     * @return multitype:number NULL unknown string
     */
    public function prepareItem($item, $type = 'ordered', $originalEntityItem = null, $event = null)
    {
        $itemData = [];

        $itemData['product_class'] = $this->maxProductCodeOffset($this->taxClassNameById($item->getProduct()->getTaxClassId()));
        $itemData['product_code'] = $this->maxProductCodeOffset($item->getSku());
        $itemData['item_id'] = $item->getId();

        if ($type == 'invoiced') {
            $price = $originalEntityItem->getPrice();
        } else {
            $price = $item->getPrice();
        }

        $itemData['price'] = $price;
        if ($type == 'ordered' && $this->vertexSMBConfigHelper->requestByInvoiceCreation($item->getStoreId())) {
            $itemData['qty'] = $item->getQtyOrdered() - $item->getQtyInvoiced();
        } elseif ($type == 'ordered') {
            $itemData['qty'] = $item->getQtyOrdered();
        } elseif ($type == 'invoiced') {
            $itemData['qty'] = $originalEntityItem->getQty();
        } elseif ($type == 'quote') {
            $itemData['qty'] = $item->getQty();
        }

        if ($type == 'invoiced') {
            $itemData['extended_price'] = $originalEntityItem->getRowTotal() - $originalEntityItem->getDiscountAmount();
        } elseif ($type == 'ordered' && $this->vertexSMBConfigHelper->requestByInvoiceCreation($item->getStoreId())) {
            $itemData['extended_price'] = $item->getRowTotal() - $item->getRowInvoiced() - $item->getDiscountAmount() + $item->getDiscountInvoiced();
        } else {
            $itemData['extended_price'] = $item->getRowTotal() - $item->getDiscountAmount();
        }

        if ($event == 'cancel' || $event == 'refund') {
            $itemData['price'] = - 1 * $itemData['price'];
            $itemData['extended_price'] = - 1 * $itemData['extended_price'];
        }
        return $itemData;
    }

    /**
     * @param unknown $orderAddress
     * @param string  $originalEntity
     * @param string  $event
     * @return multitype:number Ambigous <number> Ambigous <number, Ambigous <number>> NULL string
     */
    public function addShippingInfo($orderAddress, $originalEntity = null, $event = null)
    {
        $itemData = [];
        if ($orderAddress->getShippingMethod() && $this->isFirstOfPartial($originalEntity)) {
            $itemData['product_class'] = $this->maxProductCodeOffset($this->taxClassNameById($this->vertexSMBConfigHelper->getShippingTaxClassId($orderAddress->getStoreId())));
            //$itemData['product_code'] = $orderAddress->getShippingMethod();
            $itemData['product_code'] = $this->maxProductCodeOffset($orderAddress->getShippingMethod());
            $itemData['price'] = $orderAddress->getShippingAmount() - $orderAddress->getShippingDiscountAmount();
            $itemData['qty'] = 1;
            $itemData['extended_price'] = $itemData['price'];

            if ($originalEntity instanceof \Magento\Sales\Model\Order\Creditmemo) {
                $itemData['price'] = $originalEntity->getShippingAmount();
                $itemData['extended_price'] = $itemData['price'];
            }
            if ($event == 'cancel' || $event == 'refund') {
                $itemData['price'] = - 1 * $itemData['price'];
                $itemData['extended_price'] = - 1 * $itemData['extended_price'];
            }
        }
        return $itemData;
    }

    /**
     * @param unknown $orderAddress
     * @param string  $originalEntity
     * @param string  $event
     * @return multitype:|multitype:number NULL string
     */
    public function addOrderGiftWrap($orderAddress, $originalEntity = null, $event = null)
    {
        $itemData = [];

        if (!is_null($originalEntity) && ! $this->isFirstOfPartial($originalEntity)) {
            return $itemData;
        }

        $itemData['product_class'] = $this->maxProductCodeOffset($this->taxClassNameById($this->vertexSMBConfigHelper->getGiftWrappingOrderClass($orderAddress->getStoreId())));
        $itemData['product_code'] = $this->maxProductCodeOffset($this->vertexSMBConfigHelper->getGiftWrappingOrderCode($orderAddress->getStoreId()));
        $itemData['qty'] = 1;
        $itemData['price'] = $orderAddress->getGwPrice();
        $itemData['extended_price'] = $itemData['qty'] * $itemData['price'];

        if ($event == 'cancel' || $event == 'refund') {
            $itemData['price'] = - 1 * $itemData['price'];
            $itemData['extended_price'] = - 1 * $itemData['extended_price'];
        }

        return $itemData;
    }

    /**
     * @param unknown $orderAddress
     * @param string  $originalEntity
     * @param string  $event
     * @return multitype:|multitype:number NULL string
     */
    public function addOrderPrintCard($orderAddress, $originalEntity = null, $event = null)
    {
        $itemData = [];
        if (!is_null($originalEntity) && ! $this->isFirstOfPartial($originalEntity)) {
            return $itemData;
        }
        $itemData['product_class'] = $this->maxProductCodeOffset($this->taxClassNameById($this->vertexSMBConfigHelper->getPrintedGiftcardClass($orderAddress->getStoreId())));
        $itemData['product_code'] = $this->maxProductCodeOffset($this->vertexSMBConfigHelper->getPrintedGiftcardCode($orderAddress->getStoreId()));
        $itemData['qty'] = 1;
        $itemData['price'] = $orderAddress->getGwCardPrice();
        $itemData['extended_price'] = $orderAddress->getGwCardPrice();

        if ($event == 'cancel' || $event == 'refund') {
            $itemData['price'] = - 1 * $itemData['price'];
            $itemData['extended_price'] = - 1 * $itemData['extended_price'];
        }
        return $itemData;
    }

    /**
     *
     * @param unknown $item
     * @param string  $type
     * @param string  $originalEntityItem
     * @param string  $event
     * @return multitype:string number NULL unknown
     */
    public function prepareGiftWrapItem($item, $type = 'ordered', $originalEntityItem = null, $event = null, $store = null)
    {
        $itemData = [];

        $itemData['product_class'] = $this->maxProductCodeOffset($this->taxClassNameById($this->vertexSMBConfigHelper->getGiftWrappingItemClass($store)));
        $itemData['product_code'] = $this->maxProductCodeOffset($this->vertexSMBConfigHelper->getGiftWrappingItemCodePrefix($store) . '-' . $item->getSku());

        if ($type == 'invoiced') {
            $price = $item->getGwPriceInvoiced();
        } elseif ($type == "quote") {
            if ($item->getProduct()->getGiftWrappingPrice()) {
                $wrappingBasePrice = $item->getProduct()->getGiftWrappingPrice();
            } else {
                $wrapping = $this->wrappingFactory->create();
                $wrapping->setStoreId($item->getStoreId());
                $wrapping->load($item->getGwId());
                $wrappingBasePrice = $wrapping->getBasePrice();
            }
            $price = $this->priceCurrency->convert($wrappingBasePrice, $item->getStoreId());
        } else {
            $price = $item->getGwPrice();
        }

        $itemData['price'] = $price;
        $requestByInvoiceCreation = $this->vertexSMBConfigHelper->requestByInvoiceCreation($store);
        if ($type == 'ordered' && $requestByInvoiceCreation) {
            $itemData['qty'] = $item->getQtyOrdered() - $item->getQtyInvoiced();
        } elseif ($type == 'ordered') {
            $itemData['qty'] = $item->getQtyOrdered();
        } elseif ($type == 'invoiced') {
            $itemData['qty'] = $originalEntityItem->getQty();
        } elseif ($type == 'quote') {
            $itemData['qty'] = $item->getQty();
        }

        $itemData['extended_price'] = $itemData['qty'] * ($item->getGwPrice());
        if ($type == 'invoiced' || ($type == 'ordered' && $requestByInvoiceCreation)) {
            $itemData['extended_price'] = $itemData['qty'] * $itemData['price'];
        }

        if ($event == 'cancel' || $event == 'refund') {
            $itemData['price'] = - 1 * $itemData['price'];
            $itemData['extended_price'] = - 1 * $itemData['extended_price'];
        }
        $itemData['lineItemNumber']= 'gift_wrap_' . $item->getId();
        //var_dump($itemData);exit;
        return $itemData;
    }

    public function isGiftWrappingEnabled()
    {
        return $this->moduleManager->isEnabled('Magento_GiftWrapping');
    }
    
    public function validateTaxAddress($taxAddress)
    {
        return $taxAddress->getData('country_id') == 'US';
    }
    
    public function getQuote()
    {
        return $this->cart->getQuote();
    }
    
    public function checkForDeleiveryTerm($data, $taxAddress)
    {
        if ($taxAddress->getCountryId() == "CA") {
            $data['deliveryTerm'] = 'SUP';
        }
        return $data;
    }
}
