<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul
 * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAssignProduct\Model\Plugin;

use Magento\Quote\Model\Quote\TotalsCollector as Collector;

class TotalsCollector
{
    /**
     * @var \Webkul\MpAssignProduct\Helper\Data
     */
    private $_helper;

    /**
     * Initialize dependencies.
     *
     * @param \Webkul\MpAssignProduct\Helper\Data $helper
     */
    public function __construct(
        \Webkul\MpAssignProduct\Helper\Data $helper
    ) {
        $this->_helper = $helper;
    }

    public function aroundCollect(
        Collector $subject,
        \Closure $proceed,
        \Magento\Quote\Model\Quote $quote
    ) {
        $this->_helper->collectTotals($quote);
        $result = $proceed($quote);
        return $result;
    }
}
