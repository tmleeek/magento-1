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



namespace Mirasvit\Helpdesk\Block\Adminhtml\Permission;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Mirasvit\Helpdesk\Model\PermissionFactory
     */
    protected $permissionFactory;

    /**
     * @var \Mirasvit\Helpdesk\Model\DepartmentFactory
     */
    protected $departmentFactory;

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
     * @param \Mirasvit\Helpdesk\Model\PermissionFactory $permissionFactory
     * @param \Mirasvit\Helpdesk\Model\DepartmentFactory $departmentFactory
     * @param \Mirasvit\Helpdesk\Helper\Html             $helpdeskHtml
     * @param \Magento\Backend\Block\Widget\Context      $context
     * @param \Magento\Backend\Helper\Data               $backendHelper
     * @param array                                      $data
     */
    public function __construct(
        \Mirasvit\Helpdesk\Model\PermissionFactory $permissionFactory,
        \Mirasvit\Helpdesk\Model\DepartmentFactory $departmentFactory,
        \Mirasvit\Helpdesk\Helper\Html $helpdeskHtml,
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->permissionFactory = $permissionFactory;
        $this->departmentFactory = $departmentFactory;
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
        $this->setDefaultSort('permission_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->permissionFactory->create()
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
        $this->addColumn('role_id', [
            'header' => __('Role'),
            'index' => 'role_id',
            'filter_index' => 'main_table.role_id',
            'type' => 'options',
            'options' => $this->helpdeskHtml->getAdminRoleOptionArray(),
            'frame_callback' => [$this, '_roleFormat'],
            ]);
        $this->addColumn('departments', [
            'header' => __('Has Access to Tickets of Departments'),
            //          'align'     => 'right',
            //          'width'     => '50px',
            'index' => 'departments',
            'type' => 'text',
            'frame_callback' => [$this, '_departmentsFormat'],
            ]);
        $this->addColumn('is_ticket_remove_allowed', [
            'header' => __('Can Remove Tickets'),
            'index' => 'is_ticket_remove_allowed',
            'filter_index' => 'main_table.is_ticket_remove_allowed',
            'type' => 'options',
            'options' => [
                0 => __('No'),
                1 => __('Yes'),
            ],
            ]);

        return parent::_prepareColumns();
    }

    /**
     * @param string                                    $renderedValue
     * @param \Mirasvit\Helpdesk\Model\Permission       $row
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @param bool                                      $isExport
     *
     * @return \Magento\Framework\Phrase|string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function _roleFormat($renderedValue, $row, $column, $isExport)
    {
        if (!$renderedValue) {
            $renderedValue = __('All Roles');
        }

        return $renderedValue;
    }

    /**
     * @param \Mirasvit\Helpdesk\Block\Adminhtml\Permission\Grid $renderedValue
     * @param \Mirasvit\Helpdesk\Model\Permission                $row
     * @param \Magento\Backend\Block\Widget\Grid_Column          $column
     * @param bool                                               $isExport
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function _departmentsFormat($renderedValue, $row, $column, $isExport)
    {
        $row->loadDepartmentIds();
        $values = [];
        foreach ($row->getDepartmentIds() as $id) {
            if ($id) {
                $department = $this->departmentFactory->create()->load($id);
                $values[] = $department->getName();
            } else {
                $values[] = __('All Departments');
            }
        }

        return implode(', ', $values);
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('permission_id');
        $this->getMassactionBlock()->setFormFieldName('permission_id');
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
