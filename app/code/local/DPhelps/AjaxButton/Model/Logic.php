<?php

/* DPhelps_AjaxButton_Model_Logic */

class DPhelps_AjaxButton_Model_Logic {

    public function priceThreshold($price) {
        switch ($price) {
            case $price < 50:
                $result = 'cheap';
                break;
            case $price < 200:
                $result = 'ok';
                break;
            case $price < 500:
                $result = 'expensive';
                break;
            default:
                $result = 'not for me';
        }
        return $result;
    }
}