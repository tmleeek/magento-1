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



namespace Mirasvit\Helpdesk\Controller\Adminhtml\Pattern;

use Magento\Framework\Controller\ResultFactory;

class Save extends \Mirasvit\Helpdesk\Controller\Adminhtml\Pattern
{
    /**
     *
     */
    public function execute()
    {
        if ($data = $this->getRequest()->getParams()) {
            $pattern = $this->_initPattern();
            $pattern->addData($data);
            try {
                try {
                    preg_match($pattern->getPattern(), '');
                } catch (\Exception $e) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Incorrect pattern: %1', $e->getMessage())
                    );
                }
                $pattern->save();

                $this->messageManager->addSuccess(__('Pattern was successfully saved'));
                $this->backendSession->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $pattern->getId()]);

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
        $this->messageManager->addError(__('Unable to find Pattern to save'));
        $this->_redirect('*/*/');
    }
}
