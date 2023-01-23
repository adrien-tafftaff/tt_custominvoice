<?php

class Product extends ProductCore
{
    /** @var string hscode */
    public $hscode;

    public function __construct($id_product = null, $full = false, $id_lang = null, $id_shop = null, \Context $context = null)
    {
        self::$definition['fields']['hscode'] = ['type' => self::TYPE_INT, 'validate' => 'isInt', 'size' => 10];
        parent::__construct($id_product, $full, $id_lang, $id_shop, $context);
    }
}