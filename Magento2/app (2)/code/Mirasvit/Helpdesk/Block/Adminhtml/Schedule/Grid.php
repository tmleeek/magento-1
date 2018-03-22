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



namespace Mirasvit\Helpdesk\Block\Adminhtml\Schedule;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Mirasvit\Helpdesk\Model\ScheduleFactory
     */
    protected $scheduleFactory;

    /**
     * @var \Magento\Backend\Block\Widget\Context
     */
    protected $context;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;

    /**
     * @param \Mirasvit\Helpdesk\Model\ScheduleFactory $scheduleFactory
     * @param \Magento\Backend\Block\Widget\Context    $context
     * @param \Magento\Backend\Helper\Data             $backendHelper
     * @param array                                    $data
     */
    public function __construct(
        \Mirasvit\Helpdesk\Model\ScheduleFactory $scheduleFactory,
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->scheduleFactory = $scheduleFactory;
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
        $this->setDefaultSort('schedule_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->scheduleFactory->create()
            ->getCollection();
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
            'header' => __('Schedule Name'),
            'index' => 'name',
            'filter_index' => 'main_table.name',
            ]);
        $this->addColumn('working_hours', [
            'header' => __('Working Hours'),
            'index' => 'working_hours',
            'filter' => false,
            'frame_callback' => [$this, 'renderWorkingHours'],
            'header_css_class' => 'schedule-working-hours',
        ]);
        $this->addColumn('is_holiday', [
            'header' => __('Is Holiday'),
            'index' => 'is_holiday',
            'filter_index' => 'main_table.is_holiday',
            'type' => 'options',
            'options' => [
                0 => __('No'),
                1 => __('Yes'),
            ],
        ]);
        $this->addColumn('is_active', [
            'header' => __('Active'),
            'index' => 'is_active',
            'filter_index' => 'main_table.is_active',
            'type' => 'options',
            'options' => [
                0 => __('No'),
                1 => __('Yes'),
            ],
        ]);
        $this->addColumn('active_from', [
            'header'       => __('Active From'),
            'index'        => 'active_from',
            'filter_index' => 'main_table.active_from',
            'type'         => 'datetime',
            ]);
        $this->addColumn('active_to', [
            'header'       => __('Active To'),
            'index'        => 'active_to',
            'filter_index' => 'main_table.active_to',
            'type'         => 'datetime',
        ]);
        $this->addColumn('sort_order', [
            'header'           => __('Sort Order'),
            'index'            => 'sort_order',
            'filter_index'     => 'main_table.sort_order',
        ]);

        return parent::_prepareColumns();
    }

    /**
     * @param string                                    $renderedValue
     * @param \Mirasvit\Helpdesk\Model\Schedule         $item
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @param bool                                      $isExport
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function renderWorkingHours($renderedValue, $item, $column, $isExport)
    {
        $html = '';

        foreach ($item->getWorkingHours() as $day) {
            $html .= '<div>';
            $html .= '<span class="schedule-day-block">' . $day->getWeekdayLocalized() . ': </span>';
            $html .= '<span class="schedule-time-block">' . $day->getWorkingTime() . '</span>';
            $html .= '</div>';
        }
        $html .= '<div class="schedule-timezone-block">' . $item->getTimezoneOffset() . '</div>';

        return $html;
    }

    /**
     * @param string                                    $renderedValue
     * @param \Mirasvit\Helpdesk\Model\Schedule         $item
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @param bool                                      $isExport
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function renderTimezone($renderedValue, $item, $column, $isExport)
    {
        return $item->getTimezoneOffset();
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('schedule_id');
        $this->getMassactionBlock()->setFormFieldName('schedule_id');
        $statuses = [
                ['label' => '', 'value' => ''],
                ['label' => __('Disabled'), 'value' => 0],
                ['label' => __('Enabled'), 'value' => 1],
        ];
        $this->getMassactionBlock()->addItem('is_active', [
             'label' => __('Change status'),
             'url' => $this->getUrl('*/*/massChange', ['_current' => true]),
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
            'label' => __('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
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
