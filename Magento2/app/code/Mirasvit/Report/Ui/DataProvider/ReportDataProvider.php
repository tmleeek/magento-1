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


namespace Mirasvit\Report\Ui\DataProvider;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Mirasvit\Report\Ui\Context;

/**
 * Class ReportDataProvider
 */
class ReportDataProvider extends AbstractDataProvider
{
    /**
     * @var \Mirasvit\Report\Model\Select\Query
     */
    protected $collection;

    /**
     * @var ContextInterface
     */
    protected $context;

    /**
     * @var Context
     */
    protected $uiContext;

    /**
     * @var \Mirasvit\Report\Model\Config\Map
     */
    protected $map;

    /**
     * @param Context $uiContext
     * @param \Mirasvit\Report\Model\Config\Map $map
     * @param array $name
     * @param $primaryFieldName
     * @param $requestFieldName
     * @param ContextInterface $context
     * @param array $meta
     * @param array $data
     * @SuppressWarnings(PHPMD)
     */
    public function __construct(
        Context $uiContext,
        \Mirasvit\Report\Model\Config\Map $map,
        $name,
        $primaryFieldName,
        $requestFieldName,
        ContextInterface $context,
        array $meta = [],
        array $data = []
    ) {
        $this->context = $context;
        $this->map = $map;
        $this->uiContext = $uiContext;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $this->collection = $this->uiContext->getReport()->getQuery();
    }

    /**
     * @return void
     */
    public function reset()
    {
        $this->uiContext->getReport()->reset();
        $this->uiContext->getReport()->initQuery();
        $this->collection = $this->uiContext->getReport()->getQuery();
    }

    /**
     * Get data
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * @return array
     */
    public function getData()
    {
        $startTime = microtime(true);

        $items = $this->getSearchResult()->load();

        foreach ($items as $key => $item) {
            foreach ($item as $code => $value) {
                if ($code != '1') {
                    $column = $this->map->getColumn($code);

                    if ($column) {
                        $items[$key][$code . '_orig'] = $items[$key][$code];
                        $items[$key][$code] = $column->prepareValue($value);
                        $items[$key][$code] = $this->uiContext->getReport()->prepareValue(
                            $column,
                            $items[$key][$code],
                            $items[$key]
                        );
                    }
                }
            }
        }

        $totals = $this->collection->getTotals();

        if (is_array($totals)) {
            foreach ($totals as $code => $value) {
                if ($code != '1') {
                    $column = $this->map->getColumn($code);

                    if (in_array($column->getDataType(), ['number', 'price'])) {
                        if ($column) {
                            $totals[$code . '_orig'] = $totals[$code];
                            $totals[$code] = $column->prepareValue($value);
                            $totals[$code] = $this->uiContext->getReport()
                                ->prepareValue($column, $totals[$code], $totals);
                        }
                    } else {
                        $totals[$code] = '';
                    }
                }
            }
        }

        $result = [
            'totalRecords'    => $this->getCollection()->getSize(),
            'items'           => array_values($items),
            'totals'          => [$totals],
            'dimensionColumn' => $this->uiContext->getActiveDimension(),
            'select'          => $this->getCollection()->getSelect()->__toString(),
            'time'            => round(microtime(true) - $startTime, 4)
        ];

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchCriteria()
    {
        return $this->getSearchResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchResult()
    {
        foreach ($this->uiContext->getReport()->getRequiredColumns() as $column) {
            $this->collection->addColumnToSelect($column);
        }

        $this->collection->addColumnToSelect($this->map->getColumn($this->uiContext->getActiveDimension()));

        $this->uiContext->getReport()->initQuery();

        return $this->collection;
    }

    /**
     * @param string $field
     * @param string $alias
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addField($field, $alias = null)
    {
        $column = $this->map->getColumn($field);

        $this->collection->addColumnToSelect($column);

        return $this;
    }

    /**
     * @param string $field
     * @param string $direction
     * @return $this
     */
    public function addOrder($field, $direction)
    {
        $column = $this->map->getColumn($field);

        $this->collection->addColumnToOrder(
            $column,
            $direction
        );

        return $this;
    }

    /**
     * @param \Magento\Framework\Api\Filter $filter
     * @return $this
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        $column = $this->map->getColumn($filter->getField());

        $this->collection->addColumnToFilter(
            $column,
            [$filter->getConditionType() => $filter->getValue()]
        );

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addDimension($column)
    {
        if (!is_object($column)) {
            $column = $this->map->getColumn($column);
        }

        $this->collection->groupByColumn($column);

        return $this;
    }

    /**
     * @param int $offset
     * @param int $size
     * @return $this
     */
    public function setLimit($offset, $size)
    {
        $this->collection->setLimit($offset, $size);

        return $this;
    }

    /**
     * @return array
     */
    public function getConfigData()
    {
        $this->data['config']['params'] = [
            'report' => $this->uiContext->getReport()->getIdentifier()
        ];

        return isset($this->data['config']) ? $this->data['config'] : [];
    }
}
