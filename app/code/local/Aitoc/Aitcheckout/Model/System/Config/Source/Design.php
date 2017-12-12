<?php

class Aitoc_Aitcheckout_Model_System_Config_Source_Design
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
                'value' => Aitoc_Aitcheckout_Helper_Data::DESIGN_DEFAULT,
                'label' => Mage::helper('aitcheckout')->__('Default Design')
            ),
            array(
                'value' => Aitoc_Aitcheckout_Helper_Data::DESIGN_COMPACT,
                'label' => Mage::helper('aitcheckout')->__('Compact v1 Design')
            ),
            array(
                'value' => Aitoc_Aitcheckout_Helper_Data::DESIGN_COMPACT_V2,
                'label' => Mage::helper('aitcheckout')->__('Compact v2 Design')
            ),
        );
    }

}
