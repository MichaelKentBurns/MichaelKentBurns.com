<?php
if (!defined('ABSPATH') && !defined('MCDATAPATH')) exit;

if (!class_exists('MCProtect')) :
require_once dirname( __FILE__ ) . '/logger.php';
require_once dirname( __FILE__ ) . '/ipstore.php';
require_once dirname( __FILE__ ) . '/request.php';
require_once dirname( __FILE__ ) . '/wp_user.php';
require_once dirname( __FILE__ ) . '/lib.php';
require_once dirname( __FILE__ ) . '/fw.php';
require_once dirname( __FILE__ ) . '/lp.php';
require_once dirname( __FILE__ ) . '/../helper.php';

class MCProtect {
	public static $settings;
	public static $db;
	public static $info;

	const MODE_PREPEND = 0;
	const MODE_WP      = 1;

	const CONF_VERSION = '2';

	public static function init($mode) {
		if ($mode == MCProtect::MODE_PREPEND) {
			$config_file = MCDATAPATH .  MCCONFKEY . '-' . 'mc.conf';
			$config = MCProtectUtils::parseFile($config_file);

			if (empty($config['time']) || !($config['time'] > time() - (48*3600)) ||
					!isset($config['mc_conf_version']) ||
					(MCProtect::CONF_VERSION !== $config['mc_conf_version'])) {
				return false;

			}

			$brand_name = array_key_exists('brandname', $config) ? $config['brandname'] : 'Protect';
			$request_ip_header = array_key_exists('ipheader', $config) ? $config['ipheader'] : null;
			$request = new MCProtectRequest($request_ip_header);
			$fw_config = array_key_exists('fw', $config) ? $config['fw'] : array();

			MCProtectFW::getInstance($mode, $request, $fw_config, $brand_name)->init();
		} else {
			//For backward compatibility.
			self::$settings = new MCWPSettings();
			self::$db = new MCWPDb();
			self::$info = new MCInfo(self::$settings);

			$plug_config = self::$settings->getOption(self::$info->services_option_name);
			$config = array_key_exists('protect', $plug_config) ? $plug_config['protect'] : array();
			if (!is_array($config) || !array_key_exists('mc_conf_version', $config) ||
				(MCProtect::CONF_VERSION !== $config['mc_conf_version'])) {

				return false;
			}

			$brand_name = self::$info->getBrandName();
			$request_ip_header = array_key_exists('ipheader', $config) ? $config['ipheader'] : null;
			$request = new MCProtectRequest($request_ip_header);
			$fw_config = array_key_exists('fw', $config) ? $config['fw'] : array();
			$lp_config = array_key_exists('lp', $config) ? $config['lp'] : array();

			MCProtectFW::getInstance($mode, $request, $fw_config, $brand_name)->init();
			MCProtectLP::getInstance($request, $lp_config, $brand_name)->init();
		}
	}

	public static function uninstall() {
		self::$settings->deleteOption('bvptconf');
		self::$settings->deleteOption('bvptplug');
		MCProtectIpstore::uninstall();
		MCProtectFW::uninstall();
		MCProtectLP::uninstall();

		MCProtect::removeWPPrepend();
		MCProtect::removePHPPrepend();
		MCProtect::removeMCData();

		return true;
	}

	private static function removeWPPrepend() {
		$wp_conf_paths = array(
			dirname(ABSPATH) . "/wp-config.php",
			dirname(ABSPATH) . "/../wp-config.php"
		);

		if (file_exists($wp_conf_paths[0])) {
			$fname = $wp_conf_paths[0];
		} elseif (file_exists($wp_conf_paths[1])) {
			$fname = $wp_conf_paths[1];
		} else {
			return;
		}

		$pattern = "@include '" . dirname(ABSPATH) . "/malcare-waf.php" . "';";
		
		MCProtectUtils::fileRemovePattern($fname, $pattern);
	}

	private static function removePHPPrepend() {
		MCProtect::removeHtaccessPrepend();
		MCProtect::removeUseriniPrepend();
	}

	private static function removeHtaccessPrepend() {
		$pattern = "/# MalCare WAF(.|\n)*# END MalCare WAF/i";

		MCProtectUtils::fileRemovePattern(dirname(ABSPATH) . "/.htaccess", $pattern, true);
	}

	private static function removeUseriniPrepend() {
		$pattern = "/; MalCare WAF(.|\n)*; END MalCare WAF/i";

		MCProtectUtils::fileRemovePattern(dirname(ABSPATH) . "/.user.ini", $pattern, true);
	}

	private static function removeMCData() {
		$content_dir = defined('WP_CONTENT_DIR') ? WP_CONTENT_DIR : dirname(ABSPATH) . "/wp-content";
		$mc_data_dir = $content_dir . "/mc_data";

		MCProtectUtils::rrmdir($mc_data_dir);
	}
}
endif;