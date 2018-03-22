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
namespace Webkul\MpAssignProduct\Block\Adminhtml\Catalog\Product\Edit\Tab;
 
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
 
class AssignProduct extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'product/edit/assignproduct.phtml';
 
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;
 
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }
 
    /**
     * Retrieve product
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }
}
