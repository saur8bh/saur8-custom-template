<?php
/**
 * Create Template View
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once SAUR8_CT_PATH . 'admin/views/layout.php';
saur8_ct_layout_header();

$default_content = '<?php
/**
 * Template Name: My Custom Template
 */

get_header();
?>

<main id="main-content">
  <!-- Your custom HTML/PHP here -->
</main>

<?php
get_footer();
?>';

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
?>

<div class="saur8-content">
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="saur8-editor-layout">
        <?php wp_nonce_field( 'saur8_save_template' ); ?>
        <input type="hidden" name="action" value="saur8_save_template">
        
        <div class="saur8-editor-main">
            <textarea id="saur8-editor" name="template_content"><?php echo esc_textarea( $default_content ); ?></textarea>
        </div>

        <div class="saur8-editor-sidebar">
            <div class="saur8-card">
                <h3><?php esc_html_e( 'Template Settings', 'saur8-custom-template' ); ?></h3>
                
                <div class="saur8-form-group">
                    <label for="saur8-template-name"><?php esc_html_e( 'Template Name', 'saur8-custom-template' ); ?></label>
                    <input type="text" 
                           id="saur8-template-name" 
                           class="saur8-input" 
                           value="My Custom Template" 
                           placeholder="<?php esc_attr_e( 'Enter template name', 'saur8-custom-template' ); ?>">
                </div>

                <div class="saur8-form-group">
                    <label for="assign_to_page"><?php esc_html_e( 'Assign After Saving', 'saur8-custom-template' ); ?></label>
                    <select name="assign_to_page" id="assign_to_page" class="saur8-select">
                        <option value=""><?php esc_html_e( 'Don\'t assign yet', 'saur8-custom-template' ); ?></option>
                        <?php if ( $front_page_settings['page_on_front'] ) : ?>
                            <option value="<?php echo esc_attr( $front_page_settings['page_on_front'] ); ?>">
                                <?php echo esc_html( get_the_title( $front_page_settings['page_on_front'] ) ); ?> (Front Page)
                            </option>
                        <?php endif; ?>
                        <option value="" disabled>──────────</option>
                        <?php foreach ( $all_pages as $page ) : ?>
                            <?php if ( $page->ID != $front_page_settings['page_on_front'] ) : ?>
                                <option value="<?php echo esc_attr( $page->ID ); ?>">
                                    <?php echo esc_html( $page->post_title ); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <small class="saur8-muted"><?php esc_html_e( 'Automatically assign this template to a page after saving', 'saur8-custom-template' ); ?></small>
                </div>

                <button type="submit" class="saur8-btn saur8-btn--primary saur8-btn--block">
                    <?php esc_html_e( 'Save Template', 'saur8-custom-template' ); ?>
                </button>
            </div>

            <div class="saur8-card">
                <h4><?php echo Saur8_CT_Icons::bulb( 'saur8-icon-inline' ); ?> <?php esc_html_e( 'Tips', 'saur8-custom-template' ); ?></h4>
                <ul class="saur8-tips-list">
                    <li><?php esc_html_e( 'Template Name is required in the file header', 'saur8-custom-template' ); ?></li>
                    <li><?php esc_html_e( 'Use get_header() and get_footer() for theme consistency', 'saur8-custom-template' ); ?></li>
                    <li><?php esc_html_e( 'Access post data with global $post', 'saur8-custom-template' ); ?></li>
                </ul>
            </div>
        </div>
    </form>
</div>

<?php
saur8_ct_layout_footer();
