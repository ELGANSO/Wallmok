<?php
/**
 * Description of Emaildownloader
 *
 * @author davidslater
 */
class Interactiv4_EmailDownloader_Model_Emaildownloader {
    const TYPE_POP3 = 'POP3';
    const TYPE_IMAP = 'IMAP';
    const CRITERIAKEY_TEXT_CONTAINS = 'text_contains';
    /**
     *
     * @var Zend_Mail_Storage_Abstract 
     */
    protected $_zendMailStorage = null;
    
    /**
     *
     * @var int|boolean
     */
    protected $_currentMessageIdx = false;
    
    protected $_messages = array();
    /**
     *
     * @param string $type (TYPE_POP3 or TYPE_IMAP)
     * @param type $host 
     * @param type $username
     * @param type $password
     * @param type $port
     * @param type $ssl
     * @param string &$errorMessage
     * @return boolean 
     */
    public function initMailBox($type, $host, $username, $password, $port, $ssl, &$errorMessage) {
        try {
            $zendMailStorage = null;
            $params = array('host' => $host, 'user' => $username, 'password' => $password, 'port' => $port, 'ssl' => $ssl);
            switch ($type) {
                case self::TYPE_POP3:
                    $zendMailStorage = new Zend_Mail_Storage_Pop3($params);
                    break;
                case self::TYPE_IMAP:
                    $zendMailStorage = new Zend_Mail_Storage_Imap($params);
                    break;
                default:
                    throw new Exception("Invalid mailbox type: $type");
            }
            $this->_setZendMailStorage($zendMailStorage);
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            return false;
        }
        return true;
    }
    /**
     *
     * @param array $criteria
     * @return int|boolean 
     */
    public function findFirstMessage(array $criteria) {
        $foundMessages = $this->findMessages($criteria);
        return (count($foundMessages) > 0) ? $foundMessages[0] : false;
    }
    /**
     *
     * @param array $criteria
     * @return array (de indices de los mensajes encontrados)
     */
    public function findMessages(array $criteria) {
        $foundIndexes = array();
        $messageCount = $this->_getZendMailStorage()->countMessages();
        for ($messageIdx = 1; $messageIdx <= $messageCount; $messageIdx++) {
            $matchesCriteria = true;
            foreach ($criteria as $criteriaKey => $value) {
                if (!$this->messageMatchesCriteria($messageIdx, $criteriaKey, $value)) {
                    $matchesCriteria = false;
                    break;
                }
            }
            if ($matchesCriteria) {
                $foundIndexes[] = $messageIdx;
            }
        }
        return $foundIndexes;
    }
    /**
     *
     * @param int $messageIdx
     * @return boolean 
     */
    public function deleteMessage($messageIdx) {
        if (!$this->_getMessageByIndex($messageIdx)) {
            return false;
        }
        $uniqueId = $this->_getZendMailStorage()->getUniqueId($messageIdx);
        if ($uniqueId) {
            $messageId =  $this->_getZendMailStorage()->getNumberByUniqueId($uniqueId);
        } else {
            $messageId = null;
        }
        $messageId = $messageId ? $messageId : $messageIdx;
        try {
            $this->_getZendMailStorage()->removeMessage($messageId);
        } catch (Exception $e) {
            $this->_log("Failed to delete message {$e->getMessage()}.", __FUNCTION__ );
            return false;
        }
        return true;
    }
    /**
     *
     * @param int $messageIdx
     * @param string $critriaKey (uno de los constantes CRITERIAKEY_ ...
     * @param type $value
     * @return boolean 
     */
    public function messageMatchesCriteria($messageIdx, $critriaKey, $value) {
        switch ($critriaKey) {
            case self::CRITERIAKEY_TEXT_CONTAINS :
                return $this->messageContainsText($messageIdx, $value);
                break;
            default:
                return false;
        }
    }
    /**
     *
     * @param int $messageIdx
     * @param string $text
     * @return boolean 
     */
    public function messageContainsText($messageIdx, $text) {
        $messageText = $this->getMessageText($messageIdx);
        $position = strpos($messageText, $text);
        return $position === false ? false : true;
    }
    /**
     *
     * @param int $messageIdx
     * @return string|boolean 
     */
    public function getMessageText($messageIdx) {
        $zendMessage = $this->_getMessageByIndex($messageIdx);
        if (!$zendMessage) {
            return false;
        }
        $foundPart = null;
        foreach (new RecursiveIteratorIterator($zendMessage) as $part) {
            try {
                if (strtok($part->contentType, ';') == 'text/plain') {
                    $foundPart = $part;
                    break;
                }
            } catch (Zend_Mail_Exception $e) {
                $this->_log($e->getMessage(), __FUNCTION__);
            }
        }
        if ($foundPart) {
            return (string) $foundPart;
        } else {
            $content = $zendMessage->getContent();
            return is_string($content) ? $content : false;
        }
    }
    
    
    /**
     *
     * @param int $messageIdx
     * @return string|boolean 
     */
    public function getSenderEmailAddress($messageIdx) {
        $zendMessage = $this->_getMessageByIndex($messageIdx);
        if (!$zendMessage) {
            return false;
        }
        if (isset($zendMessage->from)) {
            $sender = strtolower($zendMessage->from);
            $emailAddressInAngleBrackets = null;
            $isFoundEmailInAngleBrackets = preg_match_all('/<(.*)>/', $sender, $emailAddressInAngleBrackets);
            if ($isFoundEmailInAngleBrackets && is_array($emailAddressInAngleBrackets) && (count($emailAddressInAngleBrackets) > 1) && is_array($emailAddressInAngleBrackets[1]) && (count($emailAddressInAngleBrackets[1]) > 0)) {
                $sender = $emailAddressInAngleBrackets[1][0];
            }
        } else {
            $sender = false;
        }
        return $sender ? $sender : false;
    }
    
    /**
     *
     * @param int $messageIdx
     * @return string|bool
     */
    public function getSenderEmailAddressDomain($messageIdx) {
        $senderEmailAddress = $this->getSenderEmailAddress($messageIdx);
        if (!$senderEmailAddress) {
            return false;
        }
        $addressParts = explode('@', $senderEmailAddress);
        if (count($addressParts) > 1) {
            return $addressParts[1];
        } else {
            return $addressParts[0];
        }
    }
    /**
     *
     * @param int $messageIdx
     * @return Zend_Mail_Message|boolean 
     */
    protected function _getMessageByIndex($messageIdx) {
        $this->_checkInitialized(__FUNCTION__);
        if (!array_key_exists($messageIdx, $this->_messages)) {
            try {
                $zendMessage = $this->_getZendMailStorage()->getMessage($messageIdx);
            } catch (Exception $e) {
                $zendMessage = false;
            }
            $this->_messages[$messageIdx] = $zendMessage;
        }
        return $this->_messages[$messageIdx];
    }
    /**
     *
     * @param  $functionName
     * @return boolean
     * @throws Exception 
     */
    protected function _checkInitialized($functionName = '') {
        if (!$this->_getZendMailStorage()) {
            throw new Exception($functionName . ': Mailbox is not initialized. Call initMailbox first.');
        }
        return true;
    }
    /**
     *
     * @param mixed $zendMailStorage
     * @return \Interactiv4_EmailDownloader_Model_Emaildownloader 
     */
    protected function _setZendMailStorage(Zend_Mail_Storage_Abstract $zendMailStorage) {
        $this->_zendMailStorage = $zendMailStorage;
        return $this;
    }
    /**
     *
     * @return type Zend_Mail_Storage_Abstract
     */
    public function _getZendMailStorage() {
        return $this->_zendMailStorage;
    }
    /**
     *
     * @param string $message
     * @return \Interactiv4_EmailDownloader_Model_Emaildownloader 
     */
    protected function _log($message, $function = '') {
        $this->_getHelper()->log("$function: $message");
        return $this;
    }
    /**
     *
     * @return Interactiv4_EmailDownloader_Helper_Data
     */
    protected function _getHelper() {
        return Mage::helper('i4emaildownloader');
    }
    
    /**
     * Se devuelve el id del siguiente correo, o el más reciente se 
     * se llama por la primera vez. Cuando no hay más correos se devuelve false.
     * @return int|false
     */
    public function getNextMessage() {
        $this->_checkInitialized(__FUNCTION__);
        if ($this->_currentMessageIdx) {
            $this->_currentMessageIdx --;
            $this->_currentMessageIdx = $this->_currentMessageIdx > 0 ? $this->_currentMessageIdx : false;
        } else {
            $messageCount =  $this->_getZendMailStorage()->countMessages();
            $this->_currentMessageIdx = $messageCount ? $messageCount : false;            
        }
        return $this->_currentMessageIdx;
    }
    
    /* public function getSenderIP($messageIdx) {
        
    }
    
    public function checkSpf($messageIdx) {
        $zendMessage = $this->_getMessageByIndex($messageIdx);
        if (!$zendMessage) {
            return false;
        }
        
    } */
    
    
}

