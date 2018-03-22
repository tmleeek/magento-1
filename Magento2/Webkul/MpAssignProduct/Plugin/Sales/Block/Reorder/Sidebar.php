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
namespace Webkul\MpAssignProduct\Plugin\Sales\Block\Reorder;

use Magento\Sales\Block\Reorder\Sidebar as SidebarBlock;

class Sidebar
{
    public function afterGetFormActionUrl(SidebarBlock $subject, $result)
    {
        return $subject->getUrl('mpassignproduct/cart/addgroup', ['_secure' => true]);
    }
}
