<?php

/**
 * @category   Webshopapps
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */
class Webshopapps_Dropship_Model_Dropship extends Mage_Core_Model_Abstract
{

	static protected $_warehouses;

	protected $_eventPrefix = "wsa_dropship";

	protected $_modName = 'Webshopapps_Dropship';

    public function _construct()
    {
        parent::_construct();

        $this->_init('dropship/dropship');
        $this->setIdFieldName('dropship_id');
    }


     /**
     * Retrieve option array
     *
     * @return array
     */
    static public function getOptionArray()
    {
        $options = array();
        foreach(self::getWarehouses() as $dropShipId=>$warehouse) {
            $options[$dropShipId] = $warehouse['title'];
        }
		asort($options);
        return $options;
    }

    static public function getWarehouses()
    {
        if (is_null(self::$_warehouses)) {
            self::$_warehouses = Mage::getModel('dropship/dropship')->getCollection();
        }

        return self::$_warehouses;
    }

       /**
     * Retrieve all options
     *
     * @return array
     */
    static public function getAllOption()
    {
        $options = self::getOptionArray();
        array_unshift($options, array('value'=>'', 'label'=>''));
        return $options;
    }

/**
     * Retireve all options
     *
     * @return array
     */
    static public function getAllOptions()
    {
        $res = array();
        $res[] = array('value'=>'', 'label'=> Mage::helper('catalog')->__('-- Please Select --'));
        foreach (self::getOptionArray() as $index => $value) {
            $res[] = array(
               'value' => $index,
               'label' => $value
            );
        }
        return $res;
    }

 /**
     * Retrieve option text
     *
     * @param int $optionId
     * @return string
     */
    static public function getOptionText($optionId)
    {
    	$optionString='';
    	$next=false;
        $options = self::getOptionArray();
        $explodedOptionsId = explode(',',$optionId);
        foreach($explodedOptionsId as $indOption) {
        	if ($next) {
        		$optionString.=',';
        	}
        	$next=true;
        	$optionString.= isset($options[$indOption]) ? $options[$indOption] : null;
        }
        return $optionString;
    }

    /**
     * Get Column(s) names for flat data building
     *
     * @return array
     */
    public function getFlatColums()
    {
        $columns = array();
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $isMulti = $this->getAttribute()->getFrontend()->getInputType() == 'multiselect';
        $compatible = false;

        if(Mage::helper('wsacommon')->getNewVersion() < 11){
        	$compatible = true;
        } else if (method_exists(Mage::helper('core'), 'useDbCompatibleMode')) {
        	if (Mage::helper('core')->useDbCompatibleMode())
        	{
        		$compatible = true;
        	}
        }

        if ($compatible) {
        	if($isMulti){
	            $columns[$attributeCode] = array(
	                'type'      => 'varchar(255)',
	                'unsigned'  => false,
	                'is_null'   => true,
	                'default'   => null,
	                'extra'     => null
	            );
        	}
            else {
                $columns[$attributeCode] = array(
                    'type'      => 'int',
                    'unsigned'  => false,
                    'is_null'   => true,
                    'default'   => null,
                    'extra'     => null
                );
            }
        } else {
        	//if(Mage::helper('wsacommon')->getNewVersion() >= 11) {
            	$type = ($isMulti) ? Varien_Db_Ddl_Table::TYPE_TEXT : Varien_Db_Ddl_Table::TYPE_INTEGER;
        	//} else {
        	//	$type = ($isMulti) ? Varien_Db_Ddl_Table::TYPE_VARCHAR : Varien_Db_Ddl_Table::TYPE_INTEGER;
        	//}
        	if($isMulti){
	            $columns[$attributeCode] = array(
	                'type'      => $type,
	                'length'    => '255',
	                'unsigned'  => false,
	                'nullable'  => true,
	                'default'   => null,
	                'extra'     => null,
	                'comment'   => $attributeCode . ' column'
	            );
        	}
            if (!$isMulti) {
                $columns[$attributeCode] = array(
                    'type'      => $type,
                    'length'    => null,
                    'unsigned'  => false,
                    'nullable'  => true,
                    'default'   => null,
                    'extra'     => null,
                    'comment'   => $attributeCode . ' column'
                );
            }
        }

        return $columns;
   }

    /**
     * Retrieve Select for update Attribute value in flat table
     *
     * @param   int $store
     * @return  Varien_Db_Select|null
     */
    public function getFlatUpdateSelect($store)
    {
        return Mage::getResourceModel('eav/entity_attribute_option')
            ->getFlatUpdateSelect($this->getAttribute(), $store, false);
    }


 	protected function _beforeSave()
    {
    	$latitude = $this->getLatitude();
    	$longitude = $this->getLongitude();
        $geoAddress = $this->getGeoAddress();

        if (is_array($geoAddress)) {
            $country = trim($geoAddress['country']);
            $region = trim($geoAddress['region']);
            $postcode = trim($geoAddress['zipcode']);

            if(empty($country)){ return; }

            Mage::helper('dropship')->fetchCoordinates($country,$region,$postcode,$latitude,$longitude);
        }

        $this->setLatitude($latitude)->setLongitude($longitude);

        return parent::_beforeSave();

    }




}