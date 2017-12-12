<?php

class Aitoc_Aitcheckout_Model_Save_Response
{

    protected $_data = array();

    public function addStepResponse($step, $response)
    {
        $this->_data[$step] = $response;

        return $this;
    }

    public function isValid()
    {
        return true;
    }

    public function toArray()
    {
        if ($this->isValid()) {
            return $this->_data;
        }
    }

}
