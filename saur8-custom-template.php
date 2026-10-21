<?php
/**
 * Plugin Name: saur8 custom template
 * Description: Create, edit, and assign custom PHP page templates from the admin. No FTP needed.
 * Version: 1.0.1
 * Author: Saurabh
 * Text Domain: saur8-custom-template
 * Domain Path: /languages
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'SAUR8_CT_VERSION', '1.0.1' );
define( 'SAUR8_CT_PATH', plugin_dir_path( __FILE__ ) );
define( 'SAUR8_CT_URL', plugin_dir_url( __FILE__ ) );

$upload_dir = wp_upload_dir();
define( 'SAUR8_CT_TEMPLATES_DIR', $upload_dir['basedir'] . '/saur8-templates/' );
define( 'SAUR8_CT_TEMPLATES_URL', $upload_dir['baseurl'] . '/saur8-templates/' );

// Require core plugin class
require_once SAUR8_CT_PATH . 'includes/class-plugin.php';

// Initialize plugin immediately
Saur8_CT_Plugin::get_instance();

// Activation hook
register_activation_hook( __FILE__, array( 'Saur8_CT_Plugin', 'activate' ) );
