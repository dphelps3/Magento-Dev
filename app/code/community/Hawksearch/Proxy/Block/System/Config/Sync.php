<?php
/**
 * Copyright (c) 2013 Hawksearch (www.hawksearch.com) - All Rights Reserved
 *
 * THIS CODE AND INFORMATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF ANY
 * KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND/OR FITNESS FOR A
 * PARTICULAR PURPOSE.
 */
 

class Hawksearch_Proxy_Block_System_Config_Sync
	extends Mage_Adminhtml_Block_System_Config_Form_Field {

    protected $_buttonId = "sync_categories_button";

    /**
     * Programmatically include the generate feed javascript in the adminhtml JS block.
     *
     * @return <type>
     */
	 
    protected function _prepareLayout() {
        $block = $this->getLayout()->createBlock("hawksearch_proxy/system_config_sync_js");
        $block->setData("button_id", $this->_buttonId);
        
        $this->getLayout()->getBlock('js')->append($block);
        return parent::_prepareLayout();
    }

    /**
     * Return element html
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        $button = $this->getButtonHtml();

        $notice = "";
        if ($this->_syncIsLocked()) {
            $notice = "<p id='hawksearch_display_msg' class='note'>" . Mage::helper('hawksearch_proxy')->getAjaxNotice()."</p>";
        }
        return $button.$notice;
    }

    /**
     * Generate button html for the feed button
     *
     * @return string
     */
    public function getButtonHtml() {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'id' => $this->_buttonId,
                'label' => $this->helper('hawksearch_proxy')->__('Synchronize Categories'),
                'onclick' => 'hawkSearchProxy.syncCategories(); return false;'
            ));

		$force = '';
        if ($this->_syncIsLocked()) {
			$button->setDisabled(true);
			$force =  ' <input type="checkbox" value="syncforce" id="syncforce" name="syncforce" onclick="hawkSearchProxy.forceSync();"><label for="syncforce">force</label>';
        }

        return $button->toHtml() . $force;
    }

    /**
     * Check to see if there are any locks for any feeds
     *
     * @return boolean
     */
    protected function _syncIsLocked() {
        return Mage::helper('hawksearch_proxy')->isSyncLocked();
    }

}
