<?php

class Simplesolutions_Groupedproduct_Block_Product_View_Type_Grouped
extends Mage_Catalog_Block_Product_View_Abstract
{
    public function getGroupedProducts()
    {
        return $this->getProduct()->getTypeInstance(true)->getGroupedProducts($this->getProduct());
    }

    public function getAssociatedProductIds()
    {
        return $this->getProduct()->getTypeInstance(true)->getAssociatedProductIds($this->getProduct());
    }

    /**
     *  Set preconfigured values to grouped associated products
     *
     * @return_Mage_Catalog_Block_Product_View_Type_Grouped
     */
    public function setPreconfiguredValue()
    {
        $configValues = $this->getProduct()->getPreconfiguredValues()->getSuperGroup();

        if (is_array($configValues))
        {
            $associatedProducts = $this->getAssociatedProducts();

            foreach ($associatedProducts as $item)
            {
                if (isset($configValues[$item->getId()]))
                {
                    $item->setQty($configValues[$item->getId()]);
                }
            }
        }
        return $this;
    }
}