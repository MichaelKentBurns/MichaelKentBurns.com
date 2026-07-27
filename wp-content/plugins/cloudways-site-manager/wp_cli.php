<?php
if (!defined('ABSPATH')) exit;
if (!class_exists('CWMGRWPCli')) :

	class CWMGRWPCli {
		public $settings;
		public $siteinfo;
		public $bvinfo;
		public $bvapi;

		public function __construct($settings, $bvinfo, $bvsiteinfo, $bvapi) {
			$this->settings = $settings;
			$this->siteinfo = $bvsiteinfo;
			$this->bvinfo = $bvinfo;
			$this->bvapi = $bvapi;
		}

		public function setkey($args, $params) {
			// Support for encoded key
			if (isset($params['key'])) {
				$decoded = base64_decode($params['key']);
				$parts = explode(':', $decoded, 3);
				if (count($parts) === 3) {
					if ($parts[0] !== 'v1') {
						WP_CLI::error('Key version incompatible or invalid key format.');
					}
					if ($parts[1] !== '' && $parts[2] !== '') {
						$pubkey = $parts[1];
						$secret = $parts[2];

						if (strlen($pubkey) < 32 || strlen($secret) < 32) {
							WP_CLI::error('Please enter valid key.');
						}
						CWMGRAccount::addAccount($this->settings, $pubkey, $secret);
						CWMGRAccount::updateApiPublicKey($this->settings, $pubkey);
						if (CWMGRAccount::exists($this->settings, $pubkey)) {
							WP_CLI::success('Key Setup Successfully.');
						} else {
							WP_CLI::error('Key Setup Failed.');
						}
					} else {
						WP_CLI::error('Invalid key format.');
					}
				} else {
					WP_CLI::error('Invalid key format.');
				}
			}
		}

		public function removekey($args, $params) {
			// Support for encoded key (same format as setkey: base64(v1:pubkey:secret))
			if (isset($params['key'])) {
				$decoded = base64_decode($params['key']);
				$parts = explode(':', $decoded, 3);
				if (count($parts) === 3) {
					if ($parts[0] !== 'v1') {
						WP_CLI::error('Key version incompatible or invalid key format.');
					}
					if ($parts[1] !== '') {
						$pubkey = $parts[1];
						if (strlen($pubkey) < 32) {
							WP_CLI::error('Please enter valid key.');
						}

						CWMGRAccount::remove($this->settings, $pubkey);

						if (!CWMGRAccount::exists($this->settings, $pubkey)) {
							WP_CLI::success('Key Removed Successfully.');
						} else {
							WP_CLI::error('Key Removal Failed.');
						}
					} else {
						WP_CLI::error('Invalid key format.');
					}
				} else {
					WP_CLI::error('Invalid key format.');
				}
			}
		}
	}
endif;