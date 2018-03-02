<?php

class CSH_Cshgroupedproduct_Model_Product_Price
extends Mage_Catalog_Model_Product_Type_Price {

    /**
     * Returns product final price depending on options chosen
     *
     * @param   double $qty
     * @param   Mage_Catalog_Model_Product $product
     * @return  double
     */
    public function getFinalPrice($qty=null, $product)
    {
        if (is_null($qty) && !is_null($product->getCalculatedFinalPrice())) {
            return $product->getCalculatedFinalPrice();
        }

        $finalPrice = parent::getFinalPrice($qty, $product);
        if ($product->hasCustomOptions()) {
            /* @var $typeInstance Mage_Catalog_Model_Product_Type_Grouped */
            $typeInstance = $product->getTypeInstance(true);
            $associatedProducts = $typeInstance->setStoreFilter($product->getStore(), $product)
                ->getCshgroupedProducts($product);

            foreach ($associatedProducts as $childProduct) {
                /* @var $childProduct Mage_Catalog_Model_Product */
                $option = $product->getCustomOption('associated_product_' . $childProduct->getId());
                if (!$option) {
                    continue;
                }
                $childQty = $option->getValue();
                if (!$childQty) {
                    continue;
                }
                $finalPrice += $childProduct->getFinalPrice($childQty) * $childQty;
            }
        }

        $product->setFinalPrice($finalPrice);
        Mage::dispatchEvent('catalog_product_type_cshgrouped_price', array('product' => $product));

        return max(0, $product->getData('final_price'));
    }
}
