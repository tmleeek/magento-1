<?php
/**
 * Copyright © 2016 Pixafy Services LLC. All rights reserved.
 *
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL 3.0)
 * @author  Emmanuel Etienne <eetienne@pixafy.com>
 */
namespace VertexSMB\Tax\Observer;

class CreditMemoObserver extends AbstractObserver
{
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $creditMemo = $observer->getCreditmemo();
        $order = $creditMemo->getOrder();
        if ($this->vertexSMBConfigHelper->isVertexSMBActive($order->getStore())) {
            $creditMemoRequestData = $this->taxInvoice->prepareInvoiceData($creditMemo, 'refund');
            if ($creditMemoRequestData && $this->taxInvoice->sendRefundRequest($creditMemoRequestData, $order)) {
                $this->messageManager->addSuccess(__('The Vertex SMB invoice has been refunded'));
            }
        }
    }
}
