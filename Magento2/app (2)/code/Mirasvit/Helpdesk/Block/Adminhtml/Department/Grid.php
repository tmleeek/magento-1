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
 * @package   mirasvit/module-helpdesk
 * @version   1.1.25
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Helpdesk\Block\Adminhtml\Department;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Mirasvit\Helpdesk\Model\DepartmentFactory
     */
    protected $departmentFactory;

    /**
     * @var \Magento\Backend\Block\Widget\Context
     */
    protected $context;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;

    /**
     * @param \Mirasvit\Helpdesk\Model\DepartmentFactory $departmentFactory
     * @param \Magento\Backend\Block\Widget\Context      $context
     * @param \Magento\Backend\Helper\Data               $backendHelper
     * @param array                                      $data
     */
    public function __construct(
        \Mirasvit\Helpdesk\Model\DepartmentFactory $departmentFactory,
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->departmentFactory = $departmentFactory;
        $this->context = $context;
        $this->backendHelper = $backendHelper;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('grid');
        $this->setDefaultSort('department_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->departmentFactory->create()
            ->getCollection()
            ->addStoreColumn();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     *
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('name', [
            'header'         => __('Title'),
            'index'          => 'name',
            'frame_callback' => [$this, '_renderCellName'],
            'filter_index'   => 'main_table.name',
        ]);
        $this->addColumn('sort_order', [
            'header'       => __('Sort Order'),
            'index'        => 'sort_order',
            'filter_index' => 'main_table.sort_order',
        ]);
        $this->addColumn('is_active', [
            'header'       => __('Active'),
            'index'        => 'is_active',
            'filter_index' => 'main_table.is_active',
            'type'         => 'options',
            'options'      => [
                0 => __('No'),
                1 => __('Yes'),
            ],
        ]);

        if (!$this->context->getStoreManager()->isSingleStoreMode()) {
            $this->addColumn('store_ids', [
                'header'     => __('Store'),
                'index'      => 'store_ids',
                'type'       => 'store',
                'store_all'  => true,
                'store_view' => true,
                'sortable'   => false,
                'filter'     => false,
            ]);
        }

        return parent::_prepareColumns();
    }

    /**
     * @param string                                    $renderedValue
     * @param \Mirasvit\Helpdesk\Model\Department       $item
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @param bool                                      $isExport
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function _renderCellName(
        $renderedValue,
        $item,
        $column,
        $isExport
    ) {
        return $item->getName();
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('department_id');
        $this->getMassactionBlock()->setFormFieldName('department_id');
        $statuses = [
            ['label' => '', 'value' => ''],
            ['label' => __('Disabled'), 'value' => 0],
            ['label' => __('Enabled'), 'value' => 1],
        ];
        $this->getMassactionBlock()->addItem('is_active', [
            'label'      => __('Change status'),
            'url'        => $this->getUrl('*/*/massChange', ['_current' => true]),
            'additional' => [
                'visibility' => [
                    'name'   => 'is_active',
                    'type'   => 'select',
                    'class'  => 'required-entry',
                    'label'  => __('Status'),
                    'values' => $statuses,
                ],
            ],
        ]);
        $this->getMassactionBlock()->addItem('delete', [
            'label'   => __('Delete'),
            'url'     => $this->getUrl('*/*/massDelete'),
            'confirm' => __('Are you sure?'),
        ]);

        return $this;
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
    }

    /************************/
}
