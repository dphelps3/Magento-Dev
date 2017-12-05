<?php

class Magestore_Megamenu_IndexController extends Mage_Core_Controller_Front_Action
{
	public function previewAction(){
		$this->loadLayout();
		$this->renderLayout();
	}
}