<?php
/*
 * Created on Jul 25, 2011
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 class Interactiv4_Mrw_Model_Shipping_Carrier_Mrw_Source_Method
{
    public function toOptionArray($isMultiselect=false)
    {
        $method = array(
        	//'0000' => Mage::helper('i4mrwes')->__('MRW Urgente 10'),
        	'0000' => Mage::helper('i4mrwes')->__('0000 - Entrega antes de las 10:00 h'),
        	'0005' => Mage::helper('i4mrwes')->__('0005 - MRW Urgente Hoy'),
            '0010' => Mage::helper('i4mrwes')->__('0010 - MRW Promociones'),
            '0100' => Mage::helper('i4mrwes')->__('0100 - MRW Urgente 12'),
            '0200' => Mage::helper('i4mrwes')->__('0200 - MRW Urgente 19'),
            '0210' => Mage::helper('i4mrwes')->__('0210 - MRW Urgente 19 más de 40Kg.'),
            '0220' => Mage::helper('i4mrwes')->__('0220 - MRW 48hrs. Portugal'),
            '0230' => Mage::helper('i4mrwes')->__('0230 - MRW Bag 19'),
            '0235' => Mage::helper('i4mrwes')->__('0235 - MRW Bag 14'),
            '0300' => Mage::helper('i4mrwes')->__('0300 - MRW Económico'),
            '0310' => Mage::helper('i4mrwes')->__('0310 - MRW Económico más de 40Kg.'),
            '0350' => Mage::helper('i4mrwes')->__('0350 - MRW Económico Interinsular'),
            '0400' => Mage::helper('i4mrwes')->__('0400 - MRW Express Documentos'),
            '0450' => Mage::helper('i4mrwes')->__('0450 - MRW Express 2Kg.'),
            '0480' => Mage::helper('i4mrwes')->__('0480 - MRW Caja Express 3Kg.'),
            '0490' => Mage::helper('i4mrwes')->__('0490 - MRW Documentos 14'),
            //'0800' => Mage::helper('i4mrwes')->__('MRW Ecommerce'),
            '0800' => Mage::helper('i4mrwes')->__('Entrega de 9:00 a 19:00 h'),
        );
        
        if($isMultiselect) {
        	$options = array();
        	foreach($method as $k=>$v){
        		$options[] = array('label' => $v, 'value'=>$k);
        	}
        	return $options;
        }
        
        return $method;
    }
}

