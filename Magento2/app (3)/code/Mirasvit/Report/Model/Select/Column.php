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

use Magento\Framework\App\ObjectManager;

class Column
{
    const TYPE_EXPRESSION = 'expression';
    const TYPE_AGGREGATION = 'aggregation';
    const TYPE_SIMPLE = 'simple';

    /**
     * @var int
     */
    protected static $colorIndex = 0;

    /**
     * @var Table
     */
    protected $table;

    /**
     * @var string[]
     */
    protected $tables = [];

    /**
     * @var string
     */
    protected $expression;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $dataType;

    /**
     * @var string|array
     */
    protected $options;

    /**
     * @var string
     */
    protected $selectType;

    /**
     * @var string
     */
    protected $color;

    /**
     * @var Field[]
     */
    protected $fieldsPool = [];

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @param string $name
     * @param array  $data
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function __construct(
        $name,
        $data = []
    ) {
        $this->name = $name;
        $this->objectManager = ObjectManager::getInstance();;

        $this->selectType = self::TYPE_SIMPLE;

        if (isset($data['expr'])) {
            $this->expression = $data['expr'];
        } else {
            $this->expression = '%1';
        }

        if (isset($data['label'])) {
            $this->label = $data['label'];
        }

        if (isset($data['type'])) {
            $this->dataType = $data['type'];
        }

        if (isset($data['options'])) {
            $this->options = $data['options'];
        }

        if (isset($data['color'])) {
            $this->color = $data['color'];
        }

        if (isset($data['table'])) {
            $this->table = $data['table'];
            $this->table->addColumn($this);
        }

        if (isset($data['tables'])) {
            $this->tables = explode(',', $data['tables']);
        }

        if (isset($data['fields'])) {
            foreach ($data['fields'] as $field) {
                $this->fieldsPool[] = $this->table->getField($field);
            }
        }

        $this->resource = $this->objectManager->get('Magento\Framework\App\ResourceConnection');
        $this->connection = $this->resource->getConnection();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->table->getName() . '|' . $this->name;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * @return string
     */
    public function getSelectType()
    {
        return $this->selectType;
    }

    /**
     * @return array|string
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @return void
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @param array $fields
     * @return $this
     */
    public function setFields($fields)
    {
        $this->fieldsPool = [];
        foreach ($fields as $field) {
            $this->fieldsPool[] = $this->table->getField($field);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        if ($this->color == null) {
            $colors = [
                '#FFD963', #yellow
                '#FF5A3E', #red
                '#77B6E7', #blue
                '#97CC64', #green
                '#A9B9B8',
                '#DC9D6B',
            ];

            $this->color = $colors[self::$colorIndex];
            if (++self::$colorIndex >= count($colors)) {
                self::$colorIndex = 0;
            }
        }

        return $this->color;
    }

    /**
     * @param string $expression
     * @return $this
     */
    public function setExpression($expression)
    {
        $this->expression = $expression;
        return $this;
    }

    /**
     * @return Field[]
     */
    public function getFields()
    {
        return $this->fieldsPool;
    }

    /**
     * @return \Zend_Db_Expr
     */
    public function toString()
    {
        $exr = $this->expression;
        $idx = 1;
        /** @var \Mirasvit\Report\Model\Select\Column $column */
        foreach ($this->fieldsPool as $column) {
            $exr = str_replace('%' . $idx, $column->toString(), $exr);
            $idx++;
        }

        return new \Zend_Db_Expr($exr);
    }

    /**
     * @return \Zend_Db_Expr
     */
    public function toSelectString()
    {
        return $this->toString();
    }

    /**
     * @param Query $query
     * @return void
     */
    public function addToSelect(Query $query)
    {
        foreach ($this->tables as $table) {
            $query->joinTable($query->getMap()->getTable($table));
        }

        foreach ($this->fieldsPool as $column) {
            $column->join($query);
        }

        $query->getSelect()->columns([$this->getName() => $this->toSelectString()]);
    }

    /**
     * @param string $value
     * @return string
     */
    public function prepareValue($value)
    {
        return $value;
    }

    /**
     * @param int $value
     * @return string
     */
    public function toOptionText($value)
    {
        if ($this->getOptions()) {
            if (is_string($this->getOptions())) {
                $options = $this->objectManager->get($this->getOptions())->toOptionArray();
            } else {
                $options = $this->getOptions();
            }

            $arValue = explode(',', $value);
            $newValue = [];
            foreach ($options as $option) {
                if (in_array($option['value'], $arValue)) {
                    $newValue[] = $option['label'];
                }
            }
            $value = implode(', ', $newValue);
        }

        return $value;
    }

    /**
     * @return array
     */
    public function getJsConfig()
    {
        return [
            'column' => $this->getName(),
            'label'  => $this->getLabel(),
        ];
    }
}
