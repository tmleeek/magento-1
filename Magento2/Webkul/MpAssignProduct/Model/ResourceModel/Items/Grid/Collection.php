<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAssignProduct\Model\ResourceModel\Items\Grid;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Search\AggregationInterface;
use Webkul\MpAssignProduct\Model\ResourceModel\Items\Collection as ItemsCollection;

class Collection extends ItemsCollection implements SearchResultInterface
{
    /**
     * @var AggregationInterface
     */
    protected $_aggregations;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entity
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $event
     * @param \Magento\Store\Model\StoreManagerInterface $store
     * @param mixed|null $mainTable
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $eventPrefix
     * @param mixed $eventObject
     * @param mixed $resourceModel
     * @param string $model
     * @param null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entity,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $event,
        \Magento\Store\Model\StoreManagerInterface $store,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = 'Magento\Framework\View\Element\UiComponent\DataProvider\Document',
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->_objectManager = $objectManager;
        parent::__construct($entity, $logger, $fetchStrategy, $event, $store, $connection, $resource);
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
    }

    /**
     * @return AggregationInterface
     */
    public function getAggregations()
    {
        return $this->_aggregations;
    }

    /**
     * @param AggregationInterface $aggregations
     *
     * @return $this
     */
    public function setAggregations($aggregations)
    {
        $this->_aggregations = $aggregations;
    }

    /**
     * Retrieve all ids for collection
     * Backward compatibility with EAV collection.
     *
     * @param int $limitCount
     * @param int $offset
     *
     * @return array
     */
    public function getAllIds($limitCount = null, $offset = null)
    {
        return $this->getConnection()->fetchCol($this->_getAllIdsSelect($limitCount, $offset), $this->_bindParams);
    }

    /**
     * Set search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }
    
    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return;
    }

    /**
     * Set items list.
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface[] $items
     *
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count.
     *
     * @param int $totalCount
     *
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    

    /**
     * Join store relation table if there is store filter.
     */
    protected function _renderFiltersBefore()
    {
        $eavAttribute = $this->_objectManager->get('Magento\Eav\Model\ResourceModel\Entity\Attribute');
        $proAttId = $eavAttribute->getIdByCode('catalog_product', 'name');
        $proPriceAttId = $eavAttribute->getIdByCode('catalog_product', 'price');

        $customerGridFlat = $this->getTable('customer_grid_flat');
        $catalogProductEntityVarchar = $this->getTable('catalog_product_entity_varchar');
        $catalogProductEntityDecimal = $this->getTable('catalog_product_entity_decimal');
        $cataloginventoryStockItem = $this->getTable('cataloginventory_stock_item');

        $sql = $customerGridFlat.' as cgf';
        $cond = 'main_table.seller_id = cgf.entity_id';
        $fields = ['name' => 'name'];
        $this->getSelect()
            ->join($sql, $cond, $fields);
        $this->addFilterToMap('name', 'cgf.name');

        $sql = $catalogProductEntityVarchar.' as cpev';
        $cond = 'main_table.product_id = cpev.entity_id';
        $fields = ['product_name' => 'value'];
        $this->getSelect()
            ->join($sql, $cond, $fields)
            ->where('cpev.store_id = 0 AND cpev.attribute_id = '.$proAttId);
        $this->addFilterToMap('product_name', 'cpev.value');

        $sql = $catalogProductEntityDecimal.' as cped';
        $cond = 'main_table.product_id = cped.entity_id';
        $fields = ['product_price' => 'value'];
        $this->getSelect()
            ->join($sql, $cond, $fields)
            ->where('cped.store_id = 0 AND cped.attribute_id = '.$proPriceAttId);
        $this->addFilterToMap('product_price', 'cped.value');

        $this->addFilterToMap('main_qty', 'csi.qty');
        parent::_renderFiltersBefore();
    }
}
