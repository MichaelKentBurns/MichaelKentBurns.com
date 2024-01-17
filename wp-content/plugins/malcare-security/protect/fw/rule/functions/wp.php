<?php
if (!defined('ABSPATH') && !defined('MCDATAPATH')) exit;

if (!trait_exists('MCProtectFWRuleWPFunc_V542')) :
trait MCProtectFWRuleWPFunc_V542 {
	private function _rf_sanitizeUser() {
		$args = $this->processRuleFunctionParams(
			'sanitizeUser',
			func_num_args(),
			func_get_args(),
			2,
			['string', 'boolean']
		);
		$username = $args[0];
		$strict = $args[1];

		if (!function_exists('sanitize_user') || !MCProtectUtils_V542::haveMupluginsLoaded()) {
			throw new MCProtectRuleError_V542(
				$this->addExState("sanitizeUser: Func sanitize_user doesn't exist.")
			);
		}

		return sanitize_user($username, $strict);
	}

	private function _rf_maybeSerialize() {
		$args = $this->processRuleFunctionParams(
			'maybeSerialize',
			func_num_args(),
			func_get_args(),
			1
		);
		$data = $args[0];

		if (!function_exists('maybe_serialize') || !MCProtectUtils_V542::haveMupluginsLoaded()) {
			throw new MCProtectRuleError_V542(
				$this->addExState("maybeSerialize: Func maybe_serialize doesn't exist.")
			);
		}

		return maybe_serialize($data);
	}

	private function _rf_isUserLoggedIn() {
		$args = $this->processRuleFunctionParams(
			'isUserLoggedIn',
			func_num_args(),
			func_get_args()
		);

		if (!function_exists('is_user_logged_in') || !MCProtectUtils_V542::havePluginsLoaded()) {
			throw new MCProtectRuleError_V542(
				$this->addExState("isUserLoggedIn: Func is_user_logged_in doesn't exist.")
			);
		}

		return is_user_logged_in();
	}

	private function _rf_getCurrentWPUser() {
		$this->processRuleFunctionParams(
			'getCurrentWPUser',
			func_num_args(),
			func_get_args()
		);

		if (!function_exists('wp_get_current_user') || !MCProtectUtils_V542::havePluginsLoaded()) {
			throw new MCProtectRuleError_V542(
				$this->addExState("getCurrentWPUser: Func wp_get_current_user doesn't exist.")
			);
		}

		return MCProtectFWRuleEngine_V542::toAllowedType(wp_get_current_user());
	}

	private function _rf_currentUserCan() {
		$args = $this->processRuleFunctionParams(
			'currentUserCan',
			func_num_args(),
			func_get_args(),
			1,
			['string']
		);
		$capability = $args[0];
		$arg1 = isset($args[1]) ? $args[1] : null;
		$arg2 = isset($args[2]) ? $args[2] : null;

		if (!function_exists('current_user_can') || !MCProtectUtils_V542::havePluginsLoaded()) {
			throw new MCProtectRuleError_V542(
				$this->addExState("currentUserCan: Required funcs doesn't exist.")
			);
		}

		if (isset($arg1)) {
			if (isset($arg2)) {
				return current_user_can($capability, $arg1, $arg2);
			} else {
				return current_user_can($capability, $arg1);
			}
		} else {
			return current_user_can($capability);
		}
	}

	private function _rf_getUserBy() {
		$args = $this->processRuleFunctionParams(
			'getUserBy',
			func_num_args(),
			func_get_args(),
			2,
			['string']
		);
		$field = $args[0];
		$value = $args[1];

		if (!function_exists('get_user_by') || !MCProtectUtils_V542::havePluginsLoaded()) {
			throw new MCProtectRuleError_V542(
				$this->addExState("getUserBy: Func get_user_by doesn't exist")
			);
		}

		if ($field === 'ID' || $field === 'id') {
			if (!is_string($value) && !is_int($value)) {
				throw new MCProtectRuleError_V542(
					$this->addExState("getUserBy: Value must be a valid string or an integer")
				);
			}
		} elseif (!is_string($value)) {
			throw new MCProtectRuleError_V542(
				$this->addExState("getUserBy: Value must be a valid string")
			);
		}

		$user = get_user_by($field, $value);
		if (false === $user) {
			return null;
		}

		return MCProtectFWRuleEngine_V542::toAllowedType($user);
	}

	private function _rf_getCurrentWPUserCapabilities() {
		$args = $this->processRuleFunctionParams(
			'getCurrentWPUserCapabilities',
			func_num_args(),
			func_get_args()
		);

		$user = $this->_rf_getCurrentWPUser();

		if (!array_key_exists("allcaps", $user)) {
			throw new MCProtectRuleError_V542(
				$this->addExState("getCurrentWPUserCapabilities: allcaps doesn't exist in user.")
			);
		}

		return MCProtectFWRuleEngine_V542::toAllowedType($user["allcaps"]);
	}

	private function _rf_getUserCapabilities() {
		$args = $this->processRuleFunctionParams(
			'getUserCapabilities',
			func_num_args(),
			func_get_args(),
			1
		);
		$user_id = $args[0];

		$user = $this->_rf_getUserBy("id", $user_id);
		if (is_null($user)) {
			return array();
		}

		if (!array_key_exists("allcaps", $user)) {
			throw new MCProtectRuleError_V542(
				$this->addExState("getUserCapabilities: allcaps doesn't exist in user.")
			);
		}

		return MCProtectFWRuleEngine_V542::toAllowedType($user["allcaps"]);
	}

	private function _rf_getDefaultUserRole() {
		$args = $this->processRuleFunctionParams(
			'getDefaultUserRole',
			func_num_args(),
			func_get_args()
		);

		return $this->_rf_getOption('default_role', null);
	}

	private function _rf_getOption() {
		$args = $this->processRuleFunctionParams(
			'getOption',
			func_num_args(),
			func_get_args(),
			1,
			['string']
		);
		$option = $args[0];
		$default_value = isset($args[1]) ? $args[1] : false;

		if (!function_exists('get_option') || !MCProtectUtils_V542::haveMupluginsLoaded()) {
			throw new MCProtectRuleError_V542(
				$this->addExState("getOption: Func get_option doesn't exist.")
			);
		}

		return MCProtectFWRuleEngine_V542::toAllowedType(get_option($option, $default_value));
	}

	private function _rf_checkPasswordResetKey() {
		$args = $this->processRuleFunctionParams(
			'checkPasswordResetKey',
			func_num_args(),
			func_get_args(),
			2,
			['string', 'string']
		);
		$key = $args[0];
		$login = $args[1];

		if (!function_exists('check_password_reset_key') || !MCProtectUtils_V542::havePluginsLoaded()) {
			throw new MCProtectRuleError_V542(
				$this->addExState("checkPasswordResetKey: Func check_password_reset_key doesn't exist.")
			);
		}

		$user = check_password_reset_key($key, $login);

		if (is_a($user, "WP_User")) {
			return MCProtectFWRuleEngine_V542::toAllowedType($user);
		}

		return null;
	}

	private function _rf_isActivationKeyValid() {
		$args = $this->processRuleFunctionParams(
			'isActivationKeyValid',
			func_num_args(),
			func_get_args(),
			2,
			['string', 'string']
		);
		$key = $args[0];
		$user_login = $args[1];

		if (is_array($this->_rf_checkPasswordResetKey($key, $user_login))) {
			return true;
		}

		return false;
	}

	private function _rf_hasValidActivationKey() {
		$args = $this->processRuleFunctionParams(
			'hasValidActivationKey',
			func_num_args(),
			func_get_args(),
			2,
			['array', 'string']
		);
		$params = $args[0];
		$user_login = $args[1];

		foreach ($params as $key => $value) {
			if (is_array($value) && $this->_rf_hasValidActivationKey($value, $user_login)) {
				return true;
			} elseif (is_string($value) && $this->_rf_isActivationKeyValid($value, $user_login)) {
				return true;
			}
		}

		return false;
	}

	private function _rf_wpUnslash() {
		$args = $this->processRuleFunctionParams(
			'wpUnslash',
			func_num_args(),
			func_get_args(),
			1
		);
		$value = $args[0];

		if (!function_exists('wp_unslash') || !MCProtectUtils_V542::haveMuPluginsLoaded()) {
			throw new MCProtectRuleError_V542(
				$this->addExState("wpUnslash: Func wp_unslash doesn't exist.")
			);
		}

		if (!is_string($value) && !is_array($value)) {
			throw new MCProtectRuleError_V542(
				$this->addExState("wpUnslash: Value must be a valid string or an array")
			);
		}

		return wp_unslash($value);
	}

	private function _rf_parseResetPassCookie() {
		$args = $this->processRuleFunctionParams(
			'parseResetPassCookie',
			func_num_args(),
			func_get_args()
		);

		if (!defined('COOKIEHASH')) {
			throw new MCProtectRuleError_V542(
				$this->addExState("parseResetPassCookie: COOKIEHASH is not defined.")
			);
		}

		$cookie_name = 'wp-resetpass-' . COOKIEHASH;
		$cookies = $this->_rf_getCookiesV2();
		if (isset($cookies[$cookie_name])) {
			$cookie = $cookies[$cookie_name];
		}

		if (isset($cookie) && is_string($cookie)) {
			$rp_arr = $this->_rf_splitString(':', $this->_rf_wpUnslash($cookie), 2);

			if (is_array($rp_arr) && isset($rp_arr[0]) && is_string($rp_arr[0]) &&
					isset($rp_arr[1]) && is_string($rp_arr[1])) {
				return array("login" => $rp_arr[0], "key" => $rp_arr[1]);
			}
		}

		return array("login" => "", "key" => "");
	}
}
endif;