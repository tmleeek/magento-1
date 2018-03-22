<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAssignProduct\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\MpAssignProduct\Model\ResourceModel\Quote\CollectionFactory as QuoteCollection;

class AfterPlaceOrder implements ObserverInterface
{
    /**
     * @var \Webkul\MpAssignProduct\Model\ItemsFactory
     */
    protected $_items;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_order;

    /**
     * @var QuoteCollection
     */
    protected $_quoteCollection;

    /**
     * @param \Webkul\MpAssignProduct\Model\ItemsFactory $itemsFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param QuoteCollection $quoteCollectionFactory
     */
    public function __construct(
        \Webkul\MpAssignProduct\Model\ItemsFactory $itemsFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        QuoteCollection $quoteCollectionFactory,
        \Webkul\MpAssignProduct\Helper\Data $helper,
        \Webkul\MpAssignProduct\Model\AssociatesFactory $associatesFactory
    )
    {
        $this->_items = $itemsFactory;
        $this->_order = $orderFactory;
        $this->_quoteCollection = $quoteCollectionFactory;
        $this->_assignHelper = $helper;
        $this->_associates = $associatesFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $orderIds = $observer->getEvent()->getData('order_ids');
        $orderId = $orderIds[0];
        $order = $this->_order->create()->load($orderId);
        $orderedItems = $order->getAllVisibleItems();
        foreach ($orderedItems as $item) {
            $qty = $item->getQtyOrdered();
            // $parentItem = ( $item->getParentItem() ? $item->getParentItem() : $item );
            $quoteItemId = $item->getQuoteItemId();
            $collection = $this->_quoteCollection
                                ->create()
                                ->addFieldToFilter('item_id', $quoteItemId);
            foreach ($collection as $row) {
                $assignId = $row->getAssignId();
                $childAssignId = $row->getChildAssignId();
                if ($assignId > 0) {
                    if ($childAssignId > 0) {
                        $productId = $this->_assignHelper->getProductFromItemId($quoteItemId);
                        $associateItem = $this->_associates
                                            ->create()
                                            ->getCollection()
                                            ->addFieldToFilter("parent_id", $assignId)
                                            ->addFieldToFilter("product_id", $productId)
                                            ->getFirstItem();
                        if ($associateItem) {
                            $qty = $associateItem->getQty() - $qty;
                            $associateItem->addData(['qty' => $qty])->setId($associateItem->getId())->save();
                        }
                    } else {
                        $assignData = $this->_items->create()->load($assignId);
                        $qty = $assignData->getQty() - $qty;
                        $assignData->addData(['qty' => $qty])->setId($assignId)->save();
                    }
                }
            }
        }
    }
}