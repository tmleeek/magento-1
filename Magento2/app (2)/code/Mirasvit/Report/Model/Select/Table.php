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

use Magento\Framework\App\ResourceConnection;

class Table
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var Field[]
     */
    protected $fieldsPool = [];

    /**
     * @var Column[]
     */
    protected $columnsPool = [];

    /**
     * @var bool
     */
    protected $isJoined = false;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var string
     */
    protected $connectionName;

    /**
     * @param ResourceConnection $resource
     * @param FieldFactory       $fieldFactory
     * @param string             $name
     * @param string             $connection
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        FieldFactory $fieldFactory,
        $name,
        $connection = 'default'
    ) {
        $this->name = $name;
        $this->connectionName = $connection;

        $this->resource = $resource;
        $this->connection = $resource->getConnection($this->connectionName);
        $this->fieldFactory = $fieldFactory;

        $this->initFields();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getConnectionName()
    {
        return $this->connectionName;
    }

    /**
     * @param Column $column
     * @return void
     */
    public function addColumn(Column $column)
    {
        $this->columnsPool[$column->getName()] = $column;
    }

    /**
     * @param string $name
     * @return Column
     * @throws \Exception
     */
    public function getColumn($name)
    {
        if (isset($this->columnsPool[$name])) {
            return $this->columnsPool[$name];
        } else {
            throw new \Exception(__('Undefined column "%1"', $name));
        }
    }

    /**
     * @param array $types
     * @return Column[]
     */
    public function getColumns($types = [])
    {
        if (!count($types)) {
            return $this->columnsPool;
        } else {
            $result = [];

            foreach ($this->columnsPool as $column) {
                if (in_array($column->getSelectType(), $types)) {
                    $result[] = $column;
                }
            }

            return $result;
        }
    }

    /**
     * @return bool
     */
    public function isJoined()
    {
        return $this->isJoined;
    }

    /**
     * @param bool $flag
     * @return $this
     */
    public function setJoined($flag)
    {
        $this->isJoined = $flag;

        return $this;
    }

    /**
     * @param string $name
     * @return Field
     */
    public function getField($name)
    {
        return $this->fieldsPool[trim($name)];
    }

    /**
     * @return void
     */
    public function initFields()
    {
        $tableName = $this->resource->getTableName($this->name);
        $fields = $this->connection->describeTable($tableName);

        foreach (array_keys($fields) as $fieldName) {
            /** @var Field $field */
            $field = $this->fieldFactory->create([
                'table' => $this,
                'name'  => $fieldName,
            ]);

            $this->fieldsPool[$field->getName()] = $field;
        }
    }
}
