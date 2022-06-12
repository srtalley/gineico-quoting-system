<?php

namespace Gineicio\QuotingSystem;
use \DustySun\WP_Settings_API\v2 as DSWPSettingsAPI;

class QuotingSystemSettings {

	private $gineico_quoting_plugin_hook;

	private $gineico_quoting_settings_page;

	private $gineico_quoting_settings = array();

	private $gineico_quoting_main_settings = array();

	private $gineico_quoting_theme_customizer;

	// Create the object
	public function __construct() {

		// create the various menu pages 
		add_action( 'admin_menu', array($this, 'gineico_quoting_create_admin_page'));

		// Register the menu
		add_action( 'admin_menu', array($this, 'gineico_quoting_admin_menu' ));

		// add admin scripts
		// add_action( 'admin_enqueue_scripts', array($this,  'gineico_quoting_admin_scripts' ));

		// Add settings & support links
   		add_filter( 'plugin_action_links', array($this,'gineico_quoting_add_action_plugin'), 10, 5 );


	} // end public function __construct()

	public function gineico_quoting_create_admin_page() {
		// set the settings api options
		$ds_api_settings = array(
			'json_file' => plugin_dir_path( __FILE__ ) . '/gineico-quoting-system.json',
			'register_settings' => true,
			'views_dir' => plugin_dir_path( __FILE__ ) . '/admin/views'
		);
		// Create the settings object
		$this->gineico_quoting_settings_page = new DSWPSettingsAPI\SettingsBuilder($ds_api_settings);

		// Create the customizer
 		// Get the current settings
		$this->gineico_quoting_settings = $this->gineico_quoting_settings_page->get_current_settings();

		// Get the plugin options
		$this->gineico_quoting_main_settings = $this->gineico_quoting_settings_page->get_main_settings();
	} // end function gineico_quoting_create_admin_page

	// Adds admin menu under the Sections section in the Dashboard
	public function gineico_quoting_admin_menu() {

		$this->gineico_quoting_plugin_hook = add_submenu_page(
			'options-general.php',
			__('Gineico Quoting System', 'gineico_quoting'),
			__('Gineico Quoting System', 'gineico_quoting'),
			'manage_options',
			'gineico-quoting-system',
			array($this, 'gineico_quoting_menu_options')
		);

	} // end public function gineico_quoting_admin_menu()

	public function gineico_quoting_admin_scripts( $hook ) {

		// if($hook == $this->gineico_quoting_plugin_hook) {
		// 	wp_enqueue_style('gineico-quoting-system-admin', plugins_url('/css/gineico-quoting-system-admin.css', __FILE__));

		// }

	} // end public function gineico_quoting_add_color_picker

	// Create the actual options page
	public function gineico_quoting_menu_options() {
		$gineico_quoting_settings_title = $this->gineico_quoting_main_settings['name'];

		// Create the main page HTML
		$this->gineico_quoting_settings_page->build_settings_panel($gineico_quoting_settings_title);
	} // end function

	//function to add settings links to plugins area
	public function gineico_quoting_add_action_plugin( $actions, $plugin_file ) {

		$plugin = plugin_basename(__DIR__) . '/gineico-quoting-system.php';

		if ($plugin == $plugin_file) {

			$site_link = array('support' => '<a href="' . $this->gineico_quoting_main_settings['item_uri'] . '" target="_blank">' . __('Support', $this->gineico_quoting_main_settings['text_domain']) . '</a>');
			$actions = array_merge($site_link, $actions);

			if ( is_plugin_active( $plugin) ) {
				$settings = array('settings' => '<a href="admin.php?page=' . $this->gineico_quoting_main_settings['page_slug'] . '">' . __('Settings', $this->gineico_quoting_main_settings['text_domain']) . '</a>');
				$actions = array_merge($settings, $actions);
			} //end if is_plugin_active
		}
		return $actions;

	} // end function gineico_quoting_add_action_plugin

	public function gineico_quoting_upgrade_process(){
		$update_db_flag = false;
		$db_plugin_settings = get_option('gineico_quoting_main_settings');

		// check the database version

		// if($db_plugin_settings['version'] < '0.1') {
		// 	$update_db_flag = true;
		// } //end if < 1.2
		
		// if($db_plugin_settings['version'] != $this->gineico_quoting_main_settings['version']){
		// 	$update_db_flag = true;
		// } // end if 

		// if($db_plugin_settings['version'] < '0.9.5') {
		// 	// Add the .htaccess rules for the dslp-games directory
		// 	Games::generate_dslp_games_dir();
		// }
		// if($update_db_flag) {
		// 	//update the version info stored in the DB
		// 	$this->gineico_quoting_settings_page->wl('Updating ' . $this->gineico_quoting_main_settings['name']  . ' settings in DB...');
		// 	$this->gineico_quoting_settings_page->set_main_settings(true);
		// } // end if($update_db_flag) 


		
   } // end function gineico_quoting_upgrade_process

	public function gineico_quoting_wp_upgrade_complete( $upgrader_object, $options ) {
		// $current_plugin_path_name = plugin_basename(__DIR__) . '/gineico-quoting-system.php';
		// if ($options['action'] == 'update' && $options['type'] == 'plugin' ){
		// 	foreach($options['plugins'] as $each_plugin){
		// 		if ($each_plugin == $current_plugin_path_name) {
		// 			set_transient('gineico_quoting_updated', 1);
		// 		} // end if ($each_plugin == $current_plugin_path_name)
		// 	 } // end foreach($options['plugins'] as $each_plugin)
		//  } // end if ($options['action'] == 'update' && $options['type'] == 'plugin' )
	} // end function gineico_quoting_wp_upgrade_complete

	
   
} // end class QuotingSystemSettings
if( is_admin() )
    $gineico_quoting_sytem_settings = new QuotingSystemSettings();
