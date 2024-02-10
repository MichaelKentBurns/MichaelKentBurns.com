<?php
if (!defined('ABSPATH') && !defined('MCDATAPATH')) exit;

if (!class_exists('MCProtectLoggerDB_V547')) :
class MCProtectLoggerDB_V547 {
	private $tablename;
	private $bv_tablename;

	const MAXROWCOUNT = 100000;

	function __construct($tablename) {
		$this->tablename = $tablename;
		$this->bv_tablename = MCProtect_V547::$db->getBVTable($tablename);
	}

	public function log($data) {
		if (is_array($data)) {
			if (MCProtect_V547::$db->rowsCount($this->bv_tablename) > MCProtectLoggerDB_V547::MAXROWCOUNT) {
				MCProtect_V547::$db->deleteRowsFromtable($this->tablename, 1);
			}

			MCProtect_V547::$db->replaceIntoBVTable($this->tablename, $data);
		}
	}
}
endif;