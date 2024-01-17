<?php

if (!defined('ABSPATH')) exit;
if (!class_exists('BVWPMaintenance')) :

	class BVWPMaintenance {
		private $conf = array();
		private $allowed_roles = array();
		public static $template = '';

		public function __construct($conf) {
			$this->conf = $conf;
			$this->initialize_all_params();
		}

		public function initialize_all_params() {
			if (!empty($this->conf) && array_key_exists('allowed_roles', $this->conf)) {
				$this->allowed_roles = $this->conf['allowed_roles'];
			}

			if (!empty($this->conf) && array_key_exists('template', $this->conf)) {
				self::$template = base64_decode($this->conf['template']);
			}
		}

		public function init() {
			if ($this->is_user_allowed() || empty(self::$template)) {
				return false;
			}
			add_filter('template_include', array($this, 'activate_maintenance'), PHP_INT_MAX);
		}

		public function activate_maintenance() {
			return plugin_dir_path( __FILE__ ) . '/template.php';
		}

		public function is_user_allowed() {
			if (!function_exists('wp_get_current_user')) {
				@include_once(ABSPATH . "wp-includes/pluggable.php");
			}
			$current_user = wp_get_current_user();
			$current_user_roles = (array) $current_user->roles;
			if (is_array($current_user_roles) && array_intersect($this->allowed_roles, $current_user_roles)) return true;
			return false;
		}
	}
endif;