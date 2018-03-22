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
namespace Webkul\MpAssignProduct\Plugin\Sales\Block\Order\Info;

use Magento\Sales\Block\Order\Info\Buttons as ButtonsBlock;

class Buttons
{
    public function aroundGetReorderUrl(ButtonsBlock $subject, \Closure $proceed, $order)
    {
        return $subject->getUrl('mpassignproduct/order/reorder', ['order_id' => $order->getId()]);
    }
}
