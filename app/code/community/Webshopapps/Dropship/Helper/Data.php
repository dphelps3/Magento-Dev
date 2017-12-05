<?php
/**
 * @category   Webshopapps
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */

/**
 * @category   Webshopapps
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */
class Webshopapps_Dropship_Helper_Data extends Mage_Core_Helper_Abstract
{

	private static $_debug;

	private static $_createShipmentEmail;

	private static $_isActive;

    private static $_calculateDropshipRates;



    public static function isDebug()
	{
		if (self::$_debug==NULL) {
			self::$_debug = Mage::helper('wsalogger')->isDebug('Webshopapps_Dropship');
		}
		return self::$_debug;
	}

    /**
     * Retrieve checkout session model
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Retrieve checkout quote model object
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }

    public function getWarehouseDescription()
    {
    	return Mage::getStoreConfig('carriers/dropship/warehouse_desc');
    }


    public function getGeoUrl()
    {
    	return Mage::getStoreConfig('ustorelocator/general/google_geo_url');
    }

  /**
     * Check if multishipping checkout is available.
     * There should be a valid quote in checkout session. If not, only the config value will be returned.
     *
     * @return bool
     */
    public function isMultipleWarehouses()
    {
        $quote = $this->getQuote();
        if ((!$quote) || $quote->hasItemsWithDecimalQty()) {
            return false;
        }
        $items= $quote->getAllVisibleItems();
        $maximunQty = (int)Mage::getStoreConfig('shipping/option/checkout_multiple_maximum_qty');
        /*if (count($items)<2) {
        	//return $quote->validateMinimumAmount(true)
            //&& (($quote->getItemsSummaryQty() - $quote->getItemVirtualQty()) > 0)
           // && ($quote->getItemsSummaryQty() <= $maximunQty);
        	return false;
        }*/
        $address=$this->getQuote()->getShippingAddress();

        return !$quote->hasItemsWithDecimalQty()
            && $quote->validateMinimumAmount(true)
            && (($quote->getItemsSummaryQty() - $quote->getItemVirtualQty()) > 0)
            && Mage::helper('dropship/shipcalculate')->isMultipleWarehouses(
            	$address->getCountryId(),$address->getRegionCode(),$address->getPostcode(),$items);
    }

    public function getWarehouseShippingHtml($encodedDetails)
    {
    	$decodedDetails = $this->decodeShippingDetails($encodedDetails);
    	$htmlText='';
    	foreach ($decodedDetails as $shipLine) {
	    	$htmlText .= Mage::helper('dropship')->getDescription($shipLine['warehouse']).
	    				' : '.$shipLine['carrierTitle'].' - '. $shipLine['methodTitle'].' '.
	    				$this->getQuote()->getStore()->formatPrice($shipLine['price']).'<br/>';
    	}
    	return $htmlText;
    }


    public function getWarehouseTitle($item) {
    	$title="";
        if ($item->getWarehouse()!='') {
            $title = Mage::getModel('dropship/dropship')->load($item->getWarehouse())->getTitle();
        } else {
            $product = Mage::getModel('catalog/product')->loadByAttribute('entity_id',$item->getProductId(), 'warehouse');
            if (is_object($product) && $product->getData('warehouse')!='') {
                $title = Mage::getModel('dropship/dropship')->load($product->getData('warehouse'))->getTitle();
            }
        }
        return $title;
    }

 	public function isMergedCheckout()
    {
    	return !(!Mage::getStoreConfig('carriers/dropship/merged_checkout') && $this->isMultipleWarehouses() );

    }

    public function encodeShippingDetails($shippingDetails)
    {
    		return Zend_Json::encode($shippingDetails);
    }


    public function decodeShippingDetails($shippingDetailsEnc)
    {
    	return Zend_Json::decode($shippingDetailsEnc);
    }

 	public function getDescription($warehouseId)
 	{
 		$warehouseId == '' ? $warehouseId = Mage::getStoreConfig('carriers/dropship/default_warehouse') : 1;
    	return Mage::getModel('dropship/dropship')->load($warehouseId)->getDescription();
    }


  	public function fetchCoordinates($country, $region, $postcode, &$latitude, &$longitude)
    {
        $url = $this->getGeoUrl();
        if (!$url) {
            $url = "http://maps.googleapis.com/maps/api/geocode/xml"; //New API V3
        }
        $region = urlencode(preg_replace('#\r|\n#', ' ', $region));
        $country = urlencode(preg_replace('#\r|\n#', ' ', $country));
        $postcode = urlencode(preg_replace('#\r|\n#', ' ', $postcode));

        $url .= strpos($url, '?')!==false ? '&' : '?';
        $urlCountryRegion = $url;
        $urlCountry = $url;
        $url .= 'components=administrative_area:'.$region.'|country:'.$country.'|postal_code:'.$postcode.'&sensor=false';
        $urlCountryRegion .= 'components=administrative_area:'.$region.'|country:'.$country.'&sensor=false';
        $urlCountry .= 'components=country:'.$country.'&sensor=false';

        for($i=0;$i<3;$i++) {
	        switch ($i) {
	        	case 0:
	        		Mage::helper('wsacommon/log')->postNotice('dropship','Get Nearest Warehouse Search', $region.' '.$country.' '.$postcode, self::isDebug());
	        		break;
	        	case 1:
	        		Mage::helper('wsacommon/log')->postNotice('dropship','Get Nearest Warehouse Search', $region.' '.$country, self::isDebug());
	        		$url = $urlCountryRegion;
	        		break;
	        	case 2:
	        		Mage::helper('wsacommon/log')->postNotice('dropship','Get Nearest Warehouse Search', $country, self::isDebug());
	        		$url = $urlCountry;
	        		break;
	        	default: break;
	        }
	        $cinit = curl_init();
	        curl_setopt($cinit, CURLOPT_URL, $url);
	        curl_setopt($cinit, CURLOPT_HEADER,0);
	        curl_setopt($cinit, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"]);
	        curl_setopt($cinit, CURLOPT_RETURNTRANSFER, 1);
	        $response = curl_exec($cinit);
	        if (!is_string($response) || empty($response)) {
	            return $this;
	        }
	        $xml = new Varien_Simplexml_Config();
	        $xml->loadString($response);
	        Mage::helper('wsacommon/log')->postNotice('dropship','Nearest Warehouse Response', $response, self::isDebug());
	        $status = $xml->getXpath("//GeocodeResponse/status/text()");
	        $failed = TRUE;

	        if ($status[0][0] == "OK") {
	        	$failed=FALSE;
	        	break;
	        }
	        Mage::helper('wsacommon/log')->postNotice('dropship','Nearest Warehouse - No Match Found', 'Searching for a more generic location', self::isDebug());
        }

        if ($failed) {
       		Mage::helper('wsacommon/log')->postCritical('dropship','Nearest Warehouse', 'Nearest Warehouse Search Failed', self::isDebug());
        	return $this;
        }

        $arr = $xml->getXpath("//GeocodeResponse/result/geometry/location/text()");

        foreach ($arr as $element){
        	if($element->lat != 0){
        		$latitude = $element->lat;
        		$longitude = $element->lng;
        		break;
        	}
        }
        return $this;
    }

	public  function getNearestWarehouse($country,$region,$postcode, $warehouses)
	{
		$shipLat=0;
		$shipLong=0;
		$this->fetchCoordinates($country,$region,$postcode,$shipLat,$shipLong);

		Mage::helper('wsacommon/log')->postNotice('dropship','Get Nearest Warehouse Lat/Long', $shipLat.'/'.$shipLong, self::isDebug());
		Mage::helper('wsacommon/log')->postNotice('dropship','Get Nearest Warehouse from these Warehouses', $warehouses, self::isDebug());

        try {
            $num = (int)Mage::getStoreConfig('ustorelocator/general/num_results');
            $units = Mage::getStoreConfig('ustorelocator/general/distance_units');
            $collection = Mage::getModel('dropship/dropship')->getCollection()
                ->addAreaFilter(
                    $shipLat,
                    $shipLong,
                    $warehouses,
                    $units
                );

            $privateFields = Mage::getConfig()->getNode('global/ustorelocator/private_fields');
            $i = 0;
            foreach ($collection as $loc){
            	$data=$loc->getData();
				if (self::isDebug()) {
            		Mage::log($data);
				}
            	return $data['dropship_id'];

            }
        } catch (Exception $e) {
        	Mage::log($e->getMessage());
            return $warehouses[0];
        }
        return $warehouses[0];

    }

	public static function isCreateShipmentEmail($store='')
    {
    	if(!Mage::getStoreConfig('carriers/dropship/active',$store)){
    		return false;
    	}

	    if (self::$_createShipmentEmail==NULL) {
			self::$_createShipmentEmail = Mage::getStoreConfig('carriers/dropship/shipment_email',$store);
		}
		return self::$_createShipmentEmail;


    }

    public static function isActive() {
        if (self::$_isActive==NULL) {
	    	self::$_isActive = Mage::getStoreConfig('carriers/dropship/active');
        }
        return self::$_isActive;
    }

    public static function calculateDropshipRates() {
        if (!self::isActive()) {
            return false; // if not active false anyhow
        }
        if (self::$_calculateDropshipRates==NULL) {
            self::$_calculateDropshipRates = !Mage::getStoreConfig('carriers/dropship/ignore_shipping');
        }
        return self::$_calculateDropshipRates;
    }
}