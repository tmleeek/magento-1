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
class Address extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $directoryRegion;

    protected $directoryCountry;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Directory\Model\Region $directoryRegion,
        \Magento\Directory\Model\Country $directoryCountry
    ) {
    
        $this->directoryRegion = $directoryRegion;
        $this->directoryCountry = $directoryCountry;
        parent::__construct($context);
    }

    public function formatAddress($street, $city, $regionId, $postalCode, $country)
    {
        $companyState = $this->getCompanyStateByRegiondId($regionId);
        $countryName = $this->getCountryISO3Code($country);
        $street2="";
        
        if (is_array($street)) {
            $street1=$street[0];
            if (isset($street[1]) && !is_null($street[1])) {
                $street2=$street[1];
            }
        } else {
            $street1=$street;
        }
         
        
        $data = [
            'StreetAddress1' => $street1,
            'StreetAddress2' => $street2,
            'City' => $city,
            'MainDivision' => $companyState,
            'PostalCode' => $postalCode,
            'Country' => $countryName
        ];
        
        return $data;
    }

    /**
     *
     * @param unknown $regionId
     */
    public function getCompanyStateByRegiondId($regionId)
    {
        $companyState = $regionId;
        
        if (is_numeric($regionId)) {
            $regionModel = $this->directoryRegion->load($regionId);
            $companyState = $regionModel->getCode();
        }
        
        return $companyState;
    }

    public function getCountryISO3Code($companyCountry)
    {
        $countryModel = $this->directoryCountry->load($companyCountry);
        return $countryModel->getData('iso3_code');
    }
}
