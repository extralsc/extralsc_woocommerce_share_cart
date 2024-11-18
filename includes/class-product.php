<?php
class Extralsc_WSC_Product {
    public $product_id;
    public $name;
    public $price;
    public $description;

    public function __construct($product_id) {
        // Retrieve the product using the WooCommerce WC_Product class
        $product = wc_get_product($product_id);
        
        if ($product) {
            // Fill in the class properties based on the product data
            $this->product_id = $product->get_id();
            $this->name = $product->get_name();
            $this->price = $product->get_price();
            $this->description = $product->get_description();
        } else {
            // Handle if product is not found
            error_log("Product with ID $product_id could not be found.");
        }
    }
}
