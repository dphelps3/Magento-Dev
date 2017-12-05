<?php
class Simplesolutions_Ssajax_IndexController extends Mage_Core_Controller_Front_Action {
  public function indexAction() {
    $params = $this->getRequest()->getParams();
    $skus = $params['sku'];
    $product_skus = explode('|', $skus);
    header('Content-Type: text/xml');
    echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><XML>';

    foreach ($product_skus as $product_sku) {
      $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $product_sku);
      if ($product_sku) {
        echo '<record>';
        echo '<p_key>' . $product_sku . '</p_key>';
        echo '<sku>' . $product_sku . '</sku>';
        /* check if the product is enabled in Magento. If yes, execute this
        code block */
        if (is_object($product)) {
          echo '<nm><![CDATA[' . $product->getName() . ']]></nm>';
          echo '<ds><![CDATA[' . $product->getShortDescription() . ']]></ds>';
          echo '<retail_price>' . $product->getFinalPrice() . '</retail_price>';
          echo '<thumb>' . $product->getThumbnailUrl() . '</thumb>';
          echo '<pic>' . $product->getImageUrl() . '</pic>';
          echo '<se_pagename>' . $product->getProductUrl() . '</se_pagename>';
        } // if product is disabled, execute this code block
        else {
          echo $product . ' has been disabled.';
        }
        echo '</record>';
      }
    }
    echo '</XML>';

    //$this->loadLayout();
    //$this->renderLayout();

  }	public function goodbyeAction() {
    $this->loadLayout();
    $this->renderLayout();
  }

  public function paramsAction() {
    echo '<dl>';
    foreach($this->getRequest()->getParams() as $key=>$value) {
      echo '<dt><strong>Param: </strong>'.$key.'</dt>';
      echo '<dt><strong>Value: </strong>'.$value.'</dt>';
    }
    echo '</dl>';
  }
}
?>