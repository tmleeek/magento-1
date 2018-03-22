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

class MassDelete extends \Mirasvit\Helpdesk\Controller\Adminhtml\Schedule
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
                $nondeleted = 0;
                foreach ($ids as $id) {
                    /** @var \Mirasvit\Helpdesk\Model\Schedule $schedule */
                    $schedule = $this->scheduleFactory->create()
                        ->setIsMassDelete(true)
                        ->load($id);
                    if (count($schedule->getAssignedGateways())) {
                        ++$nondeleted;
                        $this->messageManager->addError(
                            __(
                                'You can not delete schedule %1, because there are gateways using it.
                                 Please, change gateways settings',
                                $schedule->getName()
                            )
                        );
                    } else {
                        $schedule->delete();
                    }
                }
                $this->messageManager->addSuccess(
                    __(
                        'Total of %1 record(s) were successfully deleted',
                        count($ids) - $nondeleted
                    )
                );
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
}
