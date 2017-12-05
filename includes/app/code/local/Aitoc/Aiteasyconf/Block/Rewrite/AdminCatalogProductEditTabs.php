<?php

/* @copyright  Copyright (c) 2012 AITOC, Inc. */
class Aitoc_Aiteasyconf_Block_Rewrite_AdminCatalogProductEditTabs extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($this->shouldShowTab()) {
            $this->addTab(
                'easyconf', array(
                'label' => Mage::helper('aiteasyconf')->__('Configurable Products Pro'),
                'url' => $this->getUrl('adminhtml/aiteasyconf_index/ajax', array('_current' => true)),
                'class' => 'ajax'
                )
            );

            $this->getLayout()->getBlock('head')->addCss('aitoc/aiteasyconf.css');
            $this->getLayout()->getBlock('head')->addJs('aitoc/aiteasyconf.js');
            $this->getLayout()->getBlock('head')->addJs('varien/form.js');
        }
    }

    private function shouldShowTab()
    {
        return (($this->getRequest()->getRequestedActionName() != 'new')
            && ($this->getProduct()->isConfigurable())
            && ($this->getRequest()->getParam('id')));
    }
}
