<?php
/**
 * Front Page Settings - manages WordPress front page options
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Saur8_CT_Front_Page {

    public function __construct() {
        add_action( 'admin_post_saur8_save_front_page', array( $this, 'handle_save' ) );
    }

    public static function get_current_settings() {
        return array(
            'show_on_front'  => get_option( 'show_on_front', 'posts' ),
            'page_on_front'  => get_option( 'page_on_front', 0 ),
            'page_for_posts' => get_option( 'page_for_posts', 0 ),
        );
    }

    public static function save_settings( $homepage_id, $posts_page_id ) {
        update_option( 'show_on_front', 'page' );
        update_option( 'page_on_front', intval( $homepage_id ) );
        update_option( 'page_for_posts', intval( $posts_page_id ) );
    }

    public function handle_save() {
        check_admin_referer( 'saur8_save_front_page' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Unauthorized', 'saur8-custom-template' ) );
        }

        $homepage_id = isset( $_POST['homepage_id'] ) ? intval( $_POST['homepage_id'] ) : 0;
        $posts_page_id = isset( $_POST['posts_page_id'] ) ? intval( $_POST['posts_page_id'] ) : 0;

        if ( $homepage_id ) {
            self::save_settings( $homepage_id, $posts_page_id );
            Saur8_CT_Admin_Notices::add( __( 'Front page settings saved successfully.', 'saur8-custom-template' ), 'success' );
        } else {
            Saur8_CT_Admin_Notices::add( __( 'Please select a homepage.', 'saur8-custom-template' ), 'error' );
        }

        wp_redirect( admin_url( 'admin.php?page=saur8-ct-frontpage' ) );
        exit;
    }
}

new Saur8_CT_Front_Page();
