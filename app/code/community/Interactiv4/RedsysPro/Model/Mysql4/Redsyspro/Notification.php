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
class Interactiv4_RedsysPro_Model_Mysql4_Redsyspro_Notification extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('i4redsyspro/redsyspro_notification', 'id');
    }

}