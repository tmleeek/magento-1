<?php
/**
 * Created by PhpStorm.
 * User: EETIENNE
 * Date: 8/23/2016
 * Time: 2:43 PM
 */

namespace VertexSMB\Tax\Observer;

use Magento\Framework\Event\ObserverInterface;

class AbstractObserver implements ObserverInterface
{

    /**
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     *
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     *
     * @var \VertexSMB\Tax\Model\TaxInvoiceFactory
     */
    protected $taxInvoice;

    /**
     *
     * @var \VertexSMB\Tax\Model\TaxRequestFactory
     */
    protected $taxRequest;

    /**
     *
     * @var \VertexSMB\Tax\Helper\Config
     */
    protected $vertexSMBConfigHelper;

    /**
     *
     * @var \VertexSMB\Tax\Helper\Data
     */
    protected $vertexSMBHelper;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \VertexSMB\Tax\Model\TaxInvoiceFactory $taxInvoice,
        \VertexSMB\Tax\Model\TaxRequestFactory $taxRequest,
        \VertexSMB\Tax\Helper\Config $vertexConfigHelper,
        \VertexSMB\Tax\Helper\Data $vertexSMBHelper
    ) {
    
        $this->logger = $logger;
        $this->objectManager = $objectManager;
        $this->messageManager = $messageManager;
        $this->taxInvoice = $taxInvoice->create();
        $this->taxRequest = $taxRequest->create();
        $this->vertexSMBConfigHelper = $vertexConfigHelper;
        $this->vertexSMBHelper = $vertexSMBHelper;
    }
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    }
}
