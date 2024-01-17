<?php
if (!defined('ABSPATH') && !defined('MCDATAPATH')) exit;

if (!class_exists('MCProtectRequest_V542')) :
class MCProtectRequest_V542 {
	public $ip;
	public $host = '';
	public $uri;
	public $method = '';
	public $path = '';
	public $timestamp;
	public $get_params;
	public $post_params;
	public $cookies;
	public $headers = array();
	public $file_names = array();
	public $files;
	public $respcode;
	public $status = MCProtectRequest_V542::STATUS_ALLOWED;
	public $category = MCProtectRequest_V542::CATEGORY_NORMAL;

	public $wp_user;

	#XNOTE: SHould be part of Protect.
	const STATUS_ALLOWED  = 1;
	const STATUS_BLOCKED  = 2;
	const STATUS_BYPASSED = 3;

	const CATEGORY_BLACKLISTED        = 1;
	const CATEGORY_NORMAL             = 10;
	const CATEGORY_WHITELISTED        = 20;
	const CATEGORY_BOT_BLOCKED        = 30;
	const CATEGORY_COUNTRY_BLOCKED    = 40;
	const CATEGORY_USER_BLACKLISTED   = 50;
	const CATEGORY_RULE_BLOCKED       = 60;
	const CATEGORY_RULE_ALLOWED       = 70;
	const CATEGORY_PRIVATEIP          = 80;
	const CATEGORY_GLOBAL_BOT_BLOCKED = 90;

	public function __construct($ip_header) {
		$this->ip = MCProtectUtils_V542::getIP($ip_header);
		$this->timestamp = time();
		$this->get_params = $_GET;
		$this->cookies = $_COOKIE;
		$this->post_params = $_POST;
		$this->files = $_FILES;
		if (!empty($_FILES)) {
			foreach ($_FILES as $input => $file) {
				$this->file_names[$input] = $file['name'];
			}
		}
		if (is_array($_SERVER)) {
			foreach ($_SERVER as $key => $value) {
				if (strpos($key, 'HTTP_') === 0) {
					$header = substr($key, 5);
					$header = str_replace(array(' ', '_'), array('', ' '), $header);
					$header = ucwords(strtolower($header));
					$header = str_replace(' ', '-', $header);
					$this->headers[$header] = $value;
				}
			}
			if (array_key_exists('CONTENT_TYPE', $_SERVER)) {
				$this->headers['Content-Type'] = $_SERVER['CONTENT_TYPE'];
			}
			if (array_key_exists('CONTENT_LENGTH', $_SERVER)) {
				$this->headers['Content-Length'] = $_SERVER['CONTENT_LENGTH'];
			}
			if (array_key_exists('REFERER', $_SERVER)) {
				$this->headers['Referer'] = $_SERVER['REFERER'];
			}
			if (array_key_exists('HTTP_USER_AGENT', $_SERVER)) {
				$this->headers['User-Agent'] = $_SERVER['HTTP_USER_AGENT'];
			}

			if (array_key_exists('Host', $this->headers)) {
				$this->host = $this->headers['Host'];
			} elseif (array_key_exists('SERVER_NAME', $_SERVER)) {
				$this->host = $_SERVER['SERVER_NAME'];
			}

			$this->method = array_key_exists('REQUEST_METHOD', $_SERVER)
				? $_SERVER['REQUEST_METHOD'] : 'GET';
			$this->uri = array_key_exists('REQUEST_URI', $_SERVER) ? $_SERVER['REQUEST_URI'] : '';
			$_uri = parse_url($this->uri);
			$this->path = (is_array($_uri) && array_key_exists('path', $_uri)) ? $_uri['path']  : $this->uri;
		}
	}

	public static function blacklistedCategories() {
		return array(
			MCProtectRequest_V542::CATEGORY_BOT_BLOCKED,
			MCProtectRequest_V542::CATEGORY_COUNTRY_BLOCKED,
			MCProtectRequest_V542::CATEGORY_USER_BLACKLISTED,
			MCProtectRequest_V542::CATEGORY_GLOBAL_BOT_BLOCKED
		);
	}

	public static function whitelistedCategories() {
		return array(MCProtectRequest_V542::CATEGORY_WHITELISTED);
	}

	public function setRespCode($code) {
		$this->respcode = $code;
	}

	public function getRespCode() {
		if (!isset($this->respcode) && function_exists('http_response_code')) {
			$this->respcode = http_response_code();
		}

		return $this->respcode;
	}

	public function getStatus() {
		return $this->status;
	}

	public function getCategory() {
		return $this->category;
	}

	private function getKeyVal($array, $key) {
		if (is_array($array)) {
			if (is_array($key)) {
				$_key = array_shift($key);
				if (array_key_exists($_key, $array)) {
					if (count($key) > 0) {
						return $this->getKeyVal($array[$_key], $key);
					} else {
						return $array[$_key];
					}
				}
			} else {
				return array_key_exists($key, $array) ? $array[$key] : null;
			}
		}
		return null;
	}

	public function getPostParams() {
		if (func_num_args() > 0) {
			$args = func_get_args();
			return $this->getKeyVal($this->post_params, $args);
		}
		return $this->post_params;
	}

	public function getCookies() {
		if (func_num_args() > 0) {
			$args = func_get_args();
			return $this->getKeyVal($this->cookies, $args);
		}
		return $this->cookies;
	}
	
	public function getGetParams() {
		if (func_num_args() > 0) {
			$args = func_get_args();
			return $this->getKeyVal($this->get_params, $args);
		}
		return $this->get_params;
	}

	public function getAllParams() {
		return array("getParams" => $this->get_params, "postParams" => $this->post_params);
	}

	public function getHeader($key) {
		if (array_key_exists($key, $this->headers)) {
			return $this->headers[$key];
		}
		return null;
	}

	public function getHeaders() {
		if (func_num_args() > 0) {
			$args = func_get_args();
			return $this->getKeyVal($this->headers, $args);
		}
		return $this->headers;
	}

	public function getFiles() {
		if (func_num_args() > 0) {
			$args = func_get_args();
			return $this->getKeyVal($this->files, $args);
		}
		return $this->files;
	}

	public function getFileNames() {
		if (func_num_args() > 0) {
			$args = func_get_args();
			return $this->getKeyVal($this->file_names, $args);
		}
		return $this->file_names;
	}

	public function getHost() {
		return $this->host;
	}

	public function getURI() {
		return $this->uri;
	}

	public function getAction() {
		$post_action = $this->getPostParams('action');
		if (isset($post_action)) {
			return $post_action;
		} else {
			return $this->getGetParams('action');
		}
	}

	public function getPath() {
		return $this->path;
	}

	public function getIP() {
		return $this->ip;
	}

	public function getMethod() {
		return $this->method;
	}

	public function getTimestamp() {
		return $this->timestamp;
	}

	public function getRequestID() {
		if (!defined("BV_REQUEST_ID")) {
			define("BV_REQUEST_ID", uniqid(mt_rand()));
		}

		return BV_REQUEST_ID;
	}

	public function getServerValue($key) {
		if (isset($_SERVER) && array_key_exists($key, $_SERVER)) {
			return $_SERVER[$key];
		}
		return false;
	}

	public function getHeadersV2() {
		return $this->headers;
	}

	public function getFilesV2() {
		return $this->files;
	}

	public function getFileNamesV2() {
		return $this->file_names;
	}

	public function getPostParamsV2() {
		return $this->post_params;
	}

	public function getGetParamsV2() {
		return $this->get_params;
	}

	public function getCookiesV2() {
		return $this->cookies;
	}
}
endif;