<?php
class Simplesolutions_Customshipping_Block_Customshipping extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
{
	public function __construct(){
		$this->setTemplate('customshipping/customshipping.phtml');		
	}
}