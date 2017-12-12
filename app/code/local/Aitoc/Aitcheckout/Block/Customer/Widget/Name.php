<?php

class Aitoc_Aitcheckout_Block_Customer_Widget_Name extends Mage_Customer_Block_Widget_Name
{
    public function _construct()
    {
        parent::_construct();

        $this->setTemplate('aitcheckout/customer/widget/name.phtml');
    }

    private $_showAmount = null;

    public function suffixBlockAmount()
    {
        if ($this->_showAmount == null) {
            $this->_showAmount = ($this->showPrefix() ? 1 : 0) + ($this->showSuffix() ? 1 : 0) + ($this->showMiddlename(
                ) ? 1 : 0);
        }

        return $this->_showAmount;
    }

}
