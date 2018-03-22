<?php
/**
 * Copyright © 2016 Pixafy Services LLC. All rights reserved.
 *
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL 3.0)
 * @author  Alex Lukyanau <alukyanau@pixafy.com>
 */
namespace VertexSMB\Tax\Model\TaxQuote;

use \Magento\Framework\DataObject;

class TaxQuoteResponse extends DataObject
{
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
    
        $this->logger = $logger;
        parent::__construct($data);
    }
    
    
    
    /**
     * @param unknown $responseObject
     * @return VertexSMB_TaxEE_Model_TaxQuoteResponse
     */
    public function parseResponse($responseObject)
    {
        if (is_array($responseObject)) {
            $taxLineItems = $responseObject["LineItem"];
        } else {
            if (is_array($responseObject->QuotationResponse->LineItem)) {
                $taxLineItems = $responseObject->QuotationResponse->LineItem;
            } else {
                $taxLineItems[] = $responseObject->QuotationResponse->LineItem;
            }
        }

        $this->setTaxLineItems($taxLineItems);
        $this->setLineItemsCount(count($taxLineItems));
        $this->prepareQuoteTaxedItems($taxLineItems);
    
        return $this;
    }
    /**
     * @param array $itemsTax
     */
    public function prepareQuoteTaxedItems(array $itemsTax)
    {
        $quoteTaxedItems = [];

        foreach ($itemsTax as $item) {
            //$lineItemNumber = $item["lineItemNumber"];
            $itemTotalTax = 0;
            if (isset($item["TotalTax"])) {
                if (is_array($item["TotalTax"])) {
                    $itemTotalTax += $item["TotalTax"]["_"];
                } else {
                    $itemTotalTax += $item["TotalTax"];
                }
            }
            $taxPercent = 0;
            $taxRate = $this->_getTaxRate($item);

            $taxPercent = $taxRate * 100;
    
            $quoteItemId = $item["lineItemId"];
            $taxItemInfo = new \Magento\Framework\DataObject();
            $taxItemInfo->setProductClass($item["Product"]["productClass"]);
            $taxItemInfo->setProductSku($item["Product"]["_"]);
            if (isset($item["Quantity"])) {
                $taxItemInfo->setProductQty($item["Quantity"]["_"]);
            }
            if (isset($item["UnitPrice"])) {
                $taxItemInfo->setUnitPrice($item["UnitPrice"]);
            }
            $taxItemInfo->setTaxRate($taxRate);
            $taxItemInfo->setTaxPercent($taxPercent);
            $taxItemInfo->setBaseTaxAmount($itemTotalTax);
            $taxItemInfo->setTaxAmount($itemTotalTax);
            $quoteTaxedItems[$quoteItemId] = $taxItemInfo;
        }
        $this->setQuoteTaxedItems($quoteTaxedItems);
    }

    protected function _getTaxRate($item)
    {
        $taxRate = 0;
        foreach ($item["Taxes"] as $key => $taxValue) {
            if (!isset($item["TotalTax"])) {
                 $itemTotalTax += $taxValue["TotalTax"];
                if (is_array($taxValue["TotalTax"])) {
                    $itemTotalTax += $taxValue["TotalTax"]["_"];
                }
            }
            if (isset($taxValue["EffectiveRate"])) {
                $taxRate += (float) $taxValue["EffectiveRate"];
            } elseif ($key =="EffectiveRate") {
                $taxRate += (float) $taxValue;
            }
        }
        return $taxRate;
    }
}
