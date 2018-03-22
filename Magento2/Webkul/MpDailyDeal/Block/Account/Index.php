<?php
 /**
  * Webkul_MpDailyDeal add Deal layout page.
  * @category  Webkul
  * @package   Webkul_MpDailyDeal
  * @author    Webkul
  * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
  * @license   https://store.webkul.com/license.html
  */
namespace Webkul\MpDailyDeal\Block\Account;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Webkul\MpDailyDeal\Helper\Data as MpDailyDealHelper;

class Index extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /** @var \Magento\Catalog\Model\Product */
    protected $_productlists;

    /**
     * @param Context                                   $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Customer\Model\Session           $customerSession
     * @param CollectionFactory                         $productCollectionFactory
     * @param PriceCurrencyInterface                    $priceCurrency
     * @param array                                     $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        ProductRepositoryInterface $productRepository,
        CollectionFactory $productCollectionFactory,
        PriceCurrencyInterface $priceCurrency,
        MpDailyDealHelper $mpDailyDealHelper,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_customerSession = $customerSession;
        $this->_productRepository = $productRepository;
        $this->_imageHelper = $context->getImageHelper();
        $this->_priceCurrency = $priceCurrency;
        $this->_mpDailyDealHelper = $mpDailyDealHelper;
        parent::__construct($context, $data);
    }

    /**
     * @param int $productId
     * @return url string add deal on product
     */

    public function getAddDealUrl($productId)
    {
        return $this->getUrl(
            'mpdailydeal/account/adddeal',
            [
                '_secure' => $this->getRequest()->isSecure(),
                'id'=>$productId
            ]
        );
    }

    /**
     * @return bool|\Magento\Ctalog\Model\ResourceModel\Product\Collection
     */
    public function getAllProducts()
    {
        $proIds = $this->_mpDailyDealHelper->getSellerProductsIds($this->_customerSession->getCustomer()->getId());
        $s = $this->getRequest()->getParam('s');
        $this->_productlists = $this->_productCollectionFactory->create()->addAttributeToSelect('*')
                                          ->addAttributeToFilter('entity_id', ['in' => $proIds])
                                          ->addAttributeToFilter('name', ['like' => '%'.$s.'%'])
                                          ->addAttributeToFilter('type_id', ['nin'=> ['grouped','configurable']])
                                          ->addAttributeToFilter('visibility', ['neq'=>1]);
        return $this->_productlists;
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
                'marketplace.product.list.pager'
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
     * Get formatted by price and currency
     *
     * @param   $price
     * @return  array || float
     */
    public function getFormatedPrice($price, $currency)
    {
        return $this->_priceCurrency->format($price, true, 2, null, $currency);
    }

    public function getProductData($id = '')
    {
        return $this->_productRepository->getById($id);
    }

    public function imageHelperObj()
    {
        return $this->_imageHelper;
    }
}
