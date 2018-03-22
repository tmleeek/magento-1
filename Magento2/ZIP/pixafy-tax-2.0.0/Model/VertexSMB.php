<?php
/**
 * Copyright © 2016 Pixafy Services LLC. All rights reserved.
 *
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL 3.0)
 * @author  Alex Lukyanau <alukyanau@pixafy.com>
 */
namespace VertexSMB\Tax\Model;

use \Magento\Framework\DataObject;

class VertexSMB extends DataObject
{

    /**
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     *
     * @var \VertexSMB\Tax\Helper\Data
     */
    protected $vertexSMBHelper;

    /**
     *
     * @var \VertexSMB\Tax\Helper\Config
     */
    protected $vertexSMBConfigHelper;
    
    protected $messageManager;
    
    protected $storeManager;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \VertexSMB\Tax\Helper\Data $vertexHelper,
        \VertexSMB\Tax\Helper\Config $vertexConfigHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
    
        $this->logger = $logger;
        $this->objectManager = $objectManager;
        $this->vertexSMBHelper = $vertexHelper;
        $this->vertexSMBConfigHelper = $vertexConfigHelper;
        $this->messageManager = $messageManager;
        $this->storeManager = $storeManager;
        parent::__construct($data);
    }

    /**
     *
     * @param array   $request
     * @param unknown $type
     * @param unknown $order
     * @return Exception|array
     */
    public function sendApiRequest(array $request, $type, $order = null)
    {
        $objectData = $this->_getObjectId($type, $order);
        $objectId = $objectData[0];
        $storeId = $objectData[1];
        try {
            list($client, $taxRequestResult) = $this->_sendRequest($type, $request, $storeId);
            $taxRequestResultArray = json_decode(json_encode($taxRequestResult), true);
            //var_dump($taxRequestResultArray);exit;
            //$this->logger->debug($taxRequestResultArray);
            $taxRequestResultArray = $taxRequestResultArray[array_keys($taxRequestResultArray)[1]];
            //$this->logger->debug($taxRequestResultArray);
            list($totalTax, $taxAreaId) = $this->_getTaxAreaId($type, $taxRequestResultArray);
            $this->logRequest($type, $objectId, $client->__getLastRequest(), $client->__getLastResponse(), $totalTax, $taxAreaId);
            return $taxRequestResultArray;
        } catch (\Exception $e) {
            if (isset($client) && $client instanceof \SoapClient && ! is_null($client->__getLastResponse())) {
                $taxRequestResult = $this->logRequest($type, $objectId, $client->__getLastRequest(), $client->__getLastResponse());
            }
            $this->logger->critical($e);
            return $e;
        }
    }

    /**
     *
     * @param unknown $type
     * @param unknown $objectId
     * @param unknown $requestXml
     * @param unknown $responseXml
     * @param number  $totalTax
     * @param number  $taxAreaId
     * @return void
     */
    protected function logRequest($type, $objectId, $requestXml, $responseXml, $totalTax = 0, $taxAreaId = 0)
    {
        //$this->logger->debug(print_r($taxRequestResultArray, true));
        if (is_array($totalTax)) {
            $totalTax = $totalTax["_"];
        }
        $taxRequest = $this->objectManager->create('\VertexSMB\Tax\Model\TaxRequest');
        $timestamp = $this->objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime')->timestamp(time());
        $taxRequest->setRequestType($type)->setRequestDate(date("Y-m-d H:i:s", $timestamp));
        if (strpos($type, 'invoice') === 0) {
            $taxRequest->setOrderId($objectId);
        } elseif ($type == 'quote' || $type == 'tax_area_lookup') {
            $taxRequest->setQuoteId($objectId);
        }
        
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->loadXML($requestXml);
        $dom->formatOutput = true;
        
        if ($dom->saveXml()) {
            $requestXml = $dom->saveXml();
        }
        
        $dom->loadXML($responseXml);
        
        $dom->formatOutput = true;
        if ($dom->saveXml()) {
            $responseXml = $dom->saveXml();
        }
        
        $totalNode = $dom->getElementsByTagName('Total');
        $subtotalNode = $dom->getElementsByTagName('SubTotal');
        $lookupResultNode = $dom->getElementsByTagName('Status');
        $addressLookupFaultNode = $dom->getElementsByTagName('exceptionType');
        $total = 0;
        $subtotal = 0;
        $lookupResult = "";
        
        if ($totalNode->length > 0) {
            $total = $totalNode->item(0)->nodeValue;
        }
        
        if ($subtotalNode->length > 0) {
            $subtotal = $subtotalNode->item(0)->nodeValue;
        }
        
        if ($lookupResultNode->length > 0) {
            $lookupResult = $lookupResultNode->item(0)->getAttribute('lookupResult');
        }
        
        if (! $lookupResult && $addressLookupFaultNode->length > 0) {
            $lookupResult = $addressLookupFaultNode->item(0)->nodeValue;
        }
        
        $sourcePath = $this->vertexSMBHelper->getSourcePath();
        $taxRequest->setSourcePath($sourcePath);
        $taxRequest->setTotalTax($totalTax);
        $taxRequest->setRequestXml($requestXml);
        $taxRequest->setResponseXml($responseXml);
        $taxRequest->setTaxAreaId($taxAreaId);
        $taxRequest->setTotal($total);
        $taxRequest->setSubTotal($subtotal);
        $taxRequest->setLookupResult($lookupResult);
        $taxRequest->save();
    }

    protected function _sendRequest($type, $request, $storeId = null)
    {
        $apiUrl = $this->vertexSMBConfigHelper->getVertexHost($storeId);
        if ($type == 'tax_area_lookup') {
            $apiUrl = $this->vertexSMBConfigHelper->getVertexAddressHost($storeId);
        }
        if (stripos($apiUrl, "wsdl") === false) {
            $apiUrl .= "?wsdl";
        }
        //echo $apiUrl;
        $soapParams = [
            'connection_timeout' => 300,
            'trace' => true,
            'soap_version' => SOAP_1_1
        ];
        $context = stream_context_create(
            [
                'ssl' => [
                    'ciphers' => 'DHE-RSA-AES256-SHA:DHE-DSS-AES256-SHA:AES256-SHA:KRB5-DES-CBC3-MD5:KRB5-DES-CBC3-SHA:EDH-RSA-DES-CBC3-SHA:EDH-DSS-DES-CBC3-SHA:DES-CBC3-SHA:DES-CBC3-MD5:DHE-RSA-AES128-SHA:DHE-DSS-AES128-SHA:AES128-SHA:RC2-CBC-MD5:KRB5-RC4-MD5:KRB5-RC4-SHA:RC4-SHA:RC4-MD5:RC4-MD5:KRB5-DES-CBC-MD5:KRB5-DES-CBC-SHA:EDH-RSA-DES-CBC-SHA:EDH-DSS-DES-CBC-SHA:DES-CBC-SHA:DES-CBC-MD5:EXP-KRB5-RC2-CBC-MD5:EXP-KRB5-DES-CBC-MD5:EXP-KRB5-RC2-CBC-SHA:EXP-KRB5-DES-CBC-SHA:EXP-EDH-RSA-DES-CBC-SHA:EXP-EDH-DSS-DES-CBC-SHA:EXP-DES-CBC-SHA:EXP-RC2-CBC-MD5:EXP-RC2-CBC-MD5:EXP-KRB5-RC4-MD5:EXP-KRB5-RC4-SHA:EXP-RC4-MD5:EXP-RC4-MD5',
                ],
            ]
        );
        $soapParams['stream_context'] = $context; // for TLS 1.2
        $client = new \SoapClient($apiUrl, $soapParams);

        if ($type == 'tax_area_lookup') {
            $lookupFunc = $this->vertexSMBConfigHelper->getValidationFunction($storeId);
            if (!$lookupFunc) {
                throw new \Exception("NO Validation function set");
            }
            $taxRequestResult = $client->$lookupFunc($request);
        } else {
            $caluclationFunc = $this->vertexSMBConfigHelper->getCalculationFunction($storeId);
            if (!$caluclationFunc) {
                throw new \Exception("NO Calculation function set");
            }
            $taxRequestResult = $client->$caluclationFunc($request);
        }
        return [$client, $taxRequestResult];
    }

    protected function _getObjectId($type, $order = null)
    {
        $objectId = null;
        $storeId = null;
        if (strpos($type, 'invoice') === 0) {
            $objectId = $order->getId();
            $storeId = $order->getStoreId();
        } elseif ($type == 'quote') {
            $quote = $this->vertexSMBHelper->getSession()->getQuote();
            $objectId = $quote->getId();
            $storeId = $quote->getStoreId();
        } elseif ($type == 'tax_area_lookup') {
            $objectId = 0;
            if (is_object($this->vertexSMBHelper->getSession()->getQuote())) {
                $quote = $this->vertexSMBHelper->getSession()->getQuote();
                $objectId = $quote->getId();
                $storeId = $quote->getStoreId();
            } else {
                $storeId = $this->storeManager->getStore()->getId();
            }
        }

        return [$objectId,$storeId];
    }

    protected function _getTaxAreaId($type, $taxRequestResultArray)
    {
        $taxAreaId = 0;
        $totalTax = 0;
        if (strpos($type, 'invoice') === 0) {
            $invoiceRequest = $taxRequestResultArray;
            $totalTax = $invoiceRequest['TotalTax'];
            $lineItems = $invoiceRequest['LineItem'];
            
            foreach ($lineItems as $lineItem) {
                if (isset($lineItem['Customer'])) {
                    $taxAreaId = $lineItem['Customer']['Destination']['taxAreaId'];
                    break;
                }
            }
        } elseif ($type == 'quote') {
            $quotationRequest = $taxRequestResultArray;
            $totalTax = $quotationRequest['TotalTax'];
            $lineItems = $quotationRequest['LineItem'];
            
            foreach ($lineItems as $lineItem) {
                $taxAreaId = $lineItem['Customer']['Destination']['taxAreaId'];
                break;
            }
        } elseif ($type == 'tax_area_lookup') {
            $taxAreaResults = $taxRequestResultArray;
            //var_dump($request);exit;
            //var_dump($taxAreaResults);exit;
            $taxAreaIds = [];
            foreach ($taxAreaResults as $taxAreaResult) {
                if (isset($taxAreaResult['taxAreaId'])) {
                    $taxAreaIds[] = $taxAreaResult['taxAreaId'];
                }
            }
            $taxAreaId = implode(',', $taxAreaIds);
        }
        return [$totalTax, $taxAreaId];
    }
}
