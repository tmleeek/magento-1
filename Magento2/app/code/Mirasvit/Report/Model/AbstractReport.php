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


namespace Mirasvit\Report\Model;

use Magento\Framework\DataObject;
use Mirasvit\Report\Model\Select\Query;
use Mirasvit\Report\Model\Select\Table;
use Mirasvit\Report\Model\Select\Column;
use Mirasvit\Report\Model\Select\QueryFactory;
use Mirasvit\Report\Ui\Context as UiContext;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractReport implements ReportInterface
{
    /**
     * @var Config\Map
     */
    protected $map;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var UiContext
     */
    protected $uiContext;

    /**
     * @var Table
     */
    protected $baseTable;

    /**
     * @var Column[]
     */
    protected $fastFilters = [];

    /**
     * Required columns. We select these columns in any case.
     *
     * @var Column[]
     */
    protected $requiredColumns = [];

    /**
     * Visible columns by default
     *
     * @var Column[]
     */
    protected $defaultColumns = [];

    /**
     * @var Column[]
     */
    protected $availableColumns = [];

    /**
     * @var Column[]
     */
    protected $defaultDimensions = [];

    /**
     * @var Column[]
     */
    protected $availableDimensions = [];

    /**
     * @var array
     */
    protected $defaultFilters = [];

    /**
     * @var array
     */
    protected $gridConfig = [];

    /**
     * @var array
     */
    protected $chartConfig = [];

    /**
     * @var \Magento\Backend\Model\Url
     */
    protected $urlManager;

    /**
     * @var bool
     */
    protected $isInitialized;

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        $this->context = $context;
        $this->map = $context->getMap();
        $this->request = $context->getRequest();
        $this->urlManager = $context->getUrlManager();
    }

    /**
     * @return $this
     */
    public abstract function initialize();

    /**
     * @return Config\Map
     */
    public function getMap()
    {
        return $this->map;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        $code = str_replace('Mirasvit\Reports\Reports\\', '', get_class($this));

        return str_replace('\\', '_', $code);
    }

    /**
     * @return bool
     */
    public function isInitialized()
    {
        return $this->isInitialized;
    }

    /**
     * @param \Mirasvit\Report\Ui\Context $context
     * @return $this
     */
    public function setUiContext($context)
    {
        $this->uiContext = $context;

        return $this;
    }

    /**
     * @return UiContext
     */
    public function getUiContext()
    {
        return $this->uiContext;
    }

    /**
     * @return void
     */
    public function reset()
    {
        $this->context->initQuery();
        $this->getQuery();
    }

    /**
     * @param string|Table $table
     * @return $this
     */
    public function setBaseTable($table)
    {
        if (!$table instanceof Table) {
            $table = $this->map->getTable($table);
        }

        $this->baseTable = $table;

        return $this;
    }

    /** Fast Filters */

    /**
     * @param array $columns
     * @return $this
     */
    public function addFastFilters($columns)
    {
        $this->fastFilters = array_merge_recursive(
            $this->fastFilters,
            $this->toColumns($columns)
        );

        return $this;
    }

    /**
     * @param array $columns
     * @return $this
     */
    public function setFastFilters($columns)
    {
        $this->fastFilters = $this->toColumns($columns);

        return $this;
    }

    /**
     * @return Column[]
     */
    public function getFastFilters()
    {
        return $this->fastFilters;
    }


    /** Default columns */

    /**
     * @param array $columns
     * @return $this
     */
    public function addDefaultColumns($columns)
    {
        $this->defaultColumns = array_merge_recursive(
            $this->defaultColumns,
            $this->toColumns($columns)
        );

        return $this;
    }

    /**
     * @param array $columns
     * @return $this
     */
    public function setDefaultColumns($columns)
    {
        $this->defaultColumns = $this->toColumns($columns);

        return $this;
    }

    /**
     * @return Column[]
     */
    public function getDefaultColumns()
    {
        return $this->defaultColumns;
    }

    /** Available columns */

    /**
     * @param array $columns
     * @return $this
     */
    public function addAvailableColumns($columns)
    {
        $this->availableColumns = array_merge_recursive(
            $this->availableColumns,
            $this->toColumns($columns)
        );

        return $this;
    }

    /**
     * @param array $columns
     * @return $this
     */
    public function setAvailableColumns($columns)
    {
        $this->availableColumns = $this->toColumns($columns);

        return $this;
    }

    /**
     * @return Column[]
     */
    public function getAvailableColumns()
    {
        return $this->availableColumns;
    }

    /** Default dimensions */

    /**
     * @param array $columns
     * @return $this
     */
    public function addDefaultDimensions($columns)
    {
        $this->defaultDimensions = array_merge_recursive(
            $this->defaultDimensions,
            $this->toColumns($columns)
        );

        return $this;
    }

    /**
     * @param array $columns
     * @return $this
     */
    public function setDefaultDimensions($columns)
    {
        $this->defaultDimensions = $this->toColumns($columns);

        return $this;
    }

    /**
     * @return Column
     */
    public function getDefaultDimension()
    {
        return reset($this->defaultDimensions);
    }

    /** Available dimensions */

    /**
     * @param array $columns
     * @return $this
     */
    public function addAvailableDimensions($columns)
    {
        $this->availableDimensions = array_merge_recursive(
            $this->availableDimensions,
            $this->toColumns($columns)
        );

        return $this;
    }

    /**
     * @param array $columns
     * @return $this
     */
    public function setAvailableDimensions($columns)
    {
        $this->availableDimensions = $this->toColumns($columns);

        return $this;
    }

    /**
     * @return Column[]
     */
    public function getAvailableDimensions()
    {
        return $this->availableDimensions;
    }

    /** Default filters */

    /**
     * @param array $filters
     * @return $this
     */
    public function setDefaultFilters($filters)
    {
        $this->defaultFilters = $filters;

        return $this;
    }

    /** Required columns */

    /**
     * @param array $columns
     * @return $this
     */
    public function addRequiredColumns($columns)
    {
        $this->requiredColumns = array_merge_recursive(
            $this->requiredColumns,
            $this->toColumns($columns)
        );

        return $this;
    }

    /**
     * @param array $columns
     * @return $this
     */
    public function setRequiredColumns($columns)
    {
        $this->requiredColumns = $this->toColumns($columns);

        return $this;
    }

    /**
     * @return Column[]
     */
    public function getRequiredColumns()
    {
        return $this->requiredColumns;
    }


    /**
     * @param array $config
     * @return $this
     */
    public function setGridConfig($config)
    {
        $this->gridConfig = $config;

        return $this;
    }

    /**
     * @param string $key
     * @return DataObject|string
     */
    public function getGridConfig($key = null)
    {
        $conf = new DataObject($this->gridConfig);

        if ($key) {
            return $conf->getData($key);
        }

        return $conf;
    }

    /**
     * @param array $config
     * @return $this
     */
    public function setChartConfig($config)
    {
        $this->chartConfig = $config;

        return $this;
    }

    /**
     * @return DataObject
     */
    public function getChartConfig()
    {
        return new DataObject($this->chartConfig);
    }
    //
    /**
     * {@inheritdoc}
     */
    public function getActions(array $item)
    {
        return [];
    }

    /**
     * @param string[]|Column[] $columns
     * @return Column[]
     * @throws \Exception
     */
    protected function toColumns($columns)
    {
        $result = [];

        if (!is_array($columns)) {
            $columns = [$columns];
        }

        foreach ($columns as $column) {
            if (!$column instanceof Column) {
                $column = $this->map->getColumn($column);
            }
            if ($column) {
                $result[] = $column;
            } else {
                throw new \Exception('Undefined column "%1"', $column);
            }
        }

        return $result;
    }

    /**
     * @return Query
     */
    public function getQuery()
    {
        $this->context->getQuery()->setBaseTable($this->baseTable);

        return $this->context->getQuery();
    }

    /**
     * @return \Mirasvit\Report\Model\Select\Collection
     */
    public function getCollection()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $collection = $om->create('Mirasvit\Report\Model\Select\Collection');
        $collection->setQuery($this->getQuery());

        return $collection;
    }

    /**
     * @return bool
     */
    public function hasActions()
    {
        $reflector = new \ReflectionMethod($this, 'getActions');
        $isProto = ($reflector->getDeclaringClass()->getName() !== get_class($this));

        return !$isProto;
    }

    /**
     * @return $this
     */
    public function initQuery()
    {
        foreach ($this->defaultFilters as $filter) {
            $column = $this->map->getColumn($filter[0]);

            if (!in_array($column, $this->context->getQuery()->getFilteredColumns())) {
                $this->context->getQuery()->addColumnToFilter(
                    $column,
                    $filter[1]
                );
            }
        }

        return $this;
    }

    /**
     * @param Column $column
     * @param string $value
     * @param array  $row
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function prepareValue($column, $value, $row)
    {
        return $value;
    }

    /**
     * @param string $report
     * @param array  $filters
     * @return string
     */
    public function getReportUrl($report, $filters = [])
    {
        return $this->urlManager->getUrl(
            'reports/report/view',
            [
                'report' => $report,
                '_query' => [
                    'filters' => $filters,
                ],
            ]
        );
    }
}
