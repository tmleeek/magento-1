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



namespace Mirasvit\Helpdesk\Block\Adminhtml\Spam;

use Mirasvit\Helpdesk\Model\Config as Config;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Mirasvit\Helpdesk\Model\TicketFactory
     */
    protected $ticketFactory;

    /**
     * @var \Mirasvit\Helpdesk\Model\ResourceModel\Pattern\CollectionFactory
     */
    protected $patternCollectionFactory;

    /**
     * @var \Mirasvit\Helpdesk\Helper\Permission
     */
    protected $helpdeskPermission;

    /**
     * @var \Magento\Backend\Block\Widget\Context
     */
    protected $context;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;

    /**
     * @param \Mirasvit\Helpdesk\Model\TicketFactory                           $ticketFactory
     * @param \Mirasvit\Helpdesk\Model\ResourceModel\Pattern\CollectionFactory $patternCollectionFactory
     * @param \Mirasvit\Helpdesk\Helper\Permission                             $helpdeskPermission
     * @param \Magento\Backend\Block\Widget\Context                            $context
     * @param \Magento\Backend\Helper\Data                                     $backendHelper
     * @param array                                                            $data
     */
    public function __construct(
        \Mirasvit\Helpdesk\Model\TicketFactory $ticketFactory,
        \Mirasvit\Helpdesk\Model\ResourceModel\Pattern\CollectionFactory $patternCollectionFactory,
        \Mirasvit\Helpdesk\Helper\Permission $helpdeskPermission,
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->ticketFactory = $ticketFactory;
        $this->patternCollectionFactory = $patternCollectionFactory;
        $this->helpdeskPermission = $helpdeskPermission;
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
        $this->setDefaultSort('ticket_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->ticketFactory->create()
            ->getCollection()
            ->joinEmails()
            ->addFieldToFilter('folder', Config::FOLDER_SPAM);
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
        $this->addColumn('code', [
            'header' => __('Code'),
            'index' => 'code',
            'filter_index' => 'main_table.code',
            ]);
        $this->addColumn('name', [
            'header' => __('Subject'),
            'index' => 'name',
            'filter_index' => 'main_table.name',
            ]);
        $this->addColumn('customer_name', [
            'header' => __('Customer Name'),
            'index' => 'customer_name',
            'filter_index' => 'main_table.customer_name',
            ]);
        $this->addColumn('customer_email', [
            'header' => __('Customer Email'),
            'index' => 'customer_email',
            'filter_index' => 'main_table.customer_email',
            ]);
        $this->addColumn('created_at', [
            'header' => __('Created At'),
            'index' => 'created_at',
            'filter_index' => 'main_table.created_at',
            'type' => 'datetime',
            ]);
        $options = $this->patternCollectionFactory->create()->getOptionArray();
        $options[-1] = __('Manually');
        $this->addColumn('pattern_id', [
            'header' => __('Rejected by'),
            'index' => 'pattern_id',
            'filter_index' => 'main_table.pattern_id',
            'type' => 'options',
            'options' => $options,
            ]);

        return parent::_prepareColumns();
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('spam_id');
        $this->getMassactionBlock()->setFormFieldName('spam_id');
        $this->getMassactionBlock()->addItem('approve', [
            'label' => __('Approve'),
            'url' => $this->getUrl('*/*/massApprove'),
            'confirm' => __('Are you sure?'),
        ]);
        if ($this->helpdeskPermission->isTicketRemoveAllowed()) {
            $this->getMassactionBlock()->addItem('delete', [
                'label' => __('Delete'),
                'url' => $this->getUrl('*/*/massDelete'),
                'confirm' => __('Are you sure?'),
            ]);
        }

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
