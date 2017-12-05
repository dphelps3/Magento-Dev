<?php


/**
 * @category   Webshopapps
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */
class Webshopapps_Dropship_Block_Dropship extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getDropship()     
     { 
        if (!$this->hasData('dropship')) {
            $this->setData('dropship', Mage::registry('dropship'));
        }
        return $this->getData('dropship');
        
    }
}