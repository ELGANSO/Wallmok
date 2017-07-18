<?php
/**
 * Description of Banks
 *
 * @author davidslater
 */
class Interactiv4_ServiRedPro_Model_Config_Source_Banks {
    const BANK_DEFAULT = 'default';
    
    const BANK_BBVA = 'bbva';
    const BANK_LA_CAIXA = 'lacaixa';
    const BANK_CATALUNYA_CAIXA = 'catalunyacaixa';
    const BANK_BANKIA = 'bankia';
    const BANK_SABADELL = 'sabadell';
    const BANK_SANTANDER = 'santander';
    const BANK_DEUTSCHE_BANK = 'deutschebank';
    const BANK_BARCLAYS = 'barclays';
    const BANK_CAJAMAR = 'cajamar';
    const BANK_ING = 'ing';
    const BANK_BANK_INTER = 'bankinter';
    const BANK_CAJA_RURAL = 'cajarural';
    const BANK_BANESTO = 'banesto';
    
    public function toOptionArray() {
        return array(
            array(
                'value' => self::BANK_DEFAULT,
                'label' => $this->_getHelper()->__('-- Select your bank --')
            ),
            array(
                'value' => self::BANK_BANESTO,
                'label' => $this->_getHelper()->__('Banesto')                
            ),            
            array(
                'value' => self::BANK_BANK_INTER,
                'label' => $this->_getHelper()->__('Bank Inter')
            ),            
            array(
                'value' => self::BANK_BANKIA,
                'label' => $this->_getHelper()->__('Bankia')
            ),      
            array(
                'value' => self::BANK_BARCLAYS,
                'label' => $this->_getHelper()->__('Barclays')                
            ),            
            array(
                'value' => self::BANK_BBVA,
                'label' => $this->_getHelper()->__('BBVA')
            ),
            array(
                'value' => self::BANK_CAJA_RURAL,
                'label' => $this->_getHelper()->__('Caja Rural')                
            ),               
            array(
                'value' => self::BANK_CAJAMAR,
                'label' => $this->_getHelper()->__('Cajamar')                
            ),              
         
            array(
                'value' => self::BANK_CATALUNYA_CAIXA,
                'label' => $this->_getHelper()->__('Catalunya Caixa')
            ),        
            array(
                'value' => self::BANK_DEUTSCHE_BANK,
                'label' => $this->_getHelper()->__('Deutsche Bank')                
            ),       
            array(
                'value' => self::BANK_ING,
                'label' => $this->_getHelper()->__('ING')                
            ),               
            array(
                'value' => self::BANK_LA_CAIXA,
                'label' => $this->_getHelper()->__('La Caixa')
            ),     
            array(
                'value' => self::BANK_SABADELL,
                'label' => $this->_getHelper()->__('Sabadell')
            ),            
            
            array(
                'value' => self::BANK_SANTANDER,
                'label' => $this->_getHelper()->__('Santander')                
            ),            
            
            
            
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
