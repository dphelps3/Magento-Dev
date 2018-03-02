<?php
class CSH_Custom_Block_Checkout_Onepage extends Mage_Checkout_Block_Onepage{

	public function getSteps()
	{
		$steps = array();

		if (!$this->isCustomerLoggedIn()) {
			$steps['login'] = $this->getCheckout()->getStepData('login');
		}
		
		// Order of the checkout steps
		$stepCodes = array('billing', 'shipping','shipping_method', 'payment','review');
        // $stepCodes = array('billing', 'shipping','shipping_method', 'csh2', 'payment','review');

		foreach ($stepCodes as $step) {
			$steps[$step] = $this->getCheckout()->getStepData($step);
		}
		return $steps;
	}

	public function getActiveStep()
	{
		//New Code, make step excellence active when user is already logged in
		return $this->isCustomerLoggedIn() ? 'billing' : 'login';
	}

}