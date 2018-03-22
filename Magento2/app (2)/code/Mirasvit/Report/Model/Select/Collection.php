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


namespace Mirasvit\Report\Model\Select;

use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Mirasvit\Report\Model\Config\Map;

class Collection extends \Magento\Framework\Data\Collection
{
    /**
     * @var Query
     */
    protected $query;

    /**
     * @var Map
     */
    protected $map;

    /**
     * @var PricingHelper
     */
    protected $pricingHelper;

    /**
     * @param Map           $map
     * @param PricingHelper $pricingHelper
     */
    public function __construct(
        Map $map,
        PricingHelper $pricingHelper
    ) {
        $this->map = $map;
        $this->pricingHelper = $pricingHelper;
    }

    /**
     * @param Query $query
     * @return $this
     */
    public function setQuery($query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @param Column $column
     * @return $this
     */
    public function addColumnToSelect($column)
    {
        $this->query->addColumnToSelect($column);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        $this->_items = [];
        foreach ($this->query->getItems() as $item) {
            $data = $item->getData();
            foreach ($data as $key => $value) {
                $data[$key] = $this->prepareValue($key, $value);
            }
            $this->_items[] = new \Magento\Framework\DataObject($data);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        return $this->query->getSize();
    }

    /**
     * {@inheritdoc}
     */
    public function setCurPage($page)
    {
        $this->query->setCurrentPage($page);

        return parent::setCurPage($page);
    }

    /**
     * {@inheritdoc}
     */
    public function setPageSize($size)
    {
        $this->query->setPageSize($size);

        return parent::setPageSize($size);
    }

    /**
     * @param string        $column
     * @param string|number $value
     * @return string
     */
    protected function prepareValue($column, $value)
    {
        try {
            $instance = $this->map->getColumn($column);
        } catch (\Exception $e) {
            return $value;
        }

        if ($instance->getDataType() == 'number') {
            $value = round($value, 2);
        } elseif ($instance->getDataType() == 'price') {
            $value = $this->pricingHelper->currency($value);
        } elseif ($instance->getDataType() == 'select') {
            if (is_array($instance->getOptions())) {
                foreach ($instance->getOptions() as $option) {
                    if ($option['value'] == $value) {
                        $value = $option['label'];
                    }
                }
            }
        } else {
            $value = $instance->prepareValue($value);
        }

        return strip_tags($value);
    }
}