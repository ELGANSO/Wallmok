<?php

namespace Ontic\Sync\Models;

class TransactionInfo
{
    /** @var Transaction */
    private $transaction;
    /** @var \Mage_Catalog_Model_Product */
    private $product;
    /** @var bool */
    private $isProductNew;

    /**
     * TransactionInfo constructor.
     * @param Transaction $transaction
     * @param \Mage_Catalog_Model_Product $product
     */
    public function __construct(Transaction $transaction, \Mage_Catalog_Model_Product $product)
    {
        $this->transaction = $transaction;
        $this->product = $product;
        $this->isProductNew = $this->product->isObjectNew();
    }

    /**
     * @return Transaction
     */
    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * @return \Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return bool
     */
    public function getIsProductNew()
    {
        return $this->isProductNew;
    }
}