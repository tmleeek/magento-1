<?php
/**
 * Copyright © 2016 Pixafy Services LLC. All rights reserved.
 *
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL 3.0)
 * @author  Alex Lukyanau <alukyanau@pixafy.com>
 */
namespace VertexSMB\Tax\Helper\Request;

/**
 * Seller information helper
 */
class Header extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $vertexSMBConfigHelper;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \VertexSMB\Tax\Helper\Config $vertexSMBConfigHelper
    ) {
    
        $this->vertexSMBConfigHelper = $vertexSMBConfigHelper;
        parent::__construct($context);
    }

    public function addHeaderInformation()
    {
        $data = [];
        $data['Login'] = [
            'TrustedId' =>   $this->vertexSMBConfigHelper->getTrustedId()
        ];
        return $data;
    }
}
