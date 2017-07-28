<?php
class Interactiv4_ServiRedPro_Block_Checkout_Onepage extends Mage_Checkout_Block_Onepage
{
    protected function _prepareLayout()
    {
		if(!$this->getRequest()->isXmlHttpRequest()){
			// Add payment "form" Javascripts and Stylesheets
    		$this->getLayout()->getBlock('head')
    		->addItem('skin_js', 'js/ServiRedPro.minified.js');
		}
        return $this;
    }
}
