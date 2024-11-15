<?php
class Extralsc_WSC_Cart_Sharing {
    public $sharing_id;
    public $cart_id;
    public $shared_with_user_id;
    public $shared_at;

    public function __construct($sharing_id) {
        global $wpdb;
        $sharing_data = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}extralsc_wsc_cart_sharing WHERE sharing_id = $sharing_id");
        $this->sharing_id = $sharing_data->sharing_id;
        $this->cart_id = $sharing_data->cart_id;
        $this->shared_with_user_id = $sharing_data->shared_with_user_id;
        $this->shared_at = $sharing_data->shared_at;
    }

    public static function share_cart($cart_id, $shared_with_user_id) {
        global $wpdb;
        $shared_at = current_time('mysql');
        $wpdb->insert(
            "{$wpdb->prefix}extralsc_wsc_cart_sharing",
            [
                'cart_id' => $cart_id,
                'shared_with_user_id' => $shared_with_user_id,
                'shared_at' => $shared_at
            ]
        );
        return new Extralsc_WSC_Cart_Sharing($wpdb->insert_id);
    }

    public static function get_shared_carts($user_id) {
        global $wpdb;
        $shared_carts = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}extralsc_wsc_cart_sharing WHERE shared_with_user_id = %d",
                $user_id
            )
        );
        return $shared_carts;
    }
}
