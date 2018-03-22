<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Stripe
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Stripe\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;

class StripeConfigProvider implements ConfigProviderInterface
{
    /**
     * @var string[]
     */
    protected $_methodCode = PaymentMethod::METHOD_CODE;

    /**
     * $_method.
     *
     * @var Magento\Payment\Helper\Data
     */
    protected $_method;

    /**
     * $_helper.
     *
     * @var \Webkul\Stripe\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;


    /**
     * @var Escaper
     */
    protected $_escaper;

    /**
     * @var \Magento\Framework\View\Element\Template
     */
    protected $template;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_session;

    /**
     * __construct constructor.
     *
     * @param PaymentHelper                                      $paymentHelper
     * @param \Webkul\Stripe\Helper\Data                         $helper
     * @param \Magento\Framework\UrlInterface                    $urlBuilder
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param \Magento\Framework\View\Element\Template           $template
     * @param Escaper$escaper
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        \Webkul\Stripe\Helper\Data $helper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Element\Template $template,
        Escaper $escaper,
        \Magento\Framework\Session\SessionManagerInterface $session
    ) {
        $this->_method = $paymentHelper->getMethodInstance($this->_methodCode);  //it seems no needed. so query
        $this->_helper = $helper;
        $this->_urlBuilder = $urlBuilder;
        $this->_storeManager = $storeManager;
        $this->template = $template;
        $this->_escaper = $escaper;
        $this->_session = $session;
    }

    /**
     * getConfig function to return cofig data to payment renderer.
     *
     * @return []
     */
    public function getConfig()
    {
        if (!$this->_helper->getIsActive()) {
            return [];
        }

        /*
         * [$mediaUrl base media folder to get image.
         *
         * @var [type]
         */
        $imageOnForm = $this->_helper->getConfigValue('image_on_form');
        if ($imageOnForm) {
            $mediaImageUrl = $this->_storeManager->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'wkstripe/config/';
            $mediaImageUrl .= $imageOnForm;
        } else {
            $mediaImageUrl = $this->template->getViewFileUrl('Webkul_Stripe/images/wkstripe/config/stripe-logo.png');
        }
        /**
         * $config array to pass config data to payment renderer component.
         *
         * @var array
         */
        $config = [
            'payment' => [
                'stripe' => [
                    'title' => $this->_helper->getConfigValue('title'),
                    'debug' => $this->_helper->getConfigValue('debug'),
                    'api_key' => $this->_helper->getConfigValue('api_key'),
                    'api_publish_key' => $this->_helper->getConfigValue('api_publish_key'),
                    'name_on_form' => $this->_helper->getConfigValue('name_on_form'),
                    'image_on_form' => $mediaImageUrl,
                    'activebitcoin' => $this->_helper->getConfigValue('activebitcoin'),
                    'order_status' => $this->_helper->getConfigValue('order_status'), //it seems no needed. so query
                    'payment_action' => $this->_helper->getConfigValue('payment_action'),
                    'min_order_total' => $this->_helper->getConfigValue('min_order_total'),
                    'max_order_total' => $this->_helper->getConfigValue('max_order_total'),
                    'sort_order' => $this->_helper->getConfigValue('sort_order'),
                    'method' => $this->_methodCode,
                    'saved_cards' => $this->getSavedCards(),
                    'currency' => $this->_storeManager->getStore()->getCurrentCurrency()->getCode(),
                    'mediaUrl' => $this->template->getViewFileUrl('Webkul_Stripe/images/wkstripe'),
                    'alipay' => (boolean)$this->_helper->getConfigValue('alipay'),
                    'bitcoin' => (boolean)$this->_helper->getConfigValue('bitcoin'),
                    'locale' => $this->_helper->getLocaleForStripe(),
                    'zipCode' => (boolean)$this->_helper->getConfigValue('zip_code_validation'),
                    'billingAddress' => (boolean)$this->_helper->getConfigValue('shipping_address'),
                    'shippingAddress' => (boolean)$this->_helper->getConfigValue('billing_address'),
                    'alipay_source' => $this->_session->getWkStripeAlipaySource(),
                    'alipay_client_secret' => $this->_session->getWkStripeAlipayClientSecret()
                ],
            ],
        ];
        return $config;
    }

    /**
     * getSavedCards function to get customers cards json data
     *
     * @return json
     */
    public function getSavedCards()
    {
        $cardsArray = [];
        $cards = $this->_helper->getSavedCards();
        if ($cards) {
            foreach ($cards as $card) {
                $label = "<div class=\"stripe_logo_label\"><img src='".$this->_helper->getLogo(str_replace(" ", "_", $card->getData("brand")))."' /> ".$card->getData('label')."</div>";
                array_push(
                    $cardsArray,
                    [
                        'exp_month' => $card->getExpiryMonth(),
                        'exp_year' => $card->getExpiryYear(),
                        'brand' => $card->getBrand(),
                        'stripe_customer_id' => $card->getStripeCustomerId(),
                        'label' => $label,
                    ]
                );
            }
        }
        return json_encode($cardsArray);
    }
}
