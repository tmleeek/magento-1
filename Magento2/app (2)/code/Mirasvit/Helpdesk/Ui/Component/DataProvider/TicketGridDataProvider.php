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
 * @package   mirasvit/module-helpdesk
 * @version   1.1.25
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Helpdesk\Ui\Component\DataProvider;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;

class TicketGridDataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @param string                                                             $name
     * @param string                                                             $primaryFieldName
     * @param string                                                             $requestFieldName
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\Reporting $reporting
     * @param SearchCriteriaBuilder                                              $searchCriteriaBuilder
     * @param RequestInterface                                                   $request
     * @param FilterBuilder                                                      $filterBuilder
     * @param \Magento\Framework\Registry                                        $registry
     * @param array                                                              $meta
     * @param array                                                              $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\Framework\View\Element\UiComponent\DataProvider\Reporting $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        \Magento\Framework\Registry $registry,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );
        $this->registry = $registry;
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if ($filter->getField() == 'status_id') {
            $filter->setField('main_table.status_id');
        }
        if ($filter->getField() == 'code') {
            $filter->setField('main_table.code');
        }
        if ($filter->getField() == 'subject') {
            $filter->setField('main_table.subject');
        }
        if ($filter->getField() == 'created_at') {
            $filter->setField('main_table.created_at');
        }

        parent::addFilter($filter);
    }

    /**
     * Returns Search result
     *
     * @return \Mirasvit\Helpdesk\Model\ResourceModel\Ticket\Collection
     */
    public function getSearchResult()
    {
        return $this->reporting->search($this->getSearchCriteria())->joinStatuses();
    }
}
