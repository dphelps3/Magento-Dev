<?php
class IWD_QuickView_Block_Product extends Mage_Catalog_Block_Product_View
{
	public function __construct(){
		$this->setTemplate('iwd_quickview/catalog/product/view.phtml');
	}

	public function getProduct(){
		return $product = Mage::registry ( 'current_product' );
	}
}