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


namespace Mirasvit\Report\Model\Select\Eav;

use Magento\Framework\App\ResourceConnection;
use Mirasvit\Report\Model\Select\FieldFactory;
use Mirasvit\Report\Model\Select\Eav\FieldFactory as EavFieldFactory;
use Magento\Eav\Model\EntityFactory as EavEntityFactory;
use Mirasvit\Report\Model\Select\ColumnFactory;
use Magento\Eav\Model\Config;


class Table extends \Mirasvit\Report\Model\Select\Table
{
    /**
     * @var EavEntityFactory
     */
    protected $eavEntityFactory;

    /**
     * @var \Mirasvit\Report\Model\Select\Eav\FieldFactory
     */
    protected $eavFieldFactory;

    /**
     * @var ColumnFactory
     */
    protected $columnFactory;

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @param ResourceConnection                             $resource
     * @param \Mirasvit\Report\Model\Select\Eav\FieldFactory $eavFieldFactory
     * @param FieldFactory                                   $fieldFactory
     * @param EavEntityFactory                               $eavEntityFactory
     * @param ColumnFactory                                  $columnFactory
     * @param Config                                         $eavConfig
     * @param string                                         $name
     * @param string                                         $type
     * @param string                                         $connection
     */
    public function __construct(
        ResourceConnection $resource,
        EavFieldFactory $eavFieldFactory,
        FieldFactory $fieldFactory,
        EavEntityFactory $eavEntityFactory,
        ColumnFactory $columnFactory,
        Config $eavConfig,
        $name,
        $type,
        $connection = 'default'
    ) {
        parent::__construct($resource, $fieldFactory, $name, $connection);

        $this->eavEntityFactory = $eavEntityFactory;
        $this->eavFieldFactory = $eavFieldFactory;
        $this->columnFactory = $columnFactory;
        $this->eavConfig = $eavConfig;

        $this->initByEntityType($type);
    }

    /**
     * @param string $type
     * @return void
     */
    protected function initByEntityType($type)
    {
        $entityTypeId = (int)$this->eavEntityFactory->create()->setType($type)->getTypeId();

        $attributeCodes = $this->eavConfig->getEntityAttributeCodes($entityTypeId);
        foreach ($attributeCodes as $attributeCode) {
            $attribute = $this->eavConfig->getAttribute($entityTypeId, $attributeCode);

            $field = $this->eavFieldFactory->create(
                [
                    'table'        => $this,
                    'name'         => $attributeCode,
                    'entityTypeId' => $type,
                ]
            );

            $this->fieldsPool[$field->getName()] = $field;

            if ($attribute->getDefaultFrontendLabel()) {
                $options = false;

                if ($attribute->usesSource()) {
                    $options = $attribute->getSource()->toOptionArray();
                }

                $this->columnFactory->create(
                    [
                        'name' => $attributeCode,
                        'data' => [
                            'label'   => $attribute->getDefaultFrontendLabel(),
                            'type'    => $attribute->getFrontendInput(),
                            'options' => $options,
                            'table'   => $this,
                            'fields'  => [
                                $attributeCode,
                            ],
                        ],
                    ]
                );
            }
        }
    }
}
