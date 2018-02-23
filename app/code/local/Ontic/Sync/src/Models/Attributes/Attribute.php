<?php

namespace Ontic\Sync\Models\Attributes;

use Mage_Catalog_Model_Product;
use Ontic\Sync\Models\Transaction;

class Attribute
{
    private $code;

    function __construct($code)
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param Transaction $transaction
     * @param array $source
     * @param Mage_Catalog_Model_Product $destination
     */
    public function update(Transaction $transaction, $source, Mage_Catalog_Model_Product $destination)
    {
        if(!array_key_exists($this->code, $source))
        {
            // El objeto origen no contiene el atributo, no hacemos nada
            return;
        }

        $sourceValue = $this->getSourceValue($source);
        $destinationValue = $this->getDestinationValue($destination);
        if($this->areEqual($sourceValue, $destinationValue))
        {
            // El objeto origen y el destino contiene el mismo valor, no hacemos nada
            return;
        }

        // Añadimos una actualización de producto con el cambio
        $this->onValueChanged($transaction, $sourceValue);
    }

    protected function onValueChanged(Transaction $transaction, $newValue)
    {
        $transaction->addAttributeUpdate($this->getCode(), $newValue);
    }

    /**
     * @param mixed $value1
     * @param mixed $value2
     * @return bool
     */
    protected function areEqual($value1, $value2)
    {
        return $value1 === $value2;
    }

    protected function getSourceValue($source)
    {
        return $source[$this->getCode()];
    }

    protected function getDestinationValue(Mage_Catalog_Model_Product $destination)
    {
        return call_user_func([$destination, $this->getMethodName()]);
    }

    protected function getMethodName()
    {
        return 'get' . $str = str_replace('_', '', ucwords($this->getCode(), '_'));
    }
}