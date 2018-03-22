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

class BeforeViewCart implements ObserverInterface
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
        $this->_assignHelper->checkStatus();
        $this->_assignHelper->checkCartPrice();
    }

}