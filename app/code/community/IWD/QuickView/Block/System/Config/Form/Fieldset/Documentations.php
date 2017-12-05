<?php
class IWD_QuickView_Block_System_Config_Form_Fieldset_Documentations extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return '<span class="notice"><a href="https://docs.google.com/document/d/1EDLuuh1WyTDAnY0VTaswQ7hGhzpJZLea-HxatQwrXaQ/" target="_blank">User Guide</span>';
    }
}