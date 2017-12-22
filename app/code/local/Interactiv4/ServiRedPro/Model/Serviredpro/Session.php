<?php
/**
 * Se puede usar para guardar cierta información que tenemos que mantener en 
 * la bbdd cuando llamamos a servired, en vez de la sesión de php.
 * 
 * Este clase se debe crear como un singleton.
 *
 * @author davidslater
 */
class Interactiv4_ServiRedPro_Model_Serviredpro_Session extends Varien_Object {
   
    /* @var Mage_Sales_Model_Quote */
    protected $_quote = null;
    
    protected $_sessionId = null;
    
    /**
     * Se guarda el quote de la sesión actual en la sesión de servired.
     * @return boolean 
     */
    public function saveCurrentQuote() {
        return $this->_saveServiredSessionQuoteData('i4servired_session_id', $this->getServiredSessionId());
    }
    
    /**
     *
     * @return string
     */
    public function getServiredSessionId() {
        if (!isset($this->_sessionId)) {
            $currentSessionId = Mage::getSingleton('core/session')->getSessionId();
            $quote = $this->loadCurrentQuote();       
            $this->_sessionId =  $currentSessionId.$quote->getId();
        }
        $this->_getHelper()->log('DEBUG: servired session id = '.$this->_sessionId);
        return $this->_sessionId;
    }
    
    
    /**
     * Se guarda un dato contra el quote de la sesión especificada o la quote de 
     * la sesión actual si no se especifica.
     * @param string $key
     * @param mixed $value
     * @param string $sessionId
     * @return boolean 
     */
    protected function _saveServiredSessionQuoteData($key, $value, $sessionId = null)  {
        $quote = $this->loadCurrentQuote($sessionId);
        if (!$quote) {
            return false;
        }
        /* @var $quote Mage_Sales_Model_Quote */
        
        if (is_array($value)) {
            $value = serialize($value);
        }
        $quote->setData($key, $value)->save();
        return true;
    }
    
    
    /**
     * Devuelve el quote desde la sesión servired especificido. Si no especificamos una sesión
     * devuelve el quote de la sesión actual.
     * @param string $sessionId
     * @return Mage_Sales_Model_Quote
     */
    public function loadCurrentQuote($sessionId = null) {
        if (isset($sessionId)) {
            if  (!$this->_quote || (($this->_quote->getData('i4servired_session_id') != $sessionId))) {
                $quote = Mage::getModel('sales/quote')
                    ->getCollection()
                    ->addFieldToFilter('i4servired_session_id', $sessionId)
                    ->getLastItem(); 
                $this->_quote = $quote;               
            } 
        } else {
            if (!$this->_quote) {
                $lastOrderId = $this->_getCheckoutSession()->getLastOrderId();
                $quoteId = Mage::getModel('sales/order')->load($lastOrderId)->getQuoteId();
                $this->_quote = Mage::getModel('sales/quote')->load($quoteId);
            }
        }
        $quote = $this->_quote;
        if ($quote->getId()) {
            return $quote;
        } else {
            return false;
        }
    }    
    
    /**
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckoutSession() {
        return Mage::getSingleton('checkout/session');
    }
    
    /**
     * Se guarda el ID del último pedido de la sesión actual.
     * @return type 
     */
    public function saveLastOrderId() {
        $lastOrderId = $this->_getCheckoutSession()->getLastOrderId();
        return $this->_saveServiredSessionQuoteData('i4servired_last_order_id', $lastOrderId);
    }
    
    /**
     * Se devuelve el ID del último pedido de la sesión indicada.
     * @param string $sessionId
     * @return string 
     */
    public function loadLastOrderId($sessionId) {
        $lastOrderId = $this->_loadServiredSessionQuoteData($sessionId, 'i4servired_last_order_id');
        return $lastOrderId ? $lastOrderId : 0;
    }
    
    /**
     *
     * @param type $sessionId
     * @param type $key
     * @param type $isArray
     * @return mixed
     */
    protected function _loadServiredSessionQuoteData($sessionId, $key, $isArray = false) {
        $quote = $this->loadCurrentQuote($sessionId);
        if (!$quote) {
            return false;
        }
        $value = $quote->getData($key);
        if (!$value) {
            return false;
        } 
        
        if ($isArray) {         
            return unserialize($value);
        } else {
            return $value;
        }
    }
    
    /**
     * Se guardan los parámetros devueltos por servired
     * @param string $sessionId
     * @param array $params
     * @return boolean 
     */
    public function saveServiredProResponse($sessionId, $params) {
        return $this->_saveServiredSessionQuoteData('i4servired_params', $params, $sessionId);
    }
    
    /**
     * Se devuelven los parámetros servired de la sesión indicada.
     * @param string $sessionId
     * @return array
     */
    public function loadServiredProResponse($sessionId) {
        $params = $this->_loadServiredSessionQuoteData($sessionId, 'i4servired_params', true);
        return $params ? $params : array();
    }
    
    /**
     *
     * @return Interactiv4_ServiRedPro_Helper_Data 
     */
    protected function _getHelper() {
        return Mage::helper('serviredpro');
    }
    
    
}
?>
