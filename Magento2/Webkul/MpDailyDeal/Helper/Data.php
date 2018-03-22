<?php
namespace Webkul\MpDailyDeal\Helper;

/**
 * Webkul_MpDailyDeal data helper
 * @category  Webkul
 * @package   Webkul_MpDailyDeal
 * @author    Webkul
 * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Downloadable\Api\LinkRepositoryInterface;
use Webkul\Marketplace\Model\ProductFactory;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var PricingHelper
     */
    protected $pricingHelper;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var LinkRepositoryInterface
     */
    protected $linkRepositoryInterface;

    /**
     * @var \Webkul\Marketplace\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context,
     * @param ProductRepositoryInterface $productRepository,
     * @param TimezoneInterface $localeDate,
     * @param PricingHelper $pricingHelper,
     * @param LinkRepositoryInterface $linkRepositoryInterface
     * @param ProductFactory $productFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        TimezoneInterface $localeDate,
        ProductRepositoryInterface $productRepository,
        LinkRepositoryInterface $linkRepositoryInterface,
        PricingHelper $pricingHelper,
        ProductFactory $productFactory
    ) {
        $this->localeDate = $localeDate;
        $this->pricingHelper = $pricingHelper;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->linkRepositoryInterface = $linkRepositoryInterface;
        parent::__construct($context);
    }

    /**
     * @param object $product
     * @return array|flase []
     */
    public function getProductDealDetail($product)
    {
        $dealStatus = $product->getDealStatus();
        $content = false;
        $modEnable = $this->scopeConfig->getValue('mpdailydeals/general/enable');
        if ($dealStatus && $modEnable) {
            $content = ['deal_status' => $dealStatus];
            $today = $this->localeDate->date()->format('Y-m-d H:i:s');
            $dealFromDateTime = $this->localeDate->date($product->getDealFromDate())->format('Y-m-d H:i:s');
            $dealToDateTime = $this->localeDate->date($product->getDealToDate())->format('Y-m-d H:i:s');
            $difference = strtotime($dealToDateTime) - strtotime($today);
            $specialPrice = $product->getSpecialPrice();
            $price = $product->getPrice();
            if ($modEnable && $difference > 0 && $dealFromDateTime < $today) {
                $content['update_url'] = $this->_urlBuilder->getUrl('mpdailydeal/index/updatedealinfo');
                $content['stoptime'] = $product->getSpecialToDate();
                $content['diff_timestamp'] = $difference;
                $content['discount-percent'] = $product->getDealDiscountPercentage();
                if ($product->getTypeId() != 'bundle') {
                    $content['saved-amount'] = $this->pricingHelper->currency($price - $specialPrice, true, false);
                }

                $this->setPriceAsDeal($product);
            } elseif ($modEnable && $dealToDateTime < $today) {
                $product->setSpecialToDate(date("m/d/Y", strtotime('-1 day')));
                $product->setSpecialFromDate(date("m/d/Y", strtotime('-2 day')));
                $product->setDealStatus(0);
                $this->productRepository->save($product, true);
                $content = false;
            }
        }
        return $content;
    }

    /**
     * setPriceAsDeal
     * @param ProductRepositoryInterface $product
     * @return void
     */
    public function setPriceAsDeal($product)
    {
        $proType = $product->getTypeId();
        if ($product->getDealDiscountType() == 'percent') {
            if ($proType != 'bundle') {
                $price = $product->getPrice() * ($product->getDealValue()/100);
            } else {
                $price = $product->getDealValue();
                $product->setPrice(null);
            }
            $discount = $product->getDealValue();
        } else {
            $price = $product->getDealValue();
            $discount = ($product->getDealValue()/$product->getPrice())*100;
        }

        $product->setSpecialFromDate($product->getDealFromDate());
        $product->setSpecialToDate($product->getDealToDate());
        $product->setSpecialPrice($price);
        $product->setDealDiscountPercentage(round(100-$discount));
        if ($proType == 'downloadable') {
            $links = $this->linkRepositoryInterface->getLinksByProduct($product);
            $product->setDownloadableLinks($links);
        }
        if ($proType != 'bundle') {
            $this->productRepository->save($product, true);
        }
    }

    /**
     * @param object $sellerId
     * @return array
     */
    public function getSellerProductsIds($sellerId)
    {
        $proIds = false;
        $sellerColl = $this->productFactory->create()->getCollection()
                                ->addFieldToFilter('seller_id', ['eq' =>$sellerId]);
        if ($sellerColl->getSize()) {
            $proIds[] = 0;
            foreach ($sellerColl as $product) {
                $proIds[] = $product->getMageproductId();
            }
        }
        return $proIds;
    }
}
