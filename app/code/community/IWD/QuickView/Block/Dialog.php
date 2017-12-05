<?php
class IWD_QuickView_Block_Dialog extends Mage_Core_Block_Template
{
	public function getJsonConfig() {
        $helper = Mage::helper('iwd_quickview/settings');

		$config = array ();
		
        $_scheme = Mage::app()->getRequest()->getScheme();
        $_secure = ($_scheme == 'https') ? true : false;
        $config ['shoppingCartUrl'] = $this->getUrl('checkout/cart/', array('_secure' => $_secure));
        $config ['removeShoppingCartUrl'] = $this->getUrl ( 'iwd_quickview/cart/remove', array (
            '_secure' => $_secure
        ) );
		$config['enable'] = $helper->isExtensionEnable();
		$config['qv_selector'] = Mage::getStoreConfig(IWD_QuickView_Helper_Settings::QV_SELECTOR_PRODUCT);
		$config['aac_selector'] = Mage::getStoreConfig(IWD_QuickView_Helper_Settings::AAC_SELECTOR_PRODUCT);


		$version = Mage::getVersionInfo();
        $config['useDefaultDropDown'] = ($version['minor']>=9) ? true : false;

		return Mage::helper('core')->jsonEncode($config);
	}

    public function getBackground()
    {
        return '#00909e';
    }

    public function getForeground()
    {
        return '#FFFFFF';
    }
}