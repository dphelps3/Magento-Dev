<?php

/**
 * @category   Webshopapps
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */
class Webshopapps_Dropship_Block_Adminhtml_Shipmethods_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function _construct()
    {
        parent::_construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'dropship';
        $this->_controller = 'adminhtml_shipmethods';
        
        $this->_updateButton('save', 'label', Mage::helper('dropship')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('dropship')->__('Delete'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('shipmethods_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'shipmethods_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'shipmethods_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('shipmethods_data') && Mage::registry('shipmethods_data')->getId() ) {
            return Mage::helper('dropship')->__("Edit '%s'", $this->htmlEscape(Mage::registry('shipmethods_data')->getTitle()));
        } else {
            return Mage::helper('dropship')->__('Add');
        }
    }
}