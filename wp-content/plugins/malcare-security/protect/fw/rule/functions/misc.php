<?php
if (!defined('ABSPATH') && !defined('MCDATAPATH')) exit;

if (!trait_exists('MCProtectFWRuleMiscFunc_V542')) :
trait MCProtectFWRuleMiscFunc_V542 {
	private function _rf_isTrue() {
		$args = $this->processRuleFunctionParams(
			'isTrue',
			func_num_args(),
			func_get_args(),
			1
		);
		$value = $args[0];

		return ($value === true);
	}

	private function _rf_isFalse() {
		$args = $this->processRuleFunctionParams(
			'isFalse',
			func_num_args(),
			func_get_args(),
			1
		);
		$value = $args[0];

		return ($value === false);
	}

	private function _rf_isFileUpload() {
		$args = $this->processRuleFunctionParams(
			'isFileUpload',
			func_num_args(),
			func_get_args(),
			1
		);
		$value = $args[0];

		$file = $this->_rf_getFiles($value);
		if (is_array($file) && in_array('tmp_name', $file)) {
			return is_uploaded_file($file['tmp_name']);
		}
		
		return false;
	}

	private function _rf_getVarValue() {
		$args = $this->processRuleFunctionParams(
			'getVarValue',
			func_num_args(),
			func_get_args(),
			1,
			['string']
		);
		$name = $args[0];

		if (!array_key_exists($name, $this->variables)) {
			throw new MCProtectRuleError_V542(
				$this->addExState("UndefinedVariableError: " . $name . " is not defined.")
			);
		}

		return $this->variables[$name];
	}

	private function _rf_getVariables() {
		$args = $this->processRuleFunctionParams(
			'getVariables',
			func_num_args(),
			func_get_args()
		);

		return $this->variables;
	}

	private function _rf_digVariables() {
		$args = $this->processRuleFunctionParams(
			'digVariables',
			func_num_args(),
			func_get_args(),
			1,
			['array']
		);
		$keys = $args[0];

		return $this->_rf_digArray($this->variables, $keys);
	}

	private function _rf_isPostRequest() {
		$args = $this->processRuleFunctionParams(
			'isPostRequest',
			func_num_args(),
			func_get_args()
		);

		return $this->_rf_getReqMethod() === "POST";
	}

	private function _rf_match() {
		$args = $this->processRuleFunctionParams(
			'match',
			func_num_args(),
			func_get_args(),
			2
		);
		$pattern = $args[0];
		$subject = $args[1];

		if (is_array($subject)) {
			foreach ($subject as $k => $v) {
				if ($this->_rf_match($pattern, $v)) {
					return true;
				}
			}
			return false;
		}
		$resp = MCHelper::safePregMatch((string) $pattern, (string) $subject);
		if ($resp === false) {
			throw new MCProtectRuleError_V542(
				$this->addExState('BVHelper::safePregMatch' . serialize($subject))
			);
		} elseif ($resp > 0) {
			return true;
		}
		return false;
	}

	private function _rf_notMatch() {
		$args = $this->processRuleFunctionParams(
			'notMatch',
			func_num_args(),
			func_get_args(),
			2
		);
		$pattern = $args[0];
		$subject = $args[1];

		return !$this->_rf_match($pattern, $subject);
	}

	private function _rf_matchCount() {
		$args = $this->processRuleFunctionParams(
			'matchCount',
			func_num_args(),
			func_get_args(),
			2
		);
		$pattern = $args[0];
		$subject = $args[1];

		$count = 0;
		if (is_array($subject)) {
			foreach ($subject as $val) {
				$count += $this->_rf_matchCount($pattern, $val);
			}
			return $count;
		}
		$count = preg_match_all((string) $pattern, (string) $subject, $matches);
		if ($count === false) {
			throw new MCProtectRuleError_V542(
				$this->addExState("preg_match_all: " . serialize($subject))
			);
		}
		return $count;
	}

	private function _rf_maxMatchCount() {
		$args = $this->processRuleFunctionParams(
			'maxMatchCount',
			func_num_args(),
			func_get_args(),
			2
		);
		$pattern = $args[0];
		$subject = $args[1];

		$count = 0;
		if (is_array($subject)) {
			foreach ($subject as $val) {
				$count = max($count, $this->_rf_matchCount($pattern, $val));
			}
			return $count;
		}
		$count = preg_match_all((string) $pattern, (string) $subject, $matches);
		if ($count === false) {
			throw new MCProtectRuleError_V542(
				$this->addExState("preg_match_all: " . serialize($subject))
			);
		}
		return $count;
	}

	private function _rf_equals() {
		$args = $this->processRuleFunctionParams(
			'equals',
			func_num_args(),
			func_get_args(),
			2
		);
		$val = $args[0];
		$subject = $args[1];

		return ($val == $subject);
	}

	private function _rf_notEquals() {
		$args = $this->processRuleFunctionParams(
			'notEquals',
			func_num_args(),
			func_get_args(),
			2
		);
		$val = $args[0];
		$subject = $args[1];

		return !$this->_rf_equals($val, $subject);
	}

	private function _rf_isIdentical() {
		$args = $this->processRuleFunctionParams(
			'isIdentical',
			func_num_args(),
			func_get_args(),
			2
		);
		$val = $args[0];
		$subject = $args[1];

		return ($val === $subject);
	}

	private function _rf_notIdentical() {
		$args = $this->processRuleFunctionParams(
			'notIdentical',
			func_num_args(),
			func_get_args(),
			2
		);
		$val = $args[0];
		$subject = $args[1];

		return !$this->_rf_isIdentical($val, $subject);
	}

	private function _rf_greaterThan() {
		$args = $this->processRuleFunctionParams(
			'greaterThan',
			func_num_args(),
			func_get_args(),
			2
		);
		$val = $args[0];
		$subject = $args[1];

		return ($subject > $val);
	}

	private function _rf_greaterThanEqualTo() {
		$args = $this->processRuleFunctionParams(
			'greaterThanEqualTo',
			func_num_args(),
			func_get_args(),
			2
		);
		$val = $args[0];
		$subject = $args[1];

		return ($subject >= $val);
	}

	private function _rf_lessThan() {
		$args = $this->processRuleFunctionParams(
			'lessThan',
			func_num_args(),
			func_get_args(),
			2
		);
		$val = $args[0];
		$subject = $args[1];

		return ($subject < $val);
	}

	private function _rf_lessThanEqualTo() {
		$args = $this->processRuleFunctionParams(
			'lessThanEqualTo',
			func_num_args(),
			func_get_args(),
			2
		);
		$val = $args[0];
		$subject = $args[1];

		return ($subject <= $val);
	}

	private function _rf_lengthGreaterThan() {
		$args = $this->processRuleFunctionParams(
			'lengthGreaterThan',
			func_num_args(),
			func_get_args(),
			2
		);
		$val = $args[0];
		$subject = $args[1];

		return (strlen((string) $subject) > $val);
	}

	private function _rf_lengthLessThan() {
		$args = $this->processRuleFunctionParams(
			'lengthLessThan',
			func_num_args(),
			func_get_args(),
			2
		);
		$val = $args[0];
		$subject = $args[1];

		return (strlen((string) $subject) < $val);
	}

	private function _rf_md5Equals() {
		$args = $this->processRuleFunctionParams(
			'md5Equals',
			func_num_args(),
			func_get_args(),
			2
		);
		$val = $args[0];
		$subject = $args[1];

		return (md5((string) $subject) === $val);
	}

	private function _rf_matchActions() {
		$args = $this->processRuleFunctionParams(
			'matchActions',
			func_num_args(),
			func_get_args(),
			1
		);
		$actions = $args[0];

		return $this->_rf_inArray($this->_rf_getAction(), $actions);
	}

	private function _rf_compareMultipleSubjects() {
		$args = $this->processRuleFunctionParams(
			'compareMultipleSubjects',
			func_num_args(),
			func_get_args(),
			3
		);
		$func = $args[0];
		$_args = $args[1];
		$subjects = $args[2];

		// TODO
	}

	private function _rf_isset() {
		$args = $this->processRuleFunctionParams(
			'isset',
			func_num_args(),
			func_get_args(),
			1
		);
		$var = $args[0];

		return isset($var);
	}

	private function _rf_getDebugBacktrace() {
		$this->processRuleFunctionParams(
			'getDebugBacktrace',
			func_num_args(),
			func_get_args()
		);

		return debug_backtrace();
	}

	private function _rf_getFilesFromBacktrace() {
		$this->processRuleFunctionParams(
			'getFilesFromBacktrace',
			func_num_args(),
			func_get_args()
		);

		$files = array();
		$backtrace = $this->_rf_getDebugBacktrace();

		foreach ($backtrace as $trace) {
			if (isset($trace['file']) && is_string($trace['file'])) {
				$files[] = $trace['file'];
			}
		}

		return $files;
	}

	private function _rf_isAnyFileInBacktrace() {
		$args = $this->processRuleFunctionParams(
			'isAnyFileInBacktrace',
			func_num_args(),
			func_get_args(),
			1,
			['array']
		);
		$names = $args[0];

		return $this->_rf_checkStringsForSubstringsPos($this->_rf_getFilesFromBacktrace(), $names, 0);
	}

	private function _rf_isEmpty() {
		$args = $this->processRuleFunctionParams(
			'isEmpty',
			func_num_args(),
			func_get_args(),
			1
		);
		$value = $args[0];

		return (empty($value));
	}

	private function _rf_isConstantDefined() {
		$args = $this->processRuleFunctionParams(
			'isConstantDefined',
			func_num_args(),
			func_get_args(),
			1,
			['string']
		);
		$constant_name = $args[0];

		return defined($constant_name);
	}
}
endif;