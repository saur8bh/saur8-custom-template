<?php
/**
 * Import / Export View
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once SAUR8_CT_PATH . 'admin/views/layout.php';
saur8_ct_layout_header();

$templates = Saur8_CT_Template_Manager::get_all_templates();
$total_count = count( $templates );
?>

<div class="saur8-content">
    <div id="saur8-import-export-notices"></div>

    <div class="saur8-card-grid">
        <!-- Export Card -->
        <div class="saur8-card">
            <h3>
                <?php echo Saur8_CT_Icons::download( 'saur8-icon-inline' ); ?> 
                <?php esc_html_e( 'Export Templates', 'saur8-custom-template' ); ?>
                <span class="saur8-badge"><?php echo esc_html( sprintf( _n( '%d template', '%d templates', $total_count, 'saur8-custom-template' ), $total_count ) ); ?></span>
            </h3>
            
            <p class="saur8-muted">
                <?php esc_html_e( 'Export your custom templates as a ZIP archive or individually download .php template files to easily back them up or transfer them between WordPress sites.', 'saur8-custom-template' ); ?>
            </p>

            <?php if ( empty( $templates ) ) : ?>
                <div class="saur8-info-box">
                    <p><?php esc_html_e( 'No templates found in your templates directory yet. Create a template first to export.', 'saur8-custom-template' ); ?></p>
                </div>
            <?php else : ?>
                <div class="saur8-export-all-box">
                    <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=saur8_export_all_templates' ), 'saur8_export_all_templates' ) ); ?>" 
                       class="saur8-btn saur8-btn--primary saur8-btn--block">
                        <?php echo Saur8_CT_Icons::archive( 'saur8-icon-inline' ); ?> 
                        <?php esc_html_e( 'Export All Templates as ZIP', 'saur8-custom-template' ); ?>
                    </a>
                </div>

                <div class="saur8-divider-title">
                    <span><?php esc_html_e( 'or select specific templates to export', 'saur8-custom-template' ); ?></span>
                </div>

                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="saur8-export-selected-form">
                    <?php wp_nonce_field( 'saur8_export_selected_templates' ); ?>
                    <input type="hidden" name="action" value="saur8_export_selected_templates">

                    <div class="saur8-checklist-header">
                        <label class="saur8-checkbox-label">
                            <input type="checkbox" id="saur8-export-select-all" class="saur8-checkbox">
                            <span class="saur8-checkbox-text"><strong><?php esc_html_e( 'Select All', 'saur8-custom-template' ); ?></strong></span>
                        </label>
                        <span id="saur8-export-selected-count" class="saur8-muted">0 selected</span>
                    </div>

                    <div class="saur8-checklist-container">
                        <?php foreach ( $templates as $template ) : ?>
                            <div class="saur8-checklist-item">
                                <label class="saur8-checkbox-label">
                                    <input type="checkbox" 
                                           name="template_files[]" 
                                           value="<?php echo esc_attr( $template['filename'] ); ?>" 
                                           class="saur8-checkbox saur8-export-checkbox">
                                    <span class="saur8-checkbox-text">
                                        <strong><?php echo esc_html( $template['name'] ); ?></strong>
                                        <small><code><?php echo esc_html( $template['filename'] ); ?></code></small>
                                    </span>
                                </label>
                                <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=saur8_export_template&file=' . urlencode( $template['filename'] ) ), 'saur8_export_template' ) ); ?>" 
                                   class="saur8-btn saur8-btn--ghost saur8-btn--xs"
                                   title="<?php esc_attr_e( 'Download single .php file', 'saur8-custom-template' ); ?>">
                                    <?php echo Saur8_CT_Icons::download( 'saur8-icon-inline' ); ?> .php
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <button type="submit" id="saur8-export-selected-btn" class="saur8-btn saur8-btn--ghost saur8-btn--block" disabled style="margin-top: 16px;">
                        <?php echo Saur8_CT_Icons::archive( 'saur8-icon-inline' ); ?> 
                        <?php esc_html_e( 'Export Selected as ZIP', 'saur8-custom-template' ); ?>
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <!-- Import Card -->
        <div class="saur8-card">
            <h3>
                <?php echo Saur8_CT_Icons::upload( 'saur8-icon-inline' ); ?> 
                <?php esc_html_e( 'Import Templates', 'saur8-custom-template' ); ?>
            </h3>
            
            <p class="saur8-muted">
                <?php esc_html_e( 'Upload single .php template files or a .zip package containing multiple templates. They will be immediately installed into your templates folder.', 'saur8-custom-template' ); ?>
            </p>

            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" id="saur8-import-form">
                <?php wp_nonce_field( 'saur8_import_templates' ); ?>
                <input type="hidden" name="action" value="saur8_import_templates">

                <!-- Drag & Drop Zone -->
                <div class="saur8-dropzone" id="saur8-dropzone">
                    <input type="file" 
                           id="saur8-import-file-input" 
                           name="template_file" 
                           accept=".php,.zip" 
                           class="saur8-dropzone__input" 
                           required>
                    
                    <div class="saur8-dropzone__content">
                        <div class="saur8-dropzone__icon">
                            <?php echo Saur8_CT_Icons::upload( 'saur8-icon-large' ); ?>
                        </div>
                        <p class="saur8-dropzone__text">
                            <strong><?php esc_html_e( 'Choose a file', 'saur8-custom-template' ); ?></strong> 
                            <?php esc_html_e( 'or drag it here', 'saur8-custom-template' ); ?>
                        </p>
                        <span class="saur8-dropzone__hint"><?php esc_html_e( 'Supports .php and .zip archives', 'saur8-custom-template' ); ?></span>
                    </div>

                    <div class="saur8-dropzone__file-info" id="saur8-file-info" style="display: none;">
                        <span class="saur8-dropzone__filename" id="saur8-filename"></span>
                        <span class="saur8-dropzone__filesize saur8-muted" id="saur8-filesize"></span>
                        <button type="button" class="saur8-dropzone__remove-file" id="saur8-remove-file" title="<?php esc_attr_e( 'Remove file', 'saur8-custom-template' ); ?>">✕</button>
                    </div>
                </div>

                <div class="saur8-form-group saur8-form-group--checkbox" style="margin-top: 20px;">
                    <label class="saur8-checkbox-label">
                        <input type="checkbox" name="overwrite" value="1" id="saur8-overwrite" class="saur8-checkbox">
                        <span class="saur8-checkbox-text">
                            <strong><?php esc_html_e( 'Overwrite existing templates', 'saur8-custom-template' ); ?></strong>
                            <small><?php esc_html_e( 'If unchecked, files with duplicate names will be safely imported as new unique copies.', 'saur8-custom-template' ); ?></small>
                        </span>
                    </label>
                </div>

                <button type="submit" id="saur8-import-submit-btn" class="saur8-btn saur8-btn--primary saur8-btn--block" disabled>
                    <?php echo Saur8_CT_Icons::upload( 'saur8-icon-inline' ); ?> 
                    <span class="saur8-btn__text"><?php esc_html_e( 'Import Template(s)', 'saur8-custom-template' ); ?></span>
                </button>
            </form>

            <div class="saur8-info-box" style="margin-top: 24px;">
                <p>
                    <?php echo Saur8_CT_Icons::bulb( 'saur8-icon-inline' ); ?>
                    <strong><?php esc_html_e( 'Template Header Requirement:', 'saur8-custom-template' ); ?></strong>
                </p>
                <p class="saur8-muted" style="margin-top: 6px; font-size: 13px;">
                    <?php esc_html_e( 'Imported PHP files must contain a standard WordPress template header at the beginning:', 'saur8-custom-template' ); ?>
                </p>
                <pre class="saur8-code-snippet"><code>&lt;?php
/**
 * Template Name: Your Template Title
 */</code></pre>
            </div>
        </div>
    </div>
</div>

<?php
saur8_ct_layout_footer();

