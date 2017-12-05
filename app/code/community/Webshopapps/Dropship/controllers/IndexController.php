<?php


/**
 * @category   Webshopapps
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */
class Webshopapps_Dropship_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/dropship?id=15 
    	 *  or
    	 * http://site.com/dropship/id/15 	
    	 */
    	/* 
		$dropship_id = $this->getRequest()->getParam('id');

  		if($dropship_id != null && $dropship_id != '')	{
			$dropship = Mage::getModel('dropship/dropship')->load($dropship_id)->getData();
		} else {
			$dropship = null;
		}	
		*/
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($dropship == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$dropshipTable = $resource->getTableName('dropship');
			
			$select = $read->select()
			   ->from($dropshipTable,array('dropship_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$dropship = $read->fetchRow($select);
		}
		Mage::register('dropship', $dropship);
		*/

			
		$this->loadLayout();     
		$this->renderLayout();
    }
}