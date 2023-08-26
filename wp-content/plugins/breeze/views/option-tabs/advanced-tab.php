<?php
/**
 * Basic tab
 */

if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

set_as_network_screen();

$is_custom = false;
if ( ( ! defined( 'WP_NETWORK_ADMIN' ) || ( defined( 'WP_NETWORK_ADMIN' ) && false === WP_NETWORK_ADMIN ) ) && is_multisite() ) {
	$get_inherit = get_option( 'breeze_inherit_settings', '1' );
	$is_custom   = filter_var( $get_inherit, FILTER_VALIDATE_BOOLEAN );
}


$options = breeze_get_option( 'advanced_settings', true );

$icon = BREEZE_PLUGIN_URL . 'assets/images/advanced-active.png';
?>
<form data-section="advanced">
	<?php if ( true === $is_custom ) { ?>
		<div class="br-overlay-disable"><?php _e( 'Settings are inherited', 'breeze' ); ?></div>
	<?php } ?>
	<section>
		<div class="br-section-title">
			<img src="<?php echo $icon; ?>"/>
			<?php _e( 'ADVANCED OPTIONS', 'breeze' ); ?>
		</div>

		<!-- START OPTION -->
		<div class="br-option-item">
			<div class="br-label">
				<div class="br-option-text">
					<?php _e( 'Never Cache URL(s)', 'breeze' ); ?>
				</div>
			</div>
			<div class="br-option">
				<p>
					<?php _e( 'Specify URLs of Pages or posts that should never be cached (one per line)', 'breeze' ); ?>
				</p>
				<?php
				$excluded_url_list = true;

				if ( isset( $options['breeze-exclude-urls'] ) && ! empty( $options['breeze-exclude-urls'] ) ) {
					$excluded_url_list = breeze_validate_urls( $options['breeze-exclude-urls'] );
				}


				$css_output = '';
				if ( ! empty( $options['breeze-exclude-urls'] ) ) {
					$output     = implode( "\n", $options['breeze-exclude-urls'] );
					$css_output = esc_textarea( $output );
				}

				$placeholder_never_cache_url = 'Exclude Single URL:&#10;https://demo.com/example/&#10;&#10;Exclude Multiple URL using wildcard&#10;https://demo.com/example/(.*)';
				?>
				<textarea cols="100" rows="7" id="exclude-urls" name="exclude-urls" placeholder="<?php echo esc_attr( $placeholder_never_cache_url ); ?>"><?php echo $css_output; ?></textarea>
				<div class="br-note">
					<p>
						<?php

						_e( 'Add the URLs of the pages (one per line) you wish to exclude from the WordPress internal cache. To exclude URLs from the Varnish cache, please refer to this ', 'breeze' );
						?>
						<a
								href="https://support.cloudways.com/how-to-exclude-url-from-varnish/"
								target="_blank"><?php _e( 'Knowledge Base', 'breeze' ); ?></a><?php _e( ' article.', 'breeze' ); ?>
					</p>
					<?php if ( false === $excluded_url_list ) { ?>
						<p class="br-notice">
							<?php _e( 'One (or more) URL is invalid. Please check and correct the entry.', 'breeze' ); ?>
						</p>
					<?php } ?>
				</div>
			</div>
		</div>
		<!-- END OPTION -->

		<!-- START OPTION -->
		<div class="br-option-item">
			<div class="br-label">
				<div class="br-option-text">
					<?php _e( 'Cache Query Strings', 'breeze' ); ?>
				</div>
			</div>
			<div class="br-option">
				<?php
				$cached_query_strings = '';
				if ( isset( $options['cached-query-strings'] ) && ! empty( $options['cached-query-strings'] ) ) {
					$output               = implode( "\n", $options['cached-query-strings'] );
					$cached_query_strings = esc_textarea( $output );
				}

				?>
				<textarea cols="100" rows="7" id="cache-query-str" name="cache-query-str" placeholder="City"><?php echo $cached_query_strings; ?></textarea>
				<div class="br-note">
					<p>
						<?php
						_e( 'Pages that contain the query strings added here, will be cached. Each entry must be added in a new line. For further details please refer to this ', 'breeze' );
						?>
						<a
								href="https://support.cloudways.com/en/articles/5126470-how-to-install-and-configure-breeze-wordpress-cache-plugin"
								target="_blank"><?php _e( 'KB', 'breeze' ); ?></a><?php _e( ' article.', 'breeze' ); ?>
					</p>
				</div>
			</div>
		</div>
		<!-- END OPTION -->

		<!-- START OPTION -->
		<div class="br-option-item">
			<div class="br-label">
				<div class="br-option-text">
					<?php _e( 'Disable Emoji', 'breeze' ); ?>
				</div>
			</div>
			<div class="br-option">
				<?php
				if ( ! isset( $options['breeze-wp-emoji'] ) ) {
					$options['breeze-wp-emoji'] = '0';
				}

				$basic_value = filter_var( $options['breeze-wp-emoji'], FILTER_VALIDATE_BOOLEAN );
				$is_enabled  = ( isset( $basic_value ) && true === $basic_value ) ? checked( $options['breeze-wp-emoji'], '1', false ) : '';
				?>
				<div class="on-off-checkbox">
					<label class="br-switcher">
						<input id="breeze-wpjs-emoji" name="breeze-wpjs-emoji" type="checkbox" class="br-box" value="1" <?php echo $is_enabled; ?>>
						<div class="br-see-state">
						</div>
					</label><br>
				</div>

				<div class="br-note">
					<p>
						<?php

						_e( 'Disable the loading of emoji libraries and CSS', 'breeze' );
						?>
					</p>
				</div>
			</div>
		</div>
		<!-- END OPTION -->
	</section>
	<div class="br-submit">
		<input type="submit" value="<?php echo __( 'Save Changes', 'breeze' ); ?>" class="br-submit-save"/>
	</div>
</form>
