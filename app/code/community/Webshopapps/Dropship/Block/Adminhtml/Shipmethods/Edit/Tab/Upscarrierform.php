<?php

/**
 * @category   Webshopapps
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */
class Webshopapps_Dropship_Block_Adminhtml_Shipmethods_Edit_Tab_Upscarrierform extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      	$form = new Varien_Data_Form();
      	$this->setForm($form);
      	$fieldset = $form->addFieldset('upscarrier_form', array('legend'=>Mage::helper('dropship')->__('Combine UPS Carrier Rates')));
 
      	$originOptions = Mage::getSingleton('usa/shipping_carrier_ups')->getCode('originShipment');

		foreach ($originOptions as $origin=>$allowedMethods) {
			$tag = str_replace(' ','_',$origin);
      		$options=array();
      		$options[] = array(
	      		'value' => "none",
	      	    'label' => "**NONE**");
			foreach ($allowedMethods as $title=>$methodTitle) {
	     		$options[] = array(
	      		'value' => $title,
	          	'label' => $methodTitle
	        	); 
	      	}
      		$fieldset->addField('ups_shiptag_'.$tag, 'select', array(
		       'label'     => $origin,
		       'name'      => 'ups_shiptag_'.$tag,
		       'values'	  => $options
		       ));  		      	
	     }
 
      if ( Mage::getSingleton('adminhtml/session')->getShipmethodsData() ) 
      { 
          $form->setValues(Mage::getSingleton('adminhtml/session')->getShipmethodsData());
          Mage::getSingleton('adminhtml/session')->setShipmethodsData(null);
      } elseif ( Mage::registry('shipmethods_data') ) {
      	$form->setValues(Mage::registry('shipmethods_data')->getData());
      } 
      return parent::_prepareForm();
  }
  

}