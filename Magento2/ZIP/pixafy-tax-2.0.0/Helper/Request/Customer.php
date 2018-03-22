<?php
/**
 * Copyright © 2016 Pixafy Services LLC. All rights reserved.
 *
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL 3.0)
 * @author  Alex Lukyanau <alukyanau@pixafy.com>
 */
namespace VertexSMB\Tax\Helper\Request;

use Magento\Customer\Api\GroupRepositoryInterface as CustomerGroupRepository;
use Magento\Customer\Api\GroupManagementInterface as CustomerGroupManagement;

/**
 * Seller information helper
 */
class Customer extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $vertexSMBConfigHelper;

    protected $vertexSMBHelper;

    protected $addressHelper;

    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Customer
     *
     * @var \Magento\Customer\Model\Customer
     */
    protected $customerRepository;

    /**
     *
     * @var CustomerGroupRepository
     */
    protected $customerGroupRepository;

    /**
     *
     * @var CustomerGroupManagement
     */
    protected $customerGroupManagement;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \VertexSMB\Tax\Helper\Config $vertexConfigHelper,
        \VertexSMB\Tax\Helper\Data $vertexSMBHelper,
        Address $addressHelper,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        CustomerGroupRepository $customerGroupRepository,
        CustomerGroupManagement $customerGroupManagement
    ) {
    
        $this->addressHelper = $addressHelper;
        $this->vertexSMBConfigHelper = $vertexConfigHelper;
        $this->customerRepository = $customerRepository;
        $this->vertexSMBHelper = $vertexSMBHelper;
        $this->customerGroupRepository = $customerGroupRepository;
        $this->customerGroupManagement = $customerGroupManagement;
        parent::__construct($context);
    }

    public function addCustomerInformation(\Magento\Quote\Model\Quote\Address $taxAddress)
    {
        $data = [];
        $street = $taxAddress->getStreet();
        
        $customerId = $taxAddress->getCustomerId();
        
        $address = $this->addressHelper->formatAddress(
            $street,
            $taxAddress->getCity(),
            $taxAddress->getRegionId(),
            $taxAddress->getPostcode(),
            $taxAddress->getCountryId()
        );
        
        $data['CustomerCode']['_'] = $this->getCustomerCodeById($customerId);
        
        if ($customerId) {
            $customerData = $this->customerRepository->getById($customerId);
            $customerTaxClass = $this->customerGroupRepository->getById($customerData->getGroupId())
                ->getTaxClassId();
        } else {
            $customerTaxClass = $this->customerGroupManagement->getNotLoggedInGroup()->getTaxClassId();
        }
        
        $data['CustomerCode']['classCode'] = $this->vertexSMBHelper->taxClassNameById($customerTaxClass);
        $data['Destination'] = $address;
        return $data;
    }

    public function getCustomerCodeById($customerId = 0)
    {
        $customerCode = "";
        if ($customerId) {
            $attr = $this->customerRepository->getById($customerId)->getCustomAttribute('customer_code');
            if (is_object($attr)) {
                $customerCode = $attr->getValue();
            }
        }
        
        if (empty($customerCode)) {
            $customerCode = $this->vertexSMBConfigHelper->getDefaultCustomerCode();
        }
        
        return $customerCode;
    }

    /**
     * @param int $groupId
     * @return string
     */
    public function taxClassNameByCustomerGroupId($groupId)
    {
        $classId = $this->customerGroupRepository->getById($groupId)->getTaxClassId();
        return $this->vertexSMBHelper->taxClassNameById($classId);
    }
}
