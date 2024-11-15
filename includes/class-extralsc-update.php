<?php

class Ceeglo_Update {

    const GITHUB_API_URL = 'https://api.github.com/repos/ceeglo/ceeglo-cart/releases/latest'; // GitHub repo-URL

    // Kontrollera om det finns en ny version
    public static function check_for_updates() {
        $response = wp_remote_get( self::GITHUB_API_URL );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        
        if ( isset( $data['tag_name'] ) ) {
            return $data['tag_name']; // Ny version
        }

        return false;
    }

    // Hämta och installera uppdatering
    public static function install_update() {
        // Implementera logik för att hämta och installera uppdateringar från GitHub
    }
}
