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
namespace Webkul\MpAssignProduct\Model\Rewrite\CatalogInventory\Stock;

class Item extends \Magento\CatalogInventory\Model\Stock\Item
{
    public function getIsInStock()
    {
        if (!$this->getManageStock()) {
            return true;
        }
        return (bool) $this->_getData(static::IS_IN_STOCK);
    }

    /**
     * @return float
     */
    public function getQty()
    {
        $fullActionName = $this->helper()->getFullActionName();
        $productId = $this->getProductId();
        $qty = $this->helper()->getAssignProductQty($productId);
        if ($fullActionName == 'marketplace_product_edit') {
            return $this->_getData(static::QTY) - $qty;
        }
        return null === $this->_getData(static::QTY) ? null : floatval($this->_getData(static::QTY));
    }

    public function helper()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->get('Webkul\MpAssignProduct\Helper\Data');
        return $helper;
    }
}
