<?php
/**
 * Webkul Marketplace DailyDeals CatalogProductSaveBefore Observer.
 * @category  Webkul
 * @package   Webkul_MpDailyDeals
 * @author    Webkul
 * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpDailyDeal\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\RequestInterface;
use Webkul\MpDailyDeal\Helper\Data as HelperData;

class PreDispatchObserver implements ObserverInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var \Webkul\MpDailyDeals\Helper\Data
     */
    protected $helperData;

    /**
     * @param ProductRepositoryInterface $productRepository,
     * @param ScopeConfigInterface $scopeInterface,
     * @param Cart $cart,
     * @param RequestInterface $request,
     * @param HelperData $helperData
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ScopeConfigInterface $scopeInterface,
        Cart $cart,
        RequestInterface $request,
        HelperData $helperData
    ) {
        $this->productRepository = $productRepository;
        $this->scopeConfig = $scopeInterface;
        $this->cart = $cart;
        $this->helperData = $helperData;
        $this->request = $request;
    }

    /**
     * product save event handler.
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $routName = $this->request->getRouteName();
        if ($routName != 'adminhtml') {
            $items = $this->cart->getQuote()->getAllVisibleItems();
            $modEnable = $this->scopeConfig->getValue('mpdailydeals/general/enable');
            foreach ($items as $item) {
                $product = $this->productRepository->getById($item->getProductId());
                $dealStatus = $this->helperData->getProductDealDetail($product);
                if ($dealStatus === false && $modEnable) {
                    $item->setPrice($product->getPrice());
                    $item->setOriginalCustomPrice($product->getPrice());
                    $item->setCustomPrice($product->getPrice());
                    $item->getProduct()->setIsSuperMode(true);
                } elseif ($modEnable) {
                    $item->setPrice($product->getSpecialPrice());
                    $item->setOriginalCustomPrice($product->getSpecialPrice());
                    $item->setCustomPrice($product->getSpecialPrice());
                    $item->getProduct()->setIsSuperMode(true);
                }
            }
        }
        $this->cart->getQuote()->setTotalsCollectedFlag(false)->collectTotals();
        return $this;
    }
}
