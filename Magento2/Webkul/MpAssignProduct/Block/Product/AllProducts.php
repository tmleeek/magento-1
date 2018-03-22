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
namespace Webkul\MpAssignProduct\Block\Product;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollection;
use Webkul\MpAssignProduct\Model\ResourceModel\Items\CollectionFactory;

class AllProducts extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var ProductCollection
     */
    protected $_productCollection;

    /**
     * @var CollectionFactory
     */
    protected $_itemsCollection;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $_productList;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param ProductCollection $productCollectionFactory
     * @param CollectionFactory $itemsCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        ProductCollection $productCollectionFactory,
        CollectionFactory $itemsCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        array $data = []
    ) {
        $this->_storeManager = $context->getStoreManager();
        $this->_customerSession = $customerSession;
        $this->_productCollection = $productCollectionFactory;
        $this->_itemsCollection = $itemsCollectionFactory;
        $this->_resource = $resource;
        parent::__construct($context, $data);
    }

    /**
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('My Assigned Product List'));
    }

    /**
     * @return bool|\Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getAllProducts()
    {
        if (!$this->_productList) {
            $customerId = $this->_customerSession->getCustomerId();
            $sellercollection = $this->_itemsCollection
                                ->create()
                                ->addFieldToFilter('seller_id', $customerId);
            $sellercollection->setOrder('created_at', 'desc');
            $joinTable = $this->_resource
                            ->getTableName('marketplace_assignproduct_data');
            $sql = 'image.id = main_table.image';
            $sellercollection->getSelect()->joinLeft($joinTable.' as image', $sql, ['assign_id', 'value']);
            $sellercollection->addFilterToMap('id', 'main_table.id');
            $this->_productList = $sellercollection;
        }
        return $this->_productList;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getAllProducts()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'assignproduct.all.products.pager'
            )->setCollection(
                $this->getAllProducts()
            );
            $this->setChild('pager', $pager);
            $this->getAllProducts()->load();
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Get Current Currency Symbol
     *
     * @return string
     */
    public function getCurrencySymbol()
    {
        $symbol = $this->_storeManager->getStore()->getBaseCurrencyCode();
        return $symbol;
    }
}
