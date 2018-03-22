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
namespace Webkul\MpAssignProduct\Plugin\Checkout\Block\Cart\Item\Renderer\Actions;

class Generic
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

    public function afterIsProductVisibleInSiteVisibility(
        \Magento\Checkout\Block\Cart\Item\Renderer\Actions\Generic $subject,
        $result
    ) {
        $assignData = $this->_helper->getAssignDataByItemId($subject->getItem()->getId());
        if ($assignData['assign_id'] > 0) {
            return false;
        }
        return $result;
    }
}
