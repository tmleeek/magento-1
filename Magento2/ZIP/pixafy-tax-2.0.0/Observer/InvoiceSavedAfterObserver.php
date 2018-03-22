<?php
/**
 * Copyright © 2016 Pixafy Services LLC. All rights reserved.
 *
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL 3.0)
 * @author  Emmanuel Etienne <eetienne@pixafy.com>
 */
namespace VertexSMB\Tax\Observer;

use Magento\Framework\Event\ObserverInterface;

class InvoiceSavedAfterObserver extends AbstractObserver implements ObserverInterface
{
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $invoice = $observer->getInvoice();
        if (!$invoice->getVertexInvoiceSent() && $this->vertexSMBConfigHelper->isVertexSMBActive($invoice->getStore()) && $this->vertexSMBConfigHelper->requestByInvoiceCreation($invoice->getStore())) {
            $invoiceRequestData = $this->taxInvoice->prepareInvoiceData($invoice, 'invoice');
            if ($invoiceRequestData && $this->taxInvoice->sendInvoiceRequest($invoiceRequestData, $invoice->getOrder())) {
                $invoice->setVertexInvoiceSent(1)->save();
                $this->messageManager->addSuccess(__('The Vertex SMB invoice has been sent.'));
            }
        }
    }
}
