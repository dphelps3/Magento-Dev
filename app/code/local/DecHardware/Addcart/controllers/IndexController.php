<?php
require_once 'Mage/Checkout/controllers/CartController.php';

class DecHardware_Addcart_IndexController extends Mage_Checkout_CartController {

	public function indexAction() {

		$cart   = $this->_getCart();

        $params = $this->getRequest()->getParams();

				$response = array();

            try {
				$skus = $params['qty'];

				foreach ($skus as $product_sku => $qty) {
					if($qty < 1) { continue; }

					$product = Mage::getModel('catalog/product');
					$product->load($product->getIdBySku($product_sku));


					$related = $this->getRequest()->getParam('related_product');

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

            return;

	}
}

?>
