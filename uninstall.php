<?php
/**
 * Uninstall script - runs when plugin is deleted
 */

// Exit if not called by WordPress
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Get templates directory
$upload_dir = wp_upload_dir();
$templates_dir = $upload_dir['basedir'] . '/saur8-templates/';

// Delete all template files
if ( is_dir( $templates_dir ) ) {
    $files = glob( $templates_dir . '*' );
    foreach ( $files as $file ) {
        if ( is_file( $file ) ) {
            unlink( $file );
        }
    }
    // Remove directory
    rmdir( $templates_dir );
}

// Clean up post meta from all pages
global $wpdb;
$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value LIKE %s",
        '_wp_page_template',
        '%saur8-templates%'
    )
);

// Remove plugin options if any exist
delete_option( 'saur8_ct_version' );
