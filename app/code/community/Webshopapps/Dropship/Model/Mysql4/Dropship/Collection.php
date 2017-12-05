<?php

/**
 * @category   Webshopapps
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */
class Webshopapps_Dropship_Model_Mysql4_Dropship_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('dropship/dropship');
    }
    
    
	public function addAreaFilter($center_lat, $center_lng, $warehouses, $units='mi')
    {
    	$ids = join(',',$warehouses);  
    	$conn = $this->getConnection();
        $dist = sprintf(
            "(%s*acos(cos(radians(%s))*cos(radians(`latitude`))*cos(radians(`longitude`)-radians(%s))+sin(radians(%s))*sin(radians(`latitude`))))",
            $units=='mi' ? 3959 : 6371,
            $conn->quote($center_lat),
            $conn->quote($center_lng),
            $conn->quote($center_lat)
        );
       // $this->_select = $conn->select()->from(array('main_table' => $this->getResource()->getMainTable()), array('*', 'distance'=>$dist))
       //     ->where('`latitude` is not null and `latitude`<>0 and `longitude` is not null and `longitude`<>0 and '.$dist.'<=?', $radius)
       //     ->order('distance');
        $this->_select = $conn->select()->from(array('main_table' => $this->getResource()->getMainTable()), array('dropship_id', 'distance'=>$dist))
            ->where('`latitude` is not null and `latitude`<>0 and `longitude` is not null and `longitude`<>0')
            ->where('`dropship_id` in ('.$ids.')')
            ->order('distance')
            ->limit(1);
            
        return $this;
    }
}