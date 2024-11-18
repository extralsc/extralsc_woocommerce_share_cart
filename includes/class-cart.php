<?php
class Extralsc_WSC_Cart {
    public $cart_id;
    public $cart_token;
    public $user_id;
    public $created_at;
    public $updated_at;

    public function __construct($cart_id = null, $cart_token = null) {
        global $wpdb;
        if ($cart_id) {
            $this->cart_id = $cart_id;
            $this->cart_token = $cart_token;
            $cart_data = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}extralsc_wsc_carts WHERE cart_id = $cart_id");
            $this->user_id = $cart_data->user_id;
            $this->created_at = $cart_data->created_at;
            $this->updated_at = $cart_data->updated_at;
        }
    }

    public static function create_cart($userId) {
        global $wpdb;
        $created_at = current_time('mysql');
        $updated_at = $created_at;
        $randomToken = bin2hex(random_bytes(20));
        $wpdb->insert(
            "{$wpdb->prefix}extralsc_wsc_carts",
            [
                'cart_token' => $randomToken,
                'user_id' => $userId,
                'created_at' => $created_at,
                'updated_at' => $updated_at
            ]
        );
        return new Extralsc_WSC_Cart($wpdb->insert_id, $randomToken);
    }
}
