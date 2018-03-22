<?php
/**
 * Copyright © 2016 Pixafy Services LLC. All rights reserved.
 *
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL 3.0)
 * @author  Alex Lukyanau <alukyanau@pixafy.com>
 */
namespace VertexSMB\Tax\Model\TaxArea;

use \Magento\Framework\DataObject;

class TaxAreaRequest extends DataObject
{

    /**
     *
     * @var unknown
     */
    protected $objectManager;

    /**
     *
     * @var unknown
     */
    protected $vertexSMB;

    /**
     *
     * @var unknown
     */
    protected $taxAreaResponse;

    /**
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     *
     * @var \VertexSMB\Tax\Helper\Config
     */
    protected $vertexSMBConfigHelper;

    /**
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     *
     * @var string
     */
    protected $requestType = 'tax_area_lookup';

    /**
     * Cache to hold the rates
     *
     * @var array
     */
    protected $requestCache = [];

    /**
     *
     * @param \Psr\Log\LoggerInterface                  $logger
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \VertexSMB\Tax\Helper\Config              $vertexConfigHelper
     * @param VertexSMB                                 $vertexSMB
     * @param TaxAreaResponse                           $taxAreaResponse
     * @param array                                     $data
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \VertexSMB\Tax\Helper\Config $vertexConfigHelper,
        \VertexSMB\Tax\Model\VertexSMB $vertexSMB,
        TaxAreaResponse $taxAreaResponse,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
    
        $this->logger = $logger;
        $this->objectManager = $objectManager;
        $this->vertexSMBConfigHelper = $vertexConfigHelper;
        $this->vertexSMB = $vertexSMB;
        $this->taxAreaResponse = $taxAreaResponse;
        $this->storeManager = $storeManager;
        parent::__construct($data);
    }

    /**
     * @param DataObject $address
     * @return \VertexSMB\Tax\Model\TaxArea\TaxAreaRequest
     */
    protected function prepareRequest(array $address)
    {
        $request = [
            'Login' => [
                'TrustedId' => $this->vertexSMBConfigHelper->getTrustedId()
            ],
            'TaxAreaRequest' => [
                'TaxAreaLookup' => [
                    'PostalAddress' => $address
                ]
            ]
        ];
        
        return $request;
    }

    /**
     * @param DataObject $address
     * @return boolean
     */
    public function taxAreaLookup(array $address)
    {
        /* if (! $address['StreetAddress1'] || !  $address['City'] || ! $address['MainDivision'] || !  $address['PostalCode']) {
            $this->logger->addError("Tax area lookup error: request information not exist");
            return false;
        }*/
        $cacheKey = $this->_getRequestCacheKey($address);
         
        if (! isset($this->requestCache[$cacheKey])) {
            $requestData = $this->prepareRequest($address);
            $requestResult = $this->vertexSMB->sendApiRequest($requestData, $this->requestType);
            
            if ($requestResult instanceof \Exception) {
                $this->logger->addError("Tax Area Lookup Error: " . $requestResult->getMessage());
                return false;
            }
            
            $response = $this->taxAreaResponse->parseResponse($requestResult, $requestData);

            
            $this->requestCache[$cacheKey] = $response;
        } else {
            $response=$this->requestCache[$cacheKey];
        }
        $this->setResponse($response);
        return true;
    }

    /**
     * Get cache key value for specific address request
     *
     * @param  DataObject $request
     * @return string
     */
    protected function _getRequestCacheKey(array $address)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $key = $storeId . '|';
        
        $key .=$address['StreetAddress1'] . '|' . $address['StreetAddress2'] . '|' .$address['Country'] . '|' .
             $address['City'] . '|' . $address['MainDivision'] . '|' . $address['PostalCode'];
        return md5($key);
    }
}
