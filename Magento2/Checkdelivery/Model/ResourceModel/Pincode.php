<?php
/**
 * Copyright © 2015 Bluethink. All rights reserved.
 */
namespace Bluethink\Checkdelivery\Model\ResourceModel;

/**
 * Services resource
 */
class Pincode extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('checkdelivery_pincode', 'id');
    }

  
}
