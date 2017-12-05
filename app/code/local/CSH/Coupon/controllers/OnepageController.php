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

/**
 * This class does not rewrite the Onepage Controller, but is dependant on
 * some of the functionality
 */
require_once  'Mage/Checkout/controllers/OnepageController.php';
class CSH_Coupon_OnepageController extends Mage_Checkout_OnepageController
{
	/**
     * Adding coupons to onepage checkout
     */
	function applyAction()
	{
	    /* @var $quote Mage_Sales_Model_Quote */
	    $quote = $this->getOnepage()->getQuote();
	    $code  = trim((string) $this->getRequest()->getParam('coupon_code'));
	    
	    if ($code != $quote->getCouponCode()) {
	        if (empty($code)) {
	            $code = '';
	        }
	        
	        $quote->getShippingAddress()->setCollectShippingRates(true);
	        $quote->setCouponCode($code);
	        $quote->collectTotals()->save();
	    }
	    
	    // prepare the layout and the response
	    $this->loadLayout('checkout_onepage_review');
	    
	    $this->getResponse()->setBody(
	        Mage::helper('core')->jsonEncode(
	            array(
	                'goto_section'     => 'review',
	                'update_section'   => array(
            	        'name' => 'review',
            	        'html' => $this->_getReviewHtml()
            	    ),
	            )
	        )
        );
	}
}
