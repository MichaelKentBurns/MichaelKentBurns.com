<?php
/**
 * @copyright 2017  Cloudways  https://www.cloudways.com
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

function set_as_network_screen() {
	if ( isset( $_GET['is-network'] ) || isset( $_POST['is-network'] ) ) {
		$is_network = false;

		if ( isset( $_GET['is-network'] ) ) {
			$is_network = filter_var( $_GET['is-network'], FILTER_VALIDATE_BOOLEAN );
		}

		if ( isset( $_POST['is-network'] ) ) {
			$is_network = filter_var( $_POST['is-network'], FILTER_VALIDATE_BOOLEAN );
		}

		if ( true === $is_network && ! defined( 'WP_NETWORK_ADMIN' ) ) {
			define( 'WP_NETWORK_ADMIN', true );
		}
	}
}

/**
 * Retrieve site options accounting for settings inheritance.
 *
 * @param string $option_name
 * @param bool $is_local
 *
 * @return array
 */
function breeze_get_option( $option_name, $is_local = false ) {
	$inherit = true;

	global $breeze_network_subsite_settings;

	if ( is_network_admin() && ! $breeze_network_subsite_settings ) {
		$is_local = false;
	} elseif ( ! breeze_does_inherit_settings() ) {
		$inherit = false;
	}

	if ( ! is_multisite() || $is_local || ! $inherit ) {
		$option = get_option( 'breeze_' . $option_name );
	} else {
		$option = get_site_option( 'breeze_' . $option_name );
	}

	if ( empty( $option ) || ! is_array( $option ) ) {
		$option = array();
	}

	return $option;
}

/**
 * Update site options accounting for multisite.
 *
 * @param string $option_name
 * @param mixed $value
 * @param bool $is_local
 */
function breeze_update_option( $option_name, $value, $is_local = false ) {
	if ( is_network_admin() ) {
		$is_local = false;
	}

	if ( ! is_multisite() || $is_local ) {
		update_option( 'breeze_' . $option_name, $value );
	} else {
		update_site_option( 'breeze_' . $option_name, $value );
	}
}

/**
 * Check whether current site should inherit network-level settings.
 *
 * @return bool
 */
function breeze_does_inherit_settings() {
	global $breeze_network_subsite_settings;

	if ( ! is_multisite() || ( ! $breeze_network_subsite_settings && is_network_admin() ) ) {
		return false;
	}

	$inherit_option = get_option( 'breeze_inherit_settings' );

	return '0' !== $inherit_option;
}

/**
 * Check if plugin is activated network-wide in a multisite environment.
 *
 * @return bool
 */
function breeze_is_active_for_network() {
	return is_multisite() && is_plugin_active_for_network( 'breeze/breeze.php' );
}

function breeze_is_supported( $check ) {
	switch ( $check ) {
		case 'conditional_htaccess':
			$return = isset( $_SERVER['SERVER_SOFTWARE'] ) && stripos( $_SERVER['SERVER_SOFTWARE'], 'Apache/2.4' ) !== false;
			break;
	}

	return $return;
}

/**
 * If an array provided, the function will check all
 * array items to see if all of them are valid URLs.
 *
 * @param array $url_list
 * @param string $extension
 *
 * @return bool
 * @since 1.1.0
 *
 */
function breeze_validate_urls( $url_list = array() ) {
	if ( ! is_array( $url_list ) ) {
		return false;
	}

	$is_valid = true;
	foreach ( $url_list as $url ) {
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			$is_valid = false;
			if ( false === $is_valid ) {
				$is_valid = breeze_validate_url_via_regexp( $url );
			}

			if ( false === $is_valid ) {
				$is_valid = breeze_string_contains_exclude_regexp( $url );
			}
		}

		if ( false === $is_valid ) {
			break;
		}
	}

	return $is_valid;

}

function breeze_validate_the_right_extension( $url_list = array(), $extension = 'css' ) {
	if ( ! is_array( $url_list ) ) {
		return false;
	}

	$is_valid = true;
	foreach ( $url_list as $url ) {

		$is_regexp = breeze_string_contains_exclude_regexp( $url );

		if ( false === $is_regexp ) {
			$is_valid = breeze_validate_exclude_field_by_extension( $url, $extension );
		} else {
			$file_extension = breeze_get_file_extension_from_url( $url );

			if ( false !== $file_extension && strtolower( $extension ) !== $file_extension ) {
				$is_valid = false;
			}
		}

		if ( false === $is_valid ) {
			break;
		}
	}

	return $is_valid;
}

/**
 * Returns the extension for given file from url.
 *
 * @param string $url_given
 *
 * @return bool
 */
function breeze_get_file_extension_from_url( $url_given = '' ) {
	if ( empty( $url_given ) ) {
		return false;
	}

	$file_path = wp_parse_url( $url_given, PHP_URL_PATH );
	if ( ! empty( $file_path ) ) {
		$file_name = wp_basename( $file_path );
		if ( ! empty( $file_name ) ) {
			$bits = explode( '.', $file_name );
			if ( ! empty( $bits ) ) {
				$extension_id = count( $bits ) - 1;
				$extension    = strtolower( $bits[ $extension_id ] );
				$extension    = preg_replace( '/\s+/', ' ', $extension );
				if ( '*)' === $extension ) { // Exception when (.*) is the last statement instead of ending with an extension
					return false;
				}

				return $extension;
			}
		}
	}

	return false;
}

/**
 * Will search for given string in array values
 * if found, will result in an array with all entries found
 * if not found, an empty array will be resulted.
 *
 * @param string $needle
 * @param array $haystack
 *
 * @return array
 * @since 1.1.0
 *
 */
function breeze_is_string_in_array_values( $needle = '', $haystack = array() ) {
	if ( empty( $needle ) || empty( $haystack ) ) {
		return array();
	}
	$needle             = trim( $needle );
	$is_string_in_array = array_filter(
		$haystack,
		function ( $var ) use ( $needle ) {
			#return false;
			if ( breeze_string_contains_exclude_regexp( $var ) ) {
				return breeze_file_match_pattern( $needle, $var );
			} else {
				return strpos( $var, $needle ) !== false;
			}

		}
	);

	return $is_string_in_array;
}

/**
 * Used to check for regexp exclude pages
 *
 * @param string $needle
 * @param array $haystack
 *
 * @return array
 * @since 1.1.7
 *
 */
function breeze_check_for_exclude_values( $needle = '', $haystack = array() ) {
	if ( empty( $needle ) || empty( $haystack ) ) {
		return array();
	}
	$needle             = trim( $needle );
	$is_string_in_array = array_filter(
		$haystack,
		function ( $var ) use ( $needle ) {

			if ( breeze_string_contains_exclude_regexp( $var ) ) {
				return breeze_file_match_pattern( $needle, $var );
			} else {
				return false;
			}

		}
	);

	return $is_string_in_array;
}

/**
 * Will return true for Google fonts and other type of CDN link
 * that are missing the Scheme from the url
 *
 *
 * @param string $url_to_be_checked
 *
 * @return bool
 */
function breeze_validate_url_via_regexp( $url_to_be_checked = '' ) {
	if ( empty( $url_to_be_checked ) ) {
		return false;
	}
	$regex = '((http:|https:?)?\/\/)?([a-z0-9+!*(),;?&=.-]+(:[a-z0-9+!*(),;?&=.-]+)?@)?([a-z0-9\-\.]*)\.(([a-z]{2,4})|([0-9]{1,3}\.([0-9]{1,3})\.([0-9]{1,3})))(:[0-9]{2,5})?(\/([a-z0-9+%-]\.?)+)*\/?(\?[a-z+&$_.-][a-z0-9;:@&%=+/.-/,/:]*)?(#[a-z_.-][a-z0-9+$%_.-]*)?';

	preg_match( "~^$regex$~i", $url_to_be_checked, $matches_found );

	if ( empty( $matches_found ) ) {
		return false;
	}

	return true;
}


/**
 * Used in Breeze settings to validate if the URL corresponds to the
 * added input/textarea
 * Exclude CSS must contain only .css files
 * Exclude JS must contain only .js files
 *
 * @param $file_url
 * @param string $validate
 *
 * @return bool
 */
function breeze_validate_exclude_field_by_extension( $file_url, $validate = 'css' ) {
	if ( empty( $file_url ) ) {
		return true;
	}
	if ( empty( $validate ) ) {
		return false;
	}

	$valid      = true;
	$file_path  = wp_parse_url( $file_url, PHP_URL_PATH );
	$preg_match = preg_match( '#\.' . $validate . '$#', $file_path );
	if ( empty( $preg_match ) ) {
		$valid = false;
	}

	return $valid;

}


/**
 * Function used to determine if the excluded URL contains regexp
 *
 * @param $file_url
 * @param string $validate
 *
 * @return bool
 */
function breeze_string_contains_exclude_regexp( $file_url, $validate = '(.*)' ) {
	if ( empty( $file_url ) ) {
		return false;
	}
	if ( empty( $validate ) ) {
		return false;
	}

	$valid = false;

	if ( substr_count( $file_url, $validate ) !== 0 ) {
		$valid = true; // 0 or false
	}

	return $valid;
}

/**
 * Method will prepare the URLs escaped for preg_match
 * Will return the file_url matches the pattern.
 * empty array for false,
 * aray with data for true.
 *
 * @param $file_url
 * @param $pattern
 *
 * @return false|int
 */
function breeze_file_match_pattern( $file_url, $pattern ) {
	$remove_pattern   = str_replace( '(.*)', 'REG_EXP_ALL', $pattern );
	$prepared_pattern = preg_quote( $remove_pattern, '/' );
	$pattern          = str_replace( 'REG_EXP_ALL', '(.*)', $prepared_pattern );
	$result           = preg_match( '/' . $pattern . '/', $file_url );

	return $result;
}

/**
 * Will return true/false if the cache headers exist and
 * have values HIT or MISS.
 * HIT = Varnish is enabled and age is cached
 * MISS = Varnish is disabled or the cache has been purged.
 * This method will request only the current url homepage headers
 * and if the first time is a MISS, it will try again.
 *
 * @param int $retry how many retries count.
 * @param int $time_fresh current time to make a fresh connect.
 * @param bool $use_headers To use get_headers or cURL.
 *
 * @return bool
 */
function is_varnish_cache_started( $retry = 1, $time_fresh = 0, $use_headers = false ) {
	if ( isset( $_SERVER['HTTP_X_VARNISH'] ) && is_numeric( $_SERVER['HTTP_X_VARNISH'] ) ) {
		return true;
	}

	if ( empty( $time_fresh ) ) {
		$time_fresh = time();
	}

	// Code specific for Cloudways Server.
	if ( 1 === $retry ) {
		$check_local_server = is_varnish_layer_started();
		if ( true === $check_local_server ) {
			return true;
		}
	}

	$url_ping = trim( home_url() . '?breeze_check_cache_available=' . $time_fresh );

	if ( true === $use_headers ) {

		$ssl_verification = apply_filters( 'breeze_ssl_check_certificate', true );

		if ( ! is_bool( $ssl_verification ) ) {
			$ssl_verification = true;
		}

		if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) {
			$ssl_verification = false;
		}
		// Making sure the request is only for HEADER info without getting the content from the page
		$context_options = array(
			'http' => array(
				'method'          => 'HEAD',
				'follow_location' => 1,
			),
			'ssl'  => array(
				'verify_peer' => $ssl_verification,
			),
		);

		stream_context_set_default( $context_options );
		$headers = get_headers( $url_ping, 1 );

		if ( empty( $headers ) ) {
			$use_headers = false;
		} else {
			$headers = array_change_key_case( $headers, CASE_LOWER );
		}
	}

	if ( false === $use_headers ) {
		$headers = breeze_get_headers_via_curl( $url_ping );
	}

	if ( empty( $headers ) ) {
		return false;
	}

	if ( true === $headers ) {
		return true;
	}

	if ( ! isset( $headers['x-cache'] ) ) {
		if ( 1 === $retry ) {
			$retry ++;

			return is_varnish_cache_started( $retry, $time_fresh, $use_headers );
		}

		return false;
	} else {
		$cache_header = strtolower( trim( $headers['x-cache'] ) );

		// After the cache is cleared, the first time the headers will say that the cache is not used
		// After the first header requests, the cache headers are formed.
		// Checking the second time will give better results.
		if ( 1 === $retry ) {
			if ( substr_count( $cache_header, 'hit' ) > 0 ) {
				return true;
			} else {
				$retry ++;

				return is_varnish_cache_started( $retry, $time_fresh, $use_headers );
			}
		} else {

			if ( substr_count( $cache_header, 'hit' ) > 0 ) {
				return true;
			}

			return false;
		}
	}
}

/**
 * Fallback function to fetch headers.
 *
 * @param string $url_ping URL from where to get the headers.
 *
 * @return array|bool
 */
function breeze_get_headers_via_curl( $url_ping = '' ) {

	$ssl_verification = apply_filters( 'breeze_ssl_check_certificate', true );
	if ( ! is_bool( $ssl_verification ) ) {
		$ssl_verification = true;
	}

	if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) {
		$ssl_verification = false;
	}

	$connection = curl_init();
	$headers    = array();
	curl_setopt( $connection, CURLOPT_URL, $url_ping );
	curl_setopt( $connection, CURLOPT_NOBODY, true );
	curl_setopt( $connection, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $connection, CURLOPT_FOLLOWLOCATION, true ); // follow redirects
	curl_setopt( $connection, CURLOPT_SSL_VERIFYPEER, $ssl_verification );
	curl_setopt( $connection, CURLOPT_HEADER, true );// return just headers
	curl_setopt( $connection, CURLOPT_TIMEOUT, 1 );
	// this function is called by curl for each header received
	curl_setopt(
		$connection,
		CURLOPT_HEADERFUNCTION,
		function ( $curl, $header ) use ( &$headers ) {
			$len    = strlen( $header );
			$header = explode( ':', $header, 2 );
			if ( count( $header ) < 2 ) { // ignore invalid headers
				return $len;
			}

			$headers[ strtolower( trim( $header[0] ) ) ][] = trim( $header[1] );

			return $len;
		}
	);

	curl_exec( $connection );
	curl_close( $connection );

	// x-cacheable
	if ( isset( $headers['x-cacheable'] ) ) {
		$x_cacheable_value = array_pop( $headers['x-cacheable'] );
		if ( 'yes' === strtolower( $x_cacheable_value ) || 'short' === strtolower( $x_cacheable_value ) ) {
			return true;
		}
	}

	if ( isset( $headers['x-cache'] ) ) {
		$x_cache_value = array_pop( $headers['x-cache'] );

		return array( 'x-cache' => $x_cache_value );
	}

	return false;

}

/**
 * Determine if the Varnish server is up and running.
 *
 * CloudWays:
 * At server root level Varnish being disabled.
 * HTTP_X_VARNISH - does not exist or is NULL
 * HTTP_X_APPLICATION - contains varnishpass
 *
 * At Application level ( WP install ) - Varnish ON
 * At server level is ON
 * HTTP_X_VARNISH - has random numerical value
 * HTTP_X_APPLICATION - contains value different from varnishpass, usually application name.
 *
 * At Application level ( WP install ) - Varnish OFF
 * At server level is ON
 * HTTP_X_VARNISH - has random numerical value
 * HTTP_X_APPLICATION - contains value varnishpass
 *
 * @since 1.1.3
 */
function is_varnish_layer_started() {
	$data = $_SERVER;

	if ( ! isset( $data['HTTP_X_VARNISH'] ) ) {
		return false;
	}

	if ( isset( $data['HTTP_X_VARNISH'] ) && isset( $data['HTTP_X_APPLICATION'] ) ) {

		if ( 'varnishpass' === trim( $data['HTTP_X_APPLICATION'] ) ) {
			return false;
		} elseif ( 'bypass' === trim( $data['HTTP_X_APPLICATION'] ) ) {
			return false;
		} elseif ( is_null( $data['HTTP_X_APPLICATION'] ) ) {
			return false;
		}
	}

	if ( ! isset( $data['HTTP_X_APPLICATION'] ) ) {
		return false;
	}

	return true;
}

/**
 * Handles file writing.
 * Using fopen() si a lot faster than file_put_contents().
 *
 * @param string $file_path
 * @param string $content
 *
 * @return bool
 * @since 1.1.3
 */
function breeze_read_write_file( $file_path = '', $content = '' ) {
	if ( empty( $file_path ) ) {
		return false;
	}

	if ( ( $handler = @fopen( $file_path, 'w' ) ) !== false ) { // phpcs:ignore
		if ( ( @fwrite( $handler, $content ) ) !== false ) { // phpcs:ignore
			@fclose( $handler ); // phpcs:ignore
		}
	}

}


function breeze_lock_cache_process( $path = '' ) {
	$filename    = 'process.lock';
	$create_lock = fopen( $path . $filename, 'w' );
	if ( false === $create_lock ) {
		return false;
	}
	fclose( $create_lock );

	return true;
}

function breeze_is_process_locked( $path = '' ) {
	$filename = 'process.lock';
	if ( file_exists( $path . $filename ) ) {
		return true;
	}

	return false;
}

function breeze_unlock_process( $path = '' ) {
	$filename = 'process.lock';
	if ( file_exists( $path . $filename ) ) {
		@unlink( $path . $filename );

		return true;
	}

	return false;
}

function multisite_blog_id_config() {
	global $blog_id;

	$blog_id_requested = isset( $GLOBALS['breeze_config']['blog_id'] ) ? $GLOBALS['breeze_config']['blog_id'] : 0;
	if ( ! empty( $blog_id_requested ) ) {
		return $blog_id_requested;
	}

	if ( ! empty( $blog_id ) ) {

	}
}

/**
 * Purges the cache for a given URL.
 * Varnish cache and local cache.
 *
 * @param string $url The url for which to purge the cache.
 * @param false $purge_varnish If the check was already done for Varnish server On/OFF set to true.
 * @param bool $check_varnish If the check for Varnish was not done, set to true to check Varnish server status inside the function.
 *
 * @since 1.1.10
 */
function breeze_varnish_purge_cache( $url = '', $purge_varnish = false, $check_varnish = true ) {
	global $wp_filesystem;

	// Making sure the filesystem is loaded.
	if ( empty( $wp_filesystem ) ) {
		require_once( ABSPATH . '/wp-admin/includes/file.php' );
		WP_Filesystem();
	}

	// Clear the local cache using the product URL.
	if ( ! empty( $url ) && $wp_filesystem->exists( breeze_get_cache_base_path() . md5( $url ) ) ) {
		$wp_filesystem->rmdir( breeze_get_cache_base_path() . md5( $url ), true );
	}

	if ( false === $purge_varnish && true === $check_varnish ) {
		// Checks if the Varnish server is ON.
		$do_varnish_purge = is_varnish_cache_started();

		if ( false === $do_varnish_purge ) {
			return;
		}
	}

	if ( false === $purge_varnish && false === $check_varnish ) {
		return;
	}

	$parse_url = parse_url( $url );
	$pregex    = '';
	// Default method is URLPURGE to purge only one object, this method is specific to cloudways configuration
	$purge_method = 'URLPURGE';
	// Use PURGE method when purging all site
	if ( isset( $parse_url['query'] ) && ( 'breeze' === strtolower( $parse_url['query'] ) ) ) {
		// The regex is not needed as cloudways configuration purge all the cache of the domain when a PURGE is done
		$pregex       = '.*';
		$purge_method = 'PURGE';
	}
	// Determine the path
	$url_path = '';
	if ( isset( $parse_url['path'] ) ) {
		$url_path = $parse_url['path'];
	}
	// Determine the schema
	$schema = 'http://';
	if ( isset( $parse_url['scheme'] ) ) {
		$schema = $parse_url['scheme'] . '://';
	}
	// Determine the host
	$host = $parse_url['host'];

	$varnish_ip   = Breeze_Options_Reader::get_option_value( 'breeze-varnish-server-ip' );
	$varnish_host = isset( $varnish_ip ) ? $varnish_ip : '127.0.0.1';
	$purgeme      = $varnish_host . $url_path . $pregex;
	if ( ! empty( $parse_url['query'] ) && 'breeze' !== strtolower( $parse_url['query'] ) ) {
		$purgeme .= '?' . $parse_url['query'];
	}


	$ssl_verification = apply_filters( 'breeze_ssl_check_certificate', true );

	if ( ! is_bool( $ssl_verification ) ) {
		$ssl_verification = true;
	}

	if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) {
		$ssl_verification = false;
	}

	$request_args = array(
		'method'    => $purge_method,
		'headers'   => array(
			'Host'       => $host,
			'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36',
		),
		'sslverify' => $ssl_verification,
	);
	$response     = wp_remote_request( $schema . $purgeme, $request_args );
	if ( is_wp_error( $response ) || 200 !== (int) $response['response']['code'] ) {
		if ( 'https://' === $schema ) {
			$schema = 'http://';
		} else {
			$schema = 'https://';
		}
		wp_remote_request( $schema . $purgeme, $request_args );
	}
}

/**
 * Will ignore the files added into $minified_already array so that these files will not be minified twice.
 *
 * @param string $script_path local script path.
 *
 * @return bool
 * @since 1.1.9
 */
function breeze_libraries_already_minified( $script_path = '' ) {
	if ( empty( $script_path ) ) {
		return false;
	}

	$minified_already = array(
		'woocommerce-bookings/dist/frontend.js',
	);

	$library = explode( '/plugins/', $script_path );

	if ( empty( $library ) || ! isset( $library[1] ) ) {
		return false;
	}

	$library_path = $library[1];

	if ( in_array( $library_path, $minified_already ) ) {
		return true;
	}

	return false;

}

add_filter( 'breeze_js_ignore_minify', 'breeze_libraries_already_minified' );

/**
 * Will check if there are any differences between saved option and default.
 *
 * if returns false, the nno changes occurred.
 * If returns true, then there are differences.
 *
 * @param bool $is_network if it's called from multisite network.
 *
 * @return bool
 * @since 1.2.1
 */
function breeze_is_delayjs_changed( $is_network = false, $blog_id = 0, $root = false ) {
	if ( true === $is_network ) {
		$saved_options = get_site_option( 'breeze_advanced_settings' );
	} elseif ( true === $root ) {
		$saved_options = get_blog_option( $blog_id, 'breeze_advanced_settings' );
	} else {
		$saved_options = get_option( 'breeze_advanced_settings' );
	}


	if ( ! isset( $saved_options['breeze-delay-js-scripts'] ) ) {
		return true;
	}

	if ( empty( $saved_options['breeze-delay-js-scripts'] ) ) {
		return true;
	}

	$saved_options['breeze-delay-js-scripts'] = array_filter( $saved_options['breeze-delay-js-scripts'] );

	$default_values = array(
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

	$differences   = array_diff( $saved_options['breeze-delay-js-scripts'], $default_values );
	$differences_2 = array_diff( $default_values, $saved_options['breeze-delay-js-scripts'] );

	if ( empty( $differences ) && empty( $differences_2 ) ) {
		return false;
	}

	return true;
}

/**
 * The Page is AMP so don't minifiy stuff.
 * @return bool
 * @since 1.2.3
 */
function breeze_is_amp_page() {
	if ( function_exists( 'amp_is_request' ) && amp_is_request() ) {
		return true;
	}

	return false;
}


function breeze_migrate_old_settings( $is_sigle = true, $subsite_id = 0, $is_root = false ) {
	//If this is a single site.
	if ( true === $is_sigle ) {
		// if the option exists, then we do not need to do anything.
		// This option is not available to Breeze versions < 2.0.0.
		$new_option = breeze_get_option( 'file_settings', true );
		if ( ! empty( $new_option ) ) {
			return;
		}

		$get_current_basic    = breeze_get_option( 'basic_settings', true );
		$get_current_advanced = breeze_get_option( 'advanced_settings', true );
		$get_current_varnish  = breeze_get_option( 'varnish_cache', true );
		$get_current_cdn      = breeze_get_option( 'cdn_integration', true );
		$options              = array();
	}

	// if multisite then run code for sub-site.
	if ( false === $is_sigle && ! empty( $subsite_id ) ) {
		$subsite_id = absint( $subsite_id );
		// if the option exists, then we do not need to do anything.
		// This option is not available to Breeze versions < 2.0.0.
		$new_option = get_blog_option( $subsite_id, 'breeze_file_settings', array() );
		if ( ! empty( $new_option ) ) {
			return;
		}

		$get_current_basic    = get_blog_option( $subsite_id, 'breeze_basic_settings', array() );
		$get_current_advanced = get_blog_option( $subsite_id, 'breeze_advanced_settings', array() );
		$get_current_varnish  = get_blog_option( $subsite_id, 'breeze_varnish_cache', array() );
		$get_current_cdn      = get_blog_option( $subsite_id, 'breeze_cdn_integration', array() );
	}

	// if multisite and network level.
	if ( true === $is_root ) {
		$new_option = get_site_option( 'breeze_file_settings', array() );
		if ( ! empty( $new_option ) ) {
			return;
		}

		$get_current_basic    = get_site_option( 'breeze_basic_settings', array() );
		$get_current_advanced = get_site_option( 'breeze_advanced_settings', array() );
		$get_current_varnish  = get_site_option( 'breeze_varnish_cache', array() );
		$get_current_cdn      = get_site_option( 'breeze_cdn_integration', array() );
	}

	if ( ! empty( $get_current_basic ) ) {
		foreach ( $get_current_basic as $option_name => $value ) {
			$options[ $option_name ] = $value;
		}
	}

	if ( ! empty( $get_current_advanced ) ) {
		foreach ( $get_current_advanced as $option_name => $value ) {
			$options[ $option_name ] = $value;
		}
	}

	if ( ! empty( $get_current_varnish ) ) {
		foreach ( $get_current_varnish as $option_name => $value ) {
			$options[ $option_name ] = $value;
		}
	}

	if ( ! empty( $get_current_cdn ) ) {
		foreach ( $get_current_cdn as $option_name => $value ) {
			$options[ $option_name ] = $value;
		}
	}

	$basic = array(
		'breeze-active'           => ( isset( $options['breeze-active'] ) ? $options['breeze-active'] : '1' ),
		'breeze-cross-origin'     => ( isset( $options['breeze-cross-origin'] ) ? $options['breeze-cross-origin'] : '0' ),
		'breeze-disable-admin'    => ( isset( $options['breeze-disable-admin'] ) ? $options['breeze-disable-admin'] : array() ),
		'breeze-gzip-compression' => ( isset( $options['breeze-gzip-compression'] ) ? $options['breeze-gzip-compression'] : '1' ),
		'breeze-browser-cache'    => ( isset( $options['breeze-browser-cache'] ) ? $options['breeze-browser-cache'] : '1' ),
		'breeze-lazy-load'        => ( isset( $options['breeze-lazy-load'] ) ? $options['breeze-lazy-load'] : '0' ),
		'breeze-lazy-load-native' => ( isset( $options['breeze-lazy-load-native'] ) ? $options['breeze-lazy-load-native'] : '0' ),
		'breeze-desktop-cache'    => '1',
		'breeze-mobile-cache'     => '1',
		'breeze-display-clean'    => '1',
		'breeze-ttl'              => ( isset( $options['breeze-ttl'] ) ? $options['breeze-ttl'] : 1440 ),
	);

	$file = array(
		'breeze-minify-html'        => ( isset( $options['breeze-minify-html'] ) ? $options['breeze-minify-html'] : '0' ),
		// --
		'breeze-minify-css'         => ( isset( $options['breeze-minify-css'] ) ? $options['breeze-minify-css'] : '0' ),
		'breeze-font-display-swap'  => ( isset( $options['breeze-font-display-swap'] ) ? $options['breeze-font-display-swap'] : '0' ),
		'breeze-group-css'          => ( isset( $options['breeze-group-css'] ) ? $options['breeze-group-css'] : '0' ),
		'breeze-exclude-css'        => ( isset( $options['breeze-exclude-css'] ) ? $options['breeze-exclude-css'] : array() ),
		'breeze-include-inline-css' => ( isset( $options['breeze-include-inline-css'] ) ? $options['breeze-include-inline-css'] : '0' ),
		// --
		'breeze-minify-js'          => ( isset( $options['breeze-minify-js'] ) ? $options['breeze-minify-js'] : '0' ),
		'breeze-group-js'           => ( isset( $options['breeze-group-js'] ) ? $options['breeze-group-js'] : '0' ),
		'breeze-include-inline-js'  => ( isset( $options['breeze-include-inline-js'] ) ? $options['breeze-include-inline-js'] : '0' ),
		'breeze-exclude-js'         => ( isset( $options['breeze-exclude-js'] ) ? $options['breeze-exclude-js'] : array() ),
		'breeze-move-to-footer-js'  => ( isset( $options['breeze-move-to-footer-js'] ) ? $options['breeze-move-to-footer-js'] : array() ),
		'breeze-defer-js'           => ( isset( $options['breeze-defer-js'] ) ? $options['breeze-defer-js'] : array() ),
		'breeze-delay-all-js'       => ( isset( $options['breeze-delay-all-js'] ) ? $options['breeze-delay-all-js'] : '0' ),
		'breeze-enable-js-delay'    => ( isset( $options['breeze-enable-js-delay'] ) ? $options['breeze-enable-js-delay'] : '0' ),
		'breeze-delay-js-scripts'   => ( isset( $options['breeze-delay-js-scripts'] ) ? $options['breeze-delay-js-scripts'] : array() ),
		'no-breeze-no-delay-js'     => ( isset( $options['no-breeze-no-delay-js'] ) ? $options['no-breeze-no-delay-js'] : array() ),

	);

	$preload = array(
		'breeze-preload-fonts' => ( isset( $options['breeze-preload-fonts'] ) ? $options['breeze-preload-fonts'] : array() ),
		'breeze-preload-links' => ( isset( $options['breeze-preload-links'] ) ? $options['breeze-preload-links'] : '0' ),
	);

	$advanced = array(
		'breeze-exclude-urls'  => ( isset( $options['breeze-exclude-urls'] ) ? $options['breeze-exclude-urls'] : array() ),
		'cached-query-strings' => ( isset( $options['cached-query-strings'] ) ? $options['cached-query-strings'] : array() ),
		'breeze-wp-emoji'      => ( isset( $options['breeze-wp-emoji'] ) ? $options['breeze-wp-emoji'] : '0' ),
	);

	$wp_content = substr( WP_CONTENT_DIR, strlen( ABSPATH ) );
	$cdn        = array(
		'cdn-active'          => ( isset( $options['cdn-active'] ) ? $options['cdn-active'] : '0' ),
		'cdn-relative-path'   => ( isset( $options['cdn-relative-path'] ) ? $options['cdn-relative-path'] : '1' ),
		'cdn-url'             => ( isset( $options['cdn-url'] ) ? $options['cdn-url'] : '' ),
		'cdn-content'         => ( isset( $options['cdn-content'] ) ? $options['cdn-content'] : array( 'wp-includes', $wp_content ) ),
		'cdn-exclude-content' => ( isset( $options['cdn-exclude-content'] ) ? $options['cdn-exclude-content'] : array( '.php' ) ),
	);

	$varnish = array(
		'auto-purge-varnish'       => ( isset( $options['auto-purge-varnish'] ) ? $options['auto-purge-varnish'] : '1' ),
		'breeze-varnish-server-ip' => ( isset( $options['breeze-varnish-server-ip'] ) ? $options['breeze-varnish-server-ip'] : '127.0.0.1' ),
	);

	if ( true === $is_sigle ) {
		breeze_update_option( 'basic_settings', $basic, true );
		breeze_update_option( 'file_settings', $file, true );
		breeze_update_option( 'preload_settings', $preload, true );
		breeze_update_option( 'advanced_settings', $advanced, true );
		breeze_update_option( 'cdn_integration', $cdn, true );
		breeze_update_option( 'varnish_cache', $varnish, true );
	}

	if ( false === $is_sigle && ! empty( $subsite_id ) ) {
		update_blog_option( $subsite_id, 'breeze_basic_settings', $basic );
		update_blog_option( $subsite_id, 'breeze_file_settings', $file );
		update_blog_option( $subsite_id, 'breeze_preload_settings', $preload );
		update_blog_option( $subsite_id, 'breeze_advanced_settings', $advanced );
		update_blog_option( $subsite_id, 'breeze_cdn_integration', $cdn );
		update_blog_option( $subsite_id, 'breeze_varnish_cache', $varnish );
	}

	if ( true === $is_root ) {
		update_site_option( 'breeze_basic_settings', $basic );
		update_site_option( 'breeze_file_settings', $file );
		update_site_option( 'breeze_preload_settings', $preload );
		update_site_option( 'breeze_advanced_settings', $advanced );
		update_site_option( 'breeze_cdn_integration', $cdn );
		update_site_option( 'breeze_varnish_cache', $varnish );
	}
}

function breeze_rtrim_urls( $url ) {
	if ( empty( $url ) ) {
		$url = '';
	}

	return rtrim( $url, '/' );
}

/**
 * Check the CDN url to see if it's safe to use.
 *
 * @param $cdn_url
 *
 * @return false|string
 * @since 2.0.11
 */
function breeze_static_check_cdn_url( $cdn_url ) {
	if ( empty( trim( $cdn_url ) ) ) {
		return false;
	}

	$breeze_user_agent = 'breeze-cdn-check-help-user';

	$verify_host      = 2;
	$ssl_verification = apply_filters( 'breeze_ssl_check_certificate', true );
	if ( ! is_bool( $ssl_verification ) ) {
		$ssl_verification = true;
	}

	if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) {
		$ssl_verification = false;
		$verify_host      = 0;
	}


	$cdn_url = ltrim( $cdn_url, 'https:' );
	$cdn_url = 'https:' . $cdn_url;

	if ( false === filter_var( $cdn_url, FILTER_VALIDATE_URL ) ) {
		return false;
	}

	$connection = curl_init( 'https://sitecheck.sucuri.net/api/v3/?scan=' . $cdn_url );
	curl_setopt( $connection, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $connection, CURLOPT_SSL_VERIFYHOST, $verify_host );
	curl_setopt( $connection, CURLOPT_SSL_VERIFYPEER, $ssl_verification );
	curl_setopt( $connection, CURLOPT_USERAGENT, $breeze_user_agent );
	curl_setopt( $connection, CURLOPT_REFERER, home_url() );

	/**
	 * Accept up to 3 maximum redirects before cutting the connection.
	 */
	curl_setopt( $connection, CURLOPT_MAXREDIRS, 3 );
	curl_setopt( $connection, CURLOPT_FOLLOWLOCATION, true );
	$the_json = curl_exec( $connection );
	curl_close( $connection );

	$is_json = json_decode( $the_json, true );
	if ( $is_json === null && json_last_error() !== JSON_ERROR_NONE ) {
		// incorrect data show error message
		$is_safe = false;
	} else {
		// decoded with success
		$is_safe = false;
		if ( isset( $is_json['warnings'], $is_json['warnings']['security'], $is_json['warnings']['security']['malware'] ) ) {
			$is_safe = 'warning';

		}
	}

	return $is_safe;
}