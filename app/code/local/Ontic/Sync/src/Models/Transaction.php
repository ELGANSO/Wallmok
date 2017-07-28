<?php

namespace Ontic\Sync\Models;

use Mage;

class Transaction
{
    /** @var  \Mage_Catalog_Model_Product $product */
    private $product;
    /** @var TransactionInfo */
    private $info;
    /** @var array */
    private $updates;
    /** @var bool */
    private $requiresFullSave = false;
    /** @var callable[] */
    private $beforeUpdateActions = [];
    /** @var callable[] */
    private $beforeProductFullSaveActions = [];
    /** @var callable[] */
    private $afterProductFullSaveActions = [];

    /**
     * @param \Mage_Catalog_Model_Product $product
     */
    public function __construct(\Mage_Catalog_Model_Product $product)
    {
        $this->product = $product;
        $this->info = new TransactionInfo($this, $product);
    }

    public function addAttributeUpdate($attributeCode, $value)
    {
        $this->updates[$attributeCode] = $value;
    }

    public function requestFullSave()
    {
        $this->requiresFullSave = true;
    }

    /**
     * Añade una acción a realizar antes de realizar cambios de atributos
     * en el producto
     * @param $action
     */
    public function addBeforeUpdateAction($action)
    {
        $this->beforeUpdateActions[] = $action;
    }

    /**
     * Añade una acción a realizar antes de que se produzca el guardado
     * completo del producto. Utilizar esta función no implica necesariamente
     * que se vaya a hacer un guardado completo, para asegurar que sea así
     * llamar también a requestFullSave()
     * @param $action
     */
    public function addBeforeProductFullSaveAction($action)
    {
        $this->beforeProductFullSaveActions[] = $action;
    }

    /**
     * Añade una acción a realizar después de que se produzca el guardado
     * completo del producto. Utilizar esta función no implica necesariamente
     * que se vaya a hacer un guardado completo, para asegurar que sea así
     * llamar también a requestFullSave()
     * @param $action
     */
    public function addAfterProductFullSaveAction($action)
    {
        $this->afterProductFullSaveActions[] = $action;
    }

    /**
     * Aplica los actualizaciones pendientes
     * @return int El número de actualizaciones aplicadas
     */
    public function commit()
    {
        $updateCount = count($this->updates);

        if($updateCount === 0 && count($this->beforeUpdateActions) === 0 && count($this->beforeProductFullSaveActions) === 0)
        {
            // No hay nada que hacer, salimos directamente
            return 0;
        }

        // Lanzamos las acciones previas a la actualización de atributos
        $updateCount += $this->performActions($this->beforeUpdateActions);

        // Por defecto intentaremos realizar las actualizaciones de atributos
        // llamando a catalog/product_action::updateAttributes,
        // pero si se nos ha solicitado un guardado completo por alguna razón
        // recurrimos actualizamos los atributos en el producto y llamamos a ->save()
        if($this->requiresFullSave)
        {
            foreach($this->updates as $key => $value)
            {
                $this->product->setData($key, $value);
            }

            // Realizamos las acciones pendientes antes del guardado completo
            $updateCount += $this->performActions($this->beforeProductFullSaveActions);

            // Realizamos el guardado completo del producto
            $this->product->save();

            // Lanzamos las acciones posteriores al guardado del producto
            $updateCount += $this->performActions($this->afterProductFullSaveActions);
        }
        else
        {
            // Realizamos la actualización de atributos
            Mage::getSingleton('catalog/product_action')->updateAttributes(
                [ $this->product->getId() ],
                $this->updates,
                $this->product->getStoreId()
            );
        }

        // Devolvemos el nº de actualizaciones realizadas
        return $updateCount;
    }

    protected function performActions($actions)
    {
        $count = 0;

        foreach($actions as $action)
        {
            if($action($this->info))
            {
                $count++;
            }
        }

        return $count;
    }
}