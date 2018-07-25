<?php
/**
 * Created by PhpStorm.
 * User: magentouser
 * Date: 07/18/2014
 * Time: 3:53 PM
 */



class Hawksearch_Proxy_Adminhtml_HawkproxyvalidationController
    extends Mage_Adminhtml_Controller_Action {
    public function validateRouteStringAction() {
		/** @var Hawksearch_Proxy_Adminhtml_HawkproxyvalidationController $this */
        $response = array('valid' => false);

        $helper = Mage::helper('hawksearch_proxy');
		$helper->setStore($this->getRequest()->getParam('store'));
        if($helper->isValidSearchRoute($this->getRequest()->getParam('routeString'))){
            $response['valid'] = true;
        }

        $this->getResponse()
            ->setHeader('Content-Type', 'application/json')
            ->setBody(json_encode($response));
    }
    public function indexAction() {
//        $response = array("error" => false);
//
//        try {
//            $disabledFuncs = explode(',', ini_get('disable_functions'));
//            $isShellDisabled = is_array($disabledFuncs) ? in_array('shell_exec', $disabledFuncs) : true;
//            $isShellDisabled = (stripos(PHP_OS, 'win') === false) ? $isShellDisabled : true;
//
//            if($isShellDisabled) {
//                $response['error'] = 'This installation cannot run one off category synchronizations because the PHP function "shell_exec" has been disabled. Please use cron.';
//            } else {
//                $helper = Mage::helper('hawksearch_proxy/data');
//                if(strtolower($this->getRequest()->getParam('force')) == 'true') {
//                    $helper->removeSyncLocks();
//                }
//                $helper->launchSyncProcess();
//            }
//        }
//        catch (Exception $e) {
//            Mage::logException($e);
//            $response['error'] = "An unknown error occurred.";
//        }
//        $this->getResponse()
//            ->setHeader("Content-Type", "application/json")
//            ->setBody(json_encode($response));
    }
} 