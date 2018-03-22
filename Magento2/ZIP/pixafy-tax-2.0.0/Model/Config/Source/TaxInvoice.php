<?php
/**
 * Copyright © 2016 Pixafy Services LLC. All rights reserved.
 *
 * @license http://opensource.org/licenses/OSL-3.0  The Open Software License 3.0 (OSL 3.0)
 * @author  Alex Lukyanau <alukyanau@pixafy.com>
 */

namespace VertexSMB\Tax\Model\Config\Source;

class TaxInvoice implements \Magento\Framework\Option\ArrayInterface
{

    /**
     *
     * @var string[]
     */
    protected $_options = [
        [
            'label' => "When Invoice Created",
            'value' => 'invoice_created'
        ],
        [
            'label' => "When Order Status Is",
            'value' => 'order_status'
        ]
    ];

    /**
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = $this->_options;
        return $options;
    }
}
