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

        if ( empty( $files ) ) {
            return $templates;
        }

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
                'modified_date'  => date_i18n( get_option( 'date_format' ), filemtime( $filepath ) ),
                'assigned_pages' => $assigned_pages,
            );
        }

        return $templates;
    }

    public static function get_template_details( $filename ) {
        $filename = sanitize_file_name( $filename );
        $filepath = SAUR8_CT_TEMPLATES_DIR . $filename;

        if ( ! file_exists( $filepath ) ) {
            return false;
        }

        $content = file_get_contents( $filepath );
        $template_name = self::parse_template_name( $content );
        $assigned_pages = self::get_assigned_pages( $filename );

        return array(
            'name'           => $template_name,
            'filename'       => $filename,
            'filepath'       => $filepath,
            'modified'       => filemtime( $filepath ),
            'modified_date'  => date_i18n( get_option( 'date_format' ), filemtime( $filepath ) ),
            'assigned_pages' => $assigned_pages,
        );
    }

    public static function get_template( $filename ) {
        $filename = sanitize_file_name( $filename );
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
        if ( $original_filename ) {
            $original_filename = sanitize_file_name( $original_filename );
            if ( $original_filename !== $new_filename ) {
                $old_filepath = SAUR8_CT_TEMPLATES_DIR . $original_filename;
                if ( file_exists( $old_filepath ) ) {
                    // Update all page assignments
                    self::rename_template_assignments( $original_filename, $new_filename );
                    // Delete old file
                    unlink( $old_filepath );
                }
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
        $filename = sanitize_file_name( $filename );
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
        $filename = sanitize_file_name( $filename );
        $content = self::get_template( $filename );

        if ( ! $content ) {
            return false;
        }

        $template_name = self::parse_template_name( $content );
        $base_name = $template_name ? $template_name . ' Copy' : 'Custom Template Copy';
        $new_name = $base_name;
        $counter = 1;

        // Ensure unique copy name
        $new_filename = self::sanitize_filename( $new_name ) . '.php';
        while ( file_exists( SAUR8_CT_TEMPLATES_DIR . $new_filename ) ) {
            $counter++;
            $new_name = $base_name . ' ' . $counter;
            $new_filename = self::sanitize_filename( $new_name ) . '.php';
        }

        // Update template name in content
        $content = preg_replace(
            '/(\*\s*Template Name:\s*)(.+)/i',
            '$1' . $new_name,
            $content
        );

        return self::save_template( $content );
    }

    public static function export_single_template( $filename ) {
        $filename = sanitize_file_name( $filename );
        $filepath = SAUR8_CT_TEMPLATES_DIR . $filename;

        $real_templates_dir = realpath( SAUR8_CT_TEMPLATES_DIR );
        $real_filepath = realpath( $filepath );

        if ( ! file_exists( $filepath ) || $real_filepath === false || strpos( $real_filepath, $real_templates_dir ) !== 0 ) {
            wp_die( esc_html__( 'Template file not found or invalid.', 'saur8-custom-template' ) );
        }

        if ( pathinfo( $filepath, PATHINFO_EXTENSION ) !== 'php' || $filename === 'index.php' ) {
            wp_die( esc_html__( 'Invalid file requested.', 'saur8-custom-template' ) );
        }

        while ( ob_get_level() ) {
            ob_end_clean();
        }

        header( 'Content-Description: File Transfer' );
        header( 'Content-Type: application/x-php' );
        header( 'Content-Disposition: attachment; filename="' . basename( $filename ) . '"' );
        header( 'Content-Transfer-Encoding: binary' );
        header( 'Expires: 0' );
        header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
        header( 'Pragma: public' );
        header( 'Content-Length: ' . filesize( $filepath ) );

        readfile( $filepath );
        exit;
    }

    public static function export_templates_zip( $filenames = array(), $zip_filename = '' ) {
        $all_templates = self::get_all_templates();
        if ( empty( $all_templates ) ) {
            wp_die( esc_html__( 'No templates found to export.', 'saur8-custom-template' ) );
        }

        $files_to_export = array();
        if ( ! empty( $filenames ) ) {
            $requested_files = array_map( 'sanitize_file_name', (array) $filenames );
            foreach ( $all_templates as $template ) {
                if ( in_array( $template['filename'], $requested_files, true ) ) {
                    $files_to_export[] = $template['filepath'];
                }
            }
        } else {
            foreach ( $all_templates as $template ) {
                $files_to_export[] = $template['filepath'];
            }
        }

        if ( empty( $files_to_export ) ) {
            wp_die( esc_html__( 'No matching template files selected.', 'saur8-custom-template' ) );
        }

        if ( empty( $zip_filename ) ) {
            $zip_filename = 'saur8-templates-' . gmdate( 'Y-m-d-His' ) . '.zip';
        } else {
            $zip_filename = sanitize_file_name( $zip_filename );
            if ( pathinfo( $zip_filename, PATHINFO_EXTENSION ) !== 'zip' ) {
                $zip_filename .= '.zip';
            }
        }

        $temp_zip = wp_tempnam( 'saur8_zip_' );

        if ( class_exists( 'ZipArchive' ) ) {
            $zip = new ZipArchive();
            if ( $zip->open( $temp_zip, ZipArchive::CREATE | ZipArchive::OVERWRITE ) !== true ) {
                wp_die( esc_html__( 'Unable to create ZIP archive.', 'saur8-custom-template' ) );
            }

            foreach ( $files_to_export as $file ) {
                if ( file_exists( $file ) && pathinfo( $file, PATHINFO_EXTENSION ) === 'php' && basename( $file ) !== 'index.php' ) {
                    $zip->addFile( $file, basename( $file ) );
                }
            }

            $zip->close();
        } else {
            // Fallback using PclZip (bundled with WordPress core)
            require_once ABSPATH . 'wp-admin/includes/class-pclzip.php';
            $archive = new PclZip( $temp_zip );
            $result = $archive->create( $files_to_export, PCLZIP_OPT_REMOVE_ALL_PATH );
            if ( $result === 0 ) {
                wp_die( esc_html__( 'PclZip failed to create archive: ', 'saur8-custom-template' ) . esc_html( $archive->errorInfo( true ) ) );
            }
        }

        while ( ob_get_level() ) {
            ob_end_clean();
        }

        header( 'Content-Description: File Transfer' );
        header( 'Content-Type: application/zip' );
        header( 'Content-Disposition: attachment; filename="' . esc_attr( $zip_filename ) . '"' );
        header( 'Content-Transfer-Encoding: binary' );
        header( 'Expires: 0' );
        header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
        header( 'Pragma: public' );
        header( 'Content-Length: ' . filesize( $temp_zip ) );

        readfile( $temp_zip );
        @unlink( $temp_zip );
        exit;
    }

    public static function import_template_file( $tmp_path, $original_filename, $overwrite = false ) {
        if ( ! file_exists( $tmp_path ) ) {
            return array(
                'success' => false,
                'message' => __( 'Uploaded file was not found on server.', 'saur8-custom-template' ),
            );
        }

        $ext = strtolower( pathinfo( $original_filename, PATHINFO_EXTENSION ) );

        if ( $ext === 'php' ) {
            return self::import_single_php( $tmp_path, $original_filename, $overwrite );
        } elseif ( $ext === 'zip' ) {
            return self::import_zip_archive( $tmp_path, $overwrite );
        }

        return array(
            'success' => false,
            'message' => __( 'Unsupported file format. Please upload a .php or .zip file.', 'saur8-custom-template' ),
        );
    }

    private static function import_single_php( $file_path, $original_filename, $overwrite = false ) {
        $content = file_get_contents( $file_path );
        $template_name = self::parse_template_name( $content );

        if ( empty( $template_name ) ) {
            return array(
                'success' => false,
                'message' => sprintf(
                    __( 'File "%s" does not contain a valid "Template Name:" comment header.', 'saur8-custom-template' ),
                    esc_html( $original_filename )
                ),
            );
        }

        $base_filename = self::sanitize_filename( $template_name ) . '.php';
        $target_filename = $base_filename;

        if ( ! $overwrite && file_exists( SAUR8_CT_TEMPLATES_DIR . $target_filename ) ) {
            $counter = 1;
            $new_name = $template_name . ' (Imported)';
            $target_filename = self::sanitize_filename( $new_name ) . '.php';
            while ( file_exists( SAUR8_CT_TEMPLATES_DIR . $target_filename ) ) {
                $counter++;
                $new_name = $template_name . ' (Imported ' . $counter . ')';
                $target_filename = self::sanitize_filename( $new_name ) . '.php';
            }

            // Update template name in content
            $content = preg_replace(
                '/(\*\s*Template Name:\s*)(.+)/i',
                '$1' . $new_name,
                $content
            );
            $template_name = $new_name;
        }

        $destination = SAUR8_CT_TEMPLATES_DIR . $target_filename;
        $saved = file_put_contents( $destination, $content );

        if ( $saved === false ) {
            return array(
                'success' => false,
                'message' => sprintf( __( 'Could not write template to "%s".', 'saur8-custom-template' ), $target_filename ),
            );
        }

        return array(
            'success'            => true,
            'count'              => 1,
            'imported_templates' => array(
                array(
                    'name'     => $template_name,
                    'filename' => $target_filename,
                ),
            ),
            'message'            => sprintf(
                __( 'Successfully imported template "%s" (%s).', 'saur8-custom-template' ),
                $template_name,
                $target_filename
            ),
        );
    }

    private static function import_zip_archive( $zip_path, $overwrite = false ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        WP_Filesystem();

        $temp_dir = trailingslashit( get_temp_dir() ) . 'saur8_unzip_' . wp_generate_password( 8, false );
        if ( ! wp_mkdir_p( $temp_dir ) ) {
            return array(
                'success' => false,
                'message' => __( 'Could not create temporary directory for unzipping.', 'saur8-custom-template' ),
            );
        }

        $unzipped = unzip_file( $zip_path, $temp_dir );
        if ( is_wp_error( $unzipped ) ) {
            return array(
                'success' => false,
                'message' => sprintf( __( 'Failed to unzip file: %s', 'saur8-custom-template' ), $unzipped->get_error_message() ),
            );
        }

        // Find all PHP files recursively
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator( $temp_dir, RecursiveDirectoryIterator::SKIP_DOTS )
        );

        $imported = array();
        $errors   = array();

        foreach ( $files as $file ) {
            if ( $file->isFile() && strtolower( $file->getExtension() ) === 'php' ) {
                $filename = $file->getFilename();
                if ( $filename === 'index.php' ) {
                    continue;
                }

                $result = self::import_single_php( $file->getPathname(), $filename, $overwrite );
                if ( $result['success'] ) {
                    foreach ( $result['imported_templates'] as $t ) {
                        $imported[] = $t;
                    }
                } else {
                    $errors[] = $result['message'];
                }
            }
        }

        // Clean up temp directory
        global $wp_filesystem;
        if ( $wp_filesystem ) {
            $wp_filesystem->delete( $temp_dir, true );
        }

        if ( empty( $imported ) && ! empty( $errors ) ) {
            return array(
                'success' => false,
                'message' => implode( ' ', $errors ),
            );
        }

        if ( empty( $imported ) ) {
            return array(
                'success' => false,
                'message' => __( 'No valid PHP templates found inside the ZIP file.', 'saur8-custom-template' ),
            );
        }

        $names = wp_list_pluck( $imported, 'name' );

        return array(
            'success'            => true,
            'count'              => count( $imported ),
            'imported_templates' => $imported,
            'message'            => sprintf(
                __( 'Successfully imported %1$d template(s): %2$s', 'saur8-custom-template' ),
                count( $imported ),
                implode( ', ', $names )
            ),
            'errors'             => $errors,
        );
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
        if ( ! empty( $pages ) ) {
            foreach ( $pages as $page ) {
                $titles[] = $page->post_title;
            }
        }

        return $titles;
    }

    public static function parse_template_name( $content ) {
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
