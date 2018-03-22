<?php
/**
 * Copyright © 2016 Pixafy Services LLC. All rights reserved.
 *
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL 3.0)
 * @author  Alex Lukyanau <alukyanau@pixafy.com>
 */
namespace VertexSMB\Tax\Model\TaxQuote;

use \Magento\Framework\DataObject;

class TaxQuoteRequest extends DataObject
{
 
    /**
     *
     * @var string
     */
    protected $requestType = 'quote';
    
    protected $taxQuoteResponse;
    
    protected $vertexSMB;
    
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
    // @codingStandardsIgnoreStart
    public function __construct(\Psr\Log\LoggerInterface $logger,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        TaxQuoteResponse $taxQuoteResponse,
        \VertexSMB\Tax\Model\VertexSMB $vertexSMB,
        array $data = []
    ) {// @codingStandardsIgnoreEnd 
    
    
        $this->logger = $logger;
        $this->taxQuoteResponse = $taxQuoteResponse;
        $this->vertexSMB=$vertexSMB;
        parent::__construct($data);
    }
    
    
    
    /**
     *
     * @param unknown $information
     * @return boolean|unknown
     */
    public function taxQuote($request)
    {
        
        $cacheKey = $this->_getRequestCacheKey($request);
         
        if (! isset($this->requestCache[$cacheKey])) {
            $requestResult = $this->vertexSMB->sendApiRequest($request, $this->requestType);
        
        
            /* if ($taxQuoteResult instanceof \Exception) {
            if (Mage::app()->getRequest()->getControllerName() == 'onepage' || Mage::app()->getRequest()->getControllerName() == 'sales_order_create') {
                Mage::log("Quote Request Error: " . $taxQuoteResult->getMessage() . "Controller:  " . $this->getHelper()->getSourcePath(), null, 'vertexsmb.log');
                $result = array(
                    'error' => 1,
                    'message' => "Tax calculation request error. Please check your address"
                );
                Mage::app()->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                $action = Mage::app()->getRequest()->getActionName();
                Mage::log("Controller action to dispatch " . $action, null, 'vertexsmb.log');
                Mage::app()->getFrontController()
                ->getAction()
                ->setFlag($action, Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
                return false;
            }
            if ($this->getHelper()->getSourcePath() == 'cart_checkout_index' || $this->getHelper()->getSourcePath() == 'cart_checkout_couponPost') {
                $this->getHelper()
                ->getSession()
                ->addError(Mage::helper('core')->escapeHtml("Tax Calculation Request Error. Please check your address"));
            }
    
            return false;
            }*/
    
            //$responseModel =$this->taxQuoteResponse->parseResponse($requestResult);
        
        
            /*  $this->setResponse($responseModel);
    
            $itemsTax = $responseModel->getTaxLineItems();
            $quoteTaxedItems = $responseModel->getQuoteTaxedItems();
    
            return $quoteTaxedItems;*/
            $this->requestCache[$cacheKey]=$requestResult;
            return $this->requestCache[$cacheKey];
        } else {
            return $this->requestCache[$cacheKey];
        }
    }
    
    /**
     * Get cache key value for specific address request
     *
     * @param  DataObject $request
     * @return string
     */
    protected function _getRequestCacheKey(array $request)
    {
        $key = serialize($request);
        return md5($key);
    }
}
