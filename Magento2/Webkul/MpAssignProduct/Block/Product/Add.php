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

class Add extends \Magento\Framework\View\Element\Template
{
    /**
     * [_construct description]
     * @return [type] [description]
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('Assign Product'));
    }
}
