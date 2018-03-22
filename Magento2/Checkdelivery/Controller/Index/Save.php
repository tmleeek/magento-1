<?php

namespace Bluethink\Checkdelivery\Controller\Index;

class Save extends \Magento\Framework\App\Action\Action
{

	
    protected $_cacheTypeList;

    protected $_cacheState;

    protected $_cacheFrontendPool;

    protected $resultPageFactory;

    protected $_registry;

    public function __construct(
       \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry
       
    ) {
        parent::__construct($context);
        $this->_cacheTypeList = $cacheTypeList;
        $this->_cacheState = $cacheState;
        $this->_cacheFrontendPool = $cacheFrontendPool;
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
    }

    public function execute()
    {
    
      $data=$this->getRequest()->getPost('input');
      
      if(strlen($data)==6 && ctype_digit($data)) 
      {
          $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
          $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
          $connection = $resource->getConnection();
          $tableName = $resource->getTableName('checkdelivery_pincode');
          $sql = "Select * FROM " . $tableName." Where pincode = ".$data."";
          $result = $connection->fetchAll($sql);
 
          if(!empty($result))
          {
            echo "Delivery Avialable of this Area";
          }else
          {
             echo "Delivery Not Avialable of this Area";
          }
      }else{
            echo "Invalid pincode";
          }

     
    }


}
