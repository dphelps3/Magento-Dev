<?php

class Simplesolutions_CabinetCoupons_Adminhtml_CodesController extends Mage_Adminhtml_Controller_Action
{
		protected function _initAction()
		{
				$this->loadLayout()->_setActiveMenu("cabinetcoupons/codes")->_addBreadcrumb(Mage::helper("adminhtml")->__("Codes  Manager"),Mage::helper("adminhtml")->__("Codes Manager"));
				return $this;
		}
		public function indexAction() 
		{
			    $this->_title($this->__("CabinetCoupons"));
			    $this->_title($this->__("Manager Codes"));

				$this->_initAction();
				$this->renderLayout();
		}
		public function editAction()
		{			    
			    $this->_title($this->__("CabinetCoupons"));
				$this->_title($this->__("Codes"));
			    $this->_title($this->__("Edit Item"));
				
				$id = $this->getRequest()->getParam("id");
				$model = Mage::getModel("cabinetcoupons/codes")->load($id);
				if ($model->getId()) {
					Mage::register("codes_data", $model);
					$this->loadLayout();
					$this->_setActiveMenu("cabinetcoupons/codes");
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Codes Manager"), Mage::helper("adminhtml")->__("Codes Manager"));
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Codes Description"), Mage::helper("adminhtml")->__("Codes Description"));
					$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
					$this->_addContent($this->getLayout()->createBlock("cabinetcoupons/adminhtml_codes_edit"))->_addLeft($this->getLayout()->createBlock("cabinetcoupons/adminhtml_codes_edit_tabs"));
					$this->renderLayout();
				} 
				else {
					Mage::getSingleton("adminhtml/session")->addError(Mage::helper("cabinetcoupons")->__("Item does not exist."));
					$this->_redirect("*/*/");
				}
		}

		public function newAction()
		{

		$this->_title($this->__("CabinetCoupons"));
		$this->_title($this->__("Codes"));
		$this->_title($this->__("New Item"));

        $id   = $this->getRequest()->getParam("id");
		$model  = Mage::getModel("cabinetcoupons/codes")->load($id);

		$data = Mage::getSingleton("adminhtml/session")->getFormData(true);
		if (!empty($data)) {
			$model->setData($data);
		}

		Mage::register("codes_data", $model);

		$this->loadLayout();
		$this->_setActiveMenu("cabinetcoupons/codes");

		$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);

		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Codes Manager"), Mage::helper("adminhtml")->__("Codes Manager"));
		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Codes Description"), Mage::helper("adminhtml")->__("Codes Description"));


		$this->_addContent($this->getLayout()->createBlock("cabinetcoupons/adminhtml_codes_edit"))->_addLeft($this->getLayout()->createBlock("cabinetcoupons/adminhtml_codes_edit_tabs"));

		$this->renderLayout();

		}
		public function saveAction()
		{

			$post_data=$this->getRequest()->getPost();
			Mage::log($post_data, null, 'aaron_post_data.log');
			Mage::log($post_data['cat_id'], null, 'aaron_post_data.log');
			$post_data['cabinet_ids'] = implode(',',$post_data['cat_id']);

				if ($post_data) {

					try {

						

						$model = Mage::getModel("cabinetcoupons/codes")
						->addData($post_data)
						->setId($this->getRequest()->getParam("id"))
						->save();

						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Codes was successfully saved"));
						Mage::getSingleton("adminhtml/session")->setCodesData(false);

						if ($this->getRequest()->getParam("back")) {
							$this->_redirect("*/*/edit", array("id" => $model->getId()));
							return;
						}
						/* Create the Coupon */
						
						$from_date = $post_data['start_date'];
						$to_date = $post_data['end_date'];
						$data = array(
							'product_ids' => null,
							'name' => $post_data['name'],
							'description' => null,
							'is_active' => 1,
							'website_ids' => array(1),
							'customer_group_ids' => array(1,2,3),
							'coupon_type' => 2,
							'coupon_code' => $post_data['name'],
							'uses_per_coupon' => null,
							'uses_per_customer' => 0,
							'from_date' => $from_date,
							'to_date' => $to_date,
							'sort_order' => null,
							'is_rss' => 0,
							'rule' => array(
								'conditions' => array(
									array(
										'type' => 'salesrule/rule_condition_combine',
										'aggregator' => 'all',
										'value' => 1,
										'new_child' => null
									)
								)
							),
							'simple_action' => 'cart_fixed',
							'discount_amount' => 300,
							'discount_qty' => 0,
							'discount_step' => null,
							'apply_to_shipping' => 1,
							'simple_free_shipping' => 0,
							'stop_rules_processing' => 0,
							'rule' => array(
								'actions' => array(
									array(
										'type' => 'salesrule/rule_condition_product_combine',
										'aggregator' => 'all',
										'value' => 1,
										'new_child' => null
									)
								)
							),
							'store_labels' => array('Free Cabinet Shipping (Up to $300)')
						);
						$model = Mage::getModel('salesrule/rule');
						$data = $this->_filterDates($data, array('from_date', 'to_date'));
						$validateResult = $model->validateData(new Varien_Object($data));
						if ($validateResult == true) {
							if (isset($data['simple_action']) && $data['simple_action'] == 'by_percent'
									&& isset($data['discount_amount'])) {
								$data['discount_amount'] = min(100, $data['discount_amount']);
							}
							if (isset($data['rule']['conditions'])) {
								$data['conditions'] = $data['rule']['conditions'];
							}
							if (isset($data['rule']['actions'])) {
								$data['actions'] = $data['rule']['actions'];
							}
							unset($data['rule']);
							$model->loadPost($data);
							$model->save();
						}
						
						/* Coupon Created */
						$this->_redirect("*/*/");
						return;
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						Mage::getSingleton("adminhtml/session")->setCodesData($this->getRequest()->getPost());
						$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
					return;
					}

				}
				$this->_redirect("*/*/");
		}



		public function deleteAction()
		{
				if( $this->getRequest()->getParam("id") > 0 ) {
					try {
						$cmodel = Mage::getModel("cabinetcoupons/codes")->load($this->getRequest()->getParam("id"));
						$name = $cmodel->getData("name");
						//Mage::log('delete', null, 'aaron_cab_coupon.log');
						//Mage::log($model->getData("name"), null, 'aaron_cab_coupon.log');
						$model = Mage::getModel("cabinetcoupons/codes");
						$model->setId($this->getRequest()->getParam("id"))->delete();
						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item was successfully deleted"));
						$smodel = Mage::getModel('salesrule/rule')
							->getCollection()
							->addFieldToFilter('name', array('eq'=>$name))
							->getFirstItem();
						$smodel->delete();
						$this->_redirect("*/*/");
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
					}
				}
				
				$this->_redirect("*/*/");
		}

		
}
