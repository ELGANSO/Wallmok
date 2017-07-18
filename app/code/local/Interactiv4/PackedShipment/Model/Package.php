<?php
class Interactiv4_PackedShipment_Model_Package extends Mage_Core_Model_Abstract
{
    /**
     * Mage_Sales_Model_Order_Shipment de la que forma parte este package o bulto
     * @var Mage_Sales_Model_Order_Shipment
     */
    protected $_shipment;
    
    /**
     * Ids de los items empaquetados en este bulto
     * @var array
     */
    protected $_packedItemsIds;
    
    /**
     * Array con los items empaquetados en este bulto
     * @var array
     */
    protected $_packedItems;
    
    /*
     * Array con las cantidades de cada item en el bulto array( id1 => qty1 ...)
     */
    protected $_packedItemsQtys;
    /**
     * referencia, utilizada para generar las labels
     * @var string
     */
    protected $_ref;
    
    /**
     * Peso del bulto (suma de los items que lo componen)
     * @var float
     */
    protected $_weight;
    
    /**
     * Numero total de items que van en este paquete
     * @var unknown_type
     */
    protected $_totalItemsQty;
    
    /**
     * Precio total del bulto (suma de los precios de los items que lo componen)
     * @var float
     */
    protected $_price;
    
    
    /**
     * Construimos un package o bulto, forzando a recibir 2 parametros obligatorios
     * @param array $ids array con los ids de items que estan empaquetados en este bulto
     * @param Mage_Sales_Model_Order_Shipment $shipment de la que forma parte este bulto
     * @param String $ref de la que forma parte este bulto
     * @throws Exception
     */
    public function __construct(Mage_Sales_Model_Order_Shipment $shipment, array $ids, $ref = null)
    {
       
        if(empty($ids))
            throw new Exception("__CLASS__ : un package tiene que tener al menos un item. se ha pasado un array de itemsIds vacio");
            
        $this
            ->setShipment($shipment)    
            ->setPackedItemsIds($ids)
            ->setRef($ref);
    }
    
	/**
     * Establecemos los ids de los items que forman parte de este bulto
     * @param unknown_type $ids
     */
    public function setPackedItemsIds(array $ids)
    {
        $this->_packedItemsIds = $ids;
        return $this;
    }
    
    
    /**
     * Añade una coleccion de items de este bulto
     * @param unknown_type $items
     */
    public function setShipment(Mage_Sales_Model_Order_Shipment $shipment)
    {
        $this->_shipment = $shipment;
        return $this;
    }
    
	/**
     * Setter del ref que se utilizará para las etiquetas del paquete
     * @param string $ref
     */
    public function setRef($ref = null)
    {
        $this->_ref = ($ref) ? $ref : $this->_autocalculateRef();    
        return $this;
    }
    
    /**
     * Funcion que autocalcula un ref con los skus de los items que componen el bulto
     * separados por comas
     */
    protected function _autocalculateRef()
    {
        $items = $this->getPackedItems();
        $qtys = $this->_getPackedItemsQtys();
        $skus = array();
        foreach($items as $itemId => $item)
        {
            $tmp = $item->getSku();
            if($qtys[$itemId] > 1)
            	$tmp .= '(' . $qtys[$itemId] . ' uds.)';
            	
            $skus[] = $tmp;	
        }
        
        return implode(', ', $skus);
    }
    
    
    
    /**
     * 
     * Devuelve un array con los items de este bulto
     */
    public function getPackedItems()
    {
        if(!empty($this->_packedItems))
            return $this->_packedItems;
        
        $this->_packedItems = array();
        foreach($this->_packedItemsIds as $id)
        {
            $this->_packedItems[$id] = $this->getItemByProductId($id);
        }
        
        return $this->_packedItems;
    }
    
    /*
     * Devuelve un array de la forma array( <item_id> => <qty> ... )
     * Para cada item_id en el bulto, damos la cuantidad de ese item que 
     * contiene el bulto.
     */
    protected function _getPackedItemsQtys()
    {
        if(!empty($this->_packedItemsQtys))
            return $this->_packedItemsQtys;
        
        $this->_packedItemsQtys = array();
        foreach($this->_packedItemsIds as $id)
        {
            if (!array_key_exists($id, $this->_packedItemsQtys))
            {
                $this->_packedItemsQtys[$id] = 1;
            }
            else
            {
                $this->_packedItemsQtys[$id] ++;
            }
        }
        
        return $this->_packedItemsQtys;      
    }
    
    /**
     * Devuelve array con los item ids de este package
     */
    public function getPackedItemsIds()
    {
        return $this->_packedItemsIds;
    }
    
    
    public function getItemByProductId($productId)
    {
        foreach ($this->_shipment->getItemsCollection() as $item) {
            if ($item->getProductId()==$productId) {
                return $item;
            }
        }
        return false;
    }
    
    /**
     * Devuelve el peso del paquete. La primera vez lo calcula sumando pesos de items
     */
    public function getPackageWeight()
    {
        if(!empty($this->_weight))
            return $this->_weight;
        
        $this->_weight = (float) 0;   
        $qtys = $this->_getPackedItemsQtys();
        foreach($this->getPackedItems() as $itemId => $item)
        {
            $this->_weight += ($item->getWeight() * $qtys[$itemId]);
        }
        
        return $this->_weight;
    }
    
    public function getPackagePrice()
    {
        if(!empty($this->_price))
            return $this->_price;
        
        $this->_price = (float) 0;    
        $qtys = $this->_getPackedItemsQtys();
        foreach($this->getPackedItems() as $itemId => $item)
        {
            $this->_price += ($item->getPrice() * $qtys[$itemId]);
        }
        
        return $this->_price;
    }
   
    public function getTotalItemsQty()
    {
        if(!empty($this->_totalItemsQty))
            return $this->_totalItemsQty;
        $qtys = $this->_getPackedItemsQtys();
        $this->_totalItemsQty = 0;    
        foreach($qtys as $qty)
        {
            $this->_totalItemsQty += $qty;
        }
        
        return $this->_totalItemsQty;
    }
    public function getRef()
    {
        return $this->_ref;
    }
    
}
