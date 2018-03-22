<?php
/**
 * Created by PhpStorm.
 * User: Manny
 * Date: 10/2/2016
 * Time: 7:46 PM
 */

namespace VertexSMB\Tax\Model\Plugin;

class TotalPlugin
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
    
    protected $count;

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
        $this->cnt = 0;
    }

    // @codingStandardsIgnoreStart
    public function aroundCollect(\Magento\GiftWrapping\Model\Total\Quote\Tax\Giftwrapping $subject,
        \Closure $proceed,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {// @codingStandardsIgnoreEnd
     
    
        $result = $proceed($quote, $shippingAssignment, $total);
        foreach ($shippingAssignment->getItems() as $item) {
            if ($item->getProduct()->isVirtual() || $item->getParentItem() || !$item->getGwId()) {
                continue;
            }
            $gwItemCode = \Magento\GiftWrapping\Model\Total\Quote\Tax\Giftwrapping ::CODE_ITEM_GW_PREFIX . $this->nextIncrement();
            $this->registry->register(\VertexSMB\Tax\Helper\Data::VERTEX_QUOTE_ITEM_ID_PREFIX.$gwItemCode, \Magento\GiftWrapping\Model\Total\Quote\Tax\Giftwrapping ::CODE_ITEM_GW_PREFIX.'_'. $item->getId(), true);
        }
        return $result;
    }
    
    protected function nextIncrement()
    {
        ++$this->cnt;
        return $this->cnt;
    }
}
