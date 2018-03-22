<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Stripe
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Stripe\Controller\Cards;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;

class SaveAlipayDetailToConfig extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;
    
    /**
     * @var Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Webkul\Stripe\Helper\Data
     */
    protected $_helper;
    
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_session;
    
    /**
     * __construct
     *
     * @param Context                                            $context
     * @param PageFactory                                        $resultPageFactory
     * @param Session                                            $customerSession
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Webkul\Stripe\Helper\Data                         $helper
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $customerSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Webkul\Stripe\Helper\Data $helper,
        \Magento\Framework\Session\SessionManagerInterface $session
    ) {
        $this->_helper = $helper;
        $this->_customerSession = $customerSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_session = $session;
        parent::__construct($context);
    }

    /**
     * Retrieve customer session object.
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->_customerSession;
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_objectManager->get('Magento\Customer\Model\Url')->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * load stripe cards.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $post = $this->getRequest()->getParams();
        if (isset($post['client_secret'])) {
            $this->_session->setWkStripeAlipayClientSecret($post['client_secret']);
        } else {
            $this->_session->setWkStripeAlipayClientSecret(null);
        }
        if (isset($post['source'])) {
            $this->_session->setWkStripeAlipaySource($post['source']);
        } else {
            $this->_session->setWkStripeAlipaySource(null);
        }
        $resultRedirect->setUrl(rtrim($this->_objectManager->get("\Magento\Framework\UrlInterface")->getUrl('checkout/#payment', ['_current' => false]), "/"));
        return $resultRedirect;
    }
}
