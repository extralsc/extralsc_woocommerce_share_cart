<?php
class Extralsc_WSC_Cart_Item
{
    public $cart_item_id;
    public $cart_id;
    public $product_id;
    public $quantity;
    public $total_price;

    public function __construct($cart_item_id)
    {
        global $wpdb;
        $cart_item_data = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}extralsc_wsc_cart_items WHERE cart_item_id = $cart_item_id");
        $this->cart_item_id = $cart_item_data->cart_item_id;
        $this->cart_id = $cart_item_data->cart_id;
        $this->product_id = $cart_item_data->product_id;
        $this->quantity = $cart_item_data->quantity;
        $this->total_price = $cart_item_data->total_price;
    }

    public static function get_items_by_cart_id($cart_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'extralsc_wsc_cart_items';
        $query = $wpdb->prepare("SELECT * FROM $table_name WHERE cart_id = %d", $cart_id);
        return $wpdb->get_results($query);
    }

    public static function add_item($cart_id, $product_id, $quantity)
    {
        global $wpdb;
        $product = new Extralsc_WSC_Product($product_id);
        $total_price = $product->price * $quantity;
        $insert = $wpdb->insert(
            "{$wpdb->prefix}extralsc_wsc_cart_items",
            [
                'cart_id' => $cart_id,
                'product_id' => $product_id,
                'quantity' => $quantity,
                'total_price' => $total_price
            ]
        );

        var_dump($insert);
        return;
    }

    public static function remove_item($cart_item_id)
    {
        global $wpdb;
        $wpdb->delete("{$wpdb->prefix}extralsc_wsc_cart_items", ['cart_item_id' => $cart_item_id]);
    }
}
