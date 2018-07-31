<?php

/**
 * Redsys Pro
 *
 * @category    Interactiv4
 * @package     Interactiv4_RedsysPro
 * @copyright   Copyright (c) 2015 Interactiv4 SL. (http://www.interactiv4.com)
 * @author      Oscar Salueña Martín <oscar.saluena@interactiv4.com> @osaluena
 * @author      David Slater
 */
class Interactiv4_RedsysPro_Block_Redirect extends Mage_Core_Block_Template {

    const REDIRECT_TIMEOUT_DEFAULT_MS = 3000;
    
    const REDIRECT_MSG_DEFAULT = "You will be redirected to the Redsys gateway in a few seconds.";

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

    }

    /**
     * Retrieve redirect post in order to sent to redsys POS (TPV)
     *
     * @return string
     */
    public function getPostHtml() {
        parent::_construct();
        /** @var Interactiv4_RedsysPro_Model_Standard $standard */
        $standard = Mage::getModel('i4redsyspro/standard');
        $form = new Varien_Data_Form();
        $form->setAction($standard->getRedsysUrl())
                ->setId('i4redsyspro_standard_checkout')
                ->setName('i4redsyspro_standard_checkout')
                ->setMethod('POST')
                ->setUseContainer(true);
        
        foreach ($standard->getStandardCheckoutFormFields($this->_getHelper()->isSHA256Configured()) as $field => $value) {
            $form->addField($field, 'hidden', array('name' => $field, 'value' => $value));
        }

        $html = '<html>';
        $html .= '<head>';
        $html .= '<link rel="stylesheet" type="text/css"  href="'.$this->getSkinUrl("css/i4redsys.css").'">';
        $html .= '</head>';
        $html .= '<body>';
        $html.= '<div class="i4redsyspro-container">';
        $html.= $form->toHtml();
        $html.='</div>';
        $html.= '</body></html>';

        return $html;        
    }

    /**
     * @return mixed
     */
    public function getRedirectLogo(){

        return Mage::getStoreConfig('payment/i4redsyspro/redirectlogo');

    }

    /**
     * @return mixed
     */
    public function getRedirectSpinner(){

        return Mage::getStoreConfig('payment/i4redsyspro/loadingimage');

    }

    /**
     * @return mixed|string
     */
    public function getRedirectMsg(){
        $redirectMsg = Mage::getStoreConfig('payment/i4redsyspro/redirectmsg');
        if (!$redirectMsg) {
            $redirectMsg = $this->__(self::REDIRECT_MSG_DEFAULT);
        }
        return $redirectMsg;

    }

    /**
     * @return int|mixed
     */
    public function getRedirectTimeOut(){
        $redirectTimeoutMs = Mage::getStoreConfig('payment/i4redsyspro/redirecttimeout');
        if (!$redirectTimeoutMs && $redirectTimeoutMs !== "0" && $redirectTimeoutMs !== 0) {
            $redirectTimeoutMs = self::REDIRECT_TIMEOUT_DEFAULT_MS;
        }
        return $redirectTimeoutMs;

    }

    /**
     * @return Interactiv4_RedsysPro_Helper_Data
     */
    protected function _getHelper() {
        return Mage::helper('i4redsyspro');
    }

}