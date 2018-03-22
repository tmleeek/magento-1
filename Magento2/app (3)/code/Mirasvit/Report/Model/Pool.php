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


namespace Mirasvit\Report\Model;

use Magento\Framework\ObjectManagerInterface;

class Pool
{
    /**
     * @var AbstractReport[]
     */
    protected $pool;

    /**
     * @var array
     */
    protected $reports;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array                  $reports
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $reports = []
    ) {
        $this->objectManager = $objectManager;
        $this->reports = $reports;
    }

    /**
     * @param string $identifier
     * @return AbstractReport
     */
    public function get($identifier)
    {
        $this->initPool();

        foreach ($this->pool as $report) {
            if ($report->getIdentifier() == $identifier) {
                if (!$report->isInitialized()) {
                    $report->initialize();
                }

                return $report;
            }
        }

        return false;
    }

    /**
     * @return \Mirasvit\Report\Model\AbstractReport[]
     */
    public function getReports()
    {
        $this->initPool();

        $reports = [];
        foreach ($this->pool as $report) {
            $reports[] = $report;
        }
        return $reports;
    }

    /**
     * @return $this
     */
    protected function initPool()
    {
        if (count($this->pool)) {
            return $this;
        }

        foreach ($this->reports as $report) {
            $this->pool[] = $this->objectManager->get($report);
        }

        return $this;
    }
}
