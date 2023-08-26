<?php
/**
 * @copyright 2017  Cloudways  https://www.cloudways.com
 *
 *  Original development of this plugin by JoomUnited https://www.joomunited.com/
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
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Breeze_PurgeCache {

	public function set_action() {
		add_action( 'pre_post_update', array( $this, 'purge_post_on_update' ), 10, 1 );
		add_action( 'save_post', array( $this, 'purge_post_on_update' ), 10, 1 );
		add_action( 'save_post', array( $this, 'purge_post_on_update_content' ), 9, 3 );
		add_action( 'wp_trash_post', array( $this, 'purge_post_on_update' ), 10, 1 );
		add_action( 'wp_trash_post', array( $this, 'purge_post_on_trash' ), 9, 1 );
		add_action( 'comment_post', array( $this, 'purge_post_on_new_comment' ), 10, 3 );
		add_action( 'wp_set_comment_status', array( $this, 'purge_post_on_comment_status_change' ), 10, 2 );
		add_action( 'spammed_comment', array( $this, 'purge_post_on_comment_status_change' ), 10, 2 );
		add_action( 'trashed_comment', array( $this, 'purge_post_on_comment_status_change' ), 10, 2 );
		add_action( 'edit_comment', array( $this, 'purge_post_on_comment_status_change' ), 10, 2 );
		add_action( 'set_comment_cookies', array( $this, 'set_comment_cookie_exceptions' ), 10, 2 );

		add_action( 'switch_theme', array( &$this, 'clear_local_cache_on_switch' ), 9, 3 );
		add_action( 'customize_save_after', array( &$this, 'clear_customizer_cache' ), 11, 1 );
	}

	/**
	 * When customizer settings are saved ( Publish button is clicked ), clear all cache.
	 *
	 * @param $element
	 *
	 * @return void
	 */
	public function clear_customizer_cache( $element ) {

		do_action( 'breeze_clear_all_cache' );
	}

	/**
	 * Clear local cache on theme switch.
	 *
	 * @param $new_name
	 * @param $new_theme
	 * @param $old_theme
	 *
	 * @return void
	 */
	public function clear_local_cache_on_switch( $new_name, $new_theme, $old_theme ) {
		//delete minify
		Breeze_MinificationCache::clear_minification();
		//clear normal cache
		Breeze_PurgeCache::breeze_cache_flush();
		//do_action( 'breeze_clear_all_cache' );
	}

	/**
	 * When user posts a comment, set a cookie so we don't show them page cache
	 *
	 * @param WP_Comment $comment
	 * @param WP_User $user
	 *
	 * @since  1.3
	 */
	public function set_comment_cookie_exceptions( $comment, $user ) {
		// File based caching only
		if ( ! empty( Breeze_Options_Reader::get_option_value( 'breeze-active' ) ) ) {

			$post_id = $comment->comment_post_ID;

			setcookie( 'breeze_commented_posts[' . $post_id . ']', parse_url( get_permalink( $post_id ), PHP_URL_PATH ), ( time() + HOUR_IN_SECONDS * 24 * 7 ) );
		}
	}

	//    Automatically purge all file based page cache on post changes
	public function purge_post_on_update( $post_id ) {
		$post_type = get_post_type( $post_id );

		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || 'revision' === $post_type ) {
			return;
		} elseif ( ! current_user_can( 'edit_post', $post_id ) && ( ! defined( 'DOING_CRON' ) || ! DOING_CRON ) ) {
			return;
		}

		$do_cache_reset = true;
		if ( 'tribe_events' === $post_type ) {
			$do_cache_reset = false;
		}
		$clear_wp_cache = true;
		if ( defined( 'RedisCachePro\Version' ) ) {
			$clear_wp_cache = false;
		}

		if ( did_action( 'edit_post' ) ) {
			return;
		}

		// File based caching only
		if ( ! empty( Breeze_Options_Reader::get_option_value( 'breeze-active' ) ) ) {
			self::breeze_cache_flush( $do_cache_reset, $clear_wp_cache );
		}
	}

	/**
	 * Purge Cloudflare data on post/page/cpt update.
	 *
	 * @param int $post_id Post ID.
	 * @param WP_Post $post Post object.
	 * @param bool $update Whether this is an existing post being updated.
	 *
	 * @return void
	 * @access public
	 * @since 2.0.15
	 */
	public function purge_post_on_update_content( int $post_id, WP_Post $post, bool $update ) {
		if ( true === $update ) {

			$post_type = get_post_type( $post_id );

			if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || 'revision' === $post_type ) {
				return;
			} elseif ( ! current_user_can( 'edit_post', $post_id ) && ( ! defined( 'DOING_CRON' ) || ! DOING_CRON ) ) {
				return;
			}

			$this->purge_cloudflare_cache( $post_id );

		}

	}

	/**
	 * Clear cloudflare cache on post/page/cpt delete action.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return void
	 */
	public function purge_post_on_trash( int $post_id ) {
		$this->purge_cloudflare_cache( $post_id );
	}

	private function purge_cloudflare_cache( $post_id ) {

		if ( false === get_permalink( $post_id ) ) {
			return;
		}
		// Reset CloudFlare cache.
		$get_permalink = get_permalink( $post_id );
		// On delete action, the permalink has "__trashed" added to the permalink.
		// We need to remove that.
		$get_permalink  = str_replace( '__trashed', '', $get_permalink );
		$list_of_urls   = array();
		$list_of_urls[] = $get_permalink;

		$noarchive_post_type = array( 'post', 'page' );
		$this_post_status    = get_post_status( $post_id );
		$this_post_type      = get_post_type( $post_id );
		$rest_api_route      = 'wp/v2';
		$valid_post_status   = array( 'publish', 'private', 'trash' );

		$post_type_object = get_post_type_object( $this_post_type );
		if ( isset( $post_type_object->rest_base ) && ! empty( $post_type_object->rest_base ) ) {
			$rest_permalink = get_rest_url() . $rest_api_route . '/' . $post_type_object->rest_base . '/' . $post_id . '/';
		} elseif ( 'post' === $this_post_type ) {
			$rest_permalink = get_rest_url() . $rest_api_route . '/posts/' . $post_id . '/';
		} elseif ( 'page' === $this_post_type ) {
			$rest_permalink = get_rest_url() . $rest_api_route . '/views/' . $post_id . '/';
		}
		if ( isset( $rest_permalink ) ) {
			$list_of_urls[] = $rest_permalink;
		}

		// Add in AMP permalink if Automattic's AMP is installed
		if ( function_exists( 'amp_get_permalink' ) ) {
			$list_of_urls[] = amp_get_permalink( $post_id );
			// Regular AMP url for posts
			$list_of_urls[] = get_permalink( $post_id ) . 'amp/';
		}
		if ( 'trash' === $this_post_status ) {
			$list_of_urls[] = $get_permalink . 'feed/';
		}

		$author_id = get_post_field( 'post_author', $post_id );
		array_push(
			$list_of_urls,
			get_author_posts_url( $author_id ),
			get_author_feed_link( $author_id ),
			get_rest_url() . $rest_api_route . '/users/' . $author_id . '/'
		);

		$categories = get_the_category( $post_id );
		if ( $categories ) {
			foreach ( $categories as $cat ) {
				array_push(
					$list_of_urls,
					get_category_link( $cat->term_id ),
					get_rest_url() . $rest_api_route . '/categories/' . $cat->term_id . '/'
				);
			}
		}

		$tags = get_the_tags( $post_id );
		if ( $tags ) {
			foreach ( $tags as $tag ) {
				array_push(
					$list_of_urls,
					get_tag_link( $tag->term_id ),
					get_rest_url() . $rest_api_route . '/tags/' . $tag->term_id . '/'
				);
			}
		}

		// Archives and their feeds
		if ( $this_post_type && ! in_array( $this_post_type, $noarchive_post_type, true ) ) {
			$get_archive_link      = get_post_type_archive_link( get_post_type( $post_id ) );
			$get_archive_feed_link = get_post_type_archive_feed_link( get_post_type( $post_id ) );
			if ( ! empty( $get_archive_link ) ) {
				$list_of_urls[] = $get_archive_link;
			}
			if ( ! empty( $get_archive_feed_link ) ) {
				$list_of_urls[] = $get_archive_feed_link;
			}
		}

		// Feeds
		array_push(
			$list_of_urls,
			get_bloginfo_rss( 'rdf_url' ),
			get_bloginfo_rss( 'rss_url' ),
			get_bloginfo_rss( 'rss2_url' ),
			get_bloginfo_rss( 'atom_url' ),
			get_bloginfo_rss( 'comments_rss2_url' ),
			get_post_comments_feed_link( $post_id )
		);
		// Home Pages and (if used) posts page
		array_push(
			$list_of_urls,
			get_rest_url(),
			home_url() . '/'
		);
		if ( 'page' === get_option( 'show_on_front' ) ) {
			// Ensure we have a page_for_posts setting to avoid empty URL
			if ( get_option( 'page_for_posts' ) ) {
				$list_of_urls[] = get_permalink( get_option( 'page_for_posts' ) );
			}
		}

		Breeze_CloudFlare_Helper::purge_cloudflare_cache_urls( $list_of_urls );
	}

	public function purge_post_on_new_comment( $comment_ID, $approved, $commentdata ) {
		if ( empty( $approved ) ) {
			return;
		}
		// File based caching only
		if ( ! empty( Breeze_Options_Reader::get_option_value( 'breeze-active' ) ) ) {
			$post_id = $commentdata['comment_post_ID'];

			Breeze_CloudFlare_Helper::purge_cloudflare_cache_urls( array( get_permalink( $post_id ) ) );

			global $wp_filesystem;

			if ( empty( $wp_filesystem ) ) {
				require_once( ABSPATH . '/wp-admin/includes/file.php' );
				WP_Filesystem();
			}

			$url_path = get_permalink( $post_id );
			if ( $wp_filesystem->exists( breeze_get_cache_base_path() . md5( $url_path ) ) ) {
				$wp_filesystem->rmdir( breeze_get_cache_base_path() . md5( $url_path ), true );
			}
		}
	}

	//            if a comments status changes, purge it's parent posts cache
	public function purge_post_on_comment_status_change( $comment_ID, $comment_status ) {
		// File based caching only
		if ( ! empty( Breeze_Options_Reader::get_option_value( 'breeze-active' ) ) ) {
			$comment = get_comment( $comment_ID );
			if ( ! empty( $comment ) ) {
				$post_id = $comment->comment_post_ID;

				global $wp_filesystem;

				WP_Filesystem();

				$url_path = get_permalink( $post_id );

				Breeze_CloudFlare_Helper::purge_cloudflare_cache_urls( array( $url_path ) );

				if ( $wp_filesystem->exists( breeze_get_cache_base_path() . md5( $url_path ) ) ) {
					$wp_filesystem->rmdir( breeze_get_cache_base_path() . md5( $url_path ), true );
				}
			}
		}
	}

	/**
	 * Clear Breeze & Wordpress Cache
	 *
	 * @param $flush_cache
	 * @param $clear_ocp
	 *
	 * @return void
	 */
	public static function breeze_cache_flush( $flush_cache = true, $clear_ocp = true ) {
		global $wp_filesystem, $post;
		if ( true === Breeze_CloudFlare_Helper::is_log_enabled() ) {
			error_log( '######### PURGE LOCAL CACHE HTML ###: ' . var_export( 'true', true ) );
		}
		require_once( ABSPATH . 'wp-admin/includes/file.php' );

		WP_Filesystem();

		$cache_path = breeze_get_cache_base_path( is_network_admin() );
		$wp_filesystem->rmdir( untrailingslashit( $cache_path ), true );

		if ( true === $flush_cache && ! empty( $post ) ) {
			$post_type = get_post_type( $post->ID );

			$flush_cache = true;
			$ignore_object_cache = array(
				'tribe_events',
				'shop_order',
			);
			if ( in_array( $post_type, $ignore_object_cache ) ) {
				$flush_cache = false;
			}
		}

		if ( true === $flush_cache && isset( $_GET['post_type'] ) && 'tribe_events' === $_GET['post_type'] ) {
			$flush_cache = false;
		}

		if ( function_exists( 'wp_cache_flush' ) && true === $flush_cache && true === $clear_ocp  ) {
			if ( true === Breeze_CloudFlare_Helper::is_log_enabled() ) {
				error_log( '######### PURGE OBJECT CACHE ###: ' . var_export( 'true', true ) );
			}
			#if ( ! defined( 'RedisCachePro\Version' ) && ! defined( 'WP_REDIS_VERSION' ) ) {
				wp_cache_flush();
			#}

		}
	}

	//delete file for clean up

	public function clean_up() {

		global $wp_filesystem;
		$file = untrailingslashit( WP_CONTENT_DIR ) . '/advanced-cache.php';

		$ret = true;

		if ( ! $wp_filesystem->delete( $file ) ) {
			$ret = false;
		}

		$folder = untrailingslashit( breeze_get_cache_base_path() );

		if ( ! $wp_filesystem->delete( $folder, true ) ) {
			$ret = false;
		}

		return $ret;
	}

	/**
	 * Return an instance of the current class, create one if it doesn't exist
	 * @return object
	 * @since  1.0
	 */
	public static function factory() {

		static $instance;

		if ( ! $instance ) {
			$instance = new self();
			$instance->set_action();
		}

		return $instance;
	}


	public static function __flush_object_cache() {
		set_as_network_screen();

		if ( is_network_admin() ) {
			// in case we need to add something specific for network.
			return wp_cache_flush();
		}

		return wp_cache_flush();
	}

}

$breeze_basic_settings = Breeze_Options_Reader::get_option_value( 'breeze-active' );

if ( isset( $breeze_basic_settings ) && $breeze_basic_settings ) {
	Breeze_PurgeCache::factory();
}
