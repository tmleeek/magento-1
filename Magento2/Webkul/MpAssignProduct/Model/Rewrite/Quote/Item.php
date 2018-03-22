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
namespace Webkul\MpAssignProduct\Model\Rewrite\Quote;

class Item extends \Magento\Quote\Model\Quote\Item
{
    /**
     * Check product representation in item.
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return bool
     */
    public function representProduct($product)
    {
        $itemProduct = $this->getProduct();
        if (!$product || $itemProduct->getId() != $product->getId()) {
            return false;
        }
        /*
         * Check maybe product is planned to be a child of some quote item - in this case we limit search
         * only within same parent item
         */
        $stickWithinParent = $product->getStickWithinParent();
        if ($stickWithinParent) {
            if ($this->getParentItem() !== $stickWithinParent) {
                return false;
            }
        }
        // Check options
        $itemOptions = $this->getOptionsByCode();
        $productOptions = $product->getCustomOptions();
        if (!$this->compareOptions($itemOptions, $productOptions)) {
            return false;
        }
        if (!$this->compareOptions($productOptions, $itemOptions)) {
            return false;
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->get('Webkul\MpAssignProduct\Helper\Data');
        if ($helper->isNewProduct() || $helper->getFullActionName() == "mpassignproduct_order_reorder") {
            return false;
        }

        return true;
    }
}
