<?php

class Hawksearch_Datafeed_Model_Observer
{
    public function adminhtmlCatalogProductAttributeEditPrepareForm(Varien_Event_Observer $observer) {
        $form = $observer->getForm();
        $fieldset = $form->getElement('front_fieldset');

        $fieldset->addField('is_hawksearch', 'select', array(
            'name' => 'is_hawksearch',
            'label' => 'Export to HawkSearch',
            'title' => 'Export to HawkSearch',
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray()
        ));
    }
}