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


namespace Mirasvit\Helpdesk\Observer;

use Magento\Framework\Event\ObserverInterface;

class LoadUserSignatureObserver implements ObserverInterface
{

    /**
     * @param \Mirasvit\Helpdesk\Helper\User $helpdeskUser
     */
    public function __construct(
        \Mirasvit\Helpdesk\Helper\User $helpdeskUser
    ) {
        $this->helpdeskUser = $helpdeskUser;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\User\Model\User $user */
        $user = $observer->getObject();
        if (!$user->getId()) {
            return;
        }
        $helpdeskUser = $this->helpdeskUser->getHelpdeskUser();
        if (!$helpdeskUser) {
            return;
        }
        $user->setSignature($helpdeskUser->getSignature());
    }
}
