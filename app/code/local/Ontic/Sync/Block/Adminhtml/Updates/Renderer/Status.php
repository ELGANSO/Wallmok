<?php

class Ontic_Sync_Block_Adminhtml_Updates_Renderer_Status extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $status = $row['status'];
        $statuses = Ontic_Sync_Model_Product_Update::getStatusLabels();

        if(array_key_exists($status, $statuses))
        {
            return $statuses[$status];
        }

        return 'Desconocido';
    }
}