<?php
class Simplesolutions_Customshipping_Model_Sales_Order extends Mage_Sales_Model_Order{
	public function getShippingDescription(){
		$desc = parent::getShippingDescription();
		$customshippingObject = $this->getCustomshippingObject();
		if($customshippingObject){
			$desc .= '<br/><b>Store</b>: '.$customshippingObject->getStore();
			$desc .= '<br/><b>Name</b>: '.$customshippingObject->getName();
			$desc .= '<br/>';
		}
		return $desc;
	}
}