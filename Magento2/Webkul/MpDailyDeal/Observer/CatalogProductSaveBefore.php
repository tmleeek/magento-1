<?php
/**
 * Webkul MpDailyDeal CatalogProductSaveBefore Observer.
 * @category  Webkul
 * @package   Webkul_MpDailyDeal
 * @author    Webkul
 * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpDailyDeal\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class CatalogProductSaveBefore implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param TimezoneInterface $localeDate,
     * @param ProductRepositoryInterface $productRepository,
     * @param RequestInterface $request,
     * @param ScopeConfigInterface $scopeInterface
     */
    public function __construct(
        TimezoneInterface $localeDate,
        ProductRepositoryInterface $productRepository,
        RequestInterface $request,
        ScopeConfigInterface $scopeInterface
    ) {
        $this->localeDate = $localeDate;
        $this->productRepository = $productRepository;
        $this->request = $request;
        $this->scopeConfig = $scopeInterface;
    }

    /**
     * product save event handler.
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getProduct();
        $productData = $this->request->getParam('product');
        $modEnable = $this->scopeConfig->getValue('mpdailydeals/general/enable');
        if ($product->getDealStatus() && $modEnable) {
            $configTimeZone = $this->localeDate->getConfigTimezone();
            $defaultTimeZone = $this->localeDate->getDefaultTimezone();
    
            $dealToDate = $productData['deal_to_date_tmp'];
            $dealFromDate = $productData['deal_from_date_tmp'];
            $dealToDate = $dealToDate == '' ? $this->converToTz(
                $productData['deal_to_date'],
                $configTimeZone,
                $defaultTimeZone
            ) : $dealToDate;
            $dealFromDate = $dealFromDate == '' ? $this->converToTz(
                $productData['deal_from_date'],
                $configTimeZone,
                $defaultTimeZone
            ) : $dealFromDate;
    
            if ($dealToDate != '' && $dealFromDate != '') {
                $product->setDealFromDate($dealFromDate);
                $product->setDealToDate($dealToDate);
            }
        } elseif ($product->getEntityId() && $modEnable) {
            $proDealStatus = $this->productRepository->getById($product->getEntityId())->getDealStatus();
            //To Do for default special price of magneto
            if ($proDealStatus) {
                $product->setSpecialToDate('');
                $product->setSpecialFromDate('');
                $product->setSpecialPrice(null);
                $product->setDealDiscountPercentage('');
            }
        }
        return $this;
    }

    /**
     * convert Datetime from one zone to another
     * @param string $dateTime which we want to convert
     * @param string $fromTz timezone from which we want to convert
     * @param string $toTz timezone in which we want to convert
     */
    protected function converToTz($dateTime = "", $fromTz = '', $toTz = '')
    {
        // timezone by php friendly values
        $date = new \DateTime($dateTime, new \DateTimeZone($fromTz));
        $date->setTimezone(new \DateTimeZone($toTz));
        $dateTime = $date->format('m/d/Y H:i:s');
        return $dateTime;
    }
}
