<?php
/**
 *
 * @author  Bread   copyright   2016
 */
class Bread_BreadCheckout_Model_Session extends Mage_Checkout_Model_Session
{
    /**
     * Init namespace
     */
    public function __construct()
    {
        $this->init('breadcheckout');
    }
}
