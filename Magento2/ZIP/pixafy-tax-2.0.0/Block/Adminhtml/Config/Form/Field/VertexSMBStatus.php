<?php
/**
 * Copyright © 2016 Pixafy Services LLC. All rights reserved.
 *
 * @license http://opensource.org/licenses/OSL-3.0  The Open Software License 3.0 (OSL 3.0)
 * @author  Alex Lukyanau <alukyanau@pixafy.com>
 */
namespace VertexSMB\Tax\Block\Adminhtml\Config\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;

class VertexSMBStatus extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     *
     * @var \VertexSMB\Tax\Helper\Data
     */
    protected $_taxDataHelper;

    /**
     *
     * @var \VertexSMB\Tax\Helper\Config
     */
    protected $_taxConfigHelper;

    /**
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \VertexSMB\Tax\Helper\Data              $taxDataHelper
     * @param array                                   $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \VertexSMB\Tax\Helper\Data $taxDataHelper, \VertexSMB\Tax\Helper\Config $taxConfigHelper, array $data = [])
    {
        $this->_taxDataHelper = $taxDataHelper;
        $this->_taxConfigHelper = $taxConfigHelper;
        parent::__construct($context, $data);
    }

    /**
     *
     * @param AbstractElement $element
     * @return string
     */
    // @codingStandardsIgnoreStart
    protected function _getElementHtml(AbstractElement $element)
    {
        $store = (int)$this->getRequest()->getParam('store', 0);
        if ($this->_taxConfigHelper->IsVertexSMBActive($store)) {
            $status = $this->_taxDataHelper->CheckCredentials($store);
            if ($status == 'Valid') {
                $state = "notice";
            } else {
                $state = "minor";
            }
        } else {
            $status = "Disabled";
            $state = "critical";
        }
        
        return '<span class="grid-severity-' . $state . '"><span>' . $status . '</span></span>';
    }
}
