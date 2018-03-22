<?php
/**
 * Copyright © 2016 Pixafy Services LLC. All rights reserved.
 *
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL 3.0)
 * @author  Alex Lukyanau <alukyanau@pixafy.com>
 */
namespace VertexSMB\Tax\Helper\Request;

/**
 * Seller information helper
 */
class Shipping extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $customerHelper;

    protected $sellerHelper;

    protected $vertexConfigHelper;

    protected $vertexSMBHelper;

    protected $lineItemId;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        Seller $sellerHelper,
        Customer $customerHelper,
        \VertexSMB\Tax\Helper\Config $vertexConfigHelper,
        \VertexSMB\Tax\Helper\Data $vertexSMBHelper
    ) {
    
        $this->customerHelper = $customerHelper;
        $this->sellerHelper = $sellerHelper;
        $this->vertexSMBConfigHelper = $vertexConfigHelper;
        $this->vertexSMBHelper = $vertexSMBHelper;
        $this->lineItemId = 'shipping';
        parent::__construct($context);
    }

    /**
     *
     * @return string
     */
    public function getLineItemId()
    {
        return $this->lineItemId;
    }

    public function addShippingItem(\Magento\Quote\Model\Quote\Address $taxAddress)
    {
        $data = [];
        
        $data['Seller'] = $this->sellerHelper->addSellerInformation();
        $data['Customer'] = $this->customerHelper->addCustomerInformation($taxAddress);
        $data['Product'] = [
            '_' => $this->vertexSMBHelper->maxProductCodeOffset($taxAddress->getShippingMethod()),
            'productClass' => $this->vertexSMBHelper->maxProductCodeOffset($this->vertexSMBHelper->taxClassNameById(
                $this->vertexSMBConfigHelper->getShippingTaxClassId()
            ))
        ];
        $data['Quantity'] = 1;
        if (!$taxAddress->getShippingMethod()) {
            $taxAddress->setCollectShippingRates(true)->collectShippingRates()->save();
        }
        //echo $taxAddress->getShippingMethod();
        $rate = $taxAddress->getShippingRateByCode($taxAddress->getShippingMethod());
        //var_dump($rate->getFreeShipping());exit;
       //echo ' '.$taxAddress->getShippingAmount();exit;
        $data['UnitPrice'] = $taxAddress->getFreeShipping() ? 0 : $taxAddress->getShippingAmount() - $taxAddress->getShippingDiscountAmount();
        $data['ExtendedPrice'] = $data['UnitPrice'];
        // $data['lineItemNumber']=
        $data['lineItemId'] = $this->lineItemId;
        $data['locationCode'] = $this->vertexSMBConfigHelper->getLocationCode();
        $data = $this->vertexSMBHelper->checkForDeleiveryTerm($data, $taxAddress);
        return $data;
    }
}
