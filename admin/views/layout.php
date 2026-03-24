<?php
/**
 * Layout wrapper for admin pages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$current_page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : 'saur8-ct';

function saur8_ct_layout_header() {
    global $current_page;
    ?>
    <div class="saur8-wrap">
        <div class="saur8-header">
            <div class="saur8-logo">
                <svg class="saur8-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <defs>
                        <linearGradient id="saur8-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#7c5cbf;stop-opacity:1" />
                            <stop offset="100%" style="stop-color:#a78bfa;stop-opacity:1" />
                        </linearGradient>
                    </defs>
                    <path fill="url(#saur8-gradient)" d="M7 2v11h3v9l7-12h-4l4-8z"/>
                </svg>
                <span><?php echo esc_html__( 'Template Studio', 'saur8-custom-template' ); ?></span>
            </div>
            <span class="saur8-version">v<?php echo esc_html( SAUR8_CT_VERSION ); ?></span>
        </div>
        <nav class="saur8-tabs">
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=saur8-ct' ) ); ?>" 
               class="saur8-tab <?php echo $current_page === 'saur8-ct' ? 'saur8-tab--active' : ''; ?>">
                <?php esc_html_e( 'All Templates', 'saur8-custom-template' ); ?>
            </a>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=saur8-ct-new' ) ); ?>" 
               class="saur8-tab <?php echo $current_page === 'saur8-ct-new' ? 'saur8-tab--active' : ''; ?>">
                <?php esc_html_e( 'Create New', 'saur8-custom-template' ); ?>
            </a>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=saur8-ct-assign' ) ); ?>" 
               class="saur8-tab <?php echo $current_page === 'saur8-ct-assign' ? 'saur8-tab--active' : ''; ?>">
                <?php esc_html_e( 'Page Assignments', 'saur8-custom-template' ); ?>
            </a>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=saur8-ct-frontpage' ) ); ?>" 
               class="saur8-tab <?php echo $current_page === 'saur8-ct-frontpage' ? 'saur8-tab--active' : ''; ?>">
                <?php esc_html_e( 'Front Page Settings', 'saur8-custom-template' ); ?>
            </a>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=saur8-ct-settings' ) ); ?>" 
               class="saur8-tab <?php echo $current_page === 'saur8-ct-settings' ? 'saur8-tab--active' : ''; ?>">
                <?php esc_html_e( 'Settings', 'saur8-custom-template' ); ?>
            </a>
        </nav>
    <?php
}

function saur8_ct_layout_footer() {
    ?>
    </div>
    <?php
}
