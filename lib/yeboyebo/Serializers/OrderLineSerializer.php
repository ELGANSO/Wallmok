 <?php
class OrderLineSerializer
{
    public function serialize(\Mage_Catalog_Model_Product $item, $productsList)
    {	
    	$data =[];

		$options = $item->getProduct()->getTypeInstance(true)->getOptionsCollection($item->getProduct());
				
				$data[] = [
	    			'pvp_base' => $item->getProduct()->getData('price')
	    		];


		foreach ($options as $option) {
			//Mage::log($productsList,null,"ivan.log");
			$items = $this->serializeOrderItem($option, explode("-",$item->getSku()));

			$data[] = [
				'exclusivo' => $this->getExclusivo($option['type']),
				'grupo' => $option['default_title'],
				'obligatorio' => 'N',
				'opciones' => $items
			];

		}

	    //throw new Exception("No guardar");
        return $data;
    }

    public function serializeOrderItem($option, $productsList)
    {
        $data = [];
        $items = Mage::getResourceModel('bundle/selection_collection')->setOptionIdsFilter($option['option_id']);

        foreach ($items as $item) {
        	//Mage::log($item,null,"json.log");
        	$product = Mage::getModel('catalog/product')->load($item['product_id']);

	        Mage::log("\n Producs list \n".$product->getSku()." - ".in_array($product->getSku(),array_values($productsList)),null,"ivan.log");
	        Mage::log($productsList,null,"ivan.log");

   			$data[] = [
   				"defecto" => $this->parserBool($item['is_default']),
			    "descbreve" => "",
   				"on" => $this->parserBool(in_array($product->getSku(),array_values($productsList))),
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

}