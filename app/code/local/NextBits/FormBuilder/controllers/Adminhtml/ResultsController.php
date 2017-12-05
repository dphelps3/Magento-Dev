<?php
class NextBits_FormBuilder_Adminhtml_ResultsController
	extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction()
	{
		$this->loadLayout()
			->_setActiveMenu('formbuilder/formbuilder');
		$this->_addBreadcrumb(Mage::helper('formbuilder')->__('Results'), Mage::helper('adminhtml')->__('Results'));
		return $this;
	}
	
	public function indexAction(){
		$this->_initAction();
		$this->renderLayout();
	}
	
	
	public function gridAction()
	{
		$this->getResponse()->setBody(
			$this->getLayout()->createBlock('formbuilder/adminhtml_results_grid')->toHtml()
		);
	}	
	
	public function massDeleteAction(){
		$Ids = (array)$this->getRequest()->getParam('id');
		
		try {
			foreach($Ids as $id){
				$result = Mage::getModel('formbuilder/formbuilderresult')->load($id);
				$result->delete();
			}

			$this->_getSession()->addSuccess(
				$this->__('Total of %d record(s) have been deleted.', count($Ids))
			);
		}
		catch (Mage_Core_Model_Exception $e) {
			$this->_getSession()->addError($e->getMessage());
		}
		catch (Mage_Core_Exception $e) {
			$this->_getSession()->addError($e->getMessage());
		}
		catch (Exception $e) {
			$this->_getSession()->addException($e, $this->__('An error occurred while updating records.'));
		}

		$this->_redirect('*/*/',array('formbuilder_id' => $this->getRequest()->getParam('form_id')));
		
	}
	
	public function massApproveAction($approveStatus = 1){
		$Ids = (array)$this->getRequest()->getParam('id');
		try {
			foreach($Ids as $id){
				$result = Mage::getModel('formbuilder/formbuilderresult')->load($id);
				$result->setApproved(intval($approveStatus));
				$result->save();
			}

			$this->_getSession()->addSuccess(
				$this->__('Total of %d result(s) have been updated.', count($Ids))
			);
		}
		catch (Mage_Core_Model_Exception $e) {
			$this->_getSession()->addError($e->getMessage());
		}
		catch (Mage_Core_Exception $e) {
			$this->_getSession()->addError($e->getMessage());
		}
		catch (Exception $e) {
			$this->_getSession()->addException($e, $this->__('An error occurred during operation.'));
		}

		$this->_redirect('*/*/',array('formbuilder_id' => $this->getRequest()->getParam('form_id')));
		
	}
	
	public function massDisapproveAction(){
		$this->massApproveAction(0);
	}
	
	public function exportCsvAction(){
        $fileName   = 'results.csv';
        $grid       = $this->getLayout()->createBlock('formbuilder/adminhtml_results_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    public function exportXmlAction(){
        $fileName   = 'results.xml';
        $grid       = $this->getLayout()->createBlock('formbuilder/adminhtml_results_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }
	
	
}
?>
