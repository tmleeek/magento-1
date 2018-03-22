<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 *
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Stripe\Model;

use Webkul\Stripe\Api\Data\StripeCustomerInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Stripe StripeCustomer Model.
 */
class StripeCustomer extends \Magento\Framework\Model\AbstractModel implements StripeCustomerInterface, IdentityInterface
{
    /**
     * No route page id.
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * Stripe StripeCustomer cache tag.
     */
    const CACHE_TAG = 'wkstripe_customer';

    /**
     * @var string
     */
    protected $_cacheTag = 'wkstripe_customer';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'wkstripe_customer';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Webkul\Stripe\Model\ResourceModel\StripeCustomer');
    }

    /**
     * Load object data.
     *
     * @param int|null $id
     * @param string   $field
     *
     * @return $this
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteReasons();
        }

        return parent::load($id, $field);
    }

    /**
     * Load No-Route StripeCustomer.
     *
     * @return \Webkul\Stripe\Model\StripeCustomer
     */
    public function noRouteReasons()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Get identities.
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    /**
     * Get ID.
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return \Webkul\Stripe\Api\Data\StripeCustomerInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}
