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



namespace Mirasvit\Report\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;

class Date extends AbstractHelper
{
    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param DateTime              $date
     * @param Context               $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        DateTime $date,
        Context $context,
        StoreManagerInterface $storeManager
    ) {
        $this->date = $date;
        $this->context = $context;
        $this->storeManager = $storeManager;

        parent::__construct($context);
    }

    const TODAY            = 'today';
    const YESTERDAY        = 'yesterday';
    const THIS_WEEK        = 'week';
    const PREVIOUS_WEEK    = 'prev_week';
    const THIS_MONTH       = 'month';
    const PREVIOUS_MONTH   = 'prev_month';
    const THIS_QUARTER     = 'quarter';
    const PREVIOUS_QUARTER = 'prev_quarter';
    const THIS_YEAR        = 'year';
    const PREVIOUS_YEAR    = 'prev_year';

    const LAST_24H = 'last_24h';
    const LAST_7D  = 'last_7d';
    const LAST_30D = 'last_30d';
    const LAST_3M  = 'last_3m';
    const LAST_12M = 'last_12m';

    const LIFETIME = 'lifetime';
    const CUSTOM   = 'custom';

    /**
     * @param bool $subIntervals
     * @param bool $lifetime
     * @param bool $custom
     * @param bool $withHint
     * @return array
     */
    public function getIntervals($subIntervals = false, $lifetime = false, $custom = false, $withHint = false)
    {
        $intervals = [];

        $intervals[self::TODAY] = 'Today';
        $intervals[self::YESTERDAY] = 'Yesterday';

        $intervals[self::THIS_WEEK] = 'This week';
        $intervals[self::PREVIOUS_WEEK] = 'Previous week';

        $intervals[self::THIS_MONTH] = 'This month';
        $intervals[self::PREVIOUS_MONTH] = 'Previous month';

        $intervals[self::THIS_YEAR] = 'This year';
        $intervals[self::PREVIOUS_YEAR] = 'Previous year';

        if ($subIntervals) {
            $intervals[self::LAST_24H] = 'Last 24h hours';
            $intervals[self::LAST_7D] = 'Last 7 days';
            $intervals[self::LAST_30D] = 'Last 30 days';
            $intervals[self::LAST_3M] = 'Last 3 months';
            $intervals[self::LAST_12M] = 'Last 12 months';
        }

        if ($lifetime) {
            $intervals[self::LIFETIME] = 'Lifetime';
        }

        if ($custom) {
            $intervals[self::CUSTOM] = 'Custom';
        }

        if ($withHint) {
            foreach ($intervals as $code => $label) {
                $label = __($label);

                $hint = $this->getIntervalHint($code);

                if ($hint) {
                    $label .= ' / ' . $hint;

                    $intervals[$code] = $label . '';
                }
            }
        }

        return $intervals;
    }

    /**
     * @param string $code
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getIntervalHint($code)
    {
        $hint = '';

        $interval = $this->getInterval($code, true);
        /** @var \Zend_Date $from */
        $from = $interval->getFrom();
        /** @var \Zend_Date $to */
        $to = $interval->getTo();

        switch ($code) {
            case self::TODAY:
            case self::YESTERDAY:
                $hint = $from->get('MMM, d HH:mm') . ' - ' . $to->get('HH:mm');
                break;

            case self::THIS_WEEK:
            case self::PREVIOUS_WEEK:
            case self::LAST_7D:
            case self::LAST_30D:
            case self::LAST_3M:
            case self::LAST_12M:
            case self::THIS_MONTH:
            case self::PREVIOUS_MONTH:
            case self::THIS_QUARTER:
            case self::PREVIOUS_QUARTER:
                if ($from->get('YYYY') == $to->get('YYYY') && $from->get('YYYY') == date('Y')) {
                    if ($from->get('MMM') == $to->get('MMM')) {
                        $hint = $from->get('MMM, d') . ' - ' . $to->get('d');
                    } else {
                        $hint = $from->get('MMM, d') . ' - ' . $to->get('MMM, d');
                    }
                } else {
                    $hint = $from->get('MMM, d YYYY') . ' - ' . $to->get('MMM, d YYYY');
                }

                break;

            case self::THIS_YEAR:
            case self::PREVIOUS_YEAR:
                $hint = $from->get('MMM, d YYYY') . ' - ' . $to->get('MMM, d');
                break;

            case self::LAST_24H:
                $hint = $from->get('MMM, d HH:mm') . ' - ' . $to->get('MMM, d HH:mm');
                break;

            case self::LIFETIME:
                $hint = $from->get('MMM, d YYYY') . ' - ' . $to->get('MMM, d YYYY');
                break;
        }

        return $hint;
    }

    /**
     * @param bool $subIntervals
     * @param bool $lifetime
     * @param bool $custom
     * @return array
     */
    public function getIntervalsAsOptions($subIntervals = false, $lifetime = false, $custom = false)
    {
        $intervals = $this->getIntervals($subIntervals, $lifetime, $custom);
        $options = [];

        foreach ($intervals as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label,
            ];
        }

        return $options;
    }

    /**
     * @param string $code
     * @param bool   $timezone
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getInterval($code, $timezone = false)
    {
        $timestamp = $this->date->gmtTimestamp();
        $firstDay = (int)$this->context->getScopeConfig()->getValue('general/locale/firstday');
        $locale = $this->context->getScopeConfig()->getValue('general/locale/code');
        $localeTimezone = $this->context->getScopeConfig()->getValue('general/locale/timezone');

        if ($timezone) {
            $timestamp = $this->date->date($timestamp);
        }

        $from = new \Zend_Date(
            $timestamp,
            null,
            $locale
        );

        $offset = $this->date->calculateOffset($localeTimezone);
        $from->addSecond($offset);
        $to = clone $from;

        switch ($code) {
            case self::TODAY:
                $from->setTime('00:00:00');

                $to->setTime('23:59:59');

                break;

            case self::YESTERDAY:
                $from->subDay(1)
                    ->setTime('00:00:00');

                $to->subDay(1)
                    ->setTime('23:59:59');

                break;

            case self::THIS_MONTH:
                $from->setDay(1)
                    ->setTime('00:00:00');

                $to->setDay(1)
                    ->addDay($to->get(\Zend_Date::MONTH_DAYS) - 1)
                    ->setTime('23:59:59');

                break;

            case self::PREVIOUS_MONTH:
                $from->setDay(1)
                    ->subMonth(1)
                    ->setTime('00:00:00');

                $to->setDay(1)
                    ->subMonth(1)
                    ->addDay($to->get(\Zend_Date::MONTH_DAYS) - 1)
                    ->setTime('23:59:59');

                break;

            case self::THIS_QUARTER:
                $month = intval($from->get(\Zend_Date::MONTH) / 4) * 3 + 1;
                $from->setDay(1)
                    ->setMonth($month)
                    ->setTime('00:00:00');

                $to->setDay(1)
                    ->setMonth($month)
                    ->addMonth(3)
                    ->subDay(1)
                    ->setTime('23:59:59');

                break;

            case self::PREVIOUS_QUARTER:
                $month = intval($from->get(\Zend_Date::MONTH) / 4) * 3 + 1;

                $from->setDay(1)
                    ->setMonth($month)
                    ->subMonth(3)
                    ->setTime('00:00:00');

                $to->setDay(1)
                    ->setMonth($month)
                    ->addMonth(3)
                    ->subDay(1)
                    ->subMonth(3)
                    ->setTime('23:59:59');

                break;

            case self::THIS_YEAR:
                $from->setDay(1)
                    ->setMonth(1)
                    ->setTime('00:00:00');

                $to->setDay(1)
                    ->setMonth(1)
                    ->addDay($to->get(\Zend_Date::LEAPYEAR) ? 365 : 364)
                    ->setTime('23:59:59');

                break;

            case self::PREVIOUS_YEAR:
                $from->setDay(1)
                    ->setMonth(1)
                    ->subYear(1)
                    ->setTime('00:00:00');

                $to->setDay(1)
                    ->setMonth(1)
                    ->addDay($to->get(\Zend_Date::LEAPYEAR) ? 365 : 364)
                    ->subYear(1)
                    ->setTime('23:59:59');

                break;

            case self::LAST_24H:
                $from->subDay(1);

                break;

            case self::THIS_WEEK:
                $weekday = $from->get(\Zend_Date::WEEKDAY_DIGIT); #0-6

                if ($weekday < $firstDay) {
                    $weekday += 7;
                }

                $from->subDay($weekday - $firstDay)
                    ->setTime('00:00:00');

                $to->addDay(6 - $weekday + $firstDay)
                    ->setTime('23:59:59');

                break;

            case self::PREVIOUS_WEEK:
                $weekday = $from->get(\Zend_Date::WEEKDAY_DIGIT); #0-6

                if ($weekday < $firstDay) {
                    $weekday += 7;
                }

                $from->subDay($weekday - $firstDay)
                    ->subWeek(1)
                    ->setTime('00:00:00');

                $to->addDay(6 - $weekday + $firstDay)
                    ->subWeek(1)
                    ->setTime('23:59:59');

                break;

            case self::LAST_7D:
                $from->subDay(7);

                break;

            case self::LAST_30D:
                $from->subDay(30);

                break;

            case self::LAST_3M:
                $from->subMonth(3);

                break;

            case self::LAST_12M:
                $from->subYear(1);

                break;

            case self::LIFETIME:
                $from->subYear(10);

                $to->addYear(10);

                break;
        }

        return new \Magento\Framework\DataObject([
            'from' => $from,
            'to'   => $to]);
    }

    /**
     * @param string $code
     * @param int    $offsetDays
     * @param bool   $timezone
     * @return array
     */
    public function getPreviousInterval($code, $offsetDays = 0, $timezone = false)
    {
        $interval = $this->getInterval($code, $timezone);

        $now = new \Zend_Date(
            $this->date->gmtTimestamp(),
            null,
            $this->storeManager->getStore()->getLocaleCode()
        );

        $diff = clone $interval->getTo();
        $diff->sub($interval->getFrom());

        if ($timezone) {
            $diff->sub($this->date->getGmtOffset());
        }

        if ($interval->getTo()->getTimestamp() > $now->getTimestamp()) {
            $interval->getTo()->subTimestamp($interval->getTo()->getTimestamp() - $now->getTimestamp());
        }

        if (365 === intval($offsetDays)) {
            $interval->getFrom()->subYear(1);
            $interval->getTo()->subYear(1);
        } elseif (intval($offsetDays) > 0) {
            $interval->getFrom()->subDay($offsetDays);
            $interval->getTo()->subDay($offsetDays);
        } else {
            $interval->getFrom()->sub($diff);
            $interval->getTo()->sub($diff);
        }

        return $interval;
    }

    /**
     * @return int
     */
    public function getTimestamp()
    {
        return $this->date->timestamp();
    }

    /**
     * @return int
     */
    public function getGmtTimestamp()
    {
        return $this->date->gmtTimestamp();
    }
}
