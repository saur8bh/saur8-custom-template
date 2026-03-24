<?php
/**
 * Admin Notices - handles success/error messages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Saur8_CT_Admin_Notices {

    private static $notices = array();

    public static function add( $message, $type = 'success' ) {
        self::$notices[] = array(
            'message' => $message,
            'type'    => $type,
        );

        // Store in transient for redirect persistence
        set_transient( 'saur8_ct_notices', self::$notices, 30 );
    }

    public static function init() {
        add_action( 'admin_notices', array( __CLASS__, 'display' ) );
    }

    public static function display() {
        // Get notices from transient
        $notices = get_transient( 'saur8_ct_notices' );

        if ( ! $notices ) {
            return;
        }

        foreach ( $notices as $notice ) {
            $type = esc_attr( $notice['type'] );
            $message = esc_html( $notice['message'] );
            
            echo '<div class="saur8-notice saur8-notice--' . $type . '">';
            echo $message;
            echo '</div>';
        }

        // Clear transient
        delete_transient( 'saur8_ct_notices' );
    }
}

Saur8_CT_Admin_Notices::init();
