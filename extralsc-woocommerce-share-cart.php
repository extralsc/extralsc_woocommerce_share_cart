<?php
/**
 * Plugin Name: Extralsc Woocommerce Share Cart
 * Plugin URI: https://github.com/extralsc/extralsc_woocommerce_share_cart
 * Description: Handle Woocommerce Shopping Cart, create and share cart. Integrated with API calls.
 * Version: 1.0
 * Author: Extralsc
 * Author URI: https://github.com/extralsc
 * License: GPL2
 * Text Domain: extralsc
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


// Definiera pluginets version och katalog
define( 'EXTRALSC_WSC_PLUGIN_VERSION', '1.0' );
define( 'EXTRALSC__WSC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Ladda alla inkluderade filer
require_once plugin_dir_path(__FILE__) . 'includes/class-cart.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-product.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-cart-item.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-cart-sharing.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-cart-session.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-api.php';

// Aktivera plugin
function extralsc_wsc_activate_plugin()
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // SQL för att skapa tabeller
    $sql_carts = "
    CREATE TABLE IF NOT EXISTS {$wpdb->prefix}extralsc_wsc_carts (
        cart_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id VARCHAR(255) NOT NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        PRIMARY KEY (cart_id)
    ) $charset_collate;
    ";

    $sql_products = "
    CREATE TABLE IF NOT EXISTS {$wpdb->prefix}extralsc_wsc_products (
        product_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        description TEXT,
        PRIMARY KEY (product_id)
    ) $charset_collate;
    ";

    $sql_cart_items = "
    CREATE TABLE IF NOT EXISTS {$wpdb->prefix}extralsc_wsc_cart_items (
        cart_item_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        cart_id BIGINT(20) UNSIGNED NOT NULL,
        product_id BIGINT(20) UNSIGNED NOT NULL,
        quantity INT(10) UNSIGNED NOT NULL,
        total_price DECIMAL(10, 2) NOT NULL,
        PRIMARY KEY (cart_item_id),
        FOREIGN KEY (cart_id) REFERENCES {$wpdb->prefix}extralsc_wsc_carts(cart_id)
    ) $charset_collate;
    ";

    $sql_cart_sharing = "
    CREATE TABLE IF NOT EXISTS {$wpdb->prefix}extralsc_wsc_cart_sharing (
        sharing_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        cart_id BIGINT(20) UNSIGNED NOT NULL,
        shared_with_user_id VARCHAR(255) NOT NULL,
        shared_at DATETIME NOT NULL,
        PRIMARY KEY (sharing_id),
        FOREIGN KEY (cart_id) REFERENCES {$wpdb->prefix}extralsc_wsc_carts(cart_id)
    ) $charset_collate;
    ";

    $sql_cart_sessions = "
    CREATE TABLE IF NOT EXISTS {$wpdb->prefix}extralsc_wsc_cart_sessions (
        session_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        cart_id BIGINT(20) UNSIGNED NOT NULL,
        session_data TEXT NOT NULL,
        created_at DATETIME NOT NULL,
        PRIMARY KEY (session_id),
        FOREIGN KEY (cart_id) REFERENCES {$wpdb->prefix}extralsc_wsc_carts(cart_id)
    ) $charset_collate;
    ";

    // Försök att skapa tabeller
    try {
        if ($wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}extralsc_wsc_carts'") !== $wpdb->prefix . 'extralsc_wsc_carts') {
            $wpdb->query($sql_carts);
        }

        if ($wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}extralsc_wsc_products'") !== $wpdb->prefix . 'extralsc_wsc_products') {
            $wpdb->query($sql_products);
        }

        if ($wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}extralsc_wsc_cart_items'") !== $wpdb->prefix . 'extralsc_wsc_cart_items') {
            $wpdb->query($sql_cart_items);
        }

        if ($wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}extralsc_wsc_cart_sharing'") !== $wpdb->prefix . 'extralsc_wsc_cart_sharing') {
            $wpdb->query($sql_cart_sharing);
        }

        if ($wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}extralsc_wsc_cart_sessions'") !== $wpdb->prefix . 'extralsc_wsc_cart_sessions') {
            $wpdb->query($sql_cart_sessions);
        }
    } catch (Exception $e) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Plugin activation failed: ' . $e->getMessage());
        }
        wp_die('Database error occurred during plugin activation: ' . $e->getMessage());
    }
}
register_activation_hook(__FILE__, 'extralsc_wsc_activate_plugin');

function extralsc_wsc_uninstall_plugin()
{
    global $wpdb;

    // Lista över tabeller som ska tas bort
    $tables = [
        "{$wpdb->prefix}extralsc_wsc_carts",
        "{$wpdb->prefix}extralsc_wsc_products",
        "{$wpdb->prefix}extralsc_wsc_cart_items",
        "{$wpdb->prefix}extralsc_wsc_cart_sharing",
        "{$wpdb->prefix}extralsc_wsc_cart_sessions"
    ];

    // Ta bort varje tabell i listan
    foreach ($tables as $table) {
        $wpdb->query("DROP TABLE IF EXISTS $table");
    }
}
register_uninstall_hook(__FILE__, 'extralsc_wsc_uninstall_plugin');

// REST API slutpunkter
add_action('rest_api_init', array('Extralsc_WSC_API', 'register_routes'));

// Rewrite-regel för delbar varukorg
function extralsc_wsc_add_rewrite_rule()
{
    add_rewrite_rule('^extralsc-wsc/([0-9]+)/?', 'index.php?extralsc_cart_id=$matches[1]', 'top');
}
add_action('init', 'extralsc_wsc_add_rewrite_rule');

// Ny query var för delade varukorgar
function extralsc_wsc_add_query_vars($vars)
{
    $vars[] = 'extralsc_cart_id';
    return $vars;
}
add_filter('query_vars', 'extralsc_wsc_add_query_vars');

// Lägg till en template redirect för delad kundvagn
function extralsc_wsc_template_redirect()
{
    $cart_id = get_query_var('extralsc_cart_id');
    if ($cart_id) {
        include plugin_dir_path(__FILE__) . 'templates/shared-cart.php';
        exit;
    }
}
add_action('template_redirect', 'extralsc_wsc_template_redirect');

// Shortcode för att visa kundvagn baserat på cart_id
function extralsc_display_cart_by_id_shortcode()
{
    if (isset($_GET['cart_id'])) {
        $cart_id = sanitize_text_field($_GET['cart_id']); // Sanera input

        $cart_data = extralsc_get_cart_data($cart_id);

        if ($cart_data) {
            $total = 0;
            WC()->cart->empty_cart();


            $output = '<h2>Innehåll i kundvagnen</h2>';
            foreach ($cart_data->items as $item) {
                $item = (object) $item;
                // Lägg till produkten i WooCommerce varukorgen
                WC()->cart->add_to_cart($item->product_id, $item->quantity);
                $total = $total + ($item->price_incl_tax * $item->quantity);
                // Visa produktinformation
                $output .= '<div>';
                $output .= '<img src="' . $item->image_url . '" width="50" height="50" />';
                $output .= '<p>Produktnamn: ' . $item->name . '</p>';
                $output .= '<p>Pris (exkl. moms): ' . wc_price($item->price_excl_tax) . '</p>';
                $output .= '<p>Pris (inkl. moms): ' . wc_price($item->price_incl_tax) . '</p>';
                $output .= '<p>Momsbelopp: ' . wc_price($item->tax_amount) . '</p>';
                $output .= '<p>Kvantitet: ' . $item->quantity . '</p>';
                $output .= '<p>Totalpris: ' . $item->total_price . '</p>';
                $output .= '</div><hr>';
            }

            $output .= '<b>Totalt inkl. moms: ' . wc_price($total) . '</b><hr />';


            // Lägg till en knapp för att lägga alla produkter i varukorgen och fortsätt till kassan
            $output .= '<form action="' . esc_url(add_query_arg('add_to_cart_and_checkout', '1', $_SERVER['REQUEST_URI'])) . '" method="POST">';
            $output .= '<input type="submit" value="Fortsätt och köp">';
            $output .= '</form>';

            return $output;
        } else {
            return '<p>Kundvagnen kunde inte hittas.</p>';
        }
    } else {
        return '<p>Ingen kundvagn angiven.</p>';
    }
}
add_shortcode('display_cart', 'extralsc_display_cart_by_id_shortcode');

// Funktion för att hämta kundvagnsdata och produktinformation
function extralsc_get_cart_data($cart_id)
{
    global $wpdb;

    // Hämta kundvagnsdata
    $cart = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}extralsc_wsc_carts WHERE cart_id = %s", $cart_id));

    if ($cart) {
        $cart_items = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}extralsc_wsc_cart_items WHERE cart_id = %s",
            $cart_id
        ));

        if ($cart_items) {
            $cart->items = [];

            foreach ($cart_items as $item) {
                $product = wc_get_product($item->product_id);
                if ($product) {
                    // Hämta priser inklusive och exklusive moms
                    $price_excl_tax = $product->get_price_excluding_tax();
                    $price_incl_tax = $product->get_price_including_tax();
                    $tax_amount = $price_incl_tax - $price_excl_tax; // Beräkna moms

                    $cart->items[] = [
                        'product_id' => $item->product_id,
                        'name' => $product->get_name(),
                        'price_excl_tax' => $price_excl_tax,
                        'price_incl_tax' => $price_incl_tax,
                        'quantity' => $item->quantity,
                        'total_price' => wc_price($price_incl_tax * $item->quantity) . ' inkl. moms.',
                        'image_url' => wp_get_attachment_url($product->get_image_id()),
                        'tax_amount' => $tax_amount // Momsbelopp
                    ];
                }
            }
        }
        return $cart;
    }
    return false; // Return false if cart not found
}

function extralsc_add_products_to_cart_and_redirect()
{
    if (isset($_GET['add_to_cart_and_checkout']) && $_GET['add_to_cart_and_checkout'] === '1' && isset($_GET['cart_id'])) {
        $cart_id = sanitize_text_field($_GET['cart_id']);
        $cart_data = extralsc_get_cart_data($cart_id);

        if ($cart_data && !empty($cart_data->items)) {
            foreach ($cart_data->items as $item) {
                $product_id = wc_get_product_id_by_sku($item->sku); // Eller använd product_id direkt om du har det
                $quantity = $item->quantity;

                // Lägg till produkterna i WooCommerce varukorg
                WC()->cart->add_to_cart($product_id, $quantity);
            }

            // Skicka användaren vidare till kassan
            wp_redirect(wc_get_checkout_url());
            exit;
        }
    }
}
add_action('template_redirect', 'extralsc_add_products_to_cart_and_redirect');
