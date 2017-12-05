<?php

/**
 * @category   Webshopapps
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */
class Webshopapps_Dropship_Model_Mysql4_Dropship extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        // Note that the dropship_id refers to the key field in your database table.
        $this->_init('dropship/dropship', 'dropship_id');
    }

	protected function _afterLoad(Mage_Core_Model_Abstract $object) {
		parent::_afterLoad($object);

		if ($object->hasUpsAllowedMethods()) {

			$putAllowedMethods = array();
			$splitAllowedMethods = explode(',', $object->getUpsAllowedMethods());
			foreach ($splitAllowedMethods as $allowedMethod) {
				$putAllowedMethods[] = $allowedMethod;
			}
			$object->setUpsAllowedMethods($putAllowedMethods);
		}

		if ($object->hasFedexsoapAllowedMethods()) {

			$putAllowedMethods = array();
			$splitAllowedMethods = explode(',', $object->getFedexsoapAllowedMethods());
			foreach ($splitAllowedMethods as $allowedMethod) {
				$putAllowedMethods[] = $allowedMethod;
			}
			$object->setFedexsoapAllowedMethods($putAllowedMethods);
		}
		
			if ($object->hasUspsAllowedMethods()) {

			$putAllowedMethods = array();
			$splitAllowedMethods = explode(',', $object->getUspsAllowedMethods());
			foreach ($splitAllowedMethods as $allowedMethod) {
				$putAllowedMethods[] = $allowedMethod;
			}
			$object->setUspsAllowedMethods($putAllowedMethods);
		}

		$warehouseSelect = $this->_getReadAdapter()->select()
						->from($this->getTable('dropship_shipping'))
						->where('dropship_id=?', $object->getId());

		$shippingMethods = $this->_getReadAdapter()->fetchAll($warehouseSelect);

		$putShippingMethod = array();
		foreach ($shippingMethods as $shippingMethod) {
			$putShippingMethod[] = $shippingMethod['shipping_id'];
		}
		$object->setShippingMethods($putShippingMethod);

		return $this;
	}

   protected function _beforeSave(Mage_Core_Model_Abstract $object) {

		parent::_beforeSave($object);

		$combineAllowed = '';
		if ($object->hasUpsAllowedMethods()) {
			$first = true;
			foreach ($object->getUpsAllowedMethods() as $allowedMethod) {
				if ($first) {
					$combineAllowed = $allowedMethod;
					$first = false;
				} else {
					$combineAllowed .= ',' . $allowedMethod;
				}
			}
			$object->setUpsAllowedMethods($combineAllowed);
		}

		if ($object->hasFedexsoapAllowedMethods()) {
			$first = true;
			foreach ($object->getFedexsoapAllowedMethods() as $allowedMethod) {
				if ($first) {
					$combineAllowed = $allowedMethod;
					$first = false;
				} else {
					$combineAllowed .= ',' . $allowedMethod;
				}
			}
			$object->setFedexsoapAllowedMethods($combineAllowed);
		}
		
			if ($object->hasUspsAllowedMethods()) {
			$first = true;
			foreach ($object->getUspsAllowedMethods() as $allowedMethod) {
				if ($first) {
					$combineAllowed = $allowedMethod;
					$first = false;
				} else {
					$combineAllowed .= ',' . $allowedMethod;
				}
			}
			$object->setUspsAllowedMethods($combineAllowed);
		}
		return $this;
	}


    protected function _afterSave(Mage_Core_Model_Abstract $object) {
        parent::_afterSave($object);

    	// now save the shipping methods
	   if($object->hasShippingmethods()) {
            try {
                $condition = $this->_getWriteAdapter()->quoteInto('dropship_id = ?', $object->getId());
                $this->_getWriteAdapter()->delete($this->getTable('dropship_shipping'), $condition);
                foreach ($object->getShippingmethods() as $shippingMethod) {
                    $shippingInsert = new Varien_Object();
                    $shippingInsert->setShippingId($shippingMethod);
                    $shippingInsert->setDropshipId($object->getId());
                    $this->_getWriteAdapter()->insert($this->getTable('dropship_shipping'), $shippingInsert->getData());
                }
                $this->_getWriteAdapter()->commit();

            }
            catch (Exception  $e) {
                $this->_getWriteAdapter()->rollBack();
            }
        }


   		return $this;
    }


}