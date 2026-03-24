<?php
/**
 * Template Manager - handles template file operations
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Saur8_CT_Template_Manager {

    public static function get_all_templates() {
        $templates = array();

        if ( ! is_dir( SAUR8_CT_TEMPLATES_DIR ) ) {
            return $templates;
        }

        $files = glob( SAUR8_CT_TEMPLATES_DIR . '*.php' );

        foreach ( $files as $filepath ) {
            $filename = basename( $filepath );
            
            // Skip index.php
            if ( $filename === 'index.php' ) {
                continue;
            }

            $content = file_get_contents( $filepath );
            $template_name = self::parse_template_name( $content );

            $assigned_pages = self::get_assigned_pages( $filename );

            $templates[] = array(
                'name'           => $template_name,
                'filename'       => $filename,
                'filepath'       => $filepath,
                'modified'       => filemtime( $filepath ),
                'assigned_pages' => $assigned_pages,
            );
        }

        return $templates;
    }

    public static function get_template( $filename ) {
        // Don't sanitize again - filename is already sanitized
        $filepath = SAUR8_CT_TEMPLATES_DIR . $filename;

        // Security check: ensure file is within templates directory
        $real_templates_dir = realpath( SAUR8_CT_TEMPLATES_DIR );
        $real_filepath = realpath( $filepath );
        
        if ( ! file_exists( $filepath ) ) {
            return false;
        }
        
        if ( $real_filepath === false || strpos( $real_filepath, $real_templates_dir ) !== 0 ) {
            return false;
        }

        return file_get_contents( $filepath );
    }

    public static function save_template( $content, $original_filename = null ) {
        // Parse template name from content
        $template_name = self::parse_template_name( $content );

        if ( empty( $template_name ) ) {
            return array(
                'success' => false,
                'message' => __( 'Template Name is required in the file header.', 'saur8-custom-template' ),
            );
        }

        // Generate filename
        $new_filename = self::sanitize_filename( $template_name ) . '.php';

        // If editing existing template with different name, handle rename
        if ( $original_filename && $original_filename !== $new_filename ) {
            $old_filepath = SAUR8_CT_TEMPLATES_DIR . $original_filename;
            if ( file_exists( $old_filepath ) ) {
                // Update all page assignments
                self::rename_template_assignments( $original_filename, $new_filename );
                // Delete old file
                unlink( $old_filepath );
            }
        }

        $filepath = SAUR8_CT_TEMPLATES_DIR . $new_filename;

        // Write file
        $result = file_put_contents( $filepath, $content );

        if ( $result === false ) {
            return array(
                'success' => false,
                'message' => __( 'Failed to write template file.', 'saur8-custom-template' ),
            );
        }

        return array(
            'success'  => true,
            'filename' => $new_filename,
            'message'  => __( 'Template saved successfully.', 'saur8-custom-template' ),
        );
    }

    public static function delete_template( $filename ) {
        // Don't sanitize again - filename is already sanitized
        $filepath = SAUR8_CT_TEMPLATES_DIR . $filename;

        // Security check
        $real_templates_dir = realpath( SAUR8_CT_TEMPLATES_DIR );
        $real_filepath = realpath( $filepath );
        
        if ( ! file_exists( $filepath ) ) {
            return false;
        }
        
        if ( $real_filepath === false || strpos( $real_filepath, $real_templates_dir ) !== 0 ) {
            return false;
        }

        // Remove from all page assignments
        global $wpdb;
        $template_path = 'saur8-templates/' . $filename;
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s",
                '_wp_page_template',
                $template_path
            )
        );

        return unlink( $filepath );
    }

    public static function duplicate_template( $filename ) {
        // Don't sanitize again - filename is already sanitized
        $content = self::get_template( $filename );

        if ( ! $content ) {
            return false;
        }

        // Parse original name and add -copy
        $template_name = self::parse_template_name( $content );
        $new_name = $template_name . ' Copy';

        // Update template name in content
        $content = preg_replace(
            '/(\*\s*Template Name:\s*)(.+)/i',
            '$1' . $new_name,
            $content
        );

        return self::save_template( $content );
    }

    public static function sanitize_filename( $name ) {
        $name = strtolower( $name );
        $name = str_replace( ' ', '-', $name );
        $name = preg_replace( '/[^a-z0-9\-]/', '', $name );
        $name = trim( $name, '-' );
        return $name;
    }

    public static function get_assigned_pages( $filename ) {
        global $wpdb;
        $template_path = 'saur8-templates/' . $filename;

        $pages = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT p.ID, p.post_title 
                FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                WHERE pm.meta_key = %s AND pm.meta_value = %s
                AND p.post_type = 'page'",
                '_wp_page_template',
                $template_path
            )
        );

        $titles = array();
        foreach ( $pages as $page ) {
            $titles[] = $page->post_title;
        }

        return $titles;
    }

    private static function parse_template_name( $content ) {
        if ( preg_match( '/\*\s*Template Name:\s*(.+)/i', $content, $matches ) ) {
            return trim( $matches[1] );
        }
        return '';
    }

    private static function rename_template_assignments( $old_filename, $new_filename ) {
        global $wpdb;
        $old_path = 'saur8-templates/' . $old_filename;
        $new_path = 'saur8-templates/' . $new_filename;

        $wpdb->update(
            $wpdb->postmeta,
            array( 'meta_value' => $new_path ),
            array(
                'meta_key'   => '_wp_page_template',
                'meta_value' => $old_path,
            )
        );
    }
}
