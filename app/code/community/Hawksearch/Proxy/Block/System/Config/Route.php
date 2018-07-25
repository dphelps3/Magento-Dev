<?php

class Hawksearch_Proxy_Block_System_Config_Route extends Mage_Adminhtml_Block_System_Config_Form_Field {

    protected function _prepareLayout() {
        $block = $this->getLayout()->createBlock("hawksearch_proxy/system_config_route_js");

        $this->getLayout()->getBlock('js')->append($block);
        return parent::_prepareLayout();
    }
}