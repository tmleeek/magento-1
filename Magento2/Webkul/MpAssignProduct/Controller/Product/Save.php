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

class Save extends \Magento\Framework\App\Action\Action
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
     * @param Context $context
     * @param \Magento\Customer\Model\Url $url
     * @param \Magento\Customer\Model\Session $session
     * @param \Webkul\MpAssignProduct\Helper\Data $helper
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Url $url,
        \Magento\Customer\Model\Session $session,
        \Webkul\MpAssignProduct\Helper\Data $helper
    ) {
        $this->_url = $url;
        $this->_session = $session;
        $this->_assignHelper = $helper;
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
        $data['image'] = '';
        if (!array_key_exists('product_id', $data)) {
            $this->messageManager->addError(__('Something went wrong.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/view');
        }
        $productId = $data['product_id'];
        $product = $helper->getProduct($productId);
        $productType = $product->getTypeId();
        $result = $helper->validateData($data, $productType);
        if ($result['error']) {
            $this->messageManager->addError(__($result['msg']));
            return $this->resultRedirectFactory->create()->setPath('*/*/view');
        }
        if (array_key_exists('assign_id', $data)) {
            $flag = 1;
        } else {
            $flag = 0;
            $data['del'] = 0;
        }
        $result = $helper->processAssignProduct($data, $productType, $flag);
        if ($result['assign_id'] > 0) {
            $helper->processProductStatus($result);
            $helper->manageImages($data, $result);
            $this->messageManager->addSuccess(__('Product is saved successfully.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/productlist');
        } else {
            $this->messageManager->addError(__('There was some error while processing your request.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/add', ['id' => $data['product_id']]);
        }
    }
}
