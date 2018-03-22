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
namespace Webkul\MpAssignProduct\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class AfterAddProductToCart implements ObserverInterface
{
    /**
     * Request instance.
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Webkul\MpAssignProduct\Helper\Data
     */
    protected $_assignHelper;

    /**
     * @var \Magento\Checkout\Model\CartFactory
     */
    protected $_cart;

    /**
     * @var \Webkul\MpAssignProduct\Model\QuoteFactory
     */
    protected $_quote;

    /**
     * @param RequestInterface $request
     * @param \Webkul\MpAssignProduct\Helper\Data $helper
     * @param \Magento\Checkout\Model\CartFactory $cartFactory
     * @param \Webkul\MpAssignProduct\Model\QuoteFactory $quoteFactory
     */
    public function __construct(
        RequestInterface $request,
        \Webkul\MpAssignProduct\Helper\Data $helper,
        \Magento\Checkout\Model\CartFactory $cartFactory,
        \Webkul\MpAssignProduct\Model\QuoteFactory $quoteFactory
    )
    {
        $this->_request = $request;
        $this->_assignHelper = $helper;
        $this->_cart = $cartFactory;
        $this->_quote = $quoteFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $info = $observer->getEvent()->getData('info');
        $helper = $this->_assignHelper;
        $data = $this->_request->getParams();
        if (is_array($info)) {
            $assignId = 0;
            if (array_key_exists("mpassignproduct_id", $info)) {
                $assignId = $info['mpassignproduct_id'];
            }
            $childAssignId = 0;
            if (array_key_exists("associate_id", $info)) {
                $childAssignId = $info['associate_id'];
            }
            $productId = $info['product'];
        } else {
            $productId = (int) $this->_request->getParam('product');
            $assignId = (int) $this->_request->getParam('mpassignproduct_id');
            $childAssignId = (int) $this->_request->getParam('associate_id');
        }
        $cartModel = $this->_cart->create();
        $quote = $cartModel->getQuote();
        $quoteId = $quote->getId();
        $ownerId = $helper->getSellerIdByProductId($productId);
        $flag = 0;
        if ($assignId > 0) {
            $sellerId = $helper->getAssignSellerIdByAssignId($assignId);
        } else {
            $sellerId = $ownerId;
        }
        foreach ($quote->getAllVisibleItems() as $item) {
            $itemId = $item->getId();
            $qty = $item->getQty();
        }
        if ($helper->isNewProduct($productId, $assignId)) {
            $model = $this->_quote->create();
            $quoteData = [
                            'item_id' => $itemId,
                            'seller_id' => $sellerId,
                            'owner_id' => $ownerId,
                            'qty' => $qty,
                            'product_id' => $productId,
                            'assign_id' => $assignId,
                            'child_assign_id' => $childAssignId,
                            'quote_id' => $quoteId,
                        ];
            $model->setData($quoteData)->save();
        }
    }

}