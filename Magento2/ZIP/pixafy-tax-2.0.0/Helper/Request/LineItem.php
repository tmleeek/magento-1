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
class LineItem extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $customerHelper;

    protected $sellerHelper;

    protected $vertexConfigHelper;

    protected $vertexSMBHelper;

    /**
     * @var PriceCurrencyInterface
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
     * @var \Magento\Tax\Api\TaxClassRepositoryInterface
     */

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        Seller $sellerHelper,
        Customer $customerHelper,
        \VertexSMB\Tax\Helper\Config $vertexConfigHelper,
        \VertexSMB\Tax\Helper\Data $vertexSMBHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {
    
        $this->customerHelper = $customerHelper;
        $this->objectManager = $objectManager;
        $this->sellerHelper = $sellerHelper;
        $this->vertexSMBConfigHelper = $vertexConfigHelper;
        $this->vertexSMBHelper = $vertexSMBHelper;
        $this->priceCurrency = $priceCurrency;
        $this->moduleManager = $context->getModuleManager();
        if ($this->vertexSMBHelper->isGiftWrappingEnabled()) {
            $this->wrappingFactory = $this->objectManager->get('\Magento\GiftWrapping\Model\WrappingFactory');
            $this->giftWrappingData = $this->objectManager->get('\Magento\GiftWrapping\Helper\Data');
        }
        parent::__construct($context);
    }

    /**
     *
     * @param \Magento\Quote\Model\Quote\Address $taxAddress
     * @param \Magento\Quote\Model\Quote\Item    $taxAddressItem
     * @return number[][][]|NULL[][][]|string[][][]|string[][]|NULL[][]|mixed[][]
     */
    public function addLineItem(\Magento\Quote\Model\Quote\Address $taxAddress, $taxAddressItem)
    {
        $data = [];
        
        $data['Seller'] = $this->sellerHelper->addSellerInformation();
        $data['Customer'] = $this->customerHelper->addCustomerInformation($taxAddress);
        $data['Product'] = [
            '_' => $this->vertexSMBHelper->maxProductCodeOffset($taxAddressItem->getSku()),
            'productClass' => $this->vertexSMBHelper->taxClassNameById(
                $taxAddressItem->getProduct()
                    ->getTaxClassId()
            )
        ];
         
        $data['Quantity']  = $taxAddressItem->getQty();
        $data['UnitPrice']  = $taxAddressItem->getPrice();
        $data['ExtendedPrice']  = $taxAddressItem->getRowTotal() - $taxAddressItem->getDiscountAmount();
        
        // $data['lineItemNumber']=
        $data['lineItemId'] = $taxAddressItem->getId();
        $data['locationCode'] = $this->vertexSMBConfigHelper->getLocationCode();
        $data = $this->vertexSMBHelper->checkForDeleiveryTerm($data, $taxAddress);
        return $data;
    }
    
     /**
      *
      * @param \Magento\Quote\Model\Quote\Address $taxAddress
      * @return number[][][]|NULL[][][]|string[][][]|string[][]|NULL[][]|mixed[][]
      */
    public function addOrderPrintCard(\Magento\Quote\Model\Quote\Address $taxAddress)
    {
        $data = [];
        
        $data['Seller'] = $this->sellerHelper->addSellerInformation();
        $data['Customer'] = $this->customerHelper->addCustomerInformation($taxAddress);
        $data['Product'] = [
            '_' => $this->vertexSMBConfigHelper->getPrintedGiftcardCode(),
            'productClass' => $this->vertexSMBHelper->taxClassNameById(
                $this->vertexSMBConfigHelper->getPrintedGiftcardClass()
            )
        ];
         
        $data['Quantity']  = 1;
        $data['UnitPrice']  = $taxAddress->getGwCardPrice();
        $printedCardBasePrice = $this->giftWrappingData->getPrintedCardPrice($taxAddress->getStoreId());
        $data['UnitPrice'] = $this->priceCurrency->convert($printedCardBasePrice, $taxAddress->getStoreId());
        $data['ExtendedPrice']  = $data['UnitPrice'];
        $data['lineItemId'] = \Magento\GiftWrapping\Model\Total\Quote\Tax\Giftwrapping::CODE_PRINTED_CARD;
        $data['locationCode'] = $this->vertexSMBConfigHelper->getLocationCode();
        $data = $this->vertexSMBHelper->checkForDeleiveryTerm($data, $taxAddress);
        return $data;
    }
    
    /**
     * @param unknown $orderAddress
     * @param string  $originalEntity
     * @param string  $event
     * @return multitype:|multitype:number NULL string
     */
    public function addOrderGiftWrap(\Magento\Quote\Model\Quote\Address $taxAddress)
    {
        $data = [];

        $data['Seller'] = $this->sellerHelper->addSellerInformation();
        $data['Customer'] = $this->customerHelper->addCustomerInformation($taxAddress);
        $data['Product'] = [
            '_' => $this->vertexSMBConfigHelper->getGiftWrappingOrderCode(),
            'productClass' => $this->vertexSMBHelper->taxClassNameById(
                $this->vertexSMBConfigHelper->getGiftWrappingOrderClass()
            )
        ];

        $data['Quantity']  = 1;
        $wrapping = $this->wrappingFactory->create();
        $wrapping->setStoreId($taxAddress->getStoreId())->load($taxAddress->getGwId());
        $wrappingBaseAmount = $wrapping->getBasePrice();
        $data['UnitPrice'] = $this->priceCurrency->convert($wrappingBaseAmount, $taxAddress->getStoreId());
        $data['ExtendedPrice'] =  $data['UnitPrice'];
        $data['lineItemId'] = \Magento\GiftWrapping\Model\Total\Quote\Tax\Giftwrapping::CODE_QUOTE_GW;
        $data['locationCode'] = $this->vertexSMBConfigHelper->getLocationCode();
        $data = $this->vertexSMBHelper->checkForDeleiveryTerm($data, $taxAddress);
        return $data;
    }


    public function prepareGiftWrapItem($taxAddress, $item)
    {
        $data = [];
        $data['Seller'] = $this->sellerHelper->addSellerInformation();
        $data['Customer'] = $this->customerHelper->addCustomerInformation($taxAddress);
        $data['Product'] = [
            '_' => $this->vertexSMBConfigHelper->getGiftWrappingItemCodePrefix() . '-' . $item->getSku(),
            'productClass' => $this->vertexSMBHelper->taxClassNameById($this->vertexSMBConfigHelper->getGiftWrappingItemClass())
        ];

        if ($item->getProduct()->getGiftWrappingPrice()) {
            $wrappingBasePrice = $item->getProduct()->getGiftWrappingPrice();
        } else {
            $wrapping = $this->wrappingFactory->create();
            $wrapping->setStoreId($item->getStoreId());
            $wrapping->load($item->getGwId());
            $wrappingBasePrice = $wrapping->getBasePrice();
        }
        $itemData['UnitPrice'] = $this->priceCurrency->convert($wrappingBasePrice, $item->getStoreId());

        $data['Quantity'] = $item->getQty();
        $data['ExtendedPrice'] = $data['Quantity'] * $itemData['UnitPrice'];
        $data['lineItemId']= \Magento\GiftWrapping\Model\Total\Quote\Tax\Giftwrapping ::CODE_ITEM_GW_PREFIX.'_'. $item->getId();
        $data = $this->vertexSMBHelper->checkForDeleiveryTerm($data, $taxAddress);
        return $data;
    }
}
