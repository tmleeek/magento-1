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

use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\ProductMetadataInterface;

class Field extends \Mirasvit\Report\Model\Select\Field
{
    /**
     * @var bool
     */
    protected $isJoined = false;

    /**
     * @var EavConfig
     */
    protected $eavConfig;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var int
     */
    protected $entityTypeId;

    /**
     * @var string
     */
    protected $eavTableAlias;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    protected $attribute;

    /**
     * @param EavConfig                           $eavConfig
     * @param ProductMetadataInterface            $productMetadata
     * @param \Mirasvit\Report\Model\Select\Table $table
     * @param string                              $name
     * @param int                                 $entityTypeId
     */
    public function __construct(
        EavConfig $eavConfig,
        ProductMetadataInterface $productMetadata,
        $table,
        $name,
        $entityTypeId
    ) {
        parent::__construct($table, $name);

        $this->productMetadata = $productMetadata;

        $this->eavTableAlias = $this->table->getName() . '_' . $this->name;
        $this->eavConfig = $eavConfig;
        $this->entityTypeId = $entityTypeId;

        $this->attribute = $this->eavConfig->getAttribute($this->entityTypeId, $this->name);
    }

    /**
     * @return string
     */
    public function toString()
    {
        if ($this->attribute->getBackend()->isStatic()) {
            return $this->table->getName() . '.' . $this->name;
        } else {
            return $this->eavTableAlias . '.value';
        }
    }

    /**
     * @param \Mirasvit\Report\Model\Select\Query $query
     * @return void
     */
    public function join(\Mirasvit\Report\Model\Select\Query $query)
    {
        if ($this->isJoined) {
            return;
        }

        if ($this->attribute->getBackend()->isStatic()) {
            $query->joinTable($this->table);
        } else {

            $conditions = [];
            if ($this->productMetadata->getEdition() == "Enterprise") {
                $conditions[] = $this->eavTableAlias . '.row_id = ' . $this->table->getName() . '.row_id';
            } else {
                $conditions[] = $this->eavTableAlias . '.entity_id = ' . $this->table->getName() . '.entity_id';
            }
            $conditions[] = $this->eavTableAlias . '.attribute_id = ' . $this->attribute->getAttributeId();
            $conditions[] = $this->eavTableAlias . '.store_id = 0';

            $query->joinTable($this->table);

            $query->getSelect()->joinLeft(
                [$this->eavTableAlias => $this->attribute->getBackend()->getTable()],
                implode(' AND ', $conditions),
                []
            );
        }

        $this->isJoined = true;
    }
}
