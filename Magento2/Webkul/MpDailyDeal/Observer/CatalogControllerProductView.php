<?php
namespace Webkul\MpDailyDeal\Observer;

/**
 * Webkul_MpDailyDeal Product View Observer.
 * @category  Webkul
 * @package   Webkul_MpDailyDeal
 * @author    Webkul
 * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

use Magento\Framework\Event\ObserverInterface;
use Webkul\MpDailyDeal\Helper\Data as MpDailyDealHelperData;

/**
 * Reports Event observer model.
 */
class CatalogControllerProductView implements ObserverInterface
{
     /**
      * @var MpDailyDealHelperData
      */
    protected $_mpDailyDealHelperData;

    /**
     * @param MpDailyDealHelperData $mpDailyDealHelperData
     */

    public function __construct(
        MpDailyDealHelperData $mpDailyDealHelperData
    ) {
        $this->_mpDailyDealHelperData = $mpDailyDealHelperData;
    }

    /**
     * View Catalog Product View observer.
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $dealDetail = $this->_mpDailyDealHelperData->getProductDealDetail($product);
        return $this;
    }
}
