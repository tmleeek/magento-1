<?php
/**
 * @category   Webkul
 * @package    Webkul_MpTwilioSMSNotification
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */ 
namespace Webkul\MpTwilioSMSNotification\Model\Config\Source;

class Status
{
    const DISABLE = 0;
    const ENABLE = 1;
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_manager;

    /**
     * Construct
     *
     * @param \Magento\Framework\Module\Manager $manager
     */
    public function __construct(
        \Magento\Framework\Module\Manager $manager
    ) {
        $this->_manager = $manager;
    }
    
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $data = [
                    [
                        'value'=>self::DISABLE,
                        'label'=>__('disable')
                    ],
                    [
                        'value'=>self::ENABLE,
                        'label'=>__('enable')
                    ]
                ];
        return $data;
    }
}
