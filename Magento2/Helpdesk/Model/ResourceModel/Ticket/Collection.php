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



namespace Mirasvit\Helpdesk\Model\ResourceModel\Ticket;

/**
 * @method \Mirasvit\Helpdesk\Model\Ticket getFirstItem()
 * @method \Mirasvit\Helpdesk\Model\Ticket getLastItem()
 * @method \Mirasvit\Helpdesk\Model\ResourceModel\Ticket\Collection|\Mirasvit\Helpdesk\Model\Ticket[] addFieldToFilter
 * @method \Mirasvit\Helpdesk\Model\ResourceModel\Ticket\Collection|\Mirasvit\Helpdesk\Model\Ticket[] setOrder
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'ticket_id';//@codingStandardsIgnoreLine

    /**
     * @var \Mirasvit\Helpdesk\Model\SearchFactory
     */
    protected $searchFactory;

    /**
     * @var \Mirasvit\Helpdesk\Model\ResourceModel\Ticket\CollectionFactory
     */
    protected $ticketCollectionFactory;

    /**
     * @var \Mirasvit\Helpdesk\Helper\Field
     */
    protected $helpdeskField;

    /**
     * @var \Magento\Framework\Data\Collection\EntityFactoryInterface
     */
    protected $entityFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Data\Collection\Db\FetchStrategyInterface
     */
    protected $fetchStrategy;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var object
     */
    protected $connection;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    protected $resource;

    /**
     * @param \Mirasvit\Helpdesk\Model\SearchFactory                          $searchFactory
     * @param \Mirasvit\Helpdesk\Model\ResourceModel\Ticket\CollectionFactory $ticketCollectionFactory
     * @param \Mirasvit\Helpdesk\Helper\Field                                 $helpdeskField
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface       $entityFactory
     * @param \Psr\Log\LoggerInterface                                        $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface    $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface                       $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface                      $storeManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface                  $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb            $resource
     * @param array                                                           $data
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Mirasvit\Helpdesk\Model\SearchFactory $searchFactory,
        \Mirasvit\Helpdesk\Model\ResourceModel\Ticket\CollectionFactory $ticketCollectionFactory,
        \Mirasvit\Helpdesk\Helper\Field $helpdeskField,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null,
        array $data = []
    ) {
        $this->searchFactory = $searchFactory;
        $this->ticketCollectionFactory = $ticketCollectionFactory;
        $this->helpdeskField = $helpdeskField;
        $this->entityFactory = $entityFactory;
        $this->logger = $logger;
        $this->fetchStrategy = $fetchStrategy;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->connection = $connection;
        $this->resource = $resource;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     *
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\Helpdesk\Model\Ticket', 'Mirasvit\Helpdesk\Model\ResourceModel\Ticket');
    }

    /**
     * @param bool $emptyOption
     *
     * @return array
     */
    public function toOptionArray($emptyOption = false)
    {
        $arr = [];
        if ($emptyOption) {
            $arr[0] = ['value' => 0, 'label' => __('-- Please Select --')];
        }
        /** @var \Mirasvit\Helpdesk\Model\Ticket $item */
        foreach ($this as $item) {
            $arr[] = ['value' => $item->getId(), 'label' => $item->getName()];
        }

        return $arr;
    }

    /**
     * @param bool $emptyOption
     *
     * @return array
     */
    public function getOptionArray($emptyOption = false)
    {
        $arr = [];
        if ($emptyOption) {
            $arr[0] = __('-- Please Select --');
        }
        /** @var \Mirasvit\Helpdesk\Model\Ticket $item */
        foreach ($this as $item) {
            $arr[$item->getId()] = $item->getName();
        }

        return $arr;
    }

    /**
     * @return $this|\Mirasvit\Helpdesk\Model\Ticket[]
     */
    public function joinEmails()
    {
        $select = $this->getSelect();
        $select->joinLeft(
            ['email' => $this->getTable('mst_helpdesk_email')],
            'main_table.email_id = email.email_id',
            ['pattern_id']
        );

        return $this;
    }

    /**
     * @return $this|\Mirasvit\Helpdesk\Model\Ticket[]
     */
    public function joinFields()
    {
        $select = $this->getSelect();
        $select->joinLeft(
            ['department' => $this->getTable('mst_helpdesk_department')],
            'main_table.department_id = department.department_id',
            ['department' => 'name']
        );
        $select->joinLeft(
            ['status' => $this->getTable('mst_helpdesk_status')],
            'main_table.status_id = status.status_id',
            ['status' => 'name']
        );
        $select->joinLeft(
            ['priority' => $this->getTable('mst_helpdesk_priority')],
            'main_table.priority_id = priority.priority_id',
            ['priority' => 'name']
        );
        $select->joinLeft(
            ['user' => $this->getTable('admin_user')],
            'main_table.user_id = user.user_id',
            ['user_name' => 'CONCAT(firstname, " ", lastname)']
        );

        return $this;
    }

    /**
     * @return $this|\Mirasvit\Helpdesk\Model\Ticket[]
     */
    public function joinMessages()
    {
        $select = $this->getSelect();
        $select->joinLeft(
            ['message' => $this->getTable('mst_helpdesk_message')],
            'main_table.ticket_id = message.ticket_id',
            ['message_body' => 'group_concat(message.body)']
        )
            ->group('main_table.ticket_id');

        return $this;
    }

    /**
     * @return $this|\Mirasvit\Helpdesk\Model\Ticket[]
     */
    public function joinColors()
    {
        $select = $this->getSelect();
        $select->joinLeft(
            ['status' => $this->getTable('mst_helpdesk_status')],
            'main_table.status_id = status.status_id',
            ['status_color' => 'color']
        );
        $select->joinLeft(
            ['priority' => $this->getTable('mst_helpdesk_priority')],
            'main_table.priority_id = priority.priority_id',
            ['priority_color' => 'color']
        );
        $select->joinLeft(
            ['department' => $this->getTable('mst_helpdesk_department')],
            'main_table.department_id = department.department_id',
            []
        );

        return $this;
    }

    /**
     * @param int $tagId
     *
     * @return $this|\Mirasvit\Helpdesk\Model\Ticket[]
     */
    public function addTagFilter($tagId)
    {
        $this->getSelect()
            ->where("EXISTS (SELECT * FROM `{$this->getTable('mst_helpdesk_ticket_tag')}`
                AS `ticket_tag_table`
                WHERE main_table.ticket_id = ticket_tag_table.tt_ticket_id
                AND ticket_tag_table.tt_tag_id in (?))", [0, $tagId]);

        return $this;
    }

    /**
     *
     */
    protected function initFields()
    {
        $select = $this->getSelect();
        $select->joinLeft(
            ['email' => $this->getTable('mst_helpdesk_email')],
            'main_table.email_id = email.email_id',
            ['mailing_date'] 
        );
    }

    /**
     *
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->initFields();
    }

     /************************/

    /**
     * @return \Mirasvit\Helpdesk\Model\Search
     */
    public function getSearchInstance()
    {
        $collection = $this->ticketCollectionFactory->create()
            ->joinEmails()
            ->joinFields()
            ->joinMessages();

        $search = $this->searchFactory->create();
        $search->setSearchableCollection($collection);
        $attributes = [
            'main_table.ticket_id' => 0,
        //            'main_table.description' => 0,
            'main_table.subject' => 100,
            'main_table.code' => 0,
            'main_table.order_id' => 0,
            'main_table.last_reply_name' => 0,
            'main_table.search_index' => 0,
            'user_name' => 0,
            'message_body' => 0,
            //customer name
            'customer_email' => 0,
            //order_id
            'department.name' => 0,
            'status.name' => 0,
            'priority.name' => 0,
        ];
        foreach ($this->helpdeskField->getStaffCollection() as $field) {
            if ($field->getType() == 'text' || $field->getType() == 'textarea') {
                $attributes['main_table.'.$field->getCode()] = 0;
            }
        }
        $search->setSearchableAttributes($attributes);
        $search->setPrimaryKey('ticket_id');

        return $search;
    }

    /**
     * @return $this|\Mirasvit\Helpdesk\Model\Ticket[]
     */
    public function joinStatuses()
    {
        $this->getSelect()->joinLeft(
            ['status' => $this->getTable('mst_helpdesk_status')],
            'main_table.status_id = status.status_id',
            ['status' => 'name', 'status_sort_order' => 'sort_order']
        );

        return $this;
    }

    /**
     * @param int $customerId
     * @return int
     */
    public function countOpenTickets($customerId)
    {
        $this->joinStatuses()
            ->addFieldToFilter('customer_id', $customerId)
            ->getSelect()->columns('COUNT(*) as cnt')
            ->where('status.code = "' . \Mirasvit\Helpdesk\Model\Config::STATUS_OPEN . '"');

        return $this->load()->getFirstItem()->getCnt();
    }

    /**
     * @return $this
     */
    public function applyDefaultOrder()
    {
        return $this->setOrder('last_reply_at', 'DESK');
    }
}
