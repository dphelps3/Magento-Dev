<?php

class Bull_LogProductUpdate_Model_Observer
{
    public function logUpdate(Varien_Event_Observer $observer) {

      // retrieve the product being updated from the event observer
      $product = $observer->getEvent()->getProduct();
      $name = $product->getName();
      $sku = $product->getSku();
      $description = $product->getDescription();

      Mage::log(
        /*"{$name} ({$sku}) updated", null, 'product-updates.log'*/
        "This is my name: {$name} and this is my description: ({$description})", null, 'product-updates.log'
      );

    }
}
