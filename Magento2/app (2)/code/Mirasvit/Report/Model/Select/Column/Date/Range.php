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

use Mirasvit\Report\Model\Select\Column;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;
use Mirasvit\Report\Helper\Date as DateHelper;
use Magento\Framework\Stdlib\DateTime;
use Mirasvit\Report\Model\Config;

class Range extends Column
{
    /**
     * @var string
     */
    protected $range = 'full';

    /**
     * @var DateHelper
     */
    protected $dateHelper;

    /**
     * @param ResourceConnection $resource
     * @param DateHelper         $dateHelper
     * @param Config             $config
     * @param string             $name
     * @param array              $data
     */
    public function __construct(
        ResourceConnection $resource,
        DateHelper $dateHelper,
        Config $config,
        $name,
        $data = []
    ) {
        parent::__construct($name, $data);

        $this->dateHelper = $dateHelper;
        $this->config = $config;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
    }

    /**
     * @param string $range
     * @return $this
     */
    public function setRange($range)
    {
        $this->range = $range;

        return $this;
    }

    /**
     * @return string
     */
    public function getRange()
    {
        return $this->range;
    }

    /**
     * @return \Zend_Db_Expr
     */
    public function toString()
    {
        switch ($this->range) {
            case 'full':
                $this->expression = $this->getFullExpression();
                break;

            case 'day':
                $this->expression = $this->getDayExpression();
                break;

            case 'week':
                $this->expression = $this->getWeekExpression();
                break;

            case 'month':
                $this->expression = $this->getMonthExpression();
                break;

            case 'quarter':
                $this->expression = $this->getQuarterExpression();
                break;

            case 'year':
                $this->expression = $this->getYearExpression();
                break;
        }

        return parent::toString();
    }

    /**
     * @return \Zend_Db_Expr
     */
    protected function getFullExpression()
    {
        return $this->connection->getDateFormatSql('%1', '%Y-%m-%d %H:%i:%s');
    }

    /**
     * @return \Zend_Db_Expr
     */
    protected function getDayExpression()
    {
        return $this->connection->getDateFormatSql('%1', '%Y-%m-%d 00:00:00');
    }

    /**
     * @return \Zend_Db_Expr
     */
    protected function getWeekExpression()
    {
        $year = $this->connection->getDateFormatSql('%1', '%Y');
        $weekOfYear = new \Zend_Db_Expr('WEEKOFYEAR(%1)');
        $firstDay = new \Zend_Db_Expr("'Monday'");
        $contact = $this->connection->getConcatSql([$year, $weekOfYear, $firstDay], ' ');

        return $this->connection->getConcatSql(["STR_TO_DATE($contact, '%X %V %W')", "'00:00:00'"], ' ');
    }

    /**
     * @return \Zend_Db_Expr
     */
    protected function getMonthExpression()
    {
        return $this->connection->getDateFormatSql('%1', '%Y-%m-01 00:00:00');
    }

    /**
     * @return \Zend_Db_Expr
     */
    protected function getQuarterExpression()
    {
        $year = $this->connection->getDateFormatSql('%1', '%Y');
        $quarter = new \Zend_Db_Expr('QUARTER(%1)');

        return $this->connection->getConcatSql([$year, $quarter, "'01 00:00:00'"], '-');
    }

    /**
     * @return \Zend_Db_Expr
     */
    protected function getYearExpression()
    {
        return $this->connection->getDateFormatSql('%1', '%Y-01-01 00:00:00');
    }

    /**
     * @param string $value
     * @return string
     */
    public function prepareValue($value)
    {
        switch ($this->range) {
            case 'day':
                $value = date('d M, Y', strtotime($value));
                break;

            case 'week':
                $value = date('d M, Y', strtotime($value) - 7 * 24 * 60 * 60)
                    . ' - '
                    . date('d M, Y', strtotime($value)) . ' (' . (date('W', strtotime($value)) - 1) . ')';
                break;

            case 'month':
                $value = date('M, Y', strtotime($value));
                break;

            case 'quarter':
                $value = date('M, Y', strtotime($value)) . ' - ' . date('M, Y', strtotime($value) + 80 * 24 * 60 * 60);
                break;

            case 'year':
                $value = date('Y', strtotime($value));
                break;
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getJsConfig()
    {
        $intervals = [];

        foreach ($this->dateHelper->getIntervals() as $code => $label) {
            $interval = $this->dateHelper->getInterval($code);
            $intervals[$label] = [
                $interval->getFrom()->get(DateTime::DATETIME_INTERNAL_FORMAT),
                $interval->getTo()->get(DateTime::DATETIME_INTERNAL_FORMAT),
            ];
        }

        return [
            'component' => 'Mirasvit_Report/js/toolbar/filter/date-range',
            'value'     => [
                'from' => $this->dateHelper->getInterval('month')->getFrom()->get(DateTime::DATETIME_INTERNAL_FORMAT),
                'to'   => $this->dateHelper->getInterval('month')->getTo()->get(DateTime::DATETIME_INTERNAL_FORMAT),
            ],
            'intervals' => $intervals,
            'column'    => $this->getName(),
            'locale'    => $this->config->getLocaleData(),
        ];
    }
}
