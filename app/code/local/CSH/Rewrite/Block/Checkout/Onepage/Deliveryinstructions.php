<?php
/**
 * Sitesquad - Refactoring modifications to core Magento files
 *
 * =============================================================================
 * NOTE: See README.txt for more information about this extension
 * =============================================================================
 *
 * @category   CSH
 * @package    CSH_Rewrite
 * @copyright  Copyright (c) 2015 Sitesquad. (http://www.sitesquad.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Phil Mobley <phil.mobley@sitesquad.net>
 */

/**
 * NOTE: this file does not exist in Core Magento
 *
 * Moved file from app/code/local/Mage/Checkout/Block/Onepage/deliveryinstructions.php
 */
class CSH_Rewrite_Block_Checkout_Onepage_Deliveryinstructions
    extends Mage_Checkout_Block_Onepage_Abstract
{
	protected function _construct()
	{
		$this->getCheckout()->setStepData(
		    'deliveryinstructions',
			array(
				'label' => Mage::helper('checkout')->__('Delivery Instructions'),
				'is_show' => $this->isShow()
			)
		);

		parent::_construct();
	}
}