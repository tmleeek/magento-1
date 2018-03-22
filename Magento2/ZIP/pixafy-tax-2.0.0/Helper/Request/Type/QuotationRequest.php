<?php
/**
 * Copyright © 2016 Pixafy Services LLC. All rights reserved.
 *
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL 3.0)
 * @author  Alex Lukyanau <alukyanau@pixafy.com>
 */
namespace VertexSMB\Tax\Helper\Request\Type;

use VertexSMB\Tax\Helper\Request;

/**
 * QuotationRequest helper
 */
class QuotationRequest extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $requestType;

    protected $request;

    protected $vertexSMBConfigHelper;

    protected $lineItemHelper;

    protected $addressHelper;

    protected $requestHeaderHelper;

    protected $lineItemShippingHelper;
    
    protected $moduleManager;
    
    protected $vertexSMBHelper;

    /**
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \VertexSMB\Tax\Helper\Config $vertexConfigHelper,
        Request\LineItem $lineItemHelper,
        Request\Address $addressHelper,
        Request\Header $requestHeaderHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        Request\Shipping $lineItemShippingHelper,
        \VertexSMB\Tax\Helper\Data $vertexSMBHelper
    ) {
    
        $this->vertexSMBConfigHelper = $vertexConfigHelper;
        $this->lineItemHelper = $lineItemHelper;
        $this->addressHelper = $addressHelper;
        $this->requestHeaderHelper = $requestHeaderHelper;
        $this->request = [];
        $this->date = $date;
        $this->requestType = 'QuotationRequest';
        $this->lineItemShippingHelper = $lineItemShippingHelper;
        $this->moduleManager = $context->getModuleManager();
        $this->vertexSMBHelper = $vertexSMBHelper;
        parent::__construct($context);
    }

    /**
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     *
     * @return string
     */
    public function getRequestType()
    {
        return $this->requestType;
    }

    /**
     *
     * @param \Magento\Quote\Model\Quote\Address $taxAddress
     */
    public function prepareRequest(\Magento\Quote\Model\Quote\Address $taxAddress)
    {
        $date = date("Y-m-d", $this->date->timestamp(time()));
        $taxLineItems = [];
        $giftWrappingEnabled = $this->moduleManager->isEnabled('Magento_GiftWrapping');
        foreach ($taxAddress->getAllVisibleItems() as $taxAddressItem) {
            //echo "An\n";
            
            if ($taxAddressItem->getHasChildren() && $taxAddressItem->isChildrenCalculated()) {
                foreach ($taxAddressItem->getChildren() as $child) {
                    $taxLineItems[] = $this->lineItemHelper->addLineItem($taxAddress, $child);
                    if ($giftWrappingEnabled && $child->getGwId()) {
                        $taxLineItems[] = $this->lineItemHelper->prepareGiftWrapItem($taxAddress, $child);
                    }
                }
                continue;
            } else {
                $taxLineItems[] = $this->lineItemHelper->addLineItem($taxAddress, $taxAddressItem);
                if ($giftWrappingEnabled && $taxAddressItem->getGwId()) {
                    $taxLineItems[] = $this->lineItemHelper->prepareGiftWrapItem($taxAddress, $taxAddressItem);
                }
            }
        }
        $taxLineItems[] = $this->lineItemShippingHelper->addShippingItem($taxAddress);
        if ($giftWrappingEnabled && $taxAddress->getGwId()) {
            $taxLineItems[] = $this->lineItemHelper->addOrderGiftWrap($taxAddress);
        }
        
        if ($giftWrappingEnabled && $taxAddress->getGwAddCard()) {
            $taxLineItems[] = $this->lineItemHelper->addOrderPrintCard($taxAddress);
        }
        

        $this->request = $this->requestHeaderHelper->addHeaderInformation();
        $this->request[$this->getRequestType()] = [
            'documentDate' => $date,
            'postingDate' => $date,
            'transactionType' => $this->vertexSMBConfigHelper->getTransactionType(),
            'LineItem' => $taxLineItems
        ];
        
        return $this->getRequest();
    }
}
