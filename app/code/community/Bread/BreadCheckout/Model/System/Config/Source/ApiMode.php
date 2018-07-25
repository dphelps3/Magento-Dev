<?php
/**
 * API Mode Options
 *
 * @author  Bread   copyright   2016
 * @author  Joel    @Mediotype
 */
class Bread_BreadCheckout_Model_System_Config_Source_ApiMode
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label'=>Mage::helper('adminhtml')->__('No')),
            array('value' => 0, 'label'=>Mage::helper('adminhtml')->__('Yes')),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            0 => Mage::helper('adminhtml')->__('Sandbox'),
            1 => Mage::helper('adminhtml')->__('Live'),
        );
    }

}
