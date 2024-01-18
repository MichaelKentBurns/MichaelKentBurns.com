<?php

$sql = null;
require_once __DIR__ . '/incs.php';

function adminer_object() {

	$messages = Lev0\DbAccessAdminer\var_file(Lev0\DbAccessAdminer\FMT_MESSAGES);

	class DbAccessAdminer extends Adminer {

		private $wp_adminer_creds;
		private $wp_adminer_auto_submit;
		private $wp_adminer_message;
		private $wp_adminer_message_printed = false;

		function __construct(array $creds = null, array $message, $auto_submit) {
			if ($creds) {
				$this->wp_adminer_creds = (object) $creds;
			}
			$this->wp_adminer_message = $message;
			$this->wp_adminer_auto_submit = $auto_submit;
			if (isset($_POST['auth'])) {
				$_POST['auth']['driver'] = 'server';
				$_POST['auth']['server'] = $_POST['auth']['username'] = $_POST['auth']['password'] = '';
			}
		}

		function credentials() {
			if (!$this->wp_adminer_creds) {
				# always check external stat rather than relying on adminer's session login
				$this->wp_adminer_message_printed = true;
				auth_error($this->esc_message($this->wp_adminer_message));
				return [];
			}
			return [
				$this->wp_adminer_creds->server,
				$this->wp_adminer_creds->username,
				$this->wp_adminer_creds->password,
			];
		}

		function database() {
			return $this->wp_adminer_creds ? $this->wp_adminer_creds->db : null;
		}

		function loginForm() {
			if (!$this->wp_adminer_creds) {
				if (!$this->wp_adminer_message_printed) {
					echo '<p>', $this->esc_message($this->wp_adminer_message), '</p>';
				}
				return;
			}

			if (!$this->wp_adminer_auto_submit) {
				goto ret;
			}

			$nonce_attr = null;
			$response_headers = headers_list();
			while ($response_headers) {
				$response_header = array_pop($response_headers);
				if (preg_match('/^Content-Security-Policy:.*?\'nonce-([^\']+)/i', $response_header, $nonce_matches)) {
					$nonce_attr = ' nonce="' . h($nonce_matches[1]) . '"';
					break;
				}
			}
			echo <<<EOHTML
<script$nonce_attr>
	document.addEventListener(
		'DOMContentLoaded',
		function() {
			document.forms[0].submit();
		},
		true
	);
</script>
EOHTML;

			ret:
			parent::loginForm();
		}

		function loginFormField($name, $heading, $value) {
			# only for user's benefit, submitted values are overridden by config
			switch ($name) {
				case 'db':
					$value = isset($_GET['username']) ? ($_GET['db'] ?? '') : $this->database();
					break;
				case 'driver':
				case 'server':
				case 'username':
				case 'password':
					$name = h($name);
					return <<<EOHTML
<input type="hidden" name="auth[$name]">

EOHTML;
				default:
					return parent::loginFormField($name, $heading, $value);
			}
			$value = h($value);
			return <<<EOHTML
$heading<input type="text" name="auth[$name]" value="$value">

EOHTML;
		}

		function login($login, $password) {
			return ((bool) $this->wp_adminer_creds) ?: $this->esc_message($this->wp_adminer_message);
		}

		function esc_message(array $message) {
			list ($format, $values) = $message + ['', []];
			return vsprintf(h($format), $values);
		}
	}

	$creds_enc = $creds = null;
	$auto_submit = false;
	if (
		!$messages
		|| !is_array($messages)
	) {
		$message = [
			'No messages file found. Check %sWP Admin > Settings > Adminer%s.',
			[
				'<em>',
				'</em>',
			],
		];
	}
	elseif (count(get_class_methods('Adminer')) < 5) { # only one method in fallback class, below
		$message = $messages['load_failed'];
	}
	elseif (
		!($conduit = Lev0\DbAccessAdminer\var_file(Lev0\DbAccessAdminer\FMT_CONDUIT))
		|| !is_array($conduit)
		|| empty($conduit['url'])
		|| empty($conduit['cookie_name'])
	) {
		$message = $messages['ephemeral_read_failed'];
	}
	elseif (empty($_COOKIE[$conduit['cookie_name']])) {
		$message = $messages['unprivileged'];
	}
	else {
		$auth_req = null;
		$conduit['cookie'] = $_COOKIE[$conduit['cookie_name']];
		$conduit['xff'] = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
		if (ini_get('allow_url_fopen')) {
			$auth_req = function() use ($conduit) {
				try {
					$body = file_get_contents($conduit['url'], false, stream_context_create([
						'http' => [
							'ignore_errors' => true,
							'header' => [
								'X-WP-Nonce: ' . $conduit['nonce'],
								'Cookie: ' . http_build_query([$conduit['cookie_name'] => $conduit['cookie']]),
								'X-Forwarded-For: ' . $conduit['xff'],
							],
						],
					]));
					$status = 400;
					if (!empty($http_response_header)) {
						foreach ($http_response_header as $header) {
							if (preg_match('#^HTTP/\d+(?:\.\d+)?\s+([1-5]\d\d)\b#i', $header, $matches)) {
								$status = (int) $matches[1];
							}
						}
					}
					return compact('status', 'body');
				}
				catch (Exception $e) {
				}
			};
		}
		elseif (function_exists('curl_init')) {
			$auth_req = function() use ($conduit) {
				try {
					if (
						($curl = curl_init($conduit['url']))
						&& curl_setopt_array($curl, [
							CURLOPT_HTTPHEADER => [
								'X-WP-Nonce: ' . $conduit['nonce'],
								'Cookie: ' . http_build_query([$conduit['cookie_name'] => $conduit['cookie']]),
								'X-Forwarded-For: ' . $conduit['xff'],
							],
						])
						&& ($ob_on = ob_start())
						&& curl_exec($curl)
						&& !($ob_on = false)
						&& ($body = ob_get_clean()) !== false
						&& ($status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE))
					) {
						return compact('status', 'body');
					}
				}
				catch (Exception $e) {
				}
				if (!$ob_on) {
					ob_end_clean();
				}
			};
		}

		if (!$auth_req) {
			$message = $messages['setup_incomplete'];
		}
		elseif (
			!($auth_resp = $auth_req())
			|| !extract($auth_resp)
			|| !$status
			|| !$body
			|| !($body = json_decode($body))
			|| !is_object($body)
		) {
			#var_dump($status, $body); die;
			$message = $messages['communication_failed'];
		}
		elseif (
			$status != 200
			|| $body->message !== Lev0\DbAccessAdminer\OK
			|| !$body->creds
		) {
			if (
				$status == 409
				|| $status < 400
				|| $status >= 500
			) {
				$message = empty($body->message) ? $messages['unknown'] : (array) $body->message;
			}
			else {
				$message = $messages['unprivileged'];
			}
		}
		elseif (
			!($encryption = Lev0\DbAccessAdminer\var_file(Lev0\DbAccessAdminer\FMT_ENCRYPT))
			|| !is_array($encryption)
			|| empty($encryption['cipher_algo'])
			|| empty($encryption['passphrase'])
			|| empty($encryption['iv'])
		) {
			$message = $messages['ephemeral_read_failed'];
		}
		elseif (
			!($creds_enc = base64_decode($body->creds))
			|| !($encryption['iv'] = base64_decode($encryption['iv']))
			|| !($encryption['passphrase'] = base64_decode($encryption['passphrase']))
			|| !($creds_enc = openssl_decrypt(
				$creds_enc
				, $encryption['cipher_algo']
				, $encryption['passphrase']
				, empty($encryption['options']) ? 0 : $encryption['options']
				, $encryption['iv']
			))
			|| !($creds_enc = json_decode($creds_enc, true))
			|| !is_array($creds_enc)
			|| empty($creds_enc['db'])
			|| empty($creds_enc['username'])
			|| empty($creds_enc['password'])
			|| empty($creds_enc['server'])
		) {
			$message = $messages['decrypt_failed'];
		}
		else {
			$creds = $creds_enc;
			$message = $messages['db_failed'];
			$auto_submit = $conduit['auto_submit'];
		}
	}

	return new DbAccessAdminer($creds, $message, $auto_submit);
}

if (
	!is_readable(Lev0\DbAccessAdminer\INC_ADMINER)
	|| !include Lev0\DbAccessAdminer\INC_ADMINER
) {
	function h($string) {
		return str_replace("\0", "&#0;", htmlspecialchars($string, ENT_QUOTES, 'utf-8'));
	}
	class Adminer {
		function loginForm() {
			echo '<p>', $this->esc_message($this->wp_adminer_message), '</p>';
		}
	}
	adminer_object()->loginForm();
}
