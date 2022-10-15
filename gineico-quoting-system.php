<?php
/*
Plugin Name: Gineico Quoting System
Description: Adds features to the WooCommerce admin order screen and contains modifications to the YITH Request a Quote system.
Author: Gineico
Author URI: https://www.gineico.com.au
Version: 1.5.3
Text Domain: gineico_quoting
License: GPLv2
*/


namespace Gineico\QuotingSystem;
use \DustySun\WP_Settings_API\v2 as DSWPSettingsAPI;

define( 'GINEICO_QUOTING_SYSTEM__FILE__', __FILE__ );

//Include the admin panel page
require_once( dirname( __FILE__ ) . '/gineico-quoting-system-admin.php');

require_once( dirname( __FILE__ ) . '/lib/dustysun-wp-settings-api/ds_wp_settings_api.php');
require_once( dirname( __FILE__ ) . '/classes/class-gqs-site-utils.php');
require_once( dirname( __FILE__ ) . '/classes/class-gqs-quote-terms.php');
require_once( dirname( __FILE__ ) . '/classes/class-gqs-woocommerce-order.php');
require_once( dirname( __FILE__ ) . '/classes/class-gqs-woocommerce-product.php');
require_once( dirname( __FILE__ ) . '/classes/class-gqs-woocommerce-quote-description.php');
require_once( dirname( __FILE__ ) . '/classes/class-gqs-woocommerce-templates.php');
require_once( dirname( __FILE__ ) . '/classes/class-gqs-yith-woocommerce-quotes.php');

class QuotingSystemController {

    private $gineico_quoting_json_file;
    private $gineico_quoting_settings_obj;
    public $current_settings;
    public $gineico_quoting_main_settings;

    public function __construct() {

      // get the settings
      // $this->gineico_quoting_create_settings();
      // set the default settings
      // register_activation_hook( __FILE__, array($this, 'gineico_quoting_activation_hook' ));

        add_action( 'wp_enqueue_scripts', array( $this, 'gineico_quoting_register_styles_scripts' ), 1000 );
      
      // add_filter('get_gineico_quoting_main_settings', array($this, 'get_gineico_quoting_main_settings'));
      // add_filter('get_gineico_quoting_current_settings', array($this, 'get_gineico_quoting_current_settings'));

      
    } // end public function __construct

    public function gineico_quoting_create_settings() {

        // set the settings api options
        $ds_api_settings = array(
          'json_file' => plugin_dir_path( __FILE__ ) . '/gineico-quoting-system.json'
        );
        
        $this->gineico_quoting_settings_obj = new DSWPSettingsAPI\SettingsBuilder($ds_api_settings);

        // get the settings
        $this->current_settings = $this->gineico_quoting_settings_obj->get_current_settings();

        // Get the plugin options
        $this->gineico_quoting_main_settings = $this->gineico_quoting_settings_obj->get_main_settings();
        

    } // end function gineico_quoting_create_settings

    /**
     * Function to return the main settings object
     */
    public function get_gineico_quoting_main_settings() {
      return $this->gineico_quoting_main_settings;
    }
    /**
     * Function to return the main settings object
     */
    public function get_gineico_quoting_current_settings() {
      return $this->current_settings;
    }
    public function gineico_quoting_activation_hook() {
      // $this->generate_dslp_games_dir();
    } // end function gineico_quoting_activation_hook()
    

    public function gineico_quoting_register_styles_scripts() {

      if(site_url() != "https://www.gineicomarine.com.au" && site_url() != "https://gineicomarine.dev.dustysun.com") {

        if(is_product()) {
          $plugin_data = get_plugin_data( __FILE__ );

          wp_enqueue_script('gineico-gqs-frontend', plugins_url('js/gineico-gqs-frontend.js', __FILE__), array('jquery'), $plugin_data['Version'], true);
        
          wp_localize_script( 'gineico-gqs-frontend', 'gineico_gqs_frontend', array(
            'ajaxurl'   => admin_url( 'admin-ajax.php' ),
            'ajaxnonce' => wp_create_nonce( 'gineico_gqs_frontend' )
          ) );
        }

      }

    }

    public function gineico_quoting_header() {


    } // end function gineico_quoting_header

    public function gineico_quoting_footer() {

    } // end function gineico_quoting_footer


} // end class QuotingSystemController

$gineico_quoting_sytem_controller = new QuotingSystemController();
