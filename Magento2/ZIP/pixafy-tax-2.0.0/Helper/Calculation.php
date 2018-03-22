<?php
/**
 * Copyright © 2016 Pixafy Services LLC. All rights reserved.
 *
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL 3.0)
 * @author  Alex Lukyanau <alukyanau@pixafy.com>
 */
namespace VertexSMB\Tax\Helper;

/**
 * Tax Calculation helper
 */
class Calculation extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $objectManager;

    protected $addressHelper;

    protected $quotationRequestHelper;

    protected $taxAreaRequest;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Request\Address $addressHelper,
        Request\Type\QuotationRequest $quotationRequestHelper,
        \VertexSMB\Tax\Model\TaxArea\TaxAreaRequest $taxAreaRequest
    ) {
    
        $this->objectManager = $objectManager;
        $this->addressHelper = $addressHelper;
        $this->quotationRequestHelper = $quotationRequestHelper;
        $this->taxAreaRequest = $taxAreaRequest;

        parent::__construct($context);
    }

    /**
     *
     * @param \Magento\Quote\Model\Quote\Address $taxAddress
     * @return boolean
     */
    public function calculateTaxAreaIds(\Magento\Quote\Model\Quote\Address $taxAddress)
    {
        $street = $taxAddress->getStreet();
        
        $address = $this->addressHelper->formatAddress(
            $street,
            $taxAddress->getCity(),
            $taxAddress->getRegionId(),
            $taxAddress->getPostcode(),
            $taxAddress->getCountryId()
        );
        
    
        if ($address['Country'] != 'USA') {
            return true;
        }
        
        $requestResult = $this->taxAreaRequest->taxAreaLookup(
            $address
        );
        //$taxAreaIds = $this->taxAreaRequest->getResponse()->getTaxAreaLocationsCollection();
        //$this->_logger->info(print_r($this->taxAreaRequest->getResponse(), true));
        
        if ($requestResult instanceof \Exception || $requestResult === false) {
            return false;
        }
        return $this->taxAreaRequest->getResponse()->getTaxAreaWithHighestConfidence();
    }

    public function calculateTax(\Magento\Quote\Model\Quote\Address $taxAddress)
    {
        $request = $this->quotationRequestHelper->prepareRequest($taxAddress);

        /* Send API Request */
        $requestResult = $this->objectManager->get('\VertexSMB\Tax\Model\TaxQuote\TaxQuoteRequest')->taxQuote(
            $request
        );

        /* Process response */
        $response = $this->objectManager->get('\VertexSMB\Tax\Model\TaxQuote\TaxQuoteResponse')->parseResponse($requestResult);
        return $response;
        //var_dump($response->getQuoteTaxedItems());exit;
    }
}
