<?php
namespace Webkul\MpDailyDeal\Controller\Account;

/**
 * Webkul_MpDailyDeals Deal save controller
 * @category  Webkul
 * @package   Webkul_MpDailyDeals
 * @author    Webkul
 * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

use Magento\Framework\App\Action\Context;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Webkul\Marketplace\Model\ProductFactory as MarketplaceProductFactory;

class SaveDeal extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $_dirCurrencyFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var MarketplaceProductFactory
     */
    protected $_marketplaceProductFactory;

    /**
     * @param Context                                    $context
     * @param \Magento\Customer\Model\Session            $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Directory\Model\CurrencyFactory   $dirCurrencyFactory
     * @param ProductRepositoryInterface                 $productRepository
     * @param TimezoneInterface                          $localeDate,
     * @param MarketplaceProductFactory                  $mpProductFactory
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\CurrencyFactory $dirCurrencyFactory,
        ProductRepositoryInterface $productRepository,
        TimezoneInterface $localeDate,
        MarketplaceProductFactory $mpProductFactory
    ) {
        $this->_customerSession = $customerSession;
        $this->_storeManager = $storeManager;
        $this->_dirCurrencyFactory = $dirCurrencyFactory;
        $this->_productRepository = $productRepository;
        $this->_localeDate = $localeDate;
        $this->_marketplaceProductFactory = $mpProductFactory;
        parent::__construct($context);
    }

    /**
     * Deal save on product
     * @return \Magento\Backend\Model\View\Result\Redirect $resultRedirect
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        if ($data && isset($data['id'])) {
            $mpProduct = $this->_marketplaceProductFactory->create()->getCollection()
                              ->addFieldToFilter('mageproduct_id', $data['id'])
                              ->addFieldToFilter('seller_id', $this->_customerSession->getCustomerId())
                              ->getFirstItem();
            if ($mpProduct->getEntityId()) {
                $product = $this->_productRepository->getById($data['id'], true);
                $price = $data['deal_value'];
                if ($data['deal_discount_type'] == 'percent') {
                    $price = $product->getPrice() * ($data['deal_value']/100);
                    $discount = $data['deal_value'];
                } elseif ($product->getPrice() > 0) {
                    $data['deal_value'] = $this->converPriceInBaseCurrency($data['deal_value']);
                    $discount = ($data['deal_value']/$product->getPrice())*100;
                }
                $price = $this->converPriceInBaseCurrency($price);

                $data['deal_discount_percentage'] = round(100-$discount);

                //convert date time in Default Time Zone
                $data['deal_from_date'] = $this->converToTz(
                    $data['deal_from_date'],
                    $this->_localeDate->getConfigTimezone(),
                    $this->_localeDate->getDefaultTimezone()
                );
                $data['deal_to_date'] = $this->converToTz(
                    $data['deal_to_date'],
                    $this->_localeDate->getConfigTimezone(),
                    $this->_localeDate->getDefaultTimezone()
                );
                if ($product->getEntityId()) {
                    //To Do for default special price of magneto
                    if ($data['deal_status']) {
                        $product->setSpecialPrice(null);
                        $product->setSpecialToDate(date("m/d/Y", strtotime('-1 day')));
                        $product->setSpecialFromDate(date("m/d/Y", strtotime('-2 day')));
                    }
                    foreach ($data as $key => $value) {
                        $product->setData($key, $value);
                    }
                    $product->setSpecialFromDateIsFormated(true);
                    $this->_productRepository->save($product, true);
                }
            }
        }

        $this->messageManager->addSuccess(__('Deal information saved successfuly.'));
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setUrl($this->_url->getUrl('mpdailydeal/account/deallist'));
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

    /**
     * convert $price from current currency to base currency
     * @param decimal $price which we want to convert
     */
    protected function converPriceInBaseCurrency($price)
    {
        $store = $this->_storeManager->getStore();
        $currencyModel = $this->_dirCurrencyFactory->create();
        $baseCunyCode = $store->getBaseCurrencyCode();
        $cuntCunyCode = $store->getCurrentCurrencyCode();

        $allowedCurrencies = $currencyModel->getConfigAllowCurrencies();
        $rates = $currencyModel->getCurrencyRates($baseCunyCode, array_values($allowedCurrencies));

        $rates[$cuntCunyCode] = isset($rates[$cuntCunyCode]) ? $rates[$cuntCunyCode] : 1;
        return $price/$rates[$cuntCunyCode];
    }
}
