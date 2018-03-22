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

use Mirasvit\Report\Model\Select\QueryFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Backend\Model\Url as BackendUrl;

class Context
{
    /**
     * @var Select\Query
     */
    protected $query;

    /**
     * @var RequestInterface
     */
    protected $request;


    /**
     * @var QueryFactory
     */
    protected $queryFactory;

    /**
     * @param Config\Map       $map
     * @param QueryFactory     $queryFactory
     * @param RequestInterface $request
     * @param BackendUrl       $urlManager
     */
    public function __construct(
        Config\Map $map,
        QueryFactory $queryFactory,
        RequestInterface $request,
        BackendUrl $urlManager
    ) {
        $this->map = $map;
        $this->queryFactory = $queryFactory;
        $this->query = $queryFactory->create(['map' => $this->map]);
        $this->request = $request;
        $this->urlManager = $urlManager;
    }

    /**
     * @return $this
     */
    public function initQuery()
    {
        $this->query = $this->queryFactory->create(['map' => $this->map]);

        return $this;
    }

    /**
     * @return Config\Map
     */
    public function getMap()
    {
        return $this->map;
    }

    /**
     * @return Select\Query
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param Select\Query $query
     * @return $this
     */
    public function setQuery($query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return BackendUrl
     */
    public function getUrlManager()
    {
        return $this->urlManager;
    }
}
