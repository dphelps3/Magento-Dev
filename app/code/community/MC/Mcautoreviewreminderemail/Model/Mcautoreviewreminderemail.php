<?php

/*
 * @category    Community
 * @package     MC_Mcautoreviewreminderemail
 * @Document    Mcautoreviewreminderemail.php
 * @Created on  April 11, 2012, 7:05 PM
 * @copyright   Copyright (c) 2012 Magento Complete
 */

class MC_Mcautoreviewreminderemail_Model_Mcautoreviewreminderemail extends Mage_Core_Model_Abstract {

   const REVIEW_EMAIL_IDENTITY                 = 'mcautoreviewreminderemail_section/mcautoreviewreminderemail_group/mcautoreviewreminderemail_identity';
   
   const REVIEW_MAIL_ENABLE                    = 'mcautoreviewreminderemail_section/mcautoreviewreminderemail_group/mcautoreviewreminderemail_enable';
   
   const REVIEW_MAIL_DURATION                  = 'mcautoreviewreminderemail_section/mcautoreviewreminderemail_group/mcautoreviewreminderemail_duration';
   
   const REVIEW_MAIL_TEMPLATE                  = 'mcautoreviewreminderemail_section/mcautoreviewreminderemail_group/mcautoreviewreminderemail_email_template';
   
   const REVIEW_MAIL_ORDER_STATUS              = 'mcautoreviewreminderemail_section/mcautoreviewreminderemail_group/mcautoreviewreminderemail_order_status';  

   protected $_enabled                         = NULL;
   
   protected $_identity                        = NULL;
   
   protected $_delay                           = NULL;
   
   //protected $_email_subject                 = NULL;
   
   protected $_email_template                  = NULL;
   
   protected $_order_status                    = NULL;
   
   //protected $_Identity                      = NULL;

   /*
    * @method getSeconds()
    * @param $day [Number of days]
    * @return $three_days_second [time in second of x days before]
    * @discription:The method is mainly manipulate the time. It get day(s)
    * as parameter and manipulate it into seconds then returns the number of
    * days before time in seconds.
    */

   public function getSeconds($days=0)
   {
       $now = now();	   
       $now_seconds = strtotime($now);	   
       $three_days_second = (60 * 60 * 24 * $days);	   
       //$timestamp_three_days_before_seconds = $now_seconds - $three_days_second;	   
       return $three_days_second;
   }   
   /*
    * @method getOrderHistory()
    * @param $order [An order object]
    * @return $flag [bololean true/false]
    * @discription:The method is mainly manipulate the history of review mail . 
	* if any review history present in order then it returns true else false  .
    */
	
   public function getOrderHistory($order){
		$flag = false;
        If($_history = $order->getVisibleStatusHistory())
		{
			foreach($_history as $_historyItem)
			{
				$messMatch = explode(' date', $_historyItem->getComment());
														
				if($messMatch[0] === "Customer Notified :: Automatic Review Reminder Email sent on")
				{
					$flag= true;
				}
			}							
		}		
		return $flag;
   }

   /*
    * @method getFromMail()
    * @param $identity [string]
    * @return $from [array]
    * @discription:The method is mainly get the Store configuration trens_email settings.
    */
   public function getFromMail($identity){
		
		switch($identity)
		{
			case 'general':
				$from_email = Mage::getStoreConfig('trans_email/ident_general/email');//fetch sender email
				$from_name = Mage::getStoreConfig('trans_email/ident_general/name');//fetch sender name
				break;
			case 'sales':
				$from_email = Mage::getStoreConfig('trans_email/ident_sales/email');//fetch sender email
				$from_name = Mage::getStoreConfig('trans_email/ident_sales/name');//fetch sender name
				break;
			case 'support':
				$from_email = Mage::getStoreConfig('trans_email/ident_support/email');//fetch sender email
				$from_name = Mage::getStoreConfig('trans_email/ident_support/name');//fetch sender name
				break;
			case 'custom1':
				$from_email = Mage::getStoreConfig('trans_email/ident_custom1/email');//fetch sender email
				$from_name = Mage::getStoreConfig('trans_email/ident_custom1/name');//fetch sender name
				break;
			case 'custom2':
				$from_email = Mage::getStoreConfig('trans_email/ident_custom2/email');//fetch sender email
				$from_name = Mage::getStoreConfig('trans_email/ident_custom2/name');//fetch sender name
				break;
		}
		$from = array("name" => $from_name, "email" => $from_email);
		
		return $from;
   }
// Getting order collection  based on order history 

  public function OrderCollection()
  {
      $tbl_History  =  Mage::getSingleton('core/resource')->getTableName('sales_flat_order_status_history');		
	  $Collection = Mage::getModel('sales/order')->getCollection();
	  $Collection->getSelect()->join( array('addr' => Mage::getSingleton('core/resource')->getTableName('sales_flat_order_status_history')),
       'main_table.entity_id = addr.parent_id', array('main_table.*' , 'EnititId' => 'addr.entity_id'  , 'statusUpdationTime' => 'addr.created_at') );		      
        return   $Collection;
  }
  
    /*
    * @method sendReviewLinkEmail
    * @param
    * @return
    * @discription: The method is mainly responcible for
    */
	 
   public function sendReviewLinkEmail() {          
       $this->_enabled = Mage::getStoreConfig(self::REVIEW_MAIL_ENABLE, Mage::app()->getStore()); // Review Enable field			   
       $this->_identity = Mage::getStoreConfig(self::REVIEW_EMAIL_IDENTITY, Mage::app()->getStore()); // Email Identity field		
       $this->_delay = (int) Mage::getStoreConfig(self::REVIEW_MAIL_DURATION, Mage::app()->getStore()); // Delay field		
       $this->_email_template = Mage::getStoreConfig(self::REVIEW_MAIL_TEMPLATE, Mage::app()->getStore()); // Email Subjec field
       $this->_order_status   = Mage::getStoreConfig(self::REVIEW_MAIL_ORDER_STATUS, Mage::app()->getStore()); // Order Status field
       $this->_identity       = Mage::getStoreConfig(self::REVIEW_EMAIL_IDENTITY, Mage::app()->getStore());	  // Review Email field 
       $mailTemplate          = Mage::getModel('core/email_template');	   
       $template_collection   = $mailTemplate->load($this->_email_template);       
       $template_data         = $template_collection->getData();       
	 
	 // If extension is enable 
      if($this->_enabled){   	 
		$orders = $this->OrderCollection();
		$orders->addAttributeToSort('statusUpdationTime', 'desc');
          if(!isset($order_entity)):	$order_entity = array();endif;	
		  if(!isset($Status_Enityid)):	$Status_Enityid = array();endif;
		  foreach ($orders->getData() as $values):
				if($values['status'] ==  $this->_order_status):
				   if(!in_array($values['entity_id'], $order_entity )):			    
					   $order_entity[]    = $values['entity_id'];  // Filtering  order's entity id  of last updated
					   $Status_Enityid[]  = $values['EnititId'];   // Filtering entity id  of last updated
				   endif;	
				endif;		
		  endforeach;
		
		$collection = $this->OrderCollection();	  
	    $collection->addAttributeToFilter('addr.entity_id', array('in' => $Status_Enityid))
		->addAttributeToSort('addr.created_at', 'desc')	;	
	
	      foreach ($collection as $order){  	   		
    	  	    $now = time();            // Current Time 
     		    $Order_StatusUpdatedate =  strtotime($order->getData('statusUpdationTime'));  // Converting Order update date
     		    $datediff               =  $now - $Order_StatusUpdatedate;                    // Difference between cureent time to status updation time 
      	        $Order_numberofday      =  floor($datediff/(60*60*24));                       // Getting number of days of order Difference 	 	
				$id                     =  $order->getCreatedAt();	 	 
			    $order_Id               =  $order->getRealOrderId();			  
			    $product_name           = array();
                $_ReviewUrl             = array();			 
				  try	  
					{	 	  
						if( ($Order_numberofday == $this->_delay || $Order_numberofday == ($this->_delay+1) ) ){	
					  // for each order Checking slected Status update time (Number of day  between delay set in admin setting of Review reminder)						     
						 	$flag = $this->getOrderHistory($order);  	      // Getiing is customer is notified 
							if(!$flag){                                       // If Customer has not noitfied before 											
							$email = $order->getCustomerEmail(); 	          // Customer Email								
							$name  = $order->getBillingAddress()->getName();   // Customer Name
							if($items = $order->getAllItems())
							{					   
								foreach($items as $item)
								{							
								 $_product = Mage::getModel('catalog/product')->load($item->getProductId());
								  if($item)
									{ 	
                                     if(!$item->getData('parent_item_id')):
									 $product_name[] = $item->getName();
									 $_ReviewUrl[] = $this->generateReviewUrl($item->getProductId());
                                     endif;
									}
								}
							}	 							
							//Concatanating Ordered Products name by ', '						 
							$product    =   implode(", ", $product_name);
							$ReviewUrl  =   implode("<br/><br/>", $_ReviewUrl);
							$message    = $template_data['template_text'];						
							$message    = str_replace("{{ReviewURL}}",   $ReviewUrl, $message);		  // Replacing {{ReviewURL}} with the product review url		
							$message    = str_replace("{{OrderNumber}}", $order_Id, $message);		  // Replacing {{OrderNumber}} with the Order id		
							$message    = str_replace("{{ProductName}}", $product, $message);	      // Replacing {{ProductName}} with the Origenal products name			 
							$translate  = Mage::getSingleton('core/translate');
							$templateId = $this->_email_template;
							
							// Checking for Email Template 
							if(!empty($template_data)){
								$templateId = $template_data['template_id'];
								$mailSubject = $template_data['template_subject']; // Get Mail Subjest
								//fetch sender data from Admin end > System > Configuration > Store Email Addresses
								$from = $this->getFromMail($this->_identity);							
								$billingAddress = $order->getBillingAddress();
								$ReviewUrl  =   implode("\n", $_ReviewUrl);
								$mail = new Zend_Mail();							
								$mail->setBodyHTML($message , 'UTF-8',Zend_Mime::ENCODING_8BIT);							
								$mail->setFrom($from['email'], $from['name']);							
								$mail->addTo($order->getCustomerEmail(), $billingAddress->getFirstname() . ' ' . $billingAddress->getLastname());
								$mail->setSubject($mailSubject , 'UTF-8',Zend_Mime::ENCODING_8BIT);				  // setting subject line 		
						  		if($mail->send()){								
									$order->addStatusHistoryComment('Customer Notified :: Automatic Review Reminder Email sent on date '.date("Y-m-d H:i:s", time()), $this->_order_status)
												 ->setIsVisibleOnFront(true)
												 ->setIsCustomerNotified(true);								
									              $order->save();
									             }										   					
							} 
														
						}   // End If Customer has not noitfied before 
						  
					}	 // End if block for number of days				
					
				} // end Try block 
			  catch (Exception $e) 
				{  
					throw new Exception( 'Something really gone wrong', 0, $e);
				}  // end catch block
					  
		  }  
		 
		 
		  // end for each block 
        }  // End if extension is not enabled
    }
	
   /*
    * @method generateReviewUrl
    * @param Product id
    * @return ReviewUrl
    * @discription: The method is mainly responcible for ReviewUrl
    */
	 
	public function generateReviewUrl ($_reviewProductId) { 
    $ReviewUrl  = Mage::getUrl('review/product/list', array('id'=>$_reviewProductId));
	return $ReviewUrl;
	}
	
}
