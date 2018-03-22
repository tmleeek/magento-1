<?php
namespace Webkul\MpDailyDeal\Controller\Account;

/**
 * Webkul_MpDailyDeal deal MassDisable controller
 * @category  Webkul
 * @package   Webkul_MpDailyDeals
 * @author    Webkul
 * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

use Magento\Framework\App\Action\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Webkul\Marketplace\Model\ProductFactory as MarketplaceProductFactory;

class MassDisable extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var MarketplaceProductFactory
     */
    protected $_marketplaceProductFactory;

    /**
     * @param Context                                    $context
     * @param \Magento\Customer\Model\Session            $customerSession
     * @param ProductRepositoryInterface                 $productRepository
     * @param MarketplaceProductFactory                  $mpProductFactory
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        ProductRepositoryInterface $productRepository,
        MarketplaceProductFactory $mpProductFactory
    ) {
        $this->_customerSession = $customerSession;
        $this->_productRepository = $productRepository;
        $this->_marketplaceProductFactory = $mpProductFactory;
        parent::__construct($context);
    }

    /**
     * Deal massDisable
     * @return \Magento\Backend\Model\View\Result\Redirect $resultRedirect
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        if ($data && isset($data['deal_mass_disable'])) {
            $mpDealProductList = $this->_marketplaceProductFactory->create()->getCollection()
                                        ->addFieldToFilter('mageproduct_id', ['in'=>$data['deal_mass_disable']])
                                        ->addFieldToFilter('seller_id', $this->_customerSession->getCustomerId());
            foreach ($mpDealProductList as $mpDealProduct) {
                $product = $this->_productRepository->getById($mpDealProduct['mageproduct_id'], true);
                if ($product->getDealStatus()==1) {
                    $product->setDealStatus(0);
                    $product->setSpecialPrice(null);
                    $product->setSpecialToDate(date("m/d/Y", strtotime('-1 day')));
                    $product->setSpecialFromDate(date("m/d/Y", strtotime('-2 day')));
                    $product->save();
                }
            }
            $this->messageManager->addSuccess(__('Deal product disabled successfuly.'));
        } else {
            $this->messageManager->addError(__('Invalid request.'));
        }

        
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setUrl(
            $this->_url->getUrl('mpdailydeal/account/deallist')
        );
    }
}
