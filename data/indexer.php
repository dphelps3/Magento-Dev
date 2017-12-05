<?php
if ( isset($_GET['key'])) {
	$key = $_GET['key'];
	if ($key == 'cshadmin') {
		if ( isset($_GET['sku'])) {
			$sku = $_GET['sku'];
			
			try {
				/* Instantiate MAGE */
				require_once('../app/Mage.php'); //Path to Magento
				umask(0);
				Mage::app();
				
				/* Load product */
				$_product = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);
				
				/* Do save */
				$_product->save();
				
				/* Do reindex */
				$_product->setForceReindexRequired(true);
				Mage::getSingleton('index/indexer')->processEntityAction($_product,Mage_Catalog_Model_Product::ENTITY,Mage_Index_Model_Event::TYPE_SAVE);
				
				/* Return complete */
				echo '1';
			} catch ( Exception $e ) {
				/* Return error */
				echo '0';
			}
		}
	}	
}
?>