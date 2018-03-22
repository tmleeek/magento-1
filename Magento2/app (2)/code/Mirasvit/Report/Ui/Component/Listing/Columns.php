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


namespace Mirasvit\Report\Ui\Component\Listing;

use Magento\Framework\Profiler;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns as UiColumns;
use Magento\Framework\Registry;
use Mirasvit\Report\Ui\Component\ColumnFactory;
use Mirasvit\Report\Ui\Component\Listing\Column\ActionsFactory as ColumnActionsFactory;
use Mirasvit\Report\Ui\Context;

class Columns extends UiColumns
{
    /**
     * @var Context
     */
    protected $uiContext;

    /**
     * @var ColumnFactory
     */
    protected $columnFactory;

    /**
     * @var ColumnActionsFactory
     */
    protected $columnActionsFactory;

    /**
     * @var array
     */
    protected $filterMap = [
        'default'     => 'text',
        'select'      => 'select',
        'boolean'     => 'select',
        'multiselect' => 'select',
        'number'      => 'textRange',
        'price'       => 'textRange',
        'date'        => 'dateRange',
        'country'     => 'select',
    ];

    /**
     * @param ContextInterface     $context
     * @param Context              $uiContext
     * @param ColumnActionsFactory $columnActionsFactory
     * @param ColumnFactory        $columnFactory
     * @param array                $components
     * @param array                $data
     */
    public function __construct(
        ContextInterface $context,
        Context $uiContext,
        ColumnActionsFactory $columnActionsFactory,
        ColumnFactory $columnFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);

        $this->uiContext = $uiContext;
        $this->columnFactory = $columnFactory;
        $this->columnActionsFactory = $columnActionsFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $this->components = [];

        parent::prepare();

        $columns = [];

        $gridConfig = $this->uiContext->getReport()->getGridConfig();

        $allColumns = array_merge(
            $this->uiContext->getReport()->getDefaultColumns(),
            $this->uiContext->getReport()->getAvailableColumns(),
            $this->uiContext->getReport()->getAvailableDimensions()
        );

        /** @var \Mirasvit\Report\Model\Select\Column $column */
        foreach ($allColumns as $column) {
            $columns[$column->getName()] = [
                'label'     => $column->getLabel(),
                'type'      => $column->getDataType(),
                'options'   => $column->getOptions(),
                'visible'   => in_array($column, $this->uiContext->getReport()->getDefaultColumns()),
                'dimension' => in_array($column, $this->uiContext->getReport()->getAvailableDimensions()),
                'color'     => $column->getColor(),
                'filter'    => $this->getFilterType($column->getDataType()),
                'sorting'   => ($column->getName() == $gridConfig->getData('sort_by'))
                    ? $gridConfig->getData('sort_direction')
                    : false,
                'percent'   => $column->getName() == 'sales_order|quantity' ? true : false
            ];
        }

        $columnSortOrder = 1000;

        foreach ($columns as $attribute => $data) {
            if (!isset($this->components[$attribute])) {
                $columnSortOrder -= 10;
                $config = $data;
                $config['sortOrder'] = $columnSortOrder;

                if (isset($_GET['columns']) && in_array($attribute, $_GET['columns'])) {
                    $config['add_field'] = true;
                }

                $column = $this->columnFactory->create($attribute, $this->getContext(), $config);

                $column->prepare();

                $this->addComponent($attribute, $column);
            }
        }

        $this->addActionsColumn();
    }

    /**
     * Add actions column
     *
     * @return void
     */
    protected function addActionsColumn()
    {
        if ($this->uiContext->getReport()->hasActions()) {
            $arguments = [
                'data'    => [
                    'js_config' => [
                        'component' => 'Magento_Ui/js/grid/columns/actions',
                    ],
                    'config'    => [
                        'label'     => 'Actions',
                        'dataType'  => 'actions',
                        'sortOrder' => 0,
                        'color'     => '',
                    ],
                    'name'      => 'actions'
                ],
                'context' => $this->context,
            ];


            $actions = $this->columnActionsFactory->create($arguments);
            $actions->prepare();

            $this->addComponent('actions', $actions);
        }
    }

    /**
     * Retrieve filter type by $frontendInput
     *
     * @param string $type
     * @return string
     */
    protected function getFilterType($type)
    {
        // filter by default always should be visible only in main toolbar
        if ($type === 'date') {
            return false;
        }

        return isset($this->filterMap[$type]) ? $this->filterMap[$type] : $this->filterMap['default'];
    }
}
