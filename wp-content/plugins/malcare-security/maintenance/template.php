<?php
$bv_maintenance = plugin_dir_path(__FILE__) . '/wp_maintenance.php';
if (file_exists($bv_maintenance)) {
	require_once $bv_maintenance;
	header("Cache-Control: no-cache, no-store, must-revalidate");
	header("Pragma: no-cache");
	header("Expires: 0");
	header('HTTP/1.1 503 Down For Maintenance');
	echo BVWPMaintenance::$template;
}
?>