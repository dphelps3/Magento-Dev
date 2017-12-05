<?php

class Simplesolutions_Customshipping_Model_Observer extends Varien_Object
{
	public function saveShippingMethod($evt){
		$request = $evt->getRequest();
		$quote = $evt->getQuote();
		$customshipping = $request->getParam('shipping_customshipping',false);
		$quote_id = $quote->getId();
		$data = array($quote_id => $customshipping);
		if($customshipping){
			Mage::getSingleton('checkout/session')->setCustomshipping($data);
		}
		print_r($data);die;
	}
	public function saveOrderAfter($evt){
		$order = $evt->getOrder();
		$quote = $evt->getQuote();
		$quote_id = $quote->getId();
		$customshipping = Mage::getSingleton('checkout/session')->getCustomshipping();
		if(isset($customshipping[$quote_id])){
			$data = $customshipping[$quote_id];
			$data['order_id'] = $order->getId();
			$customshippingModel = Mage::getModel('customshipping/customshipping');
			$customshippingModel->setData($data);
			$customshippingModel->save();
		}
	}
	public function loadOrderAfter($evt){
		$order = $evt->getOrder();
		if($order->getId()){
			$order_id = $order->getId();
			$customshippingCollection = Mage::getModel('customshipping/customshipping')->getCollection();
			$customshippingCollection->addFieldToFilter('order_id',$order_id);
			$customshipping = $customshippingCollection->getFirstItem();
			$order->setCustomshippingObject($customshipping);
		}
	}	
	public function loadQuoteAfter($evt)
	{
		$quote = $evt->getQuote();
		if($quote->getId()){
			$quote_id = $quote->getId();
			$customshipping = Mage::getSingleton('checkout/session')->getCustomshipping();
			if(isset($customshipping[$quote_id])){
				$data = $customshipping[$quote_id];
				$quote->setCustomshippingData($data);
			}
		}
	}
}