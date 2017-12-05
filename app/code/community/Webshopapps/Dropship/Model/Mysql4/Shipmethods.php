<?php

/**
 * @category   Webshopapps
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */
class Webshopapps_Dropship_Model_Mysql4_Shipmethods extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('dropship/shipmethods', 'shipmethods_id');
    }
    
      protected function _afterLoad(Mage_Core_Model_Abstract $object) {
        parent::_afterLoad($object);
        
        $carrierSelect = $this->_getReadAdapter()->select()
            ->from($this->getTable('shipmethods_carriers'))
            ->where('shipmethods_id=?', $object->getId());

        $shippingMethods = $this->_getReadAdapter()->fetchAll($carrierSelect);

        foreach ($shippingMethods as $shipmethod) {
        	if ($shipmethod['carrier_code']=='productmatrix' && !is_null($shipmethod['warehouse'])) {
         		$object->setData('sshiptag_'.$shipmethod['carrier_code'].'_'.$shipmethod['warehouse'],$shipmethod['carrier_title']);
        	} else {
         		$object->setData("sshiptag_".$shipmethod['carrier_code'],$shipmethod['carrier_title']);
        	}
        }
        
     	$upsCarrierSelect = $this->_getReadAdapter()->select()
            ->from($this->getTable('shipmethods_upscarriers'))
            ->where('upscarriers_id=?', $object->getId());

        $shippingMethods = $this->_getReadAdapter()->fetchAll($upsCarrierSelect);

        foreach ($shippingMethods as $shipmethod) {
         	$object->setData("ups_shiptag_".$shipmethod['origin'],$shipmethod['carrier_title']);
        }
        
        return $this;
    }
    
    public function isShipmethodPresent($shipMethodId,$code,$title,$warehouse) {
        
        $select = $this->_getReadAdapter()->select();
 		$select
            ->from($this->getTable('shipmethods_carriers'))   	
            ->distinct(true)
            ->where('shipmethods_id=?', $shipMethodId)
            ->where('carrier_code=?', $code)
            ->where('carrier_title=?', strtolower($title));
        $result=$this->_getReadAdapter()->fetchCol($select);
        
        if (empty($result)) { return $this->isUpsShipmethodPresent($shipMethodId,$code,$title,$warehouse); }
        
        return true;
            
    }
    
	public function isUpsShipmethodPresent($shipMethodId,$code,$title,$warehouse) {
        
        if ($code != 'ups') { return false; }
        
        // try the origin options
        $select = $this->_getReadAdapter()->select();
        $select
            ->from($this->getTable('shipmethods_upscarriers'))   	
            ->distinct(true)
            ->where('upscarriers_id=?', $shipMethodId)
            ->where('carrier_title=?', strtolower($title));
        $result=$this->_getReadAdapter()->fetchCol($select);
        if (empty($result)) { return false; }
        
        return true;
            
    }
    
    
    protected function _afterSave(Mage_Core_Model_Abstract $object) {
        parent::_afterSave($object);
    
    	// now save the shipping methods
            try {
                $condition = $this->_getWriteAdapter()->quoteInto('shipmethods_id = ?', $object->getId());
                $this->_getWriteAdapter()->delete($this->getTable('shipmethods_carriers'), $condition);
                foreach ($object->getData() as $code=>$title) {
                	if (strpos($code,"sshiptag") === false) { continue; }
                	$parts =explode("_",$code);
                	if ($parts[0]!="sshiptag") {
                		continue;
                	}
                	if ($parts[1]=="none") {
                		continue;
                	}
                	if (count($parts)!=3 || is_null($parts[2])) {
                		$warehouse=-1;
                	} else {
                		$warehouse=$parts[2];
                	}
                	//if(substr($fullcode,0,13) == 'productmatrix') { $fullcode = 'productmatrix'; }
                	$shippingInsert = new Varien_Object();
                    $shippingInsert->setShipmethodsId($object->getId());
                    $shippingInsert->setCarrierCode($parts[1]);
                    $shippingInsert->setCarrierTitle($title);
                    $shippingInsert->setWarehouse($warehouse);
                    $this->_getWriteAdapter()->insert($this->getTable('shipmethods_carriers'), $shippingInsert->getData());
                }
                $this->_getWriteAdapter()->commit();
                
            }
            catch (Exception  $e) {
            	Mage::throwException($e);
                $this->_getWriteAdapter()->rollBack();
            }
            // save ups shipping methods with different origins
    		try {
                $condition = $this->_getWriteAdapter()->quoteInto('upscarriers_id = ?', $object->getId());
                $this->_getWriteAdapter()->delete($this->getTable('shipmethods_upscarriers'), $condition);
                foreach ($object->getData() as $code=>$title) {
                	if (strpos($code,"ups_shiptag") === false) { continue; }
                	$origin = str_replace('ups_shiptag_','',$code);
                	
                	$shippingInsert = new Varien_Object();
                    $shippingInsert->setUpscarriersId($object->getId());
                    $shippingInsert->setOrigin($origin);
                    $shippingInsert->setCarrierTitle($title);
                    $shippingInsert->setWarehouse(-1); // not currently used
                    $this->_getWriteAdapter()->insert($this->getTable('shipmethods_upscarriers'), $shippingInsert->getData());
                }
                $this->_getWriteAdapter()->commit();
                
            }
            catch (Exception  $e) {
            	Mage::throwException($e);
                $this->_getWriteAdapter()->rollBack();
            }
        
        
   		return $this;
    }
    
}