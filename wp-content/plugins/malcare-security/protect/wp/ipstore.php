<?php
if (!defined('ABSPATH')) exit;
if (!class_exists('BVIPStore')) :

require_once dirname( __FILE__ ) . '/../fw/request.php';

	class BVIPStore {

		public $db;
		public static $name = 'ip_store';

		#CATEGORY
		const FW = 3;
		const LP = 4;

		function __construct($db) {
			$this->db = $db;
		} 

		function init() {
			add_action('clear_ip_store', array($this, 'clearConfig'));
		}

		public static function blacklistedTypes() {
			return BVWPRequest::blacklistedCategories();
		}

		public static function whitelistedTypes() {
			return BVWPRequest::whitelistedCategories();
		}

		public function clearConfig() {
			$this->db->dropBVTable(BVIPStore::$name);
		}

		public function isLPIPBlacklisted($ip) {
			return $this->checkIPPresent($ip, self::blacklistedTypes(), BVIPStore::LP);
		}

		public function isLPIPWhitelisted($ip) {
			return $this->checkIPPresent($ip, self::whitelistedTypes(), BVIPStore::LP);
		}

		public function getTypeIfBlacklistedIP($ip) {
			return $this->getIPType($ip, self::blacklistedTypes(), BVIPStore::FW);
		}

		public function isFWIPBlacklisted($ip) {
			return $this->checkIPPresent($ip, self::blacklistedTypes(), BVIPStore::FW);
		}

		public function isFWIPWhitelisted($ip) {
			return $this->checkIPPresent($ip, self::whitelistedTypes(), BVIPStore::FW);
		}

		public function checkIPPresent($ip, $types, $category) {
			$ip_category = $this->getIPType($ip, $types, $category);
			return isset($ip_category) ? true : false;
		}

		public function getIPType($ip, $types, $category) {
			$db = $this->db;
			$table = $db->getBVTable(BVIPStore::$name);
			if ($db->isTablePresent($table)) {
				$binIP = BVProtectBase::bvInetPton($ip);
				$is_v6 = BVProtectBase::isIPv6($ip);
				if ($binIP !== false) {
					$category_str = ($category == BVIPStore::FW) ? "`is_fw` = true" : "`is_lp` = true";
					$query_str = "SELECT * FROM $table WHERE %s >= `start_ip_range` && %s <= `end_ip_range` && " . $category_str . " && `type` in (" . implode(',', $types) . ") && `is_v6` = %d LIMIT 1;";
					$query = $db->prepare($query_str, array($binIP, $binIP, $is_v6));

					return $db->getVar($query, 5);
				}
			}
		}
	}
endif;