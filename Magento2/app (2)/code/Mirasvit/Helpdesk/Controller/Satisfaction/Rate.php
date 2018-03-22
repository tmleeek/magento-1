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



namespace Mirasvit\Helpdesk\Controller\Satisfaction;

class Rate extends \Mirasvit\Helpdesk\Controller\Satisfaction
{
    /**
     *
     */
    public function execute()
    {
        $rate = $this->getRequest()->getParam('rate');
        $uid = $this->getRequest()->getParam('uid');
        if ($this->helpdeskSatisfaction->addRate($uid, $rate)) {
            $this->_redirect('helpdesk/satisfaction/form', ['uid' => $uid]);
        } else {
            $this->_redirect('/');
        }
    }
}
