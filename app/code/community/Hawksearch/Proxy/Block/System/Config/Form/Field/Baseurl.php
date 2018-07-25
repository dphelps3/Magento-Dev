<?php

/**
 * Copyright (c) 2013 Hawksearch (www.hawksearch.com) - All Rights Reserved
 *
 * THIS CODE AND INFORMATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF ANY
 * KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND/OR FITNESS FOR A
 * PARTICULAR PURPOSE.
 */
class Hawksearch_Proxy_Block_System_Config_Form_Field_Baseurl extends Mage_Adminhtml_Block_System_Config_Form_Field {
	public function getElementHtml() {
		return Mage::getStoreConfig('web/unsecure/base_url') . 'hawkproxy/';
	}
	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
		return $this->getElementHtml();
	}

} 