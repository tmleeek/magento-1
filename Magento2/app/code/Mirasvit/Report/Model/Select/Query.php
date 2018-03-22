<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-report
 * @version   1.1.15-beta3
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Report\Model\Select;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Query
{
    /**
     * @var Column[]
     */
    protected $usedColumnsPool = [];

    /**
     * @var \Mirasvit\Report\Model\Config\Map
     */
    protected $map;

    /**
     * @var \Magento\Framework\Module\Resource
     */
    protected $resource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var \Magento\Framework\DB\Select
     */
    protected $select;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var Column[]
     */
    protected $filteredColumns = [];

    /**
     * @var Table
     */
    protected $baseTable;

    /**
     * Query constructor.
     *
     * @param \Magento\Framework\App\ResourceConnection                                $resource
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\DocumentFactory $documentFactory
     * @param \Mirasvit\Report\Model\Config\Map                                        $map
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\View\Element\UiComponent\DataProvider\DocumentFactory $documentFactory,
        TimezoneInterface $timezone,
        $map
    ) {
        $this->map = $map;
        $this->resource = $resource;
        $this->documentFactory = $documentFactory;
        $this->timezone = $timezone;

        // reset data
        foreach ($this->map->getTables() as $table) {
            $table->setJoined(false);
        }
    }

    /**
     * @return \Mirasvit\Report\Model\Config\Map
     */
    public function getMap()
    {
        return $this->map;
    }

    /**
     * @param Table $table
     * @return $this
     */
    public function setBaseTable($table)
    {
        $this->baseTable = $table;
        $this->connection = $this->resource->getConnection($this->baseTable->getConnectionName());
        $this->select = $this->connection->select();

        $table->setJoined(true);

        $this->select->from(
            [$table->getName() => $this->resource->getTableName($table->getName())],
            [new \Zend_Db_Expr('1')]
        );

        return $this;
    }

    /**
     * @param Column $column
     * @return $this
     */
    public function addColumnToSelect(Column $column)
    {
        $column->addToSelect($this);

        return $this;
    }

    /**
     * @param Column $column
     * @return $this
     */
    public function groupByColumn(Column $column)
    {
        $this->usedColumnsPool[] = $column;

        $this->select->group($column->toString());

        return $this;
    }

    /**
     * @param int $page
     * @return $this
     */
    public function setCurrentPage($page)
    {
        $this->setLimit($page, $this->limit);

        return $this;
    }

    /**
     * @param int $size
     * @return $this
     */
    public function setPageSize($size)
    {
        $this->setLimit(1, $size);

        return $this;
    }

    /**
     * @return Column[]
     */
    public function getFilteredColumns()
    {
        return $this->filteredColumns;
    }

    /**
     * @param Column               $column
     * @param integer|string|array $condition
     * @return $this
     */
    public function addColumnToFilter(Column $column, $condition)
    {
        $this->filteredColumns[] = $column;
        $this->usedColumnsPool[] = $column;

        $conditionSql = $this->connection->prepareSqlCondition($column->toString(), $condition);

        if (strpos($conditionSql, 'COUNT(') !== false
            || strpos($conditionSql, 'AVG(') !== false
            || strpos($conditionSql, 'SUM(') !== false
            || strpos($conditionSql, 'CONCAT(') !== false
            || strpos($conditionSql, 'MIN(') !== false
            || strpos($conditionSql, 'MAX(') !== false
        ) {
            $this->select->having($conditionSql);
        } elseif ($condition) {
            $this->select->where($conditionSql);
        }

        return $this;
    }

    /**
     * @param Column $column
     * @param string $direction
     * @return $this
     */
    public function addColumnToOrder(Column $column, $direction)
    {
        $this->usedColumnsPool[] = $column;

        $this->select->order($column->toString() . ' ' . $direction);

        return $this;
    }

    /**
     * @param int $page
     * @param int $size
     * @return $this
     */
    public function setLimit($page, $size)
    {
        $this->limit = $size;
        $this->select->limitPage($page, $size);

        return $this;
    }

    /**
     * @return \Magento\Framework\DB\Select
     */
    public function getSelect()
    {
        return $this->select;
    }

    /**
     * @return array
     */
    public function load()
    {
        $this->beforeLoad();

        foreach ($this->usedColumnsPool as $column) {
            foreach ($column->getFields() as $field) {
                $field->join($this);
            }
        }

        $this->data = $this->connection->fetchAll($this->select);

        $this->afterLoad();

        return $this->data;
    }

    /**
     * @return void
     */
    protected function beforeLoad()
    {
        $utc = $this->connection->fetchOne('SELECT CURRENT_TIMESTAMP');
        $offset = (new \DateTimeZone($this->timezone->getConfigTimezone()))->getOffset(new \DateTime($utc));
        $h = floor($offset / 3600);
        $m = floor(($offset - $h * 3600) / 60);
        $offset = sprintf("%02d:%02d", $h, $m);

        if (substr($offset, 0, 1) != "-") {
            $offset = "+" . $offset;
        }

        $this->connection->query("SET time_zone = '$offset'");
    }

    /**
     * @return void
     */
    protected function afterLoad()
    {
        $this->connection->query("SET time_zone = '+00:00'");
    }

    /**
     * @return array
     */
    public function getItems()
    {
        $result = [];

        $items = $this->load();

        foreach ($items as $item) {
            $document = $this->documentFactory->create();
            $document->setData($item);

            $result[] = $document;
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getTotals()
    {
        $select = clone $this->select;
        $select->reset(\Zend_Db_Select::ORDER);
        $select->reset(\Zend_Db_Select::LIMIT_COUNT);
        $select->reset(\Zend_Db_Select::LIMIT_OFFSET);
        $select->reset(\Zend_Db_Select::GROUP);

        $result = [];

        $this->beforeLoad();
        $rows = $this->connection->fetchAll($select);
        $this->afterLoad();

        foreach ($rows as $row) {
            foreach ($row as $k => $v) {
                if (!isset($result[$k])) {
                    $result[$k] = null;
                }

                $result[$k] += $v;
                $result[$k] = round($result[$k], 2);
            }
        }

        return $result;
    }


    /**
     * @return int
     */
    public function getSize()
    {
        $countSelect = clone $this->select;
        $countSelect->reset(\Zend_Db_Select::ORDER);
        $countSelect->reset(\Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(\Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(\Zend_Db_Select::COLUMNS);
        $countSelect->columns();

        $select = 'SELECT COUNT(*) FROM (' . $countSelect->__toString() . ') as cnt';

        $result = $this->connection->fetchOne($select);

        return $result;
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * @var int
     */
    static $level = 0;

    /**
     * @param Table $table
     * @return bool
     */
    public function joinTable($table)
    {
        if ($table->isJoined()) {
            return true;
        }

        $relations = $this->joinWay($table);
        $relations = array_reverse($relations);

        /** @var Relation $relation */
        foreach ($relations as $relation) {
            if (!$relation->getRightTable()->isJoined()) {
                $tbl = $relation->getRightTable();

                $this->replicate($tbl);

                $this->select->joinLeft(
                    [$tbl->getName() => $this->resource->getTableName($tbl->getName())],
                    $relation->getCondition(),
                    []
                );
                $tbl->setJoined(true);
            }

            if (!$relation->getLeftTable()->isJoined()) {
                $tbl = $relation->getLeftTable();

                $this->replicate($tbl);

                $this->select->joinLeft(
                    [$tbl->getName() => $this->resource->getTableName($tbl->getName())],
                    $relation->getCondition(),
                    []
                );
                $tbl->setJoined(true);
            }
        }

        if ($relations) {
            return true;
        }

        return false;
    }

    /**
     * @param Table      $table
     * @param Relation[] $relations
     * @return Relation[]
     */
    protected function joinWay($table, $relations = [])
    {
        if ($table->isJoined()) {
            return $relations;
        }

        foreach ($this->map->getRelations() as $relation) {
            if (in_array($relation, $relations)) {
                continue;
            }

            if ($relation->getLeftTable() == $table) {
                if ($result = $this->joinWay($relation->getRightTable(), array_merge($relations, [$relation]))) {
                    return $result;
                }
            }

            if ($relation->getRightTable() == $table) {
                if ($result = $this->joinWay($relation->getLeftTable(), array_merge($relations, [$relation]))) {
                    return $result;
                }
            }
        }

        return [];
    }

    /**
     * @param Table $table
     * @return bool
     */
    protected function replicate($table)
    {
        if ($table->getConnectionName() == $this->baseTable->getConnectionName()) {
            return true;
        }

        $baseConnection = $this->resource->getConnection($this->baseTable->getConnectionName());

        $tblConnection = $this->resource->getConnection($table->getConnectionName());
        $tableName = $this->resource->getTableName($table->getName());

        if (!$baseConnection->isTableExists($tableName)) {
            $schema = $tblConnection->describeTable($tableName);

            $temporaryTable = $baseConnection->newTable($tableName);
            foreach ($schema as $column) {
                $type = $column['DATA_TYPE'];
                if ($column['DATA_TYPE'] == 'int') {
                    $type = 'integer';
                } elseif ($column['DATA_TYPE'] == 'varchar') {
                    $type = 'text';
                }

                $temporaryTable->setColumn([
                    'COLUMN_NAME'      => $column['COLUMN_NAME'],
                    'TYPE'             => $type,
                    'LENGTH'           => $column['LENGTH'],
                    'COLUMN_POSITION'  => $column['COLUMN_POSITION'],
                    'PRIMARY'          => $column['PRIMARY'],
                    'PRIMARY_POSITION' => $column['PRIMARY_POSITION'],
                    'NULLABLE'         => $column['PRIMARY'] ? false : $column['NULLABLE'],
                    'COMMENT'          => $column['COLUMN_NAME'],
                ]);
            }

            $baseConnection->createTemporaryTable($temporaryTable);

            $offset = 1;
            while (true) {
                $select = $tblConnection->select()->from($tableName)
                    ->limitPage($offset, 10000);
                $rows = $tblConnection->fetchAll($select);

                if (count($rows)) {
                    $baseConnection->insertMultiple($tableName, $rows);
                } else {
                    break;
                }

                $offset++;
            }
        }

        return true;
    }
}
