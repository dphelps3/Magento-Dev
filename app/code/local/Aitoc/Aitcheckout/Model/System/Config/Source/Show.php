<?php

class Aitoc_Aitcheckout_Model_System_Config_Source_Show
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Aitoc_Aitcheckout_Helper_Data::IS_SHOW_CHECKOUT_IN_CART,
                'label' => Mage::helper('aitcheckout')->__('Move Checkout to Cart')
            ),
            array(
                'value' => Aitoc_Aitcheckout_Helper_Data::IS_SHOW_CHECKOUT_OUTSIDE_CART,
                'label' => Mage::helper('aitcheckout')->__('Expand Checkout steps')
            ),
            array(
                'value' => Aitoc_Aitcheckout_Helper_Data::IS_SHOW_CART_IN_CHECKOUT,
                'label' => Mage::helper('aitcheckout')->__('Move Cart to Checkout')
            ),
            array(
                'value' => Aitoc_Aitcheckout_Helper_Data::IS_DISABLED,
                'label' => Mage::helper('aitcheckout')->__('Turn Off the extension')
            )
        );
    }

}
