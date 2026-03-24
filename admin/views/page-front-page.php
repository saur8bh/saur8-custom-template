<?php
/**
 * Front Page Settings View
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once SAUR8_CT_PATH . 'admin/views/layout.php';
saur8_ct_layout_header();

$settings = Saur8_CT_Front_Page::get_current_settings();
$all_pages = get_posts( array(
    'post_type'      => 'page',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => 'title',
    'order'          => 'ASC',
) );
?>

<div class="saur8-content">
    <div class="saur8-card">
        <h3><?php esc_html_e( 'Front Page Settings', 'saur8-custom-template' ); ?></h3>
        
        <div class="saur8-info-box">
            <p><?php esc_html_e( 'This sets your static front page — same as WordPress Settings → Reading. Select which page appears as your homepage and which shows your blog posts.', 'saur8-custom-template' ); ?></p>
        </div>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <?php wp_nonce_field( 'saur8_save_front_page' ); ?>
            <input type="hidden" name="action" value="saur8_save_front_page">
            
            <div class="saur8-form-group">
                <label for="homepage_id">
                    <?php esc_html_e( 'Homepage', 'saur8-custom-template' ); ?>
                    <?php if ( $settings['page_on_front'] ) : ?>
                        <span class="saur8-badge saur8-badge--success">
                            <?php esc_html_e( 'Active', 'saur8-custom-template' ); ?>
                        </span>
                    <?php endif; ?>
                </label>
                <select name="homepage_id" id="homepage_id" class="saur8-select" required>
                    <option value=""><?php esc_html_e( 'Select homepage...', 'saur8-custom-template' ); ?></option>
                    <?php foreach ( $all_pages as $page ) : ?>
                        <option value="<?php echo esc_attr( $page->ID ); ?>" 
                                <?php selected( $settings['page_on_front'], $page->ID ); ?>>
                            <?php echo esc_html( $page->post_title ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="saur8-form-group">
                <label for="posts_page_id">
                    <?php esc_html_e( 'Posts Page (Blog)', 'saur8-custom-template' ); ?>
                    <?php if ( $settings['page_for_posts'] ) : ?>
                        <span class="saur8-badge saur8-badge--success">
                            <?php esc_html_e( 'Active', 'saur8-custom-template' ); ?>
                        </span>
                    <?php endif; ?>
                </label>
                <select name="posts_page_id" id="posts_page_id" class="saur8-select">
                    <option value=""><?php esc_html_e( 'Select posts page (optional)...', 'saur8-custom-template' ); ?></option>
                    <?php foreach ( $all_pages as $page ) : ?>
                        <option value="<?php echo esc_attr( $page->ID ); ?>" 
                                <?php selected( $settings['page_for_posts'], $page->ID ); ?>>
                            <?php echo esc_html( $page->post_title ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="saur8-btn saur8-btn--primary saur8-btn--block">
                <?php esc_html_e( 'Save Settings', 'saur8-custom-template' ); ?>
            </button>
        </form>

        <?php if ( $settings['show_on_front'] === 'page' && $settings['page_on_front'] ) : ?>
            <div class="saur8-current-settings">
                <h4><?php esc_html_e( 'Current Configuration', 'saur8-custom-template' ); ?></h4>
                <p>
                    <strong><?php esc_html_e( 'Homepage:', 'saur8-custom-template' ); ?></strong>
                    <?php echo esc_html( get_the_title( $settings['page_on_front'] ) ); ?>
                </p>
                <?php if ( $settings['page_for_posts'] ) : ?>
                    <p>
                        <strong><?php esc_html_e( 'Posts Page:', 'saur8-custom-template' ); ?></strong>
                        <?php echo esc_html( get_the_title( $settings['page_for_posts'] ) ); ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
saur8_ct_layout_footer();
