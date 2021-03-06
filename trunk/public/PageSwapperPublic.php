<?php namespace Pub;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/crazypsycho
 * @since      1.0.0
 *
 * @package    PageSwapper
 * @subpackage PageSwapper/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    PageSwapper
 * @subpackage PageSwapper/public
 * @author     Sascha Hennemann <shennemann@rto.de>
 */
class PageSwapperPublic {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $pluginName The ID of this plugin.
     */
    private $pluginName;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * The options from admin-page
     *
     * @since       1.0.3
     * @access      private
     * @var         array[]
     */
    private $options;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string $pluginName The name of the plugin.
     * @param      string $version The version of this plugin.
     */
    public function __construct( $pluginName, $version ) {

        $this->pluginName = $pluginName;
        $this->version = $version;
        //$this->options = MagicAdminPage::getOption('page-swapper');


        $this->options = array(
            'debugmode' => get_theme_mod( 'pageswapper_debugmode', false ),
            'useOldOwl' => get_theme_mod( 'pageswapper_useOldOwl', false ),
            'selector' => get_theme_mod( 'pageswapper_selector', 'body' ),
            'owlConfig' => get_theme_mod( 'pageswapper_owlConfig', '' ),
            'disableHash' => get_theme_mod( 'pageswapper_disableHash', false ),
        );

        // Embed footerscript
        add_action( 'wp_footer', array( $this, 'insertFooterScript' ) );
        add_action( 'wpcf7_form_action_url', array( $this, 'removePswFromUrl' ) );

        // do_action if page is loaded with ajax
        if ( filter_has_var( INPUT_GET, 'pswLoad' ) ) {
            do_action( 'pswload_ajax_render_page' );
        }
    }

    public function removePswFromUrl( $url ) {
        $url = str_replace( array( '?pswLoad=1', '&pswLoad=1', '$pswLoad', '?pswLoad' ), '', $url );
        $url = explode( '#', $url );
        return $url[0];
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueueStyles() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in PageSwapperLoader as all of the hooks are defined
         * in that particular class.
         *
         * The PageSwapperLoader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style( $this->pluginName, plugin_dir_url( __FILE__ ) . 'css/page-swapper-public.css', array(), $this->version, 'all' );

        $buildPath = plugin_dir_url( __FILE__ ) . '../build';

        if ( !empty( $this->options['useOldOwl'] ) ) {
            // owl 1
            wp_enqueue_style( 'owl.carousel', $buildPath . '/css/owl.carousel-v1.css' );
            wp_enqueue_style( 'owl.carousel.theme', $buildPath . '/css/owl.theme-v1.css' );
            wp_enqueue_style( 'owl.carousel.transitions', $buildPath . '/css/owl.transition-v1.css' );
        } else {
            // owl 2
            wp_enqueue_style( 'owl.carousel', $buildPath . '/css/owl.carousel.min.css' );
            wp_enqueue_style( 'owl.carousel.theme', $buildPath . '/css/owl.theme.default.min.css' );
        }
        wp_enqueue_style( 'animate.css', $buildPath . '/css/animate.min.css' );
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueueScripts() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in PageSwapperLoader as all of the hooks are defined
         * in that particular class.
         *
         * The PageSwapperLoader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        #wp_enqueue_script( $this->pluginName, plugin_dir_url( __FILE__ ) . 'js/page-swapper-public.js', array( 'jquery' ), $this->version, false );

        $buildPath = plugin_dir_url( __FILE__ ) . '../build';

        if ( !empty( $this->options['useOldOwl'] ) ) {
            wp_enqueue_script( 'owl.carousel', $buildPath . '/js/owl.carousel-v1.min.js', array( 'jquery' ) );
        } else {
            wp_enqueue_script( 'owl.carousel', $buildPath . '/js/owl.carousel.min.js', array( 'jquery' ) );
        }

        if ( !empty( $this->options['debugmode'] ) ) {
            wp_enqueue_script( 'page-swapper', $buildPath . '/js/page-swapper.js', array( 'owl.carousel' ), $this->version, true );
            wp_enqueue_script( 'page-swapper-owl', $buildPath . '/js/psw.owl.js', array( 'page-swapper' ), $this->version, true );
        } else {
            wp_enqueue_script( 'page-swapper', $buildPath . '/js/page-swapper.min.js', null, $this->version, true );
        }
    }

    /**
     * Embed the script with the given settings
     *
     * @param string $footer
     */
    public function insertFooterscript( $footer ) {
        $current_site = str_replace( get_bloginfo( 'wpurl' ), '', $_SERVER['REQUEST_URI'] );
        $current_site = substr( $current_site, 1, strlen( $current_site ) - 1 );

        if ( empty( $current_site ) ) {
            $current_site = '';
        }

        // get options
        $options = $this->options;
        $selector = !empty( $options['selector'] ) ? $options['selector'] : '#wrap';
        $owlConfig = !empty( $options['owlConfig'] ) ? $options['owlConfig'] : '';
        $oldOwl = !empty( $options['useOldOwl'] ) ? 'owlVersion: 1,' : '';

        $debug = false;

        if ( ( !empty( $this->options['debugmode'] ) ) ||
            isset( $_REQUEST['pswdebug'] )
        ) {
            $debug = true;
        }

        // minify
        $owlConfig = preg_replace( "/^\s{2,}?([^,]+?),?$/m", ',', $owlConfig );
        $owlConfig = preg_replace( "/(\r?\n?)*/", '', $owlConfig );


        $script = '<script>';
        $script .= 'jQuery(function($) {$("' . $selector . '").pageSwapper({
            selector: "' . $selector . '",
            owlConfig: {' . $owlConfig . '},
            ' . ( $debug ? 'debug: true,' : '' )
            . ( !empty( $this->options['disableHash'] ) ? 'disableHash: true,' : '' ) .
            $oldOwl . '
        });});';

        $script .= 'jQuery(document).on(\'psw-loadcomplete\', \'.psw-container\', function(e, args) {';

        // fix for contact form 7
        $script .= 'if (typeof jQuery.fn.wpcf7InitForm != \'undefined\')
                jQuery(\'.wpcf7-form\').wpcf7InitForm();';

        // get post-css for elementor pages
        if ( defined( 'ELEMENTOR_VERSION' ) ) {
            $script .= file_get_contents( dirname( __FILE__ ) . '/js/elementor-fixes.js' );
        }

        $script .= '});';
        $script .= '</script>';

        $footer = $footer . $script;

        echo $footer;
    }
}
