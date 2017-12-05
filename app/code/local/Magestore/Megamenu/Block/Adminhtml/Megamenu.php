<?php

class Magestore_Megamenu_Block_Adminhtml_Megamenu extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct(){
		$this->_controller = 'adminhtml_megamenu';
		$this->_blockGroup = 'megamenu';
		$this->_headerText = Mage::helper('megamenu')->__('Menu Manager');
		$this->_addButtonLabel = Mage::helper('megamenu')->__('Add Menu Item');
		$cache = Mage::getStoreConfig('megamenu/general/cache');

		$this->_addButton('Refresh Menu Cache', array(
			'label'     => 'Refresh Menu Cache',
			'onclick'   => 'setLocation(\'' . $this->getUrl('adminhtml/megamenu/rebuildAll') .'\')',
			'class'     => 'refresh',
		),0,3);
		if($cache == Magestore_Megamenu_Model_Status::STATUS_ENABLED){
			$label = 'Disable Cache';
			$className = 'disable';
		}else{
			$label = 'Enable Cache';
			$className = 'enable';
		}
		$this->_addButton($label, array(
			'label'     => $label,
			'onclick'   => 'setLocation(\'' . $this->getUrl('adminhtml/megamenu/changeCache',array('status'=>$cache)) .'\')',
			'class'     => $className,
		),0,2);
		parent::__construct();
	}
	public function getButtonsHtml($area = null)
	{
		$cache = Mage::getStoreConfig('megamenu/general/cache');
		if($cache == Magestore_Megamenu_Model_Status::STATUS_ENABLED){
			$text = '<h3 style="margin-top:0">Mega Menu Cache Status: <span style="background:#3CB861;padding: 3px 15px;color: #fff;font-size: 11px;border-radius: 7px;">ENABLED</span></h3>';
		}else{
			$text = '<h3 style="margin-top:0">Mega Menu Cache Status: <span style="background:#E41101;padding: 3px 15px;color: #fff;font-size: 11px;border-radius: 7px;">DISABLED</span></h3>';
		}
		$out = $text;
		foreach ($this->_buttons as $level => $buttons) {
			$_buttons = array();
			foreach ($buttons as $id => $data) {
				$_buttons[$data['sort_order']]['id'] = $id;
				$_buttons[$data['sort_order']]['data'] = $data;
			}
			ksort($_buttons);
			foreach ($_buttons as $button) {
				$id = $button['id'];
				$data = $button['data'];
				if ($area && isset($data['area']) && ($area != $data['area'])) {
					continue;
				}
				$childId = $this->_prepareButtonBlockId($id);
				$child = $this->getChild($childId);

				if (!$child) {
					$child = $this->_addButtonChildBlock($childId);
				}
				if (isset($data['name'])) {
					$data['element_name'] = $data['name'];
				}
				$child->setData($data);

				$out .= $this->getChildHtml($childId);
			}
		}
		return $out;
	}
}