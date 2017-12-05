<?php

/**
 * @category   Webshopapps
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */
class Webshopapps_Dropship_Block_Adminhtml_Dropship_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('dropship_form', array('legend'=>Mage::helper('dropship')->__('Warehouse information')));
			$fieldsetAdvanced = $form->addFieldset('dropship_adv', array('legend' => Mage::helper('dropship')->__('Advanced Settings')));

      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('dropship')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('description', 'text', array(
          'label'     => Mage::helper('dropship')->__('Description'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'description',
      ));
			
			$options = Mage::getResourceModel('directory/country_collection')->load()->toOptionArray();

			$fieldset->addField('country', 'select', array(
					'label' => Mage::helper('dropship')->__('Origin Country'),
					'required' => false,
					'name' => 'country',
					'values' => $options,
			));
			
			$fieldset->addField('region', 'text', array(
          'label'     => Mage::helper('dropship')->__('Origin Region/State Code'),
          'required'  => false,
          'name'      => 'region',
      ));
			
			$fieldset->addField('zipcode', 'text', array(
          'label'     => Mage::helper('dropship')->__('Origin Zip/Postal Code'),
          'required'  => false,
          'name'      => 'zipcode',
      ));
			
			 $fieldset->addField('city', 'text', array(
          'label'     => Mage::helper('dropship')->__('Origin City'),
          'required'  => false,
          'name'      => 'city',
      ));

      $fieldset->addField('street', 'text', array(
          'label'     => Mage::helper('dropship')->__('Origin Street'),
          'required'  => false,
          'name'      => 'street',
      ));

     
  /*    $fieldset->addField('longitude', 'text', array(
          'name'      => 'longitude',
          'label'     => Mage::helper('dropship')->__('Longitude'),
          'note'      => Mage::helper('dropship')->__('This is set automatically.'),
      ));

      $fieldset->addField('latitude', 'text', array(
          'name'      => 'latitude',
          'label'     => Mage::helper('dropship')->__('Latitude'),
          'note'      => Mage::helper('dropship')->__('This is set automatically.'),
      )); */


      $fieldset->addField('email', 'text', array(
          'label'     => Mage::helper('dropship')->__('Email Address'),
          'required'  => false,
          'name'      => 'email',
      ));

          $fieldset->addField('contact', 'text', array(
          'label'     => Mage::helper('dropship')->__('Contact Name'),
          'required'  => false,
          'name'      => 'contact',
      ));

      $fieldset->addField('manualmail', 'select', array(
          'label'     => Mage::helper('dropship')->__('Send Packing Slip Email to Warehouse Manually'),
      	  'name'      => 'manualmail',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('dropship')->__('Yes'),
              ),

              array(
                  'value'     => 0,
                  'label'     => Mage::helper('dropship')->__('No'),
              ),
          ),
      ));

      $fieldset->addField('manualship', 'select', array(
      		'label'     => Mage::helper('dropship')->__('Manaully Create Packing Slips for this Warehouse'),
      		'name'      => 'manualship',
      		'values'    => array(
      				array(
      						'value'     => 1,
      						'label'     => Mage::helper('dropship')->__('Yes'),
      				),

      				array(
      						'value'     => 0,
      						'label'     => Mage::helper('dropship')->__('No'),
      				),
      		),
      ));

      $fieldset->addField('shippingmethods', 'multiselect', array(
          'label'     => Mage::helper('dropship')->__('Applicable Shipping Carriers'),
          'name'      => 'shippingmethods[]',
          'required'  => true,
      	  'values'	  => $this->getShippingMethods()
      ));

      $fieldsetAdvanced->addField('ismetapak', 'select', array(
          'name'      => 'ismetapak',
          'note'      => Mage::helper('dropship')->__('Only use if site is integrated with MetaPack.'),
     	  'label'     => Mage::helper('dropship')->__('Uses Metapack'),
          'values'    => array(
              array(
                  'value'     => 0,
                  'label'     => Mage::helper('dropship')->__('No'),
              ),

              array(
                  'value'     => 1,
                  'label'     => Mage::helper('dropship')->__('Yes'),
              ),
          ),
        ));

       $fieldsetAdvanced->addField('warehouse_code', 'text', array(
         'name'      => 'warehouse_code',
         'note'      => Mage::helper('dropship')->__('Leave empty if warehouse code not required.'),
         'label'     => Mage::helper('dropship')->__('Warehouse Code'),
         'required'  => false,
       ));



      if ( Mage::getSingleton('adminhtml/session')->getDropshipData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getDropshipData());
          Mage::getSingleton('adminhtml/session')->setDropshipData(null);
      } elseif ( Mage::registry('dropship_data') ) {
          $form->setValues(Mage::registry('dropship_data')->getData());
          $form->getElement('shippingmethods')->setValue(Mage::registry('dropship_data')->getShippingMethods());

      }
      return parent::_prepareForm();
  }

  // get the shipping methods and put in a suitable array
  private function getShippingMethods()
  {
  	$options=array();
  	//todo store id
    foreach (Mage::getStoreConfig('carriers', $this->getStoreId()) as $carrierCode=>$carrierConfig) {
        if (!isset($carrierConfig['title']) || $carrierCode=='dropship' ) {
        	continue;
        }
		$title = $carrierConfig['title'];
		if (isset($carrierConfig['name'])) {
			$title=$title." - ".$carrierConfig['name'];
		}
  		$options[] = array(
  		    'value' => $carrierCode,
          	'label' => $title
        );
    }
    return $options;
  }
}
