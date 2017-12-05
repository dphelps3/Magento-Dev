<?php

class Simplesolutions_Hardwareproduct_Block_Product_View_Type_Hardware extends Mage_Catalog_Block_Product_View_Abstract
{
	public function getHardwareProducts()
    {
        return $this->getProduct()->getTypeInstance(true)->getHardwareProducts($this->getProduct());
    }

	public function getAssociatedProductIds()
    {
		//Mage::Log('getAssociatedProductIds1');
        return $this->getProduct()->getTypeInstance(true)->getAssociatedProductIds($this->getProduct());
    }

    /**
     * Set preconfigured values to grouped associated products
     *
     * @return Mage_Catalog_Block_Product_View_Type_Grouped
     */
    public function setPreconfiguredValue() {
        $configValues = $this->getProduct()->getPreconfiguredValues()->getSuperGroup();
        if (is_array($configValues)) {
            $associatedProducts = $this->getAssociatedProducts();
            foreach ($associatedProducts as $item) {
                if (isset($configValues[$item->getId()])) {
                    $item->setQty($configValues[$item->getId()]);
                }
            }
        }
        return $this;
    }
}
