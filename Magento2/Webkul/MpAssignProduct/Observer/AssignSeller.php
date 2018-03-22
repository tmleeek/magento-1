<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 *
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAssignProduct\Observer;

use Magento\Framework\Event\ObserverInterface;

class AssignSeller implements ObserverInterface
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
        $params = $observer->getData();
        if (array_key_exists(0, $params)) {
            if (array_key_exists('id', $params[0])) {
                $productId = $params[0]['id'];
                $this->_assignHelper->assignSeller($productId);
            } elseif (array_key_exists('product_mass_delete', $params[0])) {
                $productsIdArray = $params[0]['product_mass_delete'];
                foreach ($productsIdArray as $key => $productId) {
                    $this->_assignHelper->assignSeller($productId);
                }
            }
        }
    }
}
