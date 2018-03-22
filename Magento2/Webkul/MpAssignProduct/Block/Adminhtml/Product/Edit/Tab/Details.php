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
namespace Webkul\MpAssignProduct\Block\Adminhtml\Product\Edit\Tab;

class Details extends \Magento\Backend\Block\Template
{
    protected $_coreRegistry = null;

    /**
     * @var \Webkul\MpRmaSystem\Helper\Data
     */
    protected $_mpRmaHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Webkul\MpRmaSystem\Helper\Data $mpRmaHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Webkul\MpAssignProduct\Helper\Data $mpAssignHelper,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_mpAssignHelper = $mpAssignHelper;
        parent::__construct($context, $data);
    }

    public function getDetails()
    {
        return $this->_coreRegistry->registry('mpassignproduct_product');
    }

    public function getProduct($productId)
    {
        return $this->_mpAssignHelper->getProduct($productId);
    }

    public function getSellerDetails($sellerId, $productId = 0)
    {
        $admin = "Admin";
        $details = [];
        if ($productId > 0) {
            $sellerId = $this->_mpAssignHelper->getSellerIdByProductId($productId);
        }
        if ($sellerId > 0) {
            $sellerInfo = $this->_mpAssignHelper->getSellerInfo($sellerId);
            $shopTitle = $sellerInfo->getShopTitle();
            if (!$shopTitle) {
                $shopTitle = $sellerInfo->getShopUrl();
            }
            $details['shop_title'] = $shopTitle;
            $details['seller_id'] = $sellerId;
        } else {
            $details['shop_title'] = $admin;
            $details['seller_id'] = 0;
        }
        return $details;
    }

    public function getBaseCurrencyCode()
    {
        return $this->_mpAssignHelper->getBaseCurrencyCode();
    }

    public function getHelper()
    {
        return $this->_mpAssignHelper;
    }
}
