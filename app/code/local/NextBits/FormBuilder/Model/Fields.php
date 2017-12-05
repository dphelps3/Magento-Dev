<?php
class NextBits_FormBuilder_Model_Fields extends Mage_Core_Model_Abstract{
	
	public function toOptionArray()
	{
		$formModel = Mage::getModel('formbuilder/formbuilder')->getCollection();
		$final =array();
		if(!empty($formModel)){
			foreach($formModel as $_form){
				$result =array();
				$fieldModel = Mage::getModel('formbuilder/formfields')->getCollection()->addFieldToFilter('form_id',$_form->getId());
				$result['label'] = $_form->getName();
				$option_array = array ();

				foreach ($fieldModel as $form)
					$option_array[] = array
					(
						'value' => $form->getFieldId(),
						'label' => $form->getTitle()
					);
				
				$result['value'] = $option_array;
				$final[] =$result;
			}
		}
		return $final;
	}
}
?>
