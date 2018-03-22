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



namespace Mirasvit\Helpdesk\Controller;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Mirasvit\Helpdesk\Model\Config as Config;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class Ticket extends Action
{
    /**
     * @var \Mirasvit\Helpdesk\Model\TicketFactory
     */
    protected $ticketFactory;

    /**
     * @var \Mirasvit\Helpdesk\Model\ResourceModel\Ticket\CollectionFactory
     */
    protected $ticketCollectionFactory;

    /**
     * @var \Mirasvit\Helpdesk\Model\ResourceModel\Attachment\CollectionFactory
     */
    protected $attachmentCollectionFactory;

    /**
     * @var \Mirasvit\Helpdesk\Helper\History
     */
    protected $helpdeskHistory;

    /**
     * @var \Mirasvit\Helpdesk\Helper\Process
     */
    protected $helpdeskProcess;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\Action\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;

    /**
     * @param \Mirasvit\Helpdesk\Model\TicketFactory                              $ticketFactory
     * @param \Mirasvit\Helpdesk\Model\ResourceModel\Ticket\CollectionFactory     $ticketCollectionFactory
     * @param \Mirasvit\Helpdesk\Model\ResourceModel\Attachment\CollectionFactory $attachmentCollectionFactory
     * @param \Mirasvit\Helpdesk\Helper\History                                   $helpdeskHistory
     * @param \Mirasvit\Helpdesk\Helper\Process                                   $helpdeskProcess
     * @param \Magento\Framework\Registry                                         $registry
     * @param \Magento\Customer\Model\Session                                     $customerSession
     * @param \Magento\Framework\App\Action\Context                               $context
     */
    public function __construct(
        \Mirasvit\Helpdesk\Model\TicketFactory $ticketFactory,
        \Mirasvit\Helpdesk\Model\ResourceModel\Ticket\CollectionFactory $ticketCollectionFactory,
        \Mirasvit\Helpdesk\Model\ResourceModel\Attachment\CollectionFactory $attachmentCollectionFactory,
        \Mirasvit\Helpdesk\Helper\History $helpdeskHistory,
        \Mirasvit\Helpdesk\Helper\Process $helpdeskProcess,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Action\Context $context
    ) {
        $this->ticketFactory = $ticketFactory;
        $this->ticketCollectionFactory = $ticketCollectionFactory;
        $this->attachmentCollectionFactory = $attachmentCollectionFactory;
        $this->helpdeskHistory = $helpdeskHistory;
        $this->helpdeskProcess = $helpdeskProcess;
        $this->registry = $registry;
        $this->customerSession = $customerSession;
        $this->context = $context;
        $this->resultFactory = $context->getResultFactory();
        parent::__construct($context);
    }

    /**
     * Retrieve customer session object.
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->customerSession;
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     * @throws NotFoundException
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        $action = $this->getRequest()->getActionName();
        if ($action != 'markup' &&
            $action != 'external' &&
            $action != 'stopremind' &&
            $action != 'postexternal' &&
            $action != 'attachment'
        ) {
            if (!$this->customerSession->authenticate() ||
                !$this->helpdeskProcess->getConfig()->getFrontendIsActive()
            ) {
                $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
                throw new NotFoundException(__('Page not found.'));
            }
        }

        return parent::dispatch($request);
    }

    /**
     * @param \Mirasvit\Helpdesk\Model\Ticket $ticket
     * @return bool
     */
    public function redirectMergedTicket($ticket)
    {
        if ($ticket->getMergedTicketId()) {
            $newTicket = $this->ticketFactory->create()->load($ticket->getMergedTicketId());
            $this->getResponse()->setRedirect($newTicket->getExternalUrl());

            return true;
        }

        return false;
    }

    /**
     * @return \Mirasvit\Helpdesk\Model\Ticket
     */
    protected function _initTicket()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $customerId = $this->_getSession()->getCustomerId();
            $ticket = $this->ticketCollectionFactory->create()
              ->joinFields()
              ->addFieldToFilter('main_table.ticket_id', $id)
              ->addFieldToFilter('main_table.customer_id', $customerId)
              ->getFirstItem();
            if ($ticket->getId() > 0) {
                $this->registry->register('current_ticket', $ticket);

                return $ticket;
            }
        }
    }

    /**
     * @return \Mirasvit\Helpdesk\Model\Ticket
     */
    protected function _initExternalTicket()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $ticket = $this->ticketCollectionFactory->create()
              ->joinFields()
              ->addFieldToFilter('main_table.external_id', $id)
              ->getFirstItem();

            if ($ticket->getId() > 0) {
                $this->registry->register('current_ticket', $ticket);
                $this->registry->register('external_ticket', true);

                return $ticket;
            }
        }
    }

    /**
     * @param \Mirasvit\Helpdesk\Model\Ticket $ticket
     *
     * @return void
     */
    protected function markAsRead($ticket)
    {
        $message = $ticket->getLastMessage();
        if ($message->getId()) {
            $message->setIsRead(true)->save();
        }
    }

    /**
     * @param \Mirasvit\Helpdesk\Model\Ticket  $ticket
     * @param \Magento\Customer\Model\Customer $customer
     * @return void
     */
    protected function postTicket($ticket, $customer)
    {
        $message = $this->getRequest()->getParam('message');
        $close = $this->getRequest()->getParam('close_ticket');
        $hasPostFiles = isset($_FILES['attachment']) && $_FILES['attachment']['name'][0] != '';
        try {
            if ($message || $hasPostFiles) {
                $this->submitMessage($ticket, $customer, $message);
            }
            if ($ticket && $close) {
                $this->closeTicket($ticket);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }

    /**
     * Submits message to existing ticket OR creates a new ticket.
     *
     * @param \Mirasvit\Helpdesk\Model\Ticket  $ticket
     * @param \Magento\Customer\Model\Customer $customer
     * @param \Mirasvit\Helpdesk\Model\Message $message
     *
     * @return void
     */
    private function submitMessage($ticket, $customer, $message)
    {
        if ($ticket) { //add message
            $ticket->addMessage($message, $customer, false, Config::CUSTOMER);
            $this->messageManager->addSuccessMessage(__('Your message was successfuly posted'));
        } else { //create ticket
            $this->helpdeskProcess->createFromPost(
                $this->getRequest()->getParams(),
                Config::CHANNEL_CUSTOMER_ACCOUNT
            );
            $this->messageManager->addSuccessMessage(__('Your ticket was successfuly posted'));
        }
    }

    /**
     * @param \Mirasvit\Helpdesk\Model\Ticket $ticket
     *
     * @return void
     */
    private function closeTicket($ticket)
    {
        $ticket->close();
        $this->messageManager->addSuccessMessage(__('Ticket was successfuly closed'));
    }
}
