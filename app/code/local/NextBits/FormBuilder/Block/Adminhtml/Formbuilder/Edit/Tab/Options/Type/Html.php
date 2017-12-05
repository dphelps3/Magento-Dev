<?php
class NextBits_FormBuilder_Block_Adminhtml_Formbuilder_Edit_Tab_Options_Type_Html extends
    NextBits_FormBuilder_Block_Adminhtml_Formbuilder_Edit_Tab_Options_Type_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('formbuilder/edit/options/type/html.phtml');
    }

}
