<?php
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

class Breeze_Lazy_Load {
	/**
	 * Whether Lazy load is active.
	 * @var false|mixed
	 * @since 1.2.0
	 * @access private
	 */
	private $lazy_load_enabled = false;

	/**
	 * Must use native Lazy Load.
	 * @var false|mixed
	 * @since 1.2.0
	 * @access private
	 */
	private $lazy_load_native = false;

	/**
	 * The page HTML content.
	 *
	 * @var string
	 * @since 1.2.0
	 * @access private
	 */
	private $content = '';

	/**
	 * Exclude images that have these attributes from processing.
	 *
	 * @since 1.2.0
	 * @var string[]
	 */
	private $exclude_if_atts = array();

	/**
	 * Breeze_Lazy_Load constructor.
	 *
	 * @param false $is_enabled If the base Lazy Load is enabled.
	 * @param string $content the HTML content of the page.
	 * @param false $is_native Whether to use native lazy load or Javascript based.
	 *
	 * @access public
	 * @since 1.2.0
	 */
	function __construct( $content = '', $is_enabled = false, $is_native = false ) {
		$this->lazy_load_enabled = $is_enabled;
		$this->lazy_load_native  = $is_native;
		$this->content           = $content;

		$this->exclude_if_atts = apply_filters(
			'breeze_excluded_attributes',
			array(
				'data-src',
				'data-no-lazy',
				'data-lazy-original',
				'data-lazy-src',
				'data-lazysrc',
				'data-lazyload',
				'data-bgposition',
				'data-envira-src',
				'fullurl',
				'lazy-slider-img',
				'data-srcset',
				'data-spai',
			)
		);

	}

	/**
	 * Apply lazy load library option.
	 *
	 * @return false|string
	 * @access public
	 * @since 1.2.0
	 */
	public function apply_lazy_load_feature() {
		$content = $this->content;

		if ( defined( 'REST_REQUEST' ) || is_feed() || is_admin() || is_comment_feed() || is_preview() || is_robots() || is_trackback() ) {
			return $content;
		}

		if ( false === $this->lazy_load_enabled ) {
			return $content;
		}

		if ( '' === trim( $content ) ) {
			return $content;
		}

		// If this option is set to true then loading="lazy" attribute will be use instead.
		// The native lazy load is not yet supported by all browsers. ( As of February 2021, 73% of browsers support lazy loading. )
		$use_native = apply_filters( 'breeze_use_native_lazy_load', $this->lazy_load_native );

		$content = mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' );


		/**
		 * Fetch all images
		 */
		preg_match_all( '/<img[^>]+>/i', $content, $img_matches );

		// Check if images available
		if ( ! empty( $img_matches[0] ) ) {

			// Check if required native ll
				if ( true === $use_native ) {

				foreach ( $img_matches[0] as $img_match ) {

					// If we can't find loading attr, we append it
					if ( ! preg_match( '/loading=[\'"]\s*lazy\s*[\'"]/i', $img_match ) ) {
						// Forming the new string
						$img_match_new = preg_replace( '/<img\s/i', '<img loading="lazy" ', $img_match, 1 );
						// Rewriting the old string in content
						$content = str_replace( $img_match, $img_match_new, $content );
					}
					}
				} else {

				// Non-native behavior
				foreach ( $img_matches[0] as $img_match ) {

					// Check if the image is to be ignored.
					if ( is_array( $this->exclude_if_atts ) && ! empty( $this->exclude_if_atts ) ) {
						$exclude_it = false;
						foreach ( $this->exclude_if_atts as $ex_attr ) {
							preg_match( '/' . $ex_attr . '=(?:"|\')(.+?)(?:"|\')/', $img_match, $attr_value );
							if ( ! empty( $attr_value[1] ) ) {
								$exclude_it = true;
							}
						}

						if ( true === $exclude_it ) {
							continue;
						}
					}
					// Get the image URL
					preg_match( '/src=(?:"|\')(.+?)(?:"|\')/', $img_match, $src_value );
					$current_src = ! empty( $src_value[1] ) ? $src_value[1] : '';
					if ( true !== $this->excluded_images( $current_src ) ) {
						// Add lazy-load data attribute.
						$img_match_new = preg_replace( '/(<img\s+)/', '$1data-breeze="' . $current_src . '" ', $img_match );

						// Remove the current image source.
						$img_match_new = preg_replace( '/(<img.+)(src=(?:"|\').+?(?:"|\'))(.+?>)/', '$1$3', $img_match_new );

						preg_match( '/width=(?:"|\')(.+?)(?:"|\')/', $img_match, $width_value );
						preg_match( '/height=(?:"|\')(.+?)(?:"|\')/', $img_match, $height_value );
						$get_width  = ! empty( $width_value[1] ) ? $width_value[1] : '';
						$get_height = ! empty( $height_value[1] ) ? $height_value[1] : '';

						$placeholder = $this->generate_simple_placeholder( $get_width, $get_height );

						// Add placeholder image as source
						$img_match_new = preg_replace( '/(<img\s+)/', '$1src="' . $placeholder . '" ', $img_match_new );

						// Fetch the current image CSS classes.
						preg_match( '/class=(?:"|\')(.+?)(?:"|\')/', $img_match_new, $class_value );
						$current_classes = ! empty( $class_value[1] ) ? $class_value[1] : '';

						// Append breeze lazy-load CSS class.
						if ( empty( trim( $current_classes ) ) ) {
							$current_classes = 'br-lazy';
						} else {
							$current_classes .= ' br-lazy';
						}

						$img_match_new = preg_replace( '/(<img.+)(class=(?:"|\').+?(?:"|\'))(.+?>)/', '$1$3', $img_match_new );
						// Add lazy-load CSS class.
						$img_match_new = preg_replace( '/(<img\s+)/', '$1class="' . $current_classes . '" ', $img_match_new );

						// handle SRCSET and SIZES attributes.
						preg_match( '/srcset=(?:"|\')(.+?)(?:"|\')/', $img_match_new, $srcset_value );
						preg_match( '/sizes=(?:"|\')(.+?)(?:"|\')/', $img_match_new, $sizes_value );
						$srcset = ! empty( $srcset_value[1] ) ? $srcset_value[1] : '';
						$sizes  = ! empty( $sizes_value[1] ) ? $sizes_value[1] : '';

						if ( ! empty( $srcset ) ) {
							$img_match_new = preg_replace( '/srcset=/i', 'data-brsrcset=', $img_match_new );
						}

						if ( ! empty( $sizes ) ) {
							$img_match_new = preg_replace( '/sizes=/i', 'data-brsizes=', $img_match_new );
						}

						$content = str_replace( $img_match, $img_match_new, $content );


					}
				}
			}
		}


		$apply_to_iframes = Breeze_Options_Reader::get_option_value( 'breeze-lazy-load-iframes' );
		$apply_to_iframes = apply_filters( 'breeze_enable_lazy_load_iframes', $apply_to_iframes );

		if ( true === filter_var( $apply_to_iframes, FILTER_VALIDATE_BOOLEAN ) ) {
				$allowed_iframes_url = apply_filters( 'breeze_iframe_lazy_load_list', array(
					'youtube.com',
					'dailymotion.com/embed/video',
					'facebook.com/plugins/video.php',
					'player.vimeo.com',
					'fast.wistia.net/embed/',
					'players.brightcove.net',
					's3.amazonaws.com',
					'cincopa.com/media',
					'twitch.tv',
					'bitchute.com',
					'media.myspace.com/play/video',
					'tiktok.com/embed',
				) );

			// Match and process iframes.
			preg_match_all( '/<iframe.*<\/iframe>/isU', $content, $iframe_matches );

			foreach ( $iframe_matches[0] as $iframe_tag ) {
				$src = preg_replace( '/^.*src=\"([^\"]+)\".*$/isU', '$1', $iframe_tag );

				$allowed_url = false;
				foreach ( $allowed_iframes_url as $iframe_url ) {
					if ( false !== strpos( $src, $iframe_url ) ) {
						$allowed_url = true;
						break;
					}
				}

				if ( true === $allowed_url ) {
					// Video Link
					$video_link = explode( '/', $src );
					$video_id   = end( $video_link );

					// Get classes
					$current_classes = $this->format_tag_ll_classes( $iframe_tag );

					// Forming iframe tag
					$iframe_tag_new = preg_replace( '/<iframe/isU', '<iframe data-video-id="' . $video_id . '" class="' . $current_classes . '" data-breeze="' . $src . '"', $iframe_tag );
					$iframe_tag_new = preg_replace( '/src=\"([^\"]+)\"/isU', '', $iframe_tag_new );
					$content        = str_replace( $iframe_tag, $iframe_tag_new, $content );
				}
			}

			// Match and process videos.
			preg_match_all( '/<video.*<\/video>/isU', $content, $video_matches );

			foreach ( $video_matches[0] as $video_tag ) {
				$src             = preg_replace( '/^.*src=\"([^\"]+)\".*$/isU', '$1', $video_tag );
				$current_classes = $this->format_tag_ll_classes( $video_tag );
				$video_tag_new   = preg_replace( '/<video/isU', '<video class="' . $current_classes . '" data-breeze="' . $src . '"', $video_tag );
				$video_tag_new   = preg_replace( '/src=\"([^\"]+)\"/isU', '', $video_tag_new );
				$content         = str_replace( $video_tag, $video_tag_new, $content );
			}
					}


		return $content;

	}

	/**
	 * Extract classes for lazy load and returned clean class string with br-lazy appended
	 *
	 * @param $tag
	 *
	 * @return string
	 */
	private function format_tag_ll_classes( $tag ) {
		preg_match( '/class=(?:"|\')(.+?)(?:"|\')/', $tag, $class_value );
		$current_classes = ! empty( $class_value[1] ) ? $class_value[1] : '';

		// Append breeze lazy-load CSS class.
		if ( empty( trim( $current_classes ) ) ) {
			$current_classes = 'br-lazy';
		} else {
					$current_classes .= 'br-lazy';
		}

		return $current_classes;
	}

	/**
	 * We need to exclude some images with very specific functionality.
	 * Example of excluded: Captcha, WooCommerce placeholder image.
	 *
	 * @param string $image_url The image full URL path.
	 *
	 * @return bool
	 * @since 1.2.0
	 * @access private
	 */
	private function excluded_images( $image_url = '' ) {
		$excluded_images_by_url = apply_filters(
			'breeze_excluded_images_url',
			array(
				'wpcf7_captcha/', // Contact Form 7 - Really Simple CAPTCHA.
				'woocommerce/assets/images/placeholder.png',
			)
		);

		if ( ! empty( $excluded_images_by_url ) ) {
			foreach ( $excluded_images_by_url as $partial_path ) {
				if ( false !== strpos( $image_url, $partial_path ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Generate a simple svg placeholder image.
	 *
	 * @param int $width Original image width.
	 * @param int $height Original image height.
	 *
	 * @return string
	 * @access private
	 * @since 1.2.0
	 */
	private function generate_simple_placeholder( $width = 0, $height = 0 ) {
		if ( ! is_numeric( $width ) ) {
			$width = 0;
		}

		if ( ! is_numeric( $height ) ) {
			$height = 0;
		}

		if ( ! empty( $width ) ) {
			$width = absint( $width );
		}

		if ( ! empty( $height ) ) {
			$height = absint( $height );
		}

		return "data:image/svg+xml;utf8,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20viewBox='0%200%20{$width}%20{$height}'%3E%3C/svg%3E";
	}
}
