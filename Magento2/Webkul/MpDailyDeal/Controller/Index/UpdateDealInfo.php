<?php
namespace Webkul\MpDailyDeal\Controller\Index;

/**
 * Webkul_MpDailyDeal UpdateDealInfo controller.
 * @category  Webkul
 * @package   Webkul_MpDailyDeal
 * @author    Webkul
 * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

use Magento\Framework\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Webkul\MpDailyDeal\Helper\Data as MpDailyDealHelperData;

class UpdateDealInfo extends Action\Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var MpDailyDealHelperData
     */
    protected $mpDailyDealsHelperData;

    /**
     * @param Action\Context $context,
     * @param JsonFactory $resultJsonFactory,
     * @param ProductRepositoryInterface $productRepository,
     * @param MpDailyDealHelperData $mpDailyDealHelperData
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        ProductRepositoryInterface $productRepository,
        MpDailyDealHelperData $mpDailyDealHelperData
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productRepository = $productRepository;
        $this->mpDailyDealHelperData = $mpDailyDealHelperData;
        parent::__construct($context);
    }

    /**
     * update deal detail
     *
     * @return JsonFactory
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $data = $this->getRequest()->getPostValue();
        $result = ['status'=> 0];
        if ($data['deal-id']) {
            $product = $this->productRepository->getById($data['deal-id'], true);
            $dealDetail = $this->mpDailyDealHelperData->getProductDealDetail($product);
            $result = ['status'=> 1];
        }
        return $resultJson->setData($result);
    }
}
