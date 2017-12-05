<?php
/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Sociallogin
 * @module      Sociallogin
 * @author      Magestore Developer
 *
 * @copyright   Copyright (c) 2016 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 *
 */

class Magestore_Sociallogin_Block_Adminhtml_Twlogin_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Magestore_Sociallogin_Block_Adminhtml_Twlogin_Edit constructor.
     */
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'sociallogin';
        $this->_controller = 'adminhtml_twlogin';
        
        $this->_updateButton('save', 'label', Mage::helper('sociallogin')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('sociallogin')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('twlogin_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'twlogin_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'twlogin_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    /**
     * @return string
     */
    public function getHeaderText()
    {
        if( Mage::registry('twlogin_data') && Mage::registry('twlogin_data')->getId() ) {
            return Mage::helper('sociallogin')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('twlogin_data')->getTitle()));
        } else {
            return Mage::helper('sociallogin')->__('Add Item');
        }
    }
}