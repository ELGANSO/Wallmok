<?php 
 
class Chapagain_GoogleTagManager_Block_Gtm extends Mage_Core_Block_Template
{			
	public function getIsHomePage() 
	{
		$routeName = Mage::app()->getRequest()->getRouteName();
		$identifier = Mage::getSingleton('cms/page')->getIdentifier();	  

		if($routeName == 'cms' && $identifier == 'home') {
			return true;
		} else {
			return false;
		}
	}
	
	public function getIsOrderSuccessPage()
	{
		if (strpos(Mage::app()->getRequest()->getPathInfo(), '/checkout/onepage/success') !== false) {
			return true;
		}
		return false;
	}
	
	public function getIsCartPage() 
	{
		if (strpos(Mage::app()->getRequest()->getPathInfo(), '/checkout/cart') !== false) {
			return true;
		}
		return false;
	}
	
	public function getIsCheckoutPage()
	{
		if (strpos(Mage::app()->getRequest()->getPathInfo(), '/checkout/onepage') !== false) {
			return true;
		}
		return false;
	}
	
	public function getCurrentProduct()
	{	
		return Mage::registry('current_product');		
	}
	
	public function getCurrentCategory()
	{	
		return Mage::registry('current_category');		
	}
	
	public function getOrder()
    {   	
		if ($this->getIsOrderSuccessPage()) {
			$orderId = Mage::getSingleton('checkout/session')->getLastOrderId();		
			$order = Mage::getModel('sales/order')->load($orderId);
			if (!$order) {
				return false;
			}			
			return $order;
		}
		return false;
    }
    
    public function getDataLayerProduct()
    {
		if ($product = $this->getCurrentProduct()) {			
			
			$categoryCollection = $product->getCategoryCollection();
			
			$categories = array();
			foreach ($categoryCollection as $category) {
				$categories[] = Mage::getModel('catalog/category')->load($category->getEntityId())->getName();
			}			
			
			$objProduct = new stdClass();
			$objProduct->name = $product->getName();
			$objProduct->id = $product->getSku();
			$objProduct->price = Mage::getModel('directory/currency')->formatTxt($product->getPrice(), array('display' => Zend_Currency::NO_SYMBOL));
			$objProduct->category = implode('|', $categories);
			
			$objEcommerce = new stdClass();			
			$objEcommerce->ecommerce = new stdClass();
			$objEcommerce->ecommerce->detail = new stdClass();
			$objEcommerce->ecommerce->detail->actionField = new stdClass();
			$objEcommerce->ecommerce->detail->actionField->list = 'Catalog';
			$objEcommerce->ecommerce->detail->products = $objProduct;
									
			/*$objAddToCart = new stdClass();
			$objAddToCart->event = 'addToCart';
			$objAddToCart->ecommerce = new stdClass();
			$objAddToCart->ecommerce->add = new stdClass();
			$objAddToCart->ecommerce->add->products = $objProduct;*/
									
			$pageCategory = json_encode(array('pageCategory' => 'product-detail'), JSON_PRETTY_PRINT);
			
			$dataScript = PHP_EOL;
			
			$dataScript .= '<script type="text/javascript">'.PHP_EOL.'dataLayer = [' . $pageCategory . '];'.PHP_EOL.'</script>';
			
			$dataScript .= PHP_EOL.PHP_EOL;
			
			$dataScript .= '<script type="text/javascript">'.PHP_EOL.'dataLayer.push('. json_encode($objEcommerce, JSON_PRETTY_PRINT) . ');'.PHP_EOL.'</script>';
			
			//$dataScript .= PHP_EOL.PHP_EOL;
			
			//$dataScript .= '<script type="text/javascript">'.PHP_EOL.'dataLayer.push('. json_encode($objAddToCart, JSON_PRETTY_PRINT) . ');'.PHP_EOL.'</script>';
			
			return $dataScript;
		}
	}
	
	public function getDataLayerOrder()
	{
		if ($order = $this->getOrder()) {
									
			$aItems = array();
			$productItems = array();			
			foreach ($order->getAllVisibleItems() as $item) {
				
				$categoryCollection = $item->getProduct()->getCategoryCollection();
				$categories = array();
				foreach ($categoryCollection as $category) {
					$categories[] = Mage::getModel('catalog/category')->load($category->getEntityId())->getName();
				}
				
				$productItem = array();
				$productItem['name'] = $item->getName();			  
				$productItem['id'] = $item->getSku();
				$productItem['price'] = Mage::getModel('directory/currency')->formatTxt($item->getBasePrice(), array('display' => Zend_Currency::NO_SYMBOL));
				$productItem['category'] = implode('|', $categories);
				$productItem['quantity'] = intval($item->getQtyOrdered()); // converting qty from decimal to integer
				$productItem['coupon'] = '';
				$productItems[] = (object) $productItem;

				$objItem = array();
				$objItem['sku'] = $item->getSku();
				$objItem['name'] = $item->getName();
				$objItem['category'] = implode('|', $categories);
				$objItem['price'] = Mage::getModel('directory/currency')->formatTxt($item->getBasePrice(), array('display' => Zend_Currency::NO_SYMBOL));
				$objItem['quantity'] = intval($item->getQtyOrdered()); // converting qty from decimal to integer
				$aItems[] = (object) $objItem;
			}
			
			$objOrder = new stdClass();
			
			$objOrder->transactionId = $order->getIncrementId();
			$objOrder->transactionAffiliation = Mage::app()->getStore()->getFrontendName();
			$objOrder->transactionTotal = Mage::getModel('directory/currency')->formatTxt($order->getBaseGrandTotal(), array('display' => Zend_Currency::NO_SYMBOL));
			$objOrder->transactionTax = Mage::getModel('directory/currency')->formatTxt($order->getBaseTaxAmount(), array('display' => Zend_Currency::NO_SYMBOL));
			$objOrder->transactionShipping = Mage::getModel('directory/currency')->formatTxt($order->getBaseShippingAmount(), array('display' => Zend_Currency::NO_SYMBOL));
			
			$objOrder->transactionProducts = $aItems;
						
			$objOrder->ecommerce = new stdClass();
			$objOrder->ecommerce->purchase = new stdClass();
			$objOrder->ecommerce->purchase->actionField = new stdClass();
			$objOrder->ecommerce->purchase->actionField->id = $order->getIncrementId();
			$objOrder->ecommerce->purchase->actionField->affiliation = Mage::app()->getStore()->getFrontendName();
			$objOrder->ecommerce->purchase->actionField->revenue = Mage::getModel('directory/currency')->formatTxt($order->getBaseGrandTotal(), array('display' => Zend_Currency::NO_SYMBOL));
			$objOrder->ecommerce->purchase->actionField->tax = Mage::getModel('directory/currency')->formatTxt($order->getBaseTaxAmount(), array('display' => Zend_Currency::NO_SYMBOL));
			$objOrder->ecommerce->purchase->actionField->shipping = Mage::getModel('directory/currency')->formatTxt($order->getBaseShippingAmount(), array('display' => Zend_Currency::NO_SYMBOL));
			$coupon = $order->getCouponCode();
			$objOrder->ecommerce->purchase->actionField->coupon = $coupon == null ? '' : $coupon;
			
			$objOrder->ecommerce->products = $productItems;
						
			$pageCategory = json_encode(array('pageCategory' => 'order-success'), JSON_PRETTY_PRINT);
			
			$dataScript = PHP_EOL;
			
			$dataScript .= '<script type="text/javascript">'.PHP_EOL.'dataLayer = [' . $pageCategory . '];'.PHP_EOL.'</script>';
			
			$dataScript .= PHP_EOL.PHP_EOL;
			
			$dataScript .= '<script type="text/javascript">'.PHP_EOL.'dataLayer.push('. json_encode($objOrder, JSON_PRETTY_PRINT) . ');'.PHP_EOL.'</script>';		
			
			return $dataScript;		
		}
	}
}

?>
