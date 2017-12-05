<?php
class IWD_QuickView_Helper_Render extends Mage_Core_Helper_Data
{
     public function _renderProductOptions($observer){
         $request = Mage::app()->getRequest();
         $edit = $request->getParam('edit', false);
         if ($edit=='false'){
             $edit = false;
         }else if ($edit=='true'){
             $edit = true;
         }
//         $controller	=	Mage::app()->getFrontController();

         $layout = Mage::app()->getLayout();

         $block = $layout->getBlock('iwd.qv.product.info');
         if($edit) {
             $block->setSubmitRouteData(array(
                 'route' => 'checkout/cart/updateItemOptions',
                 'params' => array('id' => $request->getParam('id'))
             ));
         }

         $block->setData('edit', $edit);
         $block->setTemplate('iwd/quickview/catalog/product/view.phtml');

         return $block->toHtml();
     }
    /** RENDER CONFIRMATION BLOCK FOR ADD TO CART **/
    public function renderConfirmationCart($product, $update=false){
        $layout = Mage::app()->getLayout();
        $block = $layout->createBlock('core/template')->setData('product', $product)->setData('updateProduct', $update)->setTemplate('iwd/quickview/ajax/success-cart.phtml');

        return $block->toHtml();
    }
    /** RENDER CONFIRMATION BLOCK FOR ADD TO CART **/
	public function _renderConfirmationCart($product, $update=false){
        $after_addtocart = Mage::app()->getRequest()->getParam('iwd_qv_mode') == 'qv' ? Mage::getStoreConfig(IWD_QuickView_Helper_Settings::QV_AFTER_ADD_TO_CART) : Mage::getStoreConfig(IWD_QuickView_Helper_Settings::AAC_AFTER_ADD_TO_CART);
        if($after_addtocart != IWD_QuickView_Model_System_Config_Addtocartafter::MESSAGE){
            return false;
        }

		$block = Mage::app()->getLayout()->createBlock('core/template')
            ->setData('product', $product)
            ->setData('updateProduct', $update)
            ->setTemplate('iwd/quickview/ajax/success-cart.phtml');
		
		return $block->toHtml();
	} 
	
	/** RENDER CONFIRMATION BLOCK FOR ADD TO WISHLIST **/
	public function renderConfirmationWishlist($product){
		$layout = Mage::app()->getLayout();
		$block = $layout->createBlock('core/template')
            ->setData('product', $product)
            ->setTemplate('iwd/quickview/ajax/success-wishlist.phtml');
	
		return $block->toHtml();
	}
	
	/** RENDER SHOPPING CART **/
	public function _renderShoppingCart($is_shopping_cart) { // var_dump(); die(var_dump($is_shopping_cart));
        if ($is_shopping_cart === true || $is_shopping_cart === 'true') {
            Mage::app()->getCacheInstance()->cleanType('layout');
            $layout = Mage::app()->getLayout();
            $layout->getUpdate()->load('checkout_cart_index');
            $layout->generateXml();
            $layout->generateBlocks();
            $block = $layout->getBlock('checkout.cart');
            if ($block) {
                return $block->toHtml();
            }
        }
		return false;
	}
	
	/** RENDER TOP LINKS **/
	public function _renderHeader(){
		$layout = Mage::app()->getLayout();
		$updater = $layout->getUpdate();

		/*start fixing for cache*/
//		$updater->load('default');

		$handles = array('default');
		foreach ($handles as $handle) {
			$updater->addHandle($handle);
		}

		foreach ($updater->getHandles() as $handle) {
			$updater->merge($handle);
		}
		/*finish fixing for cache*/
		if (!Mage::getSingleton('customer/session')->isLoggedIn()){
			$updater->merge('customer_logged_out');
		}else{
			$updater->merge('customer_logged_in');
		}
		$layout->generateXml();
		$layout->generateBlocks();
		$block = $layout->getBlock('header');
		if ($block) {
			return $block->getChild('topLinks')->toHtml();
		}
		return false;

	}

	public function _renderDropdown(){
		$version = Mage::getVersionInfo();

		//if magento version 1.9
		if ($version['minor']>=9){
			if(Mage::app()->getCacheInstance()->canUse('layout'))
				Mage::app()->getCacheInstance()->cleanType('layout');
			$layout = Mage::app()->getLayout();
			$updater = $layout->getUpdate();
			/*start fixing for cache*/

			//		$updater->load('default'); //does not use with cache

			$handles = array('default');
			foreach ($handles as $handle) {
				$updater->addHandle($handle);
			}

			foreach ($updater->getHandles() as $handle) {
				$updater->merge($handle);
			}
			/*finish fixing for cache*/
			$layout->generateXml();
			$layout->generateBlocks();
			$block = $layout->getBlock('minicart_head');
			if($block) {
				return $block->toHtml();
			}else {
				return false;
			}
		}

		$layout = Mage::app()->getLayout();
		$updater = $layout->getUpdate();
		$updater->load('iwd_quickview_topcart_dropdown');
		$layout->generateXml();
		$layout->generateBlocks();
		$block = $layout->getBlock('top_cart');
		if ($block) {
			return $block->toHtml();
		}else {
			return false;
		}
	}
}