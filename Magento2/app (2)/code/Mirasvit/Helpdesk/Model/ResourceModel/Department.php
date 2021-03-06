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



namespace Mirasvit\Helpdesk\Model\ResourceModel;

class Department extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\Context
     */
    protected $context;

    /**
     * @var string
     */
    protected $resourcePrefix;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string                                            $resourcePrefix
     * @param array                                             $data
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $resourcePrefix = null,
        array $data = []
    ) {
        $this->context = $context;
        $this->resourcePrefix = $resourcePrefix;
        parent::__construct($context, $resourcePrefix);
    }

    /**
     *
     */
    protected function _construct()
    {
        $this->_init('mst_helpdesk_department', 'department_id');
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     *
     * @return \Magento\Framework\Model\AbstractModel|\Mirasvit\Helpdesk\Model\Department
     */
    public function loadUserIds(\Magento\Framework\Model\AbstractModel $object)
    {
        /* @var  \Mirasvit\Helpdesk\Model\Department $object */
        $select = $this->getConnection()->select()
            ->from($this->getTable('mst_helpdesk_department_user'))
            ->where('du_department_id = ?', $object->getId());
        if ($data = $this->getConnection()->fetchAll($select)) {
            $array = [];
            foreach ($data as $row) {
                $array[] = $row['du_user_id'];
            }
            $object->setData('user_ids', $array);
        }

        return $object;
    }

    /**
     * @param \Mirasvit\Helpdesk\Model\Department $object
     *
     * @return void
     */
    protected function saveUserIds($object)
    {
        /* @var  \Mirasvit\Helpdesk\Model\Department $object */
        $condition = $this->getConnection()->quoteInto('du_department_id = ?', $object->getId());
        $this->getConnection()->delete($this->getTable('mst_helpdesk_department_user'), $condition);
        foreach ((array) $object->getData('user_ids') as $id) {
            $objArray = [
                'du_department_id' => $object->getId(),
                'du_user_id' => $id,
            ];
            $this->getConnection()->insert(
                $this->getTable('mst_helpdesk_department_user'),
                $objArray
            );
        }
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     *
     * @return \Magento\Framework\Model\AbstractModel|\Mirasvit\Helpdesk\Model\Department
     */
    protected function loadStoreIds(\Magento\Framework\Model\AbstractModel $object)
    {
        /* @var  \Mirasvit\Helpdesk\Model\Department $object */
        $select = $this->getConnection()->select()
            ->from($this->getTable('mst_helpdesk_department_store'))
            ->where('ds_department_id = ?', $object->getId());
        if ($data = $this->getConnection()->fetchAll($select)) {
            $array = [];
            foreach ($data as $row) {
                $array[] = $row['ds_store_id'];
            }
            $object->setData('store_ids', $array);
        }

        return $object;
    }

    /**
     * @param \Mirasvit\Helpdesk\Model\Department $object
     * @return void
     */
    protected function saveStoreIds($object)
    {
        /* @var  \Mirasvit\Helpdesk\Model\Department $object */
        $condition = $this->getConnection()->quoteInto('ds_department_id = ?', $object->getId());
        $this->getConnection()->delete($this->getTable('mst_helpdesk_department_store'), $condition);
        foreach ((array) $object->getData('store_ids') as $id) {
            $objArray = [
                'ds_department_id' => $object->getId(),
                'ds_store_id' => $id,
            ];
            $this->getConnection()->insert(
                $this->getTable('mst_helpdesk_department_store'),
                $objArray
            );
        }
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     *
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var  \Mirasvit\Helpdesk\Model\Department $object */
        if (!$object->getIsMassDelete()) {
            $this->loadUserIds($object);
            $this->loadStoreIds($object);
        }

        return parent::_afterLoad($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     *
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var  \Mirasvit\Helpdesk\Model\Department $object */
        if (!$object->getId()) {
            $object->setCreatedAt((new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT));
        }
        $object->setUpdatedAt((new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT));

        return parent::_beforeSave($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     *
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var  \Mirasvit\Helpdesk\Model\Department $object */
        if (!$object->getIsMassStatus()) {
            $this->saveUserIds($object);
            $this->saveStoreIds($object);
        }

        return parent::_afterSave($object);
    }

    /************************/
}
