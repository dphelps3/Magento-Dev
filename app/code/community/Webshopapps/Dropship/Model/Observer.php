<?php

/**
 * @category   Webshopapps
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */
/**
 * Magento Webshopapps Module
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Webshopapps
 * @package    Webshopapps_Dropship
 * @copyright  Copyright (c) 2012 Zowta Ltd (http://www.webshopapps.com)
 * @license    www.webshopapps.com/license/license.txt
 * @author     Karen Baker <sales@webshopapps.com>
*/

class Webshopapps_Dropship_Model_Observer extends Mage_Core_Model_Abstract
{	
	/**
	 * event checkout_type_onepage_save_order_after
	 */
	public function saveOrderAfter($observer){
    	try {
		
			if (!Mage::getStoreConfig('carriers/dropship/active')) {
	        	return;
	    	}
			$eventName = $observer->getEvent()->getName();
			$eventName == 'sales_order_place_after' ? $orderStore = $observer->getEvent()->getOrder()->getStore() : $orderStore = $observer->getEvent()->getInvoice()->getStore();		  
		    $createEmail = Mage::getStoreConfig('carriers/dropship/shipment_email',$orderStore);
	
			if (!Mage::getStoreConfig('carriers/dropship/active',$orderStore)) {
	    	 	return ;
	    	}
	    	 
	    	 if(($createEmail == 'order' && $eventName == 'sales_order_place_after') || ($createEmail == 'invoice' && $eventName == 'sales_order_invoice_save_after')){
			 	if(!Mage::helper('dropship/data')->isCreateShipmentEmail($orderStore)){
	    	 		return;
	    	 	}
	    	 	    	    	 
		    	 // Send emails to all the warehouses involved
	    		 Mage::helper('dropship/email')->salesOrderSaveAfter($observer);
	    	 } 
    	} catch (Exception $e) {
   			Mage::logException($e);
    	}
	}
		
	
    public function salesConvertQuoteItemToOrderItem($observer) {
    	
    	try {
    		if (!Mage::getStoreConfig('carriers/dropship/active')) {
	        	return;
	    	}
	    	$quoteItem = $observer->getEvent()->getItem();
	    	$orderItem = $observer->getEvent()->getOrderItem();
	    	
    		$warehouseChanged = false;
    		//TODO get working for Dropship Nearest Warehouse
    		$warehouse = Mage::Helper('dropship/shipcalculate')->determineWhichWarehouse($quoteItem,
    					'','','',$warehouseChanged);
    		if ($warehouseChanged) {
    			$orderItem->setWarehouse($warehouse);
    			$quoteItem->setWarehouse($warehouse);
    		}
    	} catch (Exception $e) {
   			Mage::logException($e);
    	}
    	
    }
    
    
	/**
	 * event checkout_type_onepage_save_order_after
	 */
	public function saveCartBefore($observer){
		
		if (!Mage::getStoreConfig('carriers/dropship/active')) {
        	return;
    	}
		$eventName = $observer->getEvent()->getName();
		$cart = $observer->getEvent()->getCart();
		$cart->getQuote()->getShippingAddress()->setWarehouse('');
		
		
	}
	
}