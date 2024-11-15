<?php
// Ladda klasser för varukorg, produkter, varukorgsobjekt, delning och sessioner
require_once plugin_dir_path(__FILE__) . 'class-cart.php';
require_once plugin_dir_path(__FILE__) . 'class-product.php';
require_once plugin_dir_path(__FILE__) . 'class-cart-item.php';
require_once plugin_dir_path(__FILE__) . 'class-cart-sharing.php'; // Laddar Cart Sharing
require_once plugin_dir_path(__FILE__) . 'class-cart-session.php';  // Laddar Cart Session

class Extralsc_WSC_API
{

    // Registrera REST API slutpunkter
    public static function register_routes()
    {
        register_rest_route('extralsc-wsc/v1', '/add-to-woocommerce-cart', array(
            'methods' => 'POST',
            'callback' => array(__CLASS__, 'add_to_woocommerce_cart'),
            'permission_callback' => '__return_true',
        ));

        // Skapa varukorg
        register_rest_route('extralsc-wsc/v1', '/create-cart', array(
            'methods' => 'POST',
            'callback' => array('Extralsc_WSC_API', 'create_cart'),
            'permission_callback' => '__return_true', // Behörighetskontroll kan implementeras här
        ));

        // Lägg till produkt till varukorg
        register_rest_route('extralsc-wsc/v1', '/add-to-cart', array(
            'methods' => 'POST',
            'callback' => array('Extralsc_WSC_API', 'add_to_cart'),
            'permission_callback' => '__return_true',
        ));

        // Ta bort produkt från varukorg
        register_rest_route('extralsc-wsc/v1', '/remove-from-cart', array(
            'methods' => 'DELETE',
            'callback' => array('Extralsc_WSC_API', 'remove_from_cart'),
            'permission_callback' => '__return_true',
        ));

        // Dela varukorg
        register_rest_route('extralsc-wsc/v1', '/share-cart', array(
            'methods' => 'POST',
            'callback' => array('Extralsc_WSC_API', 'share_cart'),
            'permission_callback' => '__return_true',
        ));

        // Hämta delade varukorgar
        register_rest_route('extralsc-wsc/v1', '/shared-carts', array(
            'methods' => 'GET',
            'callback' => array('Extralsc_WSC_API', 'get_shared_carts'),
            'permission_callback' => '__return_true',
        ));

        // Skapa session
        register_rest_route('extralsc-wsc/v1', '/create-session', array(
            'methods' => 'POST',
            'callback' => array('Extralsc_WSC_API', 'create_session'),
            'permission_callback' => '__return_true',
        ));

        // Hämta session
        register_rest_route('extralsc-wsc/v1', '/get-session', array(
            'methods' => 'GET',
            'callback' => array('Extralsc_WSC_API', 'get_session'),
            'permission_callback' => '__return_true',
        ));
    }

    // Skapa varukorg
    public static function create_cart(WP_REST_Request $request)
    {
        $user_id = get_current_user_id();
        if (!$user_id) {
            // return new WP_Error('no_user', 'No user is logged in', array('status' => 400));
            $user_id = 'guest_' . uniqid(); // Exempel på en anonym användaridentifierare

        }

        $cart = Extralsc_WSC_Cart::create_cart($user_id);
        return rest_ensure_response(['cart_id' => $cart->cart_id]);
    }

    // Lägg till produkt i varukorg
    public static function add_to_cart(WP_REST_Request $request)
    {
        $cart_id = $request->get_param('cart_id');
        $product_id = $request->get_param('product_id');
        $quantity = $request->get_param('quantity');

        if (!$cart_id || !$product_id || !$quantity) {
            return new WP_Error('missing_params', 'Missing required parameters', array('status' => 400));
        }

        Extralsc_WSC_Cart_Item::add_item($cart_id, $product_id, $quantity);
        return rest_ensure_response(['status' => 'success']);
    }

    // Ta bort produkt från varukorg
    public static function remove_from_cart(WP_REST_Request $request)
    {
        $cart_item_id = $request->get_param('cart_item_id');

        if (!$cart_item_id) {
            return new WP_Error('missing_cart_item', 'Missing cart item ID', array('status' => 400));
        }

        Extralsc_WSC_Cart_Item::remove_item($cart_item_id);
        return rest_ensure_response(['status' => 'success']);
    }

    // Dela varukorg
    public static function share_cart(WP_REST_Request $request)
    {
        $cart_id = $request->get_param('cart_id');
        $shared_with_user_id = $request->get_param('shared_with_user_id');

        if (!$cart_id || !$shared_with_user_id) {
            return new WP_Error('missing_params', 'Missing required parameters', array('status' => 400));
        }

        $sharing = Extralsc_WSC_Cart_Sharing::share_cart($cart_id, $shared_with_user_id);
        return rest_ensure_response(['sharing_id' => $sharing->sharing_id]);
    }

    // Hämta alla delade varukorgar för användare
    public static function get_shared_carts(WP_REST_Request $request)
    {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return new WP_Error('no_user', 'No user is logged in', array('status' => 400));
        }

        $shared_carts = Extralsc_WSC_Cart_Sharing::get_shared_carts($user_id);
        return rest_ensure_response($shared_carts);
    }

    // Skapa session för varukorg
    public static function create_session(WP_REST_Request $request)
    {
        $cart_id = $request->get_param('cart_id');
        $session_data = $request->get_param('session_data');

        if (!$cart_id || !$session_data) {
            return new WP_Error('missing_params', 'Missing required parameters', array('status' => 400));
        }

        $session = Extralsc_WSC_Cart_Session::create_session($cart_id, $session_data);
        return rest_ensure_response(['session_id' => $session->session_id]);
    }

    // Hämta session för varukorg
    public static function get_session(WP_REST_Request $request)
    {
        $cart_id = $request->get_param('cart_id');

        if (!$cart_id) {
            return new WP_Error('missing_cart_id', 'Missing cart ID', array('status' => 400));
        }

        $session = Extralsc_WSC_Cart_Session::get_session($cart_id);
        return rest_ensure_response($session);
    }

    public static function add_to_woocommerce_cart(WP_REST_Request $request) {
        $cart_id = $request->get_param('cart_id');
        if (!$cart_id) {
            return new WP_Error('missing_param', 'Cart ID saknas', array('status' => 400));
        }

        // Hämta varukorgens varor baserat på cart_id (här ska en funktion som hämtar varor implementeras)
        $cart_items = Extralsc_WSC_Cart_Item::get_items_by_cart_id($cart_id);
        if (!$cart_items) {
            return new WP_Error('no_cart', 'Varukorgen är tom eller existerar inte', array('status' => 400));
        }

        // Töm WooCommerce-varukorgen
        WC()->cart->empty_cart();

        // Lägg till varje varupost i WooCommerce-varukorgen
        foreach ($cart_items as $item) {
            WC()->cart->add_to_cart($item->product_id, $item->quantity);
        }

        return rest_ensure_response(['success' => true, 'message' => 'Produkter har lagts till i varukorgen']);
    }
}

// Registrera API-rutter när pluginet laddas
add_action('rest_api_init', array('Extralsc_WSC_API', 'register_routes'));
