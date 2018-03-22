<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-helpdesk
 * @version   1.1.25
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Helpdesk\Plugin;

/**
 * We use this plugin to insert our form on the Contact Us page.
 * @package Mirasvit\Helpdesk\Plugin
 */
class HtmlAfter
{
    /**
     * @param \Magento\Backend\Block\Page\Header $block
     * @param string                             $html
     * @return string
     */
    public function afterToHtml($block, $html)
    {
        $ourHtml = $html;
        $layout = $block->getLayout();
        if ($blockBegin = strpos($html, '<ul class="admin__action-dropdown-menu">')) {
            $blockBegin = strpos($html, '</ul>', $blockBegin);
            $beginning = substr($html, 0, $blockBegin);
            $ending = substr($html, $blockBegin);

            $html = $layout->createBlock('Mirasvit\Helpdesk\Block\Adminhtml\Notification\Indicator')
                ->setTemplate('Mirasvit_Helpdesk::notification/indicator.phtml')
                ->toHtml();

            $ourHtml = $beginning.$html.$ending;
        }

        return $ourHtml;
    }

}