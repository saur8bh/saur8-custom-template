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
    <?php if ( empty( $templates ) ) : ?>
        <div class="saur8-empty-state">
            <div class="saur8-empty-state__icon"><?php echo Saur8_CT_Icons::file( 'saur8-icon-large' ); ?></div>
            <h2><?php esc_html_e( 'No Templates Yet', 'saur8-custom-template' ); ?></h2>
            <p><?php esc_html_e( 'Create your first custom page template to get started.', 'saur8-custom-template' ); ?></p>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=saur8-ct-new' ) ); ?>" class="saur8-btn saur8-btn--primary">
                <?php esc_html_e( 'Create Your First Template', 'saur8-custom-template' ); ?>
            </a>
        </div>
    <?php else : ?>
        <div class="saur8-table-wrapper">
            <table class="saur8-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Template Name', 'saur8-custom-template' ); ?></th>
                        <th><?php esc_html_e( 'File Name', 'saur8-custom-template' ); ?></th>
                        <th><?php esc_html_e( 'Assigned To', 'saur8-custom-template' ); ?></th>
                        <th><?php esc_html_e( 'Last Modified', 'saur8-custom-template' ); ?></th>
                        <th><?php esc_html_e( 'Actions', 'saur8-custom-template' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ( $templates as $template ) : ?>
                    <tr>
                        <td><strong><?php echo esc_html( $template['name'] ); ?></strong></td>
                        <td><code><?php echo esc_html( $template['filename'] ); ?></code></td>
                        <td>
                            <?php 
                            if ( ! empty( $template['assigned_pages'] ) ) {
                                echo esc_html( implode( ', ', $template['assigned_pages'] ) );
                            } else {
                                echo '<span class="saur8-muted">—</span>';
                            }
                            ?>
                        </td>
                        <td><?php echo esc_html( date_i18n( get_option( 'date_format' ), $template['modified'] ) ); ?></td>
                        <td class="saur8-table__actions">
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=saur8-ct-edit&file=' . urlencode( $template['filename'] ) ) ); ?>" 
                               class="saur8-btn saur8-btn--ghost saur8-btn--sm">
                                <?php esc_html_e( 'Edit', 'saur8-custom-template' ); ?>
                            </a>
                            
                            <?php if ( $front_page_id ) : ?>
                                <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=saur8_apply_to_frontpage&file=' . urlencode( $template['filename'] ) ), 'saur8_apply_to_frontpage' ) ); ?>" 
                                   class="saur8-btn saur8-btn--accent saur8-btn--sm saur8-apply-frontpage-btn"
                                   title="<?php echo esc_attr( sprintf( __( 'Apply to %s', 'saur8-custom-template' ), $front_page_title ) ); ?>">
                                    <?php echo Saur8_CT_Icons::star( 'saur8-icon-inline' ); ?> <?php esc_html_e( 'Front Page', 'saur8-custom-template' ); ?>
                                </a>
                            <?php endif; ?>
                            
                            <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=saur8_duplicate_template&file=' . urlencode( $template['filename'] ) ), 'saur8_duplicate_template' ) ); ?>" 
                               class="saur8-btn saur8-btn--ghost saur8-btn--sm saur8-duplicate-btn">
                                <?php esc_html_e( 'Duplicate', 'saur8-custom-template' ); ?>
                            </a>
                            <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=saur8_delete_template&file=' . urlencode( $template['filename'] ) ), 'saur8_delete_template' ) ); ?>" 
                               class="saur8-btn saur8-btn--danger saur8-btn--sm saur8-delete-btn">
                                <?php esc_html_e( 'Delete', 'saur8-custom-template' ); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</div>

<?php
saur8_ct_layout_footer();
