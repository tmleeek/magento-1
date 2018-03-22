<?php
/**
 * Copyright © 2016 Pixafy Services LLC. All rights reserved.
 *
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL 3.0)
 * @author  Alex Lukyanau <alukyanau@pixafy.com>
 */
namespace VertexSMB\Tax\Helper\Request;

/**
 * Seller information helper
 */
class Seller extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $vertexSMBConfigHelper;

    protected $addressHelper;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \VertexSMB\Tax\Helper\Config $vertexSMBConfigHelper,
        Address $addressHelper
    ) {
    
        $this->addressHelper = $addressHelper;
        $this->vertexSMBConfigHelper = $vertexSMBConfigHelper;
        parent::__construct($context);
    }

    public function addSellerInformation($store = null)
    {
        $data = [];
        
        $address = $this->addressHelper->formatAddress(
            [$this->vertexSMBConfigHelper->getCompanyStreet1($store),
             $this->vertexSMBConfigHelper->getCompanyStreet2($store)],
            $this->vertexSMBConfigHelper->getCompanyCity($store),
            $this->vertexSMBConfigHelper->getCompanyRegionId($store),
            $this->vertexSMBConfigHelper->getCompanyPostalCode($store),
            $this->vertexSMBConfigHelper->getCompanyCountry($store)
        );
        
        $data['Company'] = $this->vertexSMBConfigHelper->getCompanyCode();
        $data['PhysicalOrigin'] = $address;
        return $data;
    }
}
