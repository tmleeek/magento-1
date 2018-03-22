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


namespace Mirasvit\Helpdesk\Reports;

use Mirasvit\Report\Model\AbstractReport;
use Mirasvit\Report\Model\Select\Column;

class Overview extends AbstractReport
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return __('Helpdesk Tickets');
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'helpdesk_overview';
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $this->setBaseTable('mst_helpdesk_ticket_aggregated_hour');
        $this->addFastFilters([
            'mst_helpdesk_ticket_aggregated_hour|user_id',
            //            'mst_helpdesk_ticket_aggregated_hour|feed_id'
        ]);
        $this->setDefaultColumns([
            'mst_helpdesk_ticket_aggregated_hour|new_ticket_cnt',
            'mst_helpdesk_ticket_aggregated_hour|changed_ticket_cnt',
            'mst_helpdesk_ticket_aggregated_hour|solved_ticket_cnt',
            'mst_helpdesk_ticket_aggregated_hour|first_reply_time',
            'mst_helpdesk_ticket_aggregated_hour|total_reply_cnt',
        ]);


        $this->addAvailableColumns(
            $this->getMap()
                ->getTable('mst_helpdesk_ticket_aggregated_hour')->getColumns()
        );

        $this->setDefaultDimensions('mst_helpdesk_ticket_aggregated_hour|day');

        $this->addAvailableDimensions([
            'mst_helpdesk_ticket_aggregated_hour|hour_of_day',
            'mst_helpdesk_ticket_aggregated_hour|day',
            'mst_helpdesk_ticket_aggregated_hour|week',
            'mst_helpdesk_ticket_aggregated_hour|month',
            'mst_helpdesk_ticket_aggregated_hour|year',
            'admin_user|name',
        ]);

        $this->setGridConfig([
            'paging' => true,
        ]);
        $this->setChartConfig([
            'chartType' => 'column',
            'vAxis'     => 'mst_helpdesk_ticket_aggregated_hour|new_ticket_cnt',
        ]);
    }
}