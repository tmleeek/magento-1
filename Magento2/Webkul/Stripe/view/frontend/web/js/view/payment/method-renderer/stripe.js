/**
* Webkul Software
*
* @category  Webkul
* @package   Webkul_Stripe
* @author    Webkul
* @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
* @license   https://store.webkul.com/license.html
*/
/*browser:true*/
/*global define*/
define(
    [
        'ko',
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/set-payment-information',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/totals',
        'mage/translate',
        "https://js.stripe.com/v3/",
        'mage/url'
    ],
    function (
        ko,
        $,
        Component,
        setPaymentInformationAction,
        selectPaymentMethodAction,
        checkoutData,
        fullScreenLoader,
        quote,
        totals,
        $t,
        stripe,
        urlBuilder
    ) {
        'use strict';
        /**
         * stripeConfig contains all the payment configuration
         */
         var stripe = Stripe('pk_test_HETQmwJHZ0HIGQ0SAQxC0OZ1');
         var stripeConfig = window.checkoutConfig.payment.stripe;
         var stripeCustomerId = null;
         /**
          * customerEmail customer email
          */
         var customerEmail = window.customerData.email;
         var alipayClientSecretKey = stripeConfig.alipay_client_secret;
         var alipaySource = stripeConfig.alipay_source;
        return Component.extend(
            {
                defaults: {
                    template: 'Webkul_Stripe/payment/stripe',
                    isCardAvailable:null,
                    isCardAvailableForSave:null,
                    stripeToken:null,
                    cardNumber:null,
                    paymentSource:'customer',
                    saveCardForCustomer:false,
                    savedCards:null,
                    sType:null,
                    brand:null,
                    bitcoinAmount:null,
                    bitcoinCurrency:null,
                    labelForPayment:null,
                    canAddNewCard:false,
                    canShowExpiryValidationField:false,
                    email:null,
                    address:JSON.stringify({}),
                    canAddNewAlipay:false,
                    canDisplayAlipay:false
                },

                /**
                 * @override
                 */
                initObservable: function () {
                    var self = this;
                    this._super()
                    .observe(
                        [
                        'isCardAvailable',
                        'isCardAvailableForSave',
                        'stripeToken',
                        'cardNumber',
                        'paymentSource',
                        'saveCardForCustomer',
                        'sType',
                        'brand',
                        'bitcoinAmount',
                        'bitcoinCurrency',
                        'labelForPayment',
                        'canAddNewCard',
                        'canShowExpiryValidationField',
                        'autoSelect',
                        'email',
                        'address',
                        'canAddNewAlipay',
                        'canDisplayAlipay'
                        ]
                    );
                    if (stripeConfig.alipay == true) {
                        self.canDisplayAlipay(true);
                        if (alipayClientSecretKey != null && alipaySource != null) {
                            var self = this;
                            var stripeBrandLogo = "<div class=\"stripe_logo_label\"><img src='"+stripeConfig.mediaUrl+"/logos/alipay_account.png"+"' />  "+$t("Alipay")+"</div>";
                            self.labelForPayment(stripeBrandLogo);
                            self.stripeToken(alipaySource);
                            self.brand('alipay');
                            self.sType('alipay');
                            self.isCardAvailable(true);
                            self.isCardAvailableForSave(false);
                            self.paymentSource('token');
                            self.canAddNewCard(false);
                            self.canAddNewAlipay(true);
                            self.canDisplayAlipay(true);
                            self.autoSelect(true);
                            self.email();
                            self.address(JSON.stringify({}));
                        }
                    } else {
                        self.canDisplayAlipay(false);
                    }
                    return this;
                },

                /**
                 * validate  to validate the payment method fields at checkout page
                 *
                 * @return boolean
                 */
                validate: function () {
                    if ($('.stripe-card').is(':checked')) {
                        if($('.stripe-token-payment').is(':checked')) {
                            var savedExpMonth = "";
                            var savedExpYear = "";
                            var selectedSavedCardCustomerId = $('.stripe-card:checked').attr('data-id');
                            var id = $('.stripe-card:checked').val();
                            var expiryMonth = $("div#"+id+" "+"select#wk_stripe_savedcard_month").val();
                            var expiryYear = $("div#"+id+" "+"select#wk_stripe_savedcard_year").val();
                            var checkForExpiryYear = isNaN(expiryYear);
                            if (checkForExpiryYear) {
                                this.messageContainer.addErrorMessage({message: $t("Please enter valid expiry year.")});
                                return false;
                            }
                            var SaveCards = this.getCustomerSavedCards()
                            $.each(SaveCards, function(index,element){
                                if (element.stripe_customer_id == selectedSavedCardCustomerId){
                                    savedExpMonth = element.exp_month
                                    savedExpYear = element.exp_year
                                }
                            });
                            if (parseInt(expiryMonth) == savedExpMonth && parseInt(expiryYear) == savedExpYear) {
                                return true;
                            } else {
                                this.messageContainer.addErrorMessage({message: $t("Please enter correct expiry date.")});
                                return false;
                            }
                            return false;
                        }
                        return true;
                    } else {
                        this.messageContainer.addErrorMessage({message: $t("Please select a payment type or create a new payment type")});
                        return false;
                    }
                },

                /**
                 * selectPaymentMethod called when payment method is selected
                 *
                 * @return boolean
                 */
                selectPaymentMethod: function address() {
                    selectPaymentMethodAction(this.getData());
                    checkoutData.setSelectedPaymentMethod(stripeConfig.method);
                    return true;

                },

                /**
                 * totals set order totals from quote
                 */
                totals: quote.getTotals(),

                /**
                 * getGrandTotal get order grand total
                 *
                 * @return decimal
                 */
                getGrandTotal:function () {
                    var price = 0;
                    if (this.totals()) {
                        price = totals.getSegment('grand_total').value;
                    }
                    return price;
                },

                /**
                 * createPaymentForStripe function to call stripe payment method api for getting access token
                 *
                 * @return boolean
                 */
                createPaymentForStripe: function (data, event) {
                    var self = this ;
                    $('.wk_stripe_savedcard_validation').css('display','none');
                    $('.stripe-token-payment').prop('checked', false);
                    var smallcurrencyarray = ["bif", "clp", "djf", "gnf", "jpy", "kmf", "krw", "mga", "pyg", "rwf",
                                    "vnd", "vuv", "xaf", "xof", "xpf"];
                    var amt = 0;
                    var handler  =  StripeCheckout.configure(
                        {
                            key:stripeConfig.api_publish_key,
                            image:self.getImage(),
                            currency:stripeConfig.currency,
                            // alipay:stripeConfig.alipay,
                            alipay:false,
                            bitcoin:stripeConfig.bitcoin,
                            locale:stripeConfig.locale,
                            zipCode:stripeConfig.zipCode,
                            billingAddress:stripeConfig.billingAddress,
                            shippingAddress:stripeConfig.shippingAddress,
                            token:function (stoken, args) {
                                if (stoken.id) {
                                    switch (stoken.type) {
                                    case "card":
                                        var stripeBrandLogo = "<div class=\"stripe_logo_label\"><img alt=\""+stoken.card.brand+"\" src='"+stripeConfig.mediaUrl+"/logos/"+stoken.card.brand.toLowerCase().replace(" ", "_")+".png"+"' />  ****"+stoken.card.last4+"</div>";
                                        self.labelForPayment(stripeBrandLogo)
                                        self.stripeToken(stoken.id);
                                        self.cardNumber(stoken.card.last4);
                                        self.brand(stoken.card.brand);
                                        self.sType(stoken.type);
                                        self.isCardAvailable(true);
                                        self.isCardAvailableForSave(window.checkoutConfig.isCustomerLoggedIn);
                                        self.paymentSource('token');
                                        self.canAddNewCard(true);
                                        self.canAddNewAlipay(false);
                                        self.autoSelect(true);
                                        self.email(stoken.email);
                                        self.address(JSON.stringify(args));
                                            break;
                                    // case "alipay_account":
                                    //     var stripeBrandLogo = "<div class=\"stripe_logo_label\"><img src='"+stripeConfig.mediaUrl+"/logos/"+stoken.type.toLowerCase().replace(" ", "_")+".png"+"' />  "+$t("Alipay")+"</div>";
                                    //     self.labelForPayment(stripeBrandLogo);
                                    //     self.stripeToken(stoken.id);
                                    //     self.brand(stoken.type);
                                    //     self.sType(stoken.type);
                                    //     self.isCardAvailable(true);
                                    //     self.isCardAvailableForSave(window.checkoutConfig.isCustomerLoggedIn);
                                    //     self.paymentSource('token');
                                    //     self.canAddNewCard(true);
                                    //     self.autoSelect(true);
                                    //     self.email(stoken.email);
                                    //     self.address(JSON.stringify(args));
                                    //         break;
                                    case "source_bitcoin":
                                        var stripeBrandLogo = "<div class=\"stripe_logo_label\"><img src='"+stripeConfig.mediaUrl+"/logos/"+'bitcoin'+".png"+"' />  "+$t("Bitcoin")+"</div>";
                                        self.labelForPayment(stripeBrandLogo);
                                        self.stripeToken(stoken.id);
                                        self.sType('bitcoin');
                                        self.brand('Bitcoin');
                                        self.bitcoinCurrency(stoken.currency);
                                        self.bitcoinAmount(stoken.amount);
                                        self.isCardAvailable(true);
                                        self.isCardAvailableForSave(false);
                                        self.paymentSource('token');
                                        self.canAddNewCard(true);
                                        self.canAddNewAlipay(false);
                                        self.autoSelect(true);
                                        self.email(stoken.email);
                                        self.address(JSON.stringify(args));
                                            break;
                                    default:
                                            return self.messageContainer.addErrorMessage({'message': $t('Not able to process card details , please try again')});
                                    }
                                } else {
                                    return self.messageContainer.addErrorMessage({'message': $t('Not able to process card details , please try again')});
                                }
                            }
                        }
                    );
                    if($.inArray(stripeConfig.currency.toLowerCase(), smallcurrencyarray) != -1) {
                        var amt = self.getGrandTotal();
                    } else {
                        var amt = self.getGrandTotal()*100;
                    }
                    handler.open(
                        {
                            name: stripeConfig.name_on_form,
                            email:customerEmail,
                            amount:amt
                        }
                    );
                },

                /**
                 * getData set payment method data for making it available in PaymentMethod Class
                 *
                 * @return object
                 */
                getData: function () {
                    self = this;
                    if (this.paymentSource() == 'token') {
                        stripeCustomerId = null;
                    }
                    return {
                        'method': stripeConfig.method,
                        'additional_data': {
                            'token':this.stripeToken(),
                            'cardNumber':this.cardNumber(),
                            'sType':this.sType(),
                            'brand':this.brand(),
                            'paymentSource':this.paymentSource(),
                            'stripeCustomerId':stripeCustomerId,
                            'saveCardForCustomer': this.saveCardForCustomer(),
                            'bitcoinCurrency':this.bitcoinCurrency(),
                            'bitcoinAmount':this.bitcoinAmount(),
                            'email':this.email(),
                            'address':this.address()
                        },
                    };
                },

                /**
                 * setStripeCustomerId update customer Id
                 *
                 * @param HtmlObject element
                 */
                setStripeCustomerId: function (element) {
                    stripeCustomerId = element.stripe_customer_id;
                    $('.wk_stripe_savedcard_validation').css('display','none');
                    $('#'+element.stripe_customer_id).css('display','block');
                    return true;
                },

                /**
                 * setSaveCardForCustomer update condition to save customer card or not
                 */
                setSaveCardForCustomer: function () {
                    if ($('.save-card-for-customer').is(':checked')) {
                        this.saveCardForCustomer(1); } else {
                        this.saveCardForCustomer(0); }
                },

                /**
                 * getCustomerSavedCards get all the saved customer card details.
                 *
                 * @return string JSON
                 */
                getCustomerSavedCards: function () {
                    this.savedCards = JSON.parse(stripeConfig.saved_cards);
                    if (this.savedCards.length > 0) {
                        return this.savedCards; }
                },

                /**
                 * getYearList update the year list for a saved card expiry date validation.
                 *
                 * @return array
                 */
                getYearList: function () {
                    var date = new Date,
                        years = [],
                        year = date.getFullYear();
                    for (var i = year+1; i < year + 20; i++) {
                           years.push(i);
                    }
                    return years;
                },

                /**
                 * getMonthList update the month list for a saved card expiry date validation.
                 *
                 * @return array
                 */
                getMonthList: function () {
                    return [1,2,3,4,5,6,7,8,9,10,11,12];
                },

                /**
                 *
                 */
                hideOpenCardExpiryCredentials: function() {
                    $('.wk_stripe_savedcard_validation').css('display','none');
                },

                /**
                 * getImage return stripe logo to show in the popup
                 *
                 * @return string
                 */
                getImage:function () {
                    return stripeConfig.image_on_form;
                },

                /**
                 * alipayAuthorization create the source for alipay payment.
                 */
                alipayAuthorization :function () {
                    var self = this;
                    var url = urlBuilder.build('stripe/cards/savealipaydetailtoconfig');
                    var smallcurrencyarray = ["bif", "clp", "djf", "gnf", "jpy", "kmf", "krw", "mga", "pyg", "rwf",
                                    "vnd", "vuv", "xaf", "xof", "xpf"];
                    var currencySupportedByAliPay = ["aud", "cad", "eur", "gbp", "hkd", "jpy", "nzd", "sgd", "usd"];
                    if($.inArray(stripeConfig.currency.toLowerCase(), currencySupportedByAliPay) == -1) {
                        return self.messageContainer.addErrorMessage({'message': $t(stripeConfig.currency.toLowerCase()+' Not supported by alipay , please change the currency and try again')});
                    } else {
                        if ($.inArray(stripeConfig.currency.toLowerCase(), smallcurrencyarray) != -1) {
                            var amt = self.getGrandTotal();
                        }else {
                            var amt = self.getGrandTotal()*100;
                        }
                        stripe.createSource({
                            type: 'alipay',
                            amount: amt,
                            currency: stripeConfig.currency.toLowerCase(),
                            usage: 'reusable',
                            redirect: {
                                return_url: url,
                            },
                        }).then(function(result) {
                            window.location = result.source.redirect.url;
                        });
                    }
                },

                /**
                 * alipayRetriveSource retrive the already created source token for alipay.
                 *
                 * @return string JSON
                 */
                alipayRetriveSource : function(result) {
                        window.location = result.source.redirect.url;
                        stripe.retrieveSource({
                        id: result.source.id,
                        client_secret: result.source.client_secret,
                    }).then(function(result) {
                    });
                }
            }
        );
    }
);
