<?php
/**
 * All Templates View
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once SAUR8_CT_PATH . 'admin/views/layout.php';
saur8_ct_layout_header();

$templates = Saur8_CT_Template_Manager::get_all_templates();

// Get current front page
$front_page_settings = Saur8_CT_Front_Page::get_current_settings();
$front_page_id = $front_page_settings['page_on_front'];
$front_page_title = $front_page_id ? get_the_title( $front_page_id ) : '';
?>

<div class="saur8-content">
    <!-- Dynamic notification area for AJAX actions -->
    <div id="saur8-ajax-notices"></div>

    <div class="saur8-empty-state" id="saur8-empty-state" style="<?php echo empty( $templates ) ? '' : 'display: none;'; ?>">
        <div class="saur8-empty-state__icon"><?php echo Saur8_CT_Icons::file( 'saur8-icon-large' ); ?></div>
        <h2><?php esc_html_e( 'No Templates Yet', 'saur8-custom-template' ); ?></h2>
        <p><?php esc_html_e( 'Create your first custom page template to get started, or import an existing template.', 'saur8-custom-template' ); ?></p>
        <div class="saur8-empty-state__actions">
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=saur8-ct-new' ) ); ?>" class="saur8-btn saur8-btn--primary">
                <?php esc_html_e( 'Create Your First Template', 'saur8-custom-template' ); ?>
            </a>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=saur8-ct-import-export' ) ); ?>" class="saur8-btn saur8-btn--ghost">
                <?php echo Saur8_CT_Icons::upload( 'saur8-icon-inline' ); ?> <?php esc_html_e( 'Import Template', 'saur8-custom-template' ); ?>
            </a>
        </div>
    </div>

    <div class="saur8-table-container" id="saur8-table-container" style="<?php echo empty( $templates ) ? 'display: none;' : ''; ?>">
        <!-- Bulk Action & Toolbar Bar -->
        <div class="saur8-toolbar">
            <div class="saur8-toolbar__left">
                <div class="saur8-bulk-controls">
                    <select id="saur8-bulk-action" class="saur8-select saur8-select--inline">
                        <option value=""><?php esc_html_e( 'Bulk Actions', 'saur8-custom-template' ); ?></option>
                        <option value="export"><?php esc_html_e( 'Export Selected (ZIP)', 'saur8-custom-template' ); ?></option>
                        <option value="delete"><?php esc_html_e( 'Delete Selected', 'saur8-custom-template' ); ?></option>
                    </select>
                    <button type="button" id="saur8-bulk-apply" class="saur8-btn saur8-btn--ghost saur8-btn--sm" disabled>
                        <?php esc_html_e( 'Apply', 'saur8-custom-template' ); ?>
                    </button>
                    <span id="saur8-bulk-count" class="saur8-bulk-count">0 selected</span>
                </div>
            </div>
            <div class="saur8-toolbar__right">
                <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=saur8_export_all_templates' ), 'saur8_export_all_templates' ) ); ?>" 
                   class="saur8-btn saur8-btn--ghost saur8-btn--sm"
                   title="<?php esc_attr_e( 'Download all templates in a single ZIP file', 'saur8-custom-template' ); ?>">
                    <?php echo Saur8_CT_Icons::archive( 'saur8-icon-inline' ); ?> <?php esc_html_e( 'Export All (ZIP)', 'saur8-custom-template' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=saur8-ct-import-export' ) ); ?>" 
                   class="saur8-btn saur8-btn--ghost saur8-btn--sm">
                    <?php echo Saur8_CT_Icons::upload( 'saur8-icon-inline' ); ?> <?php esc_html_e( 'Import', 'saur8-custom-template' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=saur8-ct-new' ) ); ?>" 
                   class="saur8-btn saur8-btn--primary saur8-btn--sm">
                    + <?php esc_html_e( 'New Template', 'saur8-custom-template' ); ?>
                </a>
            </div>
        </div>

        <div class="saur8-table-wrapper">
            <table class="saur8-table" id="saur8-templates-table">
                <thead>
                    <tr>
                        <th class="saur8-table__checkbox">
                            <input type="checkbox" id="saur8-select-all" class="saur8-checkbox" aria-label="<?php esc_attr_e( 'Select All', 'saur8-custom-template' ); ?>">
                        </th>
                        <th><?php esc_html_e( 'Template Name', 'saur8-custom-template' ); ?></th>
                        <th><?php esc_html_e( 'File Name', 'saur8-custom-template' ); ?></th>
                        <th><?php esc_html_e( 'Assigned To', 'saur8-custom-template' ); ?></th>
                        <th><?php esc_html_e( 'Last Modified', 'saur8-custom-template' ); ?></th>
                        <th class="saur8-table__actions-head"><?php esc_html_e( 'Actions', 'saur8-custom-template' ); ?></th>
                    </tr>
                </thead>
                <tbody id="saur8-templates-tbody">
                <?php foreach ( $templates as $template ) : ?>
                    <tr data-filename="<?php echo esc_attr( $template['filename'] ); ?>" data-name="<?php echo esc_attr( $template['name'] ); ?>">
                        <td class="saur8-table__checkbox">
                            <input type="checkbox" class="saur8-checkbox saur8-template-checkbox" value="<?php echo esc_attr( $template['filename'] ); ?>" aria-label="<?php echo esc_attr( $template['name'] ); ?>">
                        </td>
                        <td class="saur8-td-name">
                            <strong><?php echo esc_html( $template['name'] ); ?></strong>
                        </td>
                        <td class="saur8-td-file">
                            <code><?php echo esc_html( $template['filename'] ); ?></code>
                        </td>
                        <td class="saur8-td-assigned">
                            <?php 
                            if ( ! empty( $template['assigned_pages'] ) ) {
                                echo esc_html( implode( ', ', $template['assigned_pages'] ) );
                            } else {
                                echo '<span class="saur8-muted">—</span>';
                            }
                            ?>
                        </td>
                        <td class="saur8-td-modified">
                            <?php echo esc_html( $template['modified_date'] ); ?>
                        </td>
                        <td class="saur8-table__actions">
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=saur8-ct-edit&file=' . urlencode( $template['filename'] ) ) ); ?>" 
                               class="saur8-btn saur8-btn--ghost saur8-btn--sm">
                                <?php esc_html_e( 'Edit', 'saur8-custom-template' ); ?>
                            </a>
                            
                            <?php if ( $front_page_id ) : ?>
                                <button type="button"
                                        class="saur8-btn saur8-btn--accent saur8-btn--sm saur8-apply-frontpage-btn"
                                        data-filename="<?php echo esc_attr( $template['filename'] ); ?>"
                                        data-page-title="<?php echo esc_attr( $front_page_title ); ?>"
                                        title="<?php echo esc_attr( sprintf( __( 'Apply to %s', 'saur8-custom-template' ), $front_page_title ) ); ?>">
                                    <?php echo Saur8_CT_Icons::star( 'saur8-icon-inline' ); ?> <span><?php esc_html_e( 'Front Page', 'saur8-custom-template' ); ?></span>
                                </button>
                            <?php endif; ?>
                            
                            <button type="button"
                                    class="saur8-btn saur8-btn--ghost saur8-btn--sm saur8-duplicate-btn"
                                    data-filename="<?php echo esc_attr( $template['filename'] ); ?>"
                                    data-name="<?php echo esc_attr( $template['name'] ); ?>"
                                    title="<?php esc_attr_e( 'Duplicate this template', 'saur8-custom-template' ); ?>">
                                <?php echo Saur8_CT_Icons::copy( 'saur8-icon-inline' ); ?> <span><?php esc_html_e( 'Duplicate', 'saur8-custom-template' ); ?></span>
                            </button>

                            <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=saur8_export_template&file=' . urlencode( $template['filename'] ) ), 'saur8_export_template' ) ); ?>" 
                               class="saur8-btn saur8-btn--ghost saur8-btn--sm saur8-export-btn"
                               title="<?php esc_attr_e( 'Download template .php file', 'saur8-custom-template' ); ?>">
                                <?php echo Saur8_CT_Icons::download( 'saur8-icon-inline' ); ?> <span><?php esc_html_e( 'Export', 'saur8-custom-template' ); ?></span>
                            </a>

                            <button type="button"
                                    class="saur8-btn saur8-btn--danger saur8-btn--sm saur8-delete-btn"
                                    data-filename="<?php echo esc_attr( $template['filename'] ); ?>"
                                    data-name="<?php echo esc_attr( $template['name'] ); ?>"
                                    title="<?php esc_attr_e( 'Delete this template permanently', 'saur8-custom-template' ); ?>">
                                <?php echo Saur8_CT_Icons::trash( 'saur8-icon-inline' ); ?> <span><?php esc_html_e( 'Delete', 'saur8-custom-template' ); ?></span>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Hidden Export Form for Bulk Actions -->
<form id="saur8-bulk-export-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:none;">
    <?php wp_nonce_field( 'saur8_export_selected_templates' ); ?>
    <input type="hidden" name="action" value="saur8_export_selected_templates">
    <div id="saur8-bulk-export-inputs"></div>
</form>

<?php
saur8_ct_layout_footer();
