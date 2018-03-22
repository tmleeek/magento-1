<?php
/**
 * Copyright © 2016 Pixafy Services LLC. All rights reserved.
 *
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL 3.0)
 * @author  Alex Lukyanau <alukyanau@pixafy.com>
 */

namespace VertexSMB\Tax\Model\Calculation;

use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Tax\Model\Calculation\AbstractCalculator;
use Magento\Tax\Api\Data\AppliedTaxInterfaceFactory;
use Magento\Tax\Api\Data\AppliedTaxRateInterfaceFactory;
use Magento\Tax\Api\Data\TaxDetailsItemInterfaceFactory;
use Magento\Tax\Api\TaxClassManagementInterface;
use Magento\Tax\Model\Calculation;

class VertexSMBCalculator extends AbstractCalculator
{
    /*
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \VertexSMB\Tax\Helper\Config
     */
    protected $vertexSMBConfigHelper;
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * Constructor
     *
     * @param TaxClassManagementInterface    $taxClassService
     * @param TaxDetailsItemInterfaceFactory $taxDetailsItemDataObjectFactory
     * @param AppliedTaxInterfaceFactory     $appliedTaxDataObjectFactory
     * @param AppliedTaxRateInterfaceFactory $appliedTaxRateDataObjectFactory
     * @param Calculation                    $calculationTool
     * @param \Magento\Tax\Model\Config      $config
     * @param int                            $storeId
     * @param \Magento\Framework\DataObject  $addressRateRequest
     * @param \Magento\Framework\Registry    $registry
     */
    public function __construct(
        TaxClassManagementInterface $taxClassService,
        TaxDetailsItemInterfaceFactory $taxDetailsItemDataObjectFactory,
        AppliedTaxInterfaceFactory $appliedTaxDataObjectFactory,
        AppliedTaxRateInterfaceFactory $appliedTaxRateDataObjectFactory,
        Calculation $calculationTool,
        \Magento\Tax\Model\Config $config,
        $storeId,
        \Magento\Framework\DataObject $addressRateRequest = null,
        \Magento\Framework\Registry $registry,
        \VertexSMB\Tax\Helper\Config $vertexSMBConfigHelper,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        parent::__construct($taxClassService, $taxDetailsItemDataObjectFactory, $appliedTaxDataObjectFactory, $appliedTaxRateDataObjectFactory, $calculationTool, $config, $storeId, $addressRateRequest);
        $this->registry = $registry;
        $this->vertexSMBConfigHelper = $vertexSMBConfigHelper;
        $this->moduleManager = $moduleManager;
    }
    
    /**
     * {@inheritdoc}
     */
    protected function roundAmount(
        $amount,
        $rate = null,
        $direction = null,
        $type = self::KEY_REGULAR_DELTA_ROUNDING,
        $round = true,
        $item = null
    ) {
        if ($item->getAssociatedItemCode()) {
            // Use delta rounding of the product's instead of the weee's
            $type = $type . $item->getAssociatedItemCode();
        } else {
            $type = $type . $item->getCode();
        }

        return $this->deltaRound($amount, $rate, $direction, $type, $round);
    }

    /**
     * {@inheritdoc}
     */
    protected function calculateWithTaxInPrice(QuoteDetailsItemInterface $item, $quantity, $round = true)
    {
        $taxRateRequest = $this->getAddressRateRequest()->setProductClassId(
            $this->taxClassManagement->getTaxClassId($item->getTaxClassKey())
        );
        $rate = $this->calculationTool->getRate($taxRateRequest);
        $storeRate = $storeRate = $this->calculationTool->getStoreRate($taxRateRequest, $this->storeId);

        // Calculate $priceInclTax
        $applyTaxAfterDiscount = $this->config->applyTaxAfterDiscount($this->storeId);
        $priceInclTax = $this->calculationTool->round($item->getUnitPrice());
        if (!$this->isSameRateAsStore($rate, $storeRate)) {
            $priceInclTax = $this->calculatePriceInclTax($priceInclTax, $storeRate, $rate, $round);
        }
        $uniTax = $this->calculationTool->calcTaxAmount($priceInclTax, $rate, true, false);
        $deltaRoundingType = self::KEY_REGULAR_DELTA_ROUNDING;
        if ($applyTaxAfterDiscount) {
            $deltaRoundingType = self::KEY_TAX_BEFORE_DISCOUNT_DELTA_ROUNDING;
        }
        $uniTax = $this->roundAmount($uniTax, $rate, true, $deltaRoundingType, $round, $item);
        $price = $priceInclTax - $uniTax;

        //Handle discount
        $discountTaxCompensationAmount = 0;
        $discountAmount = $item->getDiscountAmount();
        if ($applyTaxAfterDiscount) {
            $unitDiscountAmount = $discountAmount / $quantity;
            $taxableAmount = max($priceInclTax - $unitDiscountAmount, 0);
            $unitTaxAfterDiscount = $this->calculationTool->calcTaxAmount(
                $taxableAmount,
                $rate,
                true,
                false
            );
            $unitTaxAfterDiscount = $this->roundAmount(
                $unitTaxAfterDiscount,
                $rate,
                true,
                self::KEY_REGULAR_DELTA_ROUNDING,
                $round,
                $item
            );

            // Set discount tax compensation
            $unitDiscountTaxCompensationAmount = $uniTax - $unitTaxAfterDiscount;
            $discountTaxCompensationAmount = $unitDiscountTaxCompensationAmount * $quantity;
            $uniTax = $unitTaxAfterDiscount;
        }
        $rowTax = $uniTax * $quantity;

        // Calculate applied taxes
        /**
 * @var  \Magento\Tax\Api\Data\AppliedTaxInterface[] $appliedTaxes
*/
        $appliedRates = $this->calculationTool->getAppliedRates($taxRateRequest);
        $appliedTaxes = $this->getAppliedTaxes($rowTax, $rate, $appliedRates);

        return $this->taxDetailsItemDataObjectFactory->create()
            ->setCode($item->getCode())
            ->setType($item->getType())
            ->setRowTax($rowTax)
            ->setPrice($price)
            ->setPriceInclTax($priceInclTax)
            ->setRowTotal($price * $quantity)
            ->setRowTotalInclTax($priceInclTax * $quantity)
            ->setDiscountTaxCompensationAmount($discountTaxCompensationAmount)
            ->setAssociatedItemCode($item->getAssociatedItemCode())
            ->setTaxPercent($rate)
            ->setAppliedTaxes($appliedTaxes);
    }

    /**
     * {@inheritdoc}
     */
    // @codingStandardsIgnoreStart
    protected function calculateWithTaxNotInPrice(QuoteDetailsItemInterface $item, $quantity, $round = true)
    {
        $itemTaxes  = $this->getVertexItemTaxes();
        if (!isset($itemTaxes)) {
            $itemTax = new \Magento\Framework\DataObject();
        } else {
            //var_dump(array_keys($itemTaxes));exit;
            $giftWrappingEnabled = $this->moduleManager->isEnabled('Magento_GiftWrapping');
            if ($giftWrappingEnabled) {
                $quoteGwCode = \Magento\GiftWrapping\Model\Total\Quote\Tax\Giftwrapping::CODE_QUOTE_GW;
                $printedCardCode = \Magento\GiftWrapping\Model\Total\Quote\Tax\Giftwrapping::CODE_PRINTED_CARD;
                $itemGwType  = \Magento\GiftWrapping\Model\Total\Quote\Tax\Giftwrapping::ITEM_TYPE;
            } else {
                $quoteGwCode = false;
                $printedCardCode = false;
                $itemGwType  = false;
            }
            $key = $this->getItemKey($item);
            $itemTax = new \Magento\Framework\DataObject();
            if ($item->getType() == \Magento\Tax\Model\Sales\Total\Quote\Subtotal::ITEM_TYPE_PRODUCT && isset($itemTaxes[$key])) {
                $itemTax = $itemTaxes[$key];
            } elseif ($item->getType() == \Magento\Tax\Model\Sales\Total\Quote\Subtotal::ITEM_TYPE_SHIPPING && isset($itemTaxes[\VertexSMB\Tax\Helper\Data::VERTEX_SHIPPING_LINE_ITEM_ID])) {
                $itemTax = $itemTaxes[\VertexSMB\Tax\Helper\Data::VERTEX_SHIPPING_LINE_ITEM_ID];
            } elseif ($item->getType() == $itemGwType && isset($itemTaxes[$key])) {
                $itemTax = $itemTaxes[$key];
            } elseif ($item->getType() == $printedCardCode && isset($itemTaxes[$printedCardCode])) {
                $itemTax = $itemTaxes[$printedCardCode];
            } elseif ($item->getType() == $quoteGwCode && isset($itemTaxes[$quoteGwCode])) {
                $itemTax = $itemTaxes[$quoteGwCode];
            }
        }

        /*$taxRateRequest = $this->getAddressRateRequest()->setProductClassId(
            $this->taxClassManagement->getTaxClassId($item->getTaxClassKey())
        );*/
        $rate = $itemTax->getTaxRate();//$this->calculationTool->getRate($taxRateRequest);
        $appliedRates = [$itemTax];//$this->calculationTool->getAppliedRates($taxRateRequest);

        $applyTaxAfterDiscount = $this->config->applyTaxAfterDiscount($this->storeId);
        $discountAmount = $item->getDiscountAmount();
        $discountTaxCompensationAmount = 0;

        // Calculate $price
        $price = $this->calculationTool->round($item->getUnitPrice());
        $unitTaxes = [];
        $unitTaxesBeforeDiscount = [];
        $appliedTaxes = [];
        //Apply each tax rate separately
        foreach ($appliedRates as $appliedRate) {
            //$taxId = $appliedRate['id'];
            $taxRate = $appliedRate['percent'];
            $unitTaxPerRate = $appliedRate->getTaxAmount();//$this->calculationTool->calcTaxAmount($price, $taxRate, false, false);
            $deltaRoundingType = self::KEY_REGULAR_DELTA_ROUNDING;
            if ($applyTaxAfterDiscount) {
                $deltaRoundingType = self::KEY_TAX_BEFORE_DISCOUNT_DELTA_ROUNDING;
            }
            //$unitTaxPerRate = $this->roundAmount($unitTaxPerRate, $taxId, false, $deltaRoundingType, $round, $item);
            $unitTaxAfterDiscount = $unitTaxPerRate;

            //Handle discount
            if ($applyTaxAfterDiscount) {
                $unitDiscountAmount = $discountAmount / $quantity;
                $taxableAmount = max($price - $unitDiscountAmount, 0);
                /*$unitTaxAfterDiscount = $this->calculationTool->calcTaxAmount(
                    $taxableAmount,
                    $taxRate,
                    false,
                    false
                );*/
                /*$unitTaxAfterDiscount = $this->roundAmount(
                    $unitTaxAfterDiscount,
                    $taxId,
                    false,
                    self::KEY_REGULAR_DELTA_ROUNDING,
                    $round,
                    $item
                );*/
            }
            /*$appliedTaxes[$taxId] = $this->getAppliedTax(
                $unitTaxAfterDiscount * $quantity,
                $appliedRate
            );*/

            $unitTaxes[] = $unitTaxAfterDiscount;
            $unitTaxesBeforeDiscount[] = $unitTaxPerRate;
        }
        $unitTax = array_sum($unitTaxes);
        $unitTaxBeforeDiscount = array_sum($unitTaxesBeforeDiscount);

        $rowTax = $unitTax; //* $quantity;
        $priceInclTax = $price + $unitTaxBeforeDiscount;
        return $this->taxDetailsItemDataObjectFactory->create()
            ->setCode($item->getCode())
            ->setType($item->getType())
            ->setRowTax($rowTax)
            ->setPrice($price)
            ->setPriceInclTax($priceInclTax)
            ->setRowTotal($price * $quantity)
            ->setRowTotalInclTax($priceInclTax * $quantity)
            ->setDiscountTaxCompensationAmount($discountTaxCompensationAmount)
            ->setAssociatedItemCode($item->getAssociatedItemCode())
            ->setTaxPercent($rate)
            ->setAppliedTaxes([]);
    }

    public function getVertexItemTaxes()
    {
        return $this->registry->registry(\VertexSMB\Tax\Helper\Data::VERTEX_LINE_ITEM_TAX_KEY);
    }

    public function getItemKey($item)
    {
        return $this->registry->registry(\VertexSMB\Tax\Helper\Data::VERTEX_QUOTE_ITEM_ID_PREFIX.$item->getCode());
    }
}
