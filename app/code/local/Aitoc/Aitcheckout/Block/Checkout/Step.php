<?php

class Aitoc_Aitcheckout_Block_Checkout_Step extends Mage_Checkout_Block_Onepage_Abstract
{
    public function getAddressesHtmlSelect($type)
    {
        $options   = array();
        $addressId = Mage::helper('aitcheckout/adjgiftregistry')->getAddressesHtmlSelect($this, $options);
        if ($this->isCustomerLoggedIn()) {
            foreach ($this->getCustomer()->getAddresses() as $address) {
                $options[] = array(
                    'value' => $address->getId(),
                    'label' => $address->format('oneline')
                );
            }

            if (!$addressId) {
                $addressId = $this->getAddress()->getCustomerAddressId();
                if (empty($addressId)) {
                    if ($type == 'billing') {
                        $address = $this->getCustomer()->getPrimaryBillingAddress();
                    } else {
                        $address = $this->getCustomer()->getPrimaryShippingAddress();
                    }
                    if ($address) {
                        $addressId = $address->getId();
                    }
                }
            }

            $select = $this->getLayout()->createBlock('core/html_select')
                ->setName($type . '_address_id')
                ->setId($type . '-address-select')
                ->setClass('address-select')
                //->setExtraParams('onchange="'.$type.'.newAddress(!this.value)"')
                ->setValue($addressId)
                ->setOptions($options);

            $select->addOption('', Mage::helper('checkout')->__('New Address'));

            return Mage::helper('aitcheckout/aitconfcheckout')->getAddressesHtmlSelect(
                $select->getHtml()
            );  // Aitoc Configurable Checkout compatibility
        }

        return '';
    }

    public function getCountryHtmlSelect($type)
    {
        $countryId = $this->getAddress()->getCountryId();
        if (is_null($countryId)) {
            $countryId = Mage::helper('aitcheckout')->getDefaultCountry();
        }
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName($type . '[country_id]')
            ->setId($type . ':country_id')
            ->setTitle(Mage::helper('checkout')->__('Country'))
            ->setClass('validate-select')
            ->setValue($countryId)
            ->setOptions($this->getCountryOptions());
//        if ($type === 'shipping') {
//            $select->setExtraParams('onchange="shipping.setSameAsBilling(false);"');
//        }

        return $select->getHtml();
    }

    public function getAddress()
    {
        if (is_null($this->_address)) {
            $method         = 'get' . $this->_stepType . 'Address';
            $this->_address = $this->getQuote()->$method();
            Mage::helper('aitcheckout/aitconfcheckout')->getAddress(
                $this->_address
            ); // Aitoc Configurable Checkout compatibility
        }

        return $this->_address;
    }

    public function getSortedChildren()
    {

    }
}