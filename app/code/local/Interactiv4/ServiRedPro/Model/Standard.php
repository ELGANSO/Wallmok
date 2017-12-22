<?php
/**
 * ServiRedPro
 *
 * @category    Interactiv4
 * @package     Interactiv4_ServiRedPro
 * @copyright   Copyright (c) 2012 Interactiv4 SL. (http://www.interactiv4.com)
 */
class Interactiv4_ServiRedPro_Model_Standard extends Mage_Payment_Model_Method_Abstract {
    const CALLING_SESSION_QUERY_PARAM = 'cSID';
    const ORDER_ID_SEPARATOR = '=';
    const CODE = 'serviredpro';
    protected $_code = self::CODE;
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid = true;
    protected $_canUseInternal = true;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = false;
    protected $_canSaveCc = false;
    protected $_isInitializeNeeded = true;
    protected $_formBlockType = 'serviredpro/form';
    protected $_infoBlockType = 'serviredpro/info';
    protected $_canReviewPayment = true;
    const PAYMENT_TYPE_AUTH = 'AUTHORIZATION';
    const PAYMENT_TYPE_SALE = 'SALE';
    const CODE_REFUND = '3';
    protected $languages = array(
        "da_DK" => "1",
        "de_CH" => "7",
        "de_DE" => "7",
        "en_AU" => "2",
        "en_GB" => "2",
        "en_US" => "2",
        "sv_SE" => "3",
        "nn_NO" => "4",
    );
    protected $currencies = array(
        "ADP" => "020",
        "AED" => "784",
        "AFA" => "004",
        "ALL" => "008",
        "AMD" => "051",
        "ANG" => "532",
        "AOA" => "973",
        "ARS" => "032",
        "AUD" => "036",
        "AWG" => "533",
        "AZM" => "031",
        "BAM" => "977",
        "BBD" => "052",
        "BDT" => "050",
        "BGL" => "100",
        "BGN" => "975",
        "BHD" => "048",
        "BIF" => "108",
        "BMD" => "060",
        "BND" => "096",
        "BOB" => "068",
        "BOV" => "984",
        "BRL" => "986",
        "BSD" => "044",
        "BTN" => "064",
        "BWP" => "072",
        "BYR" => "974",
        "BZD" => "084",
        "CAD" => "124",
        "CDF" => "976",
        "CHF" => "756",
        "CLF" => "990",
        "CLP" => "152",
        "CNY" => "156",
        "COP" => "170",
        "CRC" => "188",
        "CUP" => "192",
        "CVE" => "132",
        "CYP" => "196",
        "CZK" => "203",
        "DJF" => "262",
        "DKK" => "208",
        "DOP" => "214",
        "DZD" => "012",
        "ECS" => "218",
        "ECV" => "983",
        "EEK" => "233",
        "EGP" => "818",
        "ERN" => "232",
        "ETB" => "230",
        "EUR" => "978",
        "FJD" => "242",
        "FKP" => "238",
        "GBP" => "826",
        "GEL" => "981",
        "GHC" => "288",
        "GIP" => "292",
        "GMD" => "270",
        "GNF" => "324",
        "GTQ" => "320",
        "GWP" => "624",
        "GYD" => "328",
        "HKD" => "344",
        "HNL" => "340",
        "HRK" => "191",
        "HTG" => "332",
        "HUF" => "348",
        "IDR" => "360",
        "ILS" => "376",
        "INR" => "356",
        "IQD" => "368",
        "IRR" => "364",
        "ISK" => "352",
        "JMD" => "388",
        "JOD" => "400",
        "JPY" => "392",
        "KES" => "404",
        "KGS" => "417",
        "KHR" => "116",
        "KMF" => "174",
        "KPW" => "408",
        "KRW" => "410",
        "KWD" => "414",
        "KYD" => "136",
        "KZT" => "398",
        "LAK" => "418",
        "LBP" => "422",
        "LKR" => "144",
        "LRD" => "430",
        "LSL" => "426",
        "LTL" => "440",
        "LVL" => "428",
        "LYD" => "434",
        "MAD" => "504",
        "MDL" => "498",
        "MGF" => "450",
        "MKD" => "807",
        "MMK" => "104",
        "MNT" => "496",
        "MOP" => "446",
        "MRO" => "478",
        "MTL" => "470",
        "MUR" => "480",
        "MVR" => "462",
        "MWK" => "454",
        "MXN" => "484",
        "MXV" => "979",
        "MYR" => "458",
        "MZM" => "508",
        "NAD" => "516",
        "NGN" => "566",
        "NIO" => "558",
        "NOK" => "578",
        "NPR" => "524",
        "NZD" => "554",
        "OMR" => "512",
        "PAB" => "590",
        "PEN" => "604",
        "PGK" => "598",
        "PHP" => "608",
        "PKR" => "586",
        "PLN" => "985",
        "PYG" => "600",
        "QAR" => "634",
        "ROL" => "642",
        "RUB" => "643",
        "RUR" => "810",
        "RWF" => "646",
        "SAR" => "682",
        "SBD" => "090",
        "SCR" => "690",
        "SDD" => "736",
        "SEK" => "752",
        "SGD" => "702",
        "SHP" => "654",
        "SIT" => "705",
        "SKK" => "703",
        "SLL" => "694",
        "SOS" => "706",
        "SRG" => "740",
        "STD" => "678",
        "SVC" => "222",
        "SYP" => "760",
        "SZL" => "748",
        "THB" => "764",
        "TJS" => "972",
        "TMM" => "795",
        "TND" => "788",
        "TOP" => "776",
        "TPE" => "626",
        "TRL" => "792",
        "TRY" => "949",
        "TTD" => "780",
        "TWD" => "901",
        "TZS" => "834",
        "UAH" => "980",
        "UGX" => "800",
        "USD" => "840",
        "UYU" => "858",
        "UZS" => "860",
        "VEB" => "862",
        "VND" => "704",
        "VUV" => "548",
        "XAF" => "950",
        "XCD" => "951",
        "XOF" => "952",
        "XPF" => "953",
        "YER" => "886",
        "YUM" => "891",
        "ZAR" => "710",
        "ZMK" => "894",
        "ZWD" => "716",
    );
    protected $errorMessage = array(
        "SIS0007" => "Error al desmontar el XML de entrada",
        "SIS0008" => "Error falta Ds_Merchant_MerchantCode",
        "SIS0009" => "Error de formato en Ds_Merchant_MerchantCode",
        "SIS0010" => "Error falta Ds_Merchant_Terminal",
        "SIS0011" => "Error de formato en Ds_Merchant_Terminal",
        "SIS0014" => " Error de formato en Ds_Merchant_Order",
        "SIS0015" => " Error falta Ds_Merchant_Currency",
        "SIS0016" => "Error de formato en Ds_Merchant_Currency",
        "SIS0017" => "Error no se admiten operaciones en pesetas",
        "SIS0018" => "Error falta Ds_Merchant_Amount",
        "SIS0019" => "Error de formato en Ds_Merchant_Amount",
        "SIS0020" => "Error falta Ds_Merchant_MerchantSignature",
        "SIS0021" => "Error la Ds_Merchant_MerchantSignature viene vacía",
        "SIS0022" => "Error de formato en Ds_Merchant_TransactionType",
        "SIS0023" => "Error Ds_Merchant_TransactionType desconocido",
        "SIS0024" => "Error Ds_Merchant_ConsumerLanguage tiene mas de 3 posiciones",
        "SIS0025" => "Error de formato en Ds_Merchant_ConsumerLanguage",
        "SIS0026" => "Error No existe el comercio / terminal enviado",
        "SIS0027" => "Error Moneda enviada por el comercio es diferente a la que tiene asignada para ese terminal ",
        "SIS0028" => "Error Comercio / terminal está dado de baja",
        "SIS0030" => "Error en un pago con tarjeta ha llegado un tipo de operación que no es ni pago ni preautorización ",
        "SIS0031" => "Método de pago no definido",
        "SIS0033" => "Error en un pago con móvil ha llegado un tipo de operación que no es ni pago ni preautorización ",
        "SIS0034" => "Error de acceso a la Base de Datos",
        "SIS0037" => "El número de teléfono no es válido",
        "SIS0038" => "Error en java",
        "SIS0040" => "Error el comercio / terminal no tiene ningún método de pago asignado ",
        "SIS0041" => "Error en el cálculo de la HASH de datos del comercio.",
        "SIS0042" => "La firma enviada no es correcta",
        "SIS0043" => "Error al realizar la notificación on-line",
        "SIS0046" => "El bin de la tarjeta no está dado de alta",
        "SIS0051" => "Error número de pedido repetido",
        "SIS0054" => "Error no existe operación sobre la que realizar la devolución",
        "SIS0055" => "Error existe más de un pago con el mismo número de pedido",
        "SIS0056" => "La operación sobre la que se desea devolver no está autorizada",
        "SIS0057" => "El importe a devolver supera el permitido",
        "SIS0058" => "Inconsistencia de datos, en la validación de una confirmación",
        "SIS0059" => "Error no existe operación sobre la que realizar la confirmación",
        "SIS0060" => "Ya existe una confirmación asociada a la preautorización",
        "SIS0061" => "La preautorización sobre la que se desea confirmar no está autorizada ",
        "SIS0062" => "El importe a confirmar supera el permitido",
        "SIS0063" => "Error. Número de tarjeta no disponible",
        "SIS0064" => "Error. El número de tarjeta no puede tener más de 19 posiciones",
        "SIS0065" => "Error. El número de tarjeta no es numérico",
        "SIS0066" => "Error. Mes de caducidad no disponible",
        "SIS0067" => "Error. El mes de la caducidad no es numérico",
        "SIS0068" => "Error. El mes de la caducidad no es válido",
        "SIS0069" => "Error. Año de caducidad no disponible",
        "SIS0070" => "Error. El Año de la caducidad no es numérico",
        "SIS0071" => "Tarjeta caducada",
        "SIS0072" => "Operación no anulable",
        "SIS0074" => "Error falta Ds_Merchant_Order",
        "SIS0075" => "Error el Ds_Merchant_Order tiene menos de 4 posiciones o más de 12 ",
        "SIS0076" => "Error el Ds_Merchant_Order no tiene las cuatro primeras posiciones numéricas ",
        "SIS0077" => "Error el Ds_Merchant_Order no tiene las cuatro primeras posiciones numéricas. No se utiliza ",
        "SIS0078" => "Método de pago no disponible",
        "SIS0079" => "Error al realizar el pago con tarjeta",
        "SIS0081" => "La sesión es nueva, se han perdido los datos almacenados",
        "SIS0084" => "El valor de Ds_Merchant_Conciliation es nulo",
        "SIS0085" => "El valor de Ds_Merchant_Conciliation no es numérico",
        "SIS0086" => "El valor de Ds_Merchant_Conciliation no ocupa 6 posiciones",
        "SIS0089" => "El valor de Ds_Merchant_ExpiryDate no ocupa 4 posiciones",
        "SIS0092" => "El valor de Ds_Merchant_ExpiryDate es nulo",
        "SIS0093" => "Tarjeta no encontrada en la tabla de rangos",
        "SIS0094" => "La tarjeta no fue autenticada como 3D Secure",
        "SIS0097" => "Valor del campo Ds_Merchant_CComercio no válido",
        "SIS0098" => "Valor del campo Ds_Merchant_CVentana no válido",
        "SIS0112" => "Error El tipo de transacción especificado en Ds_Merchant_Transaction_Type no esta permitido ",
        "SIS0113" => "Excepción producida en el servlet de operaciones",
        "SIS0114" => "Error, se ha llamado con un GET en lugar de un POST",
        "SIS0115" => "Error no existe operación sobre la que realizar el pago de la cuota",
        "SIS0116" => "La operación sobre la que se desea pagar una cuota no es una operación válida ",
        "SIS0117" => "La operación sobre la que se desea pagar una cuota no está autorizada ",
        "SIS0118" => "Se ha excedido el importe total de las cuotas",
        "SIS0119" => "Valor del campo Ds_Merchant_DateFrecuency no válido",
        "SIS0120" => "Valor del campo Ds_Merchant_ChargeExpiryDate no válido",
        "SIS0121" => "Valor del campo Ds_Merchant_SumTotal no válido",
        "SIS0122" => "Valor del campo Ds_Merchant_DateFrecuency Ds_Merchant_SumTotal tiene formato incorrecto",
        "SIS0123" => "Se ha excedido la fecha tope para realizar transacciones",
        "SIS0124" => "No ha transcurrido la frecuencia mínima en un pago recurrente sucesivo",
        "SIS0132" => "La fecha de Confirmación de Autorización no puede superar en mas de 7 días a la de Preautorización.",
        "SIS0133" => "La fecha de Confirmación de Autenticación no puede superar en mas de 45 días a la de Autenticación Previa.",
        "SIS0139" => "Error el pago recurrente inicial está duplicado",
        "SIS0142" => "Tiempo excedido para el pago",
        "SIS0197" => "Error al obtener los datos de cesta de la compra en operación tipo pasarela",
        "SIS0198" => "Error el importe supera el límite permitido para el comercio",
        "SIS0199" => "Error el número de operaciones supera el límite permitido para el comercio ",
        "SIS0200" => "Error el importe acumulado supera el límite permitido para el comercio ",
        "SIS0214" => "El comercio no admite devoluciones",
        "SIS0216" => "Error Ds_Merchant_CVV2 tiene mas de 3 posiciones",
        "SIS0217" => "Error de formato en Ds_Merchant_CVV2",
        "SIS0218" => "El comercio no permite operaciones seguras por la entrada/operaciones ",
        "SIS0219" => "Error el número de operaciones de la tarjeta supera el límite",
        "SIS0220" => "Error el importe acumulado de la tarjeta supera el límite permitido para el comercio",
        "SIS0221" => "Error el CVV2 es obligatorio",
        "SIS0222" => "Ya existe una anulación asociada a la preautorización",
        "SIS0223" => "La preautorización que se desea anular no está autorizada",
        "SIS0224" => "El comercio no permite anulaciones por no tener firma ampliada",
        "SIS0225" => "Error no existe operación sobre la que realizar la anulación",
        "SIS0226" => "Inconsistencia de datos, en la validación de una anulación",
        "SIS0227" => "Valor del campo Ds_Merchant_TransactionDate no válido",
        "SIS0229" => "No existe el código de pago aplazado solicitado",
        "SIS0252" => "El comercio no permite el envío de tarjeta",
        "SIS0253" => "La tarjeta no cumple el check-digit",
        "SIS0254" => "El número de operaciones de la IP supera el límite permitido por el comercio ",
        "SIS0255" => "El importe acumulado por la IP supera el límite permitido por el comercio ",
        "SIS0256" => "El comercio no puede realizar preautorizaciones",
        "SIS0257" => "Esta tarjeta no permite operativa de preautorizaciones",
        "SIS0258" => "Inconsistencia de datos, en la validación de una confirmación",
        "SIS0261" => "Operación detenida por superar el control de restricciones en la entrada al SIS",
        "SIS0270" => "El comercio no puede realizar autorizaciones en diferido",
        "SIS0274" => "Tipo de operación desconocida o no permitida por esta entrada al SIS ",
    );
    public function capture(Varien_Object $payment, $amount) {
        return $this;
    }
    public function authorize(Varien_Object $payment, $amount) {
        return $this;
    }
    public function void(Varien_Object $payment) {
        return $this;
    }
    /**
     * Get checkout session namespace
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout() {
        return Mage::getSingleton('checkout/session');
    }
    /**
     * Get current quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote() {
        return $this->getCheckout()->getQuote();
    }
    public function createFormBlock($name) {
        $block = $this->getLayout()->createBlock('serviRedPro/form', $name)
                ->setMethod('serviredpro')
                ->setPayment($this->getPayment())
                ->setTemplate('payment/form/serviredpro.phtml');
        return $block;
    }
    /* validate the currency code is avaialable to use for servired or not */
//	public function validate() {
//		parent::validate();
//		$currency_code = $this->getQuote()->getBaseCurrencyCode();
//		if(!in_array($currency_code,$this->_allowCurrencyCode)) {
//			Mage::throwException(Mage::helper()->__('El codigo de moneda seleccionado (%s) no es compatible con Servired',$currency_code));
//		}
//		return	$this;
//	}
//	public function onOrderValidate(Mage_Sales_Model_Order_Payment $payment) {
//		return	$this;
//	}
//
//	public function onInvoiceCreate(Mage_Sales_Model_Invoice_Payment $payment) {
//	}
    public function getOrderPlaceRedirectUrl() {
        return Mage::getUrl('serviredpro/payment/redirect');
    }
    /**
     * Parámetro del query string donde metemos el ID de la sesión que llamó al servicio.
     * @return string
     */
    public static function getCallingSessionIdQueryParam() {
        return self::CALLING_SESSION_QUERY_PARAM;
    }
    public function getStandardCheckoutFormFields() {
        $this->_getHelper()->log(__METHOD__);
        $a = $this->getQuote()->getShippingAddress();
        $ordernum = $this->getCheckout()->getLastRealOrderId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($ordernum);  /* @var $order Mage_Sales_Model_Order */
        $serviredOrderReference = $this->_getHelper()->getServiredOrderReference($order);
        $callingSidParam = $order->getI4serviredSessionId();
        $convertor = Mage::getModel('sales/convert_order');
        $invoice = $convertor->toInvoice($order);
        $amount = $order->getBaseGrandTotal() * 100;
        $amount = round($amount);
        $code = $this->getConfigData('merchantnumber');
       
        $currency = $this->currencies[$order->getBaseCurrencyCode()];
        $clave = $this->getConfigData('merchantpassword');
        $callbackQueryString = $this->_getCallbackQueryString($callingSidParam);
        $merchurl = Mage::getUrl('serviredpro/payment/notification') . '?' . $callbackQueryString;
        if ($this->getConfigData('signaturetype') == 2) {
            $transtype = $this->getConfigData('merchanttransactiontype');
            $message = $amount . $serviredOrderReference . $code . $currency . $transtype . $merchurl . $clave;
        } else {
            $message = $amount . $serviredOrderReference . $code . $currency . $clave;
        }
        $signature = strtoupper(sha1($message));
        
        $sArr = array(
            'Ds_Merchant_Amount' => $amount, // convert to minor units
            'Ds_Merchant_Currency' => $currency,
            'Ds_Merchant_Order' => $serviredOrderReference,
            'Ds_Merchant_ProductDescription' => $this->getConfigData('mensagen'),
            'Ds_Merchant_Titular' => $this->getConfigData('merchanttitular'),
            'Ds_Merchant_MerchantCode' => $code,
            'Ds_Merchant_MerchantUrl' => $merchurl,
            'Ds_Merchant_UrlOK' => Mage::getUrl('serviredpro/payment/success') . '?' . $callbackQueryString,
            'Ds_Merchant_UrlKO' => Mage::getUrl('serviredpro/payment/cancel') . '?' . $callbackQueryString,
            'Ds_Merchant_MerchantName' => $this->getConfigData('merchanttitular'),
            'Ds_Merchant_ConsumerLanguage' => $this->getConfigData('consumerlanguage'), //$this->languages[Mage::app()->getLocale()->getLocaleCode()],
            'Ds_Merchant_MerchantSignature' => $signature,
            'Ds_Merchant_Terminal' => $this->getConfigData('merchantterminal'),
            'Ds_Merchant_SumTotal' => '',
            'Ds_Merchant_TransactionType' => (int) $this->getConfigData('merchanttransactiontype'),
            'Ds_Merchant_MerchantData' => '',
            'Ds_Merchant_DateFrecuency' => '',
            'Ds_Merchant_ChargeExpiryDate' => '',
            'Ds_Merchant_AuthorisationCode' => $this->getConfigData('authsms'),
            'Ds_Merchant_TransactionDate' => '',
            'callbackurl' => Mage::getUrl('serviredpro/payment/callback') . '?' . $callbackQueryString
//			'windowstate'						=>	$this->getConfigData('windowstate'),
        );
        $this->_getHelper()->log("----------------- START PAYMENT REQUEST ----------------");
        $this->_getHelper()->log($sArr);
        $this->_getHelper()->log("------------------ END PAYMENT REQUEST -----------------");
        $this->_getHelper()->log("");
        //
        // Make into request data
        //
		$sReq = '';
        $rArr = array();
        foreach ($sArr as $k => $v) {
            /* replacing & char with and. otherwise it will break the post */
            $value = str_replace("&", "and", $v);
            $rArr[$k] = $value;
            $sReq .= '&' . $k . '=' . $value;
        }
//		$this->_getHelper()->log($rArr);
        return $rArr;
    }
    
    /**
     *
     * @param srring $callingSidParam
     * @return string 
     */
    protected function _getCallbackQueryString($callingSidParam) {
        return self::getCallingSessionIdQueryParam() . '=' . urlencode($callingSidParam);
    }
    
    
    //
    // Simply return the url for the Servired Payment window
    //
    public function getServiredUrl() {
        return $this->_getHelper()->getServiredUrl();
    }
    private function getServiredProBackendUrl() {
        return $this->_getHelper()->getServiredProBackendUrl();
    }
    public function refund(Varien_Object $payment, $amount) {
        $this->_getHelper()->log(__METHOD__);
        $this->_getHelper()->log("----------------- START REFUND PROCESS ----------------");
        if ($amount > 0) {
            $rc = $this->_postRequest($payment, $amount, '3');
        } else {
            $this->_getHelper()->log('La cantidad es 0');
        }
        if ($rc['Error']) {
            $this->_getHelper()->log("------------ END REFUND PROCESS WITH ERRORS -----------");
            $this->_getHelper()->log("");
            Mage::throwException($rc['ErrorDescription']);
        } else {
            Mage::getModel('serviredpro/serviredpro_refund')->addRefundLog($rc, $payment->getParentId(), $amount);
        }
        $this->_getHelper()->log("------------------ END REFUND PROCESS -----------------");
        $this->_getHelper()->log("");
        return $this;
    }
    public function processCreditmemo($creditmemo, $payment) {
        $this->_getHelper()->log(__METHOD__);
        $creditmemo->setTransactionId($payment->getLastTransId());
        return $this;
    }
    /* @davidselo:extendemos este módulo para que inicialice la orden */
    public function initialize($paymentAction, $stateObject) {
        $state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
        $stateObject->setState($state);
        $stateObject->setStatus('servired_pending');
        $stateObject->setIsNotified(false);
    }
    private function _postRequest($payment, $amount, $operation) {
        $this->_getHelper()->log(__METHOD__);
        $order = $payment->getOrder();
        
        $sermepaUrl = $this->getServiredProBackendUrl();
        $timeout = 60;
        $callingSidParam = $order->getI4serviredSessionId();
        $url = Mage::getUrl('serviredpro/payment/notification') . '?' . $this->_getCallbackQueryString($callingSidParam);
        $currency = $this->currencies[$order->getBaseCurrencyCode()];
        $amount *= 100;
        $amount = round($amount);
        // tenemos que obtener la referencia ya sea igual al numero de pedido o no
        $ordernum = $this->_getHelper()->getServiredOrderReference($order);
        $code = $this->getConfigData('merchantnumber');
        $clave = $this->getConfigData('merchantpassword');
        $terminal = $this->getConfigData('merchantterminal');
//		if($operation!= '3' && $this->getConfigData('includeurl',$this->getStore())) {
//			$message = $amount.$ordernum.$code.$currency.$operation.$url.$clave;
//		}
//		else {
//			$message = $amount.$ordernum.$code.$currency.$operation.$clave;
//		}
        $message = $amount . $ordernum . $code . $currency . $operation . $clave;
        $signature = strtoupper(sha1($message));
        //
        // Make the xml request
        //
		$implementation = new DOMImplementation();
        $doc = $implementation->createDocument();
        $datosEntrada = $doc->createElement('DATOSENTRADA');
        $datosEntrada = $doc->appendChild($datosEntrada);
        $dsVersion = $doc->createElement('DS_Version', '0.1');
        $dsVersion = $datosEntrada->appendChild($dsVersion);
        $merchantAmount = $doc->createElement('DS_MERCHANT_AMOUNT', $amount);
        $merchantAmount = $datosEntrada->appendChild($merchantAmount);
        $merchantCurrency = $doc->createElement('DS_MERCHANT_CURRENCY', $currency);
        $merchantCurrency = $datosEntrada->appendChild($merchantCurrency);
        $merchantOrder = $doc->createElement('DS_MERCHANT_ORDER', $ordernum);
        $merchantOrder = $datosEntrada->appendChild($merchantOrder);
        $merchantCode = $doc->createElement('DS_MERCHANT_MERCHANTCODE', $code);
        $merchantCode = $datosEntrada->appendChild($merchantCode);
        $merchantUrl = $doc->createElement('DS_MERCHANT_MERCHANTURL', $url);
        $merchantUrl = $datosEntrada->appendChild($merchantUrl);
        $merchantSignature = $doc->createElement('DS_MERCHANT_MERCHANTSIGNATURE', $signature);
        $merchantSignature = $datosEntrada->appendChild($merchantSignature);
        $merchantTerminal = $doc->createElement('DS_MERCHANT_TERMINAL', $terminal);
        $merchantTerminal = $datosEntrada->appendChild($merchantTerminal);
        $merchantTranstype = $doc->createElement('DS_MERCHANT_TRANSACTIONTYPE', $operation);
        $merchantTranstype = $datosEntrada->appendChild($merchantTranstype);
        $rd = $doc->saveXML();
        $this->_getHelper()->log("------------------ START REFUND REQUEST -----------------");
        $this->_getHelper()->log($rd);
        $this->_getHelper()->log("------------------- END REFUND REQUEST ------------------");
        $this->_getHelper()->log("");
        $entrada = 'entrada=' . $rd;
        $curlSession = curl_init();
        // Set the URL
        curl_setopt($curlSession, CURLOPT_URL, $sermepaUrl);
        // No headers, please
        curl_setopt($curlSession, CURLOPT_HEADER, 0);
        // It's a POST request
        curl_setopt($curlSession, CURLOPT_POST, 1);
        // Set the fields for the POST
        curl_setopt($curlSession, CURLOPT_POSTFIELDS, $entrada);
        // Return it direct, don't print it out
        curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, 1);
        // This connection will timeout in 30 seconds
        curl_setopt($curlSession, CURLOPT_TIMEOUT, $timeout);
        //The next two lines must be present for the kit to work with newer version of cURL
        //You should remove them if you have any problems in earlier versions of cURL
        curl_setopt($curlSession, CURLOPT_SSL_VERIFYPEER, FALSE);
        
        // We must set it depending on config value
        $sslVersion = $this->getConfigData('sslversion');
        curl_setopt($curlSession, CURLOPT_SSLVERSION, $sslVersion);
        // We must set it to 2
        curl_setopt($curlSession, CURLOPT_SSL_VERIFYHOST, 2);
        //Send the request and store the result in an array
        $rawresponse = curl_exec($curlSession);
        $this->_getHelper()->log("----------------- START REFUND RESPONSE -----------------");
        $this->_getHelper()->log($rawresponse);
        $this->_getHelper()->log("------------------ END REFUND RESPONSE ------------------");
        $this->_getHelper()->log("");
        $rc = simplexml_load_string($rawresponse);
        if (!isset($rc->CODIGO)) {
            Mage::throwException("Error de comunicacion irreparable, comuniquese con Servired");
        } else {
            if ($rc->CODIGO != '0') {
                Mage::throwException($this->errorMessage[$rc->CODIGO]);
            }
            return $rc;
        }
//		$response['Error']=false;
//		if(preg_match('<title>500 Internal Server Error</title>',$rawresponse)) {
//			$response['Error'] = true;
//			$response['ErrorDescription'] = '500 Internal Server Error on ServiRed Server';
//		}
//		return $response;
    }
    
    /**
     *
     * @return Interactiv4_ServiRedPro_Helper_Data 
     */
    protected function _getHelper() {
        return Mage::helper('serviredpro');
    }
    
    /**
     *
     * @param mixed $store
     * @return string 
     */
    public function getClave($store = null) {
        return $this->getConfigData('merchantpassword', $store);
    }
}
