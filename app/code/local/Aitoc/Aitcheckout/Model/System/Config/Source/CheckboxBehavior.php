<?php

class Aitoc_Aitcheckout_Model_System_Config_Source_CheckboxBehavior
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
                'value' => Aitoc_Aitcheckout_Helper_Data::CHECKBOX_AVAILABLE_INSTANTLY,
                'label' => Mage::helper('aitcheckout')->__('Mark the checkbox')
            ),
            array(
                'value' => Aitoc_Aitcheckout_Helper_Data::CHECKBOX_VIEWING_REQUIRED,
                'label' => Mage::helper('aitcheckout')->__('Display a pop-up window')
            ),
        );
    }

}
