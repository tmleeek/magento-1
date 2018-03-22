<?php
/**
 * Created by PhpStorm.
 * User: EETIENNE
 * Date: 9/12/2016
 * Time: 12:35 AM
 */

namespace VertexSMB\Tax\Observer;

class SalesEventQuoteSubmitBeforeObserver extends AbstractObserver
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quoteAddress = $this->vertexSMBHelper->getTaxAddress($observer->getEvent()->getQuote());
        $observer->getEvent()->getOrder()->getBillingAddress()->setTaxAreaId($quoteAddress->getTaxAreaId());
        if ($observer->getEvent()->getOrder()->getShippingAddress()) {
            $observer->getEvent()->getOrder()->getShippingAddress()->setTaxAreaId($quoteAddress->getTaxAreaId());
        }
        return $this;
    }
}
