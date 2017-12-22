<?php
/**
 * Interfaz que debe cumplir cualquier shipping carrier que soporte packed shipments.
 * @author david.slater@interactiv4.com
 */
interface Interactiv4_PackedShipment_Model_Carrier_Interface {
    /**
     * Establecemos la tienda para usar para las siguientes llamadas
     * @param $store
     * @return mixed
     */
    function setCurrentStore($store);
    /**
     * Devolvemos la tienda actual para usar en las llamdas de esta interfaz
     * @return mixed
     */
    function getCurrentStore();
    
    /**
     * Se devuelve true si el carrier soporate validacion de direcciones contra
     * códigos postales en el país especificado.
     * @param string $countryId 
     * @return bool
     */
    function supportsAddressValidation($countryId);
    
    /*
     * Se deveulve true si la combinación de población y código postal es válida.
     * @param string $city
     * @param string $postcode
     * @param string &$errorMsg - En el caso de un error, devuelve el mensaje.     * 
     * @return bool
     */
    public function isValidCityPostcode($city, $postcode, &$errorMsg);
 
    /*
     * Se devuelve la lista de códigos postales que sean válidos para 
     * la población proporcionada.
     * @param string $city
     * @param string &$errorMsg - En el caso de un error, devuelve el mensaje.     * 
     * @return array (of string)
     */    
    public function getPostcodesForCity($city, &$errorMsg);
    
    /*
     * Se devuelve la lista de poblaciones que sean válidas para 
     * el código postal proporcionado.
     * @param string $postcode
     * @param string &$errorMsg - En el caso de un error, devuelve el mensaje.     * 
     * @return array (of string)
     */  
    public function getCitiesForPostcode($postcode, &$errorMsg);    
    
    /** Se devuelve true si el carrier puede proveer información sobre los costes
     *  del envío.
     *  @return bool 
     */
    function supportsCalculationOfShippingCosts();
    
    /*
     * Se obtiene el coste de un envío. Pasamos el pedido
     * la población y el código postal del destinario (si son diferentes 
     * que los en el pedido) y la lista de los pesos de los bultos que vamos
     * a enviar.
     * @param Mage_Sales_Model_Order $order
     * @param string $city
     * @param string $postcode
     * @param array $weightsBultos
     * @param string &$errorStr -- error message returned.
     * @return double -- cost of shipping
     */
    function getShippingCost(
            Mage_Sales_Model_Order $order, 
            $city,
            $postcode,
            $weightsBultos, 
            &$errorStr);    
    
    /** Se devuelve true si el shipping method especificado limita el contenido
     * del envío a sólo un bulto.
     * @param string $shippingMethod
     * @return int 
     */
    function shippingMethodRequiresShipmentsOfOnlyOneBulto($shippingMethod);
    
    /**
     * @param mixed $store 
     * @return bool True si debemos omitir el diálogo y comunicar el envío como un bulto 
     *              para pedidos en esta tienda.
     */
    function skipDialog($store );
}
