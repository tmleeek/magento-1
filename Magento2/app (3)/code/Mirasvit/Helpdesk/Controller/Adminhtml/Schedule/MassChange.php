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



namespace Mirasvit\Helpdesk\Controller\Adminhtml\Schedule;

class MassChange extends \Mirasvit\Helpdesk\Controller\Adminhtml\Schedule
{
    /**
     * @return void
     */
    public function execute()
    {
        $ids = $this->getRequest()->getParam('schedule_id');
        if (!is_array($ids)) {
            $this->messageManager->addError(__('Please select Schedule(s)'));
        } else {
            try {
                $isActive = $this->getRequest()->getParam('is_active');
                foreach ($ids as $id) {
                    /** @var \Mirasvit\Helpdesk\Model\Schedule $schedule */
                    $schedule = $this->scheduleFactory->create()->load($id);
                    $schedule->setIsActive($isActive);
                    $schedule->save();
                }
                $this->messageManager->addSuccess(
                    __(
                        'Total of %1 record(s) were successfully updated',
                        count($ids)
                    )
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
}
