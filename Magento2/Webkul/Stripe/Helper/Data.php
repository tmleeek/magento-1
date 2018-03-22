<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Stripe
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Stripe\Helper;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Customer\Model\Session;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;

/**
 * Stripe data helper.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const METHOD_CODE = \Webkul\Stripe\Model\PaymentMethod::METHOD_CODE;

    const MAX_SAVED_CARDS = 30;

    const CARD_IS_ACTIVE = 1;

    const CARD_NOT_ACTIVE = 0;

    /**
     * @var Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * Customer session.
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var \Magento\Framework\Locale\Resolver
     */
    protected $_resolver;

    /**
     * @var \Magento\Framework\View\Element\Template
     */
    protected $template;

    /**
     * __construct
     *
     * @param Session                                         $customerSession
     * @param \Magento\Framework\App\Helper\Context           $context
     * @param FormKeyValidator                                $formKeyValidator
     * @param DateTime                                        $date
     * @param \Magento\Framework\ObjectManagerInterface       $objectManager
     * @param \Magento\Store\Model\StoreManagerInterface      $storeManager
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\View\Element\Template        $template
     * @param \Magento\Framework\Locale\Resolver              $resolver
     */
    public function __construct(
        Session $customerSession,
        \Magento\Framework\App\Helper\Context $context,
        FormKeyValidator $formKeyValidator,
        DateTime $date,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\View\Element\Template $template,
        \Magento\Framework\Locale\Resolver $resolver
    ) {
        $this->_date = $date;
        $this->_customerSession = $customerSession;
        $this->_objectManager = $objectManager;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_storeManager = $storeManager;
        $this->_productRepository = $productRepository;
        $this->template =  $template;
        $this->_resolver =  $resolver;
        parent::__construct($context);
    }

    /**
     * function to get Config Data.
     *
     * @return string
     */
    public function getConfigValue($field = false)
    {
        if ($field) {
            return $this->scopeConfig
                ->getValue(
                    'payment/'.self::METHOD_CODE.'/'.$field,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
        } else {
            return;
        }
    }

    /**
     * getIsActive check if payment method active.
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->getConfigValue('active');
    }

    /**
     * saveStripeCustomer save stripe customer id for future payment.
     *
     * @return string stripe customer id
     */
    public function saveStripeCustomer($stripeCustomerId, $paymentData = [], $isDuplicateCard=0)
    {
        if ($this->_customerSession->isLoggedIn()) {
            $paymentLabel = $paymentData['cardNumber'];
            if ($paymentData['type'] == 'alipay_account') {
                $paymentLabel = __("Alipay");
            } elseif ($paymentData['type'] == 'bitcoin') {
                $paymentLabel = __("Bitcoin");
            } else {
                $paymentLabel = "****".$paymentLabel;
            }
            $savedCards = $this->getSavedCards($paymentData['type']);
            $cardCount = 0;
            if ($savedCards) {
                $cardCount = $savedCards->getSize();
            } else {
                $cardCount = 0;
            }
            if ($cardCount < self::MAX_SAVED_CARDS) {
                $data = [
                    'customer_id' => $this->_customerSession->getCustomer()->getId(),
                    'is_active' => self::CARD_IS_ACTIVE,
                    'stripe_customer_id' => $stripeCustomerId,
                    'label' => $paymentLabel,
                    'type' => $paymentData['type'],
                    'brand'=> $paymentData['brand'],
                    'website_id' => $this->_storeManager->getStore()->getWebsiteId(),
                    'store_id' => $this->_storeManager->getStore()->getId(),
                    'fingerprint' => $paymentData['fingerprint'],
                    'expiry_month' => $paymentData['expiry_month'],
                    'expiry_year' => $paymentData['expiry_year'],
                ];
                try {
                    $model = $this->_objectManager
                        ->create('Webkul\Stripe\Model\StripeCustomer');
                    if ($isDuplicateCard) {
                        $model->load($paymentData['fingerprint'],'fingerprint');
                        $model->setExpiryMonth($paymentData['expiry_month']);
                        $model->setExpiryMonth($paymentData['expiry_month']);
                        $model->setStripeCustomerId($stripeCustomerId);
                        $model->save();
                    } else {
                        $model->setData($data);
                        $model->save();
                    }
                } catch (\Exception $e) {
                    return $e->getMessage();
                }
            }
        }
    }

    /**
     * getSavedCards function to get saved cards of the customer.
     *
     * @return Webkul\Stripe\Model\StripeCustomer
     */
    public function getSavedCards($type = null)
    {
        if ($this->_customerSession->isLoggedIn()) {
            $customerId = $this->_customerSession->getCustomer()->getId();
            $collection = $this->_objectManager
                ->create('Webkul\Stripe\Model\StripeCustomer')
                ->getCollection()
                ->addFieldToFilter('customer_id', ['eq' => $customerId]);
            if ($type) {
                $collection->addFieldToFilter('type', ['eq' => $type]);
            }
            if ($collection->getSize() > 0) {
                return $collection;
            } else {
                return false;
            }
        }
    }

    /**
     * getLogo get payment type logo
     *
     * @param  string $brand brand type like visa,master
     * @return string
     */
    public function getLogo($brand = '')
    {
        if ($brand == '') {
            return $this->template->getViewFileUrl('Webkul_Stripe/images/wkstripe/logos/placeholder.png');
        } else {
            return $this->template->getViewFileUrl('Webkul_Stripe/images/wkstripe/logos')."/".strtolower($brand).".png";
        }
    }

    /**
     * getMediaUrl get media url
     *
     * @return string
     */
    public function getMediaUrl()
    {
        return $this->_storeManager->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * getLocaleFromConfiguration get the current set locale code from configuration.
     *
     * @return String
     */
    public function getLocaleFromConfiguration()
    {
        return $this->_resolver->getLocale();
    }

    /**
     * getLocaleForStripe return the locale value exixt in stripe api
     * other wise return "auto"
     *
     * @return String
     */
    public function getLocaleForStripe()
    {
        $configLocale = $this->getLocaleFromConfiguration();
        $stripeLocale = $this->matchCodeSupportedByStripeApi($configLocale);
        return $stripeLocale;
    }

    /**
     * matchCodeSupportedByStripeApi matches the configuration locale to the locale exixt in strip api
     *
     * @param [String] $configLocale
     * @return String
     */
    public function matchCodeSupportedByStripeApi($configLocale)
    {
        switch ($configLocale) {
            case "zh":
                return $configLocale;
                break;
            case "da":
                return $configLocale;
                break;
            case "nl":
                return $configLocale;
                break;
            case "en":
                return $configLocale;
                break;
            case "fi":
                return $configLocale;
                break;
            case "fr":
                return $configLocale;
                break;
            case "de":
                return $configLocale;
                break;
            case "it":
                return $configLocale;
                break;
            case "ja":
                return $configLocale;
                break;
            case "no":
                return $configLocale;
                break;
            case "es":
                return $configLocale;
                break;
            case "sv":
                return $configLocale;
                break;
            default:
                return "auto";
                break;
        }
    }
}
