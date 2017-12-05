<?php

class Brim_Groupedconfigured_Helper_Data extends Mage_Core_Helper_Abstract
{
	
	public function getAssociatedConfigurableProductHtml($view, $product){
		return $view->getLayout()->createBlock('catalog/product_view_type_configurable')
		->setTemplate('groupedconfigured/product/view/type/groupedconfigured/configurable.phtml')
		->setProduct($product)
		->toHtml();
	}

	/**
	 * The logic contained in Mage_Catalog_Block_Product_View.getProductViewJsonConfig() has been extracted into this method
	 * to be used from within the configurable template.
	 *
	 */
	public function getProductViewJsonConfig($product){
		$config = array();
		if (!$product->getTypeInstance(true)->hasOptions($product)) {
			return Mage::helper('core')->jsonEncode($config);
		}

		$_request = Mage::getSingleton('tax/calculation')->getRateRequest(false, false, false);
		$_request->setProductClassId($product->getTaxClassId());
		$defaultTax = Mage::getSingleton('tax/calculation')->getRate($_request);

		$_request = Mage::getSingleton('tax/calculation')->getRateRequest();
		$_request->setProductClassId($product->getTaxClassId());
		$currentTax = Mage::getSingleton('tax/calculation')->getRate($_request);

		$_regularPrice = $product->getPrice();
		$_finalPrice = $product->getFinalPrice();
		$_priceInclTax = Mage::helper('tax')->getPrice($product, $_finalPrice, true);
		$_priceExclTax = Mage::helper('tax')->getPrice($product, $_finalPrice);

		$config = array(
            'productId'           => $product->getId(),
            'priceFormat'         => Mage::app()->getLocale()->getJsPriceFormat(),
            'includeTax'          => Mage::helper('tax')->priceIncludesTax() ? 'true' : 'false',
            'showIncludeTax'      => Mage::helper('tax')->displayPriceIncludingTax(),
            'showBothPrices'      => Mage::helper('tax')->displayBothPrices(),
            'productPrice'        => Mage::helper('core')->currency($_finalPrice, false, false),
            'productOldPrice'     => Mage::helper('core')->currency($_regularPrice, false, false),
            'skipCalculate'       => ($_priceExclTax != $_priceInclTax ? 0 : 1),
            'defaultTax'          => $defaultTax,
            'currentTax'          => $currentTax,
            'idSuffix'            => '_clone',
            'oldPlusDisposition'  => 0,
            'plusDisposition'     => 0,
            'oldMinusDisposition' => 0,
            'minusDisposition'    => 0,
		);

		$responseObject = new Varien_Object();
		if (is_array($responseObject->getAdditionalOptions())) {
			foreach ($responseObject->getAdditionalOptions() as $option=>$value) {
				$config[$option] = $value;
			}
		}

		return Mage::helper('core')->jsonEncode($config);
	}

	/**
	 * Since it's a bad practice to overwrite core magento abstract classes via
	 * copying the source in the local folder structure, this method contains the
	 * same code found in Mage_Catalog_Model_Product_Type_Abstract.getSku; however,
	 * it also includes logic to allow certain options to be disregarding from
	 * sku concatination.
	 * 
	 * NOTE: Since we're providing the same logic here that is in the base abstract
	 * product type class, it is very important to check this logic during future
	 * magento upgrades to ensure nothing gets broken.
	 * 
	 * @param Mage_Catalog_Model_Product $product
     * @return string
	 */
	public function getProductSku($product){
		$excludeSkus = explode(',', Mage::getConfig()->getNode(self::XML_PATH_EXCLUDE_OPTIONS_SKU));
		$skuDelimiter = '-';
		$sku = $product->getData('sku');
		if ($optionIds = $product->getCustomOption('option_ids')) {
			foreach (explode(',', $optionIds->getValue()) as $optionId) {
				if ($option = $product->getOptionById($optionId)) {

					// pass over options with a title contained in the config
					if(in_array($option->getTitle(), $excludeSkus)){
						continue;
					}
					
					$quoteItemOption = $product->getCustomOption('option_'.$optionId);

					$group = $option->groupFactory($option->getType())
					->setOption($option)->setListener(new Varien_Object());

					if ($optionSku = $group->getOptionSku($quoteItemOption->getValue(), $skuDelimiter)) {
						$sku .= $skuDelimiter . $optionSku;
					}

					if ($group->getListener()->getHasError()) {
						$product
						->setHasError(true)
						->setMessage(
						$group->getListener()->getMessage()
						);
					}

				}
			}
		}
		return $sku;
	}
}