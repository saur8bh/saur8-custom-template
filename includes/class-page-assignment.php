<?php
/**
 * Page Assignment - handles template assignment to pages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Saur8_CT_Page_Assignment {

    public function __construct() {
        add_action( 'admin_post_saur8_assign_template', array( $this, 'handle_assignment' ) );
        add_action( 'admin_post_saur8_remove_assignment', array( $this, 'handle_removal' ) );
    }

    public static function assign_template( $page_id, $template_filepath ) {
        return update_post_meta( $page_id, '_wp_page_template', $template_filepath );
    }

    public static function remove_assignment( $page_id ) {
        return delete_post_meta( $page_id, '_wp_page_template' );
    }

    public static function get_all_assignments() {
        global $wpdb;

        $assignments = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT p.ID, p.post_title, pm.meta_value as template_path
                FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                WHERE pm.meta_key = %s 
                AND pm.meta_value LIKE %s
                AND p.post_type = 'page'
                AND p.post_status = 'publish'
                ORDER BY p.post_title ASC",
                '_wp_page_template',
                '%saur8-templates%'
            )
        );

        $result = array();
        foreach ( $assignments as $assignment ) {
            $filename = str_replace( 'saur8-templates/', '', $assignment->template_path );
            $template_name = self::get_template_name_from_file( $filename );

            $result[] = array(
                'page_id'       => $assignment->ID,
                'page_title'    => $assignment->post_title,
                'template_path' => $assignment->template_path,
                'template_name' => $template_name,
            );
        }

        return $result;
    }

    public static function get_home_page_candidates() {
        $args = array(
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        );

        $all_pages = get_posts( $args );
        $candidates = array();

        foreach ( $all_pages as $page ) {
            $title_lower = strtolower( $page->post_title );
            $slug_lower = strtolower( $page->post_name );

            if ( 
                strpos( $slug_lower, 'home' ) !== false ||
                $title_lower === 'home' ||
                $title_lower === 'homepage' ||
                strpos( $title_lower, 'home page' ) !== false
            ) {
                $candidates[] = array(
                    'ID'         => $page->ID,
                    'post_title' => $page->post_title,
                    'post_name'  => $page->post_name,
                );
            }
        }

        return $candidates;
    }

    public function handle_assignment() {
        check_admin_referer( 'saur8_assign_template' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Unauthorized', 'saur8-custom-template' ) );
        }

        $page_id = isset( $_POST['page_id'] ) ? intval( $_POST['page_id'] ) : 0;
        $template = isset( $_POST['template'] ) ? sanitize_text_field( $_POST['template'] ) : '';

        if ( $page_id && $template ) {
            self::assign_template( $page_id, $template );
            Saur8_CT_Admin_Notices::add( __( 'Template assigned successfully.', 'saur8-custom-template' ), 'success' );
        } else {
            Saur8_CT_Admin_Notices::add( __( 'Invalid page or template.', 'saur8-custom-template' ), 'error' );
        }

        wp_redirect( admin_url( 'admin.php?page=saur8-ct-assign' ) );
        exit;
    }

    public function handle_removal() {
        check_admin_referer( 'saur8_remove_assignment' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Unauthorized', 'saur8-custom-template' ) );
        }

        $page_id = isset( $_POST['page_id'] ) ? intval( $_POST['page_id'] ) : 0;

        if ( $page_id ) {
            self::remove_assignment( $page_id );
            Saur8_CT_Admin_Notices::add( __( 'Template assignment removed.', 'saur8-custom-template' ), 'success' );
        }

        wp_redirect( admin_url( 'admin.php?page=saur8-ct-assign' ) );
        exit;
    }

    private static function get_template_name_from_file( $filename ) {
        $filepath = SAUR8_CT_TEMPLATES_DIR . $filename;
        
        if ( ! file_exists( $filepath ) ) {
            return $filename;
        }

        $content = file_get_contents( $filepath );
        
        if ( preg_match( '/\*\s*Template Name:\s*(.+)/i', $content, $matches ) ) {
            return trim( $matches[1] );
        }

        return $filename;
    }
}

new Saur8_CT_Page_Assignment();
