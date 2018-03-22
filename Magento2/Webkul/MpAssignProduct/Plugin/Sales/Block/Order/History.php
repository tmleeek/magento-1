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
namespace Webkul\MpAssignProduct\Plugin\Sales\Block\Order;

use Magento\Sales\Block\Order\History as HistoryBlock;

class History
{
    public function aroundGetReorderUrl(HistoryBlock $subject, \Closure $proceed, $order)
    {
        return $subject->getUrl('mpassignproduct/order/reorder', ['order_id' => $order->getId()]);
    }
}
