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
namespace Webkul\MpAssignProduct\Model\Config\Source;

class Options
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $data = [
                    ['value' => '0', 'label' => __('With Minimun Price')],
                    ['value' => '1', 'label' => __('With Maximun Price')],
                    ['value' => '2', 'label' => __('With Minimun Quantity')],
                    ['value' => '3', 'label' => __('With Maximun Quantity')],
                ];

        return $data;
    }
}
