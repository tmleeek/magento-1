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



namespace Mirasvit\Helpdesk\Controller\Adminhtml\Draft;

use Magento\Framework\Controller\ResultFactory;

class Update extends \Mirasvit\Helpdesk\Controller\Adminhtml\Draft
{
    /**
     * @SuppressWarnings(PHPMD.ExitExpression)
     *
     * @return void
     */
    public function execute()
    {
        $ticketId = (int) $this->getRequest()->getParam('ticket_id');
        $text = $this->getRequest()->getParam('text');
        if ($text == -1) {
            $text = false;
        }

        $userId = $this->context->getAuth()->getUser()->getUserId();
        echo $this->helpdeskDraft->getNoticeMessage($ticketId, $userId, $text);
        die;
    }
}
