<?php
/**
 * Copyright © 2016 Pixafy Services LLC. All rights reserved.
 *
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL 3.0)
 * @author  Alex Lukyanau <alukyanau@pixafy.com>
 */
namespace VertexSMB\Tax\Model\TaxArea;

use \Magento\Framework\DataObject;

class TaxAreaResponse extends DataObject
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    
    protected $objectManager;
    
    
    /**
     * @param \Psr\Log\LoggerInterface                  $logger
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array                                     $data
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->logger = $logger;
        $this->objectManager= $objectManager;
        parent::__construct($data);
    }
  
     
    /**
     * @param \stdClass $responseObject
     * @param array     $requestData
     * @return \VertexSMB\Tax\Model\TaxArea\TaxAreaResponse
     */
    public function parseResponse(array $responseArray, array $requestData)
    {
        $this->setRequestCity($requestData['TaxAreaRequest']['TaxAreaLookup']['PostalAddress']['City']);
        $this->setTaxAreaResults($responseArray);
        $this->setResultsCount(count($responseArray));
        return $this;
    }
 
 
    /**
     * @return \Magento\Framework\DataObject
     */
    public function getFirstTaxAreaInfo()
    {
        $collection = $this->getTaxAreaLocationsCollection();
        return $collection->getFirstItem();
    }

    /**
     * Used for popup window frontend/adminhtml
     * @return \Magento\Framework\Data\Collection
     */
    public function getTaxAreaLocationsCollection()
    {
        if (!$this->getData("tax_area_locations")) {
            $taxAreaInfoCollection=$this->objectManager->create('\Magento\Framework\Data\Collection');
            if (! $this->getTaxAreaResults()) {
                return $taxAreaInfoCollection;
            }
            $taxAreaResults = $this->getTaxAreaResults();
            if (isset($taxAreaResults['TaxAreaResult']) && !isset($taxAreaResults['TaxAreaResult']['taxAreaId'])) {
                $taxAreaResults = $taxAreaResults['TaxAreaResult'];
            }
            foreach ($taxAreaResults as $taxResponse) {
                if (!isset($taxResponse['taxAreaId'])) {
                    continue;
                }
                $taxJurisdictions = $taxResponse['Jurisdiction'];
                krsort($taxJurisdictions);
                $areaNames = [];
                $areaName = "";
                foreach ($taxJurisdictions as $areaJursdiction) {
                    $areaNames[] = $areaJursdiction['_'];
                }
                $areaName = ucwords(strtolower(implode(', ', $areaNames)));

                $taxAreaInfo = $this->objectManager->create('\Magento\Framework\DataObject');
                $taxAreaInfo->setAreaName($areaName);
                $taxAreaInfo->setTaxAreaId($taxResponse['taxAreaId']);
                $taxAreaInfo->setConfidenceIndicator($taxResponse['confidenceIndicator']);
                if (isset($taxResponse["PostalAddress"])) {
                    $taxAreaInfo->setTaxAreaCity($taxResponse["PostalAddress"]);
                } else {
                    $taxAreaInfo->setTaxAreaCity($this->getRequestCity());
                }

                $taxAreaInfo->setRequestCity($this->getRequestCity());
                $taxAreaInfoCollection->addItem($taxAreaInfo);
            }
            $this->setData("tax_area_locations", $taxAreaInfoCollection);
        }
        return $this->getData("tax_area_locations");
    }

    public function getTaxAreaWithHighestConfidence()
    {
        $taxAreaCollection = $this->getTaxAreaLocationsCollection();
        $confidence = 0;
        $selectedTaxArea = null;
        foreach ($taxAreaCollection as $taxAreaInfo) {
            if ($taxAreaInfo->getConfidenceIndicator() > $confidence) {
                $confidence = $taxAreaInfo->getConfidenceIndicator();
                $selectedTaxArea = $taxAreaInfo;
            }
        }
        return $selectedTaxArea;
    }
}
