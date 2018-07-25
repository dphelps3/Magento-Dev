<?php

/**
 * Created by PhpStorm.
 * User: magentouser
 * Date: 1/29/15
 * Time: 4:54 PM
 */
class Hawksearch_Proxy_Model_System_Config_Backend_Cron extends Mage_Core_Model_Config_Data {
	protected function _afterSave() {
		$status = $this->checkCronExpression(trim($this->getValue()));
		if ($status == 'valid') {
			Mage::getModel('core/config_data')
				->load('crontab/jobs/hawksearch_proxy/schedule/cron_expr', 'path')
				->setValue(trim($this->getValue()))
				->setPath('crontab/jobs/hawksearch_proxy/schedule/cron_expr')
				->save();
			Mage::getModel('core/config_data')
				->load('crontab/jobs/hawksearch_proxy/run/model', 'path')
				->setValue('hawksearch_proxy/observer::syncCategories')
				->setPath('crontab/jobs/hawksearch_proxy/run/model')
				->save();

		} else {
			$setting = Mage::getModel('core/config_data')
				->load('crontab/jobs/hawksearch_proxy/schedule/cron_expr', 'path')
				->getValue();

			Mage::getSingleton('core/session')
				->addNotice(sprintf('%s %s %s %s "%s"',
					'The provided cron string does not appear to be valid and was not added to the configuration.',
					'The problem appears to be: ',
					$status,
					'The system will continue to use the current value:',
					$setting));
		}

		return parent::_afterSave();
	}

	public function checkCronExpression($expr) {
		$parts = explode(' ', $expr);
		if(count($parts) != 5) {
			return sprintf('cron expression should have 5 parts, %d given', count($parts));
		}
		$min = explode('/', $parts[0]);
		if(count($min) > 1){
			return 'minute expression cannot be modular';
		}  elseif(intval($min[0]) < 0 || intval($min[0]) > 59 || preg_match('/[^0-9]/', $min[0])) {
			return 'minute expression needs to be an integer in range [0, 59]';
		}

		$hour = explode('/', $parts[1]);
		if(count($hour) > 2){
			return 'modular hour expression has too many modular parts';
		} elseif(count($hour) == 2){
			if(preg_match('/[^0-9]/', $hour[1])) {
				return 'modular part of hour expression needs to be an integer';
			}
			if($hour[0] != '*'){
				return 'first part of modular hour expression needs to be "*"';
			}
		} elseif(intval($hour[0]) < 0 || intval($hour[0]) > 23 || preg_match('/[^0-9]/', $hour[0])) {
			return 'non modular hour expression needs to be an integer in range [0, 23]';
		}

		$dom = explode('/', $parts[2]);
		if(count($dom) > 2){
			return 'modular day of month expression has too many parts';
		} elseif(count($dom) == 2){
			if(preg_match('/[^0-9]/', $dom[1])) {
				return 'modular part of day of month expression needs to be an integer';
			}
			if($dom[0] != '*'){
				return 'first part of modular day of month expression needs to be "*"';
			}
		} elseif(count($dom) == 1 && ( $dom[0] != '*' && (intval($dom[0]) < 1 || intval($dom[0]) > 31))) {
			return 'non modular day of month expression needs to be "*" or in range [1, 31]';
		}

		$mon = explode('/', $parts[3]);
		if(count($mon) > 2){
			return 'modular month expression has too many parts';
		} elseif(count($mon) == 2){
			if(preg_match('/[^0-9]/', $mon[1])){
				return 'modular part of month expression needs to be numeric';
			}
			if($mon[0] != '*'){
				return 'first part of modular month expression needs to be "*"';
			}
		} elseif(count($mon) == 1 && ($mon[0] != '*' && (intval($mon[0]) < 1 || intval($mon[0]) > 12))) {
			return 'non modular month expression needs to be "*" or in range [1, 12]';
		}

		$day = explode('/', $parts[4]);
		if(count($day) > 2){
			return 'modular day expression has too many parts';
		} elseif(count($day) == 2){
			if(preg_match('/[^0-9]/', $day[1])){
				return 'modular part of day expression needs to be numeric';
			}
			if($day[0] != '*'){
				return 'first part of modular day expression needs to be "*"';
			}
		} elseif( $day[0] != '*' && !preg_match('/^[0-6]{1}$/', $day[0])) {
			return 'non modular day expression needs to be "*" or in range [0, 6]';
		}

		return 'valid';
	}


}