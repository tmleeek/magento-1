<?php
/**
* Webkul Software.
*
* @category Webkul
*
* @author Webkul
* @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
* @license https://store.webkul.com/license.html
*/

namespace Webkul\Stripe\Model;

use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Framework\Exception\LocalizedException;

class PaymentMethod extends AbstractMethod
{
    const METHOD_CODE = 'stripe';

    /**
     * @var string
     */
    protected $_code = self::METHOD_CODE;

    /**
     * @var bool
     */
    protected $_isGateway = true;

    /**
     * Availability option.
     *
     * @var bool
     */
    protected $_isInitializeNeeded = false;

    /**
     * @var bool
     */
    protected $_canAuthorize = true;

    /**
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * @var bool
     */
    protected $_canCapturePartial = true;

    /**
     * @var bool
     */
    protected $_canRefund = true;

    /**
     * @var bool
     */
    protected $_canVoid = true;

    /**
     * @var bool
     */
    protected $_canUseInternal = false;

    /**
     * @var bool
     */
    protected $_canUseCheckout = true;

    /**
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * @var \Webkul\Stripe\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var string
     */
    protected $_infoBlockType = \Webkul\Stripe\Block\Payment\Info::class;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_session;

    /**
     * array to check small currency
     */

     /**
      * __construct constructor.
      *
      * @param \Magento\Framework\Model\Context                        $context
      * @param \Magento\Framework\Registry                             $registry
      * @param \Magento\Framework\Api\ExtensionAttributesFactory       $extensionFactory
      * @param \Magento\Framework\Api\AttributeValueFactory            $customAttributeFactory
      * @param \Magento\Payment\Helper\Data                            $paymentData
      * @param \Magento\Framework\App\Config\ScopeConfigInterface      $scopeConfig
      * @param \Magento\Payment\Model\Method\Logger                    $logger
      * @param \Webkul\Stripe\Helper\Data                              $helper
      * @param \Magento\Framework\Session\SessionManagerInterface      $session
      * @param \Webkul\Stripe\Model\StripeCustomerFactory              $stripeCustomer
      * @param \Magento\Customer\Model\Session                         $customerSession
      * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
      * @param \Magento\Framework\Data\Collection\AbstractDb           $resourceCollection
      * @param array                                                   $data
      */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Webkul\Stripe\Helper\Data $helper,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Webkul\Stripe\Model\StripeCustomerFactory $stripeCustomer,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );

        $this->_helper = $helper;
        $this->_stripeCustomer = $stripeCustomer;
        $this->_customerSession = $customerSession;
        $this->_session = $session;
        /*
         * set api key for payment  >> sandbox api key or live api key
         */
        if ($this->getDebugFlag()) {
            \Stripe\Stripe::setApiKey($this->getConfigData('api_key'));
        } else {
            \Stripe\Stripe::setApiKey($this->getConfigData('api_key'));
        }
    }

    /**
     * Authorizes specified amount.
     *
     * @param InfoInterface $payment
     * @param float         $amount
     *
     * @return $this
     *
     * @throws LocalizedException
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $smallcurrencyarray = ["bif", "clp", "djf", "gnf", "jpy", "kmf", "krw", "mga", "pyg", "rwf",
                                    "vnd", "vuv", "xaf", "xof", "xpf"];
        $token = $this->getInfoInstance()->getAdditionalInformation('token');
        $sType = $this->getInfoInstance()->getAdditionalInformation('stype');
        $email = $this->getInfoInstance()->getAdditionalInformation('email');
        $address = $this->getInfoInstance()->getAdditionalInformation('address');
        $bitCoinAmount = 0;
        $bitcoinCurrency = '';
        $isCapture = false;
        if ($sType == 'bitcoin') {
            $bitCoinAmount = $this->getInfoInstance()->getAdditionalInformation('bitcoinAmount');
            $bitCoinCurrency = $this->getInfoInstance()->getAdditionalInformation('bitcoinCurrency');
            $isCapture = true;
        }
        $brand = $this->getInfoInstance()->getAdditionalInformation('brand');

        $paymentSource = $this->getInfoInstance()->getAdditionalInformation('paymentSource');
        $cardNumber = $this->getInfoInstance()->getAdditionalInformation('cardNumber');
        $stripeCustomerId = $this->getInfoInstance()->getAdditionalInformation('stripeCustomerId');
        $saveCardForCustomer = $this->getInfoInstance()->getAdditionalInformation('saveCardForCustomer');
        $paymentDetailsArray = '';
        $order = $payment->getOrder();

        if(in_array(strtolower($order->getStore()->getCurrentCurrencyCode()), $smallcurrencyarray)) {
            $stripeAmount = $amount;
        } else {
            $stripeAmount = $amount * 100;
        }

        if ($paymentSource == 'token' && $saveCardForCustomer) {
            $stripeCustomerId = $this->createStripeCustomer($token, $cardNumber, $sType, $brand, $email, $address, $order);
            $paymentDetailsArray = [
                    'amount' => $stripeAmount, // amount in cents, again
                    'currency' => strtolower($order->getBaseCurrencyCode()),
                    'customer' => $stripeCustomerId,
                    'description' => sprintf('#%s, %s', $order->getIncrementId(), $order->getCustomerEmail()),
                    'capture' => $isCapture,
                ];
        } elseif ($paymentSource == 'token' && !$saveCardForCustomer) {
            $paymentDetailsArray = [
                    'amount' => $stripeAmount,
                    'currency' => strtolower($order->getBaseCurrencyCode()),
                    'source' => $token,
                    'description' => sprintf('#%s, %s', $order->getIncrementId(), $order->getCustomerEmail()),
                    'capture' => $isCapture,
                ];
        } else {
            $paymentDetailsArray = [
                    'amount' => $stripeAmount,
                    'currency' => strtolower($order->getBaseCurrencyCode()),
                    'customer' => $stripeCustomerId,
                    'description' => sprintf('#%s, %s', $order->getIncrementId(), $order->getCustomerEmail()),
                    'capture' => $isCapture,
                ];
        }
        try {
            $charge = [];
            if ($sType == 'alipay') {
                $source = \Stripe\Source::retrieve($paymentDetailsArray['source']);
                if ($source->status == "chargeable") {
                    $customer = \Stripe\Customer::create([
                    "email" => $order->getCustomerEmail(),
                    "source" => $paymentDetailsArray['source'],
                    ]);

                    $charge = \Stripe\Charge::create(array(
                    "amount" => $paymentDetailsArray['amount'],
                    "currency" => $paymentDetailsArray['currency'],
                    "customer" => $customer->id,
                    "source" => $paymentDetailsArray['source'],
                    ));
                }
                if ($source->status == "failed") {
                    throw new LocalizedException(
                        __(
                            'There was an error capturing the transaction, please contact admin'
                        )
                    );
                }
            } else {
                $charge = \Stripe\Charge::create(
                    $paymentDetailsArray
                );
            }
            $charge = (array) $charge;
            foreach ($charge as $key => $value) {
                if (strpos($key, 'values') !== false) {
                    $charge = $value;
                    if(in_array(strtolower($order->getStore()->getCurrentCurrencyCode()), $smallcurrencyarray)) {
                        $charge['amount'] = $charge['amount'];
                    } else {
                        $charge['amount'] = $charge['amount'] / 100;
                    }
                    $charge['source'] = json_encode((array) $charge['source']);
                    $charge['metadata'] = json_encode((array) $charge['metadata']);
                    $charge['refunds'] = json_encode((array) $charge['refunds']);
                    $charge['fraud_details'] = json_encode((array) $charge['fraud_details']);
                }
            }
            $this->getInfoInstance()->setAdditionalInformation('stripe_charge', json_encode($charge, true));
            // if ($sType == 'bitcoin' && $this->getConfigData('payment_action') == 'authorize') {
            //     $this->capture($payment, $amount);
            // }
        } catch (\Stripe\Error $e) {
            $this->_debug([$e->getMessage()]);
            throw new LocalizedException(
                __(
                    'There was an error capturing the transaction, please contact admin'
                )
            );
        } catch (\Exception $e) {
            $this->_debug([$e->getMessage()]);
            throw new LocalizedException(
                __(
                    'There was an error capturing the transaction, please contact admin'
                )
            );
        }

        return $this;
    }

    /**
     * Captures specified amount.
     *
     * @param InfoInterface $payment
     * @param float         $amount
     *
     * @return $this
     *
     * @throws LocalizedException
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $smallcurrencyarray = ["bif", "clp", "djf", "gnf", "jpy", "kmf", "krw", "mga", "pyg", "rwf",
                                    "vnd", "vuv", "xaf", "xof", "xpf"];
        $order = $payment->getOrder();
        try {
            if ($this->getConfigPaymentAction() == 'authorize_capture') {
                $this->authorize($payment, $amount);
            }
            $charge = (array) json_decode($this->getInfoInstance()->getAdditionalInformation('stripe_charge'));
            if (isset($charge['id'])) {
                $ch = \Stripe\Charge::retrieve($charge['id']);
                if (!isset($charge['captured']) || !$charge['captured'])
                    $ch->capture();
                $chargeData = (array) $ch;
                foreach ($chargeData as $key => $value) {
                    if (strpos($key, 'values') !== false) {
                        $chargeData = $value;
                        if(in_array(strtolower($order->getStore()->getCurrentCurrencyCode()), $smallcurrencyarray)) {
                            $chargeData['amount'] = $chargeData['amount'];
                        } else {
                            $chargeData['amount'] = $chargeData['amount'] / 100;
                        }
                        $chargeData['source'] = json_encode((array) $chargeData['source']);
                        $chargeData['metadata'] = json_encode((array) $chargeData['metadata']);
                        $chargeData['refunds'] = json_encode((array) $chargeData['refunds']);
                        $chargeData['fraud_details'] = json_encode((array) $chargeData['fraud_details']);
                    }
                }
                if ($chargeData['status'] != 'paid' && $chargeData['failure_message'] != '') {
                    throw new LocalizedException(
                            __(
                                'There was an error capturing the transaction: %1',
                                $chargeData['failure_message']
                            )
                        );
                }
            } elseif ($charge['status'] != 'paid' && $charge['failure_message'] != '') {
                throw new LocalizedException(
                        __(
                            'There was an error capturing the transaction, please contact admin'
                        )
                    );
            }
        } catch (\Stripe\Error $e) {
            $this->_debug([$e->getMessage()]);
            throw new LocalizedException(
                __(
                    'There was an error capturing the transaction, please contact admin'
                )
            );
        }

        $payment
            ->setTransactionId($charge['id'])
            ->setIsTransactionClosed(1)
            ->setTransactionAdditionalInfo(\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS, $chargeData);
    }

    /**
     * refund refund the transaction.
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float                                $amount
     *
     * @return Webkul\Stripe\Model\PaymentMethod
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $smallcurrencyarray = ["bif", "clp", "djf", "gnf", "jpy", "kmf", "krw", "mga", "pyg", "rwf",
                                    "vnd", "vuv", "xaf", "xof", "xpf"];
        $transactionId = $payment->getParentTransactionId();
        $order = $payment->getOrder();
        $billing = $order->getBillingAddress()->getData();
        $transactioninfo = [];
        if(in_array(strtolower($order->getStore()->getCurrentCurrencyCode()), $smallcurrencyarray)) {
            $amount = $amount;
        } else {
            $amount = $amount * 100;
        }
        try {
            $chargeData = \Stripe\Charge::retrieve($transactionId);
            $charge = $chargeData->refund(
                [
                    'amount' => $amount,
                ]
            );
            $charge = (array) $charge;

            foreach ($charge as $key => $value) {
                if (strpos($key, 'values') !== false) {
                    $charge = $value;
                    if(in_array(strtolower($order->getStore()->getCurrentCurrencyCode()), $smallcurrencyarray)) {
                        $transactioninfo['amount'] = $charge['amount'];
                        $transactioninfo['amount_refunded'] = $charge['amount_refunded'];
                    } else {
                        $transactioninfo['amount'] = $charge['amount'] / 100;
                        $transactioninfo['amount_refunded'] = $charge['amount_refunded'] / 100;
                    }
                    $transactioninfo['currency'] = $charge['currency'];
                    $transactioninfo['customer'] = isset($charge['customer'])?$charge['customer']:"";
                    $transactioninfo['description'] = isset($charge['description'])?$charge['description']:"";
                    $transactioninfo['status'] = isset($charge['status'])?$charge['status']:"";
                    $transactioninfo['metadata'] = json_encode((array) $charge['metadata'], true);
                    $transactioninfo['outcome'] = json_encode((array) $charge['outcome'], true);
                    $transactioninfo['refunds'] = json_encode((array) $charge['refunds']);
                    $transactioninfo['source'] = json_encode((array) $charge['source'], true);

                }
            }
        } catch (\Stripe\Error $e) {
            $this->_debug([$e->getMessage()]);
            throw new LocalizedException(
                __(
                    'There was an error refunding the transaction , please contact admin'
                )
            );
        }

        $payment
            ->setTransactionId($charge['id'].'-'.\Magento\Sales\Model\Order\Payment\Transaction::TYPE_REFUND)
            ->setParentTransactionId($transactionId)
            ->setIsTransactionClosed(1)
            ->setShouldCloseParentTransaction(1)
            ->setTransactionAdditionalInfo(\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS, $transactioninfo);

        return $this;
    }

    /**
     * createStripeCustomer create customer on stripe.
     *
     * @param string $token stripe card token
     *
     * @return string stripe customer id
     */


    public function createStripeCustomer($token, $cardNumber = null, $sType = 'card', $brand = '', $email, $address, $order)
    {
        $shippingAddress = $this->getShippingAddress($address, $order);
        $billingAddress = $this->getBillingAddress($address, $order);
        try {
            /**
             * $customer stripe customer object stores stripe customer info.
             */
            $customer = \Stripe\Customer::create(

                [
                  'source' => $token,
                  'description' => 'customer created for payment',
                  'email' => $email,
                  'shipping' => $shippingAddress
                ]
            );
            if ($customer->id) {
                $cardObject = $this->getCardData($token);
                $fingerPrint = $cardObject->card->fingerprint;
                $expMonth = $cardObject->card->exp_month;
                $expYear = $cardObject->card->exp_year;
                $isDuplicateCard = $this->checkDuplicacyOfFingurePrint($fingerPrint, $this->_customerSession->getCustomer()->getId());
                $paymentTypeData['type'] = $sType;
                $paymentTypeData['cardNumber'] = $cardNumber;
                $paymentTypeData['brand'] = $brand;
                $paymentTypeData['fingerprint'] = $fingerPrint;
                $paymentTypeData['expiry_month'] = $expMonth;
                $paymentTypeData['expiry_year'] = $expYear;

                /*
                 * save stripe card info in data base
                 */
                // if (!$isDuplicateCard) {
                    $this->_helper->saveStripeCustomer($customer->id, $paymentTypeData, $isDuplicateCard);
                // }
                return $customer->id;
            }
            return false;
        } catch (\Stripe\Error $e) {
            throw new LocalizedException(

                __(
                    'There was an error capturing the transaction: %1',
                    $e->getMessage()
                )
            );
        } catch (\Exception $e) {
            throw new LocalizedException(

                __(
                    'There was an error capturing the transaction: %1',
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * Do not validate payment form using server methods.
     *
     * @return bool
     */
    public function validate()
    {
        return true;
    }

    /**
     * isAvailable check if function is available or not.
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return parent::isAvailable($quote);
    }

    /**
     * Assign corresponding data.
     *
     * @param \Magento\Framework\DataObject|mixed $data
     *
     * @return $this
     *
     * @throws LocalizedException
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        parent::assignData($data);
        $infoInstance = $this->getInfoInstance();
        $additionalData = $data->getAdditionalData();
        $this->_session->setWkStripeAlipaySource(null);
        $this->_session->setWkStripeAlipayClientSecret(null);
        if (!is_array($data->getAdditionalData())) {
            if ($data->getData('token') || $data->getData('stripeCustomerId')) {
                $switcher = $data->getData('sType');
                if ($data->getData('stripeCustomerId')) {
                    $switcher = $data->getData('paymentSource');
                }
                $address = json_decode($data->getData('address'),true);
                switch ($switcher) {
                    case 'customer' :
                        $infoInstance->setAdditionalInformation('paymentSource', $data->getData('paymentSource'));
                        $infoInstance->setAdditionalInformation('stripeCustomerId', $data->getData('stripeCustomerId'));
                        $infoInstance->setAdditionalInformation('saveCardForCustomer', $data->getData('saveCardForCustomer'));
                        break;
                    case 'card':
                        $infoInstance->setAdditionalInformation('stype', $data->getData('sType'));
                        $infoInstance->setAdditionalInformation('brand', $data->getData('brand'));
                        $infoInstance->setAdditionalInformation('token', $data->getData('token'));
                        $infoInstance->setAdditionalInformation('paymentSource', $data->getData('paymentSource'));
                        $infoInstance->setAdditionalInformation('cardNumber', $data->getData('cardNumber'));
                        $infoInstance->setAdditionalInformation('stripeCustomerId', $data->getData('stripeCustomerId'));
                        $infoInstance->setAdditionalInformation('saveCardForCustomer', $data->getData('saveCardForCustomer'));
                        $infoInstance->setAdditionalInformation('email', $data->getData('email'));
                        $infoInstance->setAdditionalInformation('address', $address);
                        break;
                    case 'alipay_account':
                        $infoInstance->setAdditionalInformation('stype', $data->getData('sType'));
                        $infoInstance->setAdditionalInformation('brand', $data->getData('brand'));
                        $infoInstance->setAdditionalInformation('token', $data->getData('token'));
                        $infoInstance->setAdditionalInformation('paymentSource', $data->getData('paymentSource'));
                        $infoInstance->setAdditionalInformation('stripeCustomerId', $data->getData('stripeCustomerId'));
                        $infoInstance->setAdditionalInformation('saveCardForCustomer', $data->getData('saveCardForCustomer'));
                        $infoInstance->setAdditionalInformation('email', $data->getData('email'));
                        $infoInstance->setAdditionalInformation('address', $address);
                        break;
                    case 'alipay':
                        $infoInstance->setAdditionalInformation('stype', $data->getData('sType'));
                        $infoInstance->setAdditionalInformation('brand', $data->getData('brand'));
                        $infoInstance->setAdditionalInformation('token', $data->getData('token'));
                        $infoInstance->setAdditionalInformation('paymentSource', $data->getData('paymentSource'));
                        $infoInstance->setAdditionalInformation('stripeCustomerId', $data->getData('stripeCustomerId'));
                        $infoInstance->setAdditionalInformation('saveCardForCustomer', $data->getData('saveCardForCustomer'));
                        $infoInstance->setAdditionalInformation('email', $data->getData('email'));
                        $infoInstance->setAdditionalInformation('address', $address);
                        break;
                    case 'bitcoin':
                        $infoInstance->setAdditionalInformation('stype', $data->getData('sType'));
                        $infoInstance->setAdditionalInformation('brand', $data->getData('brand'));
                        $infoInstance->setAdditionalInformation('token', $data->getData('token'));
                        $infoInstance->setAdditionalInformation('paymentSource', $data->getData('paymentSource'));
                        $infoInstance->setAdditionalInformation('bitcoinCurrency', $data->getData('bitcoinCurrency'));
                        $infoInstance->setAdditionalInformation('bitcoinAmount', $data->getData('bitcoinAmount'));
                        $infoInstance->setAdditionalInformation('stripeCustomerId', $data->getData('stripeCustomerId'));
                        $infoInstance->setAdditionalInformation('saveCardForCustomer', $data->getData('saveCardForCustomer'));
                        $infoInstance->setAdditionalInformation('email', $data->getData('email'));
                        $infoInstance->setAdditionalInformation('address', $address);
                        break;
                    default:
                        throw new LocalizedException(

                            __(
                                'invalid payment type'
                            )
                        );
                }
            }
        } else {
            $switcher = $additionalData['sType'];
            $address = json_decode($additionalData['address'],true);
            if (isset($additionalData['stripeCustomerId']) && $additionalData['stripeCustomerId']) {
                $switcher = $additionalData['paymentSource'];
            }
            switch ($switcher) {
                case 'customer':
                    $infoInstance->setAdditionalInformation('paymentSource', $additionalData['paymentSource']);
                    $infoInstance->setAdditionalInformation('stripeCustomerId', $additionalData['stripeCustomerId']);
                    $infoInstance->setAdditionalInformation('saveCardForCustomer', $additionalData['saveCardForCustomer']);
                    break;
                case 'card':
                    $infoInstance->setAdditionalInformation('stype', $additionalData['sType']);
                    $infoInstance->setAdditionalInformation('brand', $additionalData['brand']);
                    $infoInstance->setAdditionalInformation('token', $additionalData['token']);
                    $infoInstance->setAdditionalInformation('paymentSource', $additionalData['paymentSource']);
                    $infoInstance->setAdditionalInformation('cardNumber', $additionalData['cardNumber']);
                    $infoInstance->setAdditionalInformation('stripeCustomerId', $additionalData['stripeCustomerId']);
                    $infoInstance->setAdditionalInformation('saveCardForCustomer', $additionalData['saveCardForCustomer']);
                    $infoInstance->setAdditionalInformation('email', $additionalData['email']);
                    $infoInstance->setAdditionalInformation('address', $address);
                    break;
                case 'alipay_account':
                    $infoInstance->setAdditionalInformation('stype', $additionalData['sType']);
                    $infoInstance->setAdditionalInformation('brand', $additionalData['brand']);
                    $infoInstance->setAdditionalInformation('token', $additionalData['token']);
                    $infoInstance->setAdditionalInformation('paymentSource', $additionalData['paymentSource']);
                    $infoInstance->setAdditionalInformation('stripeCustomerId', $additionalData['stripeCustomerId']);
                    $infoInstance->setAdditionalInformation('saveCardForCustomer', $additionalData['saveCardForCustomer']);
                    $infoInstance->setAdditionalInformation('email', $additionalData['email']);
                    $infoInstance->setAdditionalInformation('address', $address);
                    break;
                case 'alipay':
                    $infoInstance->setAdditionalInformation('stype', $additionalData['sType']);
                    $infoInstance->setAdditionalInformation('brand', $additionalData['brand']);
                    $infoInstance->setAdditionalInformation('token', $additionalData['token']);
                    $infoInstance->setAdditionalInformation('paymentSource', $additionalData['paymentSource']);
                    $infoInstance->setAdditionalInformation('stripeCustomerId', $additionalData['stripeCustomerId']);
                    $infoInstance->setAdditionalInformation('saveCardForCustomer', $additionalData['saveCardForCustomer']);
                    $infoInstance->setAdditionalInformation('email', $additionalData['email']);
                    $infoInstance->setAdditionalInformation('address', $address);
                    break;
                case 'bitcoin':
                    $infoInstance->setAdditionalInformation('stype', $additionalData['sType']);
                    $infoInstance->setAdditionalInformation('brand', $additionalData['brand']);
                    $infoInstance->setAdditionalInformation('token', $additionalData['token']);
                    $infoInstance->setAdditionalInformation('bitcoinCurrency', $additionalData['bitcoinCurrency']);
                    $infoInstance->setAdditionalInformation('bitcoinAmount', $additionalData['bitcoinAmount']);
                    $infoInstance->setAdditionalInformation('paymentSource', $additionalData['paymentSource']);
                    $infoInstance->setAdditionalInformation('stripeCustomerId', $additionalData['stripeCustomerId']);
                    $infoInstance->setAdditionalInformation('saveCardForCustomer', $additionalData['saveCardForCustomer']);
                    $infoInstance->setAdditionalInformation('email', $additionalData['email']);
                    $infoInstance->setAdditionalInformation('address', $address);
                    break;
                default:
                    throw new LocalizedException(
                        __(
                            'invalid payment type'
                        )
                    );

            }
        }

        return $this;
    }

    /**
     * Define if debugging is enabled.
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     *
     * @api
     */
    public function getDebugFlag()
    {
        if ($this->getConfigData('debug') == 'sandbox') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * getCardData get the unique fingureprint from the card object
     *
     * @param [String] $token
     * @return String
     */
    public function getCardData($token = null)
    {
        try {
            $tokenData = \Stripe\Token::retrieve($token);
            return $tokenData;
        } catch (\Exception $e) {
            throw new LocalizedException(
                __(
                    'There was an error capturing the transaction: %1',
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * checkDuplicacyOfFingurePrint check the uniqueness of fingureprint for particular customer in database
     *
     * @param [String] $fingerPrint
     * @param [String] $customerId
     * @return Boolean
     */
    public function checkDuplicacyOfFingurePrint($fingerPrint = null, $customerId = null)
    {
        try {
            $model = $this->_stripeCustomer->create();
            $collection = $model->getCollection()
                            ->addFieldToFilter('fingerprint', ['eq' => $fingerPrint])
                            ->addFieldToFilter('customer_id', ['eq' => $customerId]);
            if ($collection->getSize()) {
                // throw new LocalizedException(
                //     __(
                //         'The card was already added. Please use saved cards'
                //     )
                // );
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            throw new LocalizedException(
                __(
                    'There was an error capturing the transaction: %1',
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * getShippingAddress get the shipping detail
     *
     * @param [Array] $stripeAddress
     * @param [\Magento\Sales\Model\Order] $order
     * @return Array
     */
    public function getShippingAddress($stripeAddress, $order) {
        $shippingAddress = [];
        if ($order->getIsVirtual() == 0) {
            if (empty($stripeAddress)) {
                $street = $order->getShippingAddress()->getStreet();
                if (count($street) == 2) {
                    $address['line1']  = $street[0];
                    $address['line2']  = $street[1];
                } elseif (count($street) == 1){
                    $address['line1']  = $street[0];
                    $address['line2']  = "";
                }
                $address['city'] = $order->getShippingAddress()->getCity();
                $address['country'] = $order->getShippingAddress()->getCountryId();
                $address['postal_code'] = $order->getShippingAddress()->getPostcode();
                $address['state'] = $order->getShippingAddress()->getRegion();
                $shippingAddress['address']= $address;
                $shippingAddress['name']= $order->getShippingAddress()->getFirstname()." ".$order->getShippingAddress()->getLastName();
                $shippingAddress['phone'] = $order->getShippingAddress()->getTelephone();
            } else {
                    $address['line1']  = $stripeAddress['shipping_address_line1'];
                    $address['line2']  = "";
                    $address['city'] = $stripeAddress['shipping_address_city'];
                    $address['country'] = $stripeAddress['shipping_address_country'];
                    $address['postal_code'] = $stripeAddress['shipping_address_zip'];
                    $address['state'] = "";
                    $shippingAddress['address']= $address;
                    $shippingAddress['name'] = $stripeAddress['billing_name'];
                    $shippingAddress['phone']= "";
            }
        }
        return $shippingAddress;
    }

    /**
     * getBillingAddress get the billing detail
     *
     * @param [Array] $stripeAddress
     * @param [\Magento\Sales\Model\Order] $order
     * @return Array
     */
    public function getBillingAddress($stripeAddress, $order) {
        $billingAddress = [];
        if (empty($stripeAddress)) {
            $street = $order->getBillingAddress()->getStreet();
            if (count($street) == 2) {
                $address['line1']  = $street[0];
                $address['line2']  = $street[1];
            } elseif (count($street) == 1){
                $address['line1']  = $street[0];
                $address['line2']  = "";
            }
            $address['city'] = $order->getBillingAddress()->getCity();
            $address['country'] = $order->getBillingAddress()->getCountryId();
            $address['postal_code'] = $order->getBillingAddress()->getPostcode();
            $address['state'] = $order->getBillingAddress()->getRegion();
            $billingAddress['address']= $address;
            $billingAddress['name']= $order->getBillingAddress()->getFirstname()." ".$order->getBillingAddress()->getLastName();
            $billingAddress['phone'] = $order->getBillingAddress()->getTelephone();
        } else {
                $address['line1']  = $stripeAddress['billing_address_line1'];
                $address['line2']  = "";
                $address['city'] = $stripeAddress['billing_address_city'];
                $address['country'] = $stripeAddress['billing_address_country'];
                $address['postal_code'] = $stripeAddress['billing_address_zip'];
                $address['state'] = "";
                $billingAddress['address']= $address;
                $billingAddress['name'] = $stripeAddress['billing_name'];
                $billingAddress['phone']= "";
        }
        return $billingAddress;
    }

    /**
     * Get config payment action url
     * Used to universalize payment actions when processing payment place
     *
     * @return string
     * @api
     */
    public function getConfigPaymentAction()
    {
        $sType = $this->getInfoInstance()->getAdditionalInformation('stype');
        if ($sType == 'bitcoin' && $this->getConfigData('payment_action') == 'authorize') {
            return self::ACTION_AUTHORIZE_CAPTURE;
        } else {
            return $this->getConfigData('payment_action');
            // parent::getConfigPaymentAction(); this will not call overrided authorize and capture
        }
    }
}
