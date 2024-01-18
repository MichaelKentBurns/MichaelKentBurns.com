<?php

namespace Lev0\DbAccessAdminer;

const OK = 'OK';
const INC_ADMINER = __DIR__ . '/adminer.phps'; # something other than ".php" to prevent direct invocation
const FMT_CONDUIT = 'conduit%s.php';
const FMT_ENCRYPT = 'encryption%s.php';
const FMT_MESSAGES = 'messages%s.php';

function var_file($filename_fmt, $var = null) {
	if (
		!($cwd = getcwd())
		|| !chdir(__DIR__)
	) {
		return false;
	}

	$okay = false;
	$existing_files = glob(sprintf($filename_fmt, '*'));
	if ($existing_files === false) {
		goto reset_dir;
	}

	if (func_num_args() == 1) { # read
		if (count($existing_files) != 1) {
			goto reset_dir;
		}
		$var = @include reset($existing_files);
		chdir($cwd);
		return $var;
	}

	foreach ($existing_files as $existing_file) {
		if (!unlink($existing_file)) {
			goto reset_dir;
		}
	}
	$filename = sprintf($filename_fmt, uniqid()); # attempt to bypass opcache with unique filenames
	$var = var_export($var, true);
	$okay = file_put_contents(
		$filename
		, '<' . "?php

return $var;

"
		, LOCK_EX
	);
	if ($okay) {
		@chmod($filename, 0600);
	}

	reset_dir:
	return chdir($cwd) && $okay;
}
