<?php
/**
 * Core Plugin Class
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Saur8_CT_Plugin {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    private function load_dependencies() {
        require_once SAUR8_CT_PATH . 'includes/class-template-manager.php';
        require_once SAUR8_CT_PATH . 'includes/class-template-loader.php';
        require_once SAUR8_CT_PATH . 'includes/class-page-assignment.php';
        require_once SAUR8_CT_PATH . 'includes/class-front-page.php';
        require_once SAUR8_CT_PATH . 'admin/class-admin-menu.php';
        require_once SAUR8_CT_PATH . 'admin/class-admin-notices.php';
        require_once SAUR8_CT_PATH . 'admin/includes/icons.php';
    }

    private function init_hooks() {
        add_action( 'init', array( $this, 'load_textdomain' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        
        // Initialize admin menu
        new Saur8_CT_Admin_Menu();
        
        // Initialize template loader for frontend
        new Saur8_CT_Template_Loader();
    }

    public function load_textdomain() {
        load_plugin_textdomain(
            'saur8-custom-template',
            false,
            dirname( plugin_basename( SAUR8_CT_PATH ) ) . '/languages/'
        );
    }

    public function enqueue_admin_assets( $hook ) {
        // Only load on our plugin pages
        if ( strpos( $hook, 'saur8' ) === false ) {
            return;
        }

        // Get version for cache busting
        $css_version = $this->get_asset_version( 'assets/css/admin.css' );
        $js_version = $this->get_asset_version( 'assets/js/admin.js' );

        // CodeMirror CSS
        wp_enqueue_style(
            'codemirror',
            'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css',
            array(),
            '5.65.16'
        );
        wp_enqueue_style(
            'codemirror-dracula',
            'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/dracula.min.css',
            array( 'codemirror' ),
            '5.65.16'
        );

        // Plugin admin CSS
        wp_enqueue_style(
            'saur8-ct-admin',
            SAUR8_CT_URL . 'assets/css/admin.css',
            array(),
            $css_version
        );

        // CodeMirror JS
        wp_enqueue_script(
            'codemirror',
            'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js',
            array(),
            '5.65.16',
            true
        );
        wp_enqueue_script(
            'codemirror-xml',
            'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/xml/xml.min.js',
            array( 'codemirror' ),
            '5.65.16',
            true
        );
        wp_enqueue_script(
            'codemirror-javascript',
            'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/javascript/javascript.min.js',
            array( 'codemirror' ),
            '5.65.16',
            true
        );
        wp_enqueue_script(
            'codemirror-css',
            'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/css/css.min.js',
            array( 'codemirror' ),
            '5.65.16',
            true
        );
        wp_enqueue_script(
            'codemirror-htmlmixed',
            'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/htmlmixed/htmlmixed.min.js',
            array( 'codemirror', 'codemirror-xml', 'codemirror-javascript', 'codemirror-css' ),
            '5.65.16',
            true
        );
        wp_enqueue_script(
            'codemirror-clike',
            'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/clike/clike.min.js',
            array( 'codemirror' ),
            '5.65.16',
            true
        );
        wp_enqueue_script(
            'codemirror-php',
            'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/php/php.min.js',
            array( 'codemirror', 'codemirror-htmlmixed', 'codemirror-clike' ),
            '5.65.16',
            true
        );
        wp_enqueue_script(
            'codemirror-matchbrackets',
            'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/matchbrackets.min.js',
            array( 'codemirror' ),
            '5.65.16',
            true
        );
        wp_enqueue_script(
            'codemirror-closebrackets',
            'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/closebrackets.min.js',
            array( 'codemirror' ),
            '5.65.16',
            true
        );
        wp_enqueue_script(
            'codemirror-foldcode',
            'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/fold/foldcode.min.js',
            array( 'codemirror' ),
            '5.65.16',
            true
        );
        wp_enqueue_script(
            'codemirror-foldgutter',
            'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/fold/foldgutter.min.js',
            array( 'codemirror', 'codemirror-foldcode' ),
            '5.65.16',
            true
        );

        // Plugin admin JS
        wp_enqueue_script(
            'saur8-ct-admin',
            SAUR8_CT_URL . 'assets/js/admin.js',
            array( 'jquery', 'codemirror', 'codemirror-php' ),
            $js_version,
            true
        );
    }

    /**
     * Get asset version for cache busting
     * Uses file modification time in dev mode, plugin version in production
     */
    private function get_asset_version( $asset_path ) {
        $dev_mode = get_option( 'saur8_ct_dev_mode', 0 );
        $cache_strategy = get_option( 'saur8_ct_cache_busting', 'version' );
        
        if ( $dev_mode || $cache_strategy === 'filemtime' ) {
            $file_path = SAUR8_CT_PATH . $asset_path;
            if ( file_exists( $file_path ) ) {
                return filemtime( $file_path );
            }
        } elseif ( $cache_strategy === 'timestamp' ) {
            return time();
        }
        
        return SAUR8_CT_VERSION;
    }

    public static function activate() {
        // Create templates directory
        if ( ! file_exists( SAUR8_CT_TEMPLATES_DIR ) ) {
            wp_mkdir_p( SAUR8_CT_TEMPLATES_DIR );
        }

        // Create index.php for security
        $index_file = SAUR8_CT_TEMPLATES_DIR . 'index.php';
        if ( ! file_exists( $index_file ) ) {
            file_put_contents( $index_file, '<?php // Silence is golden' );
        }
    }
}
