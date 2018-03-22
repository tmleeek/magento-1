<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Stripe
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Stripe\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.1') < 0) {
            /*
             * Update tables 'wkstripe_customer'
             */
            $setup->getConnection()->changeColumn(
                $setup->getTable('wkstripe_customer'),
                'last4',
                'label',
                [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'comment' => 'method type label',
                ]
            );

            /*
             * Update tables 'wkstripe_customer'
             */
            $setup->getConnection()->addColumn(
                $setup->getTable('wkstripe_customer'),
                'brand',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'comment' => 'stripe card type',
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('wkstripe_customer'),
                'type',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'comment' => 'stripe payment type',
                ]
            );
        }

        if (version_compare($context->getVersion(), '2.0.3') < 0) {
            /*
             * Update tables 'wkstripe_customer'
             */
            $setup->getConnection()->addColumn(
                $setup->getTable('wkstripe_customer'),
                'fingerprint',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'comment' => 'stripe card type',
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('wkstripe_customer'),
                'expiry_month',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'comment' => 'stripe card type',
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('wkstripe_customer'),
                'expiry_year',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'comment' => 'stripe card type',
                ]
            );
        }
        $setup->endSetup();
    }
}
