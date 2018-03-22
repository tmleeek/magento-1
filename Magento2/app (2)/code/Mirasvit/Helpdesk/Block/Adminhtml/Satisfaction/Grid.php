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



namespace Mirasvit\Helpdesk\Block\Adminhtml\Satisfaction;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Mirasvit\Helpdesk\Model\SatisfactionFactory
     */
    protected $satisfactionFactory;

    /**
     * @var \Mirasvit\Helpdesk\Model\Config\Source\Rate
     */
    protected $configSourceRate;

    /**
     * @var \Mirasvit\Helpdesk\Helper\Html
     */
    protected $helpdeskHtml;

    /**
     * @var \Magento\Backend\Block\Widget\Context
     */
    protected $context;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;

    /**
     * @param \Mirasvit\Helpdesk\Model\SatisfactionFactory $satisfactionFactory
     * @param \Mirasvit\Helpdesk\Model\Config\Source\Rate  $configSourceRate
     * @param \Mirasvit\Helpdesk\Helper\Html               $helpdeskHtml
     * @param \Magento\Backend\Block\Widget\Context        $context
     * @param \Magento\Backend\Helper\Data                 $backendHelper
     * @param array                                        $data
     */
    public function __construct(
        \Mirasvit\Helpdesk\Model\SatisfactionFactory $satisfactionFactory,
        \Mirasvit\Helpdesk\Model\Config\Source\Rate $configSourceRate,
        \Mirasvit\Helpdesk\Helper\Html $helpdeskHtml,
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->satisfactionFactory = $satisfactionFactory;
        $this->configSourceRate = $configSourceRate;
        $this->helpdeskHtml = $helpdeskHtml;
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
        $this->setDefaultSort('satisfaction_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->satisfactionFactory->create()
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
        $this->addColumn('satisfaction_id', [
            'header' => __('ID'),
            'index' => 'satisfaction_id',
            'filter_index' => 'main_table.satisfaction_id',
            ]);
        $this->addColumn('user_id', [
            'header' => __('User'),
            'index' => 'user_id',
            'filter_index' => 'main_table.user_id',
            'type' => 'options',
            'options' => $this->helpdeskHtml->getAdminUserOptionArray(),
            ]);
        $this->addColumn('customer_name', [
            'header' => __('Customer'),
            'index' => 'customer_name',
            'filter_index' => 'main_table.customer_name',
            ]);
        $this->addColumn('rate', [
            'header' => __('Rate'),
            'index' => 'rate',
            'filter_index' => 'main_table.rate',
            'type' => 'options',
            'options' => $this->configSourceRate->toArray(),
            ]);
        $this->addColumn('comment', [
            'header' => __('Comment'),
            'index' => 'comment',
            'filter_index' => 'main_table.comment',
            ]);
        $this->addColumn('created_at', [
            'header' => __('Created At'),
            'index' => 'created_at',
            'filter_index' => 'main_table.created_at',
            'type' => 'datetime',
            ]);

        $this->addColumn(
            'action',
            [
                // 'header'    =>  __('View Rated Message'),
                'width' => '100',
                'type' => 'action',
                'getter' => 'getTicketId',
                'actions' => [
                    [
                        'caption' => __('View Ticket'),
                        'url' => ['base' => 'helpdesk/ticket/edit'],
                        'field' => 'id',
                        'target' => '_blank',
                    ],
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'is_system' => true,
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('satisfaction_id');
        $this->getMassactionBlock()->setFormFieldName('satisfaction_id');
        $this->getMassactionBlock()->addItem('delete', [
            'label' => __('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => __('Are you sure?'),
        ]);

        return $this;
    }

    /************************/
}
