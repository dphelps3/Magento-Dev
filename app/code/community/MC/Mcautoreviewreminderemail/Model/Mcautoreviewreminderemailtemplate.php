<?php
/*
 * @category    Community
 * @package     MC_Mcautoreviewreminderemail
 * @Document    Mcautoreviewreminderemailconfig.php
 * @Created on  April 11, 2012, 7:05 PM
 * @copyright   Copyright (c) 2012 Magento Complete
 */

class MC_Mcautoreviewreminderemail_Model_Mcautoreviewreminderemailtemplate extends Mage_Core_Model_Config_Data
{
    
    protected function _beforeSave()
    {
		$config_all_fields_value = $this->getFieldset_data();
		if($config_all_fields_value['mcautoreviewreminderemail_enable'] == 1){
			$template = $this->getValue();
			
			if (empty($template) || $template == 'mcautoreviewreminderemail_section_mcautoreviewreminderemail_group_mcautoreviewreminderemail_email_template') {
				throw new Exception('Default template from locale should not be used');
			}
			
			return $this;
		}
    }
	
	
}