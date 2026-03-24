<?php
/**
 * Template Loader - registers templates with WordPress
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Saur8_CT_Template_Loader {

    public function __construct() {
        add_filter( 'theme_page_templates', array( $this, 'register_templates' ) );
        add_filter( 'template_include', array( $this, 'load_template' ) );
    }

    public function register_templates( $templates ) {
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
            
            if ( preg_match( '/\*\s*Template Name:\s*(.+)/i', $content, $matches ) ) {
                $template_name = trim( $matches[1] );
                $template_key = 'saur8-templates/' . $filename;
                $templates[ $template_key ] = $template_name;
            }
        }

        return $templates;
    }

    public function load_template( $template ) {
        global $post;

        if ( ! $post ) {
            return $template;
        }

        $page_template = get_post_meta( $post->ID, '_wp_page_template', true );

        if ( empty( $page_template ) || $page_template === 'default' ) {
            return $template;
        }

        // Check if it's our custom template
        if ( strpos( $page_template, 'saur8-templates/' ) === 0 ) {
            $filename = str_replace( 'saur8-templates/', '', $page_template );
            $custom_template = SAUR8_CT_TEMPLATES_DIR . $filename;

            if ( file_exists( $custom_template ) ) {
                return $custom_template;
            }
        }

        return $template;
    }
}
