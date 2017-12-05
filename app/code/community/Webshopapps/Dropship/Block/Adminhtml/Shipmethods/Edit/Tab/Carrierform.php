<?php

/**
 * @category   Webshopapps
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */
class Webshopapps_Dropship_Block_Adminhtml_Shipmethods_Edit_Tab_Carrierform extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('carrier_form', array('legend'=>Mage::helper('dropship')->__('Combine Shipping Methods')));

      $shippingMethods = $this->getShippingMethods();
      $carrierConfig = Mage::getModel('shipping/config');
      foreach ($shippingMethods as $shippingMethod) {

      	if ($shippingMethod['code']=='dropship') { continue; }

      	if ($shippingMethod['code']=='productmatrix') {
      		foreach (Mage::getModel('dropship/dropship')->getCollection() as $warehouse){
	      		$allowedMethods=$carrierConfig->getCarrierInstance($shippingMethod['code'])->getAllowedMethods();
	      		if (empty($allowedMethods)) { continue; }
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
		    $fieldset->addField('sshiptag_'.$shippingMethod['code'].'_'.$warehouse['dropship_id'], 'select', array(
		       'label'     => $shippingMethod['title'].' Warehouse - '.$warehouse['title'],
		       'name'      => 'sshiptag_'.$shippingMethod['code'].'_'.$warehouse['dropship_id'],
		       'values'	  => $options
		       ));
	      	}
	      	continue;
      	}
        $carrier = $carrierConfig->getCarrierInstance($shippingMethod['code']);

        if(!is_object($carrier)) {
            continue;
        }

      	$allowedMethods = $carrier->getAllowedMethods();
      	if (empty($allowedMethods)) { continue; }
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

	      $fieldset->addField('sshiptag_'.$shippingMethod['code'], 'select', array(
	          'label'     => $shippingMethod['title'],
	          'name'      => 'sshiptag_'.$shippingMethod['code'],
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


  // get the shipping methods and put in a suitable array
  private function getShippingMethods()
  {
  	$options=array();
  	//todo store id
    foreach (Mage::getStoreConfig('carriers', $this->getStoreId()) as $carrierCode=>$carrierConfig) {
        if (!isset($carrierConfig['title'])) {
        	continue;
        }
		$title = $carrierConfig['title'];
		if (isset($carrierConfig['name'])) {
			$title=$title." - ".$carrierConfig['name'];
		}
  		$options[] = array(
  		    'code' => $carrierCode,
          	'title' => $title
        );
    }
    return $options;
  }
}