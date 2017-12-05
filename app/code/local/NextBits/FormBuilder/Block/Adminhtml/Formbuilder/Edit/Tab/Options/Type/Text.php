<?php
class  NextBits_FormBuilder_Block_Adminhtml_Formbuilder_Edit_Tab_Options_Type_Text extends
    NextBits_FormBuilder_Block_Adminhtml_Formbuilder_Edit_Tab_Options_Type_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('formbuilder/edit/options/type/text.phtml');
    }

}
