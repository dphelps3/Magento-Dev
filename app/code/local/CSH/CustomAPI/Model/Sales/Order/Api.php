<?php
/**
 *	Custom Service Hardware
 *	Custom API Extension
 *	@author		David Dzimianski
 *	@package	CSH_CustomAPI
 *	@copyright	Copyright (c) 2015 Custom Service Hardware, Inc.
 *	
 *	This module extends the Sales Order Info API call to include custom order
 *	fields, such as comments, liftgate, etc. It creates these as line items
 *	with sku /WF to integrate with the setup in Sage MAS 100 ERP. These line
 *	items should be imported into Sage with the name set as CommentText on the
 *	Sage sales order line item.
 */
class CSH_CustomAPI_Model_Sales_Order_Api extends Mage_Sales_Model_Order_Api
{
	/**
     * Retrieve full order information
     *
     * @param string $orderIncrementId
     * @return array
     */
    public function info($orderIncrementId)
    {
        $order = $this->_initOrder($orderIncrementId);
        if ($order->getGiftMessageId() > 0) {
            $order->setGiftMessage(
                Mage::getSingleton('giftmessage/message')->load($order->getGiftMessageId())->getMessage()
            );
        }
        $result = $this->_getAttributes($order, 'order');
        $result['shipping_address'] = $this->_getAttributes($order->getShippingAddress(), 'order_address');
        $result['billing_address']  = $this->_getAttributes($order->getBillingAddress(), 'order_address');
        $result['items'] = array();
        foreach ($order->getAllItems() as $item) {
            if ($item->getGiftMessageId() > 0) {
                $item->setGiftMessage(
                    Mage::getSingleton('giftmessage/message')->load($item->getGiftMessageId())->getMessage()
                );
            }
            $result['items'][] = $this->_getAttributes($item, 'order_item');
        }
        $result['payment'] = $this->_getAttributes($order->getPayment(), 'order_payment');
        $result['status_history'] = array();
        foreach ($order->getAllStatusHistory() as $history) {
            $result['status_history'][] = $this->_getAttributes($history, 'order_status_history');
        }
		
		/**
		*	<<< BEGIN CUSTOM CODE >>>
		*/
		try {
			/* Load custom options */
			$model = Mage::getModel('custom/custom_order');
			$custom = $model->getByOrder($this->getOrder()->getId());

			foreach($custom as $key => $value){
				$message = '[empty]';
				
				/* Evaluate custom options */
				switch ($key) {
					case 'csh_comments2':
						$message = $value;
						break;
					case 'csh_liftgate':
						if ($value == 1) {
							$message = '>>>> Liftgate Required <<<<';
						}
						break;
					case 'csh_residential':
						if ($value == 1) {
							$message = '>>>> Residential Delivery <<<<';
						}
						break;
					case 'csh_appointment':
						if ($value == 1) {
							$message = '>>>> Delivery By Appointment <<<<';
						}
						break;
				}
				
				/* If custom options are set, then add as a line item to the order object */
				if ($message != '[empty]') {
					$commentItem = Mage::getModel('sales/order_item')
						->setName($message)
						->setSku('/WF');
					$result['items'][] = $commentItem;
				}				
			}			
		}
		catch(Exception $e){
			Mage::log($e->getMessage(), null, 'csh_apiextension.log');
		}
		
		/**
		*	<<< CLOSE CUSTOM CODE >>>
		*/
        return $result;
    }

}
		