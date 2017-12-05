<?php
class NextBits_FormBuilder_Block_Adminhtml_FormBuilder_Renderer_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row)
	{
		$urlModel = Mage::getModel('core/url');
		$href = $urlModel->getUrl('formbuilder', array('_current'=>false,'id'=>$row->getId()));
		return '<a href="'.$href.'" target="_blank">'.$this->__('Preview').'</a>';
	}
}
  
?>
