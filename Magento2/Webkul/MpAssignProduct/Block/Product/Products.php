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
namespace Webkul\MpAssignProduct\Block\Product;

class Products extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Webkul\MpAssignProduct\Helper\Data
     */
    protected $_assignHelper;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Webkul\MpAssignProduct\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Webkul\MpAssignProduct\Helper\Data $helper,
        array $data = []
    ) {
        $this->_coreRegistry = $context->getRegistry();
        $this->_request = $context->getRequest();
        $this->_assignHelper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @return bool|\Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getAssignedProducts()
    {
        $productId = $this->getProduct()->getId();
        $collection = $this->_assignHelper->getAssignProductCollection($productId);
        if ($this->getSortOrder() == "rating") {
            if ($this->getDirection() == "desc") {
                $collection->getSelect()->order('rating DESC');
            } else {
                $collection->getSelect()->order('rating ASC');
            }
        } else {
            if ($this->getDirection() == "desc") {
                $collection->getSelect()->order('price DESC');
            } else {
                $collection->getSelect()->order('price ASC');
            }
        }
        return $collection;
    }

    /**
     * [getProduct description]
     * @return [type] [description]
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('product');
    }

    /**
     * [getDirection description]
     * @return [type] [description]
     */
    public function getDirection()
    {
        $dir = $this->_request->getParam("list_dir");
        if ($dir != "desc") {
            $dir = "asc";
        }
        return $dir;
    }

    /**
     * [getSortOrder description]
     * @return [type] [description]
     */
    public function getSortOrder()
    {
        $order = $this->_request->getParam("list_order");
        if ($order != "rating") {
            $order = "price";
        }
        return $order;
    }

    /**
     * [getDefaultUrl description]
     * @return [type] [description]
     */
    public function getDefaultUrl()
    {
        $currentUrl = $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
        list($url) = explode("?", $currentUrl);
        return $url;
    }
}
