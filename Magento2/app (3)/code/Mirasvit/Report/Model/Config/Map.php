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


namespace Mirasvit\Report\Model\Config;

use Magento\Framework\ObjectManagerInterface;
use Mirasvit\Report\Model\Select\TableFactory;
use Mirasvit\Report\Model\Select\Eav\TableFactory as EavTableFactory;
use Mirasvit\Report\Model\Select\RelationFactory;
use Mirasvit\Report\Model\Select\ColumnFactory;
use Mirasvit\Report\Model\Config\Map\Converter;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Map
{
    /**
     * @var \Mirasvit\Report\Model\Select\Table[]
     */
    protected $tablePool;

    /**
     * @var \Mirasvit\Report\Model\Select\Relation[]
     */
    protected $relationPool;

    /**
     * @var Map\Data
     */
    protected $data;

    /**
     * @var TableFactory
     */
    protected $tableFactory;

    /**
     * @var EavTableFactory
     */
    protected $eavTableFactory;

    /**
     * @var RelationFactory
     */
    protected $relationFactory;

    /**
     * @var ColumnFactory
     */
    protected $columnFactory;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Map constructor.
     *
     * @param Map\Data               $data
     * @param TableFactory           $tableFactory
     * @param EavTableFactory        $eavTableEavFactory
     * @param RelationFactory        $relationFactory
     * @param ColumnFactory          $columnFactory
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        Map\Data $data,
        TableFactory $tableFactory,
        EavTableFactory $eavTableEavFactory,
        RelationFactory $relationFactory,
        ColumnFactory $columnFactory,
        ObjectManagerInterface $objectManager
    ) {
        $this->data = $data;
        $this->tableFactory = $tableFactory;
        $this->eavTableFactory = $eavTableEavFactory;
        $this->relationFactory = $relationFactory;
        $this->columnFactory = $columnFactory;
        $this->objectManager = $objectManager;

        $this->prepareData();
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function prepareData()
    {
        $config = $this->data->get('config');

        $config['table'] = isset($config['table']) ? $config['table'] : [];
        $config['eavTable'] = isset($config['eavTable']) ? $config['eavTable'] : [];
        $config['relation'] = isset($config['relation']) ? $config['relation'] : [];

        foreach ($config['table'] as $data) {
            $this->initTable($data);
        }

        foreach ($config['eavTable'] as $data) {
            $this->initEavTable($data);
        }

        foreach ($config['relation'] as $data) {
            $this->initRelation($data);
        }

        return $this;
    }

    /**
     * @param string $name
     * @return \Mirasvit\Report\Model\Select\Table
     * @throws \Exception
     */
    public function getTable($name)
    {
        if (!isset($this->tablePool[$name])) {
            throw new \Exception(__("Table '%1' is not defined.", $name));
        }

        return $this->tablePool[$name];
    }

    /**
     * @return \Mirasvit\Report\Model\Select\Table[]
     */
    public function getTables()
    {
        return $this->tablePool;
    }

    /**
     * @param string $name
     * @return \Mirasvit\Report\Model\Select\Column
     * @throws \Exception
     */
    public function getColumn($name)
    {
        if (count(explode('|', $name)) != 2) {
            throw new \Exception(__("Malformed column name '%1'.", $name));
        }

        $table = explode('|', $name)[0];

        return $this->getTable($table)->getColumn($name);
    }

    /**
     * @return \Mirasvit\Report\Model\Select\Relation[]
     */
    public function getRelations()
    {
        return $this->relationPool;
    }

    /**
     * @param array $data
     * @return void
     */
    protected function initTable($data)
    {
        $table = $this->tableFactory->create($data[Converter::DATA_ATTRIBUTES_KEY]);

        $this->tablePool[$table->getName()] = $table;

        if (isset($data['columns'])) {
            foreach ($data['columns']['column'] as $data) {
                $data[Converter::DATA_ATTRIBUTES_KEY]['table'] = $table;
                $this->initColumn($data);
            }
        }
    }

    /**
     * @param array $data
     * @return void
     */
    protected function initEavTable($data)
    {
        $table = $this->eavTableFactory->create($data[Converter::DATA_ATTRIBUTES_KEY]);
        $this->tablePool[$table->getName()] = $table;

        if (isset($data['columns'])) {
            foreach ($data['columns']['column'] as $data) {
                $data[Converter::DATA_ATTRIBUTES_KEY]['table'] = $table;
                $this->initColumn($data);
            }
        }
    }

    /**
     * @param array $data
     * @return void
     */
    protected function initRelation($data)
    {
        $data = $data[Converter::DATA_ARGUMENTS_KEY];

        $data['leftTable'] = $this->getTable($data['leftTable']);
        $data['rightTable'] = $this->getTable($data['rightTable']);

        $this->relationPool[] = $this->relationFactory->create($data);
    }

    /**
     * @param array $data
     * @return void
     */
    protected function initColumn($data)
    {
        $data = $data[Converter::DATA_ATTRIBUTES_KEY];
        $data['fields'] = explode(',', $data['fields']);

        $name = $data['name'];

        $objectData = [
            'name' => $name,
            'data' => $data,
        ];

        if (isset($data['aggregations'])) {
            $data['aggregations'] = explode(',', $data['aggregations']);
            foreach ($data['aggregations'] as $type) {
                $class = '\Mirasvit\Report\Model\Select\Column\\' . ucfirst($type);
                $this->objectManager->create(
                    $class,
                    $objectData
                );
            }
        } else {
            if (isset($data['class'])) {
                $class = $data['class'];

                $this->objectManager->create(
                    $class,
                    $objectData
                );
            } else {
                $this->columnFactory->create(
                    $objectData
                );
            }
        }
    }

    /**
     * @return $this
     */
    public function reset()
    {
        foreach ($this->tablePool as $table) {
            $table->setJoined(false);
        }

        return $this;
    }
}
