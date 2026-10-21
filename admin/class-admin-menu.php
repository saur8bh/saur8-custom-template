<?php
/**
 * Admin Menu - registers all admin pages and handles requests
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Saur8_CT_Admin_Menu {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'register_menu' ) );

        // Standard POST/GET actions
        add_action( 'admin_post_saur8_save_template', array( $this, 'handle_save_template' ) );
        add_action( 'admin_post_saur8_delete_template', array( $this, 'handle_delete_template' ) );
        add_action( 'admin_post_saur8_duplicate_template', array( $this, 'handle_duplicate_template' ) );
        add_action( 'admin_post_saur8_apply_to_frontpage', array( $this, 'handle_apply_to_frontpage' ) );
        add_action( 'admin_post_saur8_save_settings', array( $this, 'handle_save_settings' ) );
        add_action( 'admin_post_saur8_export_template', array( $this, 'handle_export_template' ) );
        add_action( 'admin_post_saur8_export_all_templates', array( $this, 'handle_export_all_templates' ) );
        add_action( 'admin_post_saur8_export_selected_templates', array( $this, 'handle_export_selected_templates' ) );
        add_action( 'admin_post_saur8_import_templates', array( $this, 'handle_import_templates' ) );

        // AJAX handlers
        add_action( 'wp_ajax_saur8_ajax_delete_template', array( $this, 'handle_ajax_delete_template' ) );
        add_action( 'wp_ajax_saur8_ajax_duplicate_template', array( $this, 'handle_ajax_duplicate_template' ) );
        add_action( 'wp_ajax_saur8_ajax_apply_frontpage', array( $this, 'handle_ajax_apply_frontpage' ) );
        add_action( 'wp_ajax_saur8_ajax_bulk_delete', array( $this, 'handle_ajax_bulk_delete' ) );
        add_action( 'wp_ajax_saur8_ajax_import_templates', array( $this, 'handle_ajax_import_templates' ) );
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
            __( 'Import / Export', 'saur8-custom-template' ),
            __( 'Import / Export', 'saur8-custom-template' ),
            'manage_options',
            'saur8-ct-import-export',
            array( $this, 'render_import_export' )
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

    public function render_import_export() {
        require_once SAUR8_CT_PATH . 'admin/views/page-import-export.php';
    }

    public function render_settings() {
        require_once SAUR8_CT_PATH . 'admin/views/page-settings.php';
    }

    /* ------------------------------------------------------------------------
     * Standard Non-AJAX Handlers
     * ------------------------------------------------------------------------ */

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
            
            if ( $original_filename ) {
                wp_redirect( admin_url( 'admin.php?page=saur8-ct-edit&file=' . urlencode( $result['filename'] ) ) );
            } else {
                wp_redirect( admin_url( 'admin.php?page=saur8-ct' ) );
            }
        } else {
            Saur8_CT_Admin_Notices::add( $result['message'], 'error' );
            
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

    public function handle_export_template() {
        check_admin_referer( 'saur8_export_template' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Unauthorized', 'saur8-custom-template' ) );
        }

        $filename = isset( $_GET['file'] ) ? sanitize_file_name( wp_unslash( $_GET['file'] ) ) : '';
        if ( empty( $filename ) ) {
            wp_die( esc_html__( 'No template specified for export.', 'saur8-custom-template' ) );
        }

        Saur8_CT_Template_Manager::export_single_template( $filename );
    }

    public function handle_export_all_templates() {
        check_admin_referer( 'saur8_export_all_templates' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Unauthorized', 'saur8-custom-template' ) );
        }

        Saur8_CT_Template_Manager::export_templates_zip();
    }

    public function handle_export_selected_templates() {
        check_admin_referer( 'saur8_export_selected_templates' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Unauthorized', 'saur8-custom-template' ) );
        }

        $files = array();
        if ( isset( $_POST['template_files'] ) && is_array( $_POST['template_files'] ) ) {
            $files = array_map( 'sanitize_file_name', wp_unslash( $_POST['template_files'] ) );
        } elseif ( isset( $_GET['files'] ) ) {
            $files = array_filter( array_map( 'sanitize_file_name', explode( ',', wp_unslash( $_GET['files'] ) ) ) );
        }

        if ( empty( $files ) ) {
            Saur8_CT_Admin_Notices::add( __( 'No templates selected for export.', 'saur8-custom-template' ), 'error' );
            wp_redirect( admin_url( 'admin.php?page=saur8-ct' ) );
            exit;
        }

        Saur8_CT_Template_Manager::export_templates_zip( $files );
    }

    public function handle_import_templates() {
        check_admin_referer( 'saur8_import_templates' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Unauthorized', 'saur8-custom-template' ) );
        }

        if ( empty( $_FILES['template_file'] ) || empty( $_FILES['template_file']['tmp_name'] ) ) {
            Saur8_CT_Admin_Notices::add( __( 'Please select a file to import.', 'saur8-custom-template' ), 'error' );
            wp_redirect( admin_url( 'admin.php?page=saur8-ct-import-export' ) );
            exit;
        }

        $overwrite = ! empty( $_POST['overwrite'] );
        $result = Saur8_CT_Template_Manager::import_template_file(
            $_FILES['template_file']['tmp_name'],
            $_FILES['template_file']['name'],
            $overwrite
        );

        if ( $result['success'] ) {
            Saur8_CT_Admin_Notices::add( $result['message'], 'success' );
            wp_redirect( admin_url( 'admin.php?page=saur8-ct' ) );
        } else {
            Saur8_CT_Admin_Notices::add( $result['message'], 'error' );
            wp_redirect( admin_url( 'admin.php?page=saur8-ct-import-export' ) );
        }
        exit;
    }

    /* ------------------------------------------------------------------------
     * AJAX Handlers for Seamless Operations (without page reload)
     * ------------------------------------------------------------------------ */

    public function handle_ajax_delete_template() {
        check_ajax_referer( 'saur8_ajax_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Unauthorized user.', 'saur8-custom-template' ) ) );
        }

        $filename = isset( $_POST['file'] ) ? sanitize_file_name( wp_unslash( $_POST['file'] ) ) : '';

        if ( empty( $filename ) ) {
            wp_send_json_error( array( 'message' => __( 'No file specified.', 'saur8-custom-template' ) ) );
        }

        if ( Saur8_CT_Template_Manager::delete_template( $filename ) ) {
            $remaining = count( Saur8_CT_Template_Manager::get_all_templates() );
            wp_send_json_success( array(
                'message'   => __( 'Template deleted successfully.', 'saur8-custom-template' ),
                'filename'  => $filename,
                'remaining' => $remaining,
            ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Failed to delete template.', 'saur8-custom-template' ) ) );
        }
    }

    public function handle_ajax_duplicate_template() {
        check_ajax_referer( 'saur8_ajax_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Unauthorized user.', 'saur8-custom-template' ) ) );
        }

        $filename = isset( $_POST['file'] ) ? sanitize_file_name( wp_unslash( $_POST['file'] ) ) : '';

        if ( empty( $filename ) ) {
            wp_send_json_error( array( 'message' => __( 'No file specified.', 'saur8-custom-template' ) ) );
        }

        $result = Saur8_CT_Template_Manager::duplicate_template( $filename );

        if ( $result && ! empty( $result['success'] ) ) {
            $new_filename = $result['filename'];
            $details = Saur8_CT_Template_Manager::get_template_details( $new_filename );

            // Prepare action URLs
            $front_page_settings = Saur8_CT_Front_Page::get_current_settings();
            $front_page_id = $front_page_settings['page_on_front'];
            $front_page_title = $front_page_id ? get_the_title( $front_page_id ) : '';

            $template_data = array(
                'name'              => $details['name'],
                'filename'          => $details['filename'],
                'assigned_pages'    => $details['assigned_pages'],
                'modified'          => $details['modified_date'],
                'edit_url'          => admin_url( 'admin.php?page=saur8-ct-edit&file=' . urlencode( $new_filename ) ),
                'duplicate_url'     => wp_nonce_url( admin_url( 'admin-post.php?action=saur8_duplicate_template&file=' . urlencode( $new_filename ) ), 'saur8_duplicate_template' ),
                'delete_url'        => wp_nonce_url( admin_url( 'admin-post.php?action=saur8_delete_template&file=' . urlencode( $new_filename ) ), 'saur8_delete_template' ),
                'export_url'        => wp_nonce_url( admin_url( 'admin-post.php?action=saur8_export_template&file=' . urlencode( $new_filename ) ), 'saur8_export_template' ),
                'front_page_url'    => $front_page_id ? wp_nonce_url( admin_url( 'admin-post.php?action=saur8_apply_to_frontpage&file=' . urlencode( $new_filename ) ), 'saur8_apply_to_frontpage' ) : '',
                'front_page_title'  => $front_page_title,
                'has_front_page'    => ! empty( $front_page_id ),
            );

            wp_send_json_success( array(
                'message'  => __( 'Template duplicated successfully.', 'saur8-custom-template' ),
                'template' => $template_data,
            ) );
        } else {
            wp_send_json_error( array(
                'message' => ! empty( $result['message'] ) ? $result['message'] : __( 'Failed to duplicate template.', 'saur8-custom-template' ),
            ) );
        }
    }

    public function handle_ajax_apply_frontpage() {
        check_ajax_referer( 'saur8_ajax_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Unauthorized user.', 'saur8-custom-template' ) ) );
        }

        $filename = isset( $_POST['file'] ) ? sanitize_file_name( wp_unslash( $_POST['file'] ) ) : '';

        if ( empty( $filename ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid template requested.', 'saur8-custom-template' ) ) );
        }

        $front_page_settings = Saur8_CT_Front_Page::get_current_settings();
        $front_page_id = $front_page_settings['page_on_front'];

        if ( ! $front_page_id ) {
            wp_send_json_error( array( 'message' => __( 'No front page is configured. Please select a homepage first under Front Page Settings.', 'saur8-custom-template' ) ) );
        }

        $template_path = 'saur8-templates/' . $filename;
        Saur8_CT_Page_Assignment::assign_template( $front_page_id, $template_path );
        $page_title = get_the_title( $front_page_id );

        wp_send_json_success( array(
            'message'          => sprintf( __( 'Template applied to front page "%s" successfully.', 'saur8-custom-template' ), $page_title ),
            'filename'         => $filename,
            'front_page_title' => $page_title,
            'front_page_id'    => $front_page_id,
        ) );
    }

    public function handle_ajax_bulk_delete() {
        check_ajax_referer( 'saur8_ajax_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Unauthorized user.', 'saur8-custom-template' ) ) );
        }

        $files = isset( $_POST['files'] ) && is_array( $_POST['files'] ) ? array_map( 'sanitize_file_name', wp_unslash( $_POST['files'] ) ) : array();

        if ( empty( $files ) ) {
            wp_send_json_error( array( 'message' => __( 'No templates selected.', 'saur8-custom-template' ) ) );
        }

        $deleted = array();
        $failed  = array();

        foreach ( $files as $file ) {
            if ( Saur8_CT_Template_Manager::delete_template( $file ) ) {
                $deleted[] = $file;
            } else {
                $failed[] = $file;
            }
        }

        $remaining = count( Saur8_CT_Template_Manager::get_all_templates() );

        if ( empty( $deleted ) ) {
            wp_send_json_error( array( 'message' => __( 'Failed to delete selected templates.', 'saur8-custom-template' ) ) );
        }

        wp_send_json_success( array(
            'message'   => sprintf( __( 'Successfully deleted %d template(s).', 'saur8-custom-template' ), count( $deleted ) ),
            'deleted'   => $deleted,
            'failed'    => $failed,
            'remaining' => $remaining,
        ) );
    }

    public function handle_ajax_import_templates() {
        check_ajax_referer( 'saur8_ajax_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Unauthorized user.', 'saur8-custom-template' ) ) );
        }

        if ( empty( $_FILES['template_file'] ) || empty( $_FILES['template_file']['tmp_name'] ) ) {
            wp_send_json_error( array( 'message' => __( 'No file was uploaded.', 'saur8-custom-template' ) ) );
        }

        $overwrite = ! empty( $_POST['overwrite'] ) && $_POST['overwrite'] === '1';

        $result = Saur8_CT_Template_Manager::import_template_file(
            $_FILES['template_file']['tmp_name'],
            $_FILES['template_file']['name'],
            $overwrite
        );

        if ( $result['success'] ) {
            wp_send_json_success( $result );
        } else {
            wp_send_json_error( $result );
        }
    }
}
