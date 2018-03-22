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



namespace Mirasvit\Helpdesk\Block\Adminhtml\Status;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Mirasvit\Helpdesk\Model\StatusFactory
     */
    protected $statusFactory;

    /**
     * @var \Mirasvit\Helpdesk\Model\Config\Source\Color
     */
    protected $configSourceColor;

    /**
     * @var \Magento\Backend\Block\Widget\Context
     */
    protected $context;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;

    /**
     * @param \Mirasvit\Helpdesk\Model\StatusFactory       $statusFactory
     * @param \Mirasvit\Helpdesk\Model\Config\Source\Color $configSourceColor
     * @param \Magento\Backend\Block\Widget\Context        $context
     * @param \Magento\Backend\Helper\Data                 $backendHelper
     * @param array                                        $data
     */
    public function __construct(
        \Mirasvit\Helpdesk\Model\StatusFactory $statusFactory,
        \Mirasvit\Helpdesk\Model\Config\Source\Color $configSourceColor,
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->statusFactory = $statusFactory;
        $this->configSourceColor = $configSourceColor;
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
        $this->setDefaultSort('status_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->statusFactory->create()
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
        $this->addColumn('status_id', [
            'header'       => __('ID'),
            'index'        => 'status_id',
            'filter_index' => 'main_table.status_id',
        ]);
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
        $this->addColumn('color', [
            'header'       => __('Color'),
            'index'        => 'color',
            'filter_index' => 'main_table.color',
            'type'         => 'options',
            'options'      => $this->configSourceColor->toArray(),
            'renderer'     => '\Mirasvit\Helpdesk\Block\Adminhtml\Ticket\Grid\Renderer\Highlight',
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
     * @param \Mirasvit\Helpdesk\Model\Status           $item
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @param bool                                      $isExport
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function _renderCellName($renderedValue, $item, $column, $isExport)
    {
        return $item->getName();
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('status_id');
        $this->getMassactionBlock()->setFormFieldName('status_id');
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
