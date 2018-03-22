<?php
/**
 * Created by PhpStorm.
 * User: EETIENNE
 * Date: 9/12/2016
 * Time: 1:38 PM
 */

namespace VertexSMB\Tax\Model\Plugin;

use Magento\Quote\Model\Quote\Item\AbstractItem;

class SubtotalPlugin
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
    public function aroundMapItem(\Magento\Tax\Model\Sales\Total\Quote\Subtotal $subtotal, \Closure $proceed,
        \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $itemDataObjectFactory,
        AbstractItem $item,
        $priceIncludesTax,
        $useBaseCurrency,
        $parentCode = null
    ) {// @codingStandardsIgnoreEnd 
    
        $itemDataObject = $proceed($itemDataObjectFactory, $item, $priceIncludesTax, $useBaseCurrency, $parentCode);
        $itemDataObject->setItemId($item->getId());
        $this->registry->register(\VertexSMB\Tax\Helper\Data::VERTEX_QUOTE_ITEM_ID_PREFIX.$itemDataObject->getCode(), $item->getId(), true);
        return $itemDataObject;
    }
}
