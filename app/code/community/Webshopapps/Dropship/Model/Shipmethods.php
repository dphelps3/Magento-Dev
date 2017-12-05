<?php

/**
 * @category   Webshopapps
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */
class Webshopapps_Dropship_Model_Shipmethods extends Mage_Core_Model_Abstract
{
	
	static protected $_shipmethods;
	
    public function _construct()
    {
        parent::_construct();
        
        $this->_init('dropship/shipmethods');
        $this->setIdFieldName('shipmethods_id');
    }
    
    
     /**
     * Retrieve option array
     *
     * @return array
     */
    static public function getOptionArray()
    {
        $options = array();
        foreach(self::getShipmethods() as $shipmethodsId=>$shipmethods) {
            $options[$shipmethodsId] = $shipmethods['title'];
        }        
        return $options;
    }
    
    static public function getShipmethods()
    {
        if (is_null(self::$_shipmethods)) {
            self::$_shipmethods = Mage::getModel('dropship/shipmethods')->getCollection();
        }

        return self::$_shipmethods;
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
        $options = self::getOptionArray();
        return isset($options[$optionId]) ? $options[$optionId] : null;
    }

    /**
     * Get Column(s) names for flat data building
     *
     * @return array
     */
    public function getFlatColums()
    {
        $columns = array();
        $columns[$this->getAttribute()->getAttributeCode()] = array(
            'type'      => 'int',
            'unsigned'  => false,
            'is_null'   => true,
            'default'   => null,
            'extra'     => null
        );
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
    
  
    
    
}