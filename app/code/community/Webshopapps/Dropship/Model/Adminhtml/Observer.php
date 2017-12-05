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

class Webshopapps_Dropship_Model_Adminhtml_Observer extends Mage_Core_Model_Abstract
{
    public function onBlockHtmlBefore(Varien_Event_Observer $observer) {
        $block = $observer->getBlock();
        if (!isset($block)) return;

        switch ($block->getType()) {
            case 'adminhtml/sales_shipment_grid':
            case 'adminhtml/sales_order_view_tab_shipments':
                /* block Adminhtml_Sales_Order_View_Tab_Shipments */
            	/* @var $block Mage_Adminhtml_Sales_Shipment_Grid */
            	/* Note addColumnAfter doesnt seem to be working here */
                $block->addColumnAfter('dropship_status', array(
		            'header' => Mage::helper('sales')->__('Status'),
		            'index' => 'dropship_status',
		            'type' => 'options',
		            'options' => Mage::getSingleton('dropship/source_shipStatus')->toOptionArray(),
		        ),'total_qty');
                break;
            case 'adminhtml/sales_order_shipment_view':
                /* block Mage_Adminhtml_Block_Sales_Order_Shipment_View */
            	$shipment = $block->getShipment();
                if (($id = $shipment->getId())) {
	                $url = $block->getUrl('dropship/adminhtml_sales_shipment/emailware', array(
	                    'shipment_id'=>$id,
	                    'order_id'=> $shipment->getOrderId()
	                ));
	                $block->addButton('emailware', array(
		                'label'     => Mage::helper('dropship')->__('Email Warehouse'),
		                'class'     => 'emailware',
		                'onclick'   => "setLocation('$url')"
		            ));
	                if($shipment->getDropshipStatus() == Webshopapps_Dropship_Model_Source_ShipStatus::DROPSHIP_SHIPSTATUS_PENDING){
    	                $url = $block->getUrl('dropship/adminhtml_sales_shipment/ship', array(
    	                        'shipment_id'=>$id,
    	                        'order_id'=> $shipment->getOrderId()
    	                ));
        	            $block->addButton('ship', array(
        	                    'label'     => Mage::helper('dropship')->__('Ship'),
        	                    'class'     => 'ship',
        	                    'onclick'   => "setLocation('$url')"
        	            ));
	                }
                }
                break;
            case 'adminhtml/sales_order_view':
                if($block->getOrder()->getManualShip() > 1) {
                    $block->removeButton('order_ship');
                }
                break;
        }
    }

   public function onSalesOrderShipmentGridLoadBefore(Varien_Event_Observer $observer) {
        $collection = $observer->getOrderShipmentGridCollection();
        if (!isset($collection)) return;
        $collection->addFieldToSelect('dropship_status');
        $collection->addFieldToSelect('entity_id');
        $collection->addFieldToSelect('created_at');
        $collection->addFieldToSelect('increment_id');
        $collection->addFieldToSelect('total_qty');
        $collection->addFieldToSelect('shipping_name');
        $collection->addFieldToSelect('order_increment_id');
        $collection->addFieldToSelect('order_created_at');
    }

}