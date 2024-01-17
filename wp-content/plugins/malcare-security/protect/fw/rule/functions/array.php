<?php
if (!defined('ABSPATH') && !defined('MCDATAPATH')) exit;

if (!trait_exists('MCProtectFWRuleArrayFunc_V542')) :
trait MCProtectFWRuleArrayFunc_V542 {
	private function _rf_inArray() {
		$args = $this->processRuleFunctionParams(
			'inArray',
			func_num_args(),
			func_get_args(),
			2
		);
		$element = $args[0];
		$array = $args[1];
		$strict = isset($args[2]) ? $args[2] : false;

		if (!is_array($array)) {
			throw new MCProtectRuleError_V542(
				$this->addExState("inArray: 2nd param is not an array")
			);
		}

		if (!is_bool($strict)) {
			throw new MCProtectRuleError_V542(
				$this->addExState("inArray: 3rd param is not a boolean")
			);
		}

		return in_array($element, $array, $strict);
	}

	private function _rf_recInArray() {
		$args = $this->processRuleFunctionParams(
			'recInArray',
			func_num_args(),
			func_get_args(),
			2
		);
		$element = $args[0];
		$array = $args[1];

		if (is_array($array)) {
			foreach ($array as $key => $value) {
				if (is_array($value)) {
					if ($this->_rf_recInArray($element, $value)) {
						return true;
					}
				} else {
					if ($value === $element) {
						return true;
					}
				}
			}
		} else {
			throw new MCProtectRuleError_V542(
				$this->addExState("recInArray: Expects an array")
			);
		}

		return false;
	}

	private function _rf_arrayKeyExists() {
		$args = $this->processRuleFunctionParams(
			'arrayKeyExists',
			func_num_args(),
			func_get_args(),
			2
		);
		$key = $args[0];
		$array = $args[1];

		if (!is_array($array)) {
			throw new MCProtectRuleError_V542(
				$this->addExState("arrayKeyExists: Array must be of type array")
			);
		} elseif (!is_string($key) && !is_int($key)) {
			throw new MCProtectRuleError_V542(
				$this->addExState("arrayKeyExists: Key must be of type string or int")
			);
		}

		return array_key_exists($key, $array);
	}

	private function _rf_isArrayEmpty() {
		$args = $this->processRuleFunctionParams(
			'isArrayEmpty',
			func_num_args(),
			func_get_args(),
			1,
			['array']
		);
		$array = $args[0];

		return $this->_rf_isEmpty($array);
	}

	private function _rf_getArrayKeys() {
		$args = $this->processRuleFunctionParams(
			'getArrayKeys',
			func_num_args(),
			func_get_args(),
			1,
			['array']
		);
		$array = $args[0];

		return array_keys($array);
	}

	private function _rf_hasAnyArrayKey() {
		$args = $this->processRuleFunctionParams(
			'hasAnyArrayKey',
			func_num_args(),
			func_get_args(),
			2,
			['array', 'array']
		);
		$array = $args[0];
		$keys = $args[1];

		foreach ($keys as $key) {
			if (!is_int($key) && !is_string($key)) {
				throw new MCProtectRuleError_V542(
					$this->addExState("hasAnyArrayKey: Key must be of type string or int")
				);
			}

			if (array_key_exists($key, $array)) {
				return true;
			}
		}

		return false;
	}

	private function _rf_digArray() {
		$args = $this->processRuleFunctionParams(
			'digArray',
			func_num_args(),
			func_get_args(),
			2,
			['array', 'array']
		);
		$array = $args[0];
		$keys = $args[1];

		foreach ($keys as $key) {
			if (!is_int($key) && !is_string($key)) {
				throw new MCProtectRuleError_V542(
					$this->addExState("digArray: Keys must be a valid array of string, or integer type")
				);
			}
		}

		return MCHelper::digArray($array, $keys);
	}

	private function _rf_filterArray() {
		$args = $this->processRuleFunctionParams(
			'filterArray',
			func_num_args(),
			func_get_args(),
			2,
			['array', 'array']
		);
		$array = $args[0];
		$keys = $args[1];

		foreach ($keys as $key) {
			if (!is_int($key) && !is_string($key)) {
				throw new MCProtectRuleError_V542(
					$this->addExState("filterArray: Keys must be a valid array of string, or integer type")
				);
			}
		}

		return MCHelper::filterArray($array, $keys);
	}

	private function _rf_getArrayVal() {
		$args = $this->processRuleFunctionParams(
			'getArrayVal',
			func_num_args(),
			func_get_args(),
			2,
			['array']
		);
		$array = $args[0];
		$key = $args[1];

		if (!is_string($key) && !is_int($key)) {
			throw new MCProtectRuleError_V542(
				$this->addExState("getArrayVal: Key must be a valid string or integer")
			);
		}

		if (array_key_exists($key, $array)) {
			return $array[$key];
		}

		return null;
	}

	private function _rf_arrayIntersection() {
		$args = $this->processRuleFunctionParams(
			'arrayIntersection',
			func_num_args(),
			func_get_args(),
			2,
			['array', 'array']
		);
		$array1 = $args[0];
		$array2 = $args[1];

		return array_intersect($array1, $array2);
	}

	private function _rf_arrayIntersectionAssoc() {
		$args = $this->processRuleFunctionParams(
			'arrayIntersectionAssoc',
			func_num_args(),
			func_get_args(),
			2,
			['array', 'array']
		);
		$array1 = $args[0];
		$array2 = $args[1];

		return array_intersect_assoc($array1, $array2);
	}

	private function _rf_arrayUnion() {
		$args = $this->processRuleFunctionParams(
			'arrayUnion',
			func_num_args(),
			func_get_args(),
			2,
			['array', 'array']
		);
		$array1 = $args[0];
		$array2 = $args[1];

		return ($array1 + $array2);
	}

	private function _rf_arrayMerge() {
		$args = $this->processRuleFunctionParams(
			'arrayMerge',
			func_num_args(),
			func_get_args(),
			2,
			['array', 'array']
		);
		$array1 = $args[0];
		$array2 = $args[1];

		return array_merge($array1, $array2);
	}

	private function _rf_arrayJoin() {
		$args = $this->processRuleFunctionParams(
			'arrayJoin',
			func_num_args(),
			func_get_args(),
			2,
			['string', 'array']
		);
		$separator = $args[0];
		$array = $args[1];

		foreach ($array as $element) {
			if (!is_scalar($element)) {
				throw new MCProtectRuleError_V542(
					$this->addExState("arrayJoin: Array element must be of scalar type")
				);
			}
		}

		return implode($separator, $array);
	}
}
endif;