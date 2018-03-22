<?php
/**
 * Copyright © 2016 Pixafy Services LLC. All rights reserved.
 *
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL 3.0)
 * @author  Alex Lukyanau <alukyanau@pixafy.com>
 */
namespace VertexSMB\Tax\Helper;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CONFIG_XML_PATH_ENABLE_VERTEX = 'tax/vertexsmb_settings/enable_vertexsmb';

    const CONFIG_XML_PATH_DEFAULT_TAX_CALCULATION_ADDRESS_TYPE = 'tax/calculation/based_on';

    const CONFIG_XML_PATH_DEFAULT_CUSTOMER_CODE = 'tax/classes/default_customer_code';

    const VERTEX_API_HOST = 'tax/vertexsmb_settings/api_url';

    const CONFIG_XML_PATH_VERTEX_API_USER = 'tax/vertexsmb_settings/login';

    const CONFIG_XML_PATH_VERTEX_API_KEY = 'tax/vertexsmb_settings/password';

    const CONFIG_XML_PATH_VERTEX_API_TRUSTED_ID = 'tax/vertexsmb_settings/trustedId';

    const CONFIG_XML_PATH_VERTEX_COMPANY_CODE = 'tax/vertexsmb_seller_info/company';

    const CONFIG_XML_PATH_VERTEX_LOCATION_CODE = 'tax/vertexsmb_seller_info/location_code';

    const CONFIG_XML_PATH_VERTEX_STREET1 = 'tax/vertexsmb_seller_info/streetAddress1';

    const CONFIG_XML_PATH_VERTEX_STREET2 = 'tax/vertexsmb_seller_info/streetAddress2';

    const CONFIG_XML_PATH_VERTEX_CITY = 'tax/vertexsmb_seller_info/city';

    const CONFIG_XML_PATH_VERTEX_COUNTRY = 'tax/vertexsmb_seller_info/country_id';

    const CONFIG_XML_PATH_VERTEX_REGION = 'tax/vertexsmb_seller_info/region_id';

    const CONFIG_XML_PATH_VERTEX_POSTAL_CODE = 'tax/vertexsmb_seller_info/postalCode';

    const CONFIG_XML_PATH_VERTEX_INVOICE_DATE = 'tax/vertexsmb_settings/invoice_tax_date';

    const CONFIG_XML_PATH_VERTEX_TRANSACTION_TYPE = 'SALE';

    const CONFIG_XML_PATH_VERTEX_INVOICE_ORDER = 'tax/vertexsmb_settings/invoice_order';

    const CONFIG_XML_PATH_VERTEX_INVOICE_ORDER_STATUS = 'tax/vertexsmb_settings/invoice_order_status';

    const CONFIG_XML_PATH_SHIPPING_TAX_CLASS = 'tax/classes/shipping_tax_class';

    const VERTEX_ADDRESS_API_HOST = 'tax/vertexsmb_settings/address_api_url';

    const VERTEX_CREDITMEMO_ADJUSTMENT_CLASS = 'tax/classes/creditmemo_adjustment_class';

    const VERTEX_CREDITMEMO_ADJUSTMENT_NEGATIVE_CODE = 'tax/classes/creditmemo_adjustment_negative_code';

    const VERTEX_CREDITMEMO_ADJUSTMENT_POSITIVE_CODE = 'tax/classes/creditmemo_adjustment_positive_code';

    const VERTEX_GIFTWRAP_ORDER_CLASS = 'tax/classes/giftwrap_order_class';

    const VERTEX_GIFTWRAP_ORDER_CODE = 'tax/classes/giftwrap_order_code';

    const VERTEX_GIFTWRAP_ITEM_CLASS = 'tax/classes/giftwrap_item_class';

    const VERTEX_GIFTWRAP_ITEM_CODE_PREFIX = 'tax/classes/giftwrap_item_code';

    const VERTEX_PRINTED_GIFTCARD_CLASS = 'tax/classes/printed_giftcard_class';

    const VERTEX_PRINTED_GIFTCARD_CODE = 'tax/classes/printed_giftcard_code';

    const CONFIG_XML_PATH_VERTEX_ALLOW_CART_QUOTE = 'tax/vertexsmb_settings/allow_cart_request';

    const CONFIG_XML_PATH_VERTEX_SHOW_MANUAL_BUTTON = 'tax/vertexsmb_settings/show_manual_button';

    const CONFIG_XML_PATH_VERTEX_SHOW_POPUP = 'tax/vertexsmb_settings/show_taxrequest_popup';
    
    const VERTEX_CALCULATION_FUNCTION = 'tax/vertexsmb_settings/calculation_function';
    
    const VERTEX_VALIDATION_FUNCTION = 'tax/vertexsmb_settings/valadtion_function';

    const MAX_CHAR_PRODUCT_CODE_ALLOWED = 40;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     *
     * @return boolean
     * @param null|string|bool|int|Store $store
     */
    public function isVertexSMBActive($store = null)
    {
        if ($this->getConfigValue(self::CONFIG_XML_PATH_ENABLE_VERTEX, $store)) {
            return true;
        }
        return false;
    }

    /**
     *
     * @param null|string|bool|int|Store $store
     * @return string
     */
    public function getLocationCode($store = null)
    {
        return $this->getConfigValue(self::CONFIG_XML_PATH_VERTEX_LOCATION_CODE, $store);
    }

    /**
     *
     * @return string
     * @param null|string|bool|int|Store $store
     */
    public function getCompanyCode($store = null)
    {
        return $this->getConfigValue(self::CONFIG_XML_PATH_VERTEX_COMPANY_CODE, $store);
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return string
     */
    public function getCompanyStreet1($store = null)
    {
        return $this->getConfigValue(self::CONFIG_XML_PATH_VERTEX_STREET1, $store);
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return string
     */
    public function getCompanyStreet2($store = null)
    {
        return $this->getConfigValue(self::CONFIG_XML_PATH_VERTEX_STREET2, $store);
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return string
     */
    public function getCompanyCity($store = null)
    {
        return $this->getConfigValue(self::CONFIG_XML_PATH_VERTEX_CITY, $store);
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return string
     */
    public function getCompanyCountry($store = null)
    {
        return ("null" !== $this->getConfigValue(self::CONFIG_XML_PATH_VERTEX_COUNTRY, $store) ? $this->getConfigValue(
            self::CONFIG_XML_PATH_VERTEX_COUNTRY,
            $store
        ) : false);
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return string
     */
    public function getCompanyRegionId($store = null)
    {
        return ("null" !== $this->getConfigValue(self::CONFIG_XML_PATH_VERTEX_REGION, $store) ? $this->getConfigValue(
            self::CONFIG_XML_PATH_VERTEX_REGION,
            $store
        ) : false);
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return string
     */
    public function getCompanyPostalCode($store = null)
    {
        return $this->getConfigValue(self::CONFIG_XML_PATH_VERTEX_POSTAL_CODE, $store);
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return string
     */
    public function getShippingTaxClassId($store = null)
    {
        return $this->getConfigValue(self::CONFIG_XML_PATH_SHIPPING_TAX_CLASS, $store);
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return string
     */
    public function getTrustedId($store = null)
    {
        return $this->getConfigValue(self::CONFIG_XML_PATH_VERTEX_API_TRUSTED_ID, $store);
    }

    /**
     *
     * @return string
     */
    public function getTransactionType()
    {
        return self::CONFIG_XML_PATH_VERTEX_TRANSACTION_TYPE;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return string
     */
    public function getVertexHost($store = null)
    {
        return $this->getConfigValue(self::VERTEX_API_HOST, $store);
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return string
     */
    public function getVertexAddressHost($store = null)
    {
        return $this->getConfigValue(self::VERTEX_ADDRESS_API_HOST, $store);
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return string
     */
    public function getDefaultCustomerCode($store = null)
    {
        return $this->getConfigValue(self::CONFIG_XML_PATH_DEFAULT_CUSTOMER_CODE, $store);
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return string
     */
    public function getCreditmemoAdjustmentFeeCode($store = null)
    {
        return $this->getConfigValue(self::VERTEX_CREDITMEMO_ADJUSTMENT_NEGATIVE_CODE, $store);
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return string
     */
    public function getCreditmemoAdjustmentFeeClass($store = null)
    {
        return $this->getConfigValue(self::VERTEX_CREDITMEMO_ADJUSTMENT_CLASS, $store);
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return string
     */
    public function getCreditmemoAdjustmentPositiveCode($store = null)
    {
        return $this->getConfigValue(self::VERTEX_CREDITMEMO_ADJUSTMENT_POSITIVE_CODE, $store);
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return string
     */
    public function getCreditmemoAdjustmentPositiveClass($store = null)
    {
        return $this->getConfigValue(self::VERTEX_CREDITMEMO_ADJUSTMENT_CLASS, $store);
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return string
     */
    public function allowCartQuote($store = null)
    {
        return $this->getConfigValue(self::CONFIG_XML_PATH_VERTEX_ALLOW_CART_QUOTE, $store);
    }

    /**
     *
     * @return string
     */
    public function getGiftWrappingOrderClass($store = null)
    {
        return $this->getConfigValue(self::VERTEX_GIFTWRAP_ORDER_CLASS, $store);
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return string
     */
    public function getGiftWrappingOrderCode($store = null)
    {
        return $this->getConfigValue(self::VERTEX_GIFTWRAP_ORDER_CODE, $store);
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return string
     */
    public function getGiftWrappingItemClass($store = null)
    {
        return $this->getConfigValue(self::VERTEX_GIFTWRAP_ITEM_CLASS, $store);
    }

    /**
     *
     * @return string
     */
    public function getGiftWrappingItemCodePrefix($store = null)
    {
        return $this->getConfigValue(self::VERTEX_GIFTWRAP_ITEM_CODE_PREFIX, $store);
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return string
     */
    public function getPrintedGiftcardClass($store = null)
    {
        return $this->getConfigValue(self::VERTEX_PRINTED_GIFTCARD_CLASS, $store);
    }
    
    /**
     * @param null|string|bool|int|Store $store
     * @return string
     */
    public function getCalculationFunction($store = null)
    {
        return $this->getConfigValue(self::VERTEX_CALCULATION_FUNCTION, $store);
    }
    
    /**
     * @param null|string|bool|int|Store $store
     * @return string
     */
    public function getValidationFunction($store = null)
    {
        return $this->getConfigValue(self::VERTEX_VALIDATION_FUNCTION, $store);
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return string
     */
    public function getPrintedGiftcardCode($store = null)
    {
        return $this->getConfigValue(self::VERTEX_PRINTED_GIFTCARD_CODE, $store);
    }

    /**
     *
     * @return string[]
     */
    public function getQuoteAllowedControllers()
    {
        $quoteAllowedControllers = [
            'onepage',
            'multishipping',
            'sales_order_create'
        ];
        return $quoteAllowedControllers;
    }

    /**
     * @return boolean
     */
    public function requestByInvoiceCreation($store = null)
    {
        $vertexInvoiceEvent = $this->getConfigValue(self::CONFIG_XML_PATH_VERTEX_INVOICE_ORDER, $store);
        if ($vertexInvoiceEvent == 'invoice_created') {
            return true;
        }
        return false;
    }
    
    /**
     * @return int
     */
    public function maxAllowedProductCode()
    {
        return self::MAX_CHAR_PRODUCT_CODE_ALLOWED;
    }

    /**
     *
     * @param string                     $value
     * @param null|string|bool|int|Store $store
     * @return float|null
     */
    public function getConfigValue($value, $store = null)
    {
        $value = $this->scopeConfig->getValue($value, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
        
        return $value;
    }
}
