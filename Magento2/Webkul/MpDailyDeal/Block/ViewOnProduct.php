<?php
namespace Webkul\MpDailyDeal\Block;

/**
 * Webkul_MpDailyDeal View On Product Block.
 * @category  Webkul
 * @package   Webkul_MpDailyDeal
 * @author    Webkul
 * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */


class ViewOnProduct extends \Magento\Framework\View\Element\Template
{
    /**
     * @param Magento\Catalog\Block\Product\Context   $context
     * @param Webkul\MpDailyDeal\Helper\Data          $helperData
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Webkul\MpDailyDeal\Helper\Data $helperData,
        array $data = []
    ) {
    
        $this->_coreRegistry = $context->getRegistry();
        $this->_helperData = $helperData;
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * @return array Product Deal Detail
     */

    public function getCurrentProductDealDetail()
    {
        $curPro = $this->_coreRegistry
                                ->registry('current_product');
        return $this->_helperData->getProductDealDetail($curPro);
    }
}
