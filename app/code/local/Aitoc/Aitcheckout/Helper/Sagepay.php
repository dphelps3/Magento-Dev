<?php

class Aitoc_Aitcheckout_Helper_Sagepay extends Aitoc_Aitcheckout_Helper_Abstract
{

    /**
     *
     * @return boolean
     */
    public function checkIfEbizmartsSagePaySuiteActive()
    {
        try {
            return Aitoc_Aitsys_Abstract_Service::get()->isModuleActive('Ebizmarts_SagePaySuite');
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     *
     * @return boolean
     */
    public function checkIfEbizmartsSagePaySuiteFormModeActive()
    {
        return $this->checkIfEbizmartsSagePaySuiteActive() && Mage::getStoreConfig('payment/sagepayform/active');
    }

    /**
     *
     * @return boolean
     */
    public function checkIfEbizmartsSagePaySuiteServerModeActive()
    {
        return $this->checkIfEbizmartsSagePaySuiteActive() && Mage::getStoreConfig('payment/sagepayserver/active');
    }

    /**
     *
     * @return boolean
     */
    public function checkIfEbizmartsSagePaySuiteServerMotoModeActive()
    {
        return $this->checkIfEbizmartsSagePaySuiteActive() && Mage::getStoreConfig('payment/sagepayserver_moto/active');
    }

    /**
     *
     * @return boolean
     */
    public function checkIfEbizmartsSagePaySuiteDirectProMotoModeActive()
    {
        return $this->checkIfEbizmartsSagePaySuiteActive()
        && Mage::getStoreConfig(
            'payment/sagepaydirectpro_moto/active'
        );
    }

    /**
     *
     * @return boolean
     */
    public function checkIfEbizmartsSagePaySuiteDirectProModeActive()
    {
        return $this->checkIfEbizmartsSagePaySuiteActive() && Mage::getStoreConfig('payment/sagepaydirectpro/active');
    }

    /**
     *
     * @return boolean
     */
    public function checkIfEbizmartsSagePaySuitePaypalModeActive()
    {
        return $this->checkIfEbizmartsSagePaySuiteActive() && Mage::getStoreConfig('payment/sagepaypaypal/active');
    }

    /**
     *
     * @return boolean
     */
    public function checkIfEbizmartsSagePaySuiteFormModeActiveOnly()
    {
        return $this->checkIfEbizmartsSagePaySuiteFormModeActive()
        && !$this->checkIfEbizmartsSagePaySuiteServerModeActive()
        && !$this->checkIfEbizmartsSagePaySuiteServerMotoModeActive()
        && !$this->checkIfEbizmartsSagePaySuiteDirectProMotoModeActive()
        && !$this->checkIfEbizmartsSagePaySuiteDirectProModeActive()
        && !$this->checkIfEbizmartsSagePaySuitePaypalModeActive();
    }

}
