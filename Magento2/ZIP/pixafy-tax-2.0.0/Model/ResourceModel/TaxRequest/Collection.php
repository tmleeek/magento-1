<?php

namespace VertexSMB\Tax\Model\ResourceModel\TaxRequest;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('VertexSMB\Tax\Model\TaxRequest', 'VertexSMB\Tax\Model\ResourceModel\TaxRequest');
    }
}
