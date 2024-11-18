<?php
class Extralsc_WSC_Cart_Session {
    public $session_id;
    public $cart_id;
    public $session_data;
    public $created_at;

    public function __construct($session_id) {
        global $wpdb;
        $session_data = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}extralsc_wsc_cart_sessions WHERE session_id = $session_id");
        $this->session_id = $session_data->session_id;
        $this->cart_id = $session_data->cart_id;
        $this->session_data = $session_data->session_data;
        $this->created_at = $session_data->created_at;
    }

    public static function create_session($cart_id, $session_data) {
        global $wpdb;
        $created_at = current_time('mysql');
        $wpdb->insert(
            "{$wpdb->prefix}extralsc_wsc_cart_sessions",
            [
                'cart_id' => $cart_id,
                'session_data' => json_encode($session_data), // Store session data as JSON
                'created_at' => $created_at
            ]
        );
        return new Extralsc_WSC_Cart_Session($wpdb->insert_id);
    }

    public static function get_session($cart_id) {
        global $wpdb;
        $session = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}extralsc_wsc_cart_sessions WHERE cart_id = %d",
                $cart_id
            )
        );
        return $session ? json_decode($session->session_data, true) : null;
    }

    public static function update_session($session_id, $session_data) {
        global $wpdb;
        $wpdb->update(
            "{$wpdb->prefix}extralsc_wsc_cart_sessions",
            [
                'session_data' => json_encode($session_data),
                'created_at' => current_time('mysql')
            ],
            ['session_id' => $session_id]
        );
    }
}
