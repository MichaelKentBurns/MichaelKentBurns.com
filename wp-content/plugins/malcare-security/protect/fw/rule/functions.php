<?php
/**
 * 	private function _rf_fooBar(string $param1, array $param2, int $param3 = null) {}
 *
 *	private function _rf_fooBar() {
 * 		$args = $this->processRuleFunctionParams(
 * 			'fooBar',
 * 			func_num_args(),
 * 			func_get_args(),
 * 			2, //required argument number.
 * 			['string', 'array'] //argument types
 * 		);
 * 		$param1 = $args[0];
 * 		$param2 = $args[1];
 * 		if(isset($args[2])) {
 * 			$param3 = $args[2];
 * 		}
 *
 *		//function definition;
 *
 * 		return $val;
 *	}
 **/
if (!defined('ABSPATH') && !defined('MCDATAPATH')) exit;

require_once dirname( __FILE__ ) . '/functions/string.php';
require_once dirname( __FILE__ ) . '/functions/array.php';
require_once dirname( __FILE__ ) . '/functions/misc.php';
require_once dirname( __FILE__ ) . '/functions/request.php';
require_once dirname( __FILE__ ) . '/functions/wp.php';