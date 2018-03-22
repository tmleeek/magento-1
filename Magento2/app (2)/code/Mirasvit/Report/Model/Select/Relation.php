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

class Relation
{
    /**
     * @var Table
     */
    public $leftTable;

    /**
     * @var Table
     */
    public $rightTable;

    /**
     * @var string
     */
    protected $condition;

    /**
     * @var string
     */
    protected $type;

    /**
     * Relation constructor.
     *
     * @param Table  $leftTable
     * @param Table  $rightTable
     * @param string $condition
     * @param string $type
     */
    public function __construct(
        Table $leftTable,
        Table $rightTable,
        $condition,
        $type
    ) {
        $this->leftTable = $leftTable;
        $this->rightTable = $rightTable;
        $this->condition = $condition;
        $this->type = $type;
    }

    /**
     * @param Table $table
     * @return $this
     */
    public function setLeftTable(Table $table)
    {
        $this->leftTable = $table;

        return $this;
    }

    /**
     * @param Table $table
     * @return $this
     */
    public function setRightTable(Table $table)
    {
        $this->rightTable = $table;

        return $this;
    }

    /**
     * @param string $condition
     * @return $this
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;

        return $this;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Table
     */
    public function getLeftTable()
    {
        return $this->leftTable;
    }

    /**
     * @return Table
     */
    public function getRightTable()
    {
        return $this->rightTable;
    }

    /**
     * @return string
     */
    public function getCondition()
    {
        $condition = str_replace('%1', $this->leftTable->getName(), $this->condition);
        $condition = str_replace('%2', $this->rightTable->getName(), $condition);

        return $condition;
    }
}
