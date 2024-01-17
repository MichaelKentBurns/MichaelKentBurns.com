<?php
if (!defined('ABSPATH') && !defined('MCDATAPATH')) exit;

if (!trait_exists('MCProtectFWRuleRequestFunc_V542')) :
trait MCProtectFWRuleRequestFunc_V542 {
	private function _rf_getAction() {
		$args = $this->processRuleFunctionParams(
			'getAction',
			func_num_args(),
			func_get_args()
		);

		return $this->request->getAction();
	}

	private function _rf_getPath() {
		$args = $this->processRuleFunctionParams(
			'getPath',
			func_num_args(),
			func_get_args()
		);

		return $this->request->getPath();
	}

	private function _rf_getServerValue() {
		$args = $this->processRuleFunctionParams(
			'getServerValue',
			func_num_args(),
			func_get_args(),
			1
		);
		$key = $args[0];

		return $this->request->getServerValue($key);
	}

	private function _rf_getHeader() {
		$args = $this->processRuleFunctionParams(
			'getHeader',
			func_num_args(),
			func_get_args(),
			1
		);
		$key = $args[0];

		return $this->request->getHeader($key);
	}

	private function _rf_getHeaders() {
		$args = $this->processRuleFunctionParams(
			'getHeaders',
			func_num_args(),
			func_get_args()
		);

		return $this->request->getHeaders();
	}

	private function _rf_getPostParams() {
		$args = $this->processRuleFunctionParams(
			'getPostParams',
			func_num_args(),
			func_get_args()
		);

		if (!empty($args)) {
			return $this->request->getPostParams($args);
		}

		return $this->request->getPostParams();
	}

	private function _rf_getReqMethod() {
		$args = $this->processRuleFunctionParams(
			'getReqMethod',
			func_num_args(),
			func_get_args()
		);

		return $this->request->getMethod();
	}

	private function _rf_getGetParams() {
		$args = $this->processRuleFunctionParams(
			'getGetParams',
			func_num_args(),
			func_get_args()
		);

		if (!empty($args)) {
			return $this->request->getGetParams($args);
		}

		return $this->request->getGetParams();
	}

	private function _rf_getCookies() {
		$args = $this->processRuleFunctionParams(
			'getCookies',
			func_num_args(),
			func_get_args()
		);

		if (!empty($args)) {
			return $this->request->getCookies($args);
		}

		return $this->request->getCookies();
	}

	private function _rf_getFiles() {
		$args = $this->processRuleFunctionParams(
			'getFiles',
			func_num_args(),
			func_get_args()
		);

		if (!empty($args)) {
			return $this->request->getFiles($args);
		}

		return $this->request->getFiles();
	}

	private function _rf_getFileNames() {
		$args = $this->processRuleFunctionParams(
			'getFileNames',
			func_num_args(),
			func_get_args()
		);

		if (!empty($args)) {
			return $this->request->getFileNames($args);
		}

		return $this->request->getFileNames();
	}

	private function _rf_getHost() {
		$args = $this->processRuleFunctionParams(
			'getHost',
			func_num_args(),
			func_get_args()
		);

		return $this->request->host;
	}

	private function _rf_getURI() {
		$args = $this->processRuleFunctionParams(
			'getURI',
			func_num_args(),
			func_get_args()
		);

		return $this->request->getURI();
	}

	private function _rf_getIP() {
		$args = $this->processRuleFunctionParams(
			'getIP',
			func_num_args(),
			func_get_args()
		);

		return $this->request->getIP();
	}

	private function _rf_getTimestamp() {
		$args = $this->processRuleFunctionParams(
			'getTimestamp',
			func_num_args(),
			func_get_args()
		);

		return $this->request->getTimeStamp();
	}

	private function _rf_getAllParams() {
		$args = $this->processRuleFunctionParams(
			'getAllParams',
			func_num_args(),
			func_get_args()
		);

		return $this->request->getAllParams();
	}

	private function _rf_getPostParamValV2() {
		$args = $this->processRuleFunctionParams(
			'getPostParamValV2',
			func_num_args(),
			func_get_args(),
			1
		);
		$key = $args[0];

		return $this->_rf_getArrayVal($this->_rf_getPostParamsV2(), $key);
	}

	private function _rf_digPostParamsV2() {
		$args = $this->processRuleFunctionParams(
			'digPostParamsV2',
			func_num_args(),
			func_get_args(),
			1
		);
		$keys = $args[0];

		return $this->_rf_digArray($this->_rf_getPostParamsV2(), $keys);
	}

	private function _rf_getGetParamValV2() {
		$args = $this->processRuleFunctionParams(
			'getGetParamValV2',
			func_num_args(),
			func_get_args(),
			1
		);
		$key = $args[0];

		return $this->_rf_getArrayVal($this->_rf_getGetParamsV2(), $key);
	}

	private function _rf_digGetParamsV2() {
		$args = $this->processRuleFunctionParams(
			'digGetParamsV2',
			func_num_args(),
			func_get_args(),
			1
		);
		$keys = $args[0];

		return $this->_rf_digArray($this->_rf_getGetParamsV2(), $keys);
	}

	private function _rf_getCookiesV2() {
		$this->processRuleFunctionParams(
			'getCookiesV2',
			func_num_args(),
			func_get_args()
		);

		return $this->request->getCookiesV2();
	}

	private function _rf_getFilesV2() {
		$this->processRuleFunctionParams(
			'getFilesV2',
			func_num_args(),
			func_get_args()
		);

		return $this->request->getFilesV2();
	}

	private function _rf_getFileNamesV2() {
		$this->processRuleFunctionParams(
			'getFileNamesV2',
			func_num_args(),
			func_get_args()
		);

		return $this->request->getFileNamesV2();
	}

	private function _rf_getHeadersV2() {
		$this->processRuleFunctionParams(
			'getHeadersV2',
			func_num_args(),
			func_get_args()
		);

		return $this->request->getHeadersV2();
	}

	private function _rf_getGetParamsV2() {
		$this->processRuleFunctionParams(
			'getGetParamsV2',
			func_num_args(),
			func_get_args()
		);

		return $this->request->getGetParamsV2();
	}

	private function _rf_getPostParamsV2() {
		$this->processRuleFunctionParams(
			'getPostParamsV2',
			func_num_args(),
			func_get_args()
		);

		return $this->request->getPostParamsV2();
	}

	private function _rf_wpUserRoleLevel() {
		$args = $this->processRuleFunctionParams(
			'wpUserRoleLevel',
			func_num_args(),
			func_get_args()
		);

		$wp_user_role_level = isset($this->request->wp_user) ? $this->request->wp_user->role_level : 0;

		return $wp_user_role_level;
	}

	private function _rf_isWPUserRoleLevel() {
		$args = $this->processRuleFunctionParams(
			'isWPUserRoleLevel',
			func_num_args(),
			func_get_args(),
			1,
			["integer"]
		);
		$role_level = $args[0];

		return ($role_level === $this->_rf_wpUserRoleLevel());
	}

	private function _rf_wpUserInfo() {
		$args = $this->processRuleFunctionParams(
			'wpUserInfo',
			func_num_args(),
			func_get_args()
		);

		$wp_user_info = isset($this->request->wp_user) ? $this->request->wp_user->getInfo() : array();

		return $wp_user_info;
	}

	private function _rf_wpUserCapabilities() {
		$args = $this->processRuleFunctionParams(
			'wpUserCapabilities',
			func_num_args(),
			func_get_args()
		);

		if (isset($this->request->wp_user)) {
			return $this->request->wp_user->capability_names;
		}

		return $this->_rf_getCurrentWPUserCapabilities();
	}

	private function _rf_wpUserRole() {
		$args = $this->processRuleFunctionParams(
			'wpUserRole',
			func_num_args(),
			func_get_args()
		);

		$wp_user_role = isset($this->request->wp_user) ? $this->request->wp_user->role : null;

		return $wp_user_role;
	}

	private function _rf_wpUserId() {
		$args = $this->processRuleFunctionParams(
			'wpUserId',
			func_num_args(),
			func_get_args()
		);

		if (isset($this->request->wp_user)) {
			return $this->request->wp_user->id;
		}

		$user = $this->_rf_getCurrentWPUser();

		if (!array_key_exists('ID', $user)) {
			throw new MCProtectRuleError_V542(
				$this->addExState("wpUserId: user's id doesn't exist")
			);
		}

		return $user['ID'];
	}

	private function _rf_isWPUserLoggedIn() {
		$args = $this->processRuleFunctionParams(
			'isWPUserLoggedIn',
			func_num_args(),
			func_get_args()
		);

		$is_user_logged_in = isset($this->request->wp_user) ? $this->request->wp_user->isLoggedIn() :
				$this->_rf_isUserLoggedIn();

		return $is_user_logged_in;
	}

	private function _rf_wpUserCan() {
		$args = $this->processRuleFunctionParams(
			'wpUserCan',
			func_num_args(),
			func_get_args(),
			1,
			['string']
		);
		$capability = $args[0];

		if (isset($this->request->wp_user)) {
			return $this->_rf_inArray($capability, $this->_rf_wpUserCapabilities(), true);
		}

		return $this->_rf_currentUserCan($capability);
	}
}
endif;