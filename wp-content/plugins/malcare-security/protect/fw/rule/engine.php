<?php
if (!defined('ABSPATH') && !defined('MCDATAPATH')) exit;

if (!class_exists('MCProtectFWRuleEngine_V542')) :
require_once dirname( __FILE__ ) . '/functions.php';

class MCProtectFWRuleEngine_V542 {
	use MCProtectFWRuleStringFunc_V542;
	use MCProtectFWRuleArrayFunc_V542;
	use MCProtectFWRuleMiscFunc_V542;
	use MCProtectFWRuleRequestFunc_V542;
	use MCProtectFWRuleWPFunc_V542;

	private $request;
	private $variables;

	private $error;
	private $ex_stack = array();
	private $ex_stack_inx = -1;

	const VERSION = 1.1;

	const MAX_DEPTH_TO_ALLOWED_TYPE_FUNC = 8;
	const FUNC_NAME_PREFIX = '_rf_';
	const CONST_NAME_PREFIX = 'MCProtectFWRule_V542::';
	const ALLOWED_EXT_CONSTANTS = [
		'DOING_CRON'
	];

	public function __construct($request = null, $variables = array()) {
		$this->request = $request;
		$this->variables = self::toAllowedType($variables);
	}

	public function hasError() {
		return isset($this->error);
	}

	public function getErrorMessage() {
		if (isset($this->error)) {
			return $this->error->getMessage();
		}
	}

	public function evaluate($rule) {
		try {
			return $this->executeStmt($rule->logic);
		} catch (MCProtectRuleError_V542 $e) {
			$this->error = $e;
		}
	}

	private static function toAllowedType($value, $depth = 1) {
		if ($depth > self::MAX_DEPTH_TO_ALLOWED_TYPE_FUNC) {
			return null;
		}

		switch (gettype($value)) {
		case 'null':
		case 'boolean':
		case 'integer':
		case 'double':
		case 'string':
			return $value;
		case 'array':
			$array_value = [];

			foreach ($value as $key => $val) {
				$array_value[$key] = self::toAllowedType($val, $depth + 1);
			}

			return $array_value;
		case 'object':
			$object_vars = [];

			foreach (get_object_vars($value) as $key => $val) {
				$object_vars[$key] = self::toAllowedType($val, $depth + 1);
			}

			return $object_vars;
		default:
			return null;
		}
	}

	private function pushExStack() {
		array_push($this->ex_stack, array('cur_op' => '-', 'op_cnt' => 0));
		$this->ex_stack_inx += 1;
	}

	private function popExStack() {
		array_pop($this->ex_stack);
		$this->ex_stack_inx -= 1;
	}

	private function updateCurOp($cur_op) {
		if (!empty($this->ex_stack[$this->ex_stack_inx])) {
			$this->ex_stack[$this->ex_stack_inx]['cur_op'] = $cur_op;
		}
	}

	private function incrOpCnt() {
		if (!empty($this->ex_stack[$this->ex_stack_inx])) {
			$this->ex_stack[$this->ex_stack_inx]['op_cnt'] += 1;
		}
	}

	private function addExState($msg) {
		if (!empty($this->ex_stack[$this->ex_stack_inx])) {
			$msg .= " on " . $this->ex_stack[$this->ex_stack_inx]['cur_op'];
			$msg .= " at (" . $this->ex_stack_inx . ":" .
				$this->ex_stack[$this->ex_stack_inx]['op_cnt'] . ").";
		}

		return $msg;
	}

	private function getValue($stmt) {
		if (!is_array($stmt) || empty($stmt["type"])) {
			throw new MCProtectRuleError_V542(
				$this->addExState("InvalidStatementError: Malformed value statement"));
		}

		$this->incrOpCnt();

		switch ($stmt["type"]) {
		case "NUMBER":
			if (!isset($stmt["value"]) || !is_int($stmt["value"])) {
				throw new MCProtectRuleError_V542(
					$this->addExState("TypeError: Value is not a number")
				);
			}

			return $stmt["value"];
		case "STRING":
			if (!isset($stmt["value"]) || !is_string($stmt["value"])) {
				throw new MCProtectRuleError_V542(
					$this->addExState("TypeError: Value is not a string")
				);
			}

			return $stmt["value"];
		case "BOOL":
			if (!isset($stmt["value"]) || !is_bool($stmt["value"])) {
				throw new MCProtectRuleError_V542(
					$this->addExState("TypeError: Value is not a boolean")
				);
			}

			return $stmt["value"];
		case "CONST":
			if (!isset($stmt["value"]) || !is_string($stmt["value"])) {
				throw new MCProtectRuleError_V542(
					$this->addExState("TypeError: Invalid constant name")
				);
			}

			//For backward compatibility.
			$name = str_replace('BVFW::', '', $stmt["value"]);
			if (!in_array($name, self::ALLOWED_EXT_CONSTANTS, true)) {
				$name = self::CONST_NAME_PREFIX . $name;
			}

			if (!defined($name)) {
				throw new MCProtectRuleError_V542(
					$this->addExState("TypeError: Undefined constant" . $stmt["value"])
				);
			}

			return constant($name);
		case "ARRAY":
			if (!isset($stmt["value"]) || !is_array($stmt["value"])) {
				throw new MCProtectRuleError_V542(
					$this->addExState("TypeError: Value is not a array")
				);
			}

			$arr = array();
			foreach ($stmt["value"] as $element) {
				$arr[] = $this->getValue($element);
			}

			return $arr;
		case "HASH_MAP":
			if (!isset($stmt["value"]) || !is_array($stmt["value"])) {
				throw new MCProtectRuleError(
					$this->addExState("TypeError: Value is not a hash map")
				);
			}

			$hash_map = array();
			foreach($stmt["value"] as $key => $value) {
				$hash_map[$key] = $this->getValue($value);
			}

			return $hash_map;
		default:
			return $this->executeStmt($stmt);
		}
	}

	private function executeStmt($stmt) {
		if (!is_array($stmt) || empty($stmt["type"])) {
			throw new MCProtectRuleError_V542(
				$this->addExState("InvalidStatementError: Malformed logic statement")
			);
		}

		$this->pushExStack();
		$this->updateCurOp($stmt["type"]);
		$return_val = null;

		switch ($stmt["type"]) {
		case "AND":
			if (empty($stmt["left_operand"]) || empty($stmt["right_operand"])) {
				throw new MCProtectRuleError_V542(
					$this->addExState("InvalidOperandError: Malformed operand(s)")
				);
			}

			$return_val = $this->getValue($stmt["left_operand"]) && $this->getValue($stmt["right_operand"]);
			break;
		case "OR":
			if (empty($stmt["left_operand"]) || empty($stmt["right_operand"])) {
				throw new MCProtectRuleError_V542(
					$this->addExState("InvalidOperandError: Malformed operand(s)")
				);
			}

			$return_val = $this->getValue($stmt["left_operand"]) || $this->getValue($stmt["right_operand"]);
			break;
		case "NOT":
			if (empty($stmt["value"])) {
				throw new MCProtectRuleError_V542(
					$this->addExState("InvalidOperandError: Malformed operand")
				);
			}

			$return_val = !$this->getValue($stmt["value"]);
			break;
		case "FUNCTION":
			if (empty($stmt["name"]) || !is_string($stmt["name"])) {
				throw new MCProtectRuleError_V542(
					$this->addExState("InvalidFunctionName: Malformed name")
				);
			}

			$name = self::FUNC_NAME_PREFIX . $stmt["name"];
			$handler = array($this, $name);

			if (!is_callable($handler)) {
				throw new MCProtectRuleError_V542(
					$this->addExState("UndefinedFunctionCall: " . $stmt["name"])
				);
			}

			if (!array_key_exists('args', $stmt) || !is_array($stmt['args'])) {
				throw new MCProtectRuleError_V542(
					$this->addExState("InvalidArguments: Malformed args")
				);
			}

			$args = array();
			foreach ($stmt['args'] as $arg_stmt) {
				array_push($args, $this->getValue($arg_stmt));
			}

			$return_val = self::toAllowedType(call_user_func_array($handler, $args));
			break;
		default:
			throw new MCProtectRuleError_V542(
				$this->addExState("UnknownOperation: -")
			);
		}

		$this->popExStack();
		return $return_val;
	}

	private function processRuleFunctionParams($func_name, $args_cnt, $args, $required_params = 0, $param_types = array()) {
		if (($args_cnt < $required_params)) {
			throw new MCProtectRuleError_V542(
				$this->addExState("ArgumentCountError: Too few arguments for " . $func_name)
			);
		}

		foreach ($param_types as $pos => $type) {
			if (!is_int($pos)) {
				throw new MCProtectRuleError_V542(
					$this->addExState("InvalidParamType: " . $pos)
				);
			}

			switch ($type) {
			case "string":
				if (!isset($args[$pos]) || !is_string($args[$pos])) {
					throw new MCProtectRuleError_V542(
						$this->addExState("TypeError: " . $func_name . " param at " . $pos . " is not a string.")
					);
				}
				break;
			case 'integer':
				if (!isset($args[$pos]) || !is_int($args[$pos])) {
					throw new MCProtectRuleError_V542(
						$this->addExState("TypeError: " . $func_name . " param at " . $pos . " is not a integer.")
					);
				}
				break;
			case 'double':
				if (!isset($args[$pos]) || !is_double($args[$pos])) {
					throw new MCProtectRuleError_V542(
						$this->addExState("TypeError: " . $func_name . " param at " . $pos . " is not a double.")
					);
				}
				break;
			case 'boolean':
				if (!isset($args[$pos]) || !is_bool($args[$pos])) {
					throw new MCProtectRuleError_V542(
						$this->addExState("TypeError: " . $func_name . " param at " . $pos . " is not a boolean.")
					);
				}
				break;
			case 'array':
				if (!isset($args[$pos]) || !is_array($args[$pos])) {
					throw new MCProtectRuleError_V542(
						$this->addExState("TypeError: " . $func_name . " param at " . $pos . " is not an array.")
					);
				}
				break;
			case 'mixed':
				break;
			default:
				throw new MCProtectRuleError_V542(
					$this->addExState("InvalidParamTypeError: Invalid type at " . $pos . " for " . $func_name)
				);
			}
		}

		return $args;
	}
}
endif;