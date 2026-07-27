<?php
/**
 * Plugin Name: Ultimate Markdown
 * Description: A set of tools that helps you work with the Markdown language.
 * Version: 1.26
 * Author: DAEXT
 * Author URI: https://daext.com
 * Text Domain: ultimate-markdown
 * License: GPLv3
 *
 * @package ultimate-markdown
 */

use Daext\Daextulma\NoticesManager\DAEXT_Notices_Manager;

// Prevent direct access to this file.
if ( ! defined( 'WPINC' ) ) {
	die();
}

// Set constants.
define( 'DAEXTULMA_EDITION', 'FREE' );

// Save the PHP version in a format that allows a numeric comparison.
if ( ! defined( 'DAEXTULMA_PHP_VERSION' ) ) {
	$version = explode( '.', PHP_VERSION );
	define( 'DAEXTULMA_PHP_VERSION', ( $version[0] * 10000 + $version[1] * 100 + $version[2] ) );
}

// Rest API.
require_once plugin_dir_path( __FILE__ ) . 'rest/class-daextulma-rest.php';
add_action( 'plugins_loaded', array( 'Daextulma_Rest', 'get_instance' ) );

// Class shared across public and admin.
require_once plugin_dir_path( __FILE__ ) . 'shared/class-daextulma-shared.php';

// Perform the Gutenberg related activities only if Gutenberg is present.
if ( function_exists( 'register_block_type' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'blocks/src/init.php';
}

/**
 * Initialize the plugin notices manager used to display documentation and review notices for this plugin.
 */
require_once __DIR__ . '/inc/daext-notices-manager/class-daext-notices-manager.php';
DAEXT_Notices_Manager::get_instance( [
	'plugin_slug'        => 'ultimate-markdown',
	'plugin_file'        => __FILE__,
	'docs_url'           => 'https://daext.com/kb/ultimate-markdown/quick-start-guide/',
	'review_url'         => 'https://wordpress.org/support/plugin/ultimate-markdown/reviews/#new-post',
	'plugin_prefix'      => 'daextulma',
	'settings_screen_id' => 'markdown_page_daextulma-options',
	'feature_used'       => true,
] );

// Admin.
if ( is_admin() ) {

	require_once plugin_dir_path( __FILE__ ) . 'admin/class-daextulma-admin.php';

	// If this is not an AJAX request, create a new singleton instance of the admin class.
	if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
		add_action( 'plugins_loaded', array( 'Daextulma_Admin', 'get_instance' ) );
	}

	// Load meta boxes.
	require_once plugin_dir_path( __FILE__ ) . 'admin/inc/class-daextulma-meta-boxes.php';
	add_action( 'plugins_loaded', array( 'Daextulma_Meta_Boxes', 'get_instance' ) );

	// Activate the plugin using only the class static methods.
	register_activation_hook( __FILE__, array( 'Daextulma_Admin', 'ac_activate' ) );

	// Update the plugin db tables and options if they are not up-to-date.
	Daextulma_Admin::ac_create_database_tables();
	Daextulma_Admin::ac_initialize_options();

}

// Ajax.
if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

	// Admin.
	require_once plugin_dir_path( __FILE__ ) . 'class-daextulma-ajax.php';
	add_action( 'plugins_loaded', array( 'Daextulma_Ajax', 'get_instance' ) );

}

/**
 * Customize the action links in the "Plugins" menu.
 *
 * @param array $actions An array of plugin action links.
 *
 * @return mixed
 */
function daextulma_customize_action_links( $actions ) {
	$actions[] = '<a href="https://daext.com/ultimate-markdown/" target="_blank">' . esc_html__( 'Buy the Pro Version', 'daext-autolinks-manager' ) . '</a>';
	return $actions;
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'daextulma_customize_action_links' );

/**
 * Load the plugin text domain for translation.
 *
 * @return void
 */
function daextulma_load_plugin_textdomain() {
	load_plugin_textdomain( 'ultimate-markdown', false, 'ultimate-markdown/lang/' );
}

add_action( 'init', 'daextulma_load_plugin_textdomain' );