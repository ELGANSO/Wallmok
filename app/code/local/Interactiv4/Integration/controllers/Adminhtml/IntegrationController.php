<?php
/**
 * Integration
 *
 * @category    Interactiv4
 * @package     Interactiv4_Integration
 * @copyright   Copyright (c) 2013 Interactiv4 SL. (http://www.interactiv4.com)
 */

class Interactiv4_Integration_Adminhtml_IntegrationController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('i4integration/logs');
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}
    
    public function syncStockAction()
    {
        $model = Mage::getModel('i4integration/cron');
        $model->runSyncStock();
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('i4integration')->__('Sync Stock was executed.'));
        $this->_redirect('*/*');
    }
    
    public function syncPriceAction()
    {
        //$model = Mage::getModel('i4integration/cron');
        //$model->runSyncPrice();
	$shellScriptPath = sprintf('%s/shell/i4syncprice.php', Mage::getBaseDir());
+       $cmd = sprintf('%s > /dev/null 2>&1 &', $shellScriptPath);
+       exec($cmd);
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('i4integration')->__('Sync Price was executed.'));
        $this->_redirect('*/*');
    }
    
    public function syncCustomerAction()
    {
        $model = Mage::getModel('i4integration/cron');
        $model->runSyncCustomer();
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('i4integration')->__('Sync Customer was executed.'));
        $this->_redirect('*/*');
    }
    
    public function syncOrderAction()
    {
        $model = Mage::getModel('i4integration/cron');
        $model->runSyncOrder();
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('i4integration')->__('Sync Order was executed.'));
        $this->_redirect('*/*');
    }
    
    public function syncCatalogAction()
    {
        $shellScriptPath = sprintf('%s/shell/i4synccatalog.php', Mage::getBaseDir());
        $cmd = sprintf('%s > /dev/null 2>&1 &', $shellScriptPath);
        exec($cmd);
        //$model = Mage::getModel('i4integration/cron');
        //$model->runSyncCatalog();
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('i4integration')->__('Sync Catalog was executed.'));
        $this->_redirect('*/*');
    }
	
    public function gridAction() {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('i4integration/adminhtml_integration_grid')->toHtml()
        );
    }

	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('i4integration/logs');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $integrationIds = $this->getRequest()->getParam('integration');
        if(!is_array($integrationIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($integrationIds as $integrationId) {
                    $integration = Mage::getModel('i4integration/logs')->load($integrationId);
                    $integration->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($integrationIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	
    public function exportCsvAction()
    {
        $fileName   = 'i4integration.csv';
        $content    = $this->getLayout()->createBlock('i4integration/adminhtml_integration_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'i4integration.xml';
        $content    = $this->getLayout()->createBlock('i4integration/adminhtml_integration_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
    
    
    /* DEPRECATED */
    
    /**
     * Archivos
     */
    public function downloadAction()
    {
        if ($this->getRequest()->getParam('file')) {
            $filename = $this->getRequest()->getParam('file');
            $this->_prepareDownloadResponse($filename, null, 'application/octet-stream', filesize($this->_getFilesPath($this->getRequest()->getParam('uploaded')) . $filename));
            $this->getResponse()->sendHeaders();
            $this->_output($filename, $this->getRequest()->getParam('uploaded'));
            exit();
        }
        $this->_redirect('*/*/');
    }
    
    /**
     * Archivos
     */
    private function _output($filename, $uploaded)
    {
        $_path = $this->_getFilesPath($uploaded);
        if (!file_exists($_path . $filename)) {
            return ;
        }
        $ioAdapter = new Varien_Io_File();
        $ioAdapter->open(array('path' => $_path));
        $ioAdapter->streamOpen($filename, 'r');
        while ($buffer = $ioAdapter->streamRead()) {
            echo $buffer;
        }
        $ioAdapter->streamClose();
    }
    
    /**
     * Archivos
     */
    private function _getFilesPath($uploaded)
    {
        if ($uploaded == '1') {
            return Mage::getBaseDir() . Mage::getStoreConfig('export_order/upload/local') . DS . 'uploaded' . DS;
        } else {
            return Mage::getBaseDir() . Mage::getStoreConfig('export_order/upload/local') . DS;
        }
    }

}
