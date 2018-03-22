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


namespace Mirasvit\Helpdesk\Controller\Adminhtml\Report;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Mirasvit\Report\Model\Pool;
class View extends Action
{
    /**
     * @var Registry
     */
    protected $registry;
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;
    /**
     * @var Context
     */
    protected $context;
    /**
     * @var Pool
     */
    protected $pool;
    /**
     * @param Pool     $pool
     * @param Registry $registry
     * @param Context  $context
     */
    public function __construct(
        Pool $pool,
        Registry $registry,
        Context $context
    ) {
        $this->pool = $pool;
        $this->registry = $registry;
        $this->context = $context;
        $this->backendSession = $context->getSession();
        parent::__construct($context);
    }
    /**
     * {@inheritdoc}
     * @param \Magento\Backend\Model\View\Result\Page\Interceptor $resultPage
     * @return \Magento\Backend\Model\View\Result\Page\Interceptor
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Mirasvit_Helpdesk::helpdesk_report');
        $resultPage->getConfig()->getTitle()->prepend(__('Helpdesk'));
        $resultPage->getConfig()->getTitle()->prepend(__('Reports'));
        return $resultPage;
    }
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->registry->register('current_report', $this->pool->get('helpdesk_overview'));
        /** @var \Magento\Backend\Model\View\Result\Page\Interceptor $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $this->initPage($resultPage);
        return $resultPage;
    }
    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_Helpdesk::helpdesk_report');
    }
}