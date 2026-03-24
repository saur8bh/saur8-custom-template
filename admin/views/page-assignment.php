<?php
/**
 * Page Assignment View
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once SAUR8_CT_PATH . 'admin/views/layout.php';
saur8_ct_layout_header();

$templates = Saur8_CT_Template_Manager::get_all_templates();
$candidates = Saur8_CT_Page_Assignment::get_home_page_candidates();
$assignments = Saur8_CT_Page_Assignment::get_all_assignments();
$all_pages = get_posts( array(
    'post_type'      => 'page',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => 'title',
    'order'          => 'ASC',
) );
?>

<div class="saur8-content">
    <div class="saur8-card-grid">
        <!-- Auto Detect Section -->
        <div class="saur8-card">
            <h3><?php echo Saur8_CT_Icons::search( 'saur8-icon-inline' ); ?> <?php esc_html_e( 'Auto-Detect Home Page', 'saur8-custom-template' ); ?></h3>
            <p class="saur8-muted"><?php esc_html_e( 'We found these pages that might be your homepage:', 'saur8-custom-template' ); ?></p>
            
            <?php if ( empty( $candidates ) ) : ?>
                <p class="saur8-muted"><?php esc_html_e( 'No home page candidates found. Use manual assignment below.', 'saur8-custom-template' ); ?></p>
            <?php else : ?>
                <?php foreach ( $candidates as $candidate ) : ?>
                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="saur8-candidate-form">
                        <?php wp_nonce_field( 'saur8_assign_template' ); ?>
                        <input type="hidden" name="action" value="saur8_assign_template">
                        <input type="hidden" name="page_id" value="<?php echo esc_attr( $candidate['ID'] ); ?>">
                        
                        <div class="saur8-candidate">
                            <div class="saur8-candidate__info">
                                <strong><?php echo esc_html( $candidate['post_title'] ); ?></strong>
                                <span class="saur8-muted"><?php echo esc_html( $candidate['post_name'] ); ?></span>
                            </div>
                            <div class="saur8-candidate__actions">
                                <select name="template" class="saur8-select">
                                    <option value=""><?php esc_html_e( 'Select Template', 'saur8-custom-template' ); ?></option>
                                    <?php foreach ( $templates as $template ) : ?>
                                        <option value="saur8-templates/<?php echo esc_attr( $template['filename'] ); ?>">
                                            <?php echo esc_html( $template['name'] ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="saur8-btn saur8-btn--primary saur8-btn--sm">
                                    <?php esc_html_e( 'Assign', 'saur8-custom-template' ); ?>
                                </button>
                            </div>
                        </div>
                    </form>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Manual Assignment Section -->
        <div class="saur8-card">
            <h3><?php echo Saur8_CT_Icons::link( 'saur8-icon-inline' ); ?> <?php esc_html_e( 'Manual Assignment', 'saur8-custom-template' ); ?></h3>
            <p class="saur8-muted"><?php esc_html_e( 'Assign any template to any page:', 'saur8-custom-template' ); ?></p>
            
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'saur8_assign_template' ); ?>
                <input type="hidden" name="action" value="saur8_assign_template">
                
                <div class="saur8-form-group">
                    <label><?php esc_html_e( 'Select Page', 'saur8-custom-template' ); ?></label>
                    <select name="page_id" class="saur8-select" required>
                        <option value=""><?php esc_html_e( 'Choose a page...', 'saur8-custom-template' ); ?></option>
                        <?php foreach ( $all_pages as $page ) : ?>
                            <option value="<?php echo esc_attr( $page->ID ); ?>">
                                <?php echo esc_html( $page->post_title ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="saur8-form-group">
                    <label><?php esc_html_e( 'Select Template', 'saur8-custom-template' ); ?></label>
                    <select name="template" class="saur8-select" required>
                        <option value=""><?php esc_html_e( 'Choose a template...', 'saur8-custom-template' ); ?></option>
                        <?php foreach ( $templates as $template ) : ?>
                            <option value="saur8-templates/<?php echo esc_attr( $template['filename'] ); ?>">
                                <?php echo esc_html( $template['name'] ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="saur8-btn saur8-btn--primary saur8-btn--block">
                    <?php esc_html_e( 'Assign Template', 'saur8-custom-template' ); ?>
                </button>
            </form>
        </div>
    </div>

    <!-- Current Assignments Section -->
    <div class="saur8-card">
        <h3><?php echo Saur8_CT_Icons::clipboard( 'saur8-icon-inline' ); ?> <?php esc_html_e( 'Current Assignments', 'saur8-custom-template' ); ?></h3>
        
        <?php if ( empty( $assignments ) ) : ?>
            <p class="saur8-muted"><?php esc_html_e( 'No templates assigned yet.', 'saur8-custom-template' ); ?></p>
        <?php else : ?>
            <div class="saur8-table-wrapper">
                <table class="saur8-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Page Title', 'saur8-custom-template' ); ?></th>
                            <th><?php esc_html_e( 'Template Name', 'saur8-custom-template' ); ?></th>
                            <th><?php esc_html_e( 'Actions', 'saur8-custom-template' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ( $assignments as $assignment ) : ?>
                        <tr>
                            <td><?php echo esc_html( $assignment['page_title'] ); ?></td>
                            <td><?php echo esc_html( $assignment['template_name'] ); ?></td>
                            <td>
                                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
                                    <?php wp_nonce_field( 'saur8_remove_assignment' ); ?>
                                    <input type="hidden" name="action" value="saur8_remove_assignment">
                                    <input type="hidden" name="page_id" value="<?php echo esc_attr( $assignment['page_id'] ); ?>">
                                    <button type="submit" class="saur8-btn saur8-btn--danger saur8-btn--sm">
                                        <?php esc_html_e( 'Remove', 'saur8-custom-template' ); ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
saur8_ct_layout_footer();
