<?php

class Hawksearch_Datafeed_Model_System_Config_Source_AttributeSource
{

	/**
	 * Options getter
	 *
	 * @return array
	 */
	public function toOptionArray()
	{
		return array(
			array('value' => 'eav', 'label'=>Mage::helper('hawksearch_datafeed')->__('Eav Tables')),
			array('value' => 'flat', 'label'=>Mage::helper('hawksearch_datafeed')->__('Flat Tables')),
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
            'eav' => Mage::helper('hawksearch_datafeed')->__('Eav Tables'),
            'flat' => Mage::helper('hawksearch_datafeed')->__('Flat Tables'),
		);
	}

}
