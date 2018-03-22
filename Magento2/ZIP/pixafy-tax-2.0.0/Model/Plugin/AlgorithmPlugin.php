<?php
/**
 * Copyright © 2016 Pixafy Services LLC. All rights reserved.
 *
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL 3.0)
 * @author  Alex Lukyanau <alukyanau@pixafy.com>
 */

namespace VertexSMB\Tax\Model\Plugin;

class AlgorithmPlugin
{

    /**
     *
     * @param \Magento\Tax\Model\System\Config\Source\Algorithm $subject
     * @param unknown                                           $result
     * @return \Magento\Framework\Phrase[][]|string[][]
     */
    // @codingStandardsIgnoreStart
    public function afterToOptionArray(\Magento\Tax\Model\System\Config\Source\Algorithm $subject, $result)
    {
        $options = [
            ['value' => \Magento\Tax\Model\Calculation::CALC_UNIT_BASE, 'label' => __('Unit Price')],
            ['value' => \Magento\Tax\Model\Calculation::CALC_ROW_BASE, 'label' => __('Row Total')],
            ['value' => \Magento\Tax\Model\Calculation::CALC_TOTAL_BASE, 'label' => __('Total')],
            ['value' => \VertexSMB\Tax\Model\Plugin\CalculatorFactoryPlugin::CALC_UNIT_VERTEXSMB, 'label' => __('Vertex SMB')]
        ];
        
        return $options;
    }
}
