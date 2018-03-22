<?php
namespace Bluethink\Checkdelivery\Block\Adminhtml;
class Pincode extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        
        $this->_controller = 'adminhtml_pincode';/*block grid.php directory*/
        $this->_blockGroup = 'Bluethink_Checkdelivery';
        $this->_headerText = __('Pincode');
        $this->_addButtonLabel = __('Upload CSV'); 
        parent::_construct();
        
    }
}
