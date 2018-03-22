<?php
namespace Webkul\MpDailyDeal\Block\Plugin;

/**
 * Webkul MpDailyDeal ProductListUpdateForDeals plugin.
 * @category  Webkul
 * @package   Webkul_MpDailyDeals
 * @author    Webkul
 * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

use Magento\Catalog\Block\Product\ListProduct;

class ProductListUpdateForDeals
{
    /**
     * @var \Webkul\MpDailyDeals\Helper\Data
     */
    protected $mpDailyDealHelper;

    /**
     * @param Webkul\MpDailyDeals\Helper\Data $dailyDealHelper
     */
    public function __construct(
        \Webkul\MpDailyDeal\Helper\Data $mpDailyDealHelper
    ) {
        $this->mpDailyDealHelper = $mpDailyDealHelper;
    }
 
    /**
     * beforeGetProductPrice // update deal details before price render
     * @param ListProduct                    $list
     * @param \Magento\Catalog\Model\Product $product
     */
    public function beforeGetProductPrice(
        ListProduct $list,
        $product
    ) {
        $dealDetail = $this->mpDailyDealHelper->getProductDealDetail($product);
    }

    /**
     * aroundGetProductPrice // add clock data html product price
     * @param ListProduct                    $list
     * @param Object                         $proceed
     * @param \Magento\Catalog\Model\Product $product
     */
    public function aroundGetProductPrice(
        ListProduct $list,
        $proceed,
        $product
    ) {
        $dealDetail = $this->mpDailyDealHelper->getProductDealDetail($product);
        $dealDetailHtml = "";
        if ($dealDetail && $dealDetail['deal_status'] && isset($dealDetail['diff_timestamp'])) {
            $dealDetailHtml = '<div class="deal wk-daily-deal" data-deal-id="'.$product->getId().'" data-update-url="'
                                .$dealDetail['update_url'].'"><span class="price-box "><b class="price-label">OFF : '
                                    .$dealDetail['discount-percent'].' %</b></span>';
            $saveBox = isset($dealDetail['saved-amount'])? '<span class="save-box "><b class="save-label">Save : '
                                            .$dealDetail['saved-amount'].'</b></span>' : '' ;
            $saveBoxAvl = isset($dealDetail['saved-amount']) ? '' : 'notavilable';
            $dealDetailHtml = $dealDetailHtml.$saveBox.'<p class="wk_cat_count_clock '.$saveBoxAvl.'" data-stoptime="'
                                .$dealDetail['stoptime'].'" data-diff-timestamp ="'.$dealDetail['diff_timestamp']
                                .'"></p></div>';
        }
        return $proceed($product).$dealDetailHtml;
    }
}
