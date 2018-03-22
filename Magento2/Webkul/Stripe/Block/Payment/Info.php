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

namespace Webkul\Stripe\Block\Payment;

/**
 * Base payment information block
 */
class Info extends \Magento\Payment\Block\Info
{
    /**
     * Get some specific information in format of array($label => $value)
     *
     * @return array
     */
    public function getSpecificInformation()
    {
        $stripeType = [];
        $additionalInformation = $this->getInfo()->getData('additional_information');
        if (isset($additionalInformation['paymentSource']) && $additionalInformation['paymentSource'] == 'customer') {
            $data=$this->getInfo()->getData();
            if (isset($data['additional_information']['stripe_charge'])) {
                $savedStripsCardDetail = $this->getInfo()->getData()['additional_information']['stripe_charge'];
                $source = json_decode($savedStripsCardDetail, true)['source'];
                $typeofStrypePayment = array_values(json_decode($source, true))[1]['object'];
                $last4 = array_values(json_decode($source, true))[1]['last4'];
                $stripeType = ['type' => $typeofStrypePayment." ****".$last4];
            }
        } elseif (isset($additionalInformation['stype'])) {
                $stripeType = ['type' => $additionalInformation['stype']];
        }
        return $this->_prepareSpecificInformation($stripeType)->getData();
    }
}
