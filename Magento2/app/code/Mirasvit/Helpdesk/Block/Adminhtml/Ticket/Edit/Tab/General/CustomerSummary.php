<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-helpdesk
 * @version   1.1.25
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Helpdesk\Block\Adminhtml\Ticket\Edit\Tab\General;

class CustomerSummary extends \Magento\Framework\View\Element\Template
{
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry                      $registry
     * @param \Mirasvit\Helpdesk\Helper\Customer               $helpdeskCustomer
     * @param \Mirasvit\Helpdesk\Helper\Order                  $helpdeskOrder
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Mirasvit\Helpdesk\Helper\Customer $helpdeskCustomer,
        \Mirasvit\Helpdesk\Helper\Order $helpdeskOrder
    ) {
        $this->registry         = $registry;
        $this->helpdeskCustomer = $helpdeskCustomer;
        $this->helpdeskOrder    = $helpdeskOrder;
        $this->context          = $context;

        parent::__construct($context, []);
    }

    /**
     * @return \Mirasvit\Helpdesk\Model\Ticket
     */
    public function getTicket()
    {
        return $this->registry->registry('current_ticket');
    }

    /**
     * @return string
     */
    public function getConfigJson()
    {
        $ticket = $this->getTicket();

        $customersOptions = [];
        $ordersOptions = [
            [
                'name' => (string) __('Unassigned'),
                'id' => 0,
            ],
        ];

        if ($ticket->getCustomerId() || $ticket->getQuoteAddressId()) {
            $customers = $this->helpdeskCustomer->getCustomerArray(
                false,
                $ticket->getCustomerId(),
                $ticket->getQuoteAddressId()
            );
            $email = false;

            foreach ($customers as $value) {
                $customersOptions[] = [
                    'name' => $value['name'],
                    'id'   => $value['id'],
                ];
                $email = $value['email'];
            }

            $orders = $this->helpdeskOrder->getOrderArray($email, $ticket->getCustomerId());
            foreach ($orders as $value) {
                $ordersOptions[] = [
                    'name' => $value['name'],
                    'id'   => $value['id'],
                    'url'  => $value['url'],
                ];
            }
        }

        $url = '#';
        if ($ticket->getCustomerId()) {
            $url = $this->context->getUrlBuilder()->getUrl('customer/index/edit/', ['id' => $ticket->getCustomerId()]);
        }
        $config = [
            '_customer' => [
                'id'     => $ticket->getCustomerId(),
                'email'  => $ticket->getCustomerEmail(),
                'name'   => $ticket->getCustomerName(),
                'url'    => $url,
                'orders' => $ordersOptions,
            ],
            '_orderId'         => (int) $ticket->getOrderId(),
            '_emailTo'         => $ticket->getCustomerEmail(),
            '_autocompleteUrl' => $this->getUrl('helpdesk/ticket/customerfind'),
        ];

        return \Zend_Json_Encoder::encode($config);
    }
}
