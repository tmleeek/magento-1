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
        $response = [];
        /*$jsondata = file_get_contents($response);
        echo "<pre>=====";
        print_r($jsondata);*/
        /*exit;*/
        try {
            if (!$this->getRequest()->isAjax()) {
                throw new \Exception('Invalid request.');
            }
            if (!$postcode = $this->getRequest()->getParam('postcode')) {
                throw new \Exception('Please enter postcode.');
            }
            $postcode = $this->getRequest()->getParam('postcode')
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
          $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
          $connection = $resource->getConnection();
          $tableName = $resource->getTableName('checkdelivery_pincode');
          $sql = "Select * FROM " . $tableName." Where pincode = ".$postcode."";
          $result = $connection->fetchAll($sql);
        } catch (\Exception $e) {
            $response['type'] = 'error';
            $response['message'] = $e->getMessage();
        }
        $this->getResponse()->setContent(json_encode($response));
    }


}
