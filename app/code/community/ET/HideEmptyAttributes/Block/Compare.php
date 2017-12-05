<?php
/**
 * NOTICE OF LICENSE
 *
 * You may not sell, sub-license, rent or lease
 * any portion of the Software or Documentation to anyone.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @category   ET
 * @package    ET_HideEmptyAttributes
 * @copyright  Copyright (c) 2012 ET Web Solutions (http://etwebsolutions.com)
 * @contacts   support@etwebsolutions.com
 * @license    http://shop.etwebsolutions.com/etws-license-free-v1/   ETWS Free License (EFL1)
 */

class ET_HideEmptyAttributes_Block_Compare extends Mage_Catalog_Block_Product_Compare_List
{
    // 1 - Module enabled
    // 0 - Module disabled
    protected $_state;

    protected function _construct()
    {
        $this->_state = Mage::getStoreConfig('ethideemptyattributes/general/poweroptions');
    }

    public function getProductAttributeValue($product, $attribute)
    {
        if ($this->_state) {
            if (!$product->hasData($attribute->getAttributeCode())) {
                return Mage::helper('ethideemptyattributes')->getEmptyAttributeText();
            }

            if ($attribute->getSourceModel()
                || in_array($attribute->getFrontendInput(), array('select', 'boolean', 'multiselect'))
            ) {
                if (!$product->getAttributeText($attribute->getAttributeCode())) {
                    $value = "";
                } else {
                    $value = $attribute->getFrontend()->getValue($product);
                }
            } else {
                $value = $product->getData($attribute->getAttributeCode());
            }
            return
                ((string)trim($value) == '') ? Mage::helper('ethideemptyattributes')->getEmptyAttributeText() : $value;
        } else {
            return parent::getProductAttributeValue($product, $attribute);
        }
    }
}