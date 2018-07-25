<?php
/**
 * Helps Integration With Catalog
 *
 * @author  Bread   copyright   2016
 * @author  Joel    @Mediotype
 */
class Bread_BreadCheckout_Helper_Catalog extends Bread_BreadCheckout_Helper_Data
{
    /**
     * Get html param string for default button size in product detail page based on configuration
     *
     * @return string
     */
    public function getDefaultButtonSizeProductDetailHtml()
    {
        if ($this->useDefaultButtonSizeProductDetail()) {
            return 'data-bread-default-size="true"';
        }

        return '';
    }
    
    /**
     * Get html param string for default button size in cart page based on configuration
     *
     * @return string
     */
    public function getDefaultButtonSizeCartHtml()
    {
        if ($this->useDefaultButtonSizeCart()) {
            return 'data-bread-default-size="true"';
        }
        
        return '';
    }
    
    /**
     * Get html param string for default button size on checkout based on configuration
     *
     * @return string
     */
    public function getDefaultButtonSizeCheckoutHtml()
    {
        if ($this->useDefaultButtonSizeCheckout()) {
            return 'data-bread-default-size="true"';
        }
        
        return '';
    }

    /**
     * Get Formatted Product Data Array
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Catalog_Model_Product $baseProduct
     * @param int $qty
     * @param null $lineItemPrice
     * @return array
     */
    public function getProductDataArray(
        Mage_Catalog_Model_Product $product,
        Mage_Catalog_Model_Product $baseProduct = null,
        $qty = 1,
        $lineItemPrice = null
    ) 
    {
        $theProduct     = ($baseProduct == null) ? $product : $baseProduct;
        $skuString      = $this->getSkuString($product, $theProduct);
        $price          = ($lineItemPrice !== null) ?
            $lineItemPrice : ( ( $baseProduct == null ) ? $product->getFinalPrice() : $baseProduct->getFinalPrice() );
        $price          = round($price, 2) * 100;

        $productData = array(
            'name'      => ( $baseProduct == null ) ? $product->getName() : $baseProduct->getName(),
            'price'     => $price,
            'sku'       => ( $baseProduct == null ) ? $skuString : ($baseProduct['sku'].'///'.$skuString),
            'detailUrl' => ( $baseProduct == null ) ? $product->getProductUrl() : $baseProduct->getProductUrl(),
            'quantity'  => $qty,
        );

        $imgSrc = $this->getImgSrc($product);
        if ($imgSrc != null) {
            $productData['imageUrl'] = $imgSrc;
        }

        return $productData;
    }

    /**
     * Return Product SKU Or Formated SKUs for Products With Options
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Catalog_Model_Product $theProduct
     * @return string
     */
    protected function getSkuString(Mage_Catalog_Model_Product $product, Mage_Catalog_Model_Product $theProduct)
    {
        $selectedOptions    = $theProduct->getTypeInstance(true)->getOrderOptions($theProduct);

        if (!array_key_exists('options', $selectedOptions)) {
            return (string) $product->getSku();
        }

        $skuString  = $product->getData('sku');
            foreach ($selectedOptions['options'] as $key => $value) {
                if ($value['option_type'] == 'multiple') {
                    $selectedOptionValues = explode(',', $value['option_value']);
                } else {
                    $selectedOptionValues = array($value['option_value']);
                }

                foreach ($selectedOptionValues as $selectedOptionValue) {
                    $found = false;
                    foreach ($theProduct->getOptions() as $option) {
                        if ($found) {
                            break;
                        }

                        if (!empty($option->getValues())) {
                            if ($option->getTitle() == $value['label']) {
                                foreach ($option->getValues() as $optionValue) {
                                    if ($selectedOptionValue == $optionValue->getOptionTypeId()) {
                                        $skuString = $skuString . '***' . (($optionValue->getData('sku')) ?
                                            $optionValue->getData('sku') : 'id~' . $optionValue->getData('option_id'))
                                            . "===" . $selectedOptionValue;
                                        $found = true;
                                        break;
                                    }
                                }
                            }
                        } else if ($value['label'] == $option['title']) {
                            $skuString = $skuString . '***' . (($optionValue->getData('sku')) ?
                                    $optionValue->getData('sku') : 'id~' . $optionValue->getData('option_id')) . '==='
                                    . $value['option_value'];
                            break;
                        }
                    }
                }
            }

        return $skuString;
    }

    /**
     * Get Img Src Value
     *
     * @param   Mage_Catalog_Model_Product $product
     * @return null|string
     */
    protected function getImgSrc(Mage_Catalog_Model_Product $product)
    {
        if ($this->isInAdmin() == true) {
            return null;
        }

        try {
            return (string) Mage::helper('catalog/image')->init($product, 'thumbnail');
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Check if Bread is enabled in a category
     *
     * @param   Mage_Catalog_Model_Category $category
     * @return boolean
     */
    public function isBreadOnCategory(Mage_Catalog_Model_Category $category)
    {
        $breadCategories = Mage::helper('breadcheckout')->getBreadCategories();
        $categoryIds = explode(',', $breadCategories);
        if(in_array($category->getId(), $categoryIds))
            return true;
        else
            return false;
    }
}
