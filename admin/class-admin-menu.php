<?php
/**
 * Admin Menu - registers all admin pages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Saur8_CT_Admin_Menu {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'register_menu' ) );
        add_action( 'admin_post_saur8_save_template', array( $this, 'handle_save_template' ) );
        add_action( 'admin_post_saur8_delete_template', array( $this, 'handle_delete_template' ) );
        add_action( 'admin_post_saur8_duplicate_template', array( $this, 'handle_duplicate_template' ) );
        add_action( 'admin_post_saur8_apply_to_frontpage', array( $this, 'handle_apply_to_frontpage' ) );
        add_action( 'admin_post_saur8_save_settings', array( $this, 'handle_save_settings' ) );
    }

    public function register_menu() {
        add_menu_page(
            __( 'Template Studio', 'saur8-custom-template' ),
            __( 'Template Studio', 'saur8-custom-template' ),
            'manage_options',
            'saur8-ct',
            array( $this, 'render_all_templates' ),
            'dashicons-editor-code',
            30
        );

        add_submenu_page(
            'saur8-ct',
            __( 'All Templates', 'saur8-custom-template' ),
            __( 'All Templates', 'saur8-custom-template' ),
            'manage_options',
            'saur8-ct',
            array( $this, 'render_all_templates' )
        );

        add_submenu_page(
            'saur8-ct',
            __( 'Create New', 'saur8-custom-template' ),
            __( 'Create New', 'saur8-custom-template' ),
            'manage_options',
            'saur8-ct-new',
            array( $this, 'render_create_template' )
        );

        add_submenu_page(
            'saur8-ct',
            __( 'Page Assignments', 'saur8-custom-template' ),
            __( 'Page Assignments', 'saur8-custom-template' ),
            'manage_options',
            'saur8-ct-assign',
            array( $this, 'render_assignment' )
        );

        add_submenu_page(
            'saur8-ct',
            __( 'Front Page Settings', 'saur8-custom-template' ),
            __( 'Front Page Settings', 'saur8-custom-template' ),
            'manage_options',
            'saur8-ct-frontpage',
            array( $this, 'render_front_page' )
        );

        add_submenu_page(
            'saur8-ct',
            __( 'Settings', 'saur8-custom-template' ),
            __( 'Settings', 'saur8-custom-template' ),
            'manage_options',
            'saur8-ct-settings',
            array( $this, 'render_settings' )
        );

        // Hidden edit page
        add_submenu_page(
            null,
            __( 'Edit Template', 'saur8-custom-template' ),
            __( 'Edit Template', 'saur8-custom-template' ),
            'manage_options',
            'saur8-ct-edit',
            array( $this, 'render_edit_template' )
        );
    }

    public function render_all_templates() {
        require_once SAUR8_CT_PATH . 'admin/views/page-all-templates.php';
    }

    public function render_create_template() {
        require_once SAUR8_CT_PATH . 'admin/views/page-create-template.php';
    }

    public function render_edit_template() {
        require_once SAUR8_CT_PATH . 'admin/views/page-edit-template.php';
    }

    public function render_assignment() {
        require_once SAUR8_CT_PATH . 'admin/views/page-assignment.php';
    }

    public function render_front_page() {
        require_once SAUR8_CT_PATH . 'admin/views/page-front-page.php';
    }

    public function render_settings() {
        require_once SAUR8_CT_PATH . 'admin/views/page-settings.php';
    }

    public function handle_save_template() {
        check_admin_referer( 'saur8_save_template' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Unauthorized', 'saur8-custom-template' ) );
        }

        $content = isset( $_POST['template_content'] ) ? wp_unslash( $_POST['template_content'] ) : '';
        $original_filename = isset( $_POST['original_filename'] ) ? wp_unslash( $_POST['original_filename'] ) : null;
        $assign_to_page = isset( $_POST['assign_to_page'] ) ? intval( $_POST['assign_to_page'] ) : 0;

        $result = Saur8_CT_Template_Manager::save_template( $content, $original_filename );

        if ( $result['success'] ) {
            // Handle page assignment if requested
            if ( $assign_to_page > 0 ) {
                $template_path = 'saur8-templates/' . $result['filename'];
                Saur8_CT_Page_Assignment::assign_template( $assign_to_page, $template_path );
                
                $page_title = get_the_title( $assign_to_page );
                Saur8_CT_Admin_Notices::add( 
                    sprintf( 
                        __( 'Template saved and assigned to "%s" successfully.', 'saur8-custom-template' ),
                        $page_title
                    ), 
                    'success' 
                );
            } else {
                Saur8_CT_Admin_Notices::add( $result['message'], 'success' );
            }
            
            // Redirect back to editor if editing, otherwise to list
            if ( $original_filename ) {
                wp_redirect( admin_url( 'admin.php?page=saur8-ct-edit&file=' . urlencode( $result['filename'] ) ) );
            } else {
                wp_redirect( admin_url( 'admin.php?page=saur8-ct' ) );
            }
        } else {
            Saur8_CT_Admin_Notices::add( $result['message'], 'error' );
            
            // Redirect back to appropriate page
            if ( $original_filename ) {
                wp_redirect( admin_url( 'admin.php?page=saur8-ct-edit&file=' . urlencode( $original_filename ) ) );
            } else {
                wp_redirect( admin_url( 'admin.php?page=saur8-ct-new' ) );
            }
        }
        exit;
    }

    public function handle_delete_template() {
        check_admin_referer( 'saur8_delete_template' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Unauthorized', 'saur8-custom-template' ) );
        }

        $filename = isset( $_GET['file'] ) ? wp_unslash( $_GET['file'] ) : '';

        if ( Saur8_CT_Template_Manager::delete_template( $filename ) ) {
            Saur8_CT_Admin_Notices::add( __( 'Template deleted successfully.', 'saur8-custom-template' ), 'success' );
        } else {
            Saur8_CT_Admin_Notices::add( __( 'Failed to delete template.', 'saur8-custom-template' ), 'error' );
        }

        wp_redirect( admin_url( 'admin.php?page=saur8-ct' ) );
        exit;
    }

    public function handle_duplicate_template() {
        check_admin_referer( 'saur8_duplicate_template' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Unauthorized', 'saur8-custom-template' ) );
        }

        $filename = isset( $_GET['file'] ) ? wp_unslash( $_GET['file'] ) : '';

        $result = Saur8_CT_Template_Manager::duplicate_template( $filename );

        if ( $result && $result['success'] ) {
            Saur8_CT_Admin_Notices::add( __( 'Template duplicated successfully.', 'saur8-custom-template' ), 'success' );
        } else {
            Saur8_CT_Admin_Notices::add( __( 'Failed to duplicate template.', 'saur8-custom-template' ), 'error' );
        }

        wp_redirect( admin_url( 'admin.php?page=saur8-ct' ) );
        exit;
    }

    public function handle_apply_to_frontpage() {
        check_admin_referer( 'saur8_apply_to_frontpage' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Unauthorized', 'saur8-custom-template' ) );
        }

        $filename = isset( $_GET['file'] ) ? wp_unslash( $_GET['file'] ) : '';
        $front_page_settings = Saur8_CT_Front_Page::get_current_settings();
        $front_page_id = $front_page_settings['page_on_front'];

        if ( ! $front_page_id ) {
            Saur8_CT_Admin_Notices::add( __( 'No front page is set. Please set a front page first.', 'saur8-custom-template' ), 'error' );
        } elseif ( $filename ) {
            $template_path = 'saur8-templates/' . $filename;
            Saur8_CT_Page_Assignment::assign_template( $front_page_id, $template_path );
            
            $page_title = get_the_title( $front_page_id );
            Saur8_CT_Admin_Notices::add( 
                sprintf( 
                    __( 'Template applied to front page "%s" successfully.', 'saur8-custom-template' ),
                    $page_title
                ), 
                'success' 
            );
        } else {
            Saur8_CT_Admin_Notices::add( __( 'Invalid template.', 'saur8-custom-template' ), 'error' );
        }

        wp_redirect( admin_url( 'admin.php?page=saur8-ct' ) );
        exit;
    }

    public function handle_save_settings() {
        check_admin_referer( 'saur8_save_settings' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Unauthorized', 'saur8-custom-template' ) );
        }

        $dev_mode = isset( $_POST['dev_mode'] ) ? 1 : 0;
        $cache_busting = isset( $_POST['cache_busting'] ) ? sanitize_text_field( $_POST['cache_busting'] ) : 'version';

        update_option( 'saur8_ct_dev_mode', $dev_mode );
        update_option( 'saur8_ct_cache_busting', $cache_busting );

        Saur8_CT_Admin_Notices::add( __( 'Settings saved successfully.', 'saur8-custom-template' ), 'success' );

        wp_redirect( admin_url( 'admin.php?page=saur8-ct-settings' ) );
        exit;
    }
}
