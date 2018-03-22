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
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory;

class ProductList extends \Magento\Framework\View\Element\Template
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
     * @var \Webkul\MpAssignProduct\Helper\Data
     */
    protected $_assignHelper;

    /**
     * @var ProductCollection
     */
    protected $_productCollection;

    /**
     * @var CollectionFactory
     */
    protected $_mpProductCollection;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $_productStatus;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_productVisibility;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $_productList;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Webkul\MpAssignProduct\Helper\Data $helper
     * @param ProductCollection $productCollectionFactory
     * @param CollectionFactory $mpProductCollectionFactory
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\MpAssignProduct\Helper\Data $helper,
        ProductCollection $productCollectionFactory,
        CollectionFactory $mpProductCollectionFactory,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        array $data = []
    ) {
        $this->_storeManager = $context->getStoreManager();
        $this->_customerSession = $customerSession;
        $this->_assignHelper = $helper;
        $this->_productCollection = $productCollectionFactory;
        $this->_mpProductCollection = $mpProductCollectionFactory;
        $this->_productStatus = $productStatus;
        $this->_productVisibility = $productVisibility;
        parent::__construct($context, $data);
    }

    /**
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('My Product List'));
    }

    /**
     * @return bool|\Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getAllProducts()
    {

        if (!$this->_productList) {

            $queryString = $this->_assignHelper->getQueryString();

            if (true || $queryString != '') {
                $customerId = $this->_customerSession->getCustomerId();
                $sellercollection = $this->_mpProductCollection
                                        ->create()
                                        ->addFieldToFilter('seller_id', ['neq' => $customerId]);
                $products = [];
                foreach ($sellercollection as $data) {
                    array_push($products, $data->getMageproductId());
                }
                $allowedTypes = $this->_assignHelper->getAllowedProductTypes();
                $collection = $this->_productCollection
                                    ->create()
                                    ->addFieldToSelect('*')
                                    ->addFieldToFilter('name', ['like' => '%'.$queryString.'%']);
                $collection->addFieldToFilter('type_id', ['in' => $allowedTypes]);
                $collection->addAttributeToFilter('status', ['in' => $this->_productStatus->getVisibleStatusIds()]);
                
                $collection->setVisibility($this->_productVisibility->getVisibleInSiteIds());
                $collection->setOrder('created_at', 'desc');
                return $collection;
            } else {
                $collection = $this->_productCollection
                                    ->create()
                                    ->addFieldToSelect('*')
                                    ->addFieldToFilter('entity_id', 0);
            }
            $this->_productList = $collection;
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
                'mpassignproduct.product.list.pager'
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
