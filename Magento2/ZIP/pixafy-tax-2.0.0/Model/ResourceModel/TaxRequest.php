<?php
/**
 * Copyright © 2016 Pixafy Services LLC. All rights reserved.
 *
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL 3.0)
 * @author  Alex Lukyanau <alukyanau@pixafy.com>
 */
namespace VertexSMB\Tax\Model\ResourceModel;

class TaxRequest extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model Get tablename from config
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('vertexsmb_taxrequest', 'request_id');
    }
}
