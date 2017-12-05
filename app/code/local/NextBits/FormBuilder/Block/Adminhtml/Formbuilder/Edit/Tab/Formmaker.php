<?php
class NextBits_FormBuilder_Block_Adminhtml_Formbuilder_Edit_Tab_Formmaker
	extends Mage_Adminhtml_Block_Widget
{
	
	protected $_itemCount = 1;

	public function __construct()
    {
        parent::__construct();
        $this->setTemplate('formbuilder/edit/options.phtml');
    }
	
	 public function getItemCount()
    {
        return $this->_itemCount;
    }

	 public function setItemCount($itemCount)
    {
        $this->_itemCount = max($this->_itemCount, $itemCount);
        return $this;
    }

	
    protected function _prepareLayout()
    {
        $this->setChild('add_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('formbuilder')->__('Add New Fieldset'),
                    'class' => 'add',
                    'id'    => 'add_new_form_option'
                ))
        );
		
		$this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('formbuilder')->__('Delete Fieldset'),
                    'class' => 'delete delete-product-option '
                ))
        );
		
		$this->setChild('delete_field_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('formbuilder')->__('Delete Field'),
                    'class' => 'delete delete-field-option'
                ))
        );
		
		$this->setChild('add_field_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('formbuilder')->__('Add Field'),
                    'class' => 'add add-product-option',
					'id'=>'add_field_form',
					'onclick'=>'addNewField();'
                ))
        );
	  $path = 'global/nextbits/formbuilder/options/custom/groups';
        foreach (Mage::getConfig()->getNode($path)->children() as $group) {
			$renderer =Mage::getConfig()->getNode($path . '/' . $group->getName() . '/render');
			$this->setChild($group->getName() . '_option_type',
                $this->getLayout()->createBlock(
                    (string)$renderer
                )
            );
        }
        return parent::_prepareLayout();
    }

    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }
	
	 public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }
	public function getFieldDeleteButtonHtml()
	{
		 return $this->getChildHtml('delete_field_button');
	}
	public function getAddNewField()
    {
        return $this->getChildHtml('add_field_button');
    }
	
	public function getRequireSelectHtml()
    {
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $this->getFormFieldId().'_{{id}}_is_require',
                'class' => 'select'
            ))
            ->setName($this->getFormFieldName().'[{{fieldset_id}}][{{id}}][is_require]')
            ->setOptions(Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray());

        return $select->getHtml();
    }
	public function getRequireStatusSelectHtml()
    {
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $this->getFormFieldId().'_{{id}}_status',
                'class' => 'select'
            ))
            ->setName($this->getFormFieldName().'[{{fieldset_id}}][{{id}}][status]')
            ->setOptions(Mage::getSingleton('adminhtml/system_config_source_enabledisable')->toOptionArray());

        return $select->getHtml();
    }
	public function getRequireSelectFieldsetHtml()
    {
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $this->getFieldId().'_{{id}}_is_status',
                'class' => 'select'
            ))
            ->setName($this->getFieldName().'[{{id}}][is_status]')
            ->setOptions(Mage::getSingleton('adminhtml/system_config_source_enabledisable')->toOptionArray());

        return $select->getHtml();
    }
	public function getFieldId()
    {
        return 'formbuilder_fieldset_option';
    }
	public function getFormFieldId()
    {
        return 'product_option';
    }
	public function getFormFieldName()
    {
        return 'product[options]';
    }
	 public function getFieldName()
    {
        return 'formbuilder[fieldset]';
    }
	public function getValidatorHtml(){
	
	 $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $this->getFormFieldId().'_{{id}}_validator_class',
                'class' => 'select'
            ))
            ->setName($this->getFormFieldName().'[{{fieldset_id}}][{{id}}][validator_class]')
            ->setOptions(Mage::getSingleton('formbuilder/formfields')->toOptionArray());

        return $select->getHtml();
	}
	public function getTypeSelectHtml()
    {
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $this->getFormFieldId().'_{{id}}_type',
				'rel'=>'{{id}}',
                'class' => 'select select-product-option-type required-option-select',
            ))
			->setExtraParams('onchange="checktype(this)"')
            ->setName($this->getFormFieldName().'[{{fieldset_id}}][{{id}}][type]')
            ->setOptions(Mage::getSingleton('formbuilder/system_config_source_product_options_type')->toOptionArray());

        return $select->getHtml();
    }
	
	
	
	public function isReadonly()
    {
         return false;
    }
	
	public function getAddButtonId()
    {
       $buttonId='add_field_form';
        return $buttonId;
    }
	
	 public function getTemplatesHtml()
    {
        $canEditPrice = false;
        $canReadPrice = false;
        $this->getChild('select_option_type')
            ->setCanReadPrice($canReadPrice)
            ->setCanEditPrice($canEditPrice);
 
        $this->getChild('file_option_type')
            ->setCanReadPrice($canReadPrice)
            ->setCanEditPrice($canEditPrice); 

        $this->getChild('date_option_type')
            ->setCanReadPrice($canReadPrice)
            ->setCanEditPrice($canEditPrice);
 
        $this->getChild('text_option_type')
            ->setCanReadPrice($canReadPrice)
            ->setCanEditPrice($canEditPrice); 

        $templates = $this->getChildHtml('text_option_type') . "\n" .
            $this->getChildHtml('file_option_type') . "\n" .
            $this->getChildHtml('select_option_type') . "\n" .
			$this->getChildHtml('html_option_type') . "\n" .
            $this->getChildHtml('date_option_type');

        return $templates;
    }
	public function getOptionValues()
    {
		if(Mage::registry('form_data')){
			$formFieldData= Mage::registry('form_data')->getFormFieldData();
			$data=array_reverse($formFieldData,true);
			$max =0;
			$values = array();
			foreach ($formFieldData as $formField) {
				$value = array();
				$value['id']=$formField['id'];
				$value['option_id']=$formField['id'];
				$value['title']=$formField['title'];
				$value['sort_order']=$formField['sort_order'];
				$value['is_status']=$formField['is_status'];
				//$value['is_status'] = $formField['sort_order'];
				if($max < $formField['id'])
				{
					$max = $formField['id'];
				}
				$value['item_count'] = $max;
				$values[] = new Varien_Object($value);
			}
			/* echo "<prE>";
			print_r($values);
			exit; */
			return $values;
		}
	}
	public function setFieldValue($id)
	{
		$values =array();
		if(Mage::registry('form_data')){
			$formFieldData= Mage::registry('form_data')->getFieldsData();
			/* echo "<pre>";
			print_r($formFieldData);
			exit; */
			foreach($formFieldData as $key=>$value)
			{
				if($key == $id)
				{
					$temp =$value;
					break;
				}
			}
			
			foreach ($temp as $formField) {
				$this->setItemCount($formField['field_id']);
				$value = array();
				$value['id']=$formField['field_id'];
				$value['fieldset_id']=$formField['fieldset_id'];
				$value['is_require']=$formField['is_require'];
				$value['fieldset']=$formField['fieldset_id'];
				$value['option_id']=$formField['field_id'];
				$value['type']=$formField['type'];
				$value['title']=$formField['title'];
				$value['sort_order']=$formField['sort_order'];
				$value['status']=$formField['status'];
				$value['sku'] = $formField['sku'];
				$value['max_characters'] = $formField['max_characters'];
				$value['item_count'] = $this->getItemCount();
				$value['file_extension'] = $formField['file_extension'];
                $value['image_size_x'] = $formField['image_size_x'];
                $value['image_size_y'] = $formField['image_size_y'];
				$value['validator_class'] = $formField['validator_class'];
				$value['class'] = $formField['class'];
				$value['html_content'] =$formField['html'];
				 if($value['type'] =='drop_down' || $value['type'] =='radio' || $value['type'] =='checkbox' || $value['type'] =='multiple')
				 {
					$optionModel =Mage::getModel('formbuilder/formbuilderoption')->getCollection();
					$optionModel->addFieldToFilter('field_id',$value['id']);
					$optionData =$optionModel->getData();
					$optionvalues=array();
					$itemCount = 0;
					foreach($optionData as $optionkey=>$optionvalue)
					{
						$temp =array();
						$temp['item_count']=max($itemCount, $optionvalue['option_id']);
						$temp['option_id']=$id.'_'.$optionvalue['field_id'];
						$temp['option_type_id']=$optionvalue['option_id'];
						$temp['title']=$optionvalue['title'];
						$temp['sku']=$optionvalue['sku'];
						$temp['sort_order']=$optionvalue['sort_order'];
						if($optionvalue['default'] ==1)
						$temp['checked']='checked';
						$optionvalues[]=$temp;
					}
					//$optionvalues['default']=array(30);
					$value['optionValues']=$optionvalues;
				 }
				 
				$values[] = new Varien_Object($value);
			}
		}
	  
		return  $values;
	}
		
}  
?>
