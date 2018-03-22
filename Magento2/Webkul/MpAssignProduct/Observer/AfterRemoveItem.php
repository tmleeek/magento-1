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

class AfterRemoveItem implements ObserverInterface
{
    /**
     * @var QuoteCollection
     */
    protected $_quoteCollection;

    /**
     * @param QuoteCollection $quoteCollectionFactory
     */
    public function __construct(QuoteCollection $quoteCollectionFactory)
    {
        $this->_quoteCollection = $quoteCollectionFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $item = $observer->getEvent()->getData('quote_item');
        $itemId = (int) $item->getId();
        $collection = $this->_quoteCollection
                            ->create()
                            ->addFieldToFilter('item_id', $itemId);
        foreach ($collection as $item) {
            $item->delete();
        }
    }
}
