<?php
namespace Webkul\MpDailyDeal\Block\Product;

/**
 * Webkul_DailyDeals ListProduct collection block.
 * @category  Webkul
 * @package   Webkul_DailyDeals
 * @author    Webkul
 * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Reports\Model\ResourceModel\Product as ReportsProducts;
use Magento\Sales\Model\ResourceModel\Report\Bestsellers as SalesReportFactory;

class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{
    /**
     * @param Context $context
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Catalog\Model\Layer\Resolver     $layerResolver
     * @param CategoryRepositoryInterface               $categoryRepository
     * @param \Magento\Framework\Url\Helper\Data        $urlHelper
     * @param CollectionFactory                         $productFactory
     * @param ReportsProducts\CollectionFactory         $reportproductsFactory,
     * @param SalesReportFactory\CollectionFactory      $salesReportFactory
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        CollectionFactory $productFactory,
        ReportsProducts\CollectionFactory $reportproductsFactory,
        SalesReportFactory\CollectionFactory $salesReportFactory
    ) {
        $this->_productFactory = $productFactory;
        $this->_reportproductsFactory = $reportproductsFactory;
        $this->_salesReportFactory = $salesReportFactory;
        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper
        );
        $this->_today = $this->_localeDate->date()->format('Y/m/d H:i:s');
    }
    
    /**
     * @return Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    public function _getProductCollection()
    {
        $data = parent::_getProductCollection();
        $data = $data->addAttributeToFilter('deal_status', 1);
             /*->addAttributeToFilter('special_from_date', ['lt'=>$this->_today])
             ->addAttributeToFilter('special_to_date', ['gt'=>$this->_today]);*/
        return $data;
    }

    /**
     * getTopDealsOfDay
     * @return CollectionFactory top 5 products on best deal
     */
    public function getTopDealsOfDay()
    {
        return $this->_productFactory
                        ->create()
                        ->addAttributeToSelect('*')
                        ->addAttributeToFilter('deal_status', 1)
                        /*->addAttributeToFilter(
                            'special_from_date', 
                            ['lt'=>$this->_today]
                        )->addAttributeToFilter(
                            'special_to_date', 
                            ['gt'=>$this->_today]
                        )*/->setOrder(
                            'deal_discount_percentage',
                            'DESC'
                        )->setPageSize(5);
    }

    /**
     * getDealProductImage
     * @param Magento\Catalog\Model\Product $product
     * @return string product image url
     */
    public function getDealProductImage($product)
    {
        return $this->_imageHelper
                        ->init($product, 'category_page_grid')
                            ->constrainOnly(false)
                            ->keepAspectRatio(true)
                            ->keepFrame(false)
                            ->resize(400)
                            ->getUrl();
    }

    /**
     * getTopDealViewsProduct
     * @return ReportsProducts // top 5 viewed product
     */
    public function getTopDealViewsProduct()
    {
        return $this->_reportproductsFactory
                        ->create()
                        ->addAttributeToSelect('*')
                        ->addViewsCount()
                        ->setStoreId(0)
                        ->addStoreFilter(0)
                        ->addAttributeToFilter('deal_status', 1)
                        /*->addAttributeToFilter(
                            'special_from_date', 
                            ['lt'=>$this->_today]
                        )->addAttributeToFilter(
                            'special_to_date', 
                            ['gt'=>$this->_today]
                        )*/->setPageSize(5);
    }

    /**
     * getDealViewsProduct
     * @return SalesReportFactory //best sold product
     */
    public function getTopSaleProduct()
    {
        return $this->_salesReportFactory->create()->setModel('Magento\Catalog\Model\Product')
                                            ->addStoreFilter(0)->setPageSize(5);
    }
}
