<?php

/**
	* Plugin Name:	Important Links
	* Description:	Easily keep track of important URLs inside your WordPress Dashboard
	* Version:		1.1.0
	* Author:		Alex Demchak
	* Author URI:	http://xhynk.com/

	*	Copyright Alexander Demchak, Third River Marketing LLC
	
	*	This program is free software; you can redistribute it and/or modify
	*	it under the terms of the GNU General Public License as published by
	*	the Free Software Foundation; either version 3 of the License, or
	*	(at your option) any later version.

	*	This program is distributed in the hope that it will be useful,
	*	but WITHOUT ANY WARRANTY; without even the implied warranty of
	*	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	*	GNU General Public License for more details.

	*	You should have received a copy of the GNU General Public License
	*	along with this program. If not, see http://www.gnu.org/licenses.
*/

if ( ! defined( 'ABSPATH' ) ) exit;

class TRM_Links {
	/**
	 * Set the Class Instance
	 */
	static $instance;

	public static function get_instance(){
		if( ! self::$instance )
			self::$instance = new self();

		return self::$instance;
	}

	/**
	 * Class Constructor - Runs Action Hooks
	 */
	public function __construct(){
		add_action( 'admin_menu', [$this, 'register_admin_page'] );
		add_action( 'admin_enqueue_scripts', [$this, 'exclusive_admin_assets'] );
		//add_action( 'admin_enqueue_scripts', [$this, 'global_admin_assets'] );

		add_action( 'wp_ajax_remove_important_link', [$this, 'remove_important_link'] );
		add_action( 'wp_ajax_add_new_important_link', [$this, 'add_new_important_link'] );
	}

	/**
	 * Prevent undefined index errors by defining a variable with a default
	 *
	 * @param variable $var - The variable to check or define.
	 * @param mixed $default - Value to default to if undefined.
	 * @return mixed - The already defined, or now defined variable
	 */
	public function issetor( &$var, $default = false ){
		return isset( $var ) ? $var : $default;
	}

	/**
	 * Add the TRM Links Admin Page
	 *
	 * @return void
	 */
	public function register_admin_page(){
		add_menu_page( 'Important Links', 'Important Links', 'edit_posts', 'trm-important-links', [$this, 'admin_panel'], 'dashicons-external' );
	}

	/**
	 * Include the source code for the admin page
	 *
	 * @return void
	 */
	public function admin_panel(){
		if( ! current_user_can( 'edit_posts' ) ){
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		} else {
			require_once dirname(__FILE__).'/inc/admin-panel.php';	
		}
	}

	/**
	 * Enqueue Exclusive Admin Only Assets
	 *
	 * @param string $hook - The current wp-admin hook.
	 * @return void
	 */
	public function exclusive_admin_assets( $hook ){
		$hook_array = [
			'toplevel_page_trm-important-links',
		];

		if( in_array( $hook, $hook_array ) ){
			$assets_dir = plugins_url( '/assets', __FILE__ );
			
			wp_enqueue_script( 'trm-links-admin', "$assets_dir/js/admin.min.js", ['jquery'], filemtime( plugin_dir_path( __FILE__ ) . 'assets/js/admin.min.js' ), true );
			wp_enqueue_style( 'trm-links-admin', "$assets_dir/css/admin.min.css", [], filemtime( plugin_dir_path( __FILE__ ) . 'assets/css/admin.min.css' ) );
		}
	}

	/**
	 * Enqueue Admin Only Assets
	 *
	 * @param string $hook - The current wp-admin hook.
	 * @return void
	 */
	public function global_admin_assets(){
		echo '<style>
			#adminmenu #toplevel_page_trm-links { background-color: #ee3d96; }
			#adminmenu #toplevel_page_trm-links:hover { opacity: .8; }
			#adminmenu #toplevel_page_trm-links:hover a { color: #fff !important; }
		</style>';
	}

	/**
	 * Display a custom SVG
	 *
	 * @param string $icon - The desired icon to display
	 * @param string $class - A space separated list of classes to add
	 * @param string $attr - A custom attribute string to display
	 * @param bool $echo - False to return, True to echo output
	 * @return string - The final usable SVG HTML
	 */
	public function display_svg( $icon = '', $class = '', $atts = '', $echo = false ){
		require dirname(__FILE__).'/inc/svg-icons.php';

		if( $echo === true ){
			echo $svg;
		} else {
			return $svg;
		}
	}

	/**
	 * Create a JSON Response for AJAX Requests
	 *
	 * @param int $status - The desired Status Code
	 * @param string $message - The desired Message
	 * @param (assoc) array $additional_info - Any other information to add to the response array
	 * @return string - echos a json_encoded array for use in AJAX
	 */
	public function json_response( $status = 501, $message = '', $additional_info = null ){
		$response = [];

		$response['status']  = $status;
		$response['message'] = $message;

		if( $additional_info ){
			foreach( $additional_info as $key => $value ){
				$response[$key] = $value;
			}
		}

		echo json_encode( $response );
		wp_die();
	}

	/**
	 * Stop AJAX functions if no $_POST data
	 */
	public function require_POST(){
		if( ! $_POST )
			wp_die( 'Please do not call this function directly, only make POST requests.' );
	}

	/**
	 * Add a new Important Link (AJAX)
	 *
	 * @return void
	 */
	public function add_new_important_link(){
		$this->require_POST();
		extract( $_POST );
		$option = '_trm_important_links';

		// Make sure Nonce is Valid
		if( ! $add_new_account || ! wp_verify_nonce( $add_new_account, 'update_option' ) )
			$this->json_response( 403, 'Error: Invalid nonce, please try again.' );

		// Make sure URL and
		if( ! $url || ! $label )
			$this->json_response( 403, 'Error: All Fields are Required' );

		// Store without Scheme
		$url       = str_replace( array( 'https://', 'http://', '//' ), '', $url );
		$login_url = str_replace( array( 'https://', 'http://', '//' ), '', $login_url );

		if( ! $links = get_option( $option ) ){
			// Set up Array if not there
			$links = array();
		}

		$new_link = array(
			'url'       => esc_url_raw( $url ),
			'login_url' => esc_url_raw( $login_url ),
			'label'     => preg_replace('/[^a-zA-Z0-9_()[]!@\#\*\' ]+/', '', strip_tags( $label ) )
		);

		$id = md5( implode( $new_link ) );

		$new_link['id'] = $id;

		$links[] = $new_link;

		if( update_option( $option, $links ) ){
			$this->json_response( 200, "$label has been added to your links.", array( 'newLink' => $new_link ) );
		} else {
			$this->json_response( 400, 'Request Failed.' );
		}
	}

	/**
	 * Remove an Important Link (AJAX)
	 *
	 * @return void
	 */
	public function remove_important_link(){
		$this->require_POST();
		extract( $_POST );
		$option = '_trm_important_links';

		// Make sure an ID is set
		if( ! $id )
			$this->json_response( 403, 'Error: No ID Specified.' );

		// Create new array, excluding "removed" ID
		if( $links = get_option( $option ) ){
			$new_links = array();

			foreach( $links as $link ){
				if( $link['id'] != $id ){
					$new_links[] = $link;
				}
			}
		}

		if( update_option( $option, $new_links ) ){
			$this->json_response( 200, "Link Removed.", array( 'id' => $id ) );
		} else {
			$this->json_response( 400, 'Request Failed.' );
		}
	}
}

add_action( 'plugins_loaded', ['TRM_Links', 'get_instance'] );