<?php


/**
 * Used in creating options for Yes|No config value selection
 *
 */
//class Mage_Adminhtml_Model_System_Config_Source_Yesno
class Hawksearch_Proxy_Model_System_Config_Source_Mode
{

	/**
	 * Options getter
	 *
	 * @return array
	 */
	public function toOptionArray()
	{
		return array(
			array('value' => 0, 'label'=>Mage::helper('adminhtml')->__('Development')),
			array('value' => 1, 'label'=>Mage::helper('adminhtml')->__('Production')),
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
			0 => Mage::helper('adminhtml')->__('Development'),
			1 => Mage::helper('adminhtml')->__('Production'),
		);
	}

}
