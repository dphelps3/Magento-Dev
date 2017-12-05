<?php
class Abc_Helloworld_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
      echo 'Hello Index';
    }

    public function goodbyeAction() {
      echo 'Goodbye world!';
    }

    public function packersAction() {
      echo 'I want a David Bakhtiari jersey';
    }

    public function paramsAction() {
      echo '<dl>';
      foreach($this->getRequest()->getParams() as $key=>$value) {
        echo '<dt><strong>Param: </strong>' . $key . '</dt>';
        echo '<dl><strong>Value: </strong>' . $value . '</dl>';
      }
        echo '</dl>';
    }

}
