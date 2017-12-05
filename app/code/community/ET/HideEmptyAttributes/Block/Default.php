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

class ET_HideEmptyAttributes_Block_Default extends Mage_Catalog_Block_Product_View_Attributes
{

    // 1 - Module enabled
    // 0 - Module disabled
    protected $_state;

    protected function _construct()
    {
        $this->_state = Mage::getStoreConfig('ethideemptyattributes/general/poweroptions');
    }

    public function getAdditionalData(array $excludeAttr = array())
    {
        if ($this->_state) {
            $data = array();
            $product = $this->getProduct();
            $attributes = $product->getAttributes();
            foreach ($attributes as $attribute) {
                if ($attribute->getIsVisibleOnFront() && !in_array($attribute->getAttributeCode(), $excludeAttr)) {
                    $value = $attribute->getFrontend()->getValue($product);

                    if ($attribute->getFrontendInput() == 'price' && is_string($value)) {
                        $value = Mage::app()->getStore()->convertPrice($value, true);
                        // hasData for empty select values returns true. Using getAttributeText instead
                    } else if ($attribute->getFrontendInput() == 'select'
                        && !$product->getAttributeText($attribute->getAttributeCode())
                    ) {
                        $value = "";
                    }

                    if (is_string($value) && strlen(trim($value))) {
                        $data[$attribute->getAttributeCode()] = array(
                            'label' => $attribute->getStoreLabel(),
                            'value' => $value,
                            'code' => $attribute->getAttributeCode()
                        );
                    }
                }
            }
            return $data;

        } else {
            return parent::getAdditionalData($excludeAttr);
        }
    }
}