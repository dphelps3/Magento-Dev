<?php

	class Simplesolutions_Roundsubtotal_Model_Observer  {
		
		public function __construct()
        {
        }

		public function roundtotal($observer) { 
		
		}
		
        public function save_csh_id($observer)
        {
			$customer = $observer->getCustomer();			
			$incrementId = Mage::getSingleton('eav/config')->getEntityType('customer');
			$incrementId->setIncrementModel('eav/entity_increment_alphanum');
			$incrementId = $incrementId->fetchNewIncrementId($customer->getStoreId());
				
			//echo 'iid2<pre>';
			//print_r($incrementId);
			//echo '</pre>';	
				
			$customer->setMasId($incrementId);
			$customer->save();
			
			//die;
        }
	}