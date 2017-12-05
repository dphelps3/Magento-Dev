<?php
class IWD_QuickView_Helper_Data extends Mage_Core_Helper_Data
{
    public function isStandardColorSwatch(){
        return Mage::getConfig()->getModuleConfig('Mage_ConfigurableSwatches')->is('active', 'true');
    }

    public function getProductMediaJs(){
        if($this->isStandardColorSwatch()){
            return 'js/iwd/quickview/configurableswatches/product-media.js';
        } else {
            return 'js/iwd/quickview/empty.js';
        }
    }

    public function getSwatchesProductJs(){
        if($this->isStandardColorSwatch()){
            return 'js/iwd/quickview/configurableswatches/swatches-product.js';
        } else {
            return 'js/iwd/quickview/empty.js';
        }
    }

    public function isAvailableVersion()
    {
        $mage = new Mage();
        if (!is_callable(array($mage, 'getEdition'))) {
            $edition = 'Community';
        } else {
            $edition = Mage::getEdition();
        }
        unset($mage);

        if ($edition == 'Enterpriscaptchae' && $this->_version == 'CE') {
            return false;
        }
        return true;
    }

    private $_version = 'EE';
}