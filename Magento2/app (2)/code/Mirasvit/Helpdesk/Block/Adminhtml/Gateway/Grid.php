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



namespace Mirasvit\Helpdesk\Block\Adminhtml\Gateway;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Mirasvit\Helpdesk\Model\GatewayFactory
     */
    protected $gatewayFactory;

    /**
     * @var \Mirasvit\Helpdesk\Helper\StringUtil
     */
    protected $helpdeskString;

    /**
     * @var \Magento\Backend\Block\Widget\Context
     */
    protected $context;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;

    /**
     * @param \Mirasvit\Helpdesk\Model\GatewayFactory $gatewayFactory
     * @param \Mirasvit\Helpdesk\Helper\StringUtil    $helpdeskString
     * @param \Magento\Backend\Block\Widget\Context   $context
     * @param \Magento\Backend\Helper\Data            $backendHelper
     * @param array                                   $data
     */
    public function __construct(
        \Mirasvit\Helpdesk\Model\GatewayFactory $gatewayFactory,
        \Mirasvit\Helpdesk\Helper\StringUtil $helpdeskString,
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->gatewayFactory = $gatewayFactory;
        $this->helpdeskString = $helpdeskString;
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
        $this->setDefaultSort('gateway_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->gatewayFactory->create()
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
        $this->addColumn('gateway_id', [
            'header' => __('ID'),
            'index'  => 'gateway_id',
        ]);
        $this->addColumn('name', [
            'header' => __('Title'),
            'index'  => 'name',
        ]);
        $this->addColumn('email', [
            'header' => __('Email'),
            'index'  => 'email',
        ]);
        $this->addColumn('is_active', [
            'header'  => __('Active'),
            'index'   => 'is_active',
            'type'    => 'options',
            'options' => [
                0 => __('No'),
                1 => __('Yes'),
            ],
        ]);
        $this->addColumn('fetched_at1', [
            'header'         => __('Last Fetched At'),
            'index'          => 'fetched_at',
            'frame_callback' => [$this, '_lastActivityFormat'],
        ]);
        $this->addColumn('last_fetch_result', [
            'header' => __('Last Fetch Result'),
            'index'  => 'last_fetch_result',
        ]);

        if (!$this->context->getStoreManager()->isSingleStoreMode()) {
            $this->addColumn('store_id', [
                'header'     => __('Store'),
                'index'      => 'store_id',
                'type'       => 'store',
                'store_all'  => true,
                'store_view' => true,
                'sortable'   => false
            ]);
        }

        return parent::_prepareColumns();
    }

    /**
     * @param string                                    $renderedValue
     * @param \Mirasvit\Helpdesk\Model\Gateway          $row
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @param bool                                      $isExport
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function _lastActivityFormat($renderedValue, $row, $column, $isExport)
    {
        return $this->helpdeskString->nicetime(strtotime($renderedValue));
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('gateway_id');
        $this->getMassactionBlock()->setFormFieldName('gateway_id');
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
