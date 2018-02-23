<?php
class OrderLineSerializer
{
    public function serialize(\Mage_Catalog_Model_Product $product, $productsList)
    {	
    	$data =[];

		$options = $product->getTypeInstance(true)->getOptionsCollection($product);
				
				$data[] = [
	    			'pvp_base' => $product->getData('price')
	    		];

		foreach ($options as $option) {
			//Mage::log($productsList,null,"ivan.log");
			$items = $this->serializeOrderItem($option, $productsList);
			$data[] = [
				'exclusivo' => $this->getExclusivo($option['type']),
				'grupo' => $option['default_title'],
				'opciones' => $items
			];

		}


        return $data;
    }

    public function serializeOrderItem($option, $productsList)
    {
        $data = [];
        $items = Mage::getResourceModel('bundle/selection_collection')->setOptionIdsFilter($option['option_id']);

        foreach ($items as $item) {
        	//Mage::log($item,null,"json.log");
        	$product = Mage::getModel('catalog/product')->load($item['product_id']);
  
   			$data[] = [
   				"defecto" => $this->parserBool($item['is_default']),
   				"on" => $this->parserBool($this->in_array($product->getSku(),array_values($productsList))),
   				"opcion" => $product->getSku(),
   				"pvp" => $item['selection_price_value']
   			];
         }

         return $data;
    }

   	private function getExclusivo($type){
   		if($type == 'radio')
   			$ret = 'S';
   		else
   			$ret = 'N';
   		return $ret;
   	}

   	private function parserBool($bool){
   		if($bool)
   			$ret = 'S';
   		else
   			$ret = 'N';
   		return $ret;
   	}

   	private function in_array($s, $items){

   		foreach($items as $item)
   		{
   			if($item["sku"] == $s)
   				return true;
   		}

   		return false;
   	}
}