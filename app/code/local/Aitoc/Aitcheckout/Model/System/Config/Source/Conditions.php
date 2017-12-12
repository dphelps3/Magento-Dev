<?php

class Aitoc_Aitcheckout_Model_System_Config_Source_Conditions
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
                'value' => Aitoc_Aitcheckout_Helper_Data::CONDITIONS_DEFAULT,
                'label' => Mage::helper('aitcheckout')->__('Text Area')
            ),
            array(
                'value' => Aitoc_Aitcheckout_Helper_Data::CONDITIONS_POPUP,
                'label' => Mage::helper('aitcheckout')->__('Pop-up Window')
            ),
        );
    }

}
