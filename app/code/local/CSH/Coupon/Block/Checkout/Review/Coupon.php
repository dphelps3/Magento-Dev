<?php
/**
 * Sitesquad - Custom Coupon functionality
 *
 * =============================================================================
 * NOTE: See README.txt for more information about this extension
 * =============================================================================
 *
 * @category   CSH
 * @package    CSH_Coupon
 * @copyright  Copyright (c) 2015 Sitesquad. (http://www.sitesquad.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Phil Mobley <phil.mobley@sitesquad.net>
 */

class CSH_Coupon_Block_Checkout_Review_Coupon
    extends Mage_Checkout_Block_Cart_Coupon
{
    /**
     * Checks if the functionality is enabled
     *
     * @todo: add system config to turn on/off this functionality
     *
     * @return boolean
     */
    public function getIsEnabled()
    {
        return true;
    }
    
    
    /**
     * Gets the URL for the form post
     *
     * @return string
     */
    public function getActionUrl()
    {
        return $this->getUrl('cshcoupon/onepage/apply', array('_secure'=>true));
    }
}
