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
use Magento\Framework\App\RequestInterface;
use Webkul\MpAssignProduct\Model\ResourceModel\Items\CollectionFactory as ItemsCollection;

class DeleteProduct implements ObserverInterface
{
    /**
     * @var ItemsCollection
     */
    protected $_itemsCollection;

    /**
     * @param ItemsCollection $itemsCollectionFactory
     */
    public function __construct(ItemsCollection $itemsCollectionFactory)
    {
        $this->_itemsCollection = $itemsCollectionFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $productId = $observer->getProduct()->getId();
            $collection = $this->_itemsCollection
                                ->create()
                                ->addFieldToFilter('product_id', $productId);
            foreach ($collection as $item) {
                $item->delete();
            }
        } catch(\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
    }
}