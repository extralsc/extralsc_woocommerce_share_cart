<?php
class Extralsc_WSC_Product {
    public $product_id;
    public $name;
    public $price;
    public $description;

    public function __construct($product_id) {
        // Hämta produkten med WooCommerce WC_Product klass
        $product = wc_get_product($product_id);
        
        if ($product) {
            // Fyll i klassens egenskaper baserat på produktens data
            $this->product_id = $product->get_id();
            $this->name = $product->get_name();
            $this->price = $product->get_price();
            $this->description = $product->get_description();
        } else {
            // Hantera om produkten inte hittas
            error_log("Produkt med ID $product_id hittades inte.");
        }
    }
}
