<?php
/**
 * Antes de pintar la página, cambiamos nuestros template personalizados por las versiones 
 * adecuadas para la versión actual de Magento.
 * Por ejemplo, se hemos reemplazado un template en el layout y la versión 
 * del layout funciona con versión 1.6 de Magento pero no con 1.7, aquí 
 * reemplazamos nuestro template con la versión para 1.7.
 *
 * @author david.slater@interactiv4.com
 */
class Interactiv4_Mrw_Model_Observer_Versioncompatibletemplates {
    /**
     * Se devuelve true is la versión actual de Magento es iqual o mayor
     * que la versión pasada.
     * @param int $major
     * @param int $minor
     * @param int $revision
     * @param int $patch
     * @return boolean 
     */
    protected function _isMagentoVersionGreaterOrEqualTo($major, $minor, $revision, $patch) {
        $versionInfo = Mage::getVersionInfo();
        if ((int) $major > (int) $versionInfo['major']) {
            return false;
        } elseif ((int) $minor > (int) $versionInfo['minor']) {
            return false;
        } elseif ((int) $revision > (int) $versionInfo['revision']) {
            return false;
        } elseif ((int) $patch > (int) $versionInfo['patch']) {
            return false;
        } else {
            return true;
        }
    }
    /**
     * Método invocado por el evento controller_action_layout_generate_blocks_after
     * Antes de pintar la página, cambiamos nuestros template personalizados por las versiones 
     * adecuadas para la versión actual de Magento.
     * Por ejemplo, se hemos reemplazado un template en el layout y la versión 
     * del layout funciona con versión 1.6 de Magento pero no con 1.7, aquí 
     * reemplazamos nuestro template con la versión para 1.7.
     * @param Varien_Event_Observer $observer 
     */
    public function setCorrectTemplatesForMagentoVersion(Varien_Event_Observer $observer) {
        $layout = Mage::getSingleton('core/layout');  /* @var $layout Mage_Core_Model_Layout */
        
        if ($this->_isMagentoVersionGreaterOrEqualTo(1, 7, 0, 0)) { // Bloques específicos para Magento versión 1.7
            
            
            
            $checkoutOnePageBillingBlock = $layout->getBlock('checkout.onepage.billing');
            if ($checkoutOnePageBillingBlock && ($checkoutOnePageBillingBlock->getTemplate() == 'i4mrwes/checkout/onepage/billing.phtml')) {
                $checkoutOnePageBillingBlock->setTemplate('i4mrwes/checkout/onepage/billing_17.phtml');
            }
            
            $checkoutOnePageShippingBlock = $layout->getBlock('checkout.onepage.shipping');
            if ($checkoutOnePageShippingBlock && ($checkoutOnePageShippingBlock->getTemplate() == 'i4mrwes/checkout/onepage/shipping.phtml')) {
                $checkoutOnePageShippingBlock->setTemplate('i4mrwes/checkout/onepage/shipping_17.phtml');
            }
            
            $customerAddressEditBlock = $layout->getBlock('customer_address_edit');
            if ($customerAddressEditBlock && ($customerAddressEditBlock->getTemplate() == 'i4mrwes/customer/address/edit.phtml')) {
                $customerAddressEditBlock->setTemplate('i4mrwes/customer/address/edit_17.phtml');
            }
            
            $customerFormRegisterBlock = $layout->getBlock('customer_form_register');
            if ($customerFormRegisterBlock && ($customerFormRegisterBlock->getTemplate() == 'i4mrwes/customer/form/register.phtml')) {
                $customerFormRegisterBlock->setTemplate('i4mrwes/customer/form/register_17.phtml');
            }
            
            /*$adminSalesOrderViewInfoBlock = $layout->getBlock('order_info');
            if ($adminSalesOrderViewInfoBlock && ($adminSalesOrderViewInfoBlock->getTemplate() == 'i4addressgmap/sales/order/view/info.phtml')) {
                $adminSalesOrderViewInfoBlock->setTemplate('i4addressgmap/sales/order/view/info_17.phtml');
            }
            
            $adminSalesOrderViewShipmentTracking = $layout->getBlock('shipment_tracking');
            if ($adminSalesOrderViewShipmentTracking && ($adminSalesOrderViewShipmentTracking->getTemplate() == 'i4mrwes/sales/order/shipment/view/tracking.phtml')) {
                $adminSalesOrderViewShipmentTracking->setTemplate('i4mrwes/sales/order/shipment/view/tracking_17.phtml');
            } */
        }
    }
}
?>
