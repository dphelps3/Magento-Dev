<?php

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
 * @copyright  Copyright (c) 2010 Zowta Ltd (http://www.webshopapps.com)
 * @license    www.webshopapps.com/license/license.txt
 * @author     Karen Baker <sales@webshopapps.com>
*/

/**
 * @category   Webshopapps
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */
class Webshopapps_Dropship_Helper_Email extends Mage_Core_Helper_Abstract
{

	const XML_DROPSHIP_PATH_EMAIL_TEMPLATE               = 'sales_email/dropship_order/template';
    const XML_DROPSHIP_PATH_EMAIL_IDENTITY               = 'sales_email/dropship_order/identity';
    const XML_DROPSHIP_PATH_EMAIL_COPY_TO                = 'sales_email/dropship_order/copy_to';
    const XML_DROPSHIP_PATH_EMAIL_COPY_METHOD            = 'sales_email/dropship_order/copy_method';
    const XML_DROPSHIP_PATH_EMAIL_ENABLED                = 'sales_email/dropship_order/enabled';

	const XML_DROPSHIP_PATH_UPDATE_EMAIL_TEMPLATE              	= 'sales_email/dropship_order_comment/template';
	const XML_DROPSHIP_PATH_UPDATE_EMAIL_IDENTITY               = 'sales_email/dropship_order_comment/identity';
	const XML_DROPSHIP_PATH_UPDATE_EMAIL_COPY_TO                = 'sales_email/dropship_order_comment/copy_to';
	const XML_DROPSHIP_PATH_UPDATE_EMAIL_COPY_METHOD            = 'sales_email/dropship_order_comment/copy_method';
	const XML_DROPSHIP_PATH_UPDATE_EMAIL_ENABLED                = 'sales_email/dropship_order_comment/enabled';

	private $_order;
	private $_storeId;
	private $_orderId;

	/**
	 * sales_order_save_after
	 */
	public function salesOrderSaveAfter($observer){
		$eventName = $observer->getEvent()->getName();
		if ($eventName == 'sales_order_place_after') {
			$order = $observer->getEvent()->getOrder();
		} else {
			$orderId = $observer->getEvent()->getInvoice()->getOrder()->getIncrementId();
			$order = $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
		}
		$this->setOrder($order);
		$sendEmail = Mage::getStoreConfig(self::XML_DROPSHIP_PATH_EMAIL_ENABLED, $order->getStore());
        // create a new shipment for each vendor
        $items = $order->getItemsCollection();

        // get the warehouses in the order
        $encDetails = $order->getWarehouseShippingDetails();

        if (!empty($encDetails)) {

 			$shipDetails = Mage::helper('dropship')->decodeShippingDetails($encDetails);
	         foreach ($shipDetails as $shipItem) {
	        	$warehouse = $shipItem['warehouse'];
	         	if (!isset($warehouse)){
        			$warehouse = Mage::getStoreConfig('carriers/dropship/default_warehouse');
        		}

	        	// create shipments for each warehouse
				$shippingDescription = $shipItem['carrierTitle'].' - '.$shipItem['methodTitle'];
				$emailDetails = $this->getEmailDetails($warehouse);

				if(!$emailDetails['manualship']){
	        		$shipment = $this->createShipment($order,$warehouse,$shippingDescription);
	        		if($order->getManualShip() != 1){
	        		    $order->setManualShip(2);
	        		}
				} else {
				    $order->setManualShip(1);
				}

	        	if ($sendEmail && !$emailDetails['manualship']) {
	        		$this->sendFullShipmentEmail($warehouse,$shipment,$order);
	        	}
	        }
        } else {
        	// else must just be one warehouse or it's merged rates
        	// create shipment for whole order in pending status

        	$warehouse=-1;
        	$warehouses = array();
        	foreach ($items as $item) {
        		$warehouse = Mage::helper('dropship/shipcalculate')->determineWhichWarehouse($item,'','','');
        		if(!in_array($warehouse, $warehouses)) {
        			array_push($warehouses, $warehouse);
        		}
        	}

        	foreach ($warehouses as $warehouse)
        	{
        		if (!isset($warehouse)){
        			$warehouse = Mage::getStoreConfig('carriers/dropship/default_warehouse');
        		}

        		$emailDetails = $this->getEmailDetails($warehouse);

        		if(!$emailDetails['manualship']){
        			$shipment = $this->createShipment($order,$warehouse,$order->getShippingDescription());
        			if($order->getManualShip() != 1){
	        		    $order->setManualShip(2);
	        		}
				} else {
				    $order->setManualShip(1);
				}

         		//$shipment = $this->createShipment($order,$warehouse,$order->getShippingDescription());
        		// send new shipment email to warehouse
        		if ($sendEmail && !$emailDetails['manualship']) {
        			$this->sendFullShipmentEmail($warehouse,$shipment,$order);
        		}

        	}
        }
	}


 	/**
     * Declare order for shipment
     *
     * @param   Mage_Sales_Model_Order $order
     * @return  Mage_Sales_Model_Order_Shipment
     */
    private function setOrder(Mage_Sales_Model_Order $order)
    {
    	$this->_order = $order;
        $this->setOrderId($order->getId());
        $this->setStoreId($order->getStoreId());
    }

 	/**
     * Retrieve the order the shipment for created for
     *
     * @return Mage_Sales_Model_Order
     */
    private function getOrder()
    {
        return $this->_order;
    }

    private function getStoreId() {
    	return $this->_storeId;
    }

   	private function setStoreId($storeId) {
    	$this->_storeId = $storeId;
    }

 	private function getOrderId() {
    	return $this->_orderId;
    }

   	private function setOrderId($orderId) {
    	$this->_orderId = $orderId;
    }




	//TODO default locale email

	private function _initShipment($order,$warehouse) {
		if (!$order->canShip()) {
            return false;
        }
        $shipment = $this->prepareShipment($warehouse);
		$shipment->setDropshipStatus(Webshopapps_Dropship_Model_Source_ShipStatus::DROPSHIP_SHIPSTATUS_PENDING);
		$shipment->setWarehouse($warehouse);

        return $shipment;
	}

    /**
     * Prepare order shipment based on order items and requested items qty
     *
     * @param array $data
     * @return Mage_Sales_Model_Order_Shipment
     */
    private function prepareShipment($warehouse, $qtys = array())
    {
        $totalQty = 0;
        $shipment = Mage::getModel('sales/convert_order')->toShipment($this->_order);
        $itemIdsAdded = array();

        foreach ($this->_order->getAllItems() as $orderItem) {
            if (!$this->_canShipItem($orderItem, $qtys)) {
                continue;
            }
        	$itemWarehouse = Mage::helper('dropship/shipcalculate')->determineWhichWarehouse($orderItem,'','','');
        	if ($warehouse!=$itemWarehouse) {
        		continue;
        	}

        	$emailDetails = $this->getEmailDetails($warehouse);
        	if($emailDetails['manualship']){
        		continue;
        	}

            $item = Mage::getModel('sales/convert_order')->itemToShipmentItem($orderItem);
            $itemIdsAdded[] = $orderItem->getId();

            if($orderItem->getParentItem()){
                if(!in_array($orderItem->getParentItemId(), $itemIdsAdded)){
                    $itemIdsAdded[] = $orderItem->getParentItemId();
                    $sitem = Mage::getModel('sales/convert_order')->itemToShipmentItem($orderItem->getParentItem());
                    $sitem->setQty(1);
                    $totalQty += 0;
                    $shipment->addItem($sitem);
               }
            }

            if ($orderItem->isDummy()) {
                $qty = 0;
            } else {
                if (isset($qtys[$orderItem->getId()])) {
                    $qty = min($qtys[$orderItem->getId()], $orderItem->getQtyToShip());
                } elseif (!count($qtys)) {
                    $qty = $orderItem->getQtyToShip();
                } else {
                    continue;
                }
            }

            $totalQty += $qty;
            $item->setQty($qty);
           // $orderItem->setLockedDoShip(true);
            $orderItem->save();
            $shipment->addItem($item);
        }
        $shipment->setTotalQty($totalQty);
        return $shipment;
    }

 /**
     * Check if order item can be shiped. Dummy item can be shiped or with his childrens or
     * with parent item which is included to shipment
     *
     * @param Mage_Sales_Model_Order_Item $item
     * @param array $qtys
     * @return bool
     */
    protected function _canShipItem($item, $qtys=array())
    {
        if ($item->getIsVirtual() || $item->getLockedDoShip()) {
            return false;
        }
        if ($item->isDummy(true)) {
            if ($item->getHasChildren()) {
                if ($item->isShipSeparately()) {
                    return true;
                }
                foreach ($item->getChildrenItems() as $child) {
                    if ($child->getIsVirtual()) {
                        continue;
                    }
                    if (empty($qtys)) {
                        if ($child->getQtyToShip() > 0) {
                            return true;
                        }
                    } else {
                        if (isset($qtys[$child->getId()]) && $qtys[$child->getId()] > 0) {
                            return true;
                        }
                    }
                }
                return false;
            } else if($item->getParentItem()) {
                $parent = $item->getParentItem();
                if (empty($qtys)) {
                    return $parent->getQtyToShip() > 0;
                } else {
                    return isset($qtys[$parent->getId()]) && $qtys[$parent->getId()] > 0;
                }
            }
        } else {
            return $item->getQtyToShip()>0;
        }
    }

    /**
     * Save shipment and order in one transaction
     * @param Mage_Sales_Model_Order_Shipment $shipment
     */
    private function _saveShipment($shipment)
    {
       // $shipment->getOrder()->setIsInProcess(true);
        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($shipment)
            ->addObject($shipment->getOrder())
            ->save();

        return $this;
    }

	// sendOrderNotificationEmail Model/Vendor.php
	// updateOrderItemsVendors Data.php multi extn

	private function createShipment($order,$warehouse,$shippingDescription)
	{
	 	try {
            if ($shipment = $this->_initShipment($order,$warehouse)) {
                //$shipment->register(); This is done in shipmentController.php
                $shipment->setShippingDescription($shippingDescription);
                $this->_saveShipment($shipment);
            } else {
                //TODO $this->_forward('noRoute');
            }
        } catch (Mage_Core_Exception $e) {
           Mage::log($e->getMessage());

        } catch (Exception $e) {
            Mage::log('Cannot save shipment.');
         //  throw ($e); // never throw exception
        }
        return $shipment;
	}

	private function _getEmails($configPath)
    {
        $data = Mage::getStoreConfig($configPath, $this->getStoreId());
        if (!empty($data)) {
            return explode(',', $data);
        }
        return false;
    }

 //   public function sendNewShipmentEmail(Mage_Sales_Model_Order_Shipment $shipment) {
    public function sendNewShipmentEmail($shipmentId) {
    	$shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);

    	if ($shipment==NULL) {
    		return;
    	}
    	$warehouse = $shipment->getWarehouse();
    	if ($warehouse!=NULL) {
    		$this->sendFullShipmentEmail($warehouse,$shipment,$shipment->getOrder(),true);
    	}
    }

	/**
     * Sending email with Invoice data
     *
     * @return Mage_Sales_Model_Order_Invoice
     */
    private function sendFullShipmentEmail($warehouse,$shipment,$order,  $overrideManual = false, $comment='')
    {

    	$warehouseContactDetails = $this->getEmailDetails($warehouse);
        if ($warehouseContactDetails['email']=="" || (!$overrideManual && $warehouseContactDetails['manualmail']) ){
        	return $this;
        }

        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);
        $mailTemplate = Mage::getModel('core/email_template');

        $copyTo = $this->_getEmails(self::XML_DROPSHIP_PATH_EMAIL_COPY_TO);
        $copyMethod = Mage::getStoreConfig(self::XML_DROPSHIP_PATH_EMAIL_COPY_METHOD, $this->getStoreId());

        if ($copyTo && $copyMethod == 'bcc') {
            foreach ($copyTo as $email) {
                $mailTemplate->addBcc($email);
            }
        }

    /*    $paymentBlock   = Mage::helper('payment')->getInfoBlock($order->getPayment())
            ->setIsSecureMode(true);
        $paymentBlock->getMethod()->setStore($order->getStore()->getId());*/

        $template = Mage::getStoreConfig(self::XML_DROPSHIP_PATH_EMAIL_TEMPLATE, $this->getStoreId());
        $sendTo = array();
        $emailAdd = preg_split('/,/', $warehouseContactDetails['email']);

        foreach ($emailAdd as $email) {

        	 $sendTo[] = array (
                'email' => $email,
            	'name'  => $warehouseContactDetails['contact']
            );
        }

        if ($copyTo && ($copyMethod == 'copy')) {
            foreach ($copyTo as $email) {
                $sendTo[] = array(
                    'name'  => null,
                    'email' => $email
                );
            }
        }

        foreach ($sendTo as $recipient) {

            $mailTemplate->setDesignConfig(array('area'=>'frontend', 'store'=>$order->getStoreId()))
                ->sendTransactional(
                    $template,
                    Mage::getStoreConfig(self::XML_DROPSHIP_PATH_EMAIL_IDENTITY, $order->getStoreId()),
                    $recipient['email'],
                    $recipient['name'],
                    array(
                        'order'       => $order,
                        'shipment'    => $shipment,
                        'comment'     => $comment,
                        'billing'     => $order->getBillingAddress(),
                    	'warehouse'	  => $warehouse,
                    	'warename'	  => $recipient['name']
                      //  'payment_html'=> $paymentBlock->toHtml(),
                    )
                );
        }

        $translate->setTranslateInline(true);

        return $this;
    }


  	private function getEmailDetails($warehouse) {
  		$warehouseDetails = Mage::getModel('dropship/dropship')->load($warehouse);
    	$emailDetails=array(
    		'email' 		=> $warehouseDetails->getEmail(),
    		'contact' 		=> $warehouseDetails->getContact(),
    		'manualmail' 	=> $warehouseDetails->getManualmail(),
    		'manualship' 	=> $warehouseDetails->getManualship(),
    	);
    	return $emailDetails;
    }


}