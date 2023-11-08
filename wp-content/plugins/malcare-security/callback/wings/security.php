<?php
if (!defined('ABSPATH')) exit;
if (!class_exists('BVSecurityCallback')) :
	class BVSecurityCallback extends BVCallbackBase {
		function getCrontab() {
			$resp = array();

			if (function_exists('exec')) {
				$output = array();
				$retval = -1;
				$execRes = exec('crontab -l', $output, $retval);
				if ($execRes !== false && $execRes !== null) {
					$resp["content"] = implode("\n", $output);
					$resp["status"] = "success";
					$resp["code"] = $retval;
				}
			}
			if (empty($resp) && function_exists('popen')) {
				$handle = popen('crontab -l', 'rb');
				if ($handle) {
					$output = '';
					while (!feof($handle)) {
						$output .= fread($handle, 8192);
					}
					$resp["content"] = $output;
					$resp["status"] = "success";
					pclose($handle);
				} else {
					$resp["status"] = "failed";
				}
			}

			return $resp;
		}

		public function process($request) {
			switch ($request->method) {
			case "gtcrntb":
				$resp = $this->getCrontab();
				break;
			default:
				$resp = false;
			}

			return $resp;
		}
	}
endif;
