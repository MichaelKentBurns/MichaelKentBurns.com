<?php

namespace Lev0\DbAccessAdminer;

const OK = 'OK';
const FMT_CONDUIT = 'conduit%s.php';
const FMT_ENCRYPT = 'encryption%s.php';
const FMT_MESSAGES = 'messages%s.php';
const PERM_DEFAULT = 0600;
const PERM_GROUP_READ = 0640;

function var_file($filename_fmt, $var = null, $looser_perms = false) {
	$existing_files = glob(sprintf("%s/$filename_fmt", quotemeta(__DIR__), '*'));
	if ($existing_files === false) {
		return false;
	}

	if (func_num_args() == 1) { # read
		if (count($existing_files) != 1) {
			return false;
		}
		$var = @include reset($existing_files);
		return $var ?: false;
	}

	foreach ($existing_files as $existing_file) {
		if (!unlink($existing_file)) {
			return false;
		}
	}
	$filename = __DIR__ . '/' . sprintf($filename_fmt, uniqid()); # attempt to bypass opcache with unique filenames
	$var = var_export($var, true);
	$okay = (bool) file_put_contents(
		$filename
		, '<' . "?php

return $var;

"
		, LOCK_EX
	);
	if ($okay) {
		@chmod($filename, $looser_perms ? PERM_GROUP_READ : PERM_DEFAULT);
	}

	return $okay;
}

function wipe_var_files() {
	$qdir = quotemeta(__DIR__);
	foreach ([FMT_CONDUIT, FMT_ENCRYPT, FMT_MESSAGES] as $filename_fmt) {
		$existing_files = glob(sprintf("%s/$filename_fmt", $qdir, '*'));
		if ($existing_files === false) {
			return false;
		}
		foreach ($existing_files as $existing_file) {
			if (!unlink($existing_file)) {
				return false;
			}
		}
	}
	return true;
}
