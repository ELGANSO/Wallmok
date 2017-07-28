<?php
class Interactiv4_Mrw_Model_Backend_Tablerate extends Mage_Core_Model_Config_Data
{
    public function _afterSave()
    {
    	Mage::getResourceModel('i4mrwes/carrier_tablerate')->uploadAndImport($this);
    }
}
