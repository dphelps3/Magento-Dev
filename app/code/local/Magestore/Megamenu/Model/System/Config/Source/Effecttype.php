<?php

class Magestore_Megamenu_Model_System_Config_Source_Effecttype

{
    const  Fade = 1;
    const  Slide = 2;
    const  Toggle = 3;
    public function toOptionArray(){
        return array(
            array('value'=>self::Fade, 'label'=>'Fade'),
            array('value'=>self::Slide, 'label'=>'Slide'),
            array('value'=>self::Toggle, 'label'=>'Toggle')
        );
    }
}