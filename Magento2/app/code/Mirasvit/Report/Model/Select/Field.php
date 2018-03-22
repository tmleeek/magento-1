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

class Field
{
    /**
     * @var string
     */
    protected $name;


    /**
     * @var Table
     */
    protected $table;

    /**
     * Field constructor.
     *
     * @param Table $table
     * @param string      $name
     */
    public function __construct(
        Table $table,
        $name
    ) {
        $this->table = $table;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Table
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->table->getName() . '.' . $this->name;
    }

    /**
     * @param Query $query
     * @return void
     */
    public function join(Query $query)
    {
        $query->joinTable($this->table);
    }
}
