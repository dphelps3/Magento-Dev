<?php
class NextBits_FormBuilder_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function recursiveReplace($search, $replace, $subject)
	{
		if(!is_array($subject))
		return $subject;
	
		foreach($subject as $key => $value)
		if(is_string($value))
		$subject[$key] = str_replace($search, $replace, $value);
		elseif(is_array($value))
		$subject[$key] = self::recursiveReplace($search, $replace, $value);
	
		return $subject;
	}
	
	public function addAssets(Mage_Core_Model_Layout $layout)
	{
		if($layout->getBlock('head')){
			$layout->getBlock('head')->addJs('calendar/calendar.js');
			$layout->getBlock('head')->addJs('mage/captcha.js');
			$layout->getBlock('head')->addJs('calendar/calendar-setup.js');
			$layout->getBlock('head')->addItem('js_css', 'calendar/calendar-win2k-1.css');
		}		
		return $this; 
	}
}