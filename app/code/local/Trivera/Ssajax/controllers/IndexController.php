<?php
require_once('app/code/local/Simplesolutions/Ssajax/controllers/IndexController.php');
class Trivera_Ssajax_IndexController extends Simplesolutions_Ssajax_IndexController {
  public function indexAction() {
    $params = $this->getRequest()->getParams();
    $skus = $params['sku'];
    $product_skus = explode('|', $skus);
    //header('Content-Type: text/xml');
	$myXml = '';
    $myXml .=  '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><XML>';

    foreach ($product_skus as $product_sku) {
      $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $product_sku);
      if ($product_sku) {
        $myXml .=  '<record>';
        $myXml .=  '<p_key>' . $product_sku . '</p_key>';
        $myXml .=  '<sku>' . $product_sku . '</sku>';
        /* check if the product is enabled in Magento. If yes, execute this
        code block */
        if (is_object($product)) {
          $myXml .=  '<nm><![CDATA[' . $product->getName() . ']]></nm>';
          $myXml .=  '<ds><![CDATA[' . $product->getShortDescription() . ']]></ds>';
          $myXml .=  '<retail_price>' . $product->getFinalPrice() . '</retail_price>';
          $myXml .=  '<thumb>' . $product->getThumbnailUrl() . '</thumb>';
          $myXml .=  '<pic>' . $product->getImageUrl() . '</pic>';
          $myXml .=  '<se_pagename>' . $product->getProductUrl() . '</se_pagename>';
        } // if product is disabled, execute this code block
        else {
          $myXml .=  $product . ' has been disabled.';
        }
        $myXml .=  '</record>';
      }
    }
    $myXml .=  '</XML>';

	$this->getResponse()
       ->clearHeaders()
       ->setHeader('Content-Type', 'text/xml')
       ->setBody($myXml);
    //$this->loadLayout();
    //$this->renderLayout();

  }	
}
?>