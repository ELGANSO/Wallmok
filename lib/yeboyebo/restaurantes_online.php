<?php 
require_once (Mage::getBaseDir().'/app/Mage.php');
//require_once('restaurante.php');
require_once('restauranteSirtpv.php');
class RestaurantesOnline {

	private $restaurants;
	private $conexion;

	/* Constructor*/

	public function __construct(){

		Mage::app();
		$db = Mage::getSingleton('core/resource');
		$this->setConexion($db);

	}
	/* Métodos*/
	public function getRestaurantes($codPostal)
	{
		$readConnection = $this->conexion->getConnection('core_read');
		$query = "SELECT * FROM restaurantes WHERE activo = 1 ";
		$restaurants = $readConnection->fetchAll($query);
		$restaurantsOnline = array();

		foreach ($restaurants as $restaurant) {
			$restaurant = new Restaurante($restaurant);
			$restaurant->isAvailable();
			//Si que hay cod Postal, busco restaurantes con conexión que lo admiten
			$codigos = $this->getPostalCodes($restaurant->getID());
			//Compruebo si el cod postal esta permitido en este restaurante
			if(in_array($codPostal,array_values($codigos))){
				array_push($restaurantsOnline, $restaurant->getDescription());
			}

		}
		return $restaurantsOnline;
	}

	public function getAllRestaurantes()
	{
		$readConnection = $this->conexion->getConnection('core_read');
		$query = "SELECT * FROM restaurantes WHERE activo = 1 ";
		$restaurants = $readConnection->fetchAll($query);
		$restaurantsOnline = array();

		foreach ($restaurants as $restaurant) {
			$restaurant = new Restaurante($restaurant);
			$restaurant->isAvailable();
			array_push($restaurantsOnline, $restaurant->getDescription());
		}
		return $restaurantsOnline;
	}
	
	public function selectRestaurant($id,$envio){
		 
		$session = Mage::getSingleton('core/session', array('name' => 'frontend'));
		$session->setData("restaurant",$id);
		$session->setData("envio",$envio);  
	}

	public function getRestaurant($id){
		$readConnection = $this->conexion->getConnection('core_read');
		$query = "SELECT * FROM restaurantes WHERE id =". $id;
		$restaurant = new Restaurante($readConnection->fetchAll($query)[0]);
		return $restaurant;
	}
	public function getRestaurantAddress($id){
		$readConnection = $this->conexion->getConnection('core_read');
		$query = "SELECT id, descripcion,direccion,ciudad,provincia,codpostal,pais,codtienda,telefono FROM restaurantes WHERE id =". $id;
		return $readConnection->fetchAll($query)[0];
	}
	protected function setConexion($conexion){
		$this->conexion = $conexion;
	}

	protected function getPostalCodes($id){
		$query = "SELECT codpostal FROM codPostalesPermitidos WHERE idrestaurante =". $id;
		$readConnection = $this->conexion->getConnection('core_read');
		return $readConnection->fetchCol($query);
	}
	public function getCodComanda($OrderId)
	{
		$query = "SELECT codcomanda FROM comandas WHERE increment_id ='".$OrderId."'";
		$readConnection = $this->conexion->getConnection('core_read');
		return $readConnection->fetchCol($query)[0];
	}
}


?>