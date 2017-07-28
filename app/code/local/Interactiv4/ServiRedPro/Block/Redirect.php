<?php
/**
 * ServiRedPro
 *
 * @category    Interactiv4
 * @package     Interactiv4_ServiRedPro
 * @copyright   Copyright (c) 2012 Interactiv4 SL. (http://www.interactiv4.com)
 */
class Interactiv4_ServiRedPro_Block_Redirect extends Mage_Core_Block_Template {
    const REDIRECT_TIMEOUT_DEFAULT_MS = 3000;
    
    const REDIRECT_MSG_DEFAULT = "You will be redirected to the ServiRed website in a few seconds.";
    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function getPostHtml() {
        parent::_construct();
        $standard = Mage::getModel('serviredpro/standard');
        $form = new Varien_Data_Form();
        $form->setAction($standard->getServiredUrl())
                ->setId('serviredpro_standard_checkout')
                ->setName('serviredpro_standard_checkout')
                ->setMethod('POST')
                ->setUseContainer(true);
        foreach ($standard->getStandardCheckoutFormFields() as $field => $value) {
            $form->addField($field, 'hidden', array('name' => $field, 'value' => $value));
        }
        $html = '<html>';
        $html .= '<head>';
        $html .= '<link rel="stylesheet" type="text/css"  href="'.$this->getSkinUrl("css/serviredpro.css").'">';
        $html .= '</head>';
        $html .= '<body>';
        $html.= '<div class="serviredpro-container">';
        $html.= $form->toHtml();
        $html.='</div>';
        $html.='<script type="text/javascript">setTimeout(function(){ document.getElementById("serviredpro_standard_checkout").submit();  }, ' . $this->getRedirectTimeOut() . '); </script>';
        $html.= '</body></html>';
        return $html;        
    }
    public function getRedirectLogo(){
        return Mage::getStoreConfig('payment/serviredpro/redirectlogo');
    }
    public function getRedirectSpinner(){
        return Mage::getStoreConfig('payment/serviredpro/loadingimage');
    }
    public function getRedirectMsg(){
        $redirectMsg = Mage::getStoreConfig('payment/serviredpro/redirectmsg');
        if (!$redirectMsg) {
            $redirectMsg = $this->__(self::REDIRECT_MSG_DEFAULT);
        }
        return $redirectMsg;
    }
    public function getRedirectTimeOut(){
        $redirectTimeoutMs = Mage::getStoreConfig('payment/serviredpro/redirecttimeout');
        if (!$redirectTimeoutMs && $redirectTimeoutMs !== "0" && $redirectTimeoutMs !== 0) {
            $redirectTimeoutMs = self::REDIRECT_TIMEOUT_DEFAULT_MS;
        }
        return $redirectTimeoutMs;
    }
}
