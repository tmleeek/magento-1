<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Stripe
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Stripe\Api\Data;

/**
 * Stripe StripeCustomer interface.
 *
 * @api
 */
interface StripeCustomerInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID = 'entity_id';
    /**#@-*/

    /**
     * Get ID.
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return \Webkul\Stripe\Api\Data\ReasonsInterface
     */
    public function setId($id);
}
