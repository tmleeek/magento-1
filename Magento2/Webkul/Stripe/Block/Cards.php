<?php
/**
* Webkul Software
*
* @category  Webkul
* @package   Webkul_Stripe
* @author    Webkul
* @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
* @license   https://store.webkul.com/license.html
*/
namespace Webkul\Stripe\Block;

use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Stripe block.
 *
 * @author Webkul Software
 */
class Cards extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;
    
    /**
     * @var Webkul\Stripe\Model\ResourceModel\StripeCustomer\CollectionFactory
     */
    protected $_stripeCustomerFactory;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * $_helper
     *
     * @var
     */
    protected $_helper;

    /**
     *
     * @param \Magento\Framework\View\Element\Template\Context                    $context
     * @param \Webkul\Stripe\Model\ResourceModel\StripeCustomer\CollectionFactory $vacationFactory
     * @param \Magento\Customer\Model\Session                                     $customerSession
     * @param \Webkul\Marketplace\Helper\Data                                     $marketplaceHelper
     * @param \Magento\Framework\Message\ManagerInterface                         $messageManager
     * @param DateTime                                                            $date
     * @param Store                                                               $store
     * @param array                                                               $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\Stripe\Model\ResourceModel\StripeCustomer\CollectionFactory $stripeCustomerFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        DateTime $date,
        \Webkul\Stripe\Helper\Data $helper,
        array $data = []
    ) {
        $this->_date = $date;
        $this->_messageManager = $messageManager;
        $this->_stripeCustomerFactory = $stripeCustomerFactory;
        $this->_customerSession = $customerSession;
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * getSavedCards get customer saved cards
     *
     * @return Webkul\Stripe\Model\StripeCustomer
     */
    public function getSavedCards()
    {
        $customerId = $this->_customerSession->getCustomerId();
        $cardData = $this->_stripeCustomerFactory->create()
            ->addFieldToFilter('customer_id', ['eq' => $customerId]);
        if ($cardData->getSize() > 0) {
            return $cardData;
        }

        return false;
    }

    /**
     * getLogo get stripe methods logo
     *
     * @param  string $brand
     * @return string
     */
    public function getLogo($brand)
    {
        return $this->_helper->getLogo($brand);
    }
}
