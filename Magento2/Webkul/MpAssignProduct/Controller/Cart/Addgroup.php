<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAssignProduct\Controller\Cart;

use Magento\Framework\Exception\LocalizedException;

class Addgroup extends \Magento\Checkout\Controller\Cart
{
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $itemIds = $this->getRequest()->getParam('order_items', []);
        if (is_array($itemIds)) {
            $items = $this->_objectManager
                            ->create('Magento\Sales\Model\Order\Item')
                            ->getCollection()
                            ->addIdFilter($itemIds)
                            ->load();
            foreach ($items as $item) {
                try {
                    $this->processItem($item);
                } catch (LocalizedException $e) {
                    $msg = $e->getMessage();
                    if ($this->_checkoutSession->getUseNotice(true)) {
                        $this->messageManager->addNotice($msg);
                    } else {
                        $this->messageManager->addError($msg);
                    }
                } catch (\Exception $e) {
                    $msg = 'We can\'t add this item to your shopping cart right now.';
                    $this->messageManager->addException($e, __($msg));
                    $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                    return $this->_goBack();
                }
            }
        }
        return $this->_goBack();
    }

    public function processItem($item)
    {
        $this->cart->addOrderItem($item, 1);
        $this->cart->save();
        $info = $item->getProductOptionByCode('info_buyRequest');
        $this->_eventManager->dispatch(
            'checkout_cart_add_product_complete',
            [
                'product' => $item->getProductId(),
                'info' => $info,
                'request' => $this->getRequest(),
                'response' => $this->getResponse()
            ]
        );
    }
}
