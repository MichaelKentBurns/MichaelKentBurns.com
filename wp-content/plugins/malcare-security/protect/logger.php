<?php
if (!defined('ABSPATH') && !defined('MCDATAPATH')) exit;

if (!class_exists('MCProtectLogger')) :
require_once dirname( __FILE__ ) . '/logger/fs.php';
require_once dirname( __FILE__ ) . '/logger/db.php';

class MCProtectLogger {
	private $log_destination;

	const TYPE_FS = 0;
	const TYPE_DB = 1;

	function __construct($name, $type = MCProtectLogger::TYPE_DB) {
		if ($type == MCProtectLogger::TYPE_FS) {
			$this->log_destination = new MCProtectLoggerFS($name);
		} else {
			$this->log_destination = new MCProtectLoggerDB($name);
		}
	}

	public function log($data) {
		$this->log_destination->log($data);
	}
}
endif;