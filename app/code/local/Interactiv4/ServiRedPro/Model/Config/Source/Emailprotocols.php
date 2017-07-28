<?php
/**
 * Description of Emailprotocols
 *
 * @author davidslater
 */
class Interactiv4_ServiRedPro_Model_Config_Source_Emailprotocols {
    const EMAIL_PROTOCOL_POP3 = 'POP3';
    const EMAIL_PROTOCOL_IMAP = 'IMAP';
    
    public function toOptionArray() {
        return array(
            array(
                'value' => self::EMAIL_PROTOCOL_POP3,
                'label' => $this->_getHelper()->__('POP3')
            ),
            array(
                'value' => self::EMAIL_PROTOCOL_IMAP,
                'label' => Mage::helper('adminhtml')->__('IMAP')
            )
        );
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
