<?php
if (!defined('ABSPATH') && !defined('MCDATAPATH')) exit;

if (!class_exists('MCProtectIpstoreFS_V542')) :
	class MCProtectIpstoreFS_V542 {
		private $whitelisted_ips;
		private $blacklisted_ips;

		const IP_TYPE_BLACKLISTED = 0;
		const IP_TYPE_WHITELISTED = 1;

		function __construct() {
			$ip_store_file = MCDATAPATH . MCCONFKEY . '-' . 'mc_ips.conf';
			$ips = MCProtectUtils_V542::parseFile($ip_store_file);
			$this->whitelisted_ips = array_key_exists('whitelisted', $ips) ? $ips['whitelisted'] : array();
			$this->blacklisted_ips = array_key_exists('blacklisted', $ips) ? $ips['blacklisted'] : array();
		}

		public function getTypeIfBlacklistedIP($ip) {
			return $this->getIPType($ip, MCProtectIpstoreFS_V542::IP_TYPE_BLACKLISTED);
		}

		public function isFWIPBlacklisted($ip) {
			return $this->checkIPPresent($ip, MCProtectIpstoreFS_V542::IP_TYPE_BLACKLISTED);
		}

		public function isFWIPWhitelisted($ip) {
			return $this->checkIPPresent($ip, MCProtectIpstoreFS_V542::IP_TYPE_WHITELISTED);
		}

		private function checkIPPresent($ip, $type) {
			$ip_category = $this->getIPType($ip, $type);
			return isset($ip_category) ? true : false;
		}

		#XNOTE: getIPCategory or getIPType?
		private function getIPType($ip, $type) {
			switch ($type) {
			case MCProtectIpstoreFS_V542::IP_TYPE_BLACKLISTED:
				return isset($this->blacklisted_ips[$ip]) ? $this->blacklisted_ips[$ip] : null;
			case MCProtectIpstoreFS_V542::IP_TYPE_WHITELISTED:
				return isset($this->whitelisted_ips[$ip]) ? $this->whitelisted_ips[$ip] : null;
			}
		}
	}
endif;