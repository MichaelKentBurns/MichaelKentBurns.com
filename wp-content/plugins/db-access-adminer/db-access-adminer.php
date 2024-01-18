<?php
/*
Plugin Name: Database Access with Adminer
Description: Direct database administration using the open source Adminer application.
Version: 2.1.0
Requires at least: 5.3
Requires PHP: 5.6
Author: Roy Orbison
Author URI: https://profiles.wordpress.org/lev0/	
Licence: GPLv2 or later
*/

namespace Lev0\DbAccessAdminer;

require_once __DIR__ . '/incs.php';

define(__NAMESPACE__ . '\BASE', basename(__FILE__, '.php'));
define(__NAMESPACE__ . '\SETTINGS', BASE . '-options');
define(__NAMESPACE__ . '\SETTINGS_URL', 'options-general.php?page=' . SETTINGS);
const CAP = 'edit_plugins'; # if you can edit code you can do anything
define(__NAMESPACE__ . '\REST_NS', 'lev0/' . BASE);
const REST_CONDUIT = '/conduit/';
const WP_REST_NONCE = 'wp_rest';
const ENCRYPTION_LIFETIME = 300;
const RES_CSS = __DIR__ . '/adminer.css';

add_action('admin_menu', function() {
	$options = get_option(BASE);
	$okay_link = null;

	$plugin_data = get_plugin_data(__FILE__, false);
	$title = $plugin_data['Name'];
	$slug_fields = BASE . '-fields';

	$added = add_options_page(
		esc_html($title)
		, esc_html($title)
		, CAP
		, SETTINGS
		, function() use (&$options, $title, $slug_fields, &$okay_link) {
			if ($reqs_unmet = reqs_unmet()) {
				add_action('admin_notices', function() use ($reqs_unmet) {
					echo '<div class="notice notice-error">';
					foreach ($reqs_unmet as $req_unmet) {
						echo '<p>';
						list ($format, $values) = $req_unmet + ['', []];
						vprintf(esc_html($format), $values);
						echo "</p>\n";
					}
					echo "</div>\n";
				});
			}
			?>
			<div class="wrap">
				<h1><?php echo esc_html($title); ?></h1>
				<form action=options.php method=post>
					<?php
					settings_fields($slug_fields);
					do_settings_sections(SETTINGS);
					submit_button();
					?>
				</form>
				<?php if ($okay_link !== null) { ?>
					<hr>
					<p><a href="<?php echo esc_attr($okay_link); ?>" class="button button-secondary"><?php
						esc_html_e('Open Adminer', 'db-access-adminer')
						?></a></p>
					<p><?php printf(
						/* translators: open and close <em> tag */
						esc_html__('The link is also found under the %sTools%s menu.', 'db-access-adminer')
						, '<em>'
						, '</em>'
					); ?></p>
				<?php } ?>
			</div>
			<?php
		}
	);
	if (!$added) {
		return;
	}

	
	add_action(
		'admin_init'
		, function() use (&$options, $slug_fields) {
			$designs = designs();

			register_setting(
				$slug_fields
				, BASE
				, [
					'sanitize_callback' => function($inputs) use ($slug_fields, $designs) {
						ignore_user_abort(true);

						foreach ($inputs as $name => &$val) {
							switch ("$name") {
								case 'design':
									if (!is_string($val) || !array_key_exists($val, $designs)) {
										add_settings_error(
											$slug_fields
											, 'design_select'
											, esc_html__('Unknown design selection.', 'db-access-adminer')
										);
										$val = '';
									}
									break;
								default:
									$val = (bool) $val;
							}
						}
						unset($val);
						$inputs += array_fill_keys(
							[
								'auto_submit',
								'warning_accepted'
							]
							, false
						);

						if (
							(
								file_exists(RES_CSS)
								&& !@unlink(RES_CSS)
							)
							|| (
								!empty($inputs['design'])
								&& !@symlink($inputs['design'], RES_CSS)
								&& !@copy($inputs['design'], RES_CSS)
							)
						) {
							add_settings_error(
								$slug_fields
								, 'design_set'
								, esc_html__('Failed to set design.', 'db-access-adminer')
							);
						}

						$inputs['last_save'] = current_time('mysql', true);
						return $inputs;
					},
				]
			);

			$slug_sect = BASE . '-general';
			add_settings_section(
				$slug_sect
				, esc_html__('Adminer options', 'wp-admin')
				, '__return_empty_string'
				, SETTINGS
			);
			if ($designs) {
				$name = 'design';
				add_settings_field(
					$name
					, '<label for="' . esc_attr(BASE . "-$name") . '">'
						. esc_html__('Alternative design', 'db-access-adminer')
						. '</label>'
					, function() use (&$options, $name, $designs) {
						printf(
							'<select id="%1$s-%2$s" name="%1$s[%2$s]">' . "\n"
							, esc_attr(BASE)
							, esc_attr($name)
						);
						foreach ($designs as $file => $design) {
							printf(
								'<option value="%s"%s>%s</option>' . "\n"
								, esc_attr($file)
								, (isset($options[$name]) ? $options[$name] : '') === $file ? ' selected' : ''
								, esc_html($design)
							);
						}
						echo "</select>\n<p class=description>"
							, esc_html__('Change the theme Adminer uses.', 'db-access-adminer')
							, '</p>';
					}
					, SETTINGS
					, $slug_sect
				);
			}
			$name = 'auto_submit';
			add_settings_field(
				$name
				, '<label for="' . esc_attr(BASE . "-$name") . '">'
					. esc_html__("Auto-submit Adminer's login form", 'db-access-adminer')
					. '</label>'
				, function() use (&$options, $name) {
					printf(
						'<input type=checkbox id="%1$s-%2$s" name="%1$s[%2$s]" value=1%3$s>'
						, esc_attr(BASE)
						, esc_attr($name)
						, empty($options[$name]) ? '' : ' checked'
					);
					echo '<p class=description>'
						, sprintf(
							esc_html__("Adminer has its own login system which must still be activated, even though this plugin limits access. It's slightly more secure to submit it manually at the cost of a little convenience.", 'db-access-adminer')
							, '<strong>'
							, '</strong>'
						)
						, '</p>';
				}
				, SETTINGS
				, $slug_sect
			);
			$slug_sect = BASE . '-danger';
			add_settings_section(
				$slug_sect
				, esc_html__('Warning', 'wp-admin')
				, '__return_empty_string'
				, SETTINGS
			);
			$name = 'warning_accepted';
			add_settings_field(
				$name
				, '<label for="' . esc_attr(BASE . "-$name") . '">'
					. esc_html__('I accept', 'db-access-adminer')
					. '</label>'
				, function() use (&$options, $name) {
					printf(
						'<input type=checkbox id="%1$s-%2$s" name="%1$s[%2$s]" value=1%3$s>'
						, esc_attr(BASE)
						, esc_attr($name)
						, empty($options[$name]) ? '' : ' checked'
					);
					echo '<p class=description>';
					printf(
						/* translators: open and close <strong> tag on consequences, WP capability name */
						esc_html__("This tool enables direct editing of your database, which can be very useful. However, if you are unfamiliar with it, %syou risk irreversible loss of or damage to your site's data%s. This includes losing access to your site admin pages if you modify records in the users table. It is recommended you do not use this tool unless you have a regular backup regime. It will also be available to all accounts that have the %s capability (normally admins), so you may wish to limit other accounts' access now. Checking this box means you understand this and accept the risk.", 'db-access-adminer')
						, '<strong>'
						, '</strong>'
						, '<code>' . esc_html(CAP) . '</code>'
					);
					echo '</p>';
				}
				, SETTINGS
				, $slug_sect
			);
		}
	);

	$found_link = false;
	add_management_page(
		esc_html($title)
		, esc_html__('Adminer', 'db-access-adminer')
		, CAP
		, BASE
		, function() use (&$options, &$found_link) {
			echo '<div class="notice notice-error"><p>';
			if (empty($options['warning_accepted'])) {
				list ($format, $values) = messages('setup_incomplete');
				vprintf(esc_html($format), $values);
			}
			elseif ($found_link) {
				list ($format, $values) = messages('ephemeral_write_failed');
				vprintf(esc_html($format), $values);
			}
			else {
				esc_html_e('Unable to override menu link.', 'db-access-adminer');
			}
			echo "</p></div>\n";
		}
		, 999
	);
	if (!empty($options['warning_accepted']) && isset($GLOBALS['submenu']['tools.php'])) {
		foreach ($GLOBALS['submenu']['tools.php'] as $i => $link) {
			if (isset($link[2]) && $link[2] === BASE) {
				$found_link = true;
				if (maintain_conduit(!empty($options['auto_submit']), true)) {
					# change link to adminer entry point
					$GLOBALS['submenu']['tools.php'][$i][2] = $okay_link = plugin_dir_url(__FILE__);
				}
				break;
			}
		}
	}
});

add_filter(
	'plugin_action_links_' . plugin_basename(__FILE__)
	, function ($links) {
		if (current_user_can(CAP)) {
			array_unshift(
				$links
				, '<a href="' . esc_attr(admin_url(SETTINGS_URL)) . '">'
					. esc_html__('Settings', 'db-access-adminer') . '</a>'
			);
		}
		return $links;
	}
);

add_action(
	'rest_api_init'
	, function () {
		register_rest_route(
			REST_NS
			, REST_CONDUIT
			, [
				'methods' => 'GET',
				'permission_callback' => function($request) {
					return current_user_can(CAP);
				},
				'callback' => function($request) {
					if (
						reqs_unmet()
						|| ! ($options = get_option(BASE))
						|| empty($options['warning_accepted'])
					) {
						return new \WP_REST_Response(
							[
								'message' => messages('setup_incomplete'),
							]
							, 409
						);
					}

					$maintained_encryption = maintain_encryption($encryption);

					if (
						(!$maintained_encryption && $encryption)
						|| !maintain_conduit(!empty($options['auto_submit']))
					) {
						return new \WP_REST_Response(
							[
								'message' => messages('ephemeral_write_failed'),
							]
							, 500
						);
					}

					if (
						!$maintained_encryption
						|| !($creds = json_encode([
							'db' => DB_NAME,
							'username' => DB_USER,
							'password' => DB_PASSWORD,
							'server' => DB_HOST,
						]))
						|| !($creds = openssl_encrypt(
							$creds
							, $encryption['cipher_algo']
							, $encryption['passphrase']
							, $encryption['options']
							, $encryption['iv']
						))
					) {
						return new \WP_REST_Response(
							[
								'message' => messages('crypt_failed'),
							]
							, 500
						);
					}

					return new \WP_REST_Response([
						'message' => OK,
						'creds' => base64_encode($creds),
					]);
				},
				'show_in_index' => false,
			]
		);
	}
);
# unnecessary to list in REST (not intended to increase security)
add_filter(
	'rest_index'
	, function($response) {
		$response->data['namespaces'] = array_values(array_diff(
			$response->data['namespaces']
			, [REST_NS]
		));
		foreach ($response->data['routes'] as $path => $route) {
			if (REST_NS === $route['namespace']) {
				unset($response->data['routes'][$path]);
			}
		}
		return $response;
	}
);
add_filter(
	'rest_namespace_index'
	, function($response, $request) {
		if (REST_NS === $request['namespace']) {
			unset($response->data['routes']);
		}
		return $response;
	}
	, 10
	, 2
);

function reqs_unmet() {
	$reqs_unmet = [];
	if (
		!ini_get('allow_url_fopen')
		&& !function_exists('curl_init')
	) {
		$reqs_unmet[] = [
			/* translators: PHP config setting name */
			__('You need the cURL PHP extension enabled, or the %s PHP config setting on to use this tool.', 'db-access-adminer'),
			[
				'<code>allow_url_fopen</code>',
			],
		];
	}
	if (!function_exists('openssl_encrypt')) {
		$reqs_unmet[] = [
			__('You need the OpenSSL PHP extension installed and enabled to use this tool.', 'wp-adminer'),
		];
	}
	return $reqs_unmet;
}

function maintain_conduit($auto_submit, $and_messages = false) {
	if ($and_messages && !maintain_messages()) {
		return false;
	}
	if (empty($_COOKIE[LOGGED_IN_COOKIE])) {
		return false;
	}

	# never saves cookie value, always read from the user's request
	$conduit = [
		'url' => rest_url(REST_NS . REST_CONDUIT),
		'cookie_name' => LOGGED_IN_COOKIE,
		'cookie_hash' => sha1($_COOKIE[LOGGED_IN_COOKIE]), # not a usable value, only for invalidating old sessions
	]
		+ compact(
			'auto_submit'
		);
	return (
			($conduit_saved = var_file(FMT_CONDUIT))
			&& is_array($conduit_saved)
			&& array_intersect_key($conduit_saved, $conduit) === $conduit
			&& !empty($conduit_saved['nonce'])
			&& wp_verify_nonce($conduit_saved['nonce'], WP_REST_NONCE) === 1
		)
		|| var_file(
			FMT_CONDUIT
			, $conduit + [
				'nonce' => wp_create_nonce(WP_REST_NONCE),
			]
		);
}

function messages($message = null) {
	static $messages;
	if ($messages === null) {
		$settings_url = admin_url(SETTINGS_URL);
		$messages = [
			'unprivileged' => [
				/* translators: 1: open tag of link to WP admin 2: close tag 3: WP capability name */
				__('You must be %1$slogged in to WordPress%2$s and possess the %3$s capability to use this tool.', 'db-access-adminer'),
				[
					'<a href="' . esc_attr(admin_url()) . '" target="_blank">',
					'</a>',
					'<code>' . esc_html(CAP) . '</code>',
				],
			],
			'setup_incomplete' => [
				/* translators: open and close tags of link to plugin settings */
				__('Ensure you have completed %sAdminer setup%s and addressed missing requirements, if any.', 'db-access-adminer'),
				[
					'<a href="' . esc_attr($settings_url) . '" target="_blank">',
					'</a>',
				],
			],
			'ephemeral_write_failed' => [
				__('Could not create one or more ephemeral files for Adminer. Write access to the plugin directory is required.', 'db-access-adminer'),
			],
			'ephemeral_read_failed' => [
				/* translators: open and close tags of link to plugin settings */
				__('Could not load one or more ephemeral files from WordPress. The plugin may not be %sset up%s correctly.', 'db-access-adminer'),
				[
					'<a href="' . esc_attr($settings_url) . '" target="_blank">',
					'</a>',
				],
			],
			'encrypt_failed' => [
				__('Credentials were not shared because they could not first be secured.', 'db-access-adminer'),
			],
			'decrypt_failed' => [
				__('Could not read secured credentials. Try again.', 'db-access-adminer'),
			],
			'communication_failed' => [
				__('Could not communicate with WordPress. It may be being blocked.', 'db-access-adminer'),
			],
			'db_failed' => [
				__('Database communication error.', 'db-access-adminer'),
			],
			'load_failed' => [
				__('Could not load Adminer.', 'db-access-adminer'),
			],
			'unknown' => [
				__('An unknown error has occurred.', 'db-access-adminer'),
			],
		];
	}
	if ($message !== null) {
		return $messages[$message] + ['', []];
	}
	return $messages;
}
function maintain_messages() {
	$messages = messages();
	return (
			($messages_saved = var_file(FMT_MESSAGES))
			&& is_array($messages_saved)
			&& $messages_saved === $messages
		)
		|| var_file(FMT_MESSAGES, $messages);
}

function maintain_encryption(&$encryption = null) {
	$cipher_algos = array_filter(openssl_get_cipher_methods(), function($cipher_algo) {
		return preg_match('/^AES-(\d+)-CBC$/i', $cipher_algo, $matches)
			&& $matches[1] > 128;
	});
	if (!$cipher_algos) {
		if ($encryption) {
			$encryption = null;
		}
		return false;
	}
	rsort($cipher_algos, SORT_NATURAL | SORT_FLAG_CASE);
	$cipher_algo = reset($cipher_algos);
	$time = time();
	if (
		!($encryption = var_file(FMT_ENCRYPT))
		|| !is_array($encryption)
		|| $encryption['cipher_algo'] !== $cipher_algo
		|| ($time - $encryption['updated']) >= max(
			10
			/**
			 * Maximum age of the credentials used to encrypt the database connection details.
			 *
			 * Clamped to a minimum of 10 seconds.
			 *
			 * @since 2.0.0
			 *
			 * @param int $encryption_lifetime the max credential age in seconds
			 */
			, (int) apply_filters('db_access_adminer_encryption_lifetime', ENCRYPTION_LIFETIME)
		)
	) {
		if (
			!($ivlen = openssl_cipher_iv_length($cipher_algo))
			|| !($iv = openssl_random_pseudo_bytes($ivlen))
			|| !($passphrase = openssl_random_pseudo_bytes(20))
		) {
			if ($encryption) {
				$encryption = null;
			}
			return false;
		}
		$encryption = compact(
			'cipher_algo'
			, 'passphrase'
			, 'iv'
		) + [
			'options' => OPENSSL_RAW_DATA,
			'updated' => $time,
		];
		$encryption_escaped = array_replace($encryption, [
			'iv' => base64_encode($iv),
			'passphrase' => base64_encode($passphrase),
		]);
		if (!var_file(FMT_ENCRYPT, $encryption_escaped)) {
			return false;
		}
	}
	elseif (
		!($encryption['iv'] = base64_decode($encryption['iv']))
		|| !($encryption['passphrase'] = base64_decode($encryption['passphrase']))
	) {
		$encryption = null;
		return false;
	}
	return true;
}

function designs() {
	$ext = '.css';
	$designs = [];
	$files = glob(__DIR__ . "/*$ext");
	if ($files) {
		/* translators: no specific design selected */
		$designs[''] = __('default', 'db-access-adminer');
		foreach ($files as $file) {
			if ($file == RES_CSS) {
				continue;
			}
			$file = basename($file, $ext);
			$designs[$file . $ext] = $file;
		}
	}
	return $designs;
}
