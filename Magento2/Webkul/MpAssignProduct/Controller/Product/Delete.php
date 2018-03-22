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
namespace Webkul\MpAssignProduct\Controller\Product;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;

class Delete extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $_url;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_session;

    /**
     * @var \Webkul\MpAssignProduct\Helper\Data
     */
    protected $_assignHelper;

    /**
     * @var \Webkul\MpAssignProduct\Model\ItemsFactory
     */
    protected $_items;

    /**
     * @param Context $context
     * @param \Magento\Customer\Model\Url $url
     * @param \Magento\Customer\Model\Session $session
     * @param \Webkul\MpAssignProduct\Helper\Data $helper
     * @param \Webkul\MpAssignProduct\Model\ItemsFactory $itemsFactory
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Url $url,
        \Magento\Customer\Model\Session $session,
        \Webkul\MpAssignProduct\Helper\Data $helper,
        \Webkul\MpAssignProduct\Model\ItemsFactory $itemsFactory
    ) {
        $this->_url = $url;
        $this->_session = $session;
        $this->_assignHelper = $helper;
        $this->_items = $itemsFactory;
        parent::__construct($context);
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_url->getLoginUrl();
        if (!$this->_session->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $helper = $this->_assignHelper;
        $data = $this->getRequest()->getParams();
        $assignId = (int) $data['id'];
        if ($helper->isValidAssignProduct($assignId)) {
            $assignData = $this->_items->create()->load($assignId);
            if ($assignData->getId() > 0) {
                $helper->updateQuote($assignId);
                if ($assignData->getType() == "configurable") {
                    $helper->updateConfigStockDataByAssignId($assignId);
                } else {
                    $helper->updateStockDataByAssignId($assignId);
                }
                $assignData->delete();
            }
            $this->messageManager->addSuccess(__('Product deleted successfully.'));
        } else {
            $this->messageManager->addError(__('Something went wrong.'));
        }
        return $this->resultRedirectFactory->create()->setPath('*/*/productlist');
    }
}
