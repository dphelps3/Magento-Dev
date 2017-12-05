<?php

/**
 * @category   Webshopapps
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */
class Webshopapps_Dropship_Block_Adminhtml_Dropship_Edit_Tab_Ups extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldsetUPS = $form->addFieldset('dropship_form', array('legend'=>Mage::helper('dropship')->__('UPS Login Details (leave empty to use defaults)')));

      $fieldsetUPS->addField('ups_user_id', 'text', array(
          'label'     => Mage::helper('dropship')->__('User Id'),
          'required'  => false,
          'name'      => 'ups_user_id',
      ));
      

      $fieldsetUPS->addField('ups_password', 'password', array(
          'label'     => Mage::helper('dropship')->__('Password'),
          'required'  => false,
          'name'      => 'ups_password',
      ));

        $fieldsetUPS->addField('ups_access_license_number', 'text', array(
          'label'     => Mage::helper('dropship')->__('Access License Number'),
          'required'  => false,
          'name'      => 'ups_access_license_number',
      ));

  
        $fieldsetUPS->addField('ups_shipper_number', 'password', array(
          'label'     => Mage::helper('dropship')->__('Shipper Number'),
          'required'  => false,
          'name'      => 'ups_shipper_number',
      ));
      
      $originOptions = Mage::getModel('usa/shipping_carrier_ups_source_originShipment')->toOptionArray();
      $unitOfMeasure = Mage::getModel('usa/shipping_carrier_ups_source_unitofmeasure')->toOptionArray();
      
      array_unshift($originOptions, array('value'=>'', 'label'=>Mage::helper('shipping')->__('USE DEFAULT')));
      array_unshift($unitOfMeasure, array('value'=>'', 'label'=>Mage::helper('shipping')->__('USE DEFAULT')));
      
      if (Mage::registry('dropship_data')->getUpsShippingOrigin()!='') {
      	$allowedMethods = Mage::getModel('usa/shipping_carrier_ups')->getCode('originShipment',Mage::registry('dropship_data')->getUpsShippingOrigin());
      } else {
      	$allowedMethods = array();
      }
      	      	
      $fieldsetUPS->addField('ups_shipping_origin', 'select', array(
          'label'     => Mage::helper('dropship')->__('Origin of the Shipment'),
          'required'  => false,
          'name'      => 'ups_shipping_origin',
          'values'   => $originOptions,
      ));
      
      $fieldsetUPS->addField('ups_unit_of_measure', 'select', array(
          'label'     => Mage::helper('dropship')->__('Weight Unit'),
          'required'  => false,
          'name'      => 'ups_unit_of_measure',
          'values'   => $unitOfMeasure,
      ));
      
      $fieldsetUPS->addField('max_package_weight', 'text', array(
      		'label'     => Mage::helper('dropship')->__('Maximum Package Weight'),
      		'required'  => false,
      		'name'      => 'max_package_weight',
      ));
      
      
      $fieldsetUPS->addField('ups_allowed_methods', 'multiselect', array(
          'label'     => Mage::helper('dropship')->__('Allowed Methods'),
          'required'  => false,
          'name'      => 'ups_allowed_methods',
          'values'   => $this->getAllowedMethods($allowedMethods),
      ));

      if ( Mage::getSingleton('adminhtml/session')->getDropshipData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getDropshipData());
          Mage::getSingleton('adminhtml/session')->setDropshipData(null);
      } elseif ( Mage::registry('dropship_data') ) {
          $form->setValues(Mage::registry('dropship_data')->getData());
          
          $form->getElement('ups_allowed_methods')->setValue(Mage::registry('dropship_data')->getUpsAllowedMethods());
      } 

      return parent::_prepareForm();
  	}
  

  
// get the allowed methods and put in a suitable array
  private function getAllowedMethods($allowedMethods)
  {
  	$options=array();
  	if(!is_array($allowedMethods)) { return $options; }
  	//todo store id
    foreach ($allowedMethods as $value=>$label) {
       
  		$options[] = array(
  		    'value' => $value,
          	'label' => $label
        );    
    }
    return $options;
  }




}