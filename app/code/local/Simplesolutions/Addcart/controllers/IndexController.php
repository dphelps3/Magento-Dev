<?php
require_once 'Mage/Checkout/controllers/CartController.php';

class Simplesolutions_Addcart_IndexController extends Mage_Checkout_CartController {  
      
	public function indexAction() {	
	
		$cart   = $this->_getCart();	
		
        $params = $this->getRequest()->getParams();
		
		//if($params['isAjax'] == 1) {
		
			$response = array();
			
            try {
				$skus = $params['qty'];
				
				//echo '<pre>';
				//print_r($skus);
				//echo '</pre>';				
				
					
				
				foreach ($skus as $product_sku => $qty) {					
					if($qty < 1) { continue; }
					//if (isset($params['qty'])) {
					//	$filter = new Zend_Filter_LocalizedToNormalized(
					//	array('locale' => Mage::app()->getLocale()->getLocaleCode())
					//	);
					//	$params['qty'] = $filter->filter($params['qty']);
					//}
					
					$product = Mage::getModel('catalog/product');
					$product->load($product->getIdBySku($product_sku));	
					
					//echo '<pre>';
					//print_r($product);
					//echo '</pre>';
					//$product = $this->_initProduct();
					
					$related = $this->getRequest()->getParam('related_product');
					/**
					 * Check product availability
					 */
					if (!$product) {
						$response['status'] = 'ERROR';
						$response['message'] = $this->__('Unable to find Product ID');
					}
										
					$data = array(
						'product' => $product->getIdBySku($product_sku),
						'qty' => $qty
					);
					
					if ($product->getIdBySku($product_sku) != '') {
						$cart->addProduct($product, $data);		
					}
					
					if (!empty($related)) {
						$cart->addProductsByIds(explode(',', $related));
					}
	 
				}
 
				$cart->save();
				$this->_getSession()->setCartWasUpdated(true);
                /**
                 * @todo remove wishlist observer processAddToCart
                 */
                Mage::dispatchEvent('checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
                );
 
                if (!$this->_getSession()->getNoCartRedirect(true)) {
                    if (!$cart->getQuote()->getHasError()){
						if (!$product) {
							$response['status'] = 'ERROR';
							$response['message'] = $this->__('No product selected to add to cart.');
						} else {
							$message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->htmlEscape($product->getName()));
							$response['status'] = 'SUCCESS';
							$response['message'] = $message;
						}                        
						//New Code Here
                        //$this->loadLayout();
                        //$toplink = $this->getLayout()->getBlock('top.links')->toHtml();
                        //$sidebar = $this->getLayout()->getBlock('cart_sidebar')->toHtml();
                        //$response['toplink'] = $toplink;
                        //$response['sidebar'] = $sidebar;
                    }
                }
            } catch (Mage_Core_Exception $e) {
                $msg = "";
                if ($this->_getSession()->getUseNotice(true)) {
                    $msg = $e->getMessage();
                } else {
                    $messages = array_unique(explode("\n", $e->getMessage()));
                    foreach ($messages as $message) {
                        $msg .= $message.'<br/>';
                    }
                }
 
                $response['status'] = 'ERROR';
                $response['message'] = $msg;
            } catch (Exception $e) {
                $response['status'] = 'ERROR';
                $response['message'] = $this->__('Cannot add the item to shopping cart.');
                Mage::logException($e);
            }
			
			$url = Mage::getModel('core/url')
                       ->getUrl("checkout/cart");

            Mage::app()
                ->getResponse()
                ->setRedirect($url);

            Mage::app()
                ->getResponse()
                ->sendResponse();
			
            //$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
            return;
		//} else {
		//	return parent::addAction();
		//}	
	}
}

?>