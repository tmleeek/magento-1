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
 * @package   mirasvit/module-report
 * @version   1.1.15-beta3
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Report\Model\Select\Column\Date;

use Magento\Framework\ObjectManagerInterface;
use Mirasvit\Report\Model\Select\Column;

class Year extends Column
{
    /**
     * {@inheritdoc}
     */
    public function __construct(
        $name,
        $data = []
    ) {
        parent::__construct($name, $data);

        $this->setExpression($this->connection->getDateFormatSql('%1', '%Y-01-01 00:00:00'));
    }

    /**
     * {@inheritdoc}
     */
    public function prepareValue($value)
    {
        return date('Y', strtotime($value));
    }
}
