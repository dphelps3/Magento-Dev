<?php
/**
 * Copyright (c) 2013 Hawksearch (www.hawksearch.com) - All Rights Reserved
 *
 * THIS CODE AND INFORMATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF ANY
 * KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND/OR FITNESS FOR A
 * PARTICULAR PURPOSE.
 */

class Hawksearch_Proxy_Block_System_Config_Form extends Mage_Adminhtml_Block_System_Config_Form {

	protected function _getAdditionalElementTypes()
	{
		$types = parent::_getAdditionalElementTypes();
		$types["version"] = Mage::getConfig()->getBlockClassName('proxy/system_config_form_field_version');
		$types["local_proxy_url"] = Mage::getConfig()->getBlockClassName('proxy/system_config_form_field_baseurl');
		return $types;
	}

}