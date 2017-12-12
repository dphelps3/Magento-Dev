<?php

class Aitoc_Aitcheckout_Model_Rewrite_Core_Url extends Mage_Core_Model_Url
{
    public function getRouteUrl($routePath = null, $routeParams = null)
    {
        $this->unsetData('route_params');

        if (isset($routeParams['_direct'])) {
            if (is_array($routeParams)) {
                $this->setRouteParams($routeParams, false);
            }

            return $this->getBaseUrl() . $routeParams['_direct'];
        }

        if (!is_null($routePath)) {
            $this->setRoutePath($routePath);
        }
        if (is_array($routeParams)) {
            $this->setRouteParams($routeParams, false);
        }

//aitoc start 
        if (!Mage::helper('aitcheckout')->isDisabled() && Mage::helper('aitcheckout')->isShowCheckoutInCart()) {
            if (false !== strpos($routePath, 'checkout/cart')) {
                $this->setSecure(true);
            }
        }
        //aitoc finish

        $url = $this->getBaseUrl() . $this->getRoutePath($routeParams);

        return $url;
    }
}