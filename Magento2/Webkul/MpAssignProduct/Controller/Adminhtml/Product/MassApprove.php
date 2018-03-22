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

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\MpAssignProduct\Model\ResourceModel\Items\CollectionFactory;

/**
 * Class MassApprove.
 */
class MassApprove extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $_filter;

    /**
     * @var \Webkul\MpAssignProduct\Helper\Data
     */
    protected $_assignHelper;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param \Webkul\MpAssignProduct\Helper\Data $helper
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        \Webkul\MpAssignProduct\Helper\Data $helper,
        CollectionFactory $collectionFactory
    ) {
        $this->_filter = $filter;
        $this->_assignHelper = $helper;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Execute action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     *
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->_filter->getCollection($this->_collectionFactory->create());
        foreach ($collection as $item) {
            $assignId = $item->getId();
            $qty = $item->getQty();
            $status = $item->getStatus();
            $type = $item->getType();
            if ($status == 0) {
                if ($type == "configurable") {
                    $assignProduct = $this->_assignHelper->approveConfigProduct($assignId);
                } else {
                    $assignProduct = $this->_assignHelper->approveProduct($assignId);
                }
                $this->_assignHelper->sendStatusMail($assignProduct);
            }
        }
        $msg = 'A total of %1 Product(s) have been approved.';
        $this->messageManager->addSuccess(__($msg, $collection->getSize()));
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Check for is allowed.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpAssignProduct::product');
    }
}
