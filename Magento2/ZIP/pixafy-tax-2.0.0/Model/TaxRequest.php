<?php
/**
 * Copyright © 2016 Pixafy Services LLC. All rights reserved.
 *
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL 3.0)
 * @author  Alex Lukyanau <alukyanau@pixafy.com>
 */
namespace VertexSMB\Tax\Model;

class TaxRequest extends \Magento\Framework\Model\AbstractModel
{

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('VertexSMB\Tax\Model\ResourceModel\TaxRequest');
    }

    /**
     * Return Total Invoiced Tax
     *
     * @param  int $orderId
     * @return number
     */
    public function getTotalInvoicedTax($orderId)
    {
        $totalTax = 0;
        $invoices = $this->getCollection()
            ->addFieldToSelect('total_tax')
            ->addFieldToFilter('order_id', $orderId)
            ->addFieldToFilter('request_type', 'invoice');
        foreach ($invoices as $invoice) {
            $totalTax += $invoice->getTotalTax();
        }
        return $totalTax;
    }

    /**
     *
     * @return \VertexSMB\Tax\Model\TaxRequest
     */
    public function removeQuotesLookupRequests()
    {
        $quotes = $this->getCollection()
            ->addFieldToSelect('request_id')
            ->addFieldToFilter(
                'request_type',
                [
                'in' => [
                    'quote',
                    'tax_area_lookup'
                ]
                ]
            );
        $quotes->walk('delete');
        return $this;
    }

    /**
     *
     * @name Cleanup Invoices for Completed Orders
     * @return \VertexSMB\Tax\Model\TaxRequest
     */
    public function removeInvoicesforCompletedOrders()
    {
        $invoices = $this->getCollection()
            ->addFieldToSelect('order_id')
            ->addFieldToFilter('request_type', 'invoice');
        
        $invoices->getSelect()->join(
            [
            'order' => 'sales_flat_order'
            ],
            'order.entity_id = main_table.order_id',
            [
            'order.state'
            ]
        );
        $invoices->addFieldToFilter(
            'order.state',
            [
                'in' => [
                    'complete',
                    'canceled',
                    'closed'
                ]
            ]
        );
        
        $completedOrderIds = [];
        foreach ($invoices as $invoice) {
            $completedOrderIds[] = $invoice->getOrderId();
        }
        
        $completedInvoices = $this->getCollection()
            ->addFieldToSelect('request_id')
            ->addFieldToFilter(
                'order_id',
                [
                'in' => $completedOrderIds
                ]
            );
        $completedInvoice->walk('delete');
        
        return $this;
    }
}
