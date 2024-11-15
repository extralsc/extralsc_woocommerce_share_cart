<?php

class Ceeglo_Licence {
    
    const LICENSE_API_URL = 'https://api.vianord.se/validate_license'; // API URL för validering

    // Hämta licensnyckeln från databas
    public static function get_license_key() {
        return get_option( 'ceeglo_license_key', '' );
    }

    // Validera licensnyckeln
    public static function validate_license() {
        $license_key = self::get_license_key();
        
        if ( empty( $license_key ) ) {
            return false;
        }

        // Gör ett API-anrop för att validera licensnyckeln
        $response = wp_remote_post( self::LICENSE_API_URL, [
            'body' => json_encode( ['license_key' => $license_key] ),
            'headers' => [ 'Content-Type' => 'application/json' ],
        ]);

        if ( is_wp_error( $response ) ) {
            return false;
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );
        
        // Om svaret är giltigt
        return isset( $data['valid'] ) && $data['valid'];
    }
}
