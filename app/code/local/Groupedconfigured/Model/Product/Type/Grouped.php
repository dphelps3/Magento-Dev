<?php

class Brim_Groupedconfigured_Model_Product_Type_Grouped extends Mage_Catalog_Model_Product_Type_Grouped
{
    
	/**
	 * Overriding this method to remove the 'required option' filter. We do this because required options are filtered
	 * and we want to be able to associate simple products with required options to grouped products.
	 */
	public function getAssociatedProducts($product = null)
	{
		if (!$this->getProduct($product)->hasData($this->_keyAssociatedProducts)) {
			$associatedProducts = array();

			if (!Mage::app()->getStore()->isAdmin()) {
				$this->setSaleableStatus($product);
			}

			$collection = $this->getAssociatedProductCollection($product)
			->addAttributeToSelect('*')
			//->addFilterByRequiredOptions()
			->setPositionOrder()
			->addStoreFilter($this->getStoreFilter($product))
			->addAttributeToFilter('status', array('in' => $this->getStatusFilters($product)));

			foreach ($collection as $item) {
				$associatedProducts[] = $item;
			}

			$this->getProduct($product)->setData($this->_keyAssociatedProducts, $associatedProducts);
		}
		return $this->getProduct($product)->getData($this->_keyAssociatedProducts);
	}
}