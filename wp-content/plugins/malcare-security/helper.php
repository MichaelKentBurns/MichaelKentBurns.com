<?php
if (!defined('ABSPATH') && !defined('MCDATAPATH')) exit;

if (!class_exists('MCHelper')) :
	class MCHelper {
		public static function safePregMatch($pattern, $subject, &$matches = null, $flags = 0, $offset = 0) {
			if (!is_string($pattern) || !is_string($subject)) {
				return false;
			}
			return preg_match($pattern, $subject, $matches, $flags, $offset);
		}

		# XNOTE - The below function assumes valid input
		# $array should be an array and $keys should be an array of string, or integer data
		public static function filterArray($array, $keys) {
			$filteredArray = array();
			foreach ($keys as $key) {
				if (array_key_exists($key, $array)) {
					$filteredArray[$key] = $array[$key];
				}
			}
			return $filteredArray;
		}

		# XNOTE - The below function assumes valid input
		# $array should be an array and $keys should be an array of string, or integer data
		public static function digArray($array, $keys) {
			if (empty($keys)) {
				return null;
			}
			$curr_array = $array;
			foreach ($keys as $key) {
				if (is_array($curr_array) && array_key_exists($key, $curr_array)) {
					$curr_array = $curr_array[$key];
				} else {
					return null;
				}
			}
			return $curr_array;
		}

		public static function arrayKeyFirst($array) {
			if (!function_exists('array_key_first')) {
				foreach ($array as $key => $value) {
					return $key;
				}
				return null;
			}

			return array_key_first($array);
		}
	}
endif;