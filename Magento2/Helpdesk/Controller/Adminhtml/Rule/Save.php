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



namespace Mirasvit\Helpdesk\Controller\Adminhtml\Rule;

use Magento\Framework\Controller\ResultFactory;

class Save extends \Mirasvit\Helpdesk\Controller\Adminhtml\Rule
{
    /**
     *
     */
    public function execute()
    {
        //        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        //        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        if ($data = $this->getRequest()->getParams()) {
            $rule = $this->_initRule();
            if (!$data['priority_id']) {
                $data['priority_id'] = null;
            }
            if (!$data['department_id']) {
                $data['department_id'] = null;
            }
            if (!$data['status_id']) {
                $data['status_id'] = null;
            }
            if (!$data['user_id']) {
                $data['user_id'] = null;
            }

            $rule->addData($data);
            if (isset($data['rule'])) {
                $rule->loadPost($data['rule']);
            }
            //format date to standart
            // $format = $this->localeDate->getDateFormat(\IntlDateFormatter::SHORT);
            // $this->mstcoreDate->formatDateForSave($rule, 'active_from', $format);
            // $this->mstcoreDate->formatDateForSave($rule, 'active_to', $format);

            try {
                $rule->save();

                $this->messageManager->addSuccess(__('Rule was successfully saved'));
                $this->backendSession->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $rule->getId()]);

                    return;
                }
                $this->_redirect('*/*/');

                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $this->backendSession->setFormData($data);
                $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);

                return;
            }
        }
        $this->messageManager->addError(__('Unable to find Rule to save'));
        $this->_redirect('*/*/');
    }
}
