<?php
if (!defined('ABSPATH') && !defined('MCDATAPATH')) exit;

if (!class_exists('MCProtectUtils_V542')) :
class MCProtectUtils_V542 {
	public static function getIP($ip_header) {
		$ip = null;

		if (is_array($ip_header)) {
			if ((array_key_exists('hdr', $ip_header) && is_string($ip_header['hdr'])) &&
					(array_key_exists('pos', $ip_header) && is_int($ip_header['pos']))) {

				if (array_key_exists($ip_header['hdr'], $_SERVER) && is_string($_SERVER[$ip_header['hdr']])) {
					$_ips = preg_split("/(,| |\t)/", $_SERVER[$ip_header['hdr']]);

					if (array_key_exists($ip_header['pos'], $_ips)) {
						$ip = $_ips[$ip_header['pos']];
					}
				}
			}
		} elseif (array_key_exists('REMOTE_ADDR', $_SERVER)) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		if (is_string($ip)) {
			$ip = trim($ip);

			if (MCHelper::safePregMatch('/^\[([0-9a-fA-F:]+)\](:[0-9]+)$/', $ip, $matches)) {
				$ip = $matches[1];
			} elseif (MCHelper::safePregMatch('/^([0-9.]+)(:[0-9]+)$/', $ip, $matches)) {
				$ip = $matches[1];
			}
		}

		return self::isValidIP($ip) ? $ip : '127.0.0.1';
	}

	public static function isIPv6($ip) {
		return (false === filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) ? false : true;
	}

	public static function hasIPv6Support() {
		return defined('AF_INET6');
	}

	public static function isValidIP($ip) {
		return filter_var($ip, FILTER_VALIDATE_IP) !== false;
	}

	public static function bvInetPton($ip) {
		$pton = self::isValidIP($ip) ? (self::hasIPv6Support() ? inet_pton($ip) : self::_bvInetPton($ip)) : false;
		return $pton;
	}

	public static function _bvInetPton($ip) {
		if (MCHelper::safePregMatch('/^(?:\d{1,3}(?:\.|$)){4}/', $ip)) {
			$octets = explode('.', $ip);
			$bin = chr($octets[0]) . chr($octets[1]) . chr($octets[2]) . chr($octets[3]);
			return $bin;
		}

		if (MCHelper::safePregMatch('/^((?:[\da-f]{1,4}(?::|)){0,8})(::)?((?:[\da-f]{1,4}(?::|)){0,8})$/i', $ip)) {
			if ($ip === '::') {
				return "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";
			}
			$colon_count = substr_count($ip, ':');
			$dbl_colon_pos = strpos($ip, '::');
			if ($dbl_colon_pos !== false) {
				$ip = str_replace('::', str_repeat(':0000',
					(($dbl_colon_pos === 0 || $dbl_colon_pos === strlen($ip) - 2) ? 9 : 8) - $colon_count) . ':', $ip);
				$ip = trim($ip, ':');
			}

			$ip_groups = explode(':', $ip);
			$ipv6_bin = '';
			foreach ($ip_groups as $ip_group) {
				$ipv6_bin .= pack('H*', str_pad($ip_group, 4, '0', STR_PAD_LEFT));
			}

			return strlen($ipv6_bin) === 16 ? $ipv6_bin : false;
		}

		if (MCHelper::safePregMatch('/^(?:\:(?:\:0{1,4}){0,4}\:|(?:0{1,4}\:){5})ffff\:(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})$/i', $ip, $matches)) {
			$octets = explode('.', $matches[1]);
			return chr($octets[0]) . chr($octets[1]) . chr($octets[2]) . chr($octets[3]);
		}

		return false;
	}

	public static function isIPInRange($start_ip_range, $end_ip_range, $ip) {
		$bin_ip = null;
		if ($ip) {
			$bin_ip = self::bvInetPton($ip);
		}
		if ($bin_ip && $bin_ip >= self::bvInetPton($start_ip_range)
				&& $bin_ip <= self::bvInetPton($end_ip_range)) {
			return true;
		}
		return false;
	}

	public static function isPrivateIP($ip) {
		$private_ip_ranges = array(
			array("10.0.0.0", "10.255.255.255"),
			array("172.16.0.0", "172.31.255.255"),
			array("192.168.0.0", "192.168.255.255"),
			array("127.0.0.1", "127.255.255.255"),
			array("::1","::1"),
			array("fc00::","fdff:ffff:ffff:ffff:ffff:ffff:ffff:ffff")
		);

		$result = false;
		foreach ($private_ip_ranges as $ip_range) {
			$result = self::isIPInRange($ip_range[0], $ip_range[1], $ip);
			if($result) {
				return $result;
			}
		}
		return $result;
	}

	public static function rrmdir($dir) {
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") {
					if (is_dir($dir . "/" . $object) && !is_link($dir . "/" . $object)) {
						MCProtectUtils_V542::rrmdir($dir . "/" . $object);
					} else {
						unlink($dir . "/" . $object);
					}
				}
			}
			rmdir($dir);
		}
	}

	public static function getLength($val) {
		$length = 0;

		if (is_array($val)) {
			foreach ($val as $e) {
				$length += MCProtectUtils_V542::getLength($e);
			}

			return $length;
		} else {
			return strlen((string) $val);
		}
	}

	public static function parseFile($fname) {
		$result = array();

		if (file_exists($fname)) {
			$content = file_get_contents($fname);
			if (($content !== false) && is_string($content)) {
				$result = json_decode($content, true);

				if (!is_array($result)) {
					$result = array();
				}
			}
		}

		return $result;
	}

	public static function fileRemovePattern($fname, $pattern, $regex_pattern = false) {
		if (!file_exists($fname)) return;

		$content = file_get_contents($fname);
		if ($content) {
			if ($regex_pattern) {
				$modified_content = preg_replace($pattern, "", $content);
			} else {
				$modified_content = str_replace($pattern, "", $content);
			}

			if ($content !== $modified_content) {
				file_put_contents($fname, $modified_content);
			}
		}
	}

	public static function havePluginsLoaded() {
		return (function_exists('did_action') && (did_action('plugins_loaded') > 0));
	}

	public static function haveMupluginsLoaded() {
		return (function_exists('did_action') && (did_action('muplugins_loaded') > 0));
	}

	public static function isWPVersionCompatible($required) {
		global $wp_version;

		// Strip off any -alpha, -RC, -beta, -src suffixes.
		list( $version ) = explode( '-', $wp_version );

		return empty( $required ) || version_compare( $version, $required, '>=' );
	}

	public static function preInitWPHook($hook_name, $function_name, $priority, $accepted_args) {
		global $wp_filter;

		// Check if $wp_filter is not initialized or not an array
		if (!isset($wp_filter) || !is_array($wp_filter)) {
			$wp_filter = array();
		}

		// Check if the hook exists in $wp_filter
		if (!isset($wp_filter[$hook_name])) {
			$wp_filter[$hook_name] = array();
		}

		// Check if the priority exists for the hook
		if (!isset($wp_filter[$hook_name][$priority])) {
			$wp_filter[$hook_name][$priority] = array();
		}

		// Add the filter function information to the $wp_filter array
		$wp_filter[$hook_name][$priority][] = array(
			'function' => $function_name,
			'accepted_args' => $accepted_args,
		);
	}

	public static function signMessage($message, $key, $algorithm = 'sha256') {
		if (!is_string($message) || !is_string($key)) {
			return false;
		}

		return hash_hmac($algorithm, $message, $key);
	}

	public static function verifyMessage($message, $signature, $key, $algorithm = 'sha256') {
		if (!is_string($message) || !is_string($signature) || !is_string($key)) {
			return false;
		}

		$calc_signature = self::signMessage($message, $key, $algorithm);

		return hash_equals($calc_signature, $signature);
	}
}
endif;