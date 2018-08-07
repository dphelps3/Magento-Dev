<?php
/* ProductMatrix
 *
 * @category   Webshopapps
 * @package    Webshopapps_productmatrix
 * @copyright  Copyright (c) 2012 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */


class Webshopapps_Productmatrix_Model_Carrier_Source_Labellers {

public function toOptionArray()
    {
        $productmatrix = Mage::getSingleton('productmatrix/carrier_productmatrix');
        $arr = array();
        foreach ($productmatrix->getCode('label_carriers') as $k=>$v) {
            $arr[] = array('value'=>$k, 'label'=>Mage::helper('shipping')->__($v));
        }
        return $arr;
    }
}
