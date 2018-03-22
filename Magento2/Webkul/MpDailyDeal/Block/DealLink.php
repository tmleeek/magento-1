<?php
 /**
  * Webkul_MpDailyDeal Deal Link Block.
  * @category  Webkul
  * @package   Webkul_MpDailyDeal
  * @author    Webkul
  * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
  * @license   https://store.webkul.com/license.html
  */
namespace Webkul\MpDailyDeal\Block;

class DealLink extends \Webkul\Marketplace\Block\Sellerblock
{
    /**
     * @return string
     */
    public function getShopUrl()
    {
        $shopUrl = $this->getRequest()->getParam('shop');
        if ($shopUrl == '') {
            $pro = parent::getProduct();
            $helper = $this->_objectManager->create('Webkul\Marketplace\Helper\Data');
            $marketplaceProduct = $helper->getSellerProductDataByProductId($pro['entity_id']);
            foreach ($marketplaceProduct as $value) {
                $sellerId = $value['seller_id'];
            }
            if ($sellerId) {
                $rowsocial = $helper->getSellerDataBySellerId($sellerId);
                foreach ($rowsocial as $value) {
                    $shopUrl = $value['shop_url'];
                }
            }
        }
        return $shopUrl;
    }
}
