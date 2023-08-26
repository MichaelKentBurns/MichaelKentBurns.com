<?php
if (!defined('MCDATAPATH')) exit;

if (!class_exists('BVPrependIPStore')) :
	class BVPrependIPStore {
		public $whitelistedIPs;
		public $blacklistedIPs;

		#CATEGORY
		const FW = 3;

		public static function blacklistedTypes() {
			return BVWPRequest::blacklistedCategories();
		}

		public static function whitelistedTypes() {
			return BVWPRequest::whitelistedCategories();
		}

		function __construct($confHash) {
			$this->whitelistedIPs = array_key_exists('whitelisted', $confHash) ? $confHash['whitelisted'] : array();
			$this->blacklistedIPs = array_key_exists('blacklisted', $confHash) ? $confHash['blacklisted'] : array();
		}

		public function isFWIPBlacklisted($ip) {
			return $this->checkIPPresent($ip, self::blacklistedTypes());
		}

		public function getTypeIfBlacklistedIP($ip) {
			return $this->getIPType($ip, self::blacklistedTypes());
		}

		public function isFWIPWhitelisted($ip) {
			return $this->checkIPPresent($ip, self::whitelistedTypes());
		}

		public function checkIPPresent($ip, $types) {
			$ip_category = $this->getIPType($ip, $types);
			return isset($ip_category) ? true : false;
		}

		public function getIPType($ip, $types) {
			switch ($types) {
			case self::blacklistedTypes():
				return isset($this->blacklistedIPs[$ip]) ? $this->blacklistedIPs[$ip] : null;
			case self::whitelistedTypes():
				return isset($this->whitelistedIPs[$ip]) ? $this->whitelistedIPs[$ip] : null;
			}
		}
	}
endif;