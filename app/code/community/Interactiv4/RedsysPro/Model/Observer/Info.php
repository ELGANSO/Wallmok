<?php
/**
 * Redsys Pro
 *
 * @category    Interactiv4
 * @package     Interactiv4_RedsysPro
 * @copyright   Copyright (c) 2015 Interactiv4 SL. (http://www.interactiv4.com)
 * @author      Oscar Salueña Martín <oscar.saluena@interactiv4.com> @osaluena
 */
class Interactiv4_RedsysPro_Model_Observer_Info {

    public function changeTemplateIfNeeded(Varien_Event_Observer $observer) {
        $block = $observer->getEvent()->getBlock();

        if ($block instanceof Interactiv4_RedsysPro_Block_Info) {
            $parentBlock = $block->getParentBlock();
            if (isset($parentBlock) && ($parentBlock instanceof Interactiv4_RedsysPro_Block_Adminhtml_Sales_Order_Payment || $parentBlock instanceof Interactiv4_ServiRedPro_Block_Adminhtml_Sales_Order_Payment))
            {
                $block->setTemplate('payment/info/i4redsyspro.phtml');
            }
            else {
                $block->setTemplate('i4redsyspro/payment/info/i4redsyspro.phtml');
            }
        }
    }
}