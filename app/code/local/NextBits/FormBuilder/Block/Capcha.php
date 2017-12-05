<?php
class NextBits_FormBuilder_Block_Capcha extends Mage_Core_Block_Template
{
	 protected function _toHtml()
    {
        $blockPath = 'formbuilder/capcha_zend';
        $block = $this->getLayout()->createBlock($blockPath);
        $block->setData($this->getData());
        return $block->toHtml();
    }
}
?>