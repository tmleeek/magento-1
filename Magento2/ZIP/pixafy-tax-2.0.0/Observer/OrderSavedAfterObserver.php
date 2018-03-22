<?php
/**
 * Copyright © 2016 Pixafy Services LLC. All rights reserved.
 *
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL 3.0)
 * @author  Emmanuel Etienne <eetienne@pixafy.com>
 */
namespace VertexSMB\Tax\Observer;

use Magento\Framework\Event\ObserverInterface;

class OrderSavedAfterObserver extends AbstractObserver implements ObserverInterface
{
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();
        if ($this->vertexSMBConfigHelper->isVertexSMBActive($order->getStore()) && $this->vertexSMBHelper->requestByOrderStatus($order->getStatus(), $order->getStore())) {
            $invoiceRequestData = $this->taxInvoice->prepareInvoiceData($order);
            if ($invoiceRequestData && $this->taxInvoice->sendInvoiceRequest($invoiceRequestData, $order)) {
                $this->messageManager->addSuccess(__('The Vertex SMB invoice has been sent.'));
            }
        }
        return $this;
    }
}
