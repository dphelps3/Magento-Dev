<?php
class Mage_Review_Ajax_ProductController extends Mage_Core_Controller_Front_Action
{
    public function viewAction()
    {
        $this->loadLayout(false);
        $this->renderLayout();
    }
}
?>