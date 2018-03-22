<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAssignProduct\Controller\Adminhtml\Product;

use Webkul\MpAssignProduct\Controller\Adminhtml\Product as ProductController;
use Magento\Framework\Controller\ResultFactory;

class Save extends ProductController
{
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Webkul\MpAssignProduct\Model\ProductFactory
     */
    protected $_Product;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Webkul\MpAssignProduct\Model\ProductFactory $Product
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Webkul\MpAssignProduct\Model\ItemsFactory $items,
        \Webkul\MpAssignProduct\Helper\Data $mpAssignHelper
    ) {
        $this->_backendSession = $context->getSession();
        $this->_registry = $registry;
        $this->_items = $items;
        $this->_assignHelper = $mpAssignHelper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if ($this->getRequest()->isPost()) {
            $assignId = $this->getRequest()->getParam('id');
            $requestedStatus = $this->getRequest()->getParam('product_status');
            $assignProduct = $this->_items->create();
            $assignProduct->load($assignId);
            $status = $assignProduct->getStatus();
            $type = $assignProduct->getType();
            if ($requestedStatus == 1) {
                //Approve Product
                if ($status == 0) {
                    if ($type == "configurable") {
                        $assignProduct = $this->_assignHelper->approveConfigProduct($assignId);
                    } else {
                        $assignProduct = $this->_assignHelper->approveProduct($assignId);
                    }
                    $this->_assignHelper->sendStatusMail($assignProduct);
                }
            } else {
                //Disapprove Product
                if ($status == 1) {
                    if ($type == "configurable") {
                        $assignProduct = $this->_assignHelper->disApproveConfigProduct($assignId, 1, 1);
                    } else {
                        $assignProduct = $this->_assignHelper->disApproveProduct($assignId, 1, 1);
                    }
                    $this->_assignHelper->sendStatusMail($assignProduct, 1);
                }
            }
            $this->messageManager->addSuccess("Status updated successfully.");
            return $resultRedirect->setPath('*/*/edit', ['_current' => true, 'id' => $assignId]);
        }
        $this->messageManager->addError("Something went wrong.");
        return $resultRedirect->setPath('*/*/');
    }
}
