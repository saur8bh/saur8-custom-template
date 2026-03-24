<?php
/**
 * Edit Template View
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once SAUR8_CT_PATH . 'admin/views/layout.php';
saur8_ct_layout_header();

$filename = isset( $_GET['file'] ) ? wp_unslash( $_GET['file'] ) : '';
$content = Saur8_CT_Template_Manager::get_template( $filename );

if ( ! $content ) {
    echo '<div class="saur8-content"><div class="saur8-notice saur8-notice--error">';
    esc_html_e( 'Template not found.', 'saur8-custom-template' );
    echo '</div></div>';
    saur8_ct_layout_footer();
    return;
}

// Parse template name
preg_match( '/\*\s*Template Name:\s*(.+)/i', $content, $matches );
$template_name = isset( $matches[1] ) ? trim( $matches[1] ) : '';

// Get all pages for assignment dropdown
$all_pages = get_posts( array(
    'post_type'      => 'page',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => 'title',
    'order'          => 'ASC',
) );

// Get current front page
$front_page_settings = Saur8_CT_Front_Page::get_current_settings();

// Get pages currently using this template
$template_path = 'saur8-templates/' . $filename;
$assigned_pages = get_posts( array(
    'post_type'      => 'page',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'meta_key'       => '_wp_page_template',
    'meta_value'     => $template_path,
) );
$assigned_page_ids = wp_list_pluck( $assigned_pages, 'ID' );
?>

<div class="saur8-content">
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="saur8-editor-layout">
        <?php wp_nonce_field( 'saur8_save_template' ); ?>
        <input type="hidden" name="action" value="saur8_save_template">
        <input type="hidden" name="original_filename" value="<?php echo esc_attr( $filename ); ?>">
        
        <div class="saur8-editor-main">
            <textarea id="saur8-editor" name="template_content"><?php echo esc_textarea( $content ); ?></textarea>
        </div>

        <div class="saur8-editor-sidebar">
            <div class="saur8-card">
                <h3><?php esc_html_e( 'Template Settings', 'saur8-custom-template' ); ?></h3>
                
                <div class="saur8-form-group">
                    <label for="saur8-template-name"><?php esc_html_e( 'Template Name', 'saur8-custom-template' ); ?></label>
                    <input type="text" 
                           id="saur8-template-name" 
                           class="saur8-input" 
                           value="<?php echo esc_attr( $template_name ); ?>" 
                           placeholder="<?php esc_attr_e( 'Enter template name', 'saur8-custom-template' ); ?>">
                </div>

                <div class="saur8-form-group">
                    <label for="assign_to_page"><?php esc_html_e( 'Assign After Saving', 'saur8-custom-template' ); ?></label>
                    <select name="assign_to_page" id="assign_to_page" class="saur8-select">
                        <option value=""><?php esc_html_e( 'Don\'t change assignments', 'saur8-custom-template' ); ?></option>
                        <?php if ( $front_page_settings['page_on_front'] ) : ?>
                            <option value="<?php echo esc_attr( $front_page_settings['page_on_front'] ); ?>"
                                    <?php echo in_array( $front_page_settings['page_on_front'], $assigned_page_ids ) ? 'selected' : ''; ?>>
                                <?php echo esc_html( get_the_title( $front_page_settings['page_on_front'] ) ); ?> (Front Page)
                            </option>
                        <?php endif; ?>
                        <option value="" disabled>──────────</option>
                        <?php foreach ( $all_pages as $page ) : ?>
                            <?php if ( $page->ID != $front_page_settings['page_on_front'] ) : ?>
                                <option value="<?php echo esc_attr( $page->ID ); ?>"
                                        <?php echo in_array( $page->ID, $assigned_page_ids ) ? 'selected' : ''; ?>>
                                    <?php echo esc_html( $page->post_title ); ?>
                                    <?php echo in_array( $page->ID, $assigned_page_ids ) ? ' ✓' : ''; ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <small class="saur8-muted"><?php esc_html_e( 'Assign this template to a page after saving', 'saur8-custom-template' ); ?></small>
                </div>

                <button type="submit" class="saur8-btn saur8-btn--primary saur8-btn--block">
                    <?php esc_html_e( 'Update Template', 'saur8-custom-template' ); ?>
                </button>
            </div>

            <div class="saur8-card saur8-card--danger">
                <h4><?php echo Saur8_CT_Icons::warning( 'saur8-icon-inline' ); ?> <?php esc_html_e( 'Danger Zone', 'saur8-custom-template' ); ?></h4>
                <p class="saur8-muted"><?php esc_html_e( 'This action cannot be undone.', 'saur8-custom-template' ); ?></p>
                <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=saur8_delete_template&file=' . urlencode( $filename ) ), 'saur8_delete_template' ) ); ?>" 
                   class="saur8-btn saur8-btn--danger saur8-btn--block saur8-delete-btn">
                    <?php esc_html_e( 'Delete This Template', 'saur8-custom-template' ); ?>
                </a>
            </div>
        </div>
    </form>
</div>

<?php
saur8_ct_layout_footer();
