<?php
// Load classes for cart, products, cart objects, sharing, and sessions
require_once plugin_dir_path(__FILE__) . 'class-cart.php';
require_once plugin_dir_path(__FILE__) . 'class-product.php';
require_once plugin_dir_path(__FILE__) . 'class-cart-item.php';
require_once plugin_dir_path(__FILE__) . 'class-cart-sharing.php'; // Laddar Cart Sharing
require_once plugin_dir_path(__FILE__) . 'class-cart-session.php';  // Laddar Cart Session

class Extralsc_WSC_API
{

    // Register REST API endpoints
    public static function register_routes()
    {
        register_rest_route('extralsc-wsc/v1', '/add-to-woocommerce-cart', array(
            'methods' => 'POST',
            'callback' => array(__CLASS__, 'add_to_woocommerce_cart'),
            'permission_callback' => '__return_true',
        ));

        // Create cart
        register_rest_route('extralsc-wsc/v1', '/create-cart', array(
            'methods' => 'POST',
            'callback' => array('Extralsc_WSC_API', 'create_cart'),
            'permission_callback' => '__return_true', // Behörighetskontroll kan implementeras här
        ));

       // Add product to cart
        register_rest_route('extralsc-wsc/v1', '/add-to-cart', array(
            'methods' => 'POST',
            'callback' => array('Extralsc_WSC_API', 'add_to_cart'),
            'permission_callback' => '__return_true',
        ));

        // Remove product from cart
        register_rest_route('extralsc-wsc/v1', '/remove-from-cart', array(
            'methods' => 'DELETE',
            'callback' => array('Extralsc_WSC_API', 'remove_from_cart'),
            'permission_callback' => '__return_true',
        ));

        // Share cart
        register_rest_route('extralsc-wsc/v1', '/share-cart', array(
            'methods' => 'POST',
            'callback' => array('Extralsc_WSC_API', 'share_cart'),
            'permission_callback' => '__return_true',
        ));

        // Retrieve shared carts
        register_rest_route('extralsc-wsc/v1', '/shared-carts', array(
            'methods' => 'GET',
            'callback' => array('Extralsc_WSC_API', 'get_shared_carts'),
            'permission_callback' => '__return_true',
        ));

        // Create session
        register_rest_route('extralsc-wsc/v1', '/create-session', array(
            'methods' => 'POST',
            'callback' => array('Extralsc_WSC_API', 'create_session'),
            'permission_callback' => '__return_true',
        ));

        // Retrieve session
        register_rest_route('extralsc-wsc/v1', '/get-session', array(
            'methods' => 'GET',
            'callback' => array('Extralsc_WSC_API', 'get_session'),
            'permission_callback' => '__return_true',
        ));
    }

    public static function get_cart_id_from_token($cart_token)
    {
        global $wpdb;
        
        $cartIdQuery = $wpdb->prepare("SELECT cart_id FROM {$wpdb->prefix}extralsc_wsc_carts WHERE cart_token = %d", $cart_token);
        $cartRow = $wpdb->get_row($cartIdQuery);
        $cart_id = $cartRow->cart_id;

        return $cart_id;
    }

    // Create cart
    public static function create_cart(WP_REST_Request $request)
    {
        $user_id = get_current_user_id();
        if (!$user_id) {
            // return new WP_Error('no_user', 'No user is logged in', array('status' => 400));
            $user_id = 'guest_' . uniqid() . microtime(); // Example of an anonymous user identifier, not very unique...

        }

        $cart = Extralsc_WSC_Cart::create_cart($user_id);
        return rest_ensure_response(['cart_id' => $cart->cart_id, 'cart_token' => $cart->cart_token]);
    }

    // Add product to cart
    public static function add_to_cart(WP_REST_Request $request)
    {
        $cart_token = $request->get_param('cart_token');
        $cart_id = self::get_cart_id_from_token($cart_token);

        $product_id = $request->get_param('product_id');
        $quantity = $request->get_param('quantity');

        if (!$cart_id || !$product_id || !$quantity) {
            return new WP_Error('missing_params', 'Missing required parameters', array('status' => 400));
        }

        Extralsc_WSC_Cart_Item::add_item($cart_id, $product_id, $quantity);
        return rest_ensure_response(['status' => 'success']);
    }

    // Remove product from cart
    public static function remove_from_cart(WP_REST_Request $request)
    {
        $cart_item_id = $request->get_param('cart_item_id');

        if (!$cart_item_id) {
            return new WP_Error('missing_cart_item', 'Missing cart item ID', array('status' => 400));
        }

        Extralsc_WSC_Cart_Item::remove_item($cart_item_id);
        return rest_ensure_response(['status' => 'success']);
    }

    // Share cart
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

    // Retrieve all shared carts for user
    public static function get_shared_carts(WP_REST_Request $request)
    {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return new WP_Error('no_user', 'No user is logged in', array('status' => 400));
        }

        $shared_carts = Extralsc_WSC_Cart_Sharing::get_shared_carts($user_id);
        return rest_ensure_response($shared_carts);
    }

    // Create session for cart
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

    // Retrieve session for cart
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
            return new WP_Error('missing_param', 'Cart ID is missing', array('status' => 400));
        }

        // Retrieve cart items based on cart_id (a function to fetch items should be implemented here)
        $cart_items = Extralsc_WSC_Cart_Item::get_items_by_cart_id($cart_id);
        if (!$cart_items) {
            return new WP_Error('no_cart', 'Varukorgen är tom eller existerar inte', array('status' => 400));
        }

        // Empty the WooCommerce cart
        WC()->cart->empty_cart();

        // Add each product item to the WooCommerce cart
        foreach ($cart_items as $item) {
            WC()->cart->add_to_cart($item->product_id, $item->quantity);
        }

        return rest_ensure_response(['success' => true, 'message' => 'Products have been added to the cart']);
    }
}

// Register API routes when the plugin loads
add_action('rest_api_init', array('Extralsc_WSC_API', 'register_routes'));
