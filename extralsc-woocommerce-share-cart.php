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

// Förhindra direkt åtkomst
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Definiera pluginets version och katalog
define( 'EXTRALSC_PLUGIN_VERSION', '1.0' );
define( 'EXTRALSC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Inkludera de nödvändiga filerna
require_once( EXTRALSC_PLUGIN_DIR . 'includes/class-extralsc-settings.php' );
require_once( EXTRALSC_PLUGIN_DIR . 'includes/class-extralsc-license.php' );
require_once( EXTRALSC_PLUGIN_DIR . 'includes/class-extralsc-update.php' );

// Aktiveringshook
function extralsc_wsc_plugin_activate() {
    // Inled inställningar vid aktivering
    extralsc_wsc_settings_init();
    // Kontrollera licensnyckel vid aktivering
    extralsc_wsc_licence_validate();
}
register_activation_hook( __FILE__, 'extralsc_wsc_plugin_activate' );

// Lägga till huvudmenyn och undermenyer
function extralsc_wsc_add_admin_menu() {
    add_menu_page(
        'Ceeglo Cart', // Huvudrubrik i menyn
        'Ceeglo Cart', // Namn på huvudmeny
        'manage_options',
        'extralsc_wsc_cart',
        'extralsc_wsc_settings_page', // Funktion för huvudinställningar
        'dashicons-cart',
        6
    );

    add_submenu_page(
        'extralsc_wsc_cart',
        'Start',
        'Start',
        'manage_options',
        'extralsc_wsc_cart_start',
        'extralsc_wsc_start_page' // Funktion för startsidan
    );

    add_submenu_page(
        'extralsc_wsc_cart',
        'Inställningar',
        'Inställningar',
        'manage_options',
        'extralsc_wsc_cart_settings',
        'extralsc_wsc_settings_page' // Funktion för inställningar
    );
}
add_action( 'admin_menu', 'extralsc_wsc_add_admin_menu' );

// Funktion för startsidan
function extralsc_wsc_start_page() {
    echo '<h1>Välkommen till Ceeglo Cart</h1>';
}

// Funktion för inställningssidan
function extralsc_wsc_settings_page() {
    $license = extralsc_wsc_get_license_key();
    $license_status = extralsc_wsc_licence_validate();

    // Visa status om licensnyckeln är giltig eller inte
    if ( ! $license || ! $license_status ) {
        echo '<p>Licensnyckel är inte giltig. Vänligen ange en giltig licensnyckel för att fortsätta.</p>';
        extralsc_wsc_licence_form();
    } else {
        // Visa inställningar
        extralsc_wsc_settings_form();
    }
}

// Licensformulär
function extralsc_wsc_licence_form() {
    echo '<form method="post" action="">
        <input type="text" name="extralsc_wsc_license_key" placeholder="Ange licensnyckel" />
        <input type="submit" name="extralsc_wsc_save_license_key" value="Spara licensnyckel" />
    </form>';
    
    if ( isset( $_POST['extralsc_wsc_save_license_key'] ) ) {
        extralsc_wsc_save_license_key( $_POST['extralsc_wsc_license_key'] );
    }
}

// Spara licensnyckel
function extralsc_wsc_save_license_key( $license_key ) {
    update_option( 'extralsc_wsc_license_key', $license_key );
    extralsc_wsc_licence_validate();
}

add_action( 'admin_init', 'ceeglo_check_for_updates' );

function ceeglo_check_for_updates() {
    $latest_version = Ceeglo_Update::check_for_updates();
    if ( $latest_version && version_compare( CEEGLO_PLUGIN_VERSION, $latest_version, '<' ) ) {
        // Visa meddelande om uppdatering finns
        add_action( 'admin_notices', function() use ( $latest_version ) {
            echo '<div class="notice notice-warning is-dismissible">
                <p>En ny version (' . $latest_version . ') av Ceeglo Cart är tillgänglig.</p>
            </div>';
        });
    }
}

if ( ! wp_next_scheduled( 'ceeglo_license_validation' ) ) {
    wp_schedule_event( time(), 'daily', 'ceeglo_license_validation' );
}

add_action( 'ceeglo_license_validation', 'ceeglo_validate_license_cron' );

function ceeglo_validate_license_cron() {
    Ceeglo_Licence::validate_license();  // Validera licens en gång om dagen
}


