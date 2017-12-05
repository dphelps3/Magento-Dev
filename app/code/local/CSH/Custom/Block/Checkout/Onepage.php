<?php
class CSH_Custom_Block_Checkout_Onepage extends Mage_Checkout_Block_Onepage{

	public function getSteps()
	{
		$steps = array();

		if (!$this->isCustomerLoggedIn()) {
			$steps['login'] = $this->getCheckout()->getStepData('login');
		}

		//New Code Adding step excellence here
		//$stepCodes = array('billing', 'shipping','csh','shipping_method', 'csh2', 'payment','review');
		
		// Removed the CSH shipping steps 5/28/2013
		$stepCodes = array('billing', 'shipping','shipping_method', 'csh2', 'payment','review');

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