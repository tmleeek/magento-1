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
 * Customer Seller form block.
 */
class Tabs extends Generic implements TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    protected $_dob = null;

    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    /**
     * @var Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $_country;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry             $registry
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Directory\Model\ResourceModel\Country\Collection $country,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_systemStore = $systemStore;
        $this->_objectManager = $objectManager;
        $this->_country = $country;
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
        return __('Seller Account Information');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Seller Account Information');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        $coll = $this->_objectManager->create(
            'Webkul\Marketplace\Block\Adminhtml\Customer\Edit'
        )->getMarketplaceUserCollection();
        $isSeller = false;
        foreach ($coll as $row) {
            $isSeller = $row->getIsSeller();
        }
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
        $coll = $this->_objectManager->create(
            'Webkul\Marketplace\Block\Adminhtml\Customer\Edit'
        )->getMarketplaceUserCollection();
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
        $customerId = $this->_coreRegistry->registry(
            RegistryConstants::CURRENT_CUSTOMER_ID
        );
        $storeid = $this->_storeManager->getStore()->getId();

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Seller Profile Information')]
        );
        $customer = $this->_objectManager->create(
            'Magento\Customer\Model\Customer'
        )->load($customerId);
        $partner = $this->_objectManager->create(
            'Webkul\Marketplace\Block\Adminhtml\Customer\Edit'
        )->getSellerInfoCollection();
        $twAactive = '';
        $fbAactive = '';
        $gplusActive = '';
        $instagramActive = '';
        $youtubeActive = '';
        $vimeoActive = '';
        $pinterestActive = '';
        $moleskineActive = '';

        if ($partner['tw_active'] == 1) {
            $twAactive = "value='1' checked='checked'";
        }
        if ($partner['fb_active'] == 1) {
            $fbAactive = "value='1' checked='checked'";
        }
        if ($partner['gplus_active'] == 1) {
            $gplusActive = "value='1' checked='checked'";
        }
        if ($partner['instagram_active'] == 1) {
            $instagramActive = "value='1' checked='checked'";
        }
        if ($partner['youtube_active'] == 1) {
            $youtubeActive = "value='1' checked='checked'";
        }
        if ($partner['vimeo_active'] == 1) {
            $vimeoActive = "value='1' checked='checked'";
        }
        if ($partner['pinterest_active'] == 1) {
            $pinterestActive = "value='1' checked='checked'";
        }
        if ($partner['moleskine_active'] == 1) {
            $moleskineActive = "value='1' checked='checked'";
        }
        $fieldset->addField(
            'contact_number',
            'text',
            [
                'name' => 'contact_number',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Contact Number'),
                'title' => __('Contact Number'),
                'value' => $partner['contact_number'],
            ]
        );
       /* $fieldset->addField(
            'company_locality',
            'text',
            [
                'name' => 'company_locality',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Suburb'),
                'title' => __('Suburb'),
                'value' => $partner['company_locality'],
            ]
        );
        $fieldset->addField(
            'distance',
            'text',
            [
                'name' => 'distance',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Service Provided within Distance(KM)'),
                'title' => __('Service Provided within Distance(KM)'),
                'value' => $partner['distance'],
            ]
        );*/

        $fieldset->addField(
            'taxvat',
            'text',
            [
                'name' => 'taxvat',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Tax/VAT Number'),
                'title' => __('Tax/VAT Number'),
                'value' => $customer->getTaxvat(),
            ]
        );
        $fieldset->addField(
            'shop_title',
            'text',
            [
                'name' => 'shop_title',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Shop Title'),
                'title' => __('Shop Title'),
                'value' => $partner['shop_title'],
            ]
        );
        
        $fieldset->addField(
            'country_pic',
            'select',
            [
                'name' => 'country_pic',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Select Country'),
                'title' => __('Select Country'),
                'values' => $this->_country->loadByStore()->toOptionArray(),
                'value' => $partner['country_pic'],
            ]
        );
        $fieldset->addField(
            'company_description',
            'textarea',
            [
                'name' => 'company_description',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Company Description'),
                'title' => __('Company Description'),
                'value' => $partner['company_description'],
            ]
        );
        $fieldset->addField(
            'return_policy',
            'textarea',
            [
                'name' => 'return_policy',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Return Policy'),
                'title' => __('Return Policy'),
                'value' => $partner['return_policy'],
            ]
        );
        $fieldset->addField(
            'shipping_policy',
            'textarea',
            [
                'name' => 'shipping_policy',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Shipping Policy'),
                'title' => __('Shipping Policy'),
                'value' => $partner['shipping_policy'],
            ]
        );
        $fieldset->addField(
            'meta_keyword',
            'textarea',
            [
                'name' => 'meta_keyword',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Meta Keywords'),
                'title' => __('Meta Keywords'),
                'value' => $partner['meta_keyword'],
            ]
        );
        $fieldset->addField(
            'meta_description',
            'textarea',
            [
                'name' => 'meta_description',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Meta Description'),
                'title' => __('Meta Description'),
                'value' => $partner['meta_description'],
            ]
        );
        $fieldset->addField(
            'banner_pic',
            'file',
            [
                'name' => 'banner_pic',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Company Banner'),
                'title' => __('Company Banner'),
                'value' => $partner['banner_pic'],
                'after_element_html' => '<label style="width:100%;">
                    Allowed File Type : [jpg, jpeg, gif, png]
                </label>
                <img style="margin:5px 0;width:700px;" 
                src="'.$this->getBaseUrl().'pub/media/avatar/'.$partner['banner_pic'].'"
                />',
            ]
        );
        $fieldset->addField(
            'logo_pic',
            'file',
            [
                'name' => 'logo_pic',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Company Logo'),
                'title' => __('Company Logo'),
                'value' => $partner['logo_pic'],
                'after_element_html' => '<label style="width:100%;">
                    Allowed File Type : [jpg, jpeg, gif, png]
                </label>
                <img style="margin:5px 0;width:250px;" 
                src="'.$this->getBaseUrl().'pub/media/avatar/'.$partner['logo_pic'].'"
                />',
            ]
        );

        $form->setUseContainer(true);

        $this->setForm($form);

        return parent::_prepareForm();
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

    /**
     * Prepare the layout.
     *
     * @return $this
     */
    public function getFormHtml()
    {
        $html = parent::getFormHtml();
        $html .= $this->getLayout()->createBlock(
            'Webkul\Marketplace\Block\Adminhtml\Customer\Edit\Js'
        )->toHtml();

        return $html;
    }
}
