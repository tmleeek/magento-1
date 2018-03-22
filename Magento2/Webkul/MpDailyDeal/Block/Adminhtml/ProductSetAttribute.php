<?php
/**
 * Webkul_MpDailyDeal Product Product Attribute Adminhtml Block.
 * @category  Webkul
 * @package   Webkul_MpDailyDeal
 * @author    Webkul
 * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpDailyDeal\Block\Adminhtml;

class ProductSetAttribute extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'product/setattribute.phtml';

    /**
     * @param \Magento\Backend\Block\Template\Context   $context
     * @param \Magento\Framework\Registry               $coreRegistry
     * @param array                                     $data = []
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * getAuctionType
     * @return false|string
     */
    public function getDealsDateTime()
    {
        $product = $this->coreRegistry->registry('product');
        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $dateFrom = $product->getDealFromDate();
        $dateTo = $product->getDealToDate();
        $dealStatus = $product->getDealStatus();
        $proType = $this->getRequest()->getParam('type');
        $proType = $proType ? $proType : $product->getTypeId();
        $dealDateTime = [
            'deal_from_date'=> $dealStatus ? $this->_localeDate->date($dateFrom)->format('d/m/Y H:i:s') :'',
            'deal_to_date'=> $dealStatus ? $this->_localeDate->date($dateTo)->format('d/m/Y H:i:s'):'',
            'date_format' => $dateFormat,
            'module_enable' => $this->_scopeConfig->getValue('mpdailydeals/general/enable'),
            'product_type' => $proType
        ];
        return $dealDateTime;
    }
}
