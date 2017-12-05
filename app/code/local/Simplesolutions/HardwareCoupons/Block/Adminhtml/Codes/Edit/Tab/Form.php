<?php
class Simplesolutions_HardwareCoupons_Block_Adminhtml_Codes_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
		protected function _prepareForm()
		{

				$form = new Varien_Data_Form();
				$this->setForm($form);

				//get categories -- Kitchen
				$cats = '<table>';
				$cat = Mage::getModel('catalog/category')->load(5109);
				$parentCategoryId = '5109';
				$cats_test = array();
				$Tcategories = Mage::getModel('catalog/category')
					->getCollection()
					->addFieldToFilter('parent_id', array('eq'=>$parentCategoryId))
					->addFieldToFilter('is_active',array("in"=>array('0', '1')))
					->addAttributeToSelect('*');
			 	//Mage::log(($Tcategories->getData(), null, 'aaron_new_cats.log');
				$children = $cat->getChildren();
				$data = Mage::registry("codes_data");
				//Mage::log($data, null, 'aaron_cats.log');
			 	//Mage::log(($children, null, 'aaron_cats.log');
				$cab_ids = explode(',',$data->_data['hardware_ids']);
			 	//Mage::log(($cab_ids, null, 'aaron_cats.log');
				$counter = 1;
				//foreach(explode(',',$children) as $subCatid)
				foreach($Tcategories->getData() as $subCatid)
				{
					$_category = Mage::getModel('catalog/category')->load($subCatid['entity_id']);
					//if($_category->getIsActive())
					//{
						$catname     = $_category->getName();
						if(in_array($subCatid['entity_id'], $cab_ids)) {
							$cats .= '<tr><td><input type="checkbox" checked="checked" value="'.$subCatid['entity_id'].'" name="cat_id['.$counter.']" /></td><td><label>'.$catname.'</label></td>';
						} else {
							$cats .= '<tr><td><input type="checkbox" value="'.$subCatid['entity_id'].'" name="cat_id['.$counter.']" /></td><td><label>'.$catname.'</label></td>';
						}
					//}
					$counter = $counter + 1;
				}
				$cats .= '</table>';

				

				$fieldset = $form->addFieldset("hardwarecoupons_form", array("legend"=>Mage::helper("hardwarecoupons")->__("Item information")));


						$fieldset->addField("name", "text", array(
						"label" => Mage::helper("hardwarecoupons")->__("Name"),
						"class" => "required-entry",
						"required" => true,
						"name" => "name",
						));

						$dateFormatIso = Mage::app()->getLocale()->getDateTimeFormat(
							Mage_Core_Model_Locale::FORMAT_TYPE_SHORT
						);

						$fieldset->addField('start_date', 'date', array(
						'label'        => Mage::helper('hardwarecoupons')->__('Start Date'),
						'name'         => 'start_date',
						'time' => true,
						'image'        => $this->getSkinUrl('images/grid-cal.gif'),
						'format'       => $dateFormatIso
						));
						$dateFormatIso = Mage::app()->getLocale()->getDateTimeFormat(
							Mage_Core_Model_Locale::FORMAT_TYPE_SHORT
						);

						$fieldset->addField('end_date', 'date', array(
						'label'        => Mage::helper('hardwarecoupons')->__('End Date'),
						'name'         => 'end_date',
						'time' => true,
						'image'        => $this->getSkinUrl('images/grid-cal.gif'),
						'format'       => $dateFormatIso
						));
						$fieldset->addField("hardware_ids", "hidden", array(
						"label" => Mage::helper("hardwarecoupons")->__("Hardware Line Ids"),
						"name" => "hardware_ids",
						'after_element_html' => '<table><tr><td>Decorative Hardware</td></tr><tr><td>'.$cats.'</td></tr></table>'
						));


				if (Mage::getSingleton("adminhtml/session")->getCodesData())
				{
					$form->setValues(Mage::getSingleton("adminhtml/session")->getCodesData());
					Mage::getSingleton("adminhtml/session")->setCodesData(null);
				}
				elseif(Mage::registry("codes_data")) {
				    $form->setValues(Mage::registry("codes_data")->getData());
				}
				return parent::_prepareForm();
		}
}
