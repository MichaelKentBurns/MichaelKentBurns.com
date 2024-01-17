<?php
if (!defined('ABSPATH') && !defined('MCDATAPATH')) exit;

if (!trait_exists('MCProtectFWRuleStringFunc_V542')) :
trait MCProtectFWRuleStringFunc_V542 {
	private function _rf_isNumeric() {
		$args = $this->processRuleFunctionParams(
			'isNumeric',
			func_num_args(),
			func_get_args(),
			1
		);
		$value = $args[0];

		return (MCHelper::safePregMatch('/^\d+$/', $value));
	}

	private function _rf_isRegularWord() {
		$args = $this->processRuleFunctionParams(
			'isRegularWord',
			func_num_args(),
			func_get_args(),
			1
		);
		$value = $args[0];

		return (MCHelper::safePregMatch('/^\w+$/', $value));
	}

	private function _rf_isSpecialWord() {
		$args = $this->processRuleFunctionParams(
			'isSpecialWord',
			func_num_args(),
			func_get_args(),
			1
		);
		$value = $args[0];

		return (MCHelper::safePregMatch('/^\S+$/', $value));
	}

	private function _rf_isRegularSentence() {
		$args = $this->processRuleFunctionParams(
			'isRegularSentence',
			func_num_args(),
			func_get_args(),
			1
		);
		$value = $args[0];

		return (MCHelper::safePregMatch('/^[\w\s]+$/', $value));
	}

	private function _rf_isSpecialCharsSentence() {
		$args = $this->processRuleFunctionParams(
			'isSpecialCharsSentence',
			func_num_args(),
			func_get_args(),
			1
		);
		$value = $args[0];

		return (MCHelper::safePregMatch('/^[\w\W]+$/', $value));
	}

	private function _rf_isLink() {
		$args = $this->processRuleFunctionParams(
			'isLink',
			func_num_args(),
			func_get_args(),
			1
		);
		$value = $args[0];

		return (MCHelper::safePregMatch('/^(http|ftp)s?:\/\/\S+$/i', $value));
	}

	private function _rf_isIpv4() {
		$args = $this->processRuleFunctionParams(
			'isIpv4',
			func_num_args(),
			func_get_args(),
			1
		);
		$value = $args[0];

		return (MCHelper::safePregMatch('/^\b((25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])\.){3}(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])\b$/x', $value));
	}

	private function _rf_isEmbededIpv4() {
		$args = $this->processRuleFunctionParams(
			'isEmbededIpv4',
			func_num_args(),
			func_get_args(),
			1
		);
		$value = $args[0];

		return (MCHelper::safePregMatch('/\b((25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])\.){3}(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])\b/x', $value));
	}

	private function _rf_isIpv6() {
		$args = $this->processRuleFunctionParams(
			'isIpv6',
			func_num_args(),
			func_get_args(),
			1
		);
		$value = $args[0];

		return (MCHelper::safePregMatch('/^(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))$/x', $value));
	}

	private function _rf_isEmbededIpv6() {
		$args = $this->processRuleFunctionParams(
			'isEmbededIpv6',
			func_num_args(),
			func_get_args(),
			1
		);
		$value = $args[0];

		return (MCHelper::safePregMatch('/(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))/x', $value));
	}

	private function _rf_isEmail() {
		$args = $this->processRuleFunctionParams(
			'isEmail',
			func_num_args(),
			func_get_args(),
			1
		);
		$value = $args[0];

		return (MCHelper::safePregMatch('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/', $value));
	}

	private function _rf_isEmbededEmail($value) {
		$args = $this->processRuleFunctionParams(
			'isEmbededEmail',
			func_num_args(),
			func_get_args(),
			1
		);
		$value = $args[0];

		return (MCHelper::safePregMatch('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}/', $value));
	}

	private function _rf_isEmbededLink() {
		$args = $this->processRuleFunctionParams(
			'isEmbededLink',
			func_num_args(),
			func_get_args(),
			1
		);
		$value = $args[0];

		return (MCHelper::safePregMatch('/(http|ftp)s?:\/\/\S+$/i', $value));
	}

	private function _rf_isEmbededHtml() {
		$args = $this->processRuleFunctionParams(
			'isEmbededHtml',
			func_num_args(),
			func_get_args(),
			1
		);
		$value = $args[0];

		return (MCHelper::safePregMatch('/<(html|head|title|base|link|meta|style|picture|source|img|iframe|embed|object|param|video|audio|track|map|area|form|label|input|button|select|datalist|optgroup|option|textarea|output|progress|meter|fieldset|legend|script|noscript|template|slot|canvas)/ix', $value));
	}

	private function _rf_isFile() {
		$args = $this->processRuleFunctionParams(
			'isFile',
			func_num_args(),
			func_get_args(),
			1
		);
		$value = $args[0];

		return (MCHelper::safePregMatch('/\.(jpg|jpeg|png|gif|ico|pdf|doc|docx|ppt|pptx|pps|ppsx|odt|xls|zip|gzip|xlsx|psd|mp3|m4a|ogg|wav|mp4|m4v|mov|wmv|avi|mpg|ogv|3gp|3g2|php|html|phtml|js|css)/ix', $value));
	}

	private function _rf_isPathTraversal() {
		$args = $this->processRuleFunctionParams(
			'isPathTraversal',
			func_num_args(),
			func_get_args(),
			1
		);
		$value = $args[0];

		return (MCHelper::safePregMatch('/(?:\.{2}[\/]+)/', $value));
	}

	private function _rf_isPhpEval() {
		$args = $this->processRuleFunctionParams(
			'isPhpEval',
			func_num_args(),
			func_get_args(),
			1
		);
		$value = $args[0];

		return (MCHelper::safePregMatch('/\\b(?i:eval)\\s*\\(\\s*(?i:base64_decode|exec|file_get_contents|gzinflate|passthru|shell_exec|stripslashes|system)\\s*\\(/', $value));
	}

	private function _rf_isSubstring() {
		$args = $this->processRuleFunctionParams(
			'isSubstring',
			func_num_args(),
			func_get_args(),
			2
		);
		$string = $args[0];
		$substring = $args[1];

		return strpos((string) $string, (string) $substring) !== false;
	}

	private function _rf_containsAnySubstring() {
		$args = $this->processRuleFunctionParams(
			'containsAnySubstring',
			func_num_args(),
			func_get_args(),
			2
		);
		$string = $args[0];
		$array_of_substrings = $args[1];

		if (is_array($array_of_substrings)) {
			foreach ($array_of_substrings as $i => $substring) {
				if ($this->_rf_isSubstring($string, $substring)) {
					return true;
				}
			}
		} else {
			throw new MCProtectRuleError_V542(
				$this->addExState("containsAnySubstring: Expects an array of substrings.")
			);
		}

		return false;
	}

	private function _rf_concatString() {
		$args = $this->processRuleFunctionParams(
			'concatString',
			func_num_args(),
			func_get_args(),
			2,
			['string', 'string']
		);
		$source_str = $args[0];
		$str = $args[1];

		return $source_str . $str;
	}

	private function _rf_strPos() {
		$args = $this->processRuleFunctionParams(
			'strPos',
			func_num_args(),
			func_get_args(),
			2,
			['string', 'string']
		);
		$haystack = $args[0];
		$needle = $args[1];
		$offset = isset($args[2]) ? $args[2] : 0;

		if (!is_int($offset)) {
			throw new MCProtectRuleError_V542(
				$this->addExState("strPos: Offset should be an integer")
			);
		}

		return strpos($haystack, $needle, $offset);
	}

	private function _rf_checkStringsForSubstringsPos() {
		$args = $this->processRuleFunctionParams(
			'checkStringsForSubstringsPos',
			func_num_args(),
			func_get_args(),
			3,
			['array', 'array', 'integer']
		);
		$strings = $args[0];
		$sub_strings = $args[1];
		$pos = $args[2];

		foreach ($strings as $string) {
			foreach ($sub_strings as $sub_string) {
				$position = $this->_rf_strPos($string, $sub_string);
				if ($position === $pos) {
					return true;
				}
			}
		}

		return false;
	}

	private function _rf_splitString() {
		$args = $this->processRuleFunctionParams(
			'splitString',
			func_num_args(),
			func_get_args(),
			2,
			['string', 'string']
		);
		$separator = $args[0];
		$str = $args[1];
		$limit = isset($args[2]) ? $args[2] : PHP_INT_MAX;

		if (empty($separator)) {
			throw new MCProtectRuleError_V542(
				$this->addExState("splitString: Separator cannot be empty")
			);
		}

		if (!is_int($limit)) {
			throw new MCProtectRuleError_V542(
				$this->addExState("splitString: Limit should be an integer")
			);
		}

		return explode($separator, $str, $limit);
	}
}
endif;