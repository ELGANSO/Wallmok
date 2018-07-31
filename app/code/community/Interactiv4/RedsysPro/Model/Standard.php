<?php

/**
 * Redsys Pro
 *
 * @category    Interactiv4
 * @package     Interactiv4_RedsysPro
 * @copyright   Copyright (c) 2015 Interactiv4 SL. (http://www.interactiv4.com)
 * @author      Oscar Salueña Martín <oscar.saluena@interactiv4.com> @osaluena
 * @author      David Slater
 */
class Interactiv4_RedsysPro_Model_Standard extends Mage_Payment_Model_Method_Abstract {

    const CALLING_SESSION_QUERY_PARAM       = 'cSID';
    const ORDER_ID_SEPARATOR                = '=';
    const CODE                              = 'i4redsyspro';
    const SIGNATURE_TYPE_SHA256             = "HMAC_SHA256_V1";
    const CODE_REFUND                       = '3';
    const TRANSACTION_TYPE_AUTHORIZATION    = 0;

    protected $_code                        = self::CODE;
    protected $_isGateway                   = true;
    protected $_canAuthorize                = true;
    protected $_canCapture                  = true;
    protected $_canCapturePartial           = true;
    protected $_canRefund                   = true;
    protected $_canRefundInvoicePartial     = true;
    protected $_canVoid                     = true;
    protected $_canUseInternal              = false;
    protected $_canUseCheckout              = true;
    protected $_canUseForMultishipping      = false;
    protected $_canSaveCc                   = false;
    protected $_isInitializeNeeded          = true;
    protected $_formBlockType               = 'i4redsyspro/form';
    protected $_infoBlockType               = 'i4redsyspro/info';
    protected $_canReviewPayment            = true;

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
        $block = $this->getLayout()->createBlock('i4redsyspro/form', $name)
                ->setMethod('i4redsyspro')
                ->setPayment($this->getPayment())
                ->setTemplate('payment/form/i4redsyspro.phtml');

        return $block;
    }

    public function getOrderPlaceRedirectUrl() {
        return Mage::getUrl('i4redsyspro/payment/redirect');
    }

    /**
     * Parámetro del query string donde metemos el ID de la sesión que llamó al servicio.
     * @return string
     */
    public static function getCallingSessionIdQueryParam() {
        return self::CALLING_SESSION_QUERY_PARAM;
    }

    public function getStandardCheckoutFormFields($isSha256 = false) {

        $this->_getHelper()->log(__METHOD__);
        $ordernum = $this->getCheckout()->getLastRealOrderId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($ordernum);  /* @var $order Mage_Sales_Model_Order */
        $redsysOrderReference = $this->_getHelper()->getRedsysOrderReference($order);
        $callingSidParam = $order->getI4redsysproSessionId();
        $amount = $order->getBaseGrandTotal() * 100;
        $amount = round($amount);
        $code = $this->getConfigData('merchantnumber');
       
        $currency = $this->currencies[$order->getBaseCurrencyCode()];

	$store = Mage::app()->getStore()->getStoreId();
        if($store == 7) {
                $currency = $this->currencies['GBP'];
                $amount = $order->getGrandTotal() * 100;
                $amount = round($amount);
        }

        $clave = $this->getClave();

        $callbackQueryString = $this->_getCallbackQueryString($callingSidParam);
        $merchurl = Mage::getUrl('i4redsyspro/payment/notification') . '?' . $callbackQueryString;
        $message = $amount . $redsysOrderReference . $code . $currency . self::TRANSACTION_TYPE_AUTHORIZATION . $merchurl . $clave;
        $signature = $this->_getSignature($message);
        
        $sArr = array(
            'Ds_Merchant_Amount' => $amount, // convert to minor units
            'Ds_Merchant_Currency' => $currency,
            'Ds_Merchant_Order' => $redsysOrderReference,
            'Ds_Merchant_ProductDescription' => $this->getConfigData('mensagen'),
            'Ds_Merchant_Titular' => $this->getConfigData('merchanttitular'),
            'Ds_Merchant_MerchantCode' => $code,
            'Ds_Merchant_MerchantUrl' => $merchurl,
            'Ds_Merchant_UrlOK' => Mage::getUrl('i4redsyspro/payment/success') . '?' . $callbackQueryString,
            'Ds_Merchant_UrlKO' => Mage::getUrl('i4redsyspro/payment/cancel') . '?' . $callbackQueryString,
            'Ds_Merchant_MerchantName' => $this->getConfigData('merchanttitular'),
            'Ds_Merchant_ConsumerLanguage' => $this->getConfigData('consumerlanguage'), //$this->languages[Mage::app()->getLocale()->getLocaleCode()],
            'Ds_Merchant_MerchantSignature' => $signature,
            'Ds_Merchant_Terminal' => $this->getConfigData('merchantterminal'),
            'Ds_Merchant_SumTotal' => '',
            'Ds_Merchant_TransactionType' => (int) self::TRANSACTION_TYPE_AUTHORIZATION,
            'Ds_Merchant_MerchantData' => '',
            'Ds_Merchant_DateFrecuency' => '',
            'Ds_Merchant_ChargeExpiryDate' => '',
            'Ds_Merchant_AuthorisationCode' => $this->getConfigData('authsms'),
            'Ds_Merchant_TransactionDate' => '',
            "Ds_Merchant_PayMethods" => "C",
            'callbackurl' => Mage::getUrl('i4redsyspro/payment/callback') . '?' . $callbackQueryString
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


        if ($isSha256) {
            unset($rArr['Ds_Merchant_MerchantSignature']);
            $result = array();
            $result['Ds_SignatureVersion']      = self::SIGNATURE_TYPE_SHA256;
            $result['Ds_MerchantParameters']    = base64_encode(json_encode($rArr));
            $result['Ds_Signature']             = $this->_getSignature("",null, $rArr);

            return  $result;
        }

        return $rArr;
    }
    
    /**
     *
     * @param string $callingSidParam
     * @return string 
     */
    protected function _getCallbackQueryString($callingSidParam) {
        return self::getCallingSessionIdQueryParam() . '=' . urlencode($callingSidParam);
    }

    //
    // Simply return the url for the Redsys Payment window
    //
    public function getRedsysUrl() {
        return $this->_getHelper()->getRedsysUrl();
    }

    private function getRedsysProBackendUrl() {
        return $this->_getHelper()->getRedsysProBackendUrl();
    }

    public function refund(Varien_Object $payment, $amount) {
        $this->_getHelper()->log(__METHOD__);
        $this->_getHelper()->log("----------------- START REFUND PROCESS ----------------");
        if ($amount > 0) {
            $rc = $this->_postRequest($payment, $amount, '3');
        } else {
            $this->_getHelper()->log('La cantidad es 0');
        }

        /* Check Signature */
        if ($this->_getHelper()->isSHA256Configured()) {
            $receivedSignature = (isset($rc->OPERACION->Ds_Signature)) ? $rc->OPERACION->Ds_Signature->__toString() : "";
            //Mage::log("Received Signature RESPONSE REFUND => " . $receivedSignature ,null, "sha256.log",true);
            $whatSignatureRefundShouldBe = $this->_getWhatSignatureRefundShouldBe($rc);
            //Mage::log("What Signature Generation Should be RESPONSE REFUND => " . $whatSignatureRefundShouldBe ,null, "sha256.log",true);
            if ($receivedSignature !== $whatSignatureRefundShouldBe) {
                Mage::throwException("La firma no coincide ");
            }

        }

        $receivedResponseCode = (isset($rc->OPERACION->Ds_Response)) ? $rc->OPERACION->Ds_Response->__toString() : "";
        if ($receivedResponseCode !== "0900") {
            $this->_getHelper()->log("------------ END REFUND PROCESS WITH ERRORS -----------");
            $this->_getHelper()->log("");
            Mage::throwException($rc['ErrorDescription']);
        } else {
            Mage::getModel('i4redsyspro/redsyspro_refund')->addRefundLog($rc, $payment->getParentId(), $amount);
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

    public function initialize($paymentAction, $stateObject) {
        $state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
        $stateObject->setState($state);
        $stateObject->setStatus('redsyspro_pending');
        $stateObject->setIsNotified(false);
    }

    private function _postRequest($payment, $amount, $operation) {
        $isSha256Configured = $this->_getHelper()->isSHA256Configured();

        $this->_getHelper()->log(__METHOD__);
        $order = $payment->getOrder();

        $redsysUrl = $this->getRedsysProBackendUrl();
        $timeout = 60;
        $callingSidParam = $order->getI4redsysproSessionId();
        $url = Mage::getUrl('i4redsyspro/payment/notification') . '?' . $this->_getCallbackQueryString($callingSidParam);
        $currency = $this->currencies[$order->getBaseCurrencyCode()];
        $amount *= 100;
        $amount = round($amount);
        // tenemos que obtener la referencia ya sea igual al numero de pedido o no
        $ordernum = $this->_getHelper()->getRedsysOrderReference($order);
        $code = $this->getConfigData('merchantnumber');
        $clave = $this->getClave($order->getStore());
        $terminal = $this->getConfigData('merchantterminal');

        $message = $amount . $ordernum . $code . $currency . $operation . $clave;
        $signature = $this->_getSignatureForRefund($message,$order->getStore());

        //
        // Make the xml request
        //
        $implementation = new DOMImplementation();
        $doc = $implementation->createDocument();

        if ($isSha256Configured) {

            $request = $doc->createElement('REQUEST');
            $request = $doc->appendChild($request);

            $datosEntrada = $doc->createElement('DATOSENTRADA');
            $datosEntrada = $request->appendChild($datosEntrada);

            /* set DATOSENTRADA */
            $rArr = array(
                'DS_MERCHANT_AMOUNT'            => $amount,
                'DS_MERCHANT_ORDER'             => $ordernum,
                'DS_MERCHANT_MERCHANTCODE'      => $code,
                'DS_MERCHANT_TERMINAL'          => $terminal,
                'DS_MERCHANT_CURRENCY'          => $currency,
                'DS_MERCHANT_TRANSACTIONTYPE'   => $operation
            );

            foreach ($rArr as $name => $value) {
                $element = $doc->createElement($name, $value);
                $datosEntrada->appendChild($element);
            }

            /* set DS_SIGNATUREVERSION */
            $signatureVersion = $doc->createElement('DS_SIGNATUREVERSION', self::SIGNATURE_TYPE_SHA256);
            $request->appendChild($signatureVersion);

            $XMLDatosEntrada = $doc->saveXML();

            $datosEntradaString = null;
            $isFoundRequestString = preg_match('/<DATOSENTRADA.*<\/DATOSENTRADA>/', $XMLDatosEntrada, $datosEntradaString);
            if (!$isFoundRequestString || !is_array($datosEntradaString) || (count($datosEntradaString) == 0)) {
                $this->_log('Could not parse SOAP notification. Request string could not be extracted.');
                return false;
            }

            /* set DS_SIGNATUREVERSION */
            $signatureForRefund = $this->_getSignatureForRefund($datosEntradaString[0],$order->getStore(), $rArr);
            $signature = $doc->createElement('DS_SIGNATURE', $signatureForRefund);
            $request->appendChild($signature);
        }
        else {
            $datosEntrada = $doc->createElement('DATOSENTRADA');
            $datosEntrada = $doc->appendChild($datosEntrada);

            $dsVersion = $doc->createElement('DS_Version', '0.1');
            $datosEntrada->appendChild($dsVersion);

            $merchantAmount = $doc->createElement('DS_MERCHANT_AMOUNT', $amount);
            $datosEntrada->appendChild($merchantAmount);

            $merchantCurrency = $doc->createElement('DS_MERCHANT_CURRENCY', $currency);
            $datosEntrada->appendChild($merchantCurrency);

            $merchantOrder = $doc->createElement('DS_MERCHANT_ORDER', $ordernum);
            $datosEntrada->appendChild($merchantOrder);

            $merchantCode = $doc->createElement('DS_MERCHANT_MERCHANTCODE', $code);
            $datosEntrada->appendChild($merchantCode);

            $merchantUrl = $doc->createElement('DS_MERCHANT_MERCHANTURL', $url);
            $datosEntrada->appendChild($merchantUrl);

            $merchantSignature = $doc->createElement('DS_MERCHANT_MERCHANTSIGNATURE', $signature);
            $datosEntrada->appendChild($merchantSignature);

            $merchantTerminal = $doc->createElement('DS_MERCHANT_TERMINAL', $terminal);
            $datosEntrada->appendChild($merchantTerminal);

            $merchantTranstype = $doc->createElement('DS_MERCHANT_TRANSACTIONTYPE', $operation);
            $datosEntrada->appendChild($merchantTranstype);
        }

        $rd = $doc->saveXML();
        $this->_getHelper()->log("------------------ START REFUND REQUEST -----------------");
        $this->_getHelper()->log($rd);
        $this->_getHelper()->log("------------------- END REFUND REQUEST ------------------");
        $this->_getHelper()->log("");
        $entrada = 'entrada=' . $rd;

        $curlSession = curl_init();

        // Set the URL
        curl_setopt($curlSession, CURLOPT_URL, $redsysUrl);
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

        //if ($isSha256Configured) {
            $rawresponse = str_replace('<RECIBIDO><?xml version="1.0"?>','<RECIBIDO>',$rawresponse);
        //}
        $rc = simplexml_load_string($rawresponse);

        if (!$rc) {
            Mage::throwException("Error para desmontar el XML de la respuesta");
        }

        if (!isset($rc->CODIGO)) {
            Mage::throwException("Error de comunicacion irreparable, comuniquese con Redsys");
        }
        else {
            Mage::log("RC CODIGO RESPONSE REFUND => " . $rc->CODIGO->__toString() ,null, "sha256.log");
            if ($rc->CODIGO->__toString() != '0') {
                Mage::throwException($this->_getHelper()->getErrorDescription($rc->CODIGO->__toString()));
            }
            return $rc;
        }
    }
    
    /**
     *
     * @return Interactiv4_RedsysPro_Helper_Data
     */
    protected function _getHelper() {
        return Mage::helper('i4redsyspro');
    }
    
    /**
     * TODO REVISAR ESTO
     * @param mixed $store
     * @return string 
     */
    public function getClave($store = null) {
        $mode = $this->getConfigData('redsysenvironment', $store);
        $signatureAlgorithm = $this->getConfigData('signaturealgorithm', $store);

        switch ($signatureAlgorithm) {
            case Interactiv4_RedsysPro_Model_Config_Source_Signaturealgorithm::SHA1_ALGORITHM:
                $result = $mode ? $this->getConfigData('merchantpassword', $store) : $this->getConfigData('devpassword', $store);
                break;

            case Interactiv4_RedsysPro_Model_Config_Source_Signaturealgorithm::SHA256_ALGORITHM:
                // Estamos incluyendo la clave en los datos de la firma y no se si es válido
                $result = $mode ? $this->getConfigData('merchantpassword256', $store) : $this->getConfigData('devpassword256', $store);
                break;

            default:
                $result = $mode ? $this->getConfigData('merchantpassword256', $store) : $this->getConfigData('devpassword256', $store);
        }

        return $result;
    }

    protected function _getSignature($string, $store = null, $data = array()) {

        $signatureAlgorithm = $this->getConfigData('signaturealgorithm', $store);
        $result = "";

        switch ($signatureAlgorithm) {
            case Interactiv4_RedsysPro_Model_Config_Source_Signaturealgorithm::SHA1_ALGORITHM:
                $result = strtoupper(sha1($string));
                break;

            case Interactiv4_RedsysPro_Model_Config_Source_Signaturealgorithm::SHA256_ALGORITHM:
                if (isset($data['Ds_Merchant_Order'])) {
                    $keyConfig = $this->getClave($store);
                    $base64decodedKey = base64_decode($keyConfig);
                    $params = base64_encode(json_encode($data));
                    $keyByOrderReference = $this->_getHelper()->encrypt_3DES($data['Ds_Merchant_Order'], $base64decodedKey);
                    $result = base64_encode(hash_hmac('sha256', $params, $keyByOrderReference, true));
                }
                break;

            default:
                if (isset($data['Ds_Merchant_Order'])) {
                    $keyConfig = $this->getClave($store);
                    $base64decodedKey = base64_decode($keyConfig);
                    $params = base64_encode(json_encode($data));
                    $keyByOrderReference = $this->_getHelper()->encrypt_3DES($data['Ds_Merchant_Order'], $base64decodedKey);
                    $result = base64_encode(hash_hmac('sha256', $params, $keyByOrderReference, true));
                }
        }

        return $result;
    }

    protected function _getSignatureForRefund($string, $store = null, $data = array()) {

        $signatureAlgorithm = $this->getConfigData('signaturealgorithm', $store);
        $result = "";

        switch ($signatureAlgorithm) {
            case Interactiv4_RedsysPro_Model_Config_Source_Signaturealgorithm::SHA1_ALGORITHM:
                $result = strtoupper(sha1($string));
                break;

            case Interactiv4_RedsysPro_Model_Config_Source_Signaturealgorithm::SHA256_ALGORITHM:
                if (isset($data['DS_MERCHANT_ORDER'])) {
                    $keyConfig = $this->getClave($store);
                    $base64decodedKey = base64_decode($keyConfig);
                    $keyByOrderReference = $this->_getHelper()->encrypt_3DES($data['DS_MERCHANT_ORDER'], $base64decodedKey);
                    $result = base64_encode(hash_hmac('sha256', $string, $keyByOrderReference, true));
                }
                break;

            default:
                if (isset($data['DS_MERCHANT_ORDER'])) {
                    $keyConfig = $this->getClave($store);
                    $base64decodedKey = base64_decode($keyConfig);
                    $keyByOrderReference = $this->_getHelper()->encrypt_3DES($data['DS_MERCHANT_ORDER'], $base64decodedKey);
                    $result = base64_encode(hash_hmac('sha256', $string, $keyByOrderReference, true));
                }
        }

        return $result;
    }

    protected function _getWhatSignatureRefundShouldBe($response, $store = null, $data = array()) {

        $amount         = isset($response->OPERACION->Ds_Amount) ? $response->OPERACION->Ds_Amount->__toString() : "";
        $orderRef       = isset($response->OPERACION->Ds_Order) ? $response->OPERACION->Ds_Order->__toString() : "";
        $merchantCode   = isset($response->OPERACION->Ds_MerchantCode) ? $response->OPERACION->Ds_MerchantCode->__toString() : "";
        $currency       = isset($response->OPERACION->Ds_Currency) ? $response->OPERACION->Ds_Currency->__toString() : "";
        $responseCode   = isset($response->OPERACION->Ds_Response) ? $response->OPERACION->Ds_Response->__toString() : "";
        $transactionType= isset($response->OPERACION->Ds_TransactionType) ? $response->OPERACION->Ds_TransactionType->__toString() : "";
        $securePayment  = isset($response->OPERACION->Ds_SecurePayment) ? $response->OPERACION->Ds_SecurePayment->__toString() : "";

        $string = $amount . $orderRef . $merchantCode . $currency . $responseCode . $transactionType . $securePayment;

        $keyConfig = $this->getClave($store);
        $base64decodedKey = base64_decode($keyConfig);
        $keyByOrderReference = $this->_getHelper()->encrypt_3DES($orderRef, $base64decodedKey);
        $result = base64_encode(hash_hmac('sha256', $string, $keyByOrderReference, true));

        return $result;
    }
}
