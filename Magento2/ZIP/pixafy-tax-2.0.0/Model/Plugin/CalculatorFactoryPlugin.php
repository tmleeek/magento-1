<?php
/**
 * Copyright © 2016 Pixafy Services LLC. All rights reserved.
 *
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL 3.0)
 * @author  Alex Lukyanau <alukyanau@pixafy.com>
 */
namespace VertexSMB\Tax\Model\Plugin;

use Magento\Customer\Api\Data\AddressInterface as CustomerAddress;
use Magento\Store\Model\StoreManagerInterface;

class CalculatorFactoryPlugin
{

    /**
     * Identifier constant for Vertex SMB based calculation
     */
    const CALC_UNIT_VERTEXSMB = 'VERTEXSMB_UNIT_BASE_CALCULATION';

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

    /*
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \VertexSMB\Tax\Helper\Config
     */
    protected $vertexSMBConfigHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Registry $registry,
        StoreManagerInterface $storeManager,
        \VertexSMB\Tax\Helper\Config $vertexSMBConfigHelper
    ) {
        $this->objectManager = $objectManager;
        $this->logger = $logger;
        $this->registry = $registry;
        $this->storeManager = $storeManager;
        $this->vertexSMBConfigHelper = $vertexSMBConfigHelper;
    }

    /**
     *
     * @param \Magento\Tax\Model\Calculation\CalculatorFactory $subject
     * @param \Closure                                         $proceed
     * @param unknown                                          $type
     * @param unknown                                          $storeId
     * @param CustomerAddress                                  $billingAddress
     * @param CustomerAddress                                  $shippingAddress
     * @param unknown                                          $customerTaxClassId
     * @param unknown                                          $customerId
     * @return \Magento\Tax\Model\Calculation\AbstractCalculator
     * @throws \InvalidArgumentException
     */

    // @codingStandardsIgnoreStart
    public function aroundCreate(\Magento\Tax\Model\Calculation\CalculatorFactory $subject,
        \Closure $proceed,
        $type,
        $storeId,
        CustomerAddress $billingAddress = null,
        CustomerAddress $shippingAddress = null,
        $customerTaxClassId = null,
        $customerId = null
    ) {// @codingStandardsIgnoreEnd
     
    
        if ($type == self::CALC_UNIT_VERTEXSMB) {
            $className = 'VertexSMB\Tax\Model\Calculation\VertexSMBCalculator';
            
            $calculator = $this->objectManager->create(
                $className,
                [
                'storeId' => $storeId
                ]
            );
            
            if (null != $shippingAddress) {
                $calculator->setShippingAddress($shippingAddress);
            }
            if (null != $billingAddress) {
                $calculator->setBillingAddress($billingAddress);
            }
            if (null != $customerTaxClassId) {
                $calculator->setCustomerTaxClassId($customerTaxClassId);
            }
            if (null != $customerId) {
                $calculator->setCustomerId($customerId);
            }
            
            if ($this->canCalculateTax()) {
                $vertexSMBCalculationHelper = $this->objectManager->create('VertexSMB\Tax\Helper\Calculation');
                $vertexSMBHelper = $this->objectManager->create('VertexSMB\Tax\Helper\Data');
                $address = $vertexSMBHelper->getTaxAddress();
                if ($address->getId() && $address->getCity() && $vertexSMBHelper->validateTaxAddress($address)) {
                    $taxArea = $vertexSMBCalculationHelper->calculateTaxAreaIds($address);
                    if ($taxArea && !is_null($taxArea)) {
                        try {
                            $address->setTaxAreaId($taxArea->getTaxAreaId())->save();
                        } catch (\Exception $e) {
                            $this->logger->error($e->getMessage());
                        }
                    } else {
                        $this->logger->error("TAX AREA NOT FOUND FOR ADDRESS{$address->getId()}");
                        throw new \Exception(__('Error, Vertex Tax Area not found for address entered'));
                    }
                }
                try {
                    $itemsVertexTaxes = $vertexSMBCalculationHelper->calculateTax($address)->getQuoteTaxedItems();
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                    $itemsVertexTaxes = [];
                }
                $this->registry->unregister(\VertexSMB\Tax\Helper\Data::VERTEX_LINE_ITEM_TAX_KEY);
                $this->registry->register(\VertexSMB\Tax\Helper\Data::VERTEX_LINE_ITEM_TAX_KEY, $itemsVertexTaxes);
            }
            
            return $calculator;
        } else {
            return $proceed($type, $storeId, $billingAddress, $shippingAddress, $customerTaxClassId, $customerId);
        }
    }

    /**
     *
     * @return \VertexSMB\Tax\Model\Calculation\VertexSMBCalculator
     */
    protected function canCalculateTax()
    {
        $storeId = $this->storeManager->getStore()->getId();
        if (! $this->vertexSMBConfigHelper->isVertexSMBActive($storeId)) {
            return;
        }
        $vertexSMBHelper = $this->objectManager->create('VertexSMB\Tax\Helper\Data');
        $quote = $vertexSMBHelper->getSession()->getQuote();

        if (! $quote->getShippingAddress() && ! $quote->getBillingAddress()) {
            return false;
        }
        
        $address = $vertexSMBHelper->getTaxAddress();
      
        
        //echo $address->getShippingMethod(); die('a');
        /* Address should have shipping method set */
        if (! $quote->isVirtual() && ! $address->getShippingMethod()) {
            return false;
        }
        
        /*False  if shipping method estimation*/
        if ($vertexSMBHelper->getSourcePath()=='/rest/default/V1/carts/mine/estimate-shipping-methods') {
            return false;
        }
        
        /* Request was not sent. Address not specified. */
        if (! $address->getCountryId() || ! $address->getRegionId() || ! $address->getPostcode()
            || ! count($address->getAllItems())
        ) {
            return false;
        }
        
        /*Bug when quote requests sends without shipping price
        if ($address->getShippingAmount())
            $address->collectShippingRates();
        */
        //$this->_request->getControllerName()
        /*
         * ! $address->getStreet1() && ! $this->vertexSMBConfigHelper->allowCartQuote()
         */
        
        /* Quote not allowed at the cart. */
        /*
         * if ($this->_request->getControllerName() == 'cart' && ! $this->vertexSMBConfigHelper->allowCartQuote()) { return false; }
         */
 
  
        return true;
    }
}
