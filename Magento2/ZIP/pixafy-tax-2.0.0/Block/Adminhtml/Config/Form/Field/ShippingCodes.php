<?php
/**
 * Copyright © 2016 Pixafy Services LLC. All rights reserved.
 *
 * @license http://opensource.org/licenses/OSL-3.0  The Open Software License 3.0 (OSL 3.0)
 * @author  Alex Lukyanau <alukyanau@pixafy.com>
 */
namespace VertexSMB\Tax\Block\Adminhtml\Config\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;

class ShippingCodes extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     *
     * @var \Magento\Shipping\Model\Config
     */
    protected $_shippingConfig;

    /**
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Shipping\Model\Config          $shippingConfig
     * @param array                                   $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Shipping\Model\Config $shippingConfig, array $data = [])
    {
        $this->_shippingConfig = $shippingConfig;
        $this->_scopeConfig = $context->getScopeConfig();
        parent::__construct($context, $data);
    }

    /**
     * {@inheritDoc}
     *
     * @see \Magento\Config\Block\System\Config\Form\Field::_getElementHtml()
     */
    // @codingStandardsIgnoreStart
    protected function _getElementHtml(AbstractElement $element)
    {
        $html = '<table cellspacing="0" class="data-grid"><thead>';
        $html .= '<tr><th class="data-grid-th">Shipping Method</th><th class="data-grid-th">Product Code</th></tr></thead><tbody>';
        $allowedMethods = array('ups', 'usps', 'fedex', 'dhl');
        $methods = $this->_shippingConfig->getActiveCarriers();
        foreach ($methods as $_ccode => $_carrier) {
            $_methodOptions = array();
            
            if ($_methods = $_carrier->getAllowedMethods()) {
                if (! $_title = $this->_scopeConfig->getValue("carriers/$_ccode/title", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                    $_title = $_ccode;
                }
                $html .= '<tr><th class="data-grid-th"   colspan="2">' . $_title . '</th></tr>';
                
                foreach ($_methods as $_mcode => $_method) {
                    $_code = $_ccode . '_' . $_mcode;
                    $_methodOptions[] = array(
                        'value' => $_code,
                        'label' => $_method
                    );
                }
                
                $html .= '<tr class="" ><td class="label"  style="padding:1rem;" >' . $_method . ': </td><td class="value" style="padding:1rem;" > ' . $_code . '</td></tr>';
            }
            
            if (in_array($_ccode, $allowedMethods)) {
                foreach ($_carrier->getAllowedMethods() as $k => $v) {
                    $html .= '<tr><td class="label"  style="padding:1rem;">' . $v . ': </td><td class="value" style="padding:1rem;" > ' . $_ccode . '_' . $k . '</td></tr>';
                }
            }
        }
        
        $html .= '</tbody></table>';
        return $html;
    }

    /**
     * {@inheritDoc}
     *
     * @see \Magento\Config\Block\System\Config\Form\Field::render()
     */
    public function render(AbstractElement $element)
    {
        $id = $element->getHtmlId();
        $html = '<td>';
        $html .= $this->_getElementHtml($element);
        $html .= '</td>';
        return $this->_decorateRowHtml($element, $html);
    }
}
