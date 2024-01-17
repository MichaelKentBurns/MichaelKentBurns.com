<?php
if (!defined('ABSPATH') && !defined('MCDATAPATH')) exit;

if (!class_exists('MCProtectWPUser_V542')) :
class MCProtectWPUser_V542 {
	public $id;
	public $role;
	public $role_level;
	public $capabilities;
	public $capability_names = array();
	public $time;

	const COOKIE_NAME = "mcfw-wp-user-cookie";

	public function __construct($id, $role_level, $capabilities, $time) {
		$this->id = $id;
		$this->role_level = $role_level;
		$this->capabilities = $capabilities;
		$this->time = $time;
	}

	public static function defaultUser() {
		$time = (int) floor(time() / 43200);
		return (new MCProtectWPUser_V542(0, 0, array(), $time));
	}

	public static function _serialize($user) {
		return $user->id . '|' . $user->role_level . '|' . implode(',', $user->capabilities) . '|' .
			$user->time;
	}

	public static function _unserialize($serialized_user) {
		if (!is_string($serialized_user)) {
			return null;
		}

		$user_attrs = explode('|', $serialized_user);
		if (count($user_attrs) !== 4) {
			return null;
		}
		list($id, $role_level, $capabilities, $time) = $user_attrs;
		$capabilities = array_map('intval', explode(',', $capabilities));

		return (new MCProtectWPUser_V542((int) $id, (int) $role_level, $capabilities, (int) $time));
	}

	public function isIdentical($user) {
		return (($this->id === $user->id) && ($this->role_level === $user->role_level) &&
			($this->capabilities === $user->capabilities) && ($this->time === $user->time));
	}

	public function isLoggedIn() {
		return $this->id !== 0;
	}

	public function getInfo() {
		return array(
			'id' => $this->id,
			'role' => $this->role,
			'capabilities' => $this->capability_names
		);
	}
}
endif;