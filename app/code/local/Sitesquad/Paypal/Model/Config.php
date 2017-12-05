<?php

class Sitesquad_Paypal_Model_Config extends Mage_Paypal_Model_Config
{
 
    /**
     * BN code getter
     * override method
     *
     * @param string $countryCode ISO 3166-1
     */
    public function getBuildNotationCode($countryCode = null)
    {
		$ceCode = 'Sitesquad_SI_MagentoCE';
		$eeCode = 'Sitesquad_SI_MagentoEE';
		
		if(Mage::getEdition() == 'Enterprise')
		{
			$newBnCode = $eeCode;
		}
		else
		{
			$newBnCode = $ceCode;
		}
		
        return $newBnCode;
    }
 
}