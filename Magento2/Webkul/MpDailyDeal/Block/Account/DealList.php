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

class DealList extends \Webkul\Marketplace\Block\Product\Productlist
{
    /**
     * @return bool|\Magento\Ctalog\Model\ResourceModel\Product\Collection
     */
    public function getAllProducts()
    {
        $collection = parent::getAllProducts();
        $proIds[]=0;
        foreach ($collection as $product) {
            $proIds[] =  $product->getmageproductId();
        }
        $coll = $this->_productCollectionFactory->create()
                            ->addAttributeToSelect('*')
                            ->addFieldToFilter('entity_id', ['in' => $proIds])
                            ->addFieldToFilter('deal_status', 1)
                            ->addFieldToFilter('type_id', ['nin'=> ['grouped','configurable']])
                            ->addFieldToFilter('visibility', ['neq'=>1]);
        return $coll;
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
     * getDateTimeAsLocale
     * @param string $data in base Time zone
     * @return string date in current Time zone
     */
    public function getDateTimeAsLocale($data)
    {
        if ($data) {
            return $this->_localeDate->date(new \DateTime($data))->format('m/d/Y H:i:s');
        }
        return $data;
    }
}
