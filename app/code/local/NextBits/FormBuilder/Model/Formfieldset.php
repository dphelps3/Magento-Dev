<?php
class NextBits_FormBuilder_Model_Formfieldset
	extends Mage_Core_Model_Abstract
{
	const STATUS_ENABLED = 1;
	const STATUS_DISABLED = 0;
	
	public function _construct()
	{
		parent::_construct();
		$this->_init('formbuilder/formfieldset');
	}
	
}
?>