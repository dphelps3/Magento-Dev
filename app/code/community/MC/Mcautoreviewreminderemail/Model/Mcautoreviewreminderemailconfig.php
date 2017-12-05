<?php

/*
 * @category    Community
 * @package     MC_Mcautoreviewreminderemail
 * @Document    Mcautoreviewreminderemailconfig.php
 * @Created on  April 11, 2012, 7:05 PM
 * @copyright   Copyright (c) 2012 Magento Complete
 */

class MC_Mcautoreviewreminderemail_Model_Mcautoreviewreminderemailconfig extends Mage_Core_Model_Config_Data
{


/*
	* Params : Server Config Varables
	* Funtion: When Curl function invoke
	*/
    public function _curlValidation() 
	{
		if(in_array('curl', get_loaded_extensions())):
			return true;
		else:
			return false;
		endif;
    }
	
	/*
	* Params : Server Config Varables
	* Funtion: When Fopen function invoke
	*/
	
	 public function _fopenValidation() 
	{
		if(ini_get('allow_url_fopen')):
			return true;
		else:
			return false;
		endif;
    }

    // checking for enabling extension license key is valid or not
	
    protected function _beforeSave()
    {
		$config_all_fields_value = $this->getFieldset_data();
		if($config_all_fields_value['mcautoreviewreminderemail_enable'] == 1){
			$licensekey = $this->getValue();
			$arr = parse_url(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB));
		    $domain = $arr['host'];
		    $store_path = $arr['host'].$arr['path'];
			$product = "Auto_Review_Reminder_Emailer";
			$validUrl = 'http://irzoo.com/Extensions-Data/getinfo.php?storepath='.$store_path;
			  if (empty($licensekey)):
	              throw new Exception('Please Enter Valid License Key');endif;
		
			$license_approval = '';
			if ($this->_curlValidation()):
			   $license_approval = $this->_curlApproval($validUrl,$licensekey,$domain,$product);
            elseif($this->_fopenValidation()):
			      $license_approval = $this->_fopenApproval($validUrl,$licensekey,$domain,$product);
			else:
			throw new Exception('Either Curl PHP Library or Allow_url_fopen setting must be Enalbled.');
			endif;
			
			if (!empty($licensekey) && $license_approval != 1):
			     throw new Exception('Please Enter Valid License Key');endif;
			return $this;
		}
    }
	
	
	/*
	* Params : All Package Activation attribute
	* Funtion: When Package Activation invoke
	*/
	
	public function _curlApproval($validUrl,$licensekey,$domain,$product)
   {
	   $_curlUrl = '';
	   $_curlUrl = $validUrl."&license=".$licensekey."&domain=".$domain."&product=".$product;
		   if(isset($_curlUrl) && $_curlUrl != ''):
		    $ch = curl_init($_curlUrl);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$response 	= curl_exec($ch);
			$response = (int)$response;
			curl_close($ch);
		   endif;
		   return $response;
   }
   
   	/*
	* Params : All Package Activation attribute
	* Funtion: When Package Activation invoke
	*/
   
    public function _fopenApproval($validUrl,$licensekey,$domain,$product)
    {
        $response = file_get_contents($validUrl."&license=".$licensekey."&domain=".$domain."&product=".$product);
		$response = (int)$response;
        return $response;
    }   
	
	
	
}
