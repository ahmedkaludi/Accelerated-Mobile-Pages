<?php
/*
Plugin Name: Accelerated Mobile Pages - TechNutty Variant
Plugin URI: https://wordpress.org/plugins/accelerated-mobile-pages/
Description: AMP for WP - Accelerated Mobile Pages for WordPress
Version: 0.9.2.tn.3.0
Author: Ahmed Kaludi, Mohammed Kaludi
Author URI: http://ampforwp.com/
Donate link: https://www.paypal.me/Kaludi/5
License: GPL2
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

// Rewrite the Endpoints after the plugin is activate, as priority is set to 11
function ampforwp_add_custom_post_support() {

	add_rewrite_endpoint( AMP_QUERY_VAR, EP_PERMALINK | EP_PAGES | EP_ROOT );
	global $redux_builder_amp;
	if($redux_builder_amp['amp-on-off-for-all-pages']){
		add_post_type_support( 'page', AMP_QUERY_VAR );
	}
}
add_action( 'init', 'ampforwp_add_custom_post_support',11);

define('AMPFORWP_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
define('AMPFORWP_IMAGE_DIR',plugin_dir_url(__FILE__).'images');
define('AMPFORWP_VERSION','0.9.2');

/*
 * Load Files only in the backend
 * As we don't need plugin activation code to run everytime the site loads
*/

if ( is_admin() ) {
 	require_once AMPFORWP_PLUGIN_DIR . '/classes/class-tgm-plugin-activation.php';

 // Add Settings Button in Plugin backend
 	if ( ! function_exists( 'ampforwp_plugin_settings_link' ) ) {


 		add_filter( 'plugin_action_links', 'ampforwp_plugin_settings_link', 10, 5 );

 		function ampforwp_plugin_settings_link( $actions, $plugin_file )  {
 			static $plugin;
 			if (!isset($plugin))
 				$plugin = plugin_basename(__FILE__);
 				if ($plugin == $plugin_file) {
 					$settings = array('settings' => '<a href="admin.php?page=amp_options&tab=8">' . __('Settings', 'ampforwp') . '</a>');
					include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
					if ( is_plugin_active( 'amp/amp.php' ) ) {
					    //if parent plugin is activated
								$actions = array_merge( $actions, $settings );
					} else{
						if(is_plugin_active( 'amp/amp.php' )){
							$actions = array_merge( $actions, $settings );
						}else{
						$please_activate_parent_plugin = array('Please Activate Parent plugin' => '<a href="'.get_admin_url() .'plugin-install.php?s=amp&tab=search&type=term">' . __('Please Activate Parent plugin', 'ampforwp') . '</a>');
						$actions = array_merge( $please_activate_parent_plugin,$actions );
					}
					}

 				}
 		return $actions;
 		}
 	}

} // is_admin() closing

if ( ! class_exists( 'Ampforwp_Init', false ) ) {
	class Ampforwp_Init {

		public function __construct(){

			// Load Files required for the plugin to run
			require AMPFORWP_PLUGIN_DIR .'/includes/includes.php';

			require AMPFORWP_PLUGIN_DIR .'/classes/class-init.php';
			new Ampforwp_Loader;

		}
	}
}
/*
 * Start the plugin.
 * Gentlemen start your engines
 */
function ampforwp_plugin_init() {
	if ( defined( 'AMP__FILE__' ) && defined('AMPFORWP_PLUGIN_DIR') ) {
		new Ampforwp_Init;
	}
}
add_action('init','ampforwp_plugin_init',9);

function ampforwp_page_template_redirect() {
	global $redux_builder_amp;
	if($redux_builder_amp['amp-mobile-redirection']){
		if ( wp_is_mobile() ) {
			if ( is_amp_endpoint() ) {
				return; 
			} else {
				if ( is_home() ) {
					wp_redirect( trailingslashit( esc_url( home_url() ) ) .'?'. AMP_QUERY_VAR ,  301 );
					exit();
				} elseif ( is_archive() ) {
					return ;
				} else {
					wp_redirect( trailingslashit( esc_url( ( get_permalink( $id ) ) ) ) . AMP_QUERY_VAR , 301 );
					exit();
				}
			}
		}
	}
}

add_action( 'template_redirect', 'ampforwp_page_template_redirect', 30 ); 
add_action( 'template_redirect', 'ampforwp_page_template_redirect_archive', 10 ); 

function ampforwp_page_template_redirect_archive() {

	if( is_archive() ) {
		if ( is_amp_endpoint() ) {
			global $wp;
			$archive_current_url = add_query_arg( '', '', home_url( $wp->request ) ); 
			$archive_current_url  = trailingslashit($archive_current_url );
			wp_redirect( esc_url( $archive_current_url )  , 301 );
			exit();
		}
	}  

}
