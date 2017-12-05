<?php
class Brim_Groupedconfigured_Block_Adminhtml_Product_Edit_Tab_Super_Group extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Group
{

	/**
	 * Overriding this method to remove the 'required option' filter. We do this because required options are filtered
	 * and we want to be able to associate simple products with required options to grouped products.
	 * (non-PHPdoc)
	 * @see app/code/core/Mage/Adminhtml/Block/Catalog/Product/Edit/Tab/Super/Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Group#_prepareCollection()
	 */
	protected function _prepareCollection()
	{
		$allowProductTypes = array();
		foreach (Mage::getConfig()->getNode('global/catalog/product/type/grouped/allow_product_types')->children() as $type) {
			$allowProductTypes[] = $type->getName();
		}

		$collection = Mage::getModel('catalog/product_link')->useGroupedLinks()
		->getProductCollection()
		->setProduct($this->_getProduct())
		->addAttributeToSelect('*')
		//->addFilterByRequiredOptions()
		->addAttributeToFilter('type_id', $allowProductTypes);

		$this->setCollection($collection);
		return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
	}
}