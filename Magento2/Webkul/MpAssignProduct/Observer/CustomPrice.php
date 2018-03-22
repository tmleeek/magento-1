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

class CustomPrice implements ObserverInterface
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
     * @param RequestInterface $request
     * @param \Webkul\MpAssignProduct\Helper\Data $helper
     * @param \Magento\Checkout\Model\CartFactory $cartFactory
     */
    public function __construct(
        RequestInterface $request,
        \Webkul\MpAssignProduct\Helper\Data $helper,
        \Magento\Checkout\Model\CartFactory $cartFactory
    )
    {
        $this->_request = $request;
        $this->_assignHelper = $helper;
        $this->_cart = $cartFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $helper = $this->_assignHelper;
        $item = $observer->getEvent()->getData('quote_item');
        $info = $observer->getEvent()->getData('info');
        if (is_object($info)) {
            $info = $info->getData();
        }
        $data = $this->_request->getParams();
        $product = $observer->getEvent()->getData('product');
        $assignId = 0;
        $childAssignId = 0;
        $qty = 1;
        $productId = $product->getId();
        if (is_array($info)) {
            if (array_key_exists("mpassignproduct_id", $info)) {
                $assignId = $info['mpassignproduct_id'];
            }
            if (array_key_exists("associate_id", $info)) {
                $childAssignId = $info['associate_id'];
            }
            if (array_key_exists("qty", $info)) {
                $qty = $info['qty'];
            }
        }
        if ($product->getTypeId() == "configurable") {
            if (!$helper->isConfigQtyAllowed($info, $product)) {
                $error = __('Requested quantity not available from seller.');
                throw new \Magento\Framework\Exception\LocalizedException($error);
            }
        } else {
            if (!$helper->isQtyAllowed($qty, $productId, $assignId, $childAssignId)) {
                $error = __('Requested quantity not available from seller.');
                throw new \Magento\Framework\Exception\LocalizedException($error);
            }
        }

        $itemId = (int) $item->getId();
        if ($itemId > 0) {
            $originalQty = $item->getQty() - $qty;
            $item->setQty($originalQty);
            $cartModel = $this->_cart->create();
            $quote = $cartModel->getQuote();
            $requestedItemId = $helper->getRequestedItemId($assignId, $productId, $quote->getId());
            foreach ($quote->getAllItems() as $item) {
                $quoteItemId = $item->getId();
                if ($requestedItemId == $quoteItemId) {
                    $qty = $item->getQty() + $qty;
                    $item->setQty($qty);
                }
            }
        } else {
            if ($assignId > 0) {
                if ($helper->isEnabled($assignId)) {
                    if ($childAssignId > 0) {
                        $childAssignId = $childAssignId;
                        $price = $helper->getAssocitePrice($assignId, $childAssignId);
                    } else {
                        $price = $helper->getAssignProductPrice($assignId);
                    }
                    $price = $helper->getFinalPrice($price);
                    $item->setCustomPrice($price);
                    $item->setOriginalCustomPrice($price);
                    $item->getProduct()->setIsSuperMode(true);
                } else {
                    $error = __('Product is currently not available from seller.');
                    throw new \Magento\Framework\Exception\LocalizedException($error);
                }
            }
        }
    }
}
