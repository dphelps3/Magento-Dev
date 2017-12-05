<?php 
class Webshopapps_Dropship_Model_Sales_Order_Shipment extends Mage_Sales_Model_Order_Shipment
{
	/**
	 * Rewrites to ignore existing ID as Dropship automatically creates the shipments, we're just adding the items to it now.
	 * Difficult to remove re-write as can't see an event here to hook into.
	 * Called from saveAction() in Mage_Adminhtml_Sales_Order_ShipmentController
     */
    public function register()
    {
       	/* if ($this->getId()) {
            Mage::throwException(
                Mage::helper('sales')->__('Cannot register existing shipment')
            );
        } */
    	
    	if(!Mage::getStoreConfig('carriers/dropship/active',$this->getStoreId())) {
    		return parent::register();
    	}

        $totalQty = 0;
        foreach ($this->getAllItems() as $item) {
            if ($item->getQty()>0) {
                $item->register();
                if (!$item->getOrderItem()->isDummy(true)) {
                    $totalQty+= $item->getQty();
                }
            }
            else {
                $item->isDeleted(true);
            }
        }
        $this->setTotalQty($totalQty);

        return $this;
    }
	
}