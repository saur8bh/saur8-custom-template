<?php
/**
 * Settings View
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once SAUR8_CT_PATH . 'admin/views/layout.php';
saur8_ct_layout_header();

// Get current settings
$dev_mode = get_option( 'saur8_ct_dev_mode', 0 );
$cache_busting = get_option( 'saur8_ct_cache_busting', 'version' );
?>

<div class="saur8-content">
    <div class="saur8-card">
        <h3><?php echo Saur8_CT_Icons::get( 'settings', 'saur8-icon-inline' ); ?> <?php esc_html_e( 'Plugin Settings', 'saur8-custom-template' ); ?></h3>
        
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <?php wp_nonce_field( 'saur8_save_settings' ); ?>
            <input type="hidden" name="action" value="saur8_save_settings">
            
            <div class="saur8-settings-section">
                <h4><?php esc_html_e( 'Development Settings', 'saur8-custom-template' ); ?></h4>
                
                <div class="saur8-form-group saur8-form-group--checkbox">
                    <label class="saur8-checkbox-label">
                        <input type="checkbox" 
                               name="dev_mode" 
                               value="1" 
                               <?php checked( $dev_mode, 1 ); ?>
                               class="saur8-checkbox">
                        <span class="saur8-checkbox-text">
                            <strong><?php esc_html_e( 'Enable Development Mode', 'saur8-custom-template' ); ?></strong>
                            <small><?php esc_html_e( 'Uses file modification time for cache busting. Disable in production.', 'saur8-custom-template' ); ?></small>
                        </span>
                    </label>
                </div>

                <div class="saur8-info-box saur8-info-box--warning">
                    <p>
                        <?php echo Saur8_CT_Icons::bulb( 'saur8-icon-inline' ); ?>
                        <strong><?php esc_html_e( 'What is Development Mode?', 'saur8-custom-template' ); ?></strong>
                    </p>
                    <ul class="saur8-info-list">
                        <li><?php esc_html_e( 'When enabled: CSS/JS files use file modification timestamp as version', 'saur8-custom-template' ); ?></li>
                        <li><?php esc_html_e( 'Browser automatically loads latest version when you save files', 'saur8-custom-template' ); ?></li>
                        <li><?php esc_html_e( 'Perfect for development - no need to clear cache', 'saur8-custom-template' ); ?></li>
                        <li><?php esc_html_e( 'When disabled: Uses plugin version number (recommended for production)', 'saur8-custom-template' ); ?></li>
                    </ul>
                </div>
            </div>

            <div class="saur8-settings-section">
                <h4><?php esc_html_e( 'Cache Busting Strategy', 'saur8-custom-template' ); ?></h4>
                
                <div class="saur8-form-group">
                    <label class="saur8-radio-label">
                        <input type="radio" 
                               name="cache_busting" 
                               value="version" 
                               <?php checked( $cache_busting, 'version' ); ?>
                               class="saur8-radio">
                        <span class="saur8-radio-text">
                            <strong><?php esc_html_e( 'Plugin Version', 'saur8-custom-template' ); ?></strong>
                            <small><?php esc_html_e( 'Stable version number (recommended for production)', 'saur8-custom-template' ); ?></small>
                        </span>
                    </label>
                </div>

                <div class="saur8-form-group">
                    <label class="saur8-radio-label">
                        <input type="radio" 
                               name="cache_busting" 
                               value="filemtime" 
                               <?php checked( $cache_busting, 'filemtime' ); ?>
                               class="saur8-radio">
                        <span class="saur8-radio-text">
                            <strong><?php esc_html_e( 'File Modification Time', 'saur8-custom-template' ); ?></strong>
                            <small><?php esc_html_e( 'Automatic cache busting on file changes (for development)', 'saur8-custom-template' ); ?></small>
                        </span>
                    </label>
                </div>

                <div class="saur8-form-group">
                    <label class="saur8-radio-label">
                        <input type="radio" 
                               name="cache_busting" 
                               value="timestamp" 
                               <?php checked( $cache_busting, 'timestamp' ); ?>
                               class="saur8-radio">
                        <span class="saur8-radio-text">
                            <strong><?php esc_html_e( 'Current Timestamp', 'saur8-custom-template' ); ?></strong>
                            <small><?php esc_html_e( 'Always loads fresh version (not recommended - impacts performance)', 'saur8-custom-template' ); ?></small>
                        </span>
                    </label>
                </div>
            </div>

            <div class="saur8-settings-section">
                <h4><?php esc_html_e( 'Current Status', 'saur8-custom-template' ); ?></h4>
                
                <div class="saur8-status-grid">
                    <div class="saur8-status-item">
                        <span class="saur8-status-label"><?php esc_html_e( 'Plugin Version', 'saur8-custom-template' ); ?></span>
                        <span class="saur8-badge saur8-badge--info"><?php echo esc_html( SAUR8_CT_VERSION ); ?></span>
                    </div>
                    
                    <div class="saur8-status-item">
                        <span class="saur8-status-label"><?php esc_html_e( 'Development Mode', 'saur8-custom-template' ); ?></span>
                        <?php if ( $dev_mode ) : ?>
                            <span class="saur8-badge saur8-badge--warning"><?php esc_html_e( 'Enabled', 'saur8-custom-template' ); ?></span>
                        <?php else : ?>
                            <span class="saur8-badge saur8-badge--success"><?php esc_html_e( 'Disabled', 'saur8-custom-template' ); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="saur8-status-item">
                        <span class="saur8-status-label"><?php esc_html_e( 'Templates Directory', 'saur8-custom-template' ); ?></span>
                        <code class="saur8-code"><?php echo esc_html( SAUR8_CT_TEMPLATES_DIR ); ?></code>
                    </div>
                </div>
            </div>

            <button type="submit" class="saur8-btn saur8-btn--primary saur8-btn--block">
                <?php esc_html_e( 'Save Settings', 'saur8-custom-template' ); ?>
            </button>
        </form>
    </div>
</div>

<?php
saur8_ct_layout_footer();
