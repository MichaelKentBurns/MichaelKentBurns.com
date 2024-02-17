<?php
/**
 * Plugin Name: Breeze
 * Description: Breeze is a WordPress cache plugin with extensive options to speed up your website. All the options including Varnish Cache are compatible with Cloudways hosting.
 * Version: 2.1.4
 * Text Domain: breeze
 * Domain Path: /languages
 * Author: Cloudways
 * Author URI: https://www.cloudways.com
 * License: GPL2
 * Network: true
 */

/**
 * @copyright 2017  Cloudways  https://www.cloudways.com
 *
 *  This plugin is inspired from WP Speed of Light by JoomUnited.
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

if ( ! defined( 'BREEZE_PLUGIN_DIR' ) ) {
	define( 'BREEZE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'BREEZE_VERSION' ) ) {
	define( 'BREEZE_VERSION', '2.1.4' );
}
if ( ! defined( 'BREEZE_SITEURL' ) ) {
	define( 'BREEZE_SITEURL', get_site_url() );
}
if ( ! defined( 'BREEZE_MINIFICATION_CACHE' ) ) {
	define( 'BREEZE_MINIFICATION_CACHE', WP_CONTENT_DIR . '/cache/breeze-minification/' );
}
if ( ! defined( 'BREEZE_CACHEFILE_PREFIX' ) ) {
	define( 'BREEZE_CACHEFILE_PREFIX', 'breeze_' );
}
if ( ! defined( 'BREEZE_MINIFICATION_EXTRA' ) ) {
	define( 'BREEZE_MINIFICATION_EXTRA', WP_CONTENT_DIR . '/cache/breeze-extra/' );
}
if ( ! defined( 'BREEZE_CACHE_CHILD_DIR' ) ) {
	define( 'BREEZE_CACHE_CHILD_DIR', '/cache/breeze-minification/' );
}
if ( ! defined( 'BREEZE_WP_CONTENT_NAME' ) ) {
	define( 'BREEZE_WP_CONTENT_NAME', '/' . wp_basename( WP_CONTENT_DIR ) );
}
if ( ! defined( 'BREEZE_BASENAME' ) ) {
	define( 'BREEZE_BASENAME', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'BREEZE_PLUGIN_URL' ) ) {
	// Usage BREEZE_PLUGIN_URL . "some_image.png" from plugin folder
	define( 'BREEZE_PLUGIN_URL', plugins_url() . '/' . dirname( plugin_basename( __FILE__ ) ) . '/' );
}

define( 'BREEZE_CACHE_DELAY', true );
define( 'BREEZE_CACHE_NOGZIP', true );
define( 'BREEZE_ROOT_DIR', str_replace( BREEZE_WP_CONTENT_NAME, '', WP_CONTENT_DIR ) );
// Options reader
require_once BREEZE_PLUGIN_DIR . 'inc/class-breeze-options-reader.php';
require_once BREEZE_PLUGIN_DIR . 'inc/class-breeze-cloudflare-helper.php';

// Compatibility checks
require_once BREEZE_PLUGIN_DIR . 'inc/plugin-incompatibility/class-breeze-incompatibility-plugins.php';
require_once BREEZE_PLUGIN_DIR . 'inc/plugin-incompatibility/class-breeze-woocs-compatibility.php';
// Check for if folder/files are writable.
require_once BREEZE_PLUGIN_DIR . 'inc/class-breeze-file-permissions.php';
// AMP compatibility.
require_once BREEZE_PLUGIN_DIR . 'inc/plugin-incompatibility/breeze-amp-compatibility.php';

// Helper functions.
require_once BREEZE_PLUGIN_DIR . 'inc/helpers.php';
require_once BREEZE_PLUGIN_DIR . 'inc/functions.php';

// Handle Heartbeat options.
require_once BREEZE_PLUGIN_DIR . 'inc/class-breeze-heartbeat-settings.php';

//action to purge cache
require_once( BREEZE_PLUGIN_DIR . 'inc/cache/purge-varnish.php' );
require_once( BREEZE_PLUGIN_DIR . 'inc/cache/purge-cache.php' );
require_once( BREEZE_PLUGIN_DIR . 'inc/cache/purge-per-time.php' );
// Handle post exclude if shortcode.
require_once( BREEZE_PLUGIN_DIR . 'inc/class-exclude-pages-by-shortcode.php' );
// Handle the WP emoji library.
require_once( BREEZE_PLUGIN_DIR . 'inc/class-breeze-disable-emoji-option.php' );
// Prefetch URLs.
require_once( BREEZE_PLUGIN_DIR . 'inc/class-breeze-dns-prefetch.php' );

// Activate plugin hook
register_activation_hook( __FILE__, array( 'Breeze_Admin', 'plugin_active_hook' ) );
//Deactivate plugin hook
register_deactivation_hook( __FILE__, array( 'Breeze_Admin', 'plugin_deactive_hook' ) );

require_once( BREEZE_PLUGIN_DIR . 'inc/breeze-admin.php' );
require_once( BREEZE_PLUGIN_DIR . 'inc/class-breeze-prefetch.php' );
require_once( BREEZE_PLUGIN_DIR . 'inc/class-breeze-preload-fonts.php' );


// Load Store Local Files class.
require_once( BREEZE_PLUGIN_DIR . 'inc/class-breeze-store-files-locally.php' );

// Include cronjobs (Gravatars curently(
require_once BREEZE_PLUGIN_DIR . 'inc/class-breeze-cache-cronjobs.php';
$gravatars_enabled = Breeze_Options_Reader::get_option_value( 'breeze-store-gravatars-locally' );
new Breeze_Cache_CronJobs( $gravatars_enabled );

if ( is_admin() || 'cli' === php_sapi_name() ) {

	require_once( BREEZE_PLUGIN_DIR . 'inc/breeze-configuration.php' );
	//config to cache
	require_once( BREEZE_PLUGIN_DIR . 'inc/cache/config-cache.php' );

	//cache when ecommerce installed
	require_once( BREEZE_PLUGIN_DIR . 'inc/cache/ecommerce-cache.php' );
	add_action(
		'init',
		function () {
			new Breeze_Ecommerce_Cache();
			Breeze_Query_Strings_Rules::when_woocommerce_settings_save();
		},
		0
	);

} else {
	if ( ! empty( Breeze_Options_Reader::get_option_value( 'cdn-active' ) )
	     || ! empty( Breeze_Options_Reader::get_option_value( 'breeze-minify-js' ) )
	     || ! empty( Breeze_Options_Reader::get_option_value( 'breeze-minify-css' ) )
	     || ! empty( Breeze_Options_Reader::get_option_value( 'breeze-minify-html' ) )
	     || ! empty( Breeze_Options_Reader::get_option_value( 'breeze-defer-js' ) )
	     || ! empty( Breeze_Options_Reader::get_option_value( 'breeze-move-to-footer-js' ) )
	     || ! empty( Breeze_Options_Reader::get_option_value( 'breeze-delay-all-js' ) )
	     || ! empty( Breeze_Options_Reader::get_option_value( 'breeze-delay-js-scripts' ) )
	) {
		// Call back ob start
		ob_start( 'breeze_ob_start_callback' );
	}
}
// Breeze API
require_once BREEZE_PLUGIN_DIR . 'inc/class-breeze-api.php';
$api_enabled = Breeze_Options_Reader::get_option_value( 'breeze-enable-api' );
if ( $api_enabled ) {
	$options = array(
		'breeze-secure-api' => Breeze_Options_Reader::get_option_value( 'breeze-secure-api' ),
		'breeze-api-token'  => Breeze_Options_Reader::get_option_value( 'breeze-api-token' ),

	);
	new Breeze_Api_Handler( $options );
}

/**
 * Store files locally, First buffer controller to occur in this plugin
 */
add_action( 'init', function () {
	ob_start( 'breeze_ob_start_localfiles_callback' );
}, 5 );


/**
 * Clear all cache if the Breeze version changed.
 * Ignore network dashboard.
 *
 * @return void
 */
function breeze_check_versions() {
	// Get Breeze version in DB
	if (
		false === is_network_admin() &&
		(
			( function_exists( 'is_ajax' ) && false === is_ajax() ) ||
			( function_exists( 'wp_doing_ajax' ) && false === wp_doing_ajax() )
		)
	) {
		$db_breeze_version = get_option( 'breeze_version' ); // breeze_version

		if ( ! $db_breeze_version || version_compare( BREEZE_VERSION, $db_breeze_version, '!=' ) ) {
			update_option( 'breeze_version', BREEZE_VERSION, 'no' );
			do_action( 'breeze_clear_all_cache' );
		}
	}

}

add_action( 'admin_init', 'breeze_check_versions' );

// Compatibility with ShortPixel.
require_once( BREEZE_PLUGIN_DIR . 'inc/compatibility/class-breeze-shortpixel-compatibility.php' );
require_once( BREEZE_PLUGIN_DIR . 'inc/compatibility/class-breeze-avada-cache.php' );

/**
 * Buffer to work with the contents before any changes occured
 *
 * @param $buffer
 *
 * @return array|false|int|mixed|string|string[]
 */
function breeze_ob_start_localfiles_callback( $buffer ) {

	// Store Files Locally
	if ( class_exists( 'Breeze_Store_Files' ) ) {

		$enabled_options = array();

		$options = array(
			'breeze-store-googlefonts-locally',
			'breeze-store-googleanalytics-locally',
			'breeze-store-facebookpixel-locally',
		);

		foreach ( $options as $option ) {
			$enabled_options[ $option ] = Breeze_Options_Reader::get_option_value( $option );
		}

		$store_locally = new \Breeze_Store_Files();
		$buffer        = $store_locally->init( $buffer, $enabled_options );
	}

	// Return content
	return $buffer;
}


// Call back ob start - stack
function breeze_ob_start_callback( $buffer ) {

	// Get buffer from minify
	$buffer = apply_filters( 'breeze_minify_content_return', $buffer );

	if ( ! empty( Breeze_Options_Reader::get_option_value( 'cdn-active' ) ) ) {
		// Get buffer after remove query strings
		$buffer = apply_filters( 'breeze_cdn_content_return', $buffer );
	}

	// Return content
	return $buffer;
}

require_once( BREEZE_PLUGIN_DIR . 'views/option-tabs-loader.php' );
// Minify

require_once( BREEZE_PLUGIN_DIR . 'inc/minification/breeze-minify-main.php' );
require_once( BREEZE_PLUGIN_DIR . 'inc/minification/breeze-minification-cache.php' );
add_action(
	'init',
	function () {
		new Breeze_Minify();

	},
	0
);
// CDN Integration
if ( ! class_exists( 'Breeze_CDN_Integration' ) ) {
	require_once( BREEZE_PLUGIN_DIR . 'inc/cdn-integration/breeze-cdn-integration.php' );
	require_once( BREEZE_PLUGIN_DIR . 'inc/cdn-integration/breeze-cdn-rewrite.php' );
	add_action(
		'init',
		function () {
			new Breeze_CDN_Integration();
		},
		0
	);
}

// Refresh cache for ordered products.
require_once BREEZE_PLUGIN_DIR . 'inc/class-breeze-woocommerce-product-cache.php';
// WP-CLI commands
require_once BREEZE_PLUGIN_DIR . 'inc/wp-cli/class-breeze-wp-cli-core.php';


// Reset to default
add_action( 'breeze_reset_default', array( 'Breeze_Admin', 'plugin_deactive_hook' ), 80 );

add_action(
	'init',
	function () {

		if ( ! isset( $_GET['reset'] ) || $_GET['reset'] != 'default' ) {
			return false;
		}

		$admin = new Breeze_Admin();

		if ( $admin->reset_to_default() ) {
			$route = $widget_id = str_replace( '&reset=default', '', $_SERVER['REQUEST_URI'] );

			$redirect_page = $route;

			header( 'Location: ' . $redirect_page );
			die();
		}

	}
);

/**
 * Add Scheduled event hook
 */
add_action( 'breeze_after_update_scheduled_hook', 'breeze_after_update_scheduled' );

/**
 * Scheduled event executed after update
 *
 * @return void
 */
function breeze_after_update_scheduled() {

	// Clear cache and update database option on update
	update_option( 'breeze_version', BREEZE_VERSION, 'no' );
	do_action( 'breeze_clear_all_cache' );
}

/**
 * This function will update htaccess files after the plugin update is done.
 *
 * This function runs when WordPress completes its upgrade process.
 * It iterates through each plugin updated to see if ours is included.
 *
 * The plugin must be active while updating, otherwise this will do nothing.
 *
 * @see https://codex.wordpress.org/Plugin_API/Action_Reference/upgrader_process_complete
 * @since 1.1.3
 *
 * @param array $upgrader_object
 * @param array $options
 */
function breeze_after_plugin_update_done( $upgrader_object, $options ) {

	// If an update has taken place and the updated type is plugins and the plugins element exists.
	if ( $options['action'] == 'update' && $options['type'] == 'plugin' && isset( $options['plugins'] ) ) {
		// Iterate through the plugins being updated and check if ours is there
		foreach ( $options['plugins'] as $plugin ) {
			if ( $plugin == BREEZE_BASENAME ) {
				// If the install is multi-site, we will add the option for all the blogs.
				if ( is_multisite() ) {
					$blogs = get_sites();
					if ( ! empty( $blogs ) ) {
						foreach ( $blogs as $blog_data ) {
							$blog_id = $blog_data->blog_id;
							switch_to_blog( $blog_id );
							// Add the option for each blog.
							// The visit on any blog will trigger the update to happen.
							add_option( 'breeze_new_update', 'yes', '', false );

							restore_current_blog();
						}
					}
				} else {
					// Add a new option to inform the install that a new version was installed.
					add_option( 'breeze_new_update', 'yes', '', false );
				}

				// Create an event that will execute the newer code
				wp_schedule_single_event( current_time( 'U' ) + 10, 'breeze_after_update_scheduled_hook', array( $options ) );
			}
		}
	}
}

add_action( 'upgrader_process_complete', 'breeze_after_plugin_update_done', 10, 2 );

/**
 * This function is checking on init if there is a need to update htaccess.
 */
function breeze_check_for_new_version() {
	// When permalinks are reset, we also reset the config files.
	if ( isset( $_POST['permalink_structure'] ) || isset( $_POST['category_base'] ) ) {
		$to_action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'permalink';
		if ( 'permalink' !== $to_action ) {
			check_admin_referer( 'options-options' );
		} else {
			check_admin_referer( 'update-permalink' );
		}
		// If the WP install is multi-site

		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			require_once( ABSPATH . '/wp-admin/includes/file.php' );
			WP_Filesystem();
		}

		// import these file in front-end when required.
		if ( ! class_exists( 'Breeze_Ecommerce_Cache' ) ) {
			//cache when ecommerce installed
			require_once( BREEZE_PLUGIN_DIR . 'inc/cache/ecommerce-cache.php' );
		}

		// import these file in front-end when required.
		if ( ! class_exists( 'Breeze_ConfigCache' ) ) {
			//config to cache
			require_once( BREEZE_PLUGIN_DIR . 'inc/cache/config-cache.php' );
		}

		if ( is_multisite() ) {
			// For multi-site we need to also reset the root config-file.
			Breeze_ConfigCache::factory()->write_config_cache( true );

			$blogs = get_sites();
			if ( ! empty( $blogs ) ) {
				foreach ( $blogs as $blog_data ) {
					$blog_id = $blog_data->blog_id;
					switch_to_blog( $blog_id );

					// if the settings are inherited, then we do not need to refresh the config file.
					$inherit_option = get_option( 'breeze_inherit_settings' );
					$inherit_option = filter_var( $inherit_option, FILTER_VALIDATE_BOOLEAN );

					// If the settings are not inherited from parent blog, then refresh the config file.
					if ( false === $inherit_option ) {
						// Refresh breeze-cache.php file
						Breeze_ConfigCache::factory()->write_config_cache();
					}

					restore_current_blog();
				}
			}
		} else {
			// For single site.
			// Refresh breeze-cache.php file
			Breeze_ConfigCache::factory()->write_config_cache();
		}
	}

	// This process can also be started by Wp-CLI.
	if ( ! empty( get_option( 'breeze_new_update', '' ) ) ) {

		// This needs to happen only once.
		if ( class_exists( 'Breeze_Configuration' ) && method_exists( 'Breeze_Configuration', 'update_htaccess' ) ) {
			Breeze_Configuration::update_htaccess();

		}

		// import these file in front-end when required.
		if ( ! class_exists( 'Breeze_Ecommerce_Cache' ) ) {
			//cache when ecommerce installed
			require_once( BREEZE_PLUGIN_DIR . 'inc/cache/ecommerce-cache.php' );
		}

		// import these file in front-end when required.
		if ( ! class_exists( 'Breeze_ConfigCache' ) ) {
			//config to cache
			require_once( BREEZE_PLUGIN_DIR . 'inc/cache/config-cache.php' );
		}
		$breeze_delay_js_scripts = array(
			'gtag',
			'document.write',
			'html5.js',
			'show_ads.js',
			'google_ad',
			'blogcatalog.com/w',
			'tweetmeme.com/i',
			'mybloglog.com/',
			'histats.com/js',
			'ads.smowtion.com/ad.js',
			'statcounter.com/counter/counter.js',
			'widgets.amung.us',
			'ws.amazon.com/widgets',
			'media.fastclick.net',
			'/ads/',
			'comment-form-quicktags/quicktags.php',
			'edToolbar',
			'intensedebate.com',
			'scripts.chitika.net/',
			'_gaq.push',
			'jotform.com/',
			'admin-bar.min.js',
			'GoogleAnalyticsObject',
			'plupload.full.min.js',
			'syntaxhighlighter',
			'adsbygoogle',
			'gist.github.com',
			'_stq',
			'nonce',
			'post_id',
			'data-noptimize',
			'googletagmanager',
		);

		// If the WP install is multi-site
		if ( is_multisite() ) {
			// Migrate old network settings if needed.
			breeze_migrate_old_settings( false, 0, true );

			$basic = get_site_option( 'breeze_basic_settings' );
			if ( isset( $basic['breeze-disable-admin'] ) && ! is_array( $basic['breeze-disable-admin'] ) ) {
				$all_user_roles     = breeze_all_wp_user_roles();
				$active_cache_users = array();
				foreach ( $all_user_roles as $usr_role ) {
					$active_cache_users[ $usr_role ] = 0;

				}

				$old_user_cache = filter_var( $basic['breeze-disable-admin'], FILTER_VALIDATE_BOOLEAN );

				$basic['breeze-disable-admin'] = $active_cache_users;

				if ( false === $old_user_cache ) {
					$basic['breeze-disable-admin']['administrator'] = 1;
					unset( $old_user_cache );
				}

				update_site_option( 'breeze_basic_settings', $basic );
			}

			$advanced_network = get_site_option( 'breeze_file_settings' );
			$is_advanced      = get_site_option( 'breeze_advanced_settings_120' );

			if ( empty( $is_advanced ) ) {
				$advanced_network['breeze-delay-js-scripts'] = $breeze_delay_js_scripts;

				update_site_option( 'breeze_file_settings', $advanced_network );
				update_site_option( 'breeze_advanced_settings_120', 'yes' );
			}

			if ( '1.2.1' === BREEZE_VERSION ) {
				$is_changed = breeze_is_delayjs_changed( true, null, false );

				if ( false === $is_changed ) {
					$advanced_network['breeze-enable-js-delay'] = '0';
				} else {
					$advanced_network['breeze-enable-js-delay'] = '1';
				}

				update_site_option( 'breeze_file_settings', $advanced_network );
			}

			// For multi-site we need to also reset the root config-file.
			Breeze_ConfigCache::factory()->write_config_cache( true );

			$blogs = get_sites();
			if ( ! empty( $blogs ) ) {
				foreach ( $blogs as $blog_data ) {
					$blog_id = (int) $blog_data->blog_id;
					switch_to_blog( $blog_id );
					// Migrate old settings if needed.
					breeze_migrate_old_settings( false, $blog_id );
					// if the settings are inherited, then we do not need to refresh the config file.
					$inherit_option = get_blog_option( $blog_id, 'breeze_inherit_settings', '' );
					if ( '' === $inherit_option ) {
						$inherit_option = '1';
						update_blog_option( $blog_id, 'breeze_inherit_settings', $inherit_option );
					}
					$inherit_option = filter_var( $inherit_option, FILTER_VALIDATE_BOOLEAN );

					// If the settings are not inherited from parent blog, then refresh the config file.
					if ( false === $inherit_option ) {
						// update cache for logged-in users from administrator only to all user roles.
						$basic = get_blog_option( $blog_id, 'breeze_basic_settings' );
						if ( isset( $basic['breeze-disable-admin'] ) && ! is_array( $basic['breeze-disable-admin'] ) ) {
							$all_user_roles     = breeze_all_wp_user_roles();
							$active_cache_users = array();
							foreach ( $all_user_roles as $usr_role ) {
								$active_cache_users[ $usr_role ] = 0;

							}
							$old_user_cache = filter_var( $basic['breeze-disable-admin'], FILTER_VALIDATE_BOOLEAN );

							$basic['breeze-disable-admin'] = $active_cache_users;

							if ( false === $old_user_cache ) {
								$basic['breeze-disable-admin']['administrator'] = 1;
								unset( $old_user_cache );
							}

							update_blog_option( $blog_id, 'breeze_basic_settings', $basic );
						}

						$advanced_options = get_blog_option( $blog_id, 'breeze_file_settings' );
						$is_advanced      = get_blog_option( $blog_id, 'breeze_advanced_settings_120' );

						if ( empty( $is_advanced ) && empty( $advanced_options['breeze-delay-js-scripts'] ) ) {
							$advanced_options['breeze-delay-js-scripts'] = $breeze_delay_js_scripts;

							update_blog_option( $blog_id, 'breeze_file_settings', $advanced_options );
							update_blog_option( $blog_id, 'breeze_advanced_settings_120', 'yes' );
						}

						if ( '1.2.1' === BREEZE_VERSION ) {
							$is_changed = breeze_is_delayjs_changed( false, $blog_id, true );
							if ( false === $is_changed ) {
								$advanced_options['breeze-enable-js-delay'] = '0';
							} else {
								$advanced_options['breeze-enable-js-delay'] = '1';
							}

							update_blog_option( $blog_id, 'breeze_file_settings', $advanced_options );
						}

						// Refresh breeze-cache.php file
						Breeze_ConfigCache::factory()->write_config_cache();
					}

					// Remove the option from all the blogs, meaning each one of them was already updated.
					delete_option( 'breeze_new_update' );

					restore_current_blog();
				}
			}
		} else {
			// Migrate old settings if needed.
			breeze_migrate_old_settings();
			// update cache for logged-in users from administrator only to all user roles.
			$basic = get_option( 'breeze_basic_settings' );
			if ( isset( $basic['breeze-disable-admin'] ) && ! is_array( $basic['breeze-disable-admin'] ) ) {

				$all_user_roles     = breeze_all_wp_user_roles();
				$active_cache_users = array();
				foreach ( $all_user_roles as $usr_role ) {
					$active_cache_users[ $usr_role ] = 0;

				}
				$old_user_cache = filter_var( $basic['breeze-disable-admin'], FILTER_VALIDATE_BOOLEAN );

				$basic['breeze-disable-admin'] = $active_cache_users;

				if ( false === $old_user_cache ) {
					$basic['breeze-disable-admin']['administrator'] = 1;
					unset( $old_user_cache );
				}

				update_option( 'breeze_basic_settings', $basic );
			}

			// For single site.
			$advanced    = breeze_get_option( 'file_settings' );
			$is_advanced = get_option( 'breeze_advanced_settings_120' );

			if ( empty( $is_advanced ) ) {
				$advanced['breeze-delay-js-scripts'] = $breeze_delay_js_scripts;

				breeze_update_option( 'file_settings', $advanced, true );
				breeze_update_option( 'advanced_settings_120', 'yes', true );
			}

			if ( '1.2.1' === BREEZE_VERSION ) {
				$is_changed = breeze_is_delayjs_changed();
				if ( false === $is_changed ) {
					$advanced['breeze-enable-js-delay'] = '0';
				} else {
					$advanced['breeze-enable-js-delay'] = '1';
				}

				breeze_update_option( 'file_settings', $advanced, true );
			}
			// Refresh breeze-cache.php file
			Breeze_ConfigCache::factory()->write_config_cache();

			delete_option( 'breeze_new_update' );
		}
		Breeze_ConfigCache::factory()->write();
	}
}

add_action( 'admin_init', 'breeze_check_for_new_version', 99 );


add_action( 'wp_login', 'refresh_config_files', 10, 2 );

/**
 * Handles the config file reset.
 *
 * @param string $user_login $user->user_login
 * @param object $user WP_User
 *
 * @since 1.1.5
 */
function refresh_config_files( $user_login, $user ) {
	if ( in_array( 'administrator', (array) $user->roles, true ) ) {
		//The user has the "administrator" role
		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			require_once( ABSPATH . '/wp-admin/includes/file.php' );
			WP_Filesystem();
		}
		// import these file in front-end when required.
		if ( ! class_exists( 'Breeze_Ecommerce_Cache' ) ) {
			//cache when ecommerce installed
			require_once( BREEZE_PLUGIN_DIR . 'inc/cache/ecommerce-cache.php' );
		}

		// import these file in front-end when required.
		if ( ! class_exists( 'Breeze_ConfigCache' ) ) {
			//config to cache
			require_once( BREEZE_PLUGIN_DIR . 'inc/cache/config-cache.php' );
		}
		if ( is_multisite() ) {
			$blogs = get_sites();
			// For multi-site we need to also reset the root config-file.
			Breeze_ConfigCache::factory()->write_config_cache( true );

			if ( ! empty( $blogs ) ) {
				foreach ( $blogs as $blog_data ) {
					$blog_id = $blog_data->blog_id;
					switch_to_blog( $blog_id );

					// if the settings are inherited, then we do not need to refresh the config file.
					$inherit_option = get_option( 'breeze_inherit_settings' );
					$inherit_option = filter_var( $inherit_option, FILTER_VALIDATE_BOOLEAN );
					// If the settings are not inherited from parent blog, then refresh the config file.
					if ( false === $inherit_option ) {
						// Refresh breeze-cache.php file
						Breeze_ConfigCache::factory()->write_config_cache();
					}
					restore_current_blog();
				}
			}
		} else {
			$current_file = WP_CONTENT_DIR . '/breeze-config/breeze-config.php';
			if ( file_exists( $current_file ) ) {
				$current_data = include $current_file; //phpcs:ignore
				if ( mb_strtolower( $current_data['homepage'] ) !== get_site_url() ) {
					// For single site.
					// Refresh breeze-cache.php file
					Breeze_ConfigCache::factory()->write_config_cache();
				}
			}
		}
	}
}


/**
 * Preg replace callback function for anchor handling
 *
 * @param $match
 *
 * @return string
 */
function breeze_cc_process_match( $match ) {
	// Get the home URL
	$home_url = $GLOBALS['breeze_config']['homepage'];
	$home_url = ltrim( $home_url, 'https:' );

	// Set the rel attribute values
	$replacement_rel_arr = array( 'noopener', 'noreferrer' );

	// Extract the href and target attributes
	$href_attr   = '';
	$target_attr = '';
	preg_match( '/href=(\'|")(.*?)\\1/si', $match[1], $href_match );
	preg_match( '/target=(\'|")(.*?)\\1/si', $match[1], $target_match );
	if ( $href_match ) {
		$href_attr = $href_match[2];
	}
	if ( $target_match ) {
		$target_attr = $target_match[2];
	}

	// Check if this is an external link
	if ( ! empty( $href_attr ) &&
		 filter_var( $href_attr, FILTER_VALIDATE_URL ) &&
		 strpos( $href_attr, $home_url ) === false &&
		 strpos( $target_attr, '_blank' ) !== false ) {

		// Extract the rel attribute, if present
		$rel_attr = '';
		preg_match( '/rel=(\'|")(.*?)\\1/si', $match[1], $rel_match );
		if ( $rel_match ) {
			$rel_attr = $rel_match[2];
		}

		// Set or modify the rel attribute as necessary
		if ( empty( $rel_attr ) ) {
			return '<a ' . $match[1] . ' rel="noopener noreferrer">';
		} else {
			$existing_rels = explode( ' ', $rel_attr );
			$existing_rels = array_unique( array_merge( $replacement_rel_arr, $existing_rels ) );

			return '<a ' . str_replace( $rel_attr, implode( ' ', $existing_rels ), $match[1] ) . '>';
		}
	} else {
		// If this is not an external link, just return the matched string
		return '<a ' . $match[1] . '>';
	}
}
