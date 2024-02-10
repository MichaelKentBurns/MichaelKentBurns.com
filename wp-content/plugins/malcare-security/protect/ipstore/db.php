<?php
if (!defined('ABSPATH') && !defined('MCDATAPATH')) exit;

if (!class_exists('MCProtectIpstoreDB_V545')) :
class MCProtectIpstoreDB_V545 {
		const TABLE_NAME = 'ip_store';

		const CATEGORY_FW = 3;
		const CATEGORY_LP = 4;

		#XNOTE: check this. 
		public static function blacklistedTypes() {
			return MCProtectRequest_V545::blacklistedCategories();
		}

		public static function whitelistedTypes() {
			return MCProtectRequest_V545::whitelistedCategories();
		}

		public static function uninstall() {
			MCProtect_V545::$db->dropBVTable(MCProtectIpstoreDB_V545::TABLE_NAME);
		}

		public function isLPIPBlacklisted($ip) {
			return $this->checkIPPresent($ip, self::blacklistedTypes(), MCProtectIpstoreDB_V545::CATEGORY_LP);
		}

		public function isLPIPWhitelisted($ip) {
			return $this->checkIPPresent($ip, self::whitelistedTypes(), MCProtectIpstoreDB_V545::CATEGORY_LP);
		}

		public function getTypeIfBlacklistedIP($ip) {
			return $this->getIPType($ip, self::blacklistedTypes(), MCProtectIpstoreDB_V545::CATEGORY_FW);
		}

		public function isFWIPBlacklisted($ip) {
			return $this->checkIPPresent($ip, self::blacklistedTypes(), MCProtectIpstoreDB_V545::CATEGORY_FW);
		}

		public function isFWIPWhitelisted($ip) {
			return $this->checkIPPresent($ip, self::whitelistedTypes(), MCProtectIpstoreDB_V545::CATEGORY_FW);
		}

		private function checkIPPresent($ip, $types, $category) {
			$ip_category = $this->getIPType($ip, $types, $category);

			return isset($ip_category) ? true : false;
		}

		#XNOTE: getIPCategory or getIPType?
		private function getIPType($ip, $types, $category) {
			$table = MCProtect_V545::$db->getBVTable(MCProtectIpstoreDB_V545::TABLE_NAME);

			if (MCProtect_V545::$db->isTablePresent($table)) {
				$binIP = MCProtectUtils_V545::bvInetPton($ip);
				$is_v6 = MCProtectUtils_V545::isIPv6($ip);

				if ($binIP !== false) {
					$query_str = "SELECT * FROM $table WHERE %s >= `start_ip_range` && %s <= `end_ip_range` && ";
					if ($category == MCProtectIpstoreDB_V545::CATEGORY_FW) {
						$query_str .= "`is_fw` = true";
					} else {
						$query_str .= "`is_lp` = true";
					}
					$query_str .= " && `type` in (" . implode(',', $types) . ") && `is_v6` = %d LIMIT 1;";

					$query = MCProtect_V545::$db->prepare($query_str, array($binIP, $binIP, $is_v6));

					return MCProtect_V545::$db->getVar($query, 5);
				}
			}
		}
	}
endif;