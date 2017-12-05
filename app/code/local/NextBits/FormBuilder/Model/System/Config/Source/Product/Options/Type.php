<?php
class NextBits_FormBuilder_MOdel_System_Config_Source_Product_Options_Type
{
    const FORMBUILDER_OPTIONS_GROUPS_PATH = 'global/nextbits/formbuilder/options/custom/groups';

    public function toOptionArray()
    {
        $groups = array(
            array('value' => '', 'label' => Mage::helper('formbuilder')->__('-- Please select --'))
        );

        $helper = Mage::helper('catalog');

        foreach (Mage::getConfig()->getNode(self::FORMBUILDER_OPTIONS_GROUPS_PATH)->children() as $group) {
            $types = array();
            $typesPath = self::FORMBUILDER_OPTIONS_GROUPS_PATH . '/' . $group->getName() . '/types';
            foreach (Mage::getConfig()->getNode($typesPath)->children() as $type) {
                $labelPath = self::FORMBUILDER_OPTIONS_GROUPS_PATH . '/' . $group->getName() . '/types/' . $type->getName()
                    . '/label';
                $types[] = array(
                    'label' => $helper->__((string) Mage::getConfig()->getNode($labelPath)),
                    'value' => $type->getName()
                );
            }

            $labelPath = self::FORMBUILDER_OPTIONS_GROUPS_PATH . '/' . $group->getName() . '/label';

            $groups[] = array(
                'label' => $helper->__((string) Mage::getConfig()->getNode($labelPath)),
                'value' => $types
            );
        }

        return $groups;
    }
}
