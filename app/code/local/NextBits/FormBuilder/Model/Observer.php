<?php
class NextBits_FormBuilder_Model_Observer{
	public function addMenu($observer){
		$block = $observer->getBlock();
		// Aaron Karol - Found the issue.  This condition is never met. 
		//if(get_class($block) == 'Mage_Adminhtml_Block_Page_Menu' || get_class($block) == 'Ess_M2ePro_Block_Adminhtml_Magento_Menu') {
			
			$parent = Mage::getSingleton('admin/config')->getAdminhtmlConfig()->getNode('menu');
			
			$formbuilderNode = $parent->children()->descend('formbuilder');
			
			$collection = Mage::getModel('formbuilder/formbuilder')
				->getCollection()
				->addFilter('menu','1');
			//$collection->getSelect()->order('name asc');
			
			$i=1;
			
			foreach($collection as $formbuilder){				
				$menuitem = new SimpleXMLElement('
					<formbuilder_'.$formbuilder->getId().' module="formbuilder">
						<title><![CDATA['.$formbuilder->getName().']]></title>
						<sort_order>'.($i++ * 10).'</sort_order>
						<action>formbuilder/adminhtml_results/index/formbuilder_id/'.$formbuilder->getId().'/</action>
					</formbuilder_'.$formbuilder->getId().'>
				');
				
				$formbuilderNode->descend('children')->appendChild($menuitem);
			}
						
			
		//}
	}
	
	
	public function addAssets($observer){
		
		$layout = $observer->getLayout();
		$update = $observer->getLayout()->getUpdate();
			
		/* if(in_array('cms_page', $update->getHandles())){
			
			$pageId = Mage::app()->getRequest()
				->getParam('page_id', Mage::app()->getRequest()->getParam('id', false));
			if(!empty($pageId)){
				$page = Mage::getModel('cms/page')->load($pageId);
				
				if(stristr($page->getContent(),'formbuilder/form')){
					Mage::helper('formbuilder')->addAssets($layout);
				}
			}else if(Mage::getBlockSingleton('page/html_header')->getIsHomePage())
			{
				Mage::helper('formbuilder')->addAssets($layout);
			}
		} */
		
		Mage::helper('formbuilder')->addAssets($layout);
		
	}
}
?>
