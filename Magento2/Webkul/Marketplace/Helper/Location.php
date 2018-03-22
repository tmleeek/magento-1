<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\Helper;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Webkul\Marketplace\Model\Product as SellerProduct;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Customer\Model\Context as CustomerContext;

/**
 * Webkul Marketplace Helper Data.
 */
class Location extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var null|array
     */
    protected $_options;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $_product;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @param \Magento\Framework\App\Helper\Context        $context
     * @param \Magento\Framework\ObjectManagerInterface    $objectManager
     * @param \Magento\Customer\Model\Session              $customerSession
     * @param CollectionFactory                            $collectionFactory
     * @param HttpContext                                  $httpContext
     * @param \Magento\Catalog\Model\ResourceModel\Product $product
     * @param \Magento\Store\Model\StoreManagerInterface   $storeManager
     * @param \Magento\Directory\Model\Currency            $currency
     * @param \Magento\Framework\Locale\CurrencyInterface  $localeCurrency
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        CollectionFactory $collectionFactory,
        HttpContext $httpContext,
        \Magento\Catalog\Model\ResourceModel\Product $product,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency
    ) {
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;
        $this->_collectionFactory = $collectionFactory;
        $this->httpContext = $httpContext;
        $this->_product = $product;
        parent::__construct($context);
        $this->_currency = $currency;
        $this->_localeCurrency = $localeCurrency;
        $this->_storeManager = $storeManager;
    }

    /**
     * Check if customer is logged in
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isCustomerLoggedIn()
    {
        return (bool)$this->httpContext->getValue(CustomerContext::CONTEXT_AUTH);
    }

    /**
     * Return the Customer seller status.
     *
     * @return bool|0|1
     */
    public function isSeller()
    {
        $sellerStatus = 0;
        $model = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Seller'
        )
            ->getCollection()
            ->addFieldToFilter(
                'seller_id',
                $this->_customerSession->getCustomerId()
            );
        foreach ($model as $value) {
            $sellerStatus = $value->getIsSeller();
        }

        return $sellerStatus;
    }
    private function getPostalCode($lat,$lng){
        $utl="https://maps.googleapis.com/maps/api/geocode/json?latlng=".$lat.",".$lng."&region=AU";
        $ch = curl_init();

            
            // Disable SSL verification
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            // Will return the response, if false it print the response
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            // Set the url
            curl_setopt($ch, CURLOPT_URL,$utl);
            // Execute
            $result=curl_exec($ch);
            // Closing
            curl_close($ch);
            $result=json_decode( json_encode(json_decode($result, true)));
            
            $pcode="";
            if($result!=null && $result->status=="OK"){
                $addressComponents = $result->results[0]->address_components;
               
                    foreach($addressComponents as $addrComp){
                        
                        if($addrComp->types[0] == 'postal_code'){
                            
                            $pcode= $addrComp->long_name;//$this->validPostalCode($addrComp->long_name);
                            break;
                        }
                }
               
            }
         
            return $pcode;
            
    }
    public function validPostalCode($postalCode,$lat="",$lng="")
    {
        /*if(!empty($lat) && !empty($lng)){
            return $this->getPostalCode($lat,$lng);
        }*/
        
        $postalCode = str_replace(" ", "+", $postalCode);

        if($postalCode){
            //  Initiate curl
             $details_url = "https://maps.googleapis.com/maps/api/geocode/json?sensor=false&address=" . $postalCode . "&key=AIzaSyB-AzuTu1Pq3K8U7X9pWDUKphcAO-txZVk&components=country:AU";

   $ch = curl_init();

            
            // Disable SSL verification
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            // Will return the response, if false it print the response
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            // Set the url
            curl_setopt($ch, CURLOPT_URL,$details_url);
            // Execute
            $result=curl_exec($ch);
            // Closing
            curl_close($ch);
//var_dump("https://maps.googleapis.com/maps/api/geocode/json?address=".urlencode($postalCode)."&region=AU&key=");
            // Will dump a beauty json :3
            
      
            $result=json_decode( json_encode(json_decode($result, true)));
           
            if($result!=null && $result->status=="OK"){
                

                    $addressComponents = $result->results[0]->address_components;
                    
                    foreach($addressComponents as $addrComp){
                        if($addrComp->types[0] == 'postal_code'){
                            
                            return $addrComp->long_name;//$this->validPostalCode($addrComp->long_name);
                            break;
                        }
                    }
                
                
                $lat = $result->results[0]->geometry->location->lat;
                $lng = $result->results[0]->geometry->location->lng; 
                            
                return $this->getPostalCode($lat,$lng);
               
                
            }
            else
                return "";
        }else{
            return "";
        }
    }


    public function getSearchData($postcode)
    {   
        $postcode = str_replace(" ", "+", $postcode);
        if($postcode){
            $latLong= $this->validPostalCode($postcode);
            $curLat=$latLong->geometry->location->lat;
            $curLng=$latLong->geometry->location->lng;      
     
            $this->db->select("u.*,p.PlanName,( 3959 * acos( cos( radians(".$curLat.") ) * cos( radians( u.lat ) ) * cos( radi`ans( u.lng ) - radians(".$curLng.") ) + sin( radians(".$curLat.") ) * sin( radians( u.lat ) ) ) ) AS distance")

            ->where("u.status",1)->having("u.distance_travallable>=distance")
            ->limit(20,$offcet)
            
            ->order_by("distance","asc");
            
        $query=$this->db->get("tbl_users as u");

            if ($query && $query->num_rows() > 0) {
                return $query->result_object();
            }
        }
        return false;
    }

}
