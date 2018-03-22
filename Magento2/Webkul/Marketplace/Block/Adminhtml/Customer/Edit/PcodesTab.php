<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\Block\Adminhtml\Customer\Edit;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Ui\Component\Layout\Tabs\TabInterface;
use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;

/**
 * Customer account form block.
 */
class PcodesTab extends Generic implements TabInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var string
     */
    protected $_isSeller;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry             $registry
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->_isSeller = 0;
        $this->_coreRegistry = $registry;
        $this->_objectManager = $objectManager;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return string|null
     */
    public function getCustomerId()
    {
        return $this->_coreRegistry->registry(
            RegistryConstants::CURRENT_CUSTOMER_ID
        );
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Postal Codes');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Postal Codes');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        $coll = $this->_objectManager
        ->create('Webkul\Marketplace\Block\Adminhtml\Customer\Edit')
        ->getMarketplaceUserCollection();
        $isSeller = false;
        foreach ($coll as $row) {
            $isSeller = $row->getIsSeller();
        }
        $this->_isSeller = $isSeller;
        if ($this->getCustomerId() && $isSeller) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        $coll = $this->_objectManager
        ->create('Webkul\Marketplace\Block\Adminhtml\Customer\Edit')
        ->getMarketplaceUserCollection();
        $isSeller = false;
        foreach ($coll as $row) {
            $isSeller = $row->getIsSeller();
        }
        if ($this->getCustomerId() && $isSeller) {
            return false;
        }

        return true;
    }

    /**
     * Tab class getter.
     *
     * @return string
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * Return URL link to Tab content.
     *
     * @return string
     */
    public function getTabUrl()
    {
        return '';
    }

    /**
     * Tab should be loaded trough Ajax call.
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return false;
    }
    
    public function initForm()
    {
        if (!$this->canShowTab()) {
            return $this;
        }
        /**@var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('marketplace_');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Seller postal codes')]
        );

$partner = $this->_objectManager->create(
            'Webkul\Marketplace\Block\Adminhtml\Customer\Edit'
        )->getSellerInfoCollection();
        
        $slocation=explode(",",$partner['company_locality']);

$i=0;
foreach ($slocation as $l) {
       
        $fieldset->addField(
            'company_locality'.$i,
            'text',
            [
                'name' => 'company_locality['.$i.']',
                'data-form-part' => $this->getData('target_form'),
                'label' => ((!$i)?__('Postal Codes'):''),
                'title' => __('Postal Codes'),
                'class' => '',
                'required' => ((!$i)?true:false),
                'value'=>$l
            ]
        );
        $i++;

}
if($slocation<=0)
for ($fld=0; $fld < 9 ; $fld++) { 
     $fieldset->addField(
            'company_locality'.$i,
            'text',
            [
                'name' => 'company_locality['.$i.']',
                'data-form-part' => $this->getData('target_form'),
                'label' => ((!$i)?__('Postal Code'):''),
                'title' => __('Postal Codea'),
                'class' => '',
                'required' => ((!$i)?true:false)
                
            ]
        );
        $i++;
}
for ($aa=$i; $aa <10 ; $aa++) { 
    $fieldset->addField(
            'company_locality'.$aa,
            'text',
            [
                'name' => 'company_locality['.$aa.']',
                'data-form-part' => $this->getData('target_form'),
                'label' => ((!$aa)?__('Postal Code'):''),
                'title' => __('Postal Code'),
                'class' => '',
                'required' => ((!$aa)?true:false)
                
            ]
        );
}
       

        $this->setForm($form);

        return $this;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->canShowTab()) {
            $this->initForm();

            return parent::_toHtml();
        } else {
            return '';
        }
    }
}
