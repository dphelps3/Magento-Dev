<?php

	class DecHardware_Register_Model_Observer  {

		public function __construct()
        {
        }

        public function save_csh_id($observer)
        {
			$customer = $observer->getCustomer();
			//Mage::log(var_export($customer->debug(), TRUE));
			$customerId = $customer->getMasId();
			//Mage::log('Customer Id:'.$customerId);
			if($customerId == '') {
				$incrementId = Mage::getSingleton('eav/config')->getEntityType('customer');
				$incrementId->setIncrementModel('eav/entity_increment_alphanum');
				$incrementId = $incrementId->fetchNewIncrementId($customer->getStoreId());


				$customer->setMasId($incrementId);
				$customer->save();
			}

        }
		
		public function test_customer_info($observer) {
			$customer = $observer->getCustomer();
			$customerId = $customer->getMasId();
			//Mage::log('Customer Id:'.$customerId);
			if($customerId == '') {
				$incrementId = Mage::getSingleton('eav/config')->getEntityType('customer');
				$incrementId->setIncrementModel('eav/entity_increment_alphanum');
				$incrementId = $incrementId->fetchNewIncrementId($customer->getStoreId());
				$customer->setMasId($incrementId);
				$customer->save();
			}
		}
	}
