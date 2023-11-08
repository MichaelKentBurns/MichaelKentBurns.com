<?php
if (!defined('ABSPATH') && !defined('MCDATAPATH')) exit;

if (!class_exists('MCProtectLoggerDB')) :
class MCProtectLoggerDB {
	private $tablename;
	private $bv_tablename;

	const MAXROWCOUNT = 100000;

	function __construct($tablename) {
		$this->tablename = $tablename;
		$this->bv_tablename = MCProtect::$db->getBVTable($tablename);
	}

	public function log($data) {
		if (is_array($data)) {
			if (MCProtect::$db->rowsCount($this->bv_tablename) > MCProtectLoggerDB::MAXROWCOUNT) {
				MCProtect::$db->deleteRowsFromtable($this->tablename, 1);
			}

			MCProtect::$db->replaceIntoBVTable($this->tablename, $data);
		}
	}
}
endif;