<?php
/**
 * Created by PhpStorm.
 * User: Manny
 * Date: 10/2/2016
 * Time: 7:46 PM
 */

namespace VertexSMB\Tax\Model\Plugin;

class GiftWrappingTaxPlugin
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

    /*
    * @var \Magento\Framework\Registry
    */
    protected $registry;

    /**
     * Constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, \Psr\Log\LoggerInterface $logger, \Magento\Framework\Registry $registry)
    {
        $this->objectManager = $objectManager;
        $this->logger = $logger;
        $this->registry = $registry;
    }

    // @codingStandardsIgnoreStart
    public function beforeCollect(\Magento\GiftWrapping\Model\Total\Quote\Tax\GiftwrappingAfterTax $subject,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {// @codingStandardsIgnoreEnd 
    
    
        $extraTaxableDetails = $total->getExtraTaxableDetails();
        $quoteGwType = \Magento\GiftWrapping\Model\Total\Quote\Tax\Giftwrapping::QUOTE_TYPE;
        $printedCardType = \Magento\GiftWrapping\Model\Total\Quote\Tax\Giftwrapping::PRINTED_CARD_TYPE;
        $itemGwType  = \Magento\GiftWrapping\Model\Total\Quote\Tax\Giftwrapping::ITEM_TYPE;
        $vertexTaxItems = $this->registry->registry(\VertexSMB\Tax\Helper\Data::VERTEX_LINE_ITEM_TAX_KEY);

        if (isset($extraTaxableDetails[$itemGwType])) {
            $extraTaxableDetails[$itemGwType] = $this->processWrappingForItems($vertexTaxItems, $extraTaxableDetails[$itemGwType]);
        }

        if (isset($extraTaxableDetails[$quoteGwType])) {
            $extraTaxableDetails[$quoteGwType] = $this->processWrappingForQuote($vertexTaxItems, $extraTaxableDetails[$quoteGwType]);
        }

        if (isset($extraTaxableDetails[$printedCardType])) {
            $extraTaxableDetails[$printedCardType] = $this->processPrintedCard($vertexTaxItems, $extraTaxableDetails[$printedCardType]);
        }
        $total->setExtraTaxableDetails($extraTaxableDetails);
        return [$quote, $shippingAssignment, $total];
    }

    /**
     * Update wrapping tax total for items
     *
     * @param  array $vertexTaxItems
     * @param  array $itemTaxDetails
     * @return arrau
     */
    protected function processWrappingForItems($vertexTaxItems, $itemTaxDetails)
    {
        foreach ($itemTaxDetails as $gwItems) {
            foreach ($gwItems as &$gwItem) {
                if (isset($vertexTaxItems[$gwItem['code']])) {
                    $vertexItem = $vertexTaxItems[$gwItem['code']];
                }
            }
        }
        return $itemTaxDetails;
    }

    /**
     * Update wrapping tax total for quote
     *
     * @param  array $vertexTaxItems
     * @param  array $itemTaxDetails
     * @return array
     */
    protected function processWrappingForQuote($vertexTaxItems, $itemTaxDetails)
    {
        /* if(isset($vertexTaxItems[\Magento\GiftWrapping\Model\Total\Quote\Tax\Giftwrapping::QUOTE_TYPE]))
        {
            $item = $vertexTaxItems[\Magento\GiftWrapping\Model\Total\Quote\Tax\Giftwrapping::QUOTE_TYPE];
            var_dump($itemTaxDetails);
            $itemTaxDetails[0]['base_row_tax']= $item->getBaseTaxAmount();
            $itemTaxDetails[0]['row_tax'] = $item->getTaxAmount();
            $itemTaxDetails[0]['price_incl_tax'] = $itemTaxDetails[0]['price_excl_tax'] + $item->getTaxAmount();
            $itemTaxDetails[0]['base_price_incl_tax'] = $itemTaxDetails[0]['base_price_excl_tax'] + $item->getTaxAmount();
            $itemTaxDetails[0]['row_total_excl_tax'] = $itemTaxDetails[0]['price_excl_tax'];
            $itemTaxDetails[0]['row_total_incl_tax'] = $itemTaxDetails[0]['price_incl_tax'];
            $itemTaxDetails[0]['tax_percent'] = $item->getTaxPercent();
        }*/
        return $itemTaxDetails;
    }

    /**
     * Update card tax total for items
     *
     * @param  array $vertexTaxItems
     * @param  array $itemTaxDetails
     * @return array
     */
    protected function processPrintedCard($vertexTaxItems, $itemTaxDetails)
    {
        return $itemTaxDetails;
    }
}
