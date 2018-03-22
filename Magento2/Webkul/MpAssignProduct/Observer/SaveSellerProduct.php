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

class SaveSellerProduct implements ObserverInterface
{
    /**
     * @var \Webkul\MpAssignProduct\Helper\Data
     */
    protected $_assignHelper;

    /**
     * @param \Webkul\MpAssignProduct\Helper\Data $helper
     */
    public function __construct(\Webkul\MpAssignProduct\Helper\Data $helper)
    {
        $this->_assignHelper = $helper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $data = $observer->getEvent()->getData();
        if (array_key_exists("product_id", $data[0])) {
            $productId = $data[0]['product_id'];
            $qty = $this->_assignHelper->getAssignProductQty($productId);
            if ($qty > 0) {
                $this->_assignHelper->updateStockData($productId, $qty);
            }
        }
    }
}