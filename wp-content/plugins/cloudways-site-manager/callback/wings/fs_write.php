<?php
if (!defined('ABSPATH')) exit;
if (!class_exists('CWMGRFSWriteCallback')) :

class CWMGRFSWriteCallback extends CWMGRCallbackBase {

	const MEGABYTE = 1048576;
	const FS_WRITE_WING_VERSION = 1.2;
	
	public function __construct() {
	}

	public function removeFiles($files) {
		$result = array();

		foreach($files as $file) {
			$file_result = array();

			if (file_exists($file)) {

				$file_result['status'] = unlink($file);
				if ($file_result['status'] === false) {
					$file_result['error'] = "UNLINK_FAILED";
				}

			} else {
				$file_result['status'] = true;
				$file_result['error'] = "NOT_PRESENT";
			}

			$result[$file] = $file_result;
		}

		$result['status'] = true;
		return $result;
	}

	public function makeDirs($dirs, $permissions = 0777, $recursive = true) {
		$result = array();

		foreach($dirs as $dir) {
			$dir_result = array();

			if (file_exists($dir)) {

				if (is_dir($dir)) {
					$dir_result['status'] = true;
					$dir_result['message'] = "DIR_ALREADY_PRESENT";
				} else {
					$dir_result['status'] = false;
					$dir_result['error'] = "FILE_PRESENT_IN_PLACE_OF_DIR";
				}

			} else {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_mkdir -- Using mkdir() directly as there is no direct suport for recursion
				$dir_result['status'] = mkdir($dir, $permissions, $recursive);
				if ($dir_result['status'] === false) {
					$dir_result['error'] = "MKDIR_FAILED";
				}

			}

			$result[$dir] = $dir_result;
		}

		$result['status'] = true;
		return $result;
	}

	public function removeDirs($dirs) {
		$result = array();

		foreach ($dirs as $dir) {
			$dir_result = array();

			if ((CWMGRWPFileSystem::getInstance()->isDir($dir) === true) && !is_link($dir)) {
				if ($this->isEmptyDir($dir)) {
					$dir_result['status'] = CWMGRWPFileSystem::getInstance()->rmdir($dir);
					if ($dir_result['status'] === false) {
						$dir_result['error'] = "RMDIR_FAILED";
						$fs_error = CWMGRWPFileSystem::getInstance()->checkForErrors();
						if (isset($fs_error)) {
							$dir_result['fs_error'] = $fs_error;
						}
					}
				} else {
					$dir_result['status'] = false;
					$dir_result['error'] = "NOT_EMPTY";
				}
			} else {
				$dir_result['status'] = false;
				$dir_result['error'] = "NOT_DIR";
			}

			$result[$dir] = $dir_result;
		}

		$result['status'] = true;
		return $result;
	}

	public function isEmptyDir($dir) {
		$handle = opendir($dir);

		while (false !== ($entry = readdir($handle))) {
			if ($entry != "." && $entry != "..") {
				closedir($handle);
				return false;
			}
		}
		closedir($handle);

		return true;
	}

	public function doChmod($path_infos) {
		$result = array();

		foreach ($path_infos as $path => $mode) {
			$path_result = array();

			if (CWMGRWPFileSystem::getInstance()->exists($path) === true) {
				$path_result['status'] = CWMGRWPFileSystem::getInstance()->chmod($path, $mode);
				if ($path_result['status'] === false) {
					$path_result['error'] = "CHMOD_FAILED";
					$fs_error = CWMGRWPFileSystem::getInstance()->checkForErrors();
					if (isset($fs_error)) {
						$path_result['fs_error'] = $fs_error;
					}
				}
			} else {
				$path_result['status'] = false;
				$path_result['error'] = "NOT_FOUND";
			}

			$result[$path] = $path_result;
		}

		$result['status'] = true;
		return $result;
	}

	// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fopen
	// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fread
	// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
	// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fclose
	public function concatFiles($ifiles, $ofile, $bsize, $offset) {
		if (($offset !== 0) && (!file_exists($ofile))) {
			return array(
				'status' => false,
				'error' => 'OFILE_NOT_FOUND_BEFORE_CONCAT'
			);
		}

		if (file_exists($ofile) && ($offset !== 0)) {
			$handle = fopen($ofile, 'rb+');
		} else {
			$handle = fopen($ofile, 'wb+');
		}

		if ($handle === false) {
			return array(
				'status' => false,
				'error' => 'FOPEN_FAILED'
			);
		}

		if ($offset !== 0) {
			if (fseek($handle, $offset, SEEK_SET) === -1) {
				return array(
					'status' => false,
					'error' => 'FSEEK_FAILED'
				);
			}
		}

		$total_written = 0;
		foreach($ifiles as $file) {
			$fp = fopen($file, 'rb');
			if ($fp === false) {
				return array(
					'status' => false,
					'error' => "UNABLE_TO_OPEN_TMP_OFILE_FOR_READING"
				);
			}

			while (!feof($fp)) {
				$content = fread($fp, $bsize);
				if ($content === false) {
					return array(
						'status' => false,
						'error' => "UNABLE_TO_READ_INFILE",
						'filename' => $file
					);
				}

				$written = fwrite($handle, $content);
				if ($written === false) {
					return array(
						'status' => false,
						'error' => "UNABLE_TO_WRITE_TO_OFILE",
						'filename' => $file
					);
				}
				$total_written += $written;
			}

			fclose($fp);
		}
		
		$result = array();
		$result['fclose'] = fclose($handle);

		if (file_exists($ofile) && ($total_written != 0)) {
			$result['status'] = true;
			$result['fsize'] = filesize($ofile);
			$result['total_written'] = $total_written;
		} else {
			$result['status'] = false;
			$result['error'] = 'CONCATINATED_FILE_FAILED';
		}

		return $result;
	}
	// phpcs:enable

	public function renameFiles($path_infos) {
		$result = array();

		foreach ($path_infos as $oldpath => $newpath) {
			$action_result = array();

			if (CWMGRWPFileSystem::getInstance()->exists($oldpath)) {
				$action_result['status'] = CWMGRWPFileSystem::getInstance()->move($oldpath, $newpath, true);
				if ($action_result['status'] === false) {
					$action_result['error'] = "RENAME_FAILED";
					$fs_error = CWMGRWPFileSystem::getInstance()->checkForErrors();
					if (isset($fs_error)) {
						$action_result['fs_error'] = $fs_error;
					}
				} else {
					if (function_exists('opcache_invalidate')) {
						$action_result['opcache'] = opcache_invalidate($newpath, true);
					}
				}
			} else {
				$action_result['status'] = false;
				$action_result['error'] = "NOT_FOUND";
			}

			$result[$oldpath] = $action_result;
		}

		$result['status'] = true;
		return $result;
	}

	private function downloadFileViaWpHttpApi($ifile_url, $ofile, $timeout = 60) {
		if (!function_exists('wp_remote_get') || !function_exists('is_wp_error') ||
				!function_exists('wp_remote_retrieve_response_code')) {
			return array('error' => 'WP_HTTP_API_NOT_AVAILABLE');
		}

		if (!is_string($ifile_url) || empty($ifile_url) || !is_string($ofile) || empty($ofile)) {
			return array('error' => 'INVALID_PARAMS');
		}

		$timeout = is_numeric($timeout) ? intval($timeout) : 60;
		if ($timeout <= 0) {
			$timeout = 60;
		}

		if (function_exists('wp_http_validate_url')) {
			$validated = wp_http_validate_url($ifile_url);
			if ($validated === false) {
				return array('error' => 'INVALID_URL');
			}
			$ifile_url = $validated;
		} elseif (function_exists('esc_url_raw')) {
			$ifile_url = esc_url_raw($ifile_url);
			if (empty($ifile_url)) {
				return array('error' => 'INVALID_URL');
			}
		}

		$target_dir = dirname($ofile);
		if (!file_exists($target_dir)) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_mkdir -- Using mkdir() directly as there is no direct support for recursion
			if (!mkdir($target_dir, 0777, true)) {
				return array('error' => 'MKDIR_FAILED_FOR_TARGET');
			}
		}

		$tmp_file = $ofile . '.bvtmp_' . uniqid('', true);

		$args = array(
			'timeout' => $timeout,
			'redirection' => 5,
			'stream' => true,
			'filename' => $tmp_file,
		);

		$response = wp_remote_get($ifile_url, $args);
		if (is_wp_error($response)) {
			if (file_exists($tmp_file)) {
				@unlink($tmp_file); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
			}
			return array('error' => $response->get_error_message());
		}

		$code = wp_remote_retrieve_response_code($response);
		if ($code < 200 || $code >= 300) {
			if (file_exists($tmp_file)) {
				@unlink($tmp_file); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
			}
			return array('error' => 'HTTP_STATUS_' . $code);
		}

		if (!file_exists($tmp_file)) {
			return array('error' => 'TMP_DOWNLOAD_NOT_FOUND');
		}

		if (CWMGRWPFileSystem::getInstance()->move($tmp_file, $ofile, true) === false) {
			if (file_exists($tmp_file)) {
				@unlink($tmp_file); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
			}
			return array('error' => 'MOVE_TMP_DOWNLOAD_FAILED');
		}

		return array();
	}

	public function curlFile($ifile_url, $ofile, $timeout) {
		return $this->downloadFileViaWpHttpApi($ifile_url, $ofile, $timeout);
	}

	public function streamCopyFile($ifile_url, $ofile) {
		return $this->downloadFileViaWpHttpApi($ifile_url, $ofile, 60);
	}

	public function writeContentToFile($content, $ofile) {
		$result = array();

		if (CWMGRWPFileSystem::getInstance()->putContents($ofile, $content) === false) {
			$result['error'] = 'UNABLE_TO_WRITE_TO_TMP_OFILE';
			$fs_error = CWMGRWPFileSystem::getInstance()->checkForErrors();
			if (isset($fs_error)) {
				$result['fs_error'] = $fs_error;
			}
		}

		return $result;
	}

	public function moveUploadedFile($ofile) {
		$result = array();

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing
		if (isset($_FILES['myfile'])) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing -- tmp_name is a path and nonce is ignored here
			$myfile = $_FILES['myfile'];
			$is_upload_ok = false;

			// Validate PHP upload errors manually
			// This approach handles any file type (PHP, ZIP, SQL, etc.) without MIME restrictions
			// Uses WordPress Filesystem API instead of wp_handle_upload() which is designed for media uploads
			switch ($myfile['error']) {
			case UPLOAD_ERR_OK:
				$is_upload_ok = true;
				break;
			case UPLOAD_ERR_NO_FILE:
				$result['error'] = "UPLOADERR_NO_FILE";
				break;
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				$result['error'] = "UPLOADERR_FORM_SIZE";
				break;
			default:
				$result['error'] = "UPLOAD_ERR_UNKNOWN";
			}

			if ($is_upload_ok && !isset($myfile['tmp_name'])) {
				$result['error'] = "MYFILE_TMP_NAME_NOT_FOUND";
				$is_upload_ok = false;
			}

			if ($is_upload_ok) {
				$tmp_name = $myfile['tmp_name'];

				// Ensure target directory exists
				$target_dir = dirname($ofile);
				if (!file_exists($target_dir)) {
					// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_mkdir -- Using mkdir() directly as there is no direct support for recursion
					if (!mkdir($target_dir, 0777, true)) {
						$result['error'] = 'MKDIR_FAILED_FOR_TARGET';
						return $result;
					}
				}

				// Use WordPress Filesystem API to move the uploaded file
				// This is WordPress.org compliant and handles any file type
				if (CWMGRWPFileSystem::getInstance()->move($tmp_name, $ofile, true) === false) {
					$result['error'] = 'MOVE_UPLOAD_FILE_FAILED';
					$fs_error = CWMGRWPFileSystem::getInstance()->checkForErrors();
					if (isset($fs_error)) {
						$result['fs_error'] = $fs_error;
					}
				}
			}

		} else {
			$result['error'] = "FILE_NOT_PRESENT_IN_FILES";
		}

		return $result;
	}


	public function uploadFile($params) {
		$resp = array();
		$ofile = $params['ofile'];

		switch($params['protocol']) {
		case "curl":
			$timeout = isset($params['timeout']) ? $params['timeout'] : 60;
			$ifile_url = isset($params['ifileurl']) ? $params['ifileurl'] : null;

			$resp = $this->curlFile($ifile_url, $ofile, $timeout);
			break;
		case "streamcopy":
			$ifile_url = isset($params['ifileurl']) ? $params['ifileurl'] : null;

			$resp = $this->streamCopyFile($ifile_url, $ofile);
			break;
		case "httpcontenttransfer":
			$resp = $this->writeContentToFile($params['content'], $ofile);
			break;
		case "httpfiletransfer":
			$resp = $this->moveUploadedFile($ofile);
			break;
		default:
			$resp['error'] = "INVALID_PROTOCOL";
		}

		if (isset($resp['error'])) {
			$resp['status'] = false;
		} else {

			if (file_exists($ofile)) {
				$resp['status'] = true;
				$resp['fsize'] = filesize($ofile);
			} else {
				$resp['status'] = false;
				$resp['error'] = "OFILE_NOT_FOUND";
			}

		}

		return $resp;
	}

	public function process($request) {
		$params = $request->params;

		switch ($request->method) {
		case "rmfle":
			$resp = $this->removeFiles($params['files']);
			break;
		case "chmd":
			$resp = $this->doChmod($params['pathinfos']);
			break;
		case "mkdr":
			$resp = $this->makeDirs($params['dirs'], $params['permissions'], $params['recursive']);
			break;
		case "rmdr":
			$resp = $this->removeDirs($params['dirs']);
			break;
		case "renmefle":
			$resp = $this->renameFiles($params['pathinfos']);
			break;
		case "wrtfle":
			$resp = $this->uploadFile($params);
			break;
		case "cncatfls":
			$bsize = (isset($params['bsize'])) ? $params['bsize'] : (8 * CWMGRFSWriteCallback::MEGABYTE);
			$offset = (isset($params['offset'])) ? $params['offset'] : 0;
			$resp = $this->concatFiles($params['infiles'], $params['ofile'], $bsize, $offset);
			break;
		default:
			$resp = false;
		}

		return $resp;
	}
}
endif;