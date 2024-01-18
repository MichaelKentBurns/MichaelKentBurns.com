<?php
/** Adminer - Compact database management
* @link https://www.adminer.org/
* @author Jakub Vrana, https://www.vrana.cz/
* @copyright 2007 Jakub Vrana
* @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
* @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
*/


function adminer_errors($errno, $errstr) {
	return !!preg_match('~^(Trying to access array offset on value of type null|Undefined array key)~', $errstr);
}

error_reporting(6135); // errors and warnings
set_error_handler('adminer_errors', E_WARNING);



// disable filter.default
$filter = !preg_match('~^(unsafe_raw)?$~', ini_get("filter.default"));
if ($filter || ini_get("filter.default_flags")) {
	foreach (array('_GET', '_POST', '_COOKIE', '_SERVER') as $val) {
		$unsafe = filter_input_array(constant("INPUT$val"), FILTER_UNSAFE_RAW);
		if ($unsafe) {
			$$val = $unsafe;
		}
	}
}

if (function_exists("mb_internal_encoding")) {
	mb_internal_encoding("8bit");
}


/** Get database connection
* @return Min_DB
*/
function connection() {
	// can be used in customization, $connection is minified
	global $connection;
	return $connection;
}

/** Get Adminer object
* @return Adminer
*/
function adminer() {
	global $adminer;
	return $adminer;
}

/** Get Adminer version
* @return string
*/
function version() {
	global $VERSION;
	return $VERSION;
}

/** Unescape database identifier
* @param string text inside ``
* @return string
*/
function idf_unescape($idf) {
	if (!preg_match('~^[`\'"]~', $idf)) {
		return $idf;
	}
	$last = substr($idf, -1);
	return str_replace($last . $last, $last, substr($idf, 1, -1));
}

/** Escape string to use inside ''
* @param string
* @return string
*/
function escape_string($val) {
	return substr(q($val), 1, -1);
}

/** Remove non-digits from a string
* @param string
* @return string
*/
function number($val) {
	return preg_replace('~[^0-9]+~', '', $val);
}

/** Get regular expression to match numeric types
* @return string
*/
function number_type() {
	return '((?<!o)int(?!er)|numeric|real|float|double|decimal|money)'; // not point, not interval
}

/** Disable magic_quotes_gpc
* @param array e.g. (&$_GET, &$_POST, &$_COOKIE)
* @param bool whether to leave values as is
* @return null modified in place
*/
function remove_slashes($process, $filter = false) {
	if (function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc()) {
		while (list($key, $val) = each($process)) {
			foreach ($val as $k => $v) {
				unset($process[$key][$k]);
				if (is_array($v)) {
					$process[$key][stripslashes($k)] = $v;
					$process[] = &$process[$key][stripslashes($k)];
				} else {
					$process[$key][stripslashes($k)] = ($filter ? $v : stripslashes($v));
				}
			}
		}
	}
}

/** Escape or unescape string to use inside form []
* @param string
* @param bool
* @return string
*/
function bracket_escape($idf, $back = false) {
	// escape brackets inside name="x[]"
	static $trans = array(':' => ':1', ']' => ':2', '[' => ':3', '"' => ':4');
	return strtr($idf, ($back ? array_flip($trans) : $trans));
}

/** Check if connection has at least the given version
* @param string required version
* @param string required MariaDB version
* @param Min_DB defaults to $connection
* @return bool
*/
function min_version($version, $maria_db = "", $connection2 = null) {
	global $connection;
	if (!$connection2) {
		$connection2 = $connection;
	}
	$server_info = $connection2->server_info;
	if ($maria_db && preg_match('~([\d.]+)-MariaDB~', $server_info, $match)) {
		$server_info = $match[1];
		$version = $maria_db;
	}
	return (version_compare($server_info, $version) >= 0);
}

/** Get connection charset
* @param Min_DB
* @return string
*/
function charset($connection) {
	return (min_version("5.5.3", 0, $connection) ? "utf8mb4" : "utf8"); // SHOW CHARSET would require an extra query
}

/** Return <script> element
* @param string
* @param string
* @return string
*/
function script($source, $trailing = "\n") {
	return "<script" . nonce() . ">$source</script>$trailing";
}

/** Return <script src> element
* @param string
* @return string
*/
function script_src($url) {
	return "<script src='" . h($url) . "'" . nonce() . "></script>\n";
}

/** Get a nonce="" attribute with CSP nonce
* @return string
*/
function nonce() {
	return ' nonce="' . get_nonce() . '"';
}

/** Get a target="_blank" attribute
* @return string
*/
function target_blank() {
	return ' target="_blank" rel="noreferrer noopener"';
}

/** Escape for HTML
* @param string
* @return string
*/
function h($string) {
	return str_replace("\0", "&#0;", htmlspecialchars($string, ENT_QUOTES, 'utf-8'));
}

/** Convert \n to <br>
* @param string
* @return string
*/
function nl_br($string) {
	return str_replace("\n", "<br>", $string); // nl2br() uses XHTML before PHP 5.3
}

/** Generate HTML checkbox
* @param string
* @param string
* @param bool
* @param string
* @param string
* @param string
* @param string
* @return string
*/
function checkbox($name, $value, $checked, $label = "", $onclick = "", $class = "", $labelled_by = "") {
	$return = "<input type='checkbox' name='$name' value='" . h($value) . "'"
		. ($checked ? " checked" : "")
		. ($labelled_by ? " aria-labelledby='$labelled_by'" : "")
		. ">"
		. ($onclick ? script("qsl('input').onclick = function () { $onclick };", "") : "")
	;
	return ($label != "" || $class ? "<label" . ($class ? " class='$class'" : "") . ">$return" . h($label) . "</label>" : $return);
}

/** Generate list of HTML options
* @param array array of strings or arrays (creates optgroup)
* @param mixed
* @param bool always use array keys for value="", otherwise only string keys are used
* @return string
*/
function optionlist($options, $selected = null, $use_keys = false) {
	$return = "";
	foreach ($options as $k => $v) {
		$opts = array($k => $v);
		if (is_array($v)) {
			$return .= '<optgroup label="' . h($k) . '">';
			$opts = $v;
		}
		foreach ($opts as $key => $val) {
			$return .= '<option' . ($use_keys || is_string($key) ? ' value="' . h($key) . '"' : '') . (($use_keys || is_string($key) ? (string) $key : $val) === $selected ? ' selected' : '') . '>' . h($val);
		}
		if (is_array($v)) {
			$return .= '</optgroup>';
		}
	}
	return $return;
}

/** Generate HTML radio list
* @param string
* @param array
* @param string
* @param string true for no onchange, false for radio
* @param string
* @return string
*/
function html_select($name, $options, $value = "", $onchange = true, $labelled_by = "") {
	if ($onchange) {
		return "<select name='" . h($name) . "'"
			. ($labelled_by ? " aria-labelledby='$labelled_by'" : "")
			. ">" . optionlist($options, $value) . "</select>"
			. (is_string($onchange) ? script("qsl('select').onchange = function () { $onchange };", "") : "")
		;
	}
	$return = "";
	foreach ($options as $key => $val) {
		$return .= "<label><input type='radio' name='" . h($name) . "' value='" . h($key) . "'" . ($key == $value ? " checked" : "") . ">" . h($val) . "</label>";
	}
	return $return;
}

/** Generate HTML <select> or <input> if $options are empty
* @param string
* @param array
* @param string
* @param string
* @param string
* @return string
*/
function select_input($attrs, $options, $value = "", $onchange = "", $placeholder = "") {
	$tag = ($options ? "select" : "input");
	return "<$tag$attrs" . ($options
		? "><option value=''>$placeholder" . optionlist($options, $value, true) . "</select>"
		: " size='10' value='" . h($value) . "' placeholder='$placeholder'>"
	) . ($onchange ? script("qsl('$tag').onchange = $onchange;", "") : ""); //! use oninput for input
}

/** Get onclick confirmation
* @param string
* @param string
* @return string
*/
function confirm($message = "", $selector = "qsl('input')") {
	return script("$selector.onclick = function () { return confirm('" . ($message ? js_escape($message) : lang(0)) . "'); };", "");
}

/** Print header for hidden fieldset (close by </div></fieldset>)
* @param string
* @param string
* @param bool
* @return null
*/
function print_fieldset($id, $legend, $visible = false) {
	echo "<fieldset><legend>";
	echo "<a href='#fieldset-$id'>$legend</a>";
	echo script("qsl('a').onclick = partial(toggle, 'fieldset-$id');", "");
	echo "</legend>";
	echo "<div id='fieldset-$id'" . ($visible ? "" : " class='hidden'") . ">\n";
}

/** Return class='active' if $bold is true
* @param bool
* @param string
* @return string
*/
function bold($bold, $class = "") {
	return ($bold ? " class='active $class'" : ($class ? " class='$class'" : ""));
}

/** Generate class for odd rows
* @param string return this for odd rows, empty to reset counter
* @return string
*/
function odd($return = ' class="odd"') {
	static $i = 0;
	if (!$return) { // reset counter
		$i = -1;
	}
	return ($i++ % 2 ? $return : '');
}

/** Escape string for JavaScript apostrophes
* @param string
* @return string
*/
function js_escape($string) {
	return addcslashes($string, "\r\n'\\/"); // slash for <script>
}

/** Print one row in JSON object
* @param string or "" to close the object
* @param string
* @return null
*/
function json_row($key, $val = null) {
	static $first = true;
	if ($first) {
		echo "{";
	}
	if ($key != "") {
		echo ($first ? "" : ",") . "\n\t\"" . addcslashes($key, "\r\n\t\"\\/") . '": ' . ($val !== null ? '"' . addcslashes($val, "\r\n\"\\/") . '"' : 'null');
		$first = false;
	} else {
		echo "\n}\n";
		$first = true;
	}
}

/** Get INI boolean value
* @param string
* @return bool
*/
function ini_bool($ini) {
	$val = ini_get($ini);
	return (preg_match('~^(on|true|yes)$~i', $val) || (int) $val); // boolean values set by php_value are strings
}

/** Check if SID is neccessary
* @return bool
*/
function sid() {
	static $return;
	if ($return === null) { // restart_session() defines SID
		$return = (SID && !($_COOKIE && ini_bool("session.use_cookies"))); // $_COOKIE - don't pass SID with permanent login
	}
	return $return;
}

/** Set password to session
* @param string
* @param string
* @param string
* @param string
* @return null
*/
function set_password($vendor, $server, $username, $password) {
	$_SESSION["pwds"][$vendor][$server][$username] = ($_COOKIE["adminer_key"] && is_string($password)
		? array(encrypt_string($password, $_COOKIE["adminer_key"]))
		: $password
	);
}

/** Get password from session
* @return string or null for missing password or false for expired password
*/
function get_password() {
	$return = get_session("pwds");
	if (is_array($return)) {
		$return = ($_COOKIE["adminer_key"]
			? decrypt_string($return[0], $_COOKIE["adminer_key"])
			: false
		);
	}
	return $return;
}

/** Shortcut for $connection->quote($string)
* @param string
* @return string
*/
function q($string) {
	global $connection;
	return $connection->quote($string);
}

/** Get list of values from database
* @param string
* @param mixed
* @return array
*/
function get_vals($query, $column = 0) {
	global $connection;
	$return = array();
	$result = $connection->query($query);
	if (is_object($result)) {
		while ($row = $result->fetch_row()) {
			$return[] = $row[$column];
		}
	}
	return $return;
}

/** Get keys from first column and values from second
* @param string
* @param Min_DB
* @param bool
* @return array
*/
function get_key_vals($query, $connection2 = null, $set_keys = true) {
	global $connection;
	if (!is_object($connection2)) {
		$connection2 = $connection;
	}
	$return = array();
	$result = $connection2->query($query);
	if (is_object($result)) {
		while ($row = $result->fetch_row()) {
			if ($set_keys) {
				$return[$row[0]] = $row[1];
			} else {
				$return[] = $row[0];
			}
		}
	}
	return $return;
}

/** Get all rows of result
* @param string
* @param Min_DB
* @param string
* @return array of associative arrays
*/
function get_rows($query, $connection2 = null, $error = "<p class='error'>") {
	global $connection;
	$conn = (is_object($connection2) ? $connection2 : $connection);
	$return = array();
	$result = $conn->query($query);
	if (is_object($result)) { // can return true
		while ($row = $result->fetch_assoc()) {
			$return[] = $row;
		}
	} elseif (!$result && !is_object($connection2) && $error && defined("PAGE_HEADER")) {
		echo $error . error() . "\n";
	}
	return $return;
}

/** Find unique identifier of a row
* @param array
* @param array result of indexes()
* @return array or null if there is no unique identifier
*/
function unique_array($row, $indexes) {
	foreach ($indexes as $index) {
		if (preg_match("~PRIMARY|UNIQUE~", $index["type"])) {
			$return = array();
			foreach ($index["columns"] as $key) {
				if (!isset($row[$key])) { // NULL is ambiguous
					continue 2;
				}
				$return[$key] = $row[$key];
			}
			return $return;
		}
	}
}

/** Escape column key used in where()
* @param string
* @return string
*/
function escape_key($key) {
	if (preg_match('(^([\w(]+)(' . str_replace("_", ".*", preg_quote(idf_escape("_"))) . ')([ \w)]+)$)', $key, $match)) { //! columns looking like functions
		return $match[1] . idf_escape(idf_unescape($match[2])) . $match[3]; //! SQL injection
	}
	return idf_escape($key);
}

/** Create SQL condition from parsed query string
* @param array parsed query string
* @param array
* @return string
*/
function where($where, $fields = array()) {
	global $connection, $jush;
	$return = array();
	foreach ((array) $where["where"] as $key => $val) {
		$key = bracket_escape($key, 1); // 1 - back
		$column = escape_key($key);
		$return[] = $column
			. ($jush == "sql" && is_numeric($val) && preg_match('~\.~', $val) ? " LIKE " . q($val) // LIKE because of floats but slow with ints
				: ($jush == "mssql" ? " LIKE " . q(preg_replace('~[_%[]~', '[\0]', $val)) // LIKE because of text
				: " = " . unconvert_field($fields[$key], q($val))
			))
		; //! enum and set
		if ($jush == "sql" && preg_match('~char|text~', $fields[$key]["type"]) && preg_match("~[^ -@]~", $val)) { // not just [a-z] to catch non-ASCII characters
			$return[] = "$column = " . q($val) . " COLLATE " . charset($connection) . "_bin";
		}
	}
	foreach ((array) $where["null"] as $key) {
		$return[] = escape_key($key) . " IS NULL";
	}
	return implode(" AND ", $return);
}

/** Create SQL condition from query string
* @param string
* @param array
* @return string
*/
function where_check($val, $fields = array()) {
	parse_str($val, $check);
	remove_slashes(array(&$check));
	return where($check, $fields);
}

/** Create query string where condition from value
* @param int condition order
* @param string column identifier
* @param string
* @param string
* @return string
*/
function where_link($i, $column, $value, $operator = "=") {
	return "&where%5B$i%5D%5Bcol%5D=" . urlencode($column) . "&where%5B$i%5D%5Bop%5D=" . urlencode(($value !== null ? $operator : "IS NULL")) . "&where%5B$i%5D%5Bval%5D=" . urlencode($value);
}

/** Get select clause for convertible fields
* @param array
* @param array
* @param array
* @return string
*/
function convert_fields($columns, $fields, $select = array()) {
	$return = "";
	foreach ($columns as $key => $val) {
		if ($select && !in_array(idf_escape($key), $select)) {
			continue;
		}
		$as = convert_field($fields[$key]);
		if ($as) {
			$return .= ", $as AS " . idf_escape($key);
		}
	}
	return $return;
}

/** Set cookie valid on current path
* @param string
* @param string
* @param int number of seconds, 0 for session cookie
* @return bool
*/
function cookie($name, $value, $lifetime = 2592000) { // 2592000 - 30 days
	global $HTTPS;
	return header("Set-Cookie: $name=" . urlencode($value)
		. ($lifetime ? "; expires=" . gmdate("D, d M Y H:i:s", time() + $lifetime) . " GMT" : "")
		. "; path=" . preg_replace('~\?.*~', '', $_SERVER["REQUEST_URI"])
		. ($HTTPS ? "; secure" : "")
		. "; HttpOnly; SameSite=lax",
		false);
}

/** Restart stopped session
* @return null
*/
function restart_session() {
	if (!ini_bool("session.use_cookies")) {
		session_start();
	}
}

/** Stop session if possible
* @param bool
* @return null
*/
function stop_session($force = false) {
	$use_cookies = ini_bool("session.use_cookies");
	if (!$use_cookies || $force) {
		session_write_close(); // improves concurrency if a user opens several pages at once, may be restarted later
		if ($use_cookies && @ini_set("session.use_cookies", false) === false) { // @ - may be disabled
			session_start();
		}
	}
}

/** Get session variable for current server
* @param string
* @return mixed
*/
function &get_session($key) {
	return $_SESSION[$key][DRIVER][SERVER][$_GET["username"]];
}

/** Set session variable for current server
* @param string
* @param mixed
* @return mixed
*/
function set_session($key, $val) {
	$_SESSION[$key][DRIVER][SERVER][$_GET["username"]] = $val; // used also in auth.inc.php
}

/** Get authenticated URL
* @param string
* @param string
* @param string
* @param string
* @return string
*/
function auth_url($vendor, $server, $username, $db = null) {
	global $drivers;
	preg_match('~([^?]*)\??(.*)~', remove_from_uri(implode("|", array_keys($drivers)) . "|username|" . ($db !== null ? "db|" : "") . session_name()), $match);
	return "$match[1]?"
		. (sid() ? SID . "&" : "")
		. ($vendor != "server" || $server != "" ? urlencode($vendor) . "=" . urlencode($server) . "&" : "")
		. "username=" . urlencode($username)
		. ($db != "" ? "&db=" . urlencode($db) : "")
		. ($match[2] ? "&$match[2]" : "")
	;
}

/** Find whether it is an AJAX request
* @return bool
*/
function is_ajax() {
	return ($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest");
}

/** Send Location header and exit
* @param string null to only set a message
* @param string
* @return null
*/
function redirect($location, $message = null) {
	if ($message !== null) {
		restart_session();
		$_SESSION["messages"][preg_replace('~^[^?]*~', '', ($location !== null ? $location : $_SERVER["REQUEST_URI"]))][] = $message;
	}
	if ($location !== null) {
		if ($location == "") {
			$location = ".";
		}
		header("Location: $location");
		exit;
	}
}

/** Execute query and redirect if successful
* @param string
* @param string
* @param string
* @param bool
* @param bool
* @param bool
* @param string
* @return bool
*/
function query_redirect($query, $location, $message, $redirect = true, $execute = true, $failed = false, $time = "") {
	global $connection, $error, $adminer;
	if ($execute) {
		$start = microtime(true);
		$failed = !$connection->query($query);
		$time = format_time($start);
	}
	$sql = "";
	if ($query) {
		$sql = $adminer->messageQuery($query, $time, $failed);
	}
	if ($failed) {
		$error = error() . $sql . script("messagesPrint();");
		return false;
	}
	if ($redirect) {
		redirect($location, $message . $sql);
	}
	return true;
}

/** Execute and remember query
* @param string or null to return remembered queries, end with ';' to use DELIMITER
* @return Min_Result or array($queries, $time) if $query = null
*/
function queries($query) {
	global $connection;
	static $queries = array();
	static $start;
	if (!$start) {
		$start = microtime(true);
	}
	if ($query === null) {
		// return executed queries
		return array(implode("\n", $queries), format_time($start));
	}
	$queries[] = (preg_match('~;$~', $query) ? "DELIMITER ;;\n$query;\nDELIMITER " : $query) . ";";
	return $connection->query($query);
}

/** Apply command to all array items
* @param string
* @param array
* @param callback
* @return bool
*/
function apply_queries($query, $tables, $escape = 'table') {
	foreach ($tables as $table) {
		if (!queries("$query " . $escape($table))) {
			return false;
		}
	}
	return true;
}

/** Redirect by remembered queries
* @param string
* @param string
* @param bool
* @return bool
*/
function queries_redirect($location, $message, $redirect) {
	list($queries, $time) = queries(null);
	return query_redirect($queries, $location, $message, $redirect, false, !$redirect, $time);
}

/** Format elapsed time
* @param float output of microtime(true)
* @return string HTML code
*/
function format_time($start) {
	return lang(1, max(0, microtime(true) - $start));
}

/** Get relative REQUEST_URI
* @return string
*/
function relative_uri() {
	return str_replace(":", "%3a", preg_replace('~^[^?]*/([^?]*)~', '\1', $_SERVER["REQUEST_URI"]));
}

/** Remove parameter from query string
* @param string
* @return string
*/
function remove_from_uri($param = "") {
	return substr(preg_replace("~(?<=[?&])($param" . (SID ? "" : "|" . session_name()) . ")=[^&]*&~", '', relative_uri() . "&"), 0, -1);
}

/** Generate page number for pagination
* @param int
* @param int
* @return string
*/
function pagination($page, $current) {
	return " " . ($page == $current
		? $page + 1
		: '<a href="' . h(remove_from_uri("page") . ($page ? "&page=$page" . ($_GET["next"] ? "&next=" . urlencode($_GET["next"]) : "") : "")) . '">' . ($page + 1) . "</a>"
	);
}

/** Get file contents from $_FILES
* @param string
* @param bool
* @return mixed int for error, string otherwise
*/
function get_file($key, $decompress = false) {
	$file = $_FILES[$key];
	if (!$file) {
		return null;
	}
	foreach ($file as $key => $val) {
		$file[$key] = (array) $val;
	}
	$return = '';
	foreach ($file["error"] as $key => $error) {
		if ($error) {
			return $error;
		}
		$name = $file["name"][$key];
		$tmp_name = $file["tmp_name"][$key];
		$content = file_get_contents($decompress && preg_match('~\.gz$~', $name)
			? "compress.zlib://$tmp_name"
			: $tmp_name
		); //! may not be reachable because of open_basedir
		if ($decompress) {
			$start = substr($content, 0, 3);
			if (function_exists("iconv") && preg_match("~^\xFE\xFF|^\xFF\xFE~", $start, $regs)) { // not ternary operator to save memory
				$content = iconv("utf-16", "utf-8", $content);
			} elseif ($start == "\xEF\xBB\xBF") { // UTF-8 BOM
				$content = substr($content, 3);
			}
			$return .= $content . "\n\n";
		} else {
			$return .= $content;
		}
	}
	//! support SQL files not ending with semicolon
	return $return;
}

/** Determine upload error
* @param int
* @return string
*/
function upload_error($error) {
	$max_size = ($error == UPLOAD_ERR_INI_SIZE ? ini_get("upload_max_filesize") : 0); // post_max_size is checked in index.php
	return ($error ? lang(2) . ($max_size ? " " . lang(3, $max_size) : "") : lang(4));
}

/** Create repeat pattern for preg
* @param string
* @param int
* @return string
*/
function repeat_pattern($pattern, $length) {
	// fix for Compilation failed: number too big in {} quantifier
	return str_repeat("$pattern{0,65535}", $length / 65535) . "$pattern{0," . ($length % 65535) . "}"; // can create {0,0} which is OK
}

/** Check whether the string is in UTF-8
* @param string
* @return bool
*/
function is_utf8($val) {
	// don't print control chars except \t\r\n
	return (preg_match('~~u', $val) && !preg_match('~[\0-\x8\xB\xC\xE-\x1F]~', $val));
}

/** Shorten UTF-8 string
* @param string
* @param int
* @param string
* @return string escaped string with appended ...
*/
function shorten_utf8($string, $length = 80, $suffix = "") {
	if (!preg_match("(^(" . repeat_pattern("[\t\r\n -\x{10FFFF}]", $length) . ")($)?)u", $string, $match)) { // ~s causes trash in $match[2] under some PHP versions, (.|\n) is slow
		preg_match("(^(" . repeat_pattern("[\t\r\n -~]", $length) . ")($)?)", $string, $match);
	}
	return h($match[1]) . $suffix . (isset($match[2]) ? "" : "<i>…</i>");
}

/** Format decimal number
* @param int
* @return string
*/
function format_number($val) {
	return strtr(number_format($val, 0, ".", lang(5)), preg_split('~~u', lang(6), -1, PREG_SPLIT_NO_EMPTY));
}

/** Generate friendly URL
* @param string
* @return string
*/
function friendly_url($val) {
	// used for blobs and export
	return preg_replace('~[^a-z0-9_]~i', '-', $val);
}

/** Print hidden fields
* @param array
* @param array
* @param string
* @return bool
*/
function hidden_fields($process, $ignore = array(), $prefix = '') {
	$return = false;
	foreach ($process as $key => $val) {
		if (!in_array($key, $ignore)) {
			if (is_array($val)) {
				hidden_fields($val, array(), $key);
			} else {
				$return = true;
				echo '<input type="hidden" name="' . h($prefix ? $prefix . "[$key]" : $key) . '" value="' . h($val) . '">';
			}
		}
	}
	return $return;
}

/** Print hidden fields for GET forms
* @return null
*/
function hidden_fields_get() {
	echo (sid() ? '<input type="hidden" name="' . session_name() . '" value="' . h(session_id()) . '">' : '');
	echo (SERVER !== null ? '<input type="hidden" name="' . DRIVER . '" value="' . h(SERVER) . '">' : "");
	echo '<input type="hidden" name="username" value="' . h($_GET["username"]) . '">';
}

/** Get status of a single table and fall back to name on error
* @param string
* @param bool
* @return array
*/
function table_status1($table, $fast = false) {
	$return = table_status($table, $fast);
	return ($return ? $return : array("Name" => $table));
}

/** Find out foreign keys for each column
* @param string
* @return array array($col => array())
*/
function column_foreign_keys($table) {
	global $adminer;
	$return = array();
	foreach ($adminer->foreignKeys($table) as $foreign_key) {
		foreach ($foreign_key["source"] as $val) {
			$return[$val][] = $foreign_key;
		}
	}
	return $return;
}

/** Print enum input field
* @param string "radio"|"checkbox"
* @param string
* @param array
* @param mixed int|string|array
* @param string
* @return null
*/
function enum_input($type, $attrs, $field, $value, $empty = null) {
	global $adminer;
	preg_match_all("~'((?:[^']|'')*)'~", $field["length"], $matches);
	$return = ($empty !== null ? "<label><input type='$type'$attrs value='$empty'" . ((is_array($value) ? in_array($empty, $value) : $value === 0) ? " checked" : "") . "><i>" . lang(7) . "</i></label>" : "");
	foreach ($matches[1] as $i => $val) {
		$val = stripcslashes(str_replace("''", "'", $val));
		$checked = (is_int($value) ? $value == $i+1 : (is_array($value) ? in_array($i+1, $value) : $value === $val));
		$return .= " <label><input type='$type'$attrs value='" . ($i+1) . "'" . ($checked ? ' checked' : '') . '>' . h($adminer->editVal($val, $field)) . '</label>';
	}
	return $return;
}

/** Print edit input field
* @param array one field from fields()
* @param mixed
* @param string
* @return null
*/
function input($field, $value, $function) {
	global $types, $adminer, $jush;
	$name = h(bracket_escape($field["field"]));
	echo "<td class='function'>";
	if (is_array($value) && !$function) {
		$args = array($value);
		if (version_compare(PHP_VERSION, 5.4) >= 0) {
			$args[] = JSON_PRETTY_PRINT;
		}
		$value = call_user_func_array('json_encode', $args); //! requires PHP 5.2
		$function = "json";
	}
	$reset = ($jush == "mssql" && $field["auto_increment"]);
	if ($reset && !$_POST["save"]) {
		$function = null;
	}
	$functions = (isset($_GET["select"]) || $reset ? array("orig" => lang(8)) : array()) + $adminer->editFunctions($field);
	$attrs = " name='fields[$name]'";
	if ($field["type"] == "enum") {
		echo h($functions[""]) . "<td>" . $adminer->editInput($_GET["edit"], $field, $attrs, $value);
	} else {
		$has_function = (in_array($function, $functions) || isset($functions[$function]));
		echo (count($functions) > 1
			? "<select name='function[$name]'>" . optionlist($functions, $function === null || $has_function ? $function : "") . "</select>"
				. on_help("getTarget(event).value.replace(/^SQL\$/, '')", 1)
				. script("qsl('select').onchange = functionChange;", "")
			: h(reset($functions))
		) . '<td>';
		$input = $adminer->editInput($_GET["edit"], $field, $attrs, $value); // usage in call is without a table
		if ($input != "") {
			echo $input;
		} elseif (preg_match('~bool~', $field["type"])) {
			echo "<input type='hidden'$attrs value='0'>" .
				"<input type='checkbox'" . (preg_match('~^(1|t|true|y|yes|on)$~i', $value) ? " checked='checked'" : "") . "$attrs value='1'>";
		} elseif ($field["type"] == "set") { //! 64 bits
			preg_match_all("~'((?:[^']|'')*)'~", $field["length"], $matches);
			foreach ($matches[1] as $i => $val) {
				$val = stripcslashes(str_replace("''", "'", $val));
				$checked = (is_int($value) ? ($value >> $i) & 1 : in_array($val, explode(",", $value), true));
				echo " <label><input type='checkbox' name='fields[$name][$i]' value='" . (1 << $i) . "'" . ($checked ? ' checked' : '') . ">" . h($adminer->editVal($val, $field)) . '</label>';
			}
		} elseif (preg_match('~blob|bytea|raw|file~', $field["type"]) && ini_bool("file_uploads")) {
			echo "<input type='file' name='fields-$name'>";
		} elseif (($text = preg_match('~text|lob|memo~i', $field["type"])) || preg_match("~\n~", $value)) {
			if ($text && $jush != "sqlite") {
				$attrs .= " cols='50' rows='12'";
			} else {
				$rows = min(12, substr_count($value, "\n") + 1);
				$attrs .= " cols='30' rows='$rows'" . ($rows == 1 ? " style='height: 1.2em;'" : ""); // 1.2em - line-height
			}
			echo "<textarea$attrs>" . h($value) . '</textarea>';
		} elseif ($function == "json" || preg_match('~^jsonb?$~', $field["type"])) {
			echo "<textarea$attrs cols='50' rows='12' class='jush-js'>" . h($value) . '</textarea>';
		} else {
			// int(3) is only a display hint
			$maxlength = (!preg_match('~int~', $field["type"]) && preg_match('~^(\d+)(,(\d+))?$~', $field["length"], $match) ? ((preg_match("~binary~", $field["type"]) ? 2 : 1) * $match[1] + ($match[3] ? 1 : 0) + ($match[2] && !$field["unsigned"] ? 1 : 0)) : ($types[$field["type"]] ? $types[$field["type"]] + ($field["unsigned"] ? 0 : 1) : 0));
			if ($jush == 'sql' && min_version(5.6) && preg_match('~time~', $field["type"])) {
				$maxlength += 7; // microtime
			}
			// type='date' and type='time' display localized value which may be confusing, type='datetime' uses 'T' as date and time separator
			echo "<input"
				. ((!$has_function || $function === "") && preg_match('~(?<!o)int(?!er)~', $field["type"]) && !preg_match('~\[\]~', $field["full_type"]) ? " type='number'" : "")
				. " value='" . h($value) . "'" . ($maxlength ? " data-maxlength='$maxlength'" : "")
				. (preg_match('~char|binary~', $field["type"]) && $maxlength > 20 ? " size='40'" : "")
				. "$attrs>"
			;
		}
		echo $adminer->editHint($_GET["edit"], $field, $value);
		// skip 'original'
		$first = 0;
		foreach ($functions as $key => $val) {
			if ($key === "" || !$val) {
				break;
			}
			$first++;
		}
		if ($first) {
			echo script("mixin(qsl('td'), {onchange: partial(skipOriginal, $first), oninput: function () { this.onchange(); }});");
		}
	}
}

/** Process edit input field
* @param one field from fields()
* @return string or false to leave the original value
*/
function process_input($field) {
	global $adminer, $driver;
	$idf = bracket_escape($field["field"]);
	$function = $_POST["function"][$idf];
	$value = $_POST["fields"][$idf];
	if ($field["type"] == "enum") {
		if ($value == -1) {
			return false;
		}
		if ($value == "") {
			return "NULL";
		}
		return +$value;
	}
	if ($field["auto_increment"] && $value == "") {
		return null;
	}
	if ($function == "orig") {
		return (preg_match('~^CURRENT_TIMESTAMP~i', $field["on_update"]) ? idf_escape($field["field"]) : false);
	}
	if ($function == "NULL") {
		return "NULL";
	}
	if ($field["type"] == "set") {
		return array_sum((array) $value);
	}
	if ($function == "json") {
		$function = "";
		$value = json_decode($value, true);
		if (!is_array($value)) {
			return false; //! report errors
		}
		return $value;
	}
	if (preg_match('~blob|bytea|raw|file~', $field["type"]) && ini_bool("file_uploads")) {
		$file = get_file("fields-$idf");
		if (!is_string($file)) {
			return false; //! report errors
		}
		return $driver->quoteBinary($file);
	}
	return $adminer->processInput($field, $value, $function);
}

/** Compute fields() from $_POST edit data
* @return array
*/
function fields_from_edit() {
	global $driver;
	$return = array();
	foreach ((array) $_POST["field_keys"] as $key => $val) {
		if ($val != "") {
			$val = bracket_escape($val);
			$_POST["function"][$val] = $_POST["field_funs"][$key];
			$_POST["fields"][$val] = $_POST["field_vals"][$key];
		}
	}
	foreach ((array) $_POST["fields"] as $key => $val) {
		$name = bracket_escape($key, 1); // 1 - back
		$return[$name] = array(
			"field" => $name,
			"privileges" => array("insert" => 1, "update" => 1),
			"null" => 1,
			"auto_increment" => ($key == $driver->primary),
		);
	}
	return $return;
}

/** Print results of search in all tables
* @uses $_GET["where"][0]
* @uses $_POST["tables"]
* @return null
*/
function search_tables() {
	global $adminer, $connection;
	$_GET["where"][0]["val"] = $_POST["query"];
	$sep = "<ul>\n";
	foreach (table_status('', true) as $table => $table_status) {
		$name = $adminer->tableName($table_status);
		if (isset($table_status["Engine"]) && $name != "" && (!$_POST["tables"] || in_array($table, $_POST["tables"]))) {
			$result = $connection->query("SELECT" . limit("1 FROM " . table($table), " WHERE " . implode(" AND ", $adminer->selectSearchProcess(fields($table), array())), 1));
			if (!$result || $result->fetch_row()) {
				$print = "<a href='" . h(ME . "select=" . urlencode($table) . "&where[0][op]=" . urlencode($_GET["where"][0]["op"]) . "&where[0][val]=" . urlencode($_GET["where"][0]["val"])) . "'>$name</a>";
				echo "$sep<li>" . ($result ? $print : "<p class='error'>$print: " . error()) . "\n";
				$sep = "";
			}
		}
	}
	echo ($sep ? "<p class='message'>" . lang(9) : "</ul>") . "\n";
}

/** Send headers for export
* @param string
* @param bool
* @return string extension
*/
function dump_headers($identifier, $multi_table = false) {
	global $adminer;
	$return = $adminer->dumpHeaders($identifier, $multi_table);
	$output = $_POST["output"];
	if ($output != "text") {
		header("Content-Disposition: attachment; filename=" . $adminer->dumpFilename($identifier) . ".$return" . ($output != "file" && preg_match('~^[0-9a-z]+$~', $output) ? ".$output" : ""));
	}
	session_write_close();
	ob_flush();
	flush();
	return $return;
}

/** Print CSV row
* @param array
* @return null
*/
function dump_csv($row) {
	foreach ($row as $key => $val) {
		if (preg_match('~["\n,;\t]|^0|\.\d*0$~', $val) || $val === "") {
			$row[$key] = '"' . str_replace('"', '""', $val) . '"';
		}
	}
	echo implode(($_POST["format"] == "csv" ? "," : ($_POST["format"] == "tsv" ? "\t" : ";")), $row) . "\r\n";
}

/** Apply SQL function
* @param string
* @param string escaped column identifier
* @return string
*/
function apply_sql_function($function, $column) {
	return ($function ? ($function == "unixepoch" ? "DATETIME($column, '$function')" : ($function == "count distinct" ? "COUNT(DISTINCT " : strtoupper("$function(")) . "$column)") : $column);
}

/** Get path of the temporary directory
* @return string
*/
function get_temp_dir() {
	$return = ini_get("upload_tmp_dir"); // session_save_path() may contain other storage path
	if (!$return) {
		if (function_exists('sys_get_temp_dir')) {
			$return = sys_get_temp_dir();
		} else {
			$filename = @tempnam("", ""); // @ - temp directory can be disabled by open_basedir
			if (!$filename) {
				return false;
			}
			$return = dirname($filename);
			unlink($filename);
		}
	}
	return $return;
}

/** Open and exclusively lock a file
* @param string
* @return resource or null for error
*/
function file_open_lock($filename) {
	$fp = @fopen($filename, "r+"); // @ - may not exist
	if (!$fp) { // c+ is available since PHP 5.2.6
		$fp = @fopen($filename, "w"); // @ - may not be writable
		if (!$fp) {
			return;
		}
		chmod($filename, 0660);
	}
	flock($fp, LOCK_EX);
	return $fp;
}

/** Write and unlock a file
* @param resource
* @param string
*/
function file_write_unlock($fp, $data) {
	rewind($fp);
	fwrite($fp, $data);
	ftruncate($fp, strlen($data));
	flock($fp, LOCK_UN);
	fclose($fp);
}

/** Read password from file adminer.key in temporary directory or create one
* @param bool
* @return string or false if the file can not be created
*/
function password_file($create) {
	$filename = get_temp_dir() . "/adminer.key";
	$return = @file_get_contents($filename); // @ - may not exist
	if ($return || !$create) {
		return $return;
	}
	$fp = @fopen($filename, "w"); // @ - can have insufficient rights //! is not atomic
	if ($fp) {
		chmod($filename, 0660);
		$return = rand_string();
		fwrite($fp, $return);
		fclose($fp);
	}
	return $return;
}

/** Get a random string
* @return string 32 hexadecimal characters
*/
function rand_string() {
	return md5(uniqid(mt_rand(), true));
}

/** Format value to use in select
* @param string
* @param string
* @param array
* @param int
* @return string HTML
*/
function select_value($val, $link, $field, $text_length) {
	global $adminer;
	if (is_array($val)) {
		$return = "";
		foreach ($val as $k => $v) {
			$return .= "<tr>"
				. ($val != array_values($val) ? "<th>" . h($k) : "")
				. "<td>" . select_value($v, $link, $field, $text_length)
			;
		}
		return "<table cellspacing='0'>$return</table>";
	}
	if (!$link) {
		$link = $adminer->selectLink($val, $field);
	}
	if ($link === null) {
		if (is_mail($val)) {
			$link = "mailto:$val";
		}
		if (is_url($val)) {
			$link = $val; // IE 11 and all modern browsers hide referrer
		}
	}
	$return = $adminer->editVal($val, $field);
	if ($return !== null) {
		if (!is_utf8($return)) {
			$return = "\0"; // htmlspecialchars of binary data returns an empty string
		} elseif ($text_length != "" && is_shortable($field)) {
			$return = shorten_utf8($return, max(0, +$text_length)); // usage of LEFT() would reduce traffic but complicate query - expected average speedup: .001 s VS .01 s on local network
		} else {
			$return = h($return);
		}
	}
	return $adminer->selectVal($return, $link, $field, $val);
}

/** Check whether the string is e-mail address
* @param string
* @return bool
*/
function is_mail($email) {
	$atom = '[-a-z0-9!#$%&\'*+/=?^_`{|}~]'; // characters of local-name
	$domain = '[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])'; // one domain component
	$pattern = "$atom+(\\.$atom+)*@($domain?\\.)+$domain";
	return is_string($email) && preg_match("(^$pattern(,\\s*$pattern)*\$)i", $email);
}

/** Check whether the string is URL address
* @param string
* @return bool
*/
function is_url($string) {
	$domain = '[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])'; // one domain component //! IDN
	return preg_match("~^(https?)://($domain?\\.)+$domain(:\\d+)?(/.*)?(\\?.*)?(#.*)?\$~i", $string); //! restrict path, query and fragment characters
}

/** Check if field should be shortened
* @param array
* @return bool
*/
function is_shortable($field) {
	return preg_match('~char|text|json|lob|geometry|point|linestring|polygon|string|bytea~', $field["type"]);
}

/** Get query to compute number of found rows
* @param string
* @param array
* @param bool
* @param array
* @return string
*/
function count_rows($table, $where, $is_group, $group) {
	global $jush;
	$query = " FROM " . table($table) . ($where ? " WHERE " . implode(" AND ", $where) : "");
	return ($is_group && ($jush == "sql" || count($group) == 1)
		? "SELECT COUNT(DISTINCT " . implode(", ", $group) . ")$query"
		: "SELECT COUNT(*)" . ($is_group ? " FROM (SELECT 1$query GROUP BY " . implode(", ", $group) . ") x" : $query)
	);
}

/** Run query which can be killed by AJAX call after timing out
* @param string
* @return array of strings
*/
function slow_query($query) {
	global $adminer, $token, $driver;
	$db = $adminer->database();
	$timeout = $adminer->queryTimeout();
	$slow_query = $driver->slowQuery($query, $timeout);
	if (!$slow_query && support("kill") && is_object($connection2 = connect()) && ($db == "" || $connection2->select_db($db))) {
		$kill = $connection2->result(connection_id()); // MySQL and MySQLi can use thread_id but it's not in PDO_MySQL
		?>
<script<?php echo nonce(); ?>>
var timeout = setTimeout(function () {
	ajax('<?php echo js_escape(ME); ?>script=kill', function () {
	}, 'kill=<?php echo $kill; ?>&token=<?php echo $token; ?>');
}, <?php echo 1000 * $timeout; ?>);
</script>
<?php
	} else {
		$connection2 = null;
	}
	ob_flush();
	flush();
	$return = @get_key_vals(($slow_query ? $slow_query : $query), $connection2, false); // @ - may be killed
	if ($connection2) {
		echo script("clearTimeout(timeout);");
		ob_flush();
		flush();
	}
	return $return;
}

/** Generate BREACH resistant CSRF token
* @return string
*/
function get_token() {
	$rand = rand(1, 1e6);
	return ($rand ^ $_SESSION["token"]) . ":$rand";
}

/** Verify if supplied CSRF token is valid
* @return bool
*/
function verify_token() {
	list($token, $rand) = explode(":", $_POST["token"]);
	return ($rand ^ $_SESSION["token"]) == $token;
}

/** Return events to display help on mouse over
* @param string JS expression
* @param bool JS expression
* @return string
*/
function on_help($command, $side = 0) {
	return script("mixin(qsl('select, input'), {onmouseover: function (event) { helpMouseover.call(this, event, $command, $side) }, onmouseout: helpMouseout});", "");
}

/** Print edit data form
* @param string
* @param array
* @param mixed
* @param bool
* @return null
*/
function edit_form($table, $fields, $row, $update) {
	global $adminer, $jush, $token, $error;
	$table_name = $adminer->tableName(table_status1($table, true));
	page_header(
		($update ? lang(10) : lang(11)),
		$error,
		array("select" => array($table, $table_name)),
		$table_name
	);
	$adminer->editRowPrint($table, $fields, $row, $update);
	if ($row === false) {
		echo "<p class='error'>" . lang(12) . "\n";
	}
	?>
<form action="" method="post" enctype="multipart/form-data" id="form">
<?php
	if (!$fields) {
		echo "<p class='error'>" . lang(13) . "\n";
	} else {
		echo "<table cellspacing='0' class='layout'>" . script("qsl('table').onkeydown = editingKeydown;");

		foreach ($fields as $name => $field) {
			echo "<tr><th>" . $adminer->fieldName($field);
			$default = $_GET["set"][bracket_escape($name)];
			if ($default === null) {
				$default = $field["default"];
				if ($field["type"] == "bit" && preg_match("~^b'([01]*)'\$~", $default, $regs)) {
					$default = $regs[1];
				}
			}
			$value = ($row !== null
				? ($row[$name] != "" && $jush == "sql" && preg_match("~enum|set~", $field["type"])
					? (is_array($row[$name]) ? array_sum($row[$name]) : +$row[$name])
					: (is_bool($row[$name]) ? +$row[$name] : $row[$name])
				)
				: (!$update && $field["auto_increment"]
					? ""
					: (isset($_GET["select"]) ? false : $default)
				)
			);
			if (!$_POST["save"] && is_string($value)) {
				$value = $adminer->editVal($value, $field);
			}
			$function = ($_POST["save"]
				? (string) $_POST["function"][$name]
				: ($update && preg_match('~^CURRENT_TIMESTAMP~i', $field["on_update"])
					? "now"
					: ($value === false ? null : ($value !== null ? '' : 'NULL'))
				)
			);
			if (!$_POST && !$update && $value == $field["default"] && preg_match('~^[\w.]+\(~', $value)) {
				$function = "SQL";
			}
			if (preg_match("~time~", $field["type"]) && preg_match('~^CURRENT_TIMESTAMP~i', $value)) {
				$value = "";
				$function = "now";
			}
			input($field, $value, $function);
			echo "\n";
		}
		if (!support("table")) {
			echo "<tr>"
				. "<th><input name='field_keys[]'>"
				. script("qsl('input').oninput = fieldChange;")
				. "<td class='function'>" . html_select("field_funs[]", $adminer->editFunctions(array("null" => isset($_GET["select"]))))
				. "<td><input name='field_vals[]'>"
				. "\n"
			;
		}
		echo "</table>\n";
	}
	echo "<p>\n";
	if ($fields) {
		echo "<input type='submit' value='" . lang(14) . "'>\n";
		if (!isset($_GET["select"])) {
			echo "<input type='submit' name='insert' value='" . ($update
				? lang(15)
				: lang(16)
			) . "' title='Ctrl+Shift+Enter'>\n";
			echo ($update ? script("qsl('input').onclick = function () { return !ajaxForm(this.form, '" . lang(17) . "…', this); };") : "");
		}
	}
	echo ($update ? "<input type='submit' name='delete' value='" . lang(18) . "'>" . confirm() . "\n"
		: ($_POST || !$fields ? "" : script("focus(qsa('td', qs('#form'))[1].firstChild);"))
	);
	if (isset($_GET["select"])) {
		hidden_fields(array("check" => (array) $_POST["check"], "clone" => $_POST["clone"], "all" => $_POST["all"]));
	}
	?>
<input type="hidden" name="referer" value="<?php echo h(isset($_POST["referer"]) ? $_POST["referer"] : $_SERVER["HTTP_REFERER"]); ?>">
<input type="hidden" name="save" value="1">
<input type="hidden" name="token" value="<?php echo $token; ?>">
</form>
<?php
}


// used only in compiled file
if (isset($_GET["file"])) {
	
if ($_SERVER["HTTP_IF_MODIFIED_SINCE"]) {
	header("HTTP/1.1 304 Not Modified");
	exit;
}

header("Expires: " . gmdate("D, d M Y H:i:s", time() + 365*24*60*60) . " GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: immutable");


if ($_GET["file"] == "favicon.ico") {
	header("Content-Type: image/x-icon");
	echo base64_decode('AAABAAEAEBAQAAEABAAoAQAAFgAAACgAAAAQAAAAIAAAAAEABAAAAAAAwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AAAA/wBhTgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAERERAAAAAAETMzEQAAAAATERExAAAAABMRETEAAAAAExERMQAAAAATERExAAAAABMRETEAAAAAEzMzMREREQATERExEhEhABEzMxEhEREAAREREhERIRAAAAARIRESEAAAAAESEiEQAAAAABEREQAAAAAAAAAAD//9UAwP/VAIB/AACAf/AAgH+kAIB/gACAfwAAgH8AAIABAACAAf8AgAH/AMAA/wD+AP8A/wAIAf+B1QD//9UA');
} elseif ($_GET["file"] == "default.css") {
	header("Content-Type: text/css; charset=utf-8");
	echo "/** @author Ondrej Valka, http://valka.info */\nbody { color: #000; background: #fff; font: 90%/1.25 Verdana, Arial, Helvetica, sans-serif; margin: 0; width: -moz-fit-content; width: fit-content; }\na { color: blue; text-decoration: none; }\na:visited { color: navy; }\na:link:hover, a:visited:hover { color: red; text-decoration: underline; }\na.text:hover { text-decoration: none; }\na.jush-help:hover { color: inherit; }\nh1 { font-size: 150%; margin: 0; padding: .8em 1em; border-bottom: 1px solid #999; font-weight: normal; color: #777; background: #eee; }\nh2 { font-size: 150%; margin: 0 0 20px -18px; padding: .8em 1em; border-bottom: 1px solid #000; color: #000; font-weight: normal; background: #ddf; }\nh3 { font-weight: normal; font-size: 130%; margin: 1em 0 0; }\nform { margin: 0; }\ntd table { width: 100%; margin: 0; }\ntable { margin: 1em 20px 0 0; border-collapse: collapse; font-size: 90%; }\ntd, th { border: 1px solid #999; padding: .2em .3em; }\nth { background: #eee; text-align: left; }\nthead th { text-align: center; padding: .2em .5em; }\nthead td, thead th { background: #ddf; } /* position: sticky; causes Firefox to lose borders */\nfieldset { display: inline; vertical-align: top; padding: .5em .8em; margin: .8em .5em 0 0; border: 1px solid #999; }\np { margin: .8em 20px 0 0; }\nimg { vertical-align: middle; border: 0; }\ntd img { max-width: 200px; max-height: 200px; }\ncode { background: #eee; }\ntbody tr:hover td, tbody tr:hover th { background: #eee; }\npre { margin: 1em 0 0; }\npre, textarea { font: 100%/1.25 monospace; }\ninput { vertical-align: middle; }\ninput.default { box-shadow: 1px 1px 1px #777; }\ninput.required { box-shadow: 1px 1px 1px red; }\ninput.maxlength { box-shadow: 1px 1px 1px red; }\ninput.wayoff { left: -1000px; position: absolute; }\n.block { display: block; }\n.version { color: #777; font-size: 67%; }\n.js .hidden, .nojs .jsonly { display: none; }\n.js .column { position: absolute; background: #ddf; padding: .27em 1ex .3em 0; margin-top: -.27em; }\n.nowrap td, .nowrap th, td.nowrap, p.nowrap { white-space: pre; }\n.wrap td { white-space: normal; }\n.error { color: red; background: #fee; }\n.error b { background: #fff; font-weight: normal; }\n.message { color: green; background: #efe; }\n.message table { color: #000; background: #fff; }\n.error, .message { padding: .5em .8em; margin: 1em 20px 0 0; }\n.char { color: #007F00; }\n.date { color: #7F007F; }\n.enum { color: #007F7F; }\n.binary { color: red; }\n.odd td { background: #F5F5F5; }\n.js .checkable .checked td, .js .checkable .checked th { background: #ddf; }\n.time { color: silver; font-size: 70%; }\n.function { text-align: right; }\n.number { text-align: right; }\n.datetime { text-align: right; }\n.type { width: 15ex; width: auto\\9; }\n.options select { width: 20ex; width: auto\\9; }\n.view { font-style: italic; }\n.active { font-weight: bold; }\n.sqlarea { width: 98%; }\n.icon { width: 18px; height: 18px; background-color: navy; }\n.icon:hover { background-color: red; }\n.size { width: 6ex; }\n.help { cursor: help; }\n.footer { position: sticky; bottom: 0; margin-right: -20px; border-top: 20px solid rgba(255, 255, 255, .7); border-image: linear-gradient(rgba(255, 255, 255, .2), #fff) 100% 0; }\n.footer > div { background: #fff; padding: 0 0 .5em; }\n.footer fieldset { margin-top: 0; }\n.links a { white-space: nowrap; margin-right: 20px; }\n.logout { margin-top: .5em; position: absolute; top: 0; right: 0; }\n.loadmore { margin-left: 1ex; }\n/* .edit used in designs */\n#menu { position: absolute; margin: 10px 0 0; padding: 0 0 30px 0; top: 2em; left: 0; width: 19em; }\n#menu p, #logins, #tables { padding: .8em 1em; margin: 0; border-bottom: 1px solid #ccc; }\n#logins li, #tables li { list-style: none; }\n#dbs { overflow: hidden; }\n#logins, #tables { white-space: nowrap; overflow: auto; }\n#logins a, #tables a, #tables span { background: #fff; }\n#content { margin: 2em 0 0 21em; padding: 10px 20px 20px 0; }\n#lang { position: absolute; top: 0; left: 0; line-height: 1.8em; padding: .3em 1em; }\n#breadcrumb { white-space: nowrap; position: absolute; top: 0; left: 21em; background: #eee; height: 2em; line-height: 1.8em; padding: 0 1em; margin: 0 0 0 -18px; }\n#h1 { color: #777; text-decoration: none; font-style: italic; }\n#version { font-size: 67%; color: red; }\n#schema { margin-left: 60px; position: relative; -moz-user-select: none; -webkit-user-select: none; }\n#schema .table { border: 1px solid silver; padding: 0 2px; cursor: move; position: absolute; }\n#schema .references { position: absolute; }\n#help { position: absolute; border: 1px solid #999; background: #eee; padding: 5px; font-family: monospace; z-index: 1; }\n\n.rtl h2 { margin: 0 -18px 20px 0; }\n.rtl p, .rtl table, .rtl .error, .rtl .message { margin: 1em 0 0 20px; }\n.rtl .logout { left: 0; right: auto; }\n.rtl #content { margin: 2em 21em 0 0; padding: 10px 0 20px 20px; }\n.rtl #breadcrumb { left: auto; right: 21em; margin: 0 -18px 0 0; }\n.rtl .pages { left: auto; right: 21em; }\n.rtl input.wayoff { left: auto; right: -1000px; }\n.rtl #lang, .rtl #menu { left: auto; right: 0; }\n\n@media all and (max-device-width: 880px) {\n\t.pages { left: auto; }\n\t#menu { position: static; width: auto; }\n\t#content { margin-left: 10px; }\n\t#lang { position: static; border-top: 1px solid #999; }\n\t#breadcrumb { left: auto; }\n\t.rtl .pages { right: auto; }\n\t.rtl #content { margin-right: 10px; }\n\t.rtl #breadcrumb { right: auto; }\n}\n\n@media print {\n\t#lang, #menu { display: none; }\n\t#content { margin-left: 1em; }\n\t#breadcrumb { left: 1em; }\n\t.nowrap td, .nowrap th, td.nowrap { white-space: normal; }\n}\n.jush { color: black; }\n.jush-htm_com, .jush-com, .jush-com_code, .jush-one, .jush-php_doc, .jush-php_com, .jush-php_one, .jush-js_one, .jush-js_doc { color: gray; }\n.jush-php, .jush-php_new, .jush-php_fun { color: #000033; background-color: #FFF0F0; }\n.jush-php_quo, .jush-quo, .jush-quo_one, .jush-php_eot, .jush-apo, .jush-sql_apo, .jush-sqlite_apo, .jush-sql_quo, .jush-sql_eot { color: green; }\n.jush-php_apo { color: #009F00; }\n.jush-php_quo_var, .jush-php_var, .jush-sql_var { font-style: italic; }\n.jush-php_apo .jush-php_quo_var, .jush-php_apo .jush-php_var { font-style: normal; }\n.jush-php_halt2 { background-color: white; color: black; }\n.jush-tag_css, .jush-att_css .jush-att_quo, .jush-att_css .jush-att_apo, .jush-att_css .jush-att_val { color: black; background-color: #FFFFE0; }\n.jush-tag_js, .jush-att_js .jush-att_quo, .jush-att_js .jush-att_apo, .jush-att_js .jush-att_val, .jush-css_js { color: black; background-color: #F0F0FF; }\n.jush-tag, .jush-xml_tag { color: navy; }\n.jush-att, .jush-xml_att, .jush-att_js, .jush-att_css, .jush-att_http { color: teal; }\n.jush-att_quo, .jush-att_apo, .jush-att_val { color: purple; }\n.jush-ent { color: purple; }\n.jush-js_key, .jush-js_key .jush-quo, .jush-js_key .jush-apo { color: purple; }\n.jush-js_reg { color: navy; }\n.jush-php_sql .jush-php_quo, .jush-php_sql .jush-php_apo,\n.jush-php_sqlite .jush-php_quo, .jush-php_sqlite .jush-php_apo,\n.jush-php_pgsql .jush-php_quo, .jush-php_pgsql .jush-php_apo,\n.jush-php_mssql .jush-php_quo, .jush-php_mssql .jush-php_apo,\n.jush-php_oracle .jush-php_quo, .jush-php_oracle .jush-php_apo\n{ background-color: #FFBBB0; }\n.jush-bac, .jush-php_bac, .jush-bra, .jush-mssql_bra, .jush-sqlite_quo { color: red; }\n.jush-num, .jush-clr { color: #007F7F; }\n\n.jush a { color: navy; }\n.jush a.jush-help { cursor: help; }\n.jush-sql a, .jush-sql_code a, .jush-sqlite a, .jush-pgsql a, .jush-mssql a, .jush-oracle a, .jush-simpledb a { font-weight: bold; }\n.jush-php_sql .jush-php_quo a, .jush-php_sql .jush-php_apo a { font-weight: normal; }\n.jush-tag a, .jush-att a, .jush-apo a, .jush-quo a, .jush-php_apo a, .jush-php_quo a, .jush-php_eot2 a { color: inherit; color: expression(parentNode.currentStyle.color); }\na.jush-custom:link, a.jush-custom:visited { font-weight: normal; color: inherit; color: expression(parentNode.currentStyle.color); }\n\n.jush p { margin: 0; }\n";
} elseif ($_GET["file"] == "functions.js") {
	header("Content-Type: text/javascript; charset=utf-8");
	echo "\n/** Get first element by selector\n* @param string\n* @param [HTMLElement] defaults to document\n* @return HTMLElement\n*/\nfunction qs(selector, context) {\n\treturn (context || document).querySelector(selector);\n}\n\n/** Get last element by selector\n* @param string\n* @param [HTMLElement] defaults to document\n* @return HTMLElement\n*/\nfunction qsl(selector, context) {\n\tvar els = qsa(selector, context);\n\treturn els[els.length - 1];\n}\n\n/** Get all elements by selector\n* @param string\n* @param [HTMLElement] defaults to document\n* @return NodeList\n*/\nfunction qsa(selector, context) {\n\treturn (context || document).querySelectorAll(selector);\n}\n\n/** Return a function calling fn with the next arguments\n* @param function\n* @param ...\n* @return function with preserved this\n*/\nfunction partial(fn) {\n\tvar args = Array.apply(null, arguments).slice(1);\n\treturn function () {\n\t\treturn fn.apply(this, args);\n\t};\n}\n\n/** Return a function calling fn with the first parameter and then the next arguments\n* @param function\n* @param ...\n* @return function with preserved this\n*/\nfunction partialArg(fn) {\n\tvar args = Array.apply(null, arguments);\n\treturn function (arg) {\n\t\targs[0] = arg;\n\t\treturn fn.apply(this, args);\n\t};\n}\n\n/** Assign values from source to target\n* @param Object\n* @param Object\n*/\nfunction mixin(target, source) {\n\tfor (var key in source) {\n\t\ttarget[key] = source[key];\n\t}\n}\n\n/** Add or remove CSS class\n* @param HTMLElement\n* @param string\n* @param [bool]\n*/\nfunction alterClass(el, className, enable) {\n\tif (el) {\n\t\tel.className = el.className.replace(RegExp('(^|\\\\s)' + className + '(\\\\s|\$)'), '\$2') + (enable ? ' ' + className : '');\n\t}\n}\n\n/** Toggle visibility\n* @param string\n* @return boolean false\n*/\nfunction toggle(id) {\n\tvar el = qs('#' + id);\n\tel.className = (el.className == 'hidden' ? '' : 'hidden');\n\treturn false;\n}\n\n/** Set permanent cookie\n* @param string\n* @param number\n* @param string optional\n*/\nfunction cookie(assign, days) {\n\tvar date = new Date();\n\tdate.setDate(date.getDate() + days);\n\tdocument.cookie = assign + '; expires=' + date;\n}\n\n/** Verify current Adminer version\n* @param string\n* @param string own URL base\n* @param string\n*/\nfunction verifyVersion(current, url, token) {\n\tcookie('adminer_version=0', 1);\n\tvar iframe = document.createElement('iframe');\n\tiframe.src = 'https://www.adminer.org/version/?current=' + current;\n\tiframe.frameBorder = 0;\n\tiframe.marginHeight = 0;\n\tiframe.scrolling = 'no';\n\tiframe.style.width = '7ex';\n\tiframe.style.height = '1.25em';\n\tif (window.postMessage && window.addEventListener) {\n\t\tiframe.style.display = 'none';\n\t\taddEventListener('message', function (event) {\n\t\t\tif (event.origin == 'https://www.adminer.org') {\n\t\t\t\tvar match = /version=(.+)/.exec(event.data);\n\t\t\t\tif (match) {\n\t\t\t\t\tcookie('adminer_version=' + match[1], 1);\n\t\t\t\t\tajax(url + 'script=version', function () {\n\t\t\t\t\t}, event.data + '&token=' + token);\n\t\t\t\t}\n\t\t\t}\n\t\t}, false);\n\t}\n\tqs('#version').appendChild(iframe);\n}\n\n/** Get value of select\n* @param HTMLElement <select> or <input>\n* @return string\n*/\nfunction selectValue(select) {\n\tif (!select.selectedIndex) {\n\t\treturn select.value;\n\t}\n\tvar selected = select.options[select.selectedIndex];\n\treturn ((selected.attributes.value || {}).specified ? selected.value : selected.text);\n}\n\n/** Verify if element has a specified tag name\n* @param HTMLElement\n* @param string regular expression\n* @return bool\n*/\nfunction isTag(el, tag) {\n\tvar re = new RegExp('^(' + tag + ')\$', 'i');\n\treturn el && re.test(el.tagName);\n}\n\n/** Get parent node with specified tag name\n* @param HTMLElement\n* @param string regular expression\n* @return HTMLElement\n*/\nfunction parentTag(el, tag) {\n\twhile (el && !isTag(el, tag)) {\n\t\tel = el.parentNode;\n\t}\n\treturn el;\n}\n\n/** Set checked class\n* @param HTMLInputElement\n*/\nfunction trCheck(el) {\n\tvar tr = parentTag(el, 'tr');\n\talterClass(tr, 'checked', el.checked);\n\tif (el.form && el.form['all'] && el.form['all'].onclick) { // Opera treats form.all as document.all\n\t\tel.form['all'].onclick();\n\t}\n}\n\n/** Fill number of selected items\n* @param string\n* @param string\n* @uses thousandsSeparator\n*/\nfunction selectCount(id, count) {\n\tsetHtml(id, (count === '' ? '' : '(' + (count + '').replace(/\\B(?=(\\d{3})+\$)/g, thousandsSeparator) + ')'));\n\tvar el = qs('#' + id);\n\tif (el) {\n\t\tvar inputs = qsa('input', el.parentNode.parentNode);\n\t\tfor (var i = 0; i < inputs.length; i++) {\n\t\t\tvar input = inputs[i];\n\t\t\tif (input.type == 'submit') {\n\t\t\t\tinput.disabled = (count == '0');\n\t\t\t}\n\t\t}\n\t}\n}\n\n/** Check all elements matching given name\n* @param RegExp\n* @this HTMLInputElement\n*/\nfunction formCheck(name) {\n\tvar elems = this.form.elements;\n\tfor (var i=0; i < elems.length; i++) {\n\t\tif (name.test(elems[i].name)) {\n\t\t\telems[i].checked = this.checked;\n\t\t\ttrCheck(elems[i]);\n\t\t}\n\t}\n}\n\n/** Check all rows in <table class=\"checkable\">\n*/\nfunction tableCheck() {\n\tvar inputs = qsa('table.checkable td:first-child input');\n\tfor (var i=0; i < inputs.length; i++) {\n\t\ttrCheck(inputs[i]);\n\t}\n}\n\n/** Uncheck single element\n* @param string\n*/\nfunction formUncheck(id) {\n\tvar el = qs('#' + id);\n\tel.checked = false;\n\ttrCheck(el);\n}\n\n/** Get number of checked elements matching given name\n* @param HTMLInputElement\n* @param RegExp\n* @return number\n*/\nfunction formChecked(el, name) {\n\tvar checked = 0;\n\tvar elems = el.form.elements;\n\tfor (var i=0; i < elems.length; i++) {\n\t\tif (name.test(elems[i].name) && elems[i].checked) {\n\t\t\tchecked++;\n\t\t}\n\t}\n\treturn checked;\n}\n\n/** Select clicked row\n* @param MouseEvent\n* @param [boolean] force click\n*/\nfunction tableClick(event, click) {\n\tvar td = parentTag(getTarget(event), 'td');\n\tvar text;\n\tif (td && (text = td.getAttribute('data-text'))) {\n\t\tif (selectClick.call(td, event, +text, td.getAttribute('data-warning'))) {\n\t\t\treturn;\n\t\t}\n\t}\n\tclick = (click || !window.getSelection || getSelection().isCollapsed);\n\tvar el = getTarget(event);\n\twhile (!isTag(el, 'tr')) {\n\t\tif (isTag(el, 'table|a|input|textarea')) {\n\t\t\tif (el.type != 'checkbox') {\n\t\t\t\treturn;\n\t\t\t}\n\t\t\tcheckboxClick.call(el, event);\n\t\t\tclick = false;\n\t\t}\n\t\tel = el.parentNode;\n\t\tif (!el) { // Ctrl+click on text fields hides the element\n\t\t\treturn;\n\t\t}\n\t}\n\tel = el.firstChild.firstChild;\n\tif (click) {\n\t\tel.checked = !el.checked;\n\t\tel.onclick && el.onclick();\n\t}\n\tif (el.name == 'check[]') {\n\t\tel.form['all'].checked = false;\n\t\tformUncheck('all-page');\n\t}\n\tif (/^(tables|views)\\[\\]\$/.test(el.name)) {\n\t\tformUncheck('check-all');\n\t}\n\ttrCheck(el);\n}\n\nvar lastChecked;\n\n/** Shift-click on checkbox for multiple selection.\n* @param MouseEvent\n* @this HTMLInputElement\n*/\nfunction checkboxClick(event) {\n\tif (!this.name) {\n\t\treturn;\n\t}\n\tif (event.shiftKey && (!lastChecked || lastChecked.name == this.name)) {\n\t\tvar checked = (lastChecked ? lastChecked.checked : true);\n\t\tvar inputs = qsa('input', parentTag(this, 'table'));\n\t\tvar checking = !lastChecked;\n\t\tfor (var i=0; i < inputs.length; i++) {\n\t\t\tvar input = inputs[i];\n\t\t\tif (input.name === this.name) {\n\t\t\t\tif (checking) {\n\t\t\t\t\tinput.checked = checked;\n\t\t\t\t\ttrCheck(input);\n\t\t\t\t}\n\t\t\t\tif (input === this || input === lastChecked) {\n\t\t\t\t\tif (checking) {\n\t\t\t\t\t\tbreak;\n\t\t\t\t\t}\n\t\t\t\t\tchecking = true;\n\t\t\t\t}\n\t\t\t}\n\t\t}\n\t} else {\n\t\tlastChecked = this;\n\t}\n}\n\n/** Set HTML code of an element\n* @param string\n* @param string undefined to set parentNode to empty string\n*/\nfunction setHtml(id, html) {\n\tvar el = qs('[id=\"' + id.replace(/[\\\\\"]/g, '\\\\\$&') + '\"]'); // database name is used as ID\n\tif (el) {\n\t\tif (html == null) {\n\t\t\tel.parentNode.innerHTML = '';\n\t\t} else {\n\t\t\tel.innerHTML = html;\n\t\t}\n\t}\n}\n\n/** Find node position\n* @param Node\n* @return number\n*/\nfunction nodePosition(el) {\n\tvar pos = 0;\n\twhile (el = el.previousSibling) {\n\t\tpos++;\n\t}\n\treturn pos;\n}\n\n/** Go to the specified page\n* @param string\n* @param string\n*/\nfunction pageClick(href, page) {\n\tif (!isNaN(page) && page) {\n\t\tlocation.href = href + (page != 1 ? '&page=' + (page - 1) : '');\n\t}\n}\n\n\n\n/** Display items in menu\n* @param MouseEvent\n* @this HTMLElement\n*/\nfunction menuOver(event) {\n\tvar a = getTarget(event);\n\tif (isTag(a, 'a|span') && a.offsetLeft + a.offsetWidth > a.parentNode.offsetWidth - 15) { // 15 - ellipsis\n\t\tthis.style.overflow = 'visible';\n\t}\n}\n\n/** Hide items in menu\n* @this HTMLElement\n*/\nfunction menuOut() {\n\tthis.style.overflow = 'auto';\n}\n\n\n\n/** Add row in select fieldset\n* @this HTMLSelectElement\n*/\nfunction selectAddRow() {\n\tvar field = this;\n\tvar row = cloneNode(field.parentNode);\n\tfield.onchange = selectFieldChange;\n\tfield.onchange();\n\tvar selects = qsa('select', row);\n\tfor (var i=0; i < selects.length; i++) {\n\t\tselects[i].name = selects[i].name.replace(/[a-z]\\[\\d+/, '\$&1');\n\t\tselects[i].selectedIndex = 0;\n\t}\n\tvar inputs = qsa('input', row);\n\tfor (var i=0; i < inputs.length; i++) {\n\t\tinputs[i].name = inputs[i].name.replace(/[a-z]\\[\\d+/, '\$&1');\n\t\tinputs[i].className = '';\n\t\tif (inputs[i].type == 'checkbox') {\n\t\t\tinputs[i].checked = false;\n\t\t} else {\n\t\t\tinputs[i].value = '';\n\t\t}\n\t}\n\tfield.parentNode.parentNode.appendChild(row);\n}\n\n/** Prevent onsearch handler on Enter\n* @param KeyboardEvent\n* @this HTMLInputElement\n*/\nfunction selectSearchKeydown(event) {\n\tif (event.keyCode == 13 || event.keyCode == 10) {\n\t\tthis.onsearch = function () {\n\t\t};\n\t}\n}\n\n/** Clear column name after resetting search\n* @this HTMLInputElement\n*/\nfunction selectSearchSearch() {\n\tif (!this.value) {\n\t\tthis.parentNode.firstChild.selectedIndex = 0;\n\t}\n}\n\n\n\n/** Toggles column context menu\n* @param [string] extra class name\n* @this HTMLElement\n*/\nfunction columnMouse(className) {\n\tvar spans = qsa('span', this);\n\tfor (var i=0; i < spans.length; i++) {\n\t\tif (/column/.test(spans[i].className)) {\n\t\t\tspans[i].className = 'column' + (className || '');\n\t\t}\n\t}\n}\n\n\n\n/** Fill column in search field\n* @param string\n* @return boolean false\n*/\nfunction selectSearch(name) {\n\tvar el = qs('#fieldset-search');\n\tel.className = '';\n\tvar divs = qsa('div', el);\n\tfor (var i=0; i < divs.length; i++) {\n\t\tvar div = divs[i];\n\t\tvar el = qs('[name\$=\"[col]\"]', div);\n\t\tif (el && selectValue(el) == name) {\n\t\t\tbreak;\n\t\t}\n\t}\n\tif (i == divs.length) {\n\t\tdiv.firstChild.value = name;\n\t\tdiv.firstChild.onchange();\n\t}\n\tqs('[name\$=\"[val]\"]', div).focus();\n\treturn false;\n}\n\n\n/** Check if Ctrl key (Command key on Mac) was pressed\n* @param KeyboardEvent|MouseEvent\n* @return boolean\n*/\nfunction isCtrl(event) {\n\treturn (event.ctrlKey || event.metaKey) && !event.altKey; // shiftKey allowed\n}\n\n/** Return event target\n* @param Event\n* @return HTMLElement\n*/\nfunction getTarget(event) {\n\treturn event.target || event.srcElement;\n}\n\n\n\n/** Send form by Ctrl+Enter on <select> and <textarea>\n* @param KeyboardEvent\n* @param [string]\n* @return boolean\n*/\nfunction bodyKeydown(event, button) {\n\teventStop(event);\n\tvar target = getTarget(event);\n\tif (target.jushTextarea) {\n\t\ttarget = target.jushTextarea;\n\t}\n\tif (isCtrl(event) && (event.keyCode == 13 || event.keyCode == 10) && isTag(target, 'select|textarea|input')) { // 13|10 - Enter\n\t\ttarget.blur();\n\t\tif (button) {\n\t\t\ttarget.form[button].click();\n\t\t} else {\n\t\t\tif (target.form.onsubmit) {\n\t\t\t\ttarget.form.onsubmit();\n\t\t\t}\n\t\t\ttarget.form.submit();\n\t\t}\n\t\ttarget.focus();\n\t\treturn false;\n\t}\n\treturn true;\n}\n\n/** Open form to a new window on Ctrl+click or Shift+click\n* @param MouseEvent\n*/\nfunction bodyClick(event) {\n\tvar target = getTarget(event);\n\tif ((isCtrl(event) || event.shiftKey) && target.type == 'submit' && isTag(target, 'input')) {\n\t\ttarget.form.target = '_blank';\n\t\tsetTimeout(function () {\n\t\t\t// if (isCtrl(event)) { focus(); } doesn't work\n\t\t\ttarget.form.target = '';\n\t\t}, 0);\n\t}\n}\n\n\n\n/** Change focus by Ctrl+Up or Ctrl+Down\n* @param KeyboardEvent\n* @return boolean\n*/\nfunction editingKeydown(event) {\n\tif ((event.keyCode == 40 || event.keyCode == 38) && isCtrl(event)) { // 40 - Down, 38 - Up\n\t\tvar target = getTarget(event);\n\t\tvar sibling = (event.keyCode == 40 ? 'nextSibling' : 'previousSibling');\n\t\tvar el = target.parentNode.parentNode[sibling];\n\t\tif (el && (isTag(el, 'tr') || (el = el[sibling])) && isTag(el, 'tr') && (el = el.childNodes[nodePosition(target.parentNode)]) && (el = el.childNodes[nodePosition(target)])) {\n\t\t\tel.focus();\n\t\t}\n\t\treturn false;\n\t}\n\tif (event.shiftKey && !bodyKeydown(event, 'insert')) {\n\t\treturn false;\n\t}\n\treturn true;\n}\n\n/** Disable maxlength for functions\n* @this HTMLSelectElement\n*/\nfunction functionChange() {\n\tvar input = this.form[this.name.replace(/^function/, 'fields')];\n\tif (input) { // undefined with the set data type\n\t\tif (selectValue(this)) {\n\t\t\tif (input.origType === undefined) {\n\t\t\t\tinput.origType = input.type;\n\t\t\t\tinput.origMaxLength = input.getAttribute('data-maxlength');\n\t\t\t}\n\t\t\tinput.removeAttribute('data-maxlength');\n\t\t\tinput.type = 'text';\n\t\t} else if (input.origType) {\n\t\t\tinput.type = input.origType;\n\t\t\tif (input.origMaxLength >= 0) {\n\t\t\t\tinput.setAttribute('data-maxlength', input.origMaxLength);\n\t\t\t}\n\t\t}\n\t\toninput({target: input});\n\t}\n\thelpClose();\n}\n\n/** Skip 'original' when typing\n* @param number\n* @this HTMLTableCellElement\n*/\nfunction skipOriginal(first) {\n\tvar fnSelect = this.previousSibling.firstChild;\n\tif (fnSelect.selectedIndex < first) {\n\t\tfnSelect.selectedIndex = first;\n\t}\n}\n\n/** Add new field in schema-less edit\n* @this HTMLInputElement\n*/\nfunction fieldChange() {\n\tvar row = cloneNode(parentTag(this, 'tr'));\n\tvar inputs = qsa('input', row);\n\tfor (var i = 0; i < inputs.length; i++) {\n\t\tinputs[i].value = '';\n\t}\n\t// keep value in <select> (function)\n\tparentTag(this, 'table').appendChild(row);\n\tthis.oninput = function () { };\n}\n\n\n\n/** Create AJAX request\n* @param string\n* @param function (XMLHttpRequest)\n* @param [string]\n* @param [string]\n* @return XMLHttpRequest or false in case of an error\n* @uses offlineMessage\n*/\nfunction ajax(url, callback, data, message) {\n\tvar request = (window.XMLHttpRequest ? new XMLHttpRequest() : (window.ActiveXObject ? new ActiveXObject('Microsoft.XMLHTTP') : false));\n\tif (request) {\n\t\tvar ajaxStatus = qs('#ajaxstatus');\n\t\tif (message) {\n\t\t\tajaxStatus.innerHTML = '<div class=\"message\">' + message + '</div>';\n\t\t\tajaxStatus.className = ajaxStatus.className.replace(/ hidden/g, '');\n\t\t} else {\n\t\t\tajaxStatus.className += ' hidden';\n\t\t}\n\t\trequest.open((data ? 'POST' : 'GET'), url);\n\t\tif (data) {\n\t\t\trequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');\n\t\t}\n\t\trequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest');\n\t\trequest.onreadystatechange = function () {\n\t\t\tif (request.readyState == 4) {\n\t\t\t\tif (/^2/.test(request.status)) {\n\t\t\t\t\tcallback(request);\n\t\t\t\t} else {\n\t\t\t\t\tajaxStatus.innerHTML = (request.status ? request.responseText : '<div class=\"error\">' + offlineMessage + '</div>');\n\t\t\t\t\tajaxStatus.className = ajaxStatus.className.replace(/ hidden/g, '');\n\t\t\t\t}\n\t\t\t}\n\t\t};\n\t\trequest.send(data);\n\t}\n\treturn request;\n}\n\n/** Use setHtml(key, value) for JSON response\n* @param string\n* @return boolean false for success\n*/\nfunction ajaxSetHtml(url) {\n\treturn !ajax(url, function (request) {\n\t\tvar data = window.JSON ? JSON.parse(request.responseText) : eval('(' + request.responseText + ')');\n\t\tfor (var key in data) {\n\t\t\tsetHtml(key, data[key]);\n\t\t}\n\t});\n}\n\n/** Save form contents through AJAX\n* @param HTMLFormElement\n* @param string\n* @param [HTMLInputElement]\n* @return boolean\n*/\nfunction ajaxForm(form, message, button) {\n\tvar data = [];\n\tvar els = form.elements;\n\tfor (var i = 0; i < els.length; i++) {\n\t\tvar el = els[i];\n\t\tif (el.name && !el.disabled) {\n\t\t\tif (/^file\$/i.test(el.type) && el.value) {\n\t\t\t\treturn false;\n\t\t\t}\n\t\t\tif (!/^(checkbox|radio|submit|file)\$/i.test(el.type) || el.checked || el == button) {\n\t\t\t\tdata.push(encodeURIComponent(el.name) + '=' + encodeURIComponent(isTag(el, 'select') ? selectValue(el) : el.value));\n\t\t\t}\n\t\t}\n\t}\n\tdata = data.join('&');\n\t\n\tvar url = form.action;\n\tif (!/post/i.test(form.method)) {\n\t\turl = url.replace(/\\?.*/, '') + '?' + data;\n\t\tdata = '';\n\t}\n\treturn ajax(url, function (request) {\n\t\tsetHtml('ajaxstatus', request.responseText);\n\t\tif (window.jush) {\n\t\t\tjush.highlight_tag(qsa('code', qs('#ajaxstatus')), 0);\n\t\t}\n\t\tmessagesPrint(qs('#ajaxstatus'));\n\t}, data, message);\n}\n\n\n\n/** Display edit field\n* @param MouseEvent\n* @param number display textarea instead of input, 2 - load long text\n* @param [string] warning to display\n* @return boolean\n* @this HTMLElement\n*/\nfunction selectClick(event, text, warning) {\n\tvar td = this;\n\tvar target = getTarget(event);\n\tif (!isCtrl(event) || isTag(td.firstChild, 'input|textarea') || isTag(target, 'a')) {\n\t\treturn;\n\t}\n\tif (warning) {\n\t\talert(warning);\n\t\treturn true;\n\t}\n\tvar original = td.innerHTML;\n\ttext = text || /\\n/.test(original);\n\tvar input = document.createElement(text ? 'textarea' : 'input');\n\tinput.onkeydown = function (event) {\n\t\tif (!event) {\n\t\t\tevent = window.event;\n\t\t}\n\t\tif (event.keyCode == 27 && !event.shiftKey && !event.altKey && !isCtrl(event)) { // 27 - Esc\n\t\t\tinputBlur.apply(input);\n\t\t\ttd.innerHTML = original;\n\t\t}\n\t};\n\tvar pos = event.rangeOffset;\n\tvar value = (td.firstChild && td.firstChild.alt) || td.textContent || td.innerText;\n\tinput.style.width = Math.max(td.clientWidth - 14, 20) + 'px'; // 14 = 2 * (td.border + td.padding + input.border)\n\tif (text) {\n\t\tvar rows = 1;\n\t\tvalue.replace(/\\n/g, function () {\n\t\t\trows++;\n\t\t});\n\t\tinput.rows = rows;\n\t}\n\tif (qsa('i', td).length) { // <i> - NULL\n\t\tvalue = '';\n\t}\n\tif (document.selection) {\n\t\tvar range = document.selection.createRange();\n\t\trange.moveToPoint(event.clientX, event.clientY);\n\t\tvar range2 = range.duplicate();\n\t\trange2.moveToElementText(td);\n\t\trange2.setEndPoint('EndToEnd', range);\n\t\tpos = range2.text.length;\n\t}\n\ttd.innerHTML = '';\n\ttd.appendChild(input);\n\tsetupSubmitHighlight(td);\n\tinput.focus();\n\tif (text == 2) { // long text\n\t\treturn ajax(location.href + '&' + encodeURIComponent(td.id) + '=', function (request) {\n\t\t\tif (request.responseText) {\n\t\t\t\tinput.value = request.responseText;\n\t\t\t\tinput.name = td.id;\n\t\t\t}\n\t\t});\n\t}\n\tinput.value = value;\n\tinput.name = td.id;\n\tinput.selectionStart = pos;\n\tinput.selectionEnd = pos;\n\tif (document.selection) {\n\t\tvar range = document.selection.createRange();\n\t\trange.moveEnd('character', -input.value.length + pos);\n\t\trange.select();\n\t}\n\treturn true;\n}\n\n\n\n/** Load and display next page in select\n* @param number\n* @param string\n* @return boolean false for success\n* @this HTMLLinkElement\n*/\nfunction selectLoadMore(limit, loading) {\n\tvar a = this;\n\tvar title = a.innerHTML;\n\tvar href = a.href;\n\ta.innerHTML = loading;\n\tif (href) {\n\t\ta.removeAttribute('href');\n\t\treturn !ajax(href, function (request) {\n\t\t\tvar tbody = document.createElement('tbody');\n\t\t\ttbody.innerHTML = request.responseText;\n\t\t\tqs('#table').appendChild(tbody);\n\t\t\tif (tbody.children.length < limit) {\n\t\t\t\ta.parentNode.removeChild(a);\n\t\t\t} else {\n\t\t\t\ta.href = href.replace(/\\d+\$/, function (page) {\n\t\t\t\t\treturn +page + 1;\n\t\t\t\t});\n\t\t\t\ta.innerHTML = title;\n\t\t\t}\n\t\t});\n\t}\n}\n\n\n\n/** Stop event propagation\n* @param Event\n*/\nfunction eventStop(event) {\n\tif (event.stopPropagation) {\n\t\tevent.stopPropagation();\n\t} else {\n\t\tevent.cancelBubble = true;\n\t}\n}\n\n\n\n/** Setup highlighting of default submit button on form field focus\n* @param HTMLElement\n*/\nfunction setupSubmitHighlight(parent) {\n\tfor (var key in { input: 1, select: 1, textarea: 1 }) {\n\t\tvar inputs = qsa(key, parent);\n\t\tfor (var i = 0; i < inputs.length; i++) {\n\t\t\tsetupSubmitHighlightInput(inputs[i])\n\t\t}\n\t}\n}\n\n/** Setup submit highlighting for single element\n* @param HTMLElement\n*/\nfunction setupSubmitHighlightInput(input) {\n\tif (!/submit|image|file/.test(input.type)) {\n\t\taddEvent(input, 'focus', inputFocus);\n\t\taddEvent(input, 'blur', inputBlur);\n\t}\n}\n\n/** Highlight default submit button\n* @this HTMLInputElement\n*/\nfunction inputFocus() {\n\tvar submit = findDefaultSubmit(this);\n\tif (submit) {\n\t\talterClass(submit, 'default', true);\n\t}\n}\n\n/** Unhighlight default submit button\n* @this HTMLInputElement\n*/\nfunction inputBlur() {\n\tvar submit = findDefaultSubmit(this);\n\tif (submit) {\n\t\talterClass(submit, 'default');\n\t}\n}\n\n/** Find submit button used by Enter\n* @param HTMLElement\n* @return HTMLInputElement\n*/\nfunction findDefaultSubmit(el) {\n\tif (el.jushTextarea) {\n\t\tel = el.jushTextarea;\n\t}\n\tif (!el.form) {\n\t\treturn null;\n\t}\n\tvar inputs = qsa('input', el.form);\n\tfor (var i = 0; i < inputs.length; i++) {\n\t\tvar input = inputs[i];\n\t\tif (input.type == 'submit' && !input.style.zIndex) {\n\t\t\treturn input;\n\t\t}\n\t}\n}\n\n\n\n/** Add event listener\n* @param HTMLElement\n* @param string without 'on'\n* @param function\n*/\nfunction addEvent(el, action, handler) {\n\tif (el.addEventListener) {\n\t\tel.addEventListener(action, handler, false);\n\t} else {\n\t\tel.attachEvent('on' + action, handler);\n\t}\n}\n\n/** Defer focusing element\n* @param HTMLElement\n*/\nfunction focus(el) {\n\tsetTimeout(function () { // this has to be an anonymous function because Firefox passes some arguments to setTimeout callback\n\t\tel.focus();\n\t}, 0);\n}\n\n/** Clone node and setup submit highlighting\n* @param HTMLElement\n* @return HTMLElement\n*/\nfunction cloneNode(el) {\n\tvar el2 = el.cloneNode(true);\n\tvar selector = 'input, select';\n\tvar origEls = qsa(selector, el);\n\tvar cloneEls = qsa(selector, el2);\n\tfor (var i=0; i < origEls.length; i++) {\n\t\tvar origEl = origEls[i];\n\t\tfor (var key in origEl) {\n\t\t\tif (/^on/.test(key) && origEl[key]) {\n\t\t\t\tcloneEls[i][key] = origEl[key];\n\t\t\t}\n\t\t}\n\t}\n\tsetupSubmitHighlight(el2);\n\treturn el2;\n}\n\noninput = function (event) {\n\tvar target = event.target;\n\tvar maxLength = target.getAttribute('data-maxlength');\n\talterClass(target, 'maxlength', target.value && maxLength != null && target.value.length > maxLength); // maxLength could be 0\n};\n// Adminer specific functions\n\n/** Load syntax highlighting\n* @param string first three characters of database system version\n* @param [boolean]\n*/\nfunction bodyLoad(version, maria) {\n\tif (window.jush) {\n\t\tjush.create_links = ' target=\"_blank\" rel=\"noreferrer noopener\"';\n\t\tif (version) {\n\t\t\tfor (var key in jush.urls) {\n\t\t\t\tvar obj = jush.urls;\n\t\t\t\tif (typeof obj[key] != 'string') {\n\t\t\t\t\tobj = obj[key];\n\t\t\t\t\tkey = 0;\n\t\t\t\t\tif (maria) {\n\t\t\t\t\t\tfor (var i = 1; i < obj.length; i++) {\n\t\t\t\t\t\t\tobj[i] = obj[i]\n\t\t\t\t\t\t\t\t.replace(/\\.html/, '/')\n\t\t\t\t\t\t\t\t.replace(/-type-syntax/, '-data-types')\n\t\t\t\t\t\t\t\t.replace(/numeric-(data-types)/, '\$1-\$&')\n\t\t\t\t\t\t\t\t.replace(/#statvar_.*/, '#\$\$1')\n\t\t\t\t\t\t\t;\n\t\t\t\t\t\t}\n\t\t\t\t\t}\n\t\t\t\t}\n\t\t\t\tobj[key] = (maria ? obj[key].replace(/dev\\.mysql\\.com\\/doc\\/mysql\\/en\\//, 'mariadb.com/kb/en/library/') : obj[key]) // MariaDB\n\t\t\t\t\t.replace(/\\/doc\\/mysql/, '/doc/refman/' + version) // MySQL\n\t\t\t\t\t.replace(/\\/docs\\/current/, '/docs/' + version) // PostgreSQL\n\t\t\t\t;\n\t\t\t}\n\t\t}\n\t\tif (window.jushLinks) {\n\t\t\tjush.custom_links = jushLinks;\n\t\t}\n\t\tjush.highlight_tag('code', 0);\n\t\tvar tags = qsa('textarea');\n\t\tfor (var i = 0; i < tags.length; i++) {\n\t\t\tif (/(^|\\s)jush-/.test(tags[i].className)) {\n\t\t\t\tvar pre = jush.textarea(tags[i]);\n\t\t\t\tif (pre) {\n\t\t\t\t\tsetupSubmitHighlightInput(pre);\n\t\t\t\t}\n\t\t\t}\n\t\t}\n\t}\n}\n\n/** Get value of dynamically created form field\n* @param HTMLFormElement\n* @param string\n* @return HTMLElement\n*/\nfunction formField(form, name) {\n\t// required in IE < 8, form.elements[name] doesn't work\n\tfor (var i=0; i < form.length; i++) {\n\t\tif (form[i].name == name) {\n\t\t\treturn form[i];\n\t\t}\n\t}\n}\n\n/** Try to change input type to password or to text\n* @param HTMLInputElement\n* @param boolean\n*/\nfunction typePassword(el, disable) {\n\ttry {\n\t\tel.type = (disable ? 'text' : 'password');\n\t} catch (e) {\n\t}\n}\n\n/** Install toggle handler\n* @param [HTMLElement]\n*/\nfunction messagesPrint(el) {\n\tvar els = qsa('.toggle', el);\n\tfor (var i = 0; i < els.length; i++) {\n\t\tels[i].onclick = partial(toggle, els[i].getAttribute('href').substr(1));\n\t}\n}\n\n\n\n/** Hide or show some login rows for selected driver\t\n* @param HTMLSelectElement\t\n*/\t\nfunction loginDriver(driver) {\t\n\tvar trs = parentTag(driver, 'table').rows;\t\n\tvar disabled = /sqlite/.test(selectValue(driver));\t\n\talterClass(trs[1], 'hidden', disabled);\t// 1 - row with server\n\ttrs[1].getElementsByTagName('input')[0].disabled = disabled;\t\n}\n\n\n\nvar dbCtrl;\nvar dbPrevious = {};\n\n/** Check if database should be opened to a new window\n* @param MouseEvent\n* @this HTMLSelectElement\n*/\nfunction dbMouseDown(event) {\n\tdbCtrl = isCtrl(event);\n\tif (dbPrevious[this.name] == undefined) {\n\t\tdbPrevious[this.name] = this.value;\n\t}\n}\n\n/** Load database after selecting it\n* @this HTMLSelectElement\n*/\nfunction dbChange() {\n\tif (dbCtrl) {\n\t\tthis.form.target = '_blank';\n\t}\n\tthis.form.submit();\n\tthis.form.target = '';\n\tif (dbCtrl && dbPrevious[this.name] != undefined) {\n\t\tthis.value = dbPrevious[this.name];\n\t\tdbPrevious[this.name] = undefined;\n\t}\n}\n\n\n\n/** Check whether the query will be executed with index\n* @this HTMLElement\n*/\nfunction selectFieldChange() {\n\tvar form = this.form;\n\tvar ok = (function () {\n\t\tvar inputs = qsa('input', form);\n\t\tfor (var i=0; i < inputs.length; i++) {\n\t\t\tif (inputs[i].value && /^fulltext/.test(inputs[i].name)) {\n\t\t\t\treturn true;\n\t\t\t}\n\t\t}\n\t\tvar ok = form.limit.value;\n\t\tvar selects = qsa('select', form);\n\t\tvar group = false;\n\t\tvar columns = {};\n\t\tfor (var i=0; i < selects.length; i++) {\n\t\t\tvar select = selects[i];\n\t\t\tvar col = selectValue(select);\n\t\t\tvar match = /^(where.+)col\\]/.exec(select.name);\n\t\t\tif (match) {\n\t\t\t\tvar op = selectValue(form[match[1] + 'op]']);\n\t\t\t\tvar val = form[match[1] + 'val]'].value;\n\t\t\t\tif (col in indexColumns && (!/LIKE|REGEXP/.test(op) || (op == 'LIKE' && val.charAt(0) != '%'))) {\n\t\t\t\t\treturn true;\n\t\t\t\t} else if (col || val) {\n\t\t\t\t\tok = false;\n\t\t\t\t}\n\t\t\t}\n\t\t\tif ((match = /^(columns.+)fun\\]/.exec(select.name))) {\n\t\t\t\tif (/^(avg|count|count distinct|group_concat|max|min|sum)\$/.test(col)) {\n\t\t\t\t\tgroup = true;\n\t\t\t\t}\n\t\t\t\tvar val = selectValue(form[match[1] + 'col]']);\n\t\t\t\tif (val) {\n\t\t\t\t\tcolumns[col && col != 'count' ? '' : val] = 1;\n\t\t\t\t}\n\t\t\t}\n\t\t\tif (col && /^order/.test(select.name)) {\n\t\t\t\tif (!(col in indexColumns)) {\n\t\t\t\t\tok = false;\n\t\t\t\t}\n\t\t\t\tbreak;\n\t\t\t}\n\t\t}\n\t\tif (group) {\n\t\t\tfor (var col in columns) {\n\t\t\t\tif (!(col in indexColumns)) {\n\t\t\t\t\tok = false;\n\t\t\t\t}\n\t\t\t}\n\t\t}\n\t\treturn ok;\n\t})();\n\tsetHtml('noindex', (ok ? '' : '!'));\n}\n\n\n\nvar added = '.', rowCount;\n\n/** Check if val is equal to a-delimiter-b where delimiter is '_', '' or big letter\n* @param string\n* @param string\n* @param string\n* @return boolean\n*/\nfunction delimiterEqual(val, a, b) {\n\treturn (val == a + '_' + b || val == a + b || val == a + b.charAt(0).toUpperCase() + b.substr(1));\n}\n\n/** Escape string to use as identifier\n* @param string\n* @return string\n*/\nfunction idfEscape(s) {\n\treturn s.replace(/`/, '``');\n}\n\n\n\n/** Set up event handlers for edit_fields().\n*/\nfunction editFields() {\n\tvar els = qsa('[name\$=\"[field]\"]');\n\tfor (var i = 0; i < els.length; i++) {\n\t\tels[i].oninput = function () {\n\t\t\teditingNameChange.call(this);\n\t\t\tif (!this.defaultValue) {\n\t\t\t\teditingAddRow.call(this);\n\t\t\t}\n\t\t}\n\t}\n\tels = qsa('[name\$=\"[length]\"]');\n\tfor (var i = 0; i < els.length; i++) {\n\t\tmixin(els[i], {onfocus: editingLengthFocus, oninput: editingLengthChange});\n\t}\n\tels = qsa('[name\$=\"[type]\"]');\n\tfor (var i = 0; i < els.length; i++) {\n\t\tmixin(els[i], {\n\t\t\tonfocus: function () { lastType = selectValue(this); },\n\t\t\tonchange: editingTypeChange,\n\t\t\tonmouseover: function (event) { helpMouseover.call(this, event, getTarget(event).value, 1) },\n\t\t\tonmouseout: helpMouseout\n\t\t});\n\t}\n}\n\n/** Handle clicks on fields editing\n* @param MouseEvent\n* @return boolean false to cancel action\n*/\nfunction editingClick(event) {\n\tvar el = getTarget(event);\n\tif (!isTag(el, 'input')) {\n\t\tel = parentTag(el, 'label');\n\t\tel = el && qs('input', el);\n\t}\n\tif (el) {\n\t\tvar name = el.name;\n\t\tif (/^add\\[/.test(name)) {\n\t\t\teditingAddRow.call(el, 1);\n\t\t} else if (/^up\\[/.test(name)) {\n\t\t\teditingMoveRow.call(el, 1);\n\t\t} else if (/^down\\[/.test(name)) {\n\t\t\teditingMoveRow.call(el);\n\t\t} else if (/^drop_col\\[/.test(name)) {\n\t\t\teditingRemoveRow.call(el, 'fields\\\$1[field]');\n\t\t} else {\n\t\t\tif (name == 'auto_increment_col') {\n\t\t\t\tvar field = el.form['fields[' + el.value + '][field]'];\n\t\t\t\tif (!field.value) {\n\t\t\t\t\tfield.value = 'id';\n\t\t\t\t\tfield.oninput();\n\t\t\t\t}\n\t\t\t}\n\t\t\treturn;\n\t\t}\n\t\treturn false;\n\t}\n}\n\n/** Handle input on fields editing\n* @param InputEvent\n*/\nfunction editingInput(event) {\n\tvar el = getTarget(event);\n\tif (/\\[default\\]\$/.test(el.name)) {\n\t\t el.previousSibling.checked = true;\n\t}\n}\n\n/** Detect foreign key\n* @this HTMLInputElement\n*/\nfunction editingNameChange() {\n\tvar name = this.name.substr(0, this.name.length - 7);\n\tvar type = formField(this.form, name + '[type]');\n\tvar opts = type.options;\n\tvar candidate; // don't select anything with ambiguous match (like column `id`)\n\tvar val = this.value;\n\tfor (var i = opts.length; i--; ) {\n\t\tvar match = /(.+)`(.+)/.exec(opts[i].value);\n\t\tif (!match) { // common type\n\t\t\tif (candidate && i == opts.length - 2 && val == opts[candidate].value.replace(/.+`/, '') && name == 'fields[1]') { // single target table, link to column, first field - probably `id`\n\t\t\t\treturn;\n\t\t\t}\n\t\t\tbreak;\n\t\t}\n\t\tvar table = match[1];\n\t\tvar column = match[2];\n\t\tvar tables = [ table, table.replace(/s\$/, ''), table.replace(/es\$/, '') ];\n\t\tfor (var j=0; j < tables.length; j++) {\n\t\t\ttable = tables[j];\n\t\t\tif (val == column || val == table || delimiterEqual(val, table, column) || delimiterEqual(val, column, table)) {\n\t\t\t\tif (candidate) {\n\t\t\t\t\treturn;\n\t\t\t\t}\n\t\t\t\tcandidate = i;\n\t\t\t\tbreak;\n\t\t\t}\n\t\t}\n\t}\n\tif (candidate) {\n\t\ttype.selectedIndex = candidate;\n\t\ttype.onchange();\n\t}\n}\n\n/** Add table row for next field\n* @param [boolean]\n* @return boolean false\n* @this HTMLInputElement\n*/\nfunction editingAddRow(focus) {\n\tvar match = /(\\d+)(\\.\\d+)?/.exec(this.name);\n\tvar x = match[0] + (match[2] ? added.substr(match[2].length) : added) + '1';\n\tvar row = parentTag(this, 'tr');\n\tvar row2 = cloneNode(row);\n\tvar tags = qsa('select', row);\n\tvar tags2 = qsa('select', row2);\n\tfor (var i=0; i < tags.length; i++) {\n\t\ttags2[i].name = tags[i].name.replace(/[0-9.]+/, x);\n\t\ttags2[i].selectedIndex = tags[i].selectedIndex;\n\t}\n\ttags = qsa('input', row);\n\ttags2 = qsa('input', row2);\n\tvar input = tags2[0]; // IE loose tags2 after insertBefore()\n\tfor (var i=0; i < tags.length; i++) {\n\t\tif (tags[i].name == 'auto_increment_col') {\n\t\t\ttags2[i].value = x;\n\t\t\ttags2[i].checked = false;\n\t\t}\n\t\ttags2[i].name = tags[i].name.replace(/([0-9.]+)/, x);\n\t\tif (/\\[(orig|field|comment|default)/.test(tags[i].name)) {\n\t\t\ttags2[i].value = '';\n\t\t}\n\t\tif (/\\[(has_default)/.test(tags[i].name)) {\n\t\t\ttags2[i].checked = false;\n\t\t}\n\t}\n\ttags[0].oninput = editingNameChange;\n\trow.parentNode.insertBefore(row2, row.nextSibling);\n\tif (focus) {\n\t\tinput.oninput = editingNameChange;\n\t\tinput.focus();\n\t}\n\tadded += '0';\n\trowCount++;\n\treturn false;\n}\n\n/** Remove table row for field\n* @param string regular expression replacement\n* @return boolean false\n* @this HTMLInputElement\n*/\nfunction editingRemoveRow(name) {\n\tvar field = formField(this.form, this.name.replace(/[^\\[]+(.+)/, name));\n\tfield.parentNode.removeChild(field);\n\tparentTag(this, 'tr').style.display = 'none';\n\treturn false;\n}\n\n/** Move table row for field\n* @param [boolean]\n* @return boolean false for success\n* @this HTMLInputElement\n*/\nfunction editingMoveRow(up){\n\tvar row = parentTag(this, 'tr');\n\tif (!('nextElementSibling' in row)) {\n\t\treturn true;\n\t}\n\trow.parentNode.insertBefore(row, up\n\t\t? row.previousElementSibling\n\t\t: row.nextElementSibling ? row.nextElementSibling.nextElementSibling : row.parentNode.firstChild);\n\treturn false;\n}\n\nvar lastType = '';\n\n/** Clear length and hide collation or unsigned\n* @this HTMLSelectElement\n*/\nfunction editingTypeChange() {\n\tvar type = this;\n\tvar name = type.name.substr(0, type.name.length - 6);\n\tvar text = selectValue(type);\n\tfor (var i=0; i < type.form.elements.length; i++) {\n\t\tvar el = type.form.elements[i];\n\t\tif (el.name == name + '[length]') {\n\t\t\tif (!(\n\t\t\t\t(/(char|binary)\$/.test(lastType) && /(char|binary)\$/.test(text))\n\t\t\t\t|| (/(enum|set)\$/.test(lastType) && /(enum|set)\$/.test(text))\n\t\t\t)) {\n\t\t\t\tel.value = '';\n\t\t\t}\n\t\t\tel.oninput.apply(el);\n\t\t}\n\t\tif (lastType == 'timestamp' && el.name == name + '[has_default]' && /timestamp/i.test(formField(type.form, name + '[default]').value)) {\n\t\t\tel.checked = false;\n\t\t}\n\t\tif (el.name == name + '[collation]') {\n\t\t\talterClass(el, 'hidden', !/(char|text|enum|set)\$/.test(text));\n\t\t}\n\t\tif (el.name == name + '[unsigned]') {\n\t\t\talterClass(el, 'hidden', !/(^|[^o])int(?!er)|numeric|real|float|double|decimal|money/.test(text));\n\t\t}\n\t\tif (el.name == name + '[on_update]') {\n\t\t\talterClass(el, 'hidden', !/timestamp|datetime/.test(text)); // MySQL supports datetime since 5.6.5\n\t\t}\n\t\tif (el.name == name + '[on_delete]') {\n\t\t\talterClass(el, 'hidden', !/`/.test(text));\n\t\t}\n\t}\n\thelpClose();\n}\n\n/** Mark length as required\n* @this HTMLInputElement\n*/\nfunction editingLengthChange() {\n\talterClass(this, 'required', !this.value.length && /var(char|binary)\$/.test(selectValue(this.parentNode.previousSibling.firstChild)));\n}\n\n/** Edit enum or set\n* @this HTMLInputElement\n*/\nfunction editingLengthFocus() {\n\tvar td = this.parentNode;\n\tif (/(enum|set)\$/.test(selectValue(td.previousSibling.firstChild))) {\n\t\tvar edit = qs('#enum-edit');\n\t\tedit.value = enumValues(this.value);\n\t\ttd.appendChild(edit);\n\t\tthis.style.display = 'none';\n\t\tedit.style.display = 'inline';\n\t\tedit.focus();\n\t}\n}\n\n/** Get enum values\n* @param string\n* @return string values separated by newlines\n*/\nfunction enumValues(s) {\n\tvar re = /(^|,)\\s*'(([^\\\\']|\\\\.|'')*)'\\s*/g;\n\tvar result = [];\n\tvar offset = 0;\n\tvar match;\n\twhile (match = re.exec(s)) {\n\t\tif (offset != match.index) {\n\t\t\tbreak;\n\t\t}\n\t\tresult.push(match[2].replace(/'(')|\\\\(.)/g, '\$1\$2'));\n\t\toffset += match[0].length;\n\t}\n\treturn (offset == s.length ? result.join('\\n') : s);\n}\n\n/** Finish editing of enum or set\n* @this HTMLTextAreaElement\n*/\nfunction editingLengthBlur() {\n\tvar field = this.parentNode.firstChild;\n\tvar val = this.value;\n\tfield.value = (/^'[^\\n]+'\$/.test(val) ? val : val && \"'\" + val.replace(/\\n+\$/, '').replace(/'/g, \"''\").replace(/\\\\/g, '\\\\\\\\').replace(/\\n/g, \"','\") + \"'\");\n\tfield.style.display = 'inline';\n\tthis.style.display = 'none';\n}\n\n/** Show or hide selected table column\n* @param boolean\n* @param number\n*/\nfunction columnShow(checked, column) {\n\tvar trs = qsa('tr', qs('#edit-fields'));\n\tfor (var i=0; i < trs.length; i++) {\n\t\talterClass(qsa('td', trs[i])[column], 'hidden', !checked);\n\t}\n}\n\n/** Display partition options\n* @this HTMLSelectElement\n*/\nfunction partitionByChange() {\n\tvar partitionTable = /RANGE|LIST/.test(selectValue(this));\n\talterClass(this.form['partitions'], 'hidden', partitionTable || !this.selectedIndex);\n\talterClass(qs('#partition-table'), 'hidden', !partitionTable);\n\thelpClose();\n}\n\n/** Add next partition row\n* @this HTMLInputElement\n*/\nfunction partitionNameChange() {\n\tvar row = cloneNode(parentTag(this, 'tr'));\n\trow.firstChild.firstChild.value = '';\n\tparentTag(this, 'table').appendChild(row);\n\tthis.oninput = function () {};\n}\n\n/** Show or hide comment fields\n* @param HTMLInputElement\n* @param [boolean] whether to focus Comment if checked\n*/\nfunction editingCommentsClick(el, focus) {\n\tvar comment = el.form['Comment'];\n\tcolumnShow(el.checked, 6);\n\talterClass(comment, 'hidden', !el.checked);\n\tif (focus && el.checked) {\n\t\tcomment.focus();\n\t}\n}\n\n\n\n/** Uncheck 'all' checkbox\n* @param MouseEvent\n* @this HTMLTableElement\n*/\nfunction dumpClick(event) {\n\tvar el = parentTag(getTarget(event), 'label');\n\tif (el) {\n\t\tel = qs('input', el);\n\t\tvar match = /(.+)\\[\\]\$/.exec(el.name);\n\t\tif (match) {\n\t\t\tcheckboxClick.call(el, event);\n\t\t\tformUncheck('check-' + match[1]);\n\t\t}\n\t}\n}\n\n\n\n/** Add row for foreign key\n* @this HTMLSelectElement\n*/\nfunction foreignAddRow() {\n\tvar row = cloneNode(parentTag(this, 'tr'));\n\tthis.onchange = function () { };\n\tvar selects = qsa('select', row);\n\tfor (var i=0; i < selects.length; i++) {\n\t\tselects[i].name = selects[i].name.replace(/\\]/, '1\$&');\n\t\tselects[i].selectedIndex = 0;\n\t}\n\tparentTag(this, 'table').appendChild(row);\n}\n\n\n\n/** Add row for indexes\n* @this HTMLSelectElement\n*/\nfunction indexesAddRow() {\n\tvar row = cloneNode(parentTag(this, 'tr'));\n\tthis.onchange = function () { };\n\tvar selects = qsa('select', row);\n\tfor (var i=0; i < selects.length; i++) {\n\t\tselects[i].name = selects[i].name.replace(/indexes\\[\\d+/, '\$&1');\n\t\tselects[i].selectedIndex = 0;\n\t}\n\tvar inputs = qsa('input', row);\n\tfor (var i=0; i < inputs.length; i++) {\n\t\tinputs[i].name = inputs[i].name.replace(/indexes\\[\\d+/, '\$&1');\n\t\tinputs[i].value = '';\n\t}\n\tparentTag(this, 'table').appendChild(row);\n}\n\n/** Change column in index\n* @param string name prefix\n* @this HTMLSelectElement\n*/\nfunction indexesChangeColumn(prefix) {\n\tvar names = [];\n\tfor (var tag in { 'select': 1, 'input': 1 }) {\n\t\tvar columns = qsa(tag, parentTag(this, 'td'));\n\t\tfor (var i=0; i < columns.length; i++) {\n\t\t\tif (/\\[columns\\]/.test(columns[i].name)) {\n\t\t\t\tvar value = selectValue(columns[i]);\n\t\t\t\tif (value) {\n\t\t\t\t\tnames.push(value);\n\t\t\t\t}\n\t\t\t}\n\t\t}\n\t}\n\tthis.form[this.name.replace(/\\].*/, '][name]')].value = prefix + names.join('_');\n}\n\n/** Add column for index\n* @param string name prefix\n* @this HTMLSelectElement\n*/\nfunction indexesAddColumn(prefix) {\n\tvar field = this;\n\tvar select = field.form[field.name.replace(/\\].*/, '][type]')];\n\tif (!select.selectedIndex) {\n\t\twhile (selectValue(select) != \"INDEX\" && select.selectedIndex < select.options.length) {\n\t\t\tselect.selectedIndex++;\n\t\t}\n\t\tselect.onchange();\n\t}\n\tvar column = cloneNode(field.parentNode);\n\tvar selects = qsa('select', column);\n\tfor (var i = 0; i < selects.length; i++) {\n\t\tselect = selects[i];\n\t\tselect.name = select.name.replace(/\\]\\[\\d+/, '\$&1');\n\t\tselect.selectedIndex = 0;\n\t}\n\tfield.onchange = partial(indexesChangeColumn, prefix);\n\tvar inputs = qsa('input', column);\n\tfor (var i = 0; i < inputs.length; i++) {\n\t\tvar input = inputs[i];\n\t\tinput.name = input.name.replace(/\\]\\[\\d+/, '\$&1');\n\t\tif (input.type != 'checkbox') {\n\t\t\tinput.value = '';\n\t\t}\n\t}\n\tparentTag(field, 'td').appendChild(column);\n\tfield.onchange();\n}\n\n\n\n/** Updates the form action\n* @param HTMLFormElement\n* @param string\n*/\nfunction sqlSubmit(form, root) {\n\tif (encodeURIComponent(form['query'].value).length < 2e3) {\n\t\tform.action = root\n\t\t\t+ '&sql=' + encodeURIComponent(form['query'].value)\n\t\t\t+ (form['limit'].value ? '&limit=' + +form['limit'].value : '')\n\t\t\t+ (form['error_stops'].checked ? '&error_stops=1' : '')\n\t\t\t+ (form['only_errors'].checked ? '&only_errors=1' : '')\n\t\t;\n\t}\n}\n\n\n\n/** Handle changing trigger time or event\n* @param RegExp\n* @param string\n* @param HTMLFormElement\n*/\nfunction triggerChange(tableRe, table, form) {\n\tvar formEvent = selectValue(form['Event']);\n\tif (tableRe.test(form['Trigger'].value)) {\n\t\tform['Trigger'].value = table + '_' + (selectValue(form['Timing']).charAt(0) + formEvent.charAt(0)).toLowerCase();\n\t}\n\talterClass(form['Of'], 'hidden', !/ OF/.test(formEvent));\n}\n\n\n\nvar that, x, y; // em and tablePos defined in schema.inc.php\n\n/** Get mouse position\n* @param MouseEvent\n* @this HTMLElement\n*/\nfunction schemaMousedown(event) {\n\tif ((event.which ? event.which : event.button) == 1) {\n\t\tthat = this;\n\t\tx = event.clientX - this.offsetLeft;\n\t\ty = event.clientY - this.offsetTop;\n\t}\n}\n\n/** Move object\n* @param MouseEvent\n*/\nfunction schemaMousemove(event) {\n\tif (that !== undefined) {\n\t\tvar left = (event.clientX - x) / em;\n\t\tvar top = (event.clientY - y) / em;\n\t\tvar divs = qsa('div', that);\n\t\tvar lineSet = { };\n\t\tfor (var i=0; i < divs.length; i++) {\n\t\t\tif (divs[i].className == 'references') {\n\t\t\t\tvar div2 = qs('[id=\"' + (/^refs/.test(divs[i].id) ? 'refd' : 'refs') + divs[i].id.substr(4) + '\"]');\n\t\t\t\tvar ref = (tablePos[divs[i].title] ? tablePos[divs[i].title] : [ div2.parentNode.offsetTop / em, 0 ]);\n\t\t\t\tvar left1 = -1;\n\t\t\t\tvar id = divs[i].id.replace(/^ref.(.+)-.+/, '\$1');\n\t\t\t\tif (divs[i].parentNode != div2.parentNode) {\n\t\t\t\t\tleft1 = Math.min(0, ref[1] - left) - 1;\n\t\t\t\t\tdivs[i].style.left = left1 + 'em';\n\t\t\t\t\tdivs[i].querySelector('div').style.width = -left1 + 'em';\n\t\t\t\t\tvar left2 = Math.min(0, left - ref[1]) - 1;\n\t\t\t\t\tdiv2.style.left = left2 + 'em';\n\t\t\t\t\tdiv2.querySelector('div').style.width = -left2 + 'em';\n\t\t\t\t}\n\t\t\t\tif (!lineSet[id]) {\n\t\t\t\t\tvar line = qs('[id=\"' + divs[i].id.replace(/^....(.+)-.+\$/, 'refl\$1') + '\"]');\n\t\t\t\t\tvar top1 = top + divs[i].offsetTop / em;\n\t\t\t\t\tvar top2 = top + div2.offsetTop / em;\n\t\t\t\t\tif (divs[i].parentNode != div2.parentNode) {\n\t\t\t\t\t\ttop2 += ref[0] - top;\n\t\t\t\t\t\tline.querySelector('div').style.height = Math.abs(top1 - top2) + 'em';\n\t\t\t\t\t}\n\t\t\t\t\tline.style.left = (left + left1) + 'em';\n\t\t\t\t\tline.style.top = Math.min(top1, top2) + 'em';\n\t\t\t\t\tlineSet[id] = true;\n\t\t\t\t}\n\t\t\t}\n\t\t}\n\t\tthat.style.left = left + 'em';\n\t\tthat.style.top = top + 'em';\n\t}\n}\n\n/** Finish move\n* @param MouseEvent\n* @param string\n*/\nfunction schemaMouseup(event, db) {\n\tif (that !== undefined) {\n\t\ttablePos[that.firstChild.firstChild.firstChild.data] = [ (event.clientY - y) / em, (event.clientX - x) / em ];\n\t\tthat = undefined;\n\t\tvar s = '';\n\t\tfor (var key in tablePos) {\n\t\t\ts += '_' + key + ':' + Math.round(tablePos[key][0] * 10000) / 10000 + 'x' + Math.round(tablePos[key][1] * 10000) / 10000;\n\t\t}\n\t\ts = encodeURIComponent(s.substr(1));\n\t\tvar link = qs('#schema-link');\n\t\tlink.href = link.href.replace(/[^=]+\$/, '') + s;\n\t\tcookie('adminer_schema-' + db + '=' + s, 30); //! special chars in db\n\t}\n}\n\n\n\nvar helpOpen, helpIgnore; // when mouse outs <option> then it mouse overs border of <select> - ignore it\n\n/** Display help\n* @param MouseEvent\n* @param string\n* @param bool display on left side (otherwise on top)\n* @this HTMLElement\n*/\nfunction helpMouseover(event, text, side) {\n\tvar target = getTarget(event);\n\tif (!text) {\n\t\thelpClose();\n\t} else if (window.jush && (!helpIgnore || this != target)) {\n\t\thelpOpen = 1;\n\t\tvar help = qs('#help');\n\t\thelp.innerHTML = text;\n\t\tjush.highlight_tag([ help ]);\n\t\talterClass(help, 'hidden');\n\t\tvar rect = target.getBoundingClientRect();\n\t\tvar body = document.documentElement;\n\t\thelp.style.top = (body.scrollTop + rect.top - (side ? (help.offsetHeight - target.offsetHeight) / 2 : help.offsetHeight)) + 'px';\n\t\thelp.style.left = (body.scrollLeft + rect.left - (side ? help.offsetWidth : (help.offsetWidth - target.offsetWidth) / 2)) + 'px';\n\t}\n}\n\n/** Close help after timeout\n* @param MouseEvent\n* @this HTMLElement\n*/\nfunction helpMouseout(event) {\n\thelpOpen = 0;\n\thelpIgnore = (this != getTarget(event));\n\tsetTimeout(function () {\n\t\tif (!helpOpen) {\n\t\t\thelpClose();\n\t\t}\n\t}, 200);\n}\n\n/** Close help\n*/\nfunction helpClose() {\n\talterClass(qs('#help'), 'hidden', true);\n}\n";
} elseif ($_GET["file"] == "jush.js") {
	header("Content-Type: text/javascript; charset=utf-8");
	echo "/** JUSH - JavaScript Syntax Highlighter\n* @link http://jush.sourceforge.net\n* @author Jakub Vrana, https://www.vrana.cz\n* @copyright 2007 Jakub Vrana\n* @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0\n*/\n\n/* Limitations:\n<style> and <script> supposes CDATA or HTML comments\nunnecessary escaping (e.g. echo \"\\'\" or ='&quot;') is removed\n*/\n\nvar jush = {\n\tcreate_links: true, // string for extra <a> parameters, e.g. ' target=\"_blank\"'\n\ttimeout: 1000, // milliseconds\n\tcustom_links: { }, // { state: [ url, regexp ] }, for example { php : [ 'doc/\$&.html', /\\b(getData|setData)\\b/g ] }\n\tapi: { }, // { state: { function: description } }, for example { php: { array: 'Create an array' } }\n\t\n\tphp: /<\\?(?!xml)(?:php)?|<script\\s+language\\s*=\\s*(?:\"php\"|'php'|php)\\s*>/i, // asp_tags=0, short_open_tag=1\n\tnum: /(?:0x[0-9a-f]+)|(?:\\b[0-9]+\\.?[0-9]*|\\.[0-9]+)(?:e[+-]?[0-9]+)?/i,\n\t\n\tregexps: undefined,\n\tsubpatterns: { },\n\n\t/** Link stylesheet\n\t* @param string\n\t*/\n\tstyle: function (href) {\n\t\tvar link = document.createElement('link');\n\t\tlink.rel = 'stylesheet';\n\t\tlink.type = 'text/css';\n\t\tlink.href = href;\n\t\tdocument.getElementsByTagName('head')[0].appendChild(link);\n\t},\n\n\t/** Highlight text\n\t* @param string\n\t* @param string\n\t* @return string\n\t*/\n\thighlight: function (language, text) {\n\t\tthis.last_tag = '';\n\t\tthis.last_class = '';\n\t\treturn '<span class=\"jush\">' + this.highlight_states([ language ], text.replace(/\\r\\n?/g, '\\n'), !/^(htm|tag|xml|txt)\$/.test(language))[0] + '</span>';\n\t},\n\n\t/** Highlight html\n\t* @param string\n\t* @param string\n\t* @return string\n\t*/\n\thighlight_html: function (language, html) {\n\t\tvar original = html.replace(/<br(\\s+[^>]*)?>/gi, '\\n');\n\t\tvar highlighted = jush.highlight(language, jush.html_entity_decode(original.replace(/<[^>]*>/g, ''))).replace(/(^|\\n| ) /g, '\$1&nbsp;');\n\t\t\n\t\tvar inject = { };\n\t\tvar pos = 0;\n\t\tvar last_offset = 0;\n\t\toriginal.replace(/(&[^;]+;)|(?:<[^>]+>)+/g, function (str, entity, offset) {\n\t\t\tpos += (offset - last_offset) + (entity ? 1 : 0);\n\t\t\tif (!entity) {\n\t\t\t\tinject[pos] = str;\n\t\t\t}\n\t\t\tlast_offset = offset + str.length;\n\t\t});\n\t\t\n\t\tpos = 0;\n\t\thighlighted = highlighted.replace(/([^&<]*)(?:(&[^;]+;)|(?:<[^>]+>)+|\$)/g, function (str, text, entity) {\n\t\t\tfor (var i = text.length; i >= 0; i--) {\n\t\t\t\tif (inject[pos + i]) {\n\t\t\t\t\tstr = str.substr(0, i) + inject[pos + i] + str.substr(i);\n\t\t\t\t\tdelete inject[pos + i];\n\t\t\t\t}\n\t\t\t}\n\t\t\tpos += text.length + (entity ? 1 : 0);\n\t\t\treturn str;\n\t\t});\n\t\treturn highlighted;\n\t},\n\n\t/** Highlight text in tags\n\t* @param mixed tag name or array of HTMLElement\n\t* @param number number of spaces for tab, 0 for tab itself, defaults to 4\n\t*/\n\thighlight_tag: function (tag, tab_width) {\n\t\tvar pre = (typeof tag == 'string' ? document.getElementsByTagName(tag) : tag);\n\t\tvar tab = '';\n\t\tfor (var i = (tab_width !== undefined ? tab_width : 4); i--; ) {\n\t\t\ttab += ' ';\n\t\t}\n\t\tvar i = 0;\n\t\tvar highlight = function () {\n\t\t\tvar start = new Date();\n\t\t\twhile (i < pre.length) {\n\t\t\t\tvar match = /(^|\\s)(?:jush|language(?=-\\S))(\$|\\s|-(\\S+))/.exec(pre[i].className); // https://www.w3.org/TR/html5/text-level-semantics.html#the-code-element\n\t\t\t\tif (match) {\n\t\t\t\t\tvar language = match[3] ? match[3] : 'htm';\n\t\t\t\t\tvar s = '<span class=\"jush-' + language + '\">' + jush.highlight_html(language, pre[i].innerHTML.replace(/\\t/g, tab.length ? tab : '\\t')) + '</span>'; // span - enable style for class=\"language-\"\n\t\t\t\t\tif (pre[i].outerHTML && /^pre\$/i.test(pre[i].tagName)) {\n\t\t\t\t\t\tpre[i].outerHTML = pre[i].outerHTML.match(/[^>]+>/)[0] + s + '</' + pre[i].tagName + '>';\n\t\t\t\t\t} else {\n\t\t\t\t\t\tpre[i].innerHTML = s.replace(/\\n/g, '<br />');\n\t\t\t\t\t}\n\t\t\t\t}\n\t\t\t\ti++;\n\t\t\t\tif (jush.timeout && window.setTimeout && (new Date() - start) > jush.timeout) {\n\t\t\t\t\twindow.setTimeout(highlight, 100);\n\t\t\t\t\tbreak;\n\t\t\t\t}\n\t\t\t}\n\t\t};\n\t\thighlight();\n\t},\n\t\n\tlink_manual: function (language, text) {\n\t\tvar code = document.createElement('code');\n\t\tcode.innerHTML = this.highlight(language, text);\n\t\tvar as = code.getElementsByTagName('a');\n\t\tfor (var i = 0; i < as.length; i++) {\n\t\t\tif (as[i].href) {\n\t\t\t\treturn as[i].href;\n\t\t\t}\n\t\t}\n\t\treturn '';\n\t},\n\n\tcreate_link: function (link, s, attrs) {\n\t\treturn '<a'\n\t\t\t+ (this.create_links && link ? ' href=\"' + link + '\" class=\"jush-help\"' : '')\n\t\t\t+ (typeof this.create_links == 'string' ? this.create_links : '')\n\t\t\t+ (attrs || '')\n\t\t\t+ '>' + s + '</a>'\n\t\t;\n\t},\n\n\tkeywords_links: function (state, s) {\n\t\tif (/^js(_write|_code)+\$/.test(state)) {\n\t\t\tstate = 'js';\n\t\t}\n\t\tif (/^(php_quo_var|php_php|php_sql|php_sqlite|php_pgsql|php_mssql|php_oracle|php_echo|php_phpini|php_http|php_mail)\$/.test(state)) {\n\t\t\tstate = 'php2';\n\t\t}\n\t\tif (state == 'sql_code') {\n\t\t\tstate = 'sql';\n\t\t}\n\t\tif (this.links2 && this.links2[state]) {\n\t\t\tvar url = this.urls[state];\n\t\t\tvar links2 = this.links2[state];\n\t\t\ts = s.replace(links2, function (str, match1) {\n\t\t\t\tfor (var i=arguments.length - 4; i > 1; i--) {\n\t\t\t\t\tif (arguments[i]) {\n\t\t\t\t\t\tvar link = (/^https?:/.test(url[i-1]) || !url[i-1] ? url[i-1] : url[0].replace(/\\\$key/g, url[i-1]));\n\t\t\t\t\t\tswitch (state) {\n\t\t\t\t\t\t\tcase 'php': link = link.replace(/\\\$1/g, arguments[i].toLowerCase()); break;\n\t\t\t\t\t\t\tcase 'php_new': link = link.replace(/\\\$1/g, arguments[i].toLowerCase()); break; // toLowerCase() - case sensitive after #\n\t\t\t\t\t\t\tcase 'phpini': link = link.replace(/\\\$1/g, (/^suhosin\\./.test(arguments[i])) ? arguments[i] : arguments[i].toLowerCase().replace(/_/g, '-')); break;\n\t\t\t\t\t\t\tcase 'php_doc': link = link.replace(/\\\$1/g, arguments[i].replace(/^\\W+/, '')); break;\n\t\t\t\t\t\t\tcase 'js_doc': link = link.replace(/\\\$1/g, arguments[i].replace(/^\\W*(.)/, function (match, p1) { return p1.toUpperCase(); })); break;\n\t\t\t\t\t\t\tcase 'http': link = link.replace(/\\\$1/g, arguments[i].toLowerCase()); break;\n\t\t\t\t\t\t\tcase 'sql': link = link.replace(/\\\$1/g, arguments[i].replace(/\\b(ALTER|CREATE|DROP|RENAME|SHOW)\\s+SCHEMA\\b/, '\$1 DATABASE').toLowerCase().replace(/\\s+|_/g, '-')); break;\n\t\t\t\t\t\t\tcase 'sqlset': link = link.replace(/\\\$1/g, (links2.test(arguments[i].replace(/_/g, '-')) ? arguments[i].replace(/_/g, '-') : arguments[i]).toLowerCase()); break;\n\t\t\t\t\t\t\tcase 'sqlite': link = link.replace(/\\\$1/g, arguments[i].toLowerCase().replace(/\\s+/g, '')); break;\n\t\t\t\t\t\t\tcase 'sqliteset': link = link.replace(/\\\$1/g, arguments[i].toLowerCase()); break;\n\t\t\t\t\t\t\tcase 'sqlitestatus': link = link.replace(/\\\$1/g, arguments[i].toLowerCase()); break;\n\t\t\t\t\t\t\tcase 'pgsql': link = link.replace(/\\\$1/g, arguments[i].toLowerCase().replace(/\\s+/g, (i == 1 ? '-' : ''))); break;\n\t\t\t\t\t\t\tcase 'pgsqlset': link = link.replace(/\\\$1/g, arguments[i].replace(/_/g, '-').toUpperCase()); break;\n\t\t\t\t\t\t\tcase 'cnf': link = link.replace(/\\\$1/g, arguments[i].toLowerCase()); break;\n\t\t\t\t\t\t\tcase 'js': link = link.replace(/\\\$1/g, arguments[i].replace(/\\./g, '/')); break;\n\t\t\t\t\t\t\tdefault: link = link.replace(/\\\$1/g, arguments[i]);\n\t\t\t\t\t\t}\n\t\t\t\t\t\tvar title = '';\n\t\t\t\t\t\tif (jush.api[state]) {\n\t\t\t\t\t\t\ttitle = jush.api[state][(state == 'js' ? arguments[i] : arguments[i].toLowerCase())];\n\t\t\t\t\t\t}\n\t\t\t\t\t\treturn (match1 ? match1 : '') + jush.create_link(link, arguments[i], (title ? ' title=\"' + jush.htmlspecialchars_quo(title) + '\"' : '')) + (arguments[arguments.length - 3] ? arguments[arguments.length - 3] : '');\n\t\t\t\t\t}\n\t\t\t\t}\n\t\t\t});\n\t\t}\n\t\tif (this.custom_links[state]) {\n\t\t\ts = s.replace(this.custom_links[state][1], function (str) {\n\t\t\t\tvar offset = arguments[arguments.length - 2];\n\t\t\t\tif (/<[^>]*\$/.test(s.substr(0, offset))) {\n\t\t\t\t\treturn str; // don't create links inside tags\n\t\t\t\t}\n\t\t\t\treturn '<a href=\"' + jush.htmlspecialchars_quo(jush.custom_links[state][0].replace('\$&', encodeURIComponent(str))) + '\" class=\"jush-custom\">' + str + '</a>' // not create_link() - ignores create_links\n\t\t\t});\n\t\t}\n\t\treturn s;\n\t},\n\n\tbuild_regexp: function (key, tr1) {\n\t\tvar re = [ ];\n\t\tsubpatterns = [ '' ];\n\t\tfor (var k in tr1) {\n\t\t\tvar in_bra = false;\n\t\t\tsubpatterns.push(k);\n\t\t\tvar s = tr1[k].source.replace(/\\\\.|\\((?!\\?)|\\[|]|([a-z])(?:-([a-z]))?/gi, function (str, match1, match2) {\n\t\t\t\t// count capturing subpatterns\n\t\t\t\tif (str == (in_bra ? ']' : '[')) {\n\t\t\t\t\tin_bra = !in_bra;\n\t\t\t\t}\n\t\t\t\tif (str == '(') {\n\t\t\t\t\tsubpatterns.push(k);\n\t\t\t\t}\n\t\t\t\tif (match1 && tr1[k].ignoreCase) {\n\t\t\t\t\tif (in_bra) {\n\t\t\t\t\t\treturn str.toLowerCase() + str.toUpperCase();\n\t\t\t\t\t}\n\t\t\t\t\treturn '[' + match1.toLowerCase() + match1.toUpperCase() + ']' + (match2 ? '-[' + match2.toLowerCase() + match2.toUpperCase() + ']' : '');\n\t\t\t\t}\n\t\t\t\treturn str;\n\t\t\t});\n\t\t\tre.push('(' + s + ')');\n\t\t}\n\t\tthis.subpatterns[key] = subpatterns;\n\t\tthis.regexps[key] = new RegExp(re.join('|'), 'g');\n\t},\n\t\n\thighlight_states: function (states, text, in_php, escape) {\n\t\tif (!this.regexps) {\n\t\t\tthis.regexps = { };\n\t\t\tfor (var key in this.tr) {\n\t\t\t\tthis.build_regexp(key, this.tr[key]);\n\t\t\t}\n\t\t} else {\n\t\t\tfor (var key in this.tr) {\n\t\t\t\tthis.regexps[key].lastIndex = 0;\n\t\t\t}\n\t\t}\n\t\tvar state = states[states.length - 1];\n\t\tif (!this.tr[state]) {\n\t\t\treturn [ this.htmlspecialchars(text), states ];\n\t\t}\n\t\tvar ret = [ ]; // return\n\t\tfor (var i=1; i < states.length; i++) {\n\t\t\tret.push('<span class=\"jush-' + states[i] + '\">');\n\t\t}\n\t\tvar match;\n\t\tvar child_states = [ ];\n\t\tvar s_states;\n\t\tvar start = 0;\n\t\twhile (start < text.length && (match = this.regexps[state].exec(text))) {\n\t\t\tif (states[0] != 'htm' && /^<\\/(script|style)>\$/i.test(match[0])) {\n\t\t\t\tcontinue;\n\t\t\t}\n\t\t\tvar key, m = [ ];\n\t\t\tfor (var i = match.length; i--; ) {\n\t\t\t\tif (match[i] || !match[0].length) { // WScript returns empty string even for non matched subexpressions\n\t\t\t\t\tkey = this.subpatterns[state][i];\n\t\t\t\t\twhile (this.subpatterns[state][i - 1] == key) {\n\t\t\t\t\t\ti--;\n\t\t\t\t\t}\n\t\t\t\t\twhile (this.subpatterns[state][i] == key) {\n\t\t\t\t\t\tm.push(match[i]);\n\t\t\t\t\t\ti++;\n\t\t\t\t\t}\n\t\t\t\t\tbreak;\n\t\t\t\t}\n\t\t\t}\n\t\t\tif (!key) {\n\t\t\t\treturn [ 'regexp not found', [ ] ];\n\t\t\t}\n\t\t\t\n\t\t\tif (in_php && key == 'php') {\n\t\t\t\tcontinue;\n\t\t\t}\n\t\t\t//~ console.log(states + ' (' + key + '): ' + text.substring(start).replace(/\\n/g, '\\\\n'));\n\t\t\tvar out = (key.charAt(0) == '_');\n\t\t\tvar division = match.index + (key == 'php_halt2' ? match[0].length : 0);\n\t\t\tvar s = text.substring(start, division);\n\t\t\t\n\t\t\t// highlight children\n\t\t\tvar prev_state = states[states.length - 2];\n\t\t\tif (/^(att_quo|att_apo|att_val)\$/.test(state) && (/^(att_js|att_css|att_http)\$/.test(prev_state) || /^\\s*javascript:/i.test(s))) { // javascript: - easy but without own state //! should be checked only in %URI;\n\t\t\t\tchild_states.unshift(prev_state == 'att_css' ? 'css_pro' : (prev_state == 'att_http' ? 'http' : 'js'));\n\t\t\t\ts_states = this.highlight_states(child_states, this.html_entity_decode(s), true, (state == 'att_apo' ? this.htmlspecialchars_apo : (state == 'att_quo' ? this.htmlspecialchars_quo : this.htmlspecialchars_quo_apo)));\n\t\t\t} else if (state == 'css_js' || state == 'cnf_http' || state == 'cnf_phpini' || state == 'sql_sqlset' || state == 'sqlite_sqliteset' || state == 'pgsql_pgsqlset') {\n\t\t\t\tchild_states.unshift(state.replace(/^[^_]+_/, ''));\n\t\t\t\ts_states = this.highlight_states(child_states, s, true);\n\t\t\t} else if ((state == 'php_quo' || state == 'php_apo') && /^(php_php|php_sql|php_sqlite|php_pgsql|php_mssql|php_oracle|php_phpini|php_http|php_mail)\$/.test(prev_state)) {\n\t\t\t\tchild_states.unshift(prev_state.substr(4));\n\t\t\t\ts_states = this.highlight_states(child_states, this.stripslashes(s), true, (state == 'php_apo' ? this.addslashes_apo : this.addslashes_quo));\n\t\t\t} else if (key == 'php_halt2') {\n\t\t\t\tchild_states.unshift('htm');\n\t\t\t\ts_states = this.highlight_states(child_states, s, true);\n\t\t\t} else if ((state == 'apo' || state == 'quo') && prev_state == 'js_write_code') {\n\t\t\t\tchild_states.unshift('htm');\n\t\t\t\ts_states = this.highlight_states(child_states, s, true);\n\t\t\t} else if ((state == 'apo' || state == 'quo') && prev_state == 'js_http_code') {\n\t\t\t\tchild_states.unshift('http');\n\t\t\t\ts_states = this.highlight_states(child_states, s, true);\n\t\t\t} else if (((state == 'php_quo' || state == 'php_apo') && prev_state == 'php_echo') || (state == 'php_eot2' && states[states.length - 3] == 'php_echo')) {\n\t\t\t\tvar i;\n\t\t\t\tfor (i=states.length; i--; ) {\n\t\t\t\t\tprev_state = states[i];\n\t\t\t\t\tif (prev_state.substring(0, 3) != 'php' && prev_state != 'att_quo' && prev_state != 'att_apo' && prev_state != 'att_val') {\n\t\t\t\t\t\tbreak;\n\t\t\t\t\t}\n\t\t\t\t\tprev_state = '';\n\t\t\t\t}\n\t\t\t\tvar f = (state == 'php_eot2' ? this.addslashes : (state == 'php_apo' ? this.addslashes_apo : this.addslashes_quo));\n\t\t\t\ts = this.stripslashes(s);\n\t\t\t\tif (/^(att_js|att_css|att_http)\$/.test(prev_state)) {\n\t\t\t\t\tvar g = (states[i+1] == 'att_quo' ? this.htmlspecialchars_quo : (states[i+1] == 'att_apo' ? this.htmlspecialchars_apo : this.htmlspecialchars_quo_apo));\n\t\t\t\t\tchild_states.unshift(prev_state == 'att_js' ? 'js' : prev_state.substr(4));\n\t\t\t\t\ts_states = this.highlight_states(child_states, this.html_entity_decode(s), true, function (string) { return f(g(string)); });\n\t\t\t\t} else if (prev_state && child_states) {\n\t\t\t\t\tchild_states.unshift(prev_state);\n\t\t\t\t\ts_states = this.highlight_states(child_states, s, true, f);\n\t\t\t\t} else {\n\t\t\t\t\ts = this.htmlspecialchars(s);\n\t\t\t\t\ts_states = [ (escape ? escape(s) : s), (!out || !/^(att_js|att_css|att_http|css_js|js_write_code|js_http_code|php_php|php_sql|php_sqlite|php_pgsql|php_mssql|php_oracle|php_echo|php_phpini|php_http|php_mail)\$/.test(state) ? child_states : [ ]) ];\n\t\t\t\t}\n\t\t\t} else {\n\t\t\t\ts = this.htmlspecialchars(s);\n\t\t\t\ts_states = [ (escape ? escape(s) : s), (!out || !/^(att_js|att_css|att_http|css_js|js_write_code|js_http_code|php_php|php_sql|php_sqlite|php_pgsql|php_mssql|php_oracle|php_echo|php_phpini|php_http|php_mail)\$/.test(state) ? child_states : [ ]) ]; // reset child states when leaving construct\n\t\t\t}\n\t\t\ts = s_states[0];\n\t\t\tchild_states = s_states[1];\n\t\t\ts = this.keywords_links(state, s);\n\t\t\tret.push(s);\n\t\t\t\n\t\t\ts = text.substring(division, match.index + match[0].length);\n\t\t\ts = (m.length < 3 ? (s ? '<span class=\"jush-op\">' + this.htmlspecialchars(escape ? escape(s) : s) + '</span>' : '') : (m[1] ? '<span class=\"jush-op\">' + this.htmlspecialchars(escape ? escape(m[1]) : m[1]) + '</span>' : '') + this.htmlspecialchars(escape ? escape(m[2]) : m[2]) + (m[3] ? '<span class=\"jush-op\">' + this.htmlspecialchars(escape ? escape(m[3]) : m[3]) + '</span>' : ''));\n\t\t\tif (!out) {\n\t\t\t\tif (this.links && this.links[key] && m[2]) {\n\t\t\t\t\tif (/^tag/.test(key)) {\n\t\t\t\t\t\tthis.last_tag = m[2].toUpperCase();\n\t\t\t\t\t}\n\t\t\t\t\tvar link = (/^tag/.test(key) && !/^(ins|del)\$/i.test(m[2]) ? m[2].toUpperCase() : m[2].toLowerCase());\n\t\t\t\t\tvar k_link = '';\n\t\t\t\t\tvar att_tag = (this.att_mapping[link + '-' + this.last_tag] ? this.att_mapping[link + '-' + this.last_tag] : this.last_tag);\n\t\t\t\t\tfor (var k in this.links[key]) {\n\t\t\t\t\t\tif (key == 'att' && this.links[key][k].test(link + '-' + att_tag) && !/^https?:/.test(k)) {\n\t\t\t\t\t\t\tlink += '-' + att_tag;\n\t\t\t\t\t\t\tk_link = k;\n\t\t\t\t\t\t\tbreak;\n\t\t\t\t\t\t} else {\n\t\t\t\t\t\t\tvar m2 = this.links[key][k].exec(m[2]);\n\t\t\t\t\t\t\tif (m2) {\n\t\t\t\t\t\t\t\tif (m2[1]) {\n\t\t\t\t\t\t\t\t\tlink = (/^tag/.test(key) && !/^(ins|del)\$/i.test(m2[1]) ? m2[1].toUpperCase() : m2[1].toLowerCase());\n\t\t\t\t\t\t\t\t}\n\t\t\t\t\t\t\t\tk_link = k;\n\t\t\t\t\t\t\t\tif (key != 'att') {\n\t\t\t\t\t\t\t\t\tbreak;\n\t\t\t\t\t\t\t\t}\n\t\t\t\t\t\t\t}\n\t\t\t\t\t\t}\n\t\t\t\t\t}\n\t\t\t\t\tif (key == 'php_met') {\n\t\t\t\t\t\tthis.last_class = (k_link && !/^(self|parent|static|dir)\$/i.test(link) ? link : '');\n\t\t\t\t\t}\n\t\t\t\t\tif (k_link) {\n\t\t\t\t\t\ts = (m[1] ? '<span class=\"jush-op\">' + this.htmlspecialchars(escape ? escape(m[1]) : m[1]) + '</span>' : '');\n\t\t\t\t\t\ts += this.create_link((/^https?:/.test(k_link) ? k_link : this.urls[key].replace(/\\\$key/, k_link)).replace(/\\\$val/, (/^https?:/.test(k_link) ? link.toLowerCase() : link)), this.htmlspecialchars(escape ? escape(m[2]) : m[2])); //! use jush.api\n\t\t\t\t\t\ts += (m[3] ? '<span class=\"jush-op\">' + this.htmlspecialchars(escape ? escape(m[3]) : m[3]) + '</span>' : '');\n\t\t\t\t\t}\n\t\t\t\t}\n\t\t\t\tret.push('<span class=\"jush-' + key + '\">', s);\n\t\t\t\tstates.push(key);\n\t\t\t\tif (state == 'php_eot') {\n\t\t\t\t\tthis.tr.php_eot2._2 = new RegExp('(\\n)(' + match[1] + ')(;?\\n)');\n\t\t\t\t\tthis.build_regexp('php_eot2', (match[2] == \"'\" ? { _2: this.tr.php_eot2._2 } : this.tr.php_eot2));\n\t\t\t\t} else if (state == 'pgsql_eot') {\n\t\t\t\t\tthis.tr.pgsql_eot2._2 = new RegExp('\\\\\$' + match[0].replace(/\\\$/, '\\\\\$'));\n\t\t\t\t\tthis.build_regexp('pgsql_eot2', this.tr.pgsql_eot2);\n\t\t\t\t}\n\t\t\t} else {\n\t\t\t\tif (state == 'php_met' && this.last_class) {\n\t\t\t\t\ts = this.create_link(this.urls[state].replace(/\\\$key/, this.last_class) + '.' + s.toLowerCase(), s);\n\t\t\t\t}\n\t\t\t\tret.push(s);\n\t\t\t\tfor (var i = Math.min(states.length, +key.substr(1)); i--; ) {\n\t\t\t\t\tret.push('</span>');\n\t\t\t\t\tstates.pop();\n\t\t\t\t}\n\t\t\t}\n\t\t\tstart = match.index + match[0].length;\n\t\t\tif (!states.length) { // out of states\n\t\t\t\tbreak;\n\t\t\t}\n\t\t\tstate = states[states.length - 1];\n\t\t\tthis.regexps[state].lastIndex = start;\n\t\t}\n\t\tret.push(this.keywords_links(state, this.htmlspecialchars(text.substring(start))));\n\t\tfor (var i=1; i < states.length; i++) {\n\t\t\tret.push('</span>');\n\t\t}\n\t\tstates.shift();\n\t\treturn [ ret.join(''), states ];\n\t},\n\n\tatt_mapping: {\n\t\t'align-APPLET': 'IMG', 'align-IFRAME': 'IMG', 'align-INPUT': 'IMG', 'align-OBJECT': 'IMG',\n\t\t'align-COL': 'TD', 'align-COLGROUP': 'TD', 'align-TBODY': 'TD', 'align-TFOOT': 'TD', 'align-TH': 'TD', 'align-THEAD': 'TD', 'align-TR': 'TD',\n\t\t'border-OBJECT': 'IMG',\n\t\t'cite-BLOCKQUOTE': 'Q',\n\t\t'cite-DEL': 'INS',\n\t\t'color-BASEFONT': 'FONT',\n\t\t'face-BASEFONT': 'FONT',\n\t\t'height-INPUT': 'IMG',\n\t\t'height-TD': 'TH',\n\t\t'height-OBJECT': 'IMG',\n\t\t'label-MENU': 'OPTION',\n\t\t'longdesc-IFRAME': 'FRAME',\n\t\t'name-FIELDSET': 'FORM',\n\t\t'name-TEXTAREA': 'BUTTON',\n\t\t'name-IFRAME': 'FRAME',\n\t\t'name-OBJECT': 'INPUT',\n\t\t'src-IFRAME': 'FRAME',\n\t\t'type-AREA': 'A',\n\t\t'type-LINK': 'A',\n\t\t'width-INPUT': 'IMG',\n\t\t'width-OBJECT': 'IMG',\n\t\t'width-TD': 'TH'\n\t},\n\n\t/** Replace <&> by HTML entities\n\t* @param string\n\t* @return string\n\t*/\n\thtmlspecialchars: function (string) {\n\t\treturn string.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');\n\t},\n\t\n\thtmlspecialchars_quo: function (string) {\n\t\treturn jush.htmlspecialchars(string).replace(/\"/g, '&quot;'); // jush - this.htmlspecialchars_quo is passed as reference\n\t},\n\t\n\thtmlspecialchars_apo: function (string) {\n\t\treturn jush.htmlspecialchars(string).replace(/'/g, '&#39;');\n\t},\n\t\n\thtmlspecialchars_quo_apo: function (string) {\n\t\treturn jush.htmlspecialchars_quo(string).replace(/'/g, '&#39;');\n\t},\n\t\n\t/** Decode HTML entities\n\t* @param string\n\t* @return string\n\t*/\n\thtml_entity_decode: function (string) {\n\t\treturn string.replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '\"').replace(/&nbsp;/g, '\\u00A0').replace(/&#(?:([0-9]+)|x([0-9a-f]+));/gi, function (str, p1, p2) { //! named entities\n\t\t\treturn String.fromCharCode(p1 ? p1 : parseInt(p2, 16));\n\t\t}).replace(/&amp;/g, '&');\n\t},\n\t\n\t/** Add backslash before backslash\n\t* @param string\n\t* @return string\n\t*/\n\taddslashes: function (string) {\n\t\treturn string.replace(/\\\\/g, '\\\\\$&');\n\t},\n\t\n\taddslashes_apo: function (string) {\n\t\treturn string.replace(/[\\\\']/g, '\\\\\$&');\n\t},\n\t\n\taddslashes_quo: function (string) {\n\t\treturn string.replace(/[\\\\\"]/g, '\\\\\$&');\n\t},\n\t\n\t/** Remove backslash before \\\"'\n\t* @param string\n\t* @return string\n\t*/\n\tstripslashes: function (string) {\n\t\treturn string.replace(/\\\\([\\\\\"'])/g, '\$1');\n\t}\n};\n\n\n\njush.tr = { // transitions - key: go inside this state, _2: go outside 2 levels (number alone is put to the beginning in Chrome)\n\t// regular expressions matching empty string could be used only in the last key\n\tquo: { php: jush.php, esc: /\\\\/, _1: /\"/ },\n\tapo: { php: jush.php, esc: /\\\\/, _1: /'/ },\n\tcom: { php: jush.php, _1: /\\*\\// },\n\tcom_nest: { com_nest: /\\/\\*/, _1: /\\*\\// },\n\tphp: { _1: /\\?>/ }, // overwritten by jush-php.js\n\tesc: { _1: /./ }, //! php_quo allows [0-7]{1,3} and x[0-9A-Fa-f]{1,2}\n\tone: { _1: /(?=\\n)/ },\n\tnum: { _1: /()/ },\n\t\n\tsql_apo: { esc: /\\\\/, _0: /''/, _1: /'/ },\n\tsql_quo: { esc: /\\\\/, _0: /\"\"/, _1: /\"/ },\n\tsql_var: { _1: /(?=[^_.\$a-zA-Z0-9])/ },\n\tsqlite_apo: { _0: /''/, _1: /'/ },\n\tsqlite_quo: { _0: /\"\"/, _1: /\"/ },\n\tbac: { _1: /`/ },\n\tbra: { _1: /]/ }\n};\n\n// string: \$key stands for key in jush.links, \$val stands for found string\n// array: [0] is base, other elements correspond to () in jush.links2, \$key stands for text of selected element, \$1 stands for found string\njush.urls = { };\njush.links = { };\njush.links2 = { }; // first and last () is used as delimiter\njush.textarea = (function () {\n\t//! IE sometimes inserts empty <p> in start of a string when newline is entered inside\n\t\n\tfunction findPosition(el, container, offset) {\n\t\tvar pos = { pos: 0 };\n\t\tfindPositionRecurse(el, container, offset, pos);\n\t\treturn pos.pos;\n\t}\n\n\tfunction findPositionRecurse(child, container, offset, pos) {\n\t\tif (child.nodeType == 3) {\n\t\t\tif (child == container) {\n\t\t\t\tpos.pos += offset;\n\t\t\t\treturn true;\n\t\t\t}\n\t\t\tpos.pos += child.textContent.length;\n\t\t} else if (child == container) {\n\t\t\tfor (var i = 0; i < offset; i++) {\n\t\t\t\tfindPositionRecurse(child.childNodes[i], container, offset, pos);\n\t\t\t}\n\t\t\treturn true;\n\t\t} else {\n\t\t\tif (/^(br|div)\$/i.test(child.tagName)) {\n\t\t\t\tpos.pos++;\n\t\t\t}\n\t\t\tfor (var i = 0; i < child.childNodes.length; i++) {\n\t\t\t\tif (findPositionRecurse(child.childNodes[i], container, offset, pos)) {\n\t\t\t\t\treturn true;\n\t\t\t\t}\n\t\t\t}\n\t\t\tif (/^p\$/i.test(child.tagName)) {\n\t\t\t\tpos.pos++;\n\t\t\t}\n\t\t}\n\t}\n\t\n\tfunction findOffset(el, pos) {\n\t\treturn findOffsetRecurse(el, { pos: pos });\n\t}\n\t\n\tfunction findOffsetRecurse(child, pos) {\n\t\tif (child.nodeType == 3) { // 3 - TEXT_NODE\n\t\t\tif (child.textContent.length >= pos.pos) {\n\t\t\t\treturn { container: child, offset: pos.pos };\n\t\t\t}\n\t\t\tpos.pos -= child.textContent.length;\n\t\t} else {\n\t\t\tfor (var i = 0; i < child.childNodes.length; i++) {\n\t\t\t\tif (/^br\$/i.test(child.childNodes[i].tagName)) {\n\t\t\t\t\tif (!pos.pos) {\n\t\t\t\t\t\treturn { container: child, offset: i };\n\t\t\t\t\t}\n\t\t\t\t\tpos.pos--;\n\t\t\t\t\tif (!pos.pos && i == child.childNodes.length - 1) { // last invisible <br>\n\t\t\t\t\t\treturn { container: child, offset: i };\n\t\t\t\t\t}\n\t\t\t\t} else {\n\t\t\t\t\tvar result = findOffsetRecurse(child.childNodes[i], pos);\n\t\t\t\t\tif (result) {\n\t\t\t\t\t\treturn result;\n\t\t\t\t\t}\n\t\t\t\t}\n\t\t\t}\n\t\t}\n\t}\n\t\n\tfunction setText(pre, text, end) {\n\t\tvar lang = 'txt';\n\t\tif (text.length < 1e4) { // highlighting is slow with most languages\n\t\t\tvar match = /(^|\\s)(?:jush|language)-(\\S+)/.exec(pre.jushTextarea.className);\n\t\t\tlang = (match ? match[2] : 'htm');\n\t\t}\n\t\tvar html = jush.highlight(lang, text).replace(/\\n/g, '<br>');\n\t\tsetHTML(pre, html, text, end);\n\t}\n\t\n\tfunction setHTML(pre, html, text, pos) {\n\t\tpre.innerHTML = html;\n\t\tpre.lastHTML = pre.innerHTML; // not html because IE reformats the string\n\t\tpre.jushTextarea.value = text;\n\t\tif (pos) {\n\t\t\tvar start = findOffset(pre, pos);\n\t\t\tif (start) {\n\t\t\t\tvar range = document.createRange();\n\t\t\t\trange.setStart(start.container, start.offset);\n\t\t\t\tvar sel = getSelection();\n\t\t\t\tsel.removeAllRanges();\n\t\t\t\tsel.addRange(range);\n\t\t\t}\n\t\t}\n\t}\n\t\n\tfunction keydown(event) {\n\t\tevent = event || window.event;\n\t\tthis.keydownCode = event.keyCode;\n\t\tif ((event.ctrlKey || event.metaKey) && !event.altKey) {\n\t\t\tvar isUndo = (event.keyCode == 90); // 90 - z\n\t\t\tvar isRedo = (event.keyCode == 89 || (event.keyCode == 90 && event.shiftKey)); // 89 - y\n\t\t\tif (isUndo || isRedo) {\n\t\t\t\tif (isRedo) {\n\t\t\t\t\tif (this.jushUndoPos + 1 < this.jushUndo.length) {\n\t\t\t\t\t\tthis.jushUndoPos++;\n\t\t\t\t\t\tvar undo = this.jushUndo[this.jushUndoPos];\n\t\t\t\t\t\tsetText(this, undo.text, undo.end)\n\t\t\t\t\t}\n\t\t\t\t} else if (this.jushUndoPos >= 0) {\n\t\t\t\t\tthis.jushUndoPos--;\n\t\t\t\t\tvar undo = this.jushUndo[this.jushUndoPos] || { html: '', text: '' };\n\t\t\t\t\tsetText(this, undo.text, this.jushUndo[this.jushUndoPos + 1].start);\n\t\t\t\t}\n\t\t\t\treturn false;\n\t\t\t}\n\t\t} else {\n\t\t\tsetLastPos(this);\n\t\t}\n\t}\n\t\n\tfunction setLastPos(pre) {\n\t\tvar sel = getSelection();\n\t\tif (sel.rangeCount) {\n\t\t\tvar range = sel.getRangeAt(0);\n\t\t\tif (pre.lastPos === undefined) {\n\t\t\t\tpre.lastPos = findPosition(pre, range.endContainer, range.endOffset);\n\t\t\t}\n\t\t}\n\t}\n\t\n\tfunction highlight(pre, forceNewUndo) {\n\t\tvar start = pre.lastPos;\n\t\tpre.lastPos = undefined;\n\t\tvar innerHTML = pre.innerHTML;\n\t\tif (innerHTML != pre.lastHTML) {\n\t\t\tvar end;\n\t\t\tvar sel = getSelection();\n\t\t\tif (sel.rangeCount) {\n\t\t\t\tvar range = sel.getRangeAt(0);\n\t\t\t\tend = findPosition(pre, range.startContainer, range.startOffset);\n\t\t\t}\n\t\t\tinnerHTML = innerHTML.replace(/<br>((<\\/[^>]+>)*<\\/?div>)(?!\$)/gi, function (all, rest) {\n\t\t\t\tif (end) {\n\t\t\t\t\tend--;\n\t\t\t\t}\n\t\t\t\treturn rest;\n\t\t\t});\n\t\t\tpre.innerHTML = innerHTML\n\t\t\t\t.replace(/<(br|div)\\b[^>]*>/gi, '\\n') // Firefox, Chrome\n\t\t\t\t.replace(/&nbsp;(<\\/[pP]\\b)/g, '\$1') // IE\n\t\t\t\t.replace(/<\\/p\\b[^>]*>(\$|<p\\b[^>]*>)/gi, '\\n') // IE\n\t\t\t\t.replace(/(&nbsp;)+\$/gm, '') // Chrome for some users\n\t\t\t;\n\t\t\tsetText(pre, pre.textContent.replace(/\\u00A0/g, ' '), end);\n\t\t\tpre.jushUndo.length = pre.jushUndoPos + 1;\n\t\t\tif (forceNewUndo || !pre.jushUndo.length || pre.jushUndo[pre.jushUndoPos].end !== start) {\n\t\t\t\tpre.jushUndo.push({ text: pre.jushTextarea.value, start: start, end: (forceNewUndo ? undefined : end) });\n\t\t\t\tpre.jushUndoPos++;\n\t\t\t} else {\n\t\t\t\tpre.jushUndo[pre.jushUndoPos].text = pre.jushTextarea.value;\n\t\t\t\tpre.jushUndo[pre.jushUndoPos].end = end;\n\t\t\t}\n\t\t}\n\t}\n\t\n\tfunction keyup() {\n\t\tif (this.keydownCode != 229) { // 229 - IME composition\n\t\t\thighlight(this);\n\t\t}\n\t}\n\t\n\tfunction paste(event) {\n\t\tevent = event || window.event;\n\t\tif (event.clipboardData) {\n\t\t\tsetLastPos(this);\n\t\t\tif (document.execCommand('insertHTML', false, jush.htmlspecialchars(event.clipboardData.getData('text')))) { // Opera doesn't support insertText\n\t\t\t\tevent.preventDefault();\n\t\t\t}\n\t\t\thighlight(this, true);\n\t\t}\n\t}\n\t\n\treturn function textarea(el) {\n\t\tif (!window.getSelection) {\n\t\t\treturn;\n\t\t}\n\t\tvar pre = document.createElement('pre');\n\t\tpre.contentEditable = true;\n\t\tpre.className = el.className + ' jush';\n\t\tpre.style.border = '1px inset #ccc';\n\t\tpre.style.width = el.clientWidth + 'px';\n\t\tpre.style.height = el.clientHeight + 'px';\n\t\tpre.style.padding = '3px';\n\t\tpre.style.overflow = 'auto';\n\t\tpre.style.resize = 'both';\n\t\tif (el.wrap != 'off') {\n\t\t\tpre.style.whiteSpace = 'pre-wrap';\n\t\t}\n\t\tpre.jushTextarea = el;\n\t\tpre.jushUndo = [ ];\n\t\tpre.jushUndoPos = -1;\n\t\tpre.keydownCode = 0;\n\t\tpre.onkeydown = keydown;\n\t\tpre.onkeyup = keyup;\n\t\tpre.onpaste = paste;\n\t\tpre.appendChild(document.createTextNode(el.value));\n\t\thighlight(pre);\n\t\tif (el.spellcheck === false) {\n\t\t\tdocument.documentElement.spellcheck = false; // doesn't work when set on pre or its parent in Firefox\n\t\t}\n\t\tel.parentNode.insertBefore(pre, el);\n\t\tif (document.activeElement === el && !/firefox/i.test(navigator.userAgent)) { // clicking on focused element makes Firefox to lose focus\n\t\t\tpre.focus();\n\t\t}\n\t\tel.style.display = 'none';\n\t\treturn pre;\n\t};\n})();\njush.tr.txt = { php: jush.php };\njush.tr.js = { php: jush.php, js_reg: /\\s*\\/(?![\\/*])/, js_obj: /\\s*\\{/, js_code: /()/ };\njush.tr.js_code = { php: jush.php, quo: /\"/, apo: /'/, js_one: /\\/\\//, js_doc: /\\/\\*\\*/, com: /\\/\\*/, num: jush.num, js_write: /(\\b)(write(?:ln)?)(\\()/, js_http: /(\\.)(setRequestHeader|getResponseHeader)(\\()/, _3: /(<)(\\/script)(>)/i, _1: /[^.\\])}\$\\w\\s]/ };\njush.tr.js_write = { php: jush.php, js_reg: /\\s*\\/(?![\\/*])/, js_write_code: /()/ };\njush.tr.js_http = { php: jush.php, js_reg: /\\s*\\/(?![\\/*])/, js_http_code: /()/ };\njush.tr.js_write_code = { php: jush.php, quo: /\"/, apo: /'/, js_one: /\\/\\//, com: /\\/\\*/, num: jush.num, js_write: /\\(/, _2: /\\)/, _1: /[^\\])}\$\\w\\s]/ };\njush.tr.js_http_code = { php: jush.php, quo: /\"/, apo: /'/, js_one: /\\/\\//, com: /\\/\\*/, num: jush.num, js_http: /\\(/, _2: /\\)/, _1: /[^\\])}\$\\w\\s]/ };\njush.tr.js_one = { php: jush.php, _1: /\\n/, _3: /(<)(\\/script)(>)/i };\njush.tr.js_reg = { php: jush.php, esc: /\\\\/, js_reg_bra: /\\[/, _1: /\\/[a-z]*/i }; //! highlight regexp\njush.tr.js_reg_bra = { php: jush.php, esc: /\\\\/, _1: /]/ };\njush.tr.js_doc = { _1: /\\*\\// };\njush.tr.js_arr = { php: jush.php, quo: /\"/, apo: /'/, js_one: /\\/\\//, com: /\\/\\*/, num: jush.num, js_arr: /\\[/, js_obj: /\\{/, _1: /]/ };\njush.tr.js_obj = { php: jush.php, js_one: /\\s*\\/\\//, com: /\\s*\\/\\*/, js_val: /:/, _1: /\\s*}/, js_key: /()/ };\njush.tr.js_val = { php: jush.php, quo: /\"/, apo: /'/, js_one: /\\/\\//, com: /\\/\\*/, num: jush.num, js_arr: /\\[/, js_obj: /\\{/, _1: /,|(?=})/ };\njush.tr.js_key = { php: jush.php, quo: /\"/, apo: /'/, js_one: /\\/\\//, com: /\\/\\*/, num: jush.num, _1: /(?=[:}])/ };\n\njush.urls.js_write = 'https://developer.mozilla.org/en/docs/DOM/\$key.\$val';\njush.urls.js_http = 'https://www.w3.org/TR/XMLHttpRequest/#the-\$val-\$key';\njush.urls.js = ['https://developer.mozilla.org/en/\$key',\n\t'JavaScript/Reference/Global_Objects/\$1',\n\t'JavaScript/Reference/Statements/\$1',\n\t'JavaScript/Reference/Statements/do...while',\n\t'JavaScript/Reference/Statements/if...else',\n\t'JavaScript/Reference/Statements/try...catch',\n\t'JavaScript/Reference/Operators/Special/\$1',\n\t'DOM/document.\$1', 'DOM/element.\$1', 'DOM/event.\$1', 'DOM/form.\$1', 'DOM/table.\$1', 'DOM/window.\$1',\n\t'https://www.w3.org/TR/XMLHttpRequest/',\n\t'JavaScript/Reference/Global_Objects/Array\$1',\n\t'JavaScript/Reference/Global_Objects/Array\$1',\n\t'JavaScript/Reference/Global_Objects/Date\$1',\n\t'JavaScript/Reference/Global_Objects/Function\$1',\n\t'JavaScript/Reference/Global_Objects/Number\$1',\n\t'JavaScript/Reference/Global_Objects/RegExp\$1',\n\t'JavaScript/Reference/Global_Objects/String\$1'\n];\njush.urls.js_doc = ['https://code.google.com/p/jsdoc-toolkit/wiki/Tag\$key',\n\t'\$1', 'Param', 'Augments', '\$1'\n];\n\njush.links.js_write = { 'document': /^(write|writeln)\$/ };\njush.links.js_http = { 'method': /^(setRequestHeader|getResponseHeader)\$/ };\n\njush.links2.js = /(\\b)(String\\.fromCharCode|Date\\.(?:parse|UTC)|Math\\.(?:E|LN2|LN10|LOG2E|LOG10E|PI|SQRT1_2|SQRT2|abs|acos|asin|atan|atan2|ceil|cos|exp|floor|log|max|min|pow|random|round|sin|sqrt|tan)|Array|Boolean|Date|Error|Function|JavaArray|JavaClass|JavaObject|JavaPackage|Math|Number|Object|Packages|RegExp|String|Infinity|JSON|NaN|undefined|Error|EvalError|RangeError|ReferenceError|SyntaxError|TypeError|URIError|decodeURI|decodeURIComponent|encodeURI|encodeURIComponent|eval|isFinite|isNaN|parseFloat|parseInt|(break|continue|for|function|return|switch|throw|var|while|with)|(do)|(if|else)|(try|catch|finally)|(delete|in|instanceof|new|this|typeof|void)|(alinkColor|anchors|applets|bgColor|body|characterSet|compatMode|contentType|cookie|defaultView|designMode|doctype|documentElement|domain|embeds|fgColor|forms|height|images|implementation|lastModified|linkColor|links|plugins|popupNode|referrer|styleSheets|title|tooltipNode|URL|vlinkColor|width|clear|createAttribute|createDocumentFragment|createElement|createElementNS|createEvent|createNSResolver|createRange|createTextNode|createTreeWalker|evaluate|execCommand|getElementById|getElementsByName|importNode|loadOverlay|queryCommandEnabled|queryCommandIndeterm|queryCommandState|queryCommandValue|write|writeln)|(attributes|childNodes|className|clientHeight|clientLeft|clientTop|clientWidth|dir|firstChild|id|innerHTML|lang|lastChild|localName|name|namespaceURI|nextSibling|nodeName|nodeType|nodeValue|offsetHeight|offsetLeft|offsetParent|offsetTop|offsetWidth|ownerDocument|parentNode|prefix|previousSibling|scrollHeight|scrollLeft|scrollTop|scrollWidth|style|tabIndex|tagName|textContent|addEventListener|appendChild|blur|click|cloneNode|dispatchEvent|focus|getAttribute|getAttributeNS|getAttributeNode|getAttributeNodeNS|getElementsByTagName|getElementsByTagNameNS|hasAttribute|hasAttributeNS|hasAttributes|hasChildNodes|insertBefore|item|normalize|removeAttribute|removeAttributeNS|removeAttributeNode|removeChild|removeEventListener|replaceChild|scrollIntoView|setAttribute|setAttributeNS|setAttributeNode|setAttributeNodeNS|supports|onblur|onchange|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|onresize)|(altKey|bubbles|button|cancelBubble|cancelable|clientX|clientY|ctrlKey|currentTarget|detail|eventPhase|explicitOriginalTarget|isChar|layerX|layerY|metaKey|originalTarget|pageX|pageY|relatedTarget|screenX|screenY|shiftKey|target|timeStamp|type|view|which|initEvent|initKeyEvent|initMouseEvent|initUIEvent|stopPropagation|preventDefault)|(elements|name|acceptCharset|action|enctype|encoding|method|submit|reset)|(caption|tHead|tFoot|rows|tBodies|align|bgColor|border|cellPadding|cellSpacing|frame|rules|summary|width|createTHead|deleteTHead|createTFoot|deleteTFoot|createCaption|deleteCaption|insertRow|deleteRow)|(content|closed|controllers|crypto|defaultStatus|directories|document|frameElement|frames|history|innerHeight|innerWidth|location|locationbar|menubar|name|navigator|opener|outerHeight|outerWidth|pageXOffset|pageYOffset|parent|personalbar|pkcs11|screen|availTop|availLeft|availHeight|availWidth|colorDepth|height|left|pixelDepth|top|width|scrollbars|scrollMaxX|scrollMaxY|scrollX|scrollY|self|sidebar|status|statusbar|toolbar|window|alert|atob|back|btoa|captureEvents|clearInterval|clearTimeout|close|confirm|dump|escape|find|forward|getAttention|getComputedStyle|getSelection|home|moveBy|moveTo|open|openDialog|print|prompt|releaseEvents|resizeBy|resizeTo|scroll|scrollBy|scrollByLines|scrollByPages|scrollTo|setInterval|setTimeout|sizeToContent|stop|unescape|updateCommands|onabort|onclose|ondragdrop|onerror|onload|onpaint|onreset|onscroll|onselect|onsubmit|onunload)|(XMLHttpRequest)|(length))\\b|(\\.(?:pop|push|reverse|shift|sort|splice|unshift|concat|join|slice)|(\\.(?:getDate|getDay|getFullYear|getHours|getMilliseconds|getMinutes|getMonth|getSeconds|getTime|getTimezoneOffset|getUTCDate|getUTCDay|getUTCFullYear|getUTCHours|getUTCMilliseconds|getUTCMinutes|getUTCMonth|getUTCSeconds|setDate|setFullYear|setHours|setMilliseconds|setMinutes|setMonth|setSeconds|setTime|setUTCDate|setUTCFullYear|setUTCHours|setUTCMilliseconds|setUTCMinutes|setUTCMonth|setUTCSeconds|toDateString|toLocaleDateString|toLocaleTimeString|toTimeString|toUTCString))|(\\.(?:apply|call))|(\\.(?:toExponential|toFixed|toPrecision))|(\\.(?:exec|test))|(\\.(?:charAt|charCodeAt|concat|indexOf|lastIndexOf|localeCompare|match|replace|search|slice|split|substr|substring|toLocaleLowerCase|toLocaleUpperCase|toLowerCase|toUpperCase)))(\\s*\\(|\$)/g; // collisions: bgColor, height, width, length, name\njush.links2.js_doc = /(^[ \\t]*|\\n\\s*\\*\\s*|(?={))(@(?:augments|author|borrows|class|constant|constructor|constructs|default|deprecated|description|event|example|field|fileOverview|function|ignore|inner|lends|memberOf|name|namespace|param|private|property|public|requires|returns|see|since|static|throws|type|version)|(@argument)|(@extends)|(\\{@link))(\\b)/g;\njush.tr.sql = { one: /-- |#|--(?=\\n|\$)/, com_code: /\\/\\*![0-9]*|\\*\\//, com: /\\/\\*/, sql_sqlset: /(\\s*)(SET)(\\s+|\$)(?!NAMES\\b|CHARACTER\\b|PASSWORD\\b|(?:GLOBAL\\s+|SESSION\\s+)?TRANSACTION\\b|@[^@]|NEW\\.|OLD\\.)/i, sql_code: /()/ };\njush.tr.sql_code = { sql_apo: /'/, sql_quo: /\"/, bac: /`/, one: /-- |#|--(?=\\n|\$)/, com_code: /\\/\\*![0-9]*|\\*\\//, com: /\\/\\*/, sql_var: /\\B@/, num: jush.num, _1: /;|\\b(THEN|ELSE|LOOP|REPEAT|DO)\\b/i };\njush.tr.sql_sqlset = { one: /-- |#|--(?=\\n|\$)/, com: /\\/\\*/, sqlset_val: /=/, _1: /;|\$/ };\njush.tr.sqlset_val = { sql_apo: /'/, sql_quo: /\"/, bac: /`/, one: /-- |#|--(?=\\n|\$)/, com: /\\/\\*/, _1: /,/, _2: /;|\$/, num: jush.num }; //! comma can be inside function call\njush.tr.sqlset = { _0: /\$/ }; //! jump from SHOW VARIABLES LIKE ''\njush.tr.sqlstatus = { _0: /\$/ }; //! jump from SHOW STATUS LIKE ''\njush.tr.com_code = { _1: /()/ };\n\njush.urls.sql_sqlset = 'https://dev.mysql.com/doc/mysql/en/\$key';\njush.urls.sql = ['https://dev.mysql.com/doc/mysql/en/\$key',\n\t'alter-event.html', 'alter-table.html', 'alter-view.html', 'analyze-table.html', 'create-event.html', 'create-function.html', 'create-procedure.html', 'create-index.html', 'create-table.html', 'create-trigger.html', 'create-view.html', 'drop-index.html', 'drop-table.html', 'begin-end.html', 'optimize-table.html', 'repair-table.html', 'set-transaction.html', 'show-columns.html', 'show-engines.html', 'show-index.html', 'show-processlist.html', 'show-status.html', 'show-tables.html', 'show-variables.html',\n\t'\$1.html', '\$1-statement.html', 'if-statement.html', 'repeat-statement.html', 'truncate-table.html', 'commit.html', 'savepoints.html', 'lock-tables.html', 'charset-connection.html', 'insert-on-duplicate.html', 'fulltext-search.html', 'example-auto-increment.html',\n\t'comparison-operators.html#operator_\$1', 'comparison-operators.html#function_\$1', 'any-in-some-subqueries.html', 'all-subqueries.html', 'exists-and-not-exists-subqueries.html', 'group-by-modifiers.html', 'string-functions.html#operator_\$1', 'string-comparison-functions.html#operator_\$1', 'regexp.html#operator_\$1', 'regexp.html#operator_regexp', 'logical-operators.html#operator_\$1', 'control-flow-functions.html#operator_\$1', 'arithmetic-functions.html#operator_\$1', 'cast-functions.html#operator_\$1', 'date-and-time-functions.html#function_\$1', 'date-and-time-functions.html#function_date-add',\n\t'', // keywords without link\n\t'numeric-type-syntax.html', 'date-and-time-type-syntax.html', 'string-type-syntax.html', 'mysql-spatial-datatypes.html',\n\t'mathematical-functions.html#function_\$1', 'information-functions.html#function_\$1',\n\t'\$1-storage-engine.html', 'merge-storage-engine.html',\n\t'partitioning-range.html', 'partitioning-list.html', 'partitioning-columns.html', 'partitioning-hash.html', 'partitioning-linear-hash.html', 'partitioning-key.html',\n\t'comparison-operators.html#function_\$1', 'control-flow-functions.html#function_\$1', 'string-functions.html#function_\$1', 'string-comparison-functions.html#function_\$1', 'mathematical-functions.html#function_\$1', 'date-and-time-functions.html#function_\$1', 'cast-functions.html#function_\$1', 'xml-functions.html#function_\$1', 'bit-functions.html#function_\$1', 'encryption-functions.html#function_\$1', 'information-functions.html#function_\$1', 'miscellaneous-functions.html#function_\$1', 'group-by-functions.html#function_\$1',\n\t'functions-to-convert-geometries-between-formats.html#function_asbinary',\n\t'functions-to-convert-geometries-between-formats.html#function_astext',\n\t'functions-for-testing-spatial-relations-between-geometric-objects.html#function_\$1',\n\t'functions-that-create-new-geometries-from-existing-ones.html#function_\$1',\n\t'geometry-property-functions.html#function_\$1',\n\t'creating-spatial-values.html#function_\$1',\n\t'row-subqueries.html',\n\t'fulltext-search.html#function_match'\n];\njush.urls.sqlset = ['https://dev.mysql.com/doc/mysql/en/\$key',\n\t'innodb-parameters.html#sysvar_\$1',\n\t'mysql-cluster-program-options-mysqld.html#option_mysqld_\$1', 'mysql-cluster-replication-conflict-resolution.html#option_mysqld_\$1', 'mysql-cluster-replication-schema.html', 'mysql-cluster-replication-starting.html', 'mysql-cluster-system-variables.html#sysvar_\$1',\n\t'replication-options-binary-log.html#option_mysqld_\$1', 'replication-options-binary-log.html#sysvar_\$1', 'replication-options-master.html#sysvar_\$1', 'replication-options-slave.html#option_mysqld_log-slave-updates', 'replication-options-slave.html#option_mysqld_\$1', 'replication-options-slave.html#sysvar_\$1', 'replication-options.html#option_mysqld_\$1',\n\t'server-options.html#option_mysqld_big-tables', 'server-options.html#option_mysqld_\$1',\n\t'server-system-variables.html#sysvar_\$1', // previously server-session-variables\n\t'server-system-variables.html#sysvar_low_priority_updates', 'server-system-variables.html#sysvar_max_join_size', 'server-system-variables.html#sysvar_\$1',\n\t'ssl-options.html#option_general_\$1'\n];\njush.urls.sqlstatus = ['https://dev.mysql.com/doc/mysql/en/\$key',\n\t'server-status-variables.html#statvar_Com_xxx',\n\t'server-status-variables.html#statvar_\$1'\n];\n\njush.links.sql_sqlset = { 'set-statement.html': /.+/ };\n\njush.links2.sql = /(\\b)(ALTER(?:\\s+DEFINER\\s*=\\s*\\S+)?\\s+EVENT|(ALTER(?:\\s+ONLINE|\\s+OFFLINE)?(?:\\s+IGNORE)?\\s+TABLE)|(ALTER(?:\\s+ALGORITHM\\s*=\\s*(?:UNDEFINED|MERGE|TEMPTABLE))?(?:\\s+DEFINER\\s*=\\s*\\S+)?(?:\\s+SQL\\s+SECURITY\\s+(?:DEFINER|INVOKER))?\\s+VIEW)|(ANALYZE(?:\\s+NO_WRITE_TO_BINLOG|\\s+LOCAL)?\\s+TABLE)|(CREATE(?:\\s+DEFINER\\s*=\\s*\\S+)?\\s+EVENT)|(CREATE(?:\\s+DEFINER\\s*=\\s*\\S+)?\\s+FUNCTION)|(CREATE(?:\\s+DEFINER\\s*=\\s*\\S+)?\\s+PROCEDURE)|(CREATE(?:\\s+ONLINE|\\s+OFFLINE)?(?:\\s+UNIQUE|\\s+FULLTEXT|\\s+SPATIAL)?\\s+INDEX)|(CREATE(?:\\s+TEMPORARY)?\\s+TABLE)|(CREATE(?:\\s+DEFINER\\s*=\\s*\\S+)?\\s+TRIGGER)|(CREATE(?:\\s+OR\\s+REPLACE)?(?:\\s+ALGORITHM\\s*=\\s*(?:UNDEFINED|MERGE|TEMPTABLE))?(?:\\s+DEFINER\\s*=\\s*\\S+)?(?:\\s+SQL\\s+SECURITY\\s+(?:DEFINER|INVOKER))?\\s+VIEW)|(DROP(?:\\s+ONLINE|\\s+OFFLINE)?\\s+INDEX)|(DROP(?:\\s+TEMPORARY)?\\s+TABLE)|(END)|(OPTIMIZE(?:\\s+NO_WRITE_TO_BINLOG|\\s+LOCAL)?\\s+TABLE)|(REPAIR(?:\\s+NO_WRITE_TO_BINLOG|\\s+LOCAL)?\\s+TABLE)|(SET(?:\\s+GLOBAL|\\s+SESSION)?\\s+TRANSACTION\\s+ISOLATION\\s+LEVEL)|(SHOW(?:\\s+FULL)?\\s+COLUMNS)|(SHOW(?:\\s+STORAGE)?\\s+ENGINES)|(SHOW\\s+(?:INDEX|INDEXES|KEYS))|(SHOW(?:\\s+FULL)?\\s+PROCESSLIST)|(SHOW(?:\\s+GLOBAL|\\s+SESSION)?\\s+STATUS)|(SHOW(?:\\s+FULL)?\\s+TABLES)|(SHOW(?:\\s+GLOBAL|\\s+SESSION)?\\s+VARIABLES)|(ALTER\\s+(?:DATABASE|SCHEMA)|ALTER\\s+LOGFILE\\s+GROUP|ALTER\\s+SERVER|ALTER\\s+TABLESPACE|BACKUP\\s+TABLE|CACHE\\s+INDEX|CALL|CHANGE\\s+MASTER\\s+TO|CHECK\\s+TABLE|CHECKSUM\\s+TABLE|CREATE\\s+(?:DATABASE|SCHEMA)|CREATE\\s+LOGFILE\\s+GROUP|CREATE\\s+SERVER|CREATE\\s+TABLESPACE|CREATE\\s+USER|DELETE|DESCRIBE|DO|DROP\\s+(?:DATABASE|SCHEMA)|DROP\\s+EVENT|DROP\\s+FUNCTION|DROP\\s+PROCEDURE|DROP\\s+LOGFILE\\s+GROUP|DROP\\s+SERVER|DROP\\s+TABLESPACE|DROP\\s+TRIGGER|DROP\\s+USER|DROP\\s+VIEW|EXPLAIN|FLUSH|GRANT|HANDLER|HELP|INSERT|INSTALL\\s+PLUGIN|JOIN|KILL|LOAD\\s+DATA\\s+FROM\\s+MASTER|LOAD\\s+DATA|LOAD\\s+INDEX|LOAD\\s+XML|PURGE\\s+MASTER\\s+LOGS|RENAME\\s+(?:DATABASE|SCHEMA)|RENAME\\s+TABLE|RENAME\\s+USER|REPLACE|RESET\\s+MASTER|RESET\\s+SLAVE|RESIGNAL|RESTORE\\s+TABLE|REVOKE|SELECT|SET\\s+PASSWORD|SHOW\\s+AUTHORS|SHOW\\s+BINARY\\s+LOGS|SHOW\\s+BINLOG\\s+EVENTS|SHOW\\s+CHARACTER\\s+SET|SHOW\\s+COLLATION|SHOW\\s+CONTRIBUTORS|SHOW\\s+CREATE\\s+(?:DATABASE|SCHEMA)|SHOW\\s+CREATE\\s+TABLE|SHOW\\s+CREATE\\s+VIEW|SHOW\\s+(?:DATABASE|SCHEMA)S|SHOW\\s+ENGINE|SHOW\\s+ERRORS|SHOW\\s+GRANTS|SHOW\\s+MASTER\\s+STATUS|SHOW\\s+OPEN\\s+TABLES|SHOW\\s+PLUGINS|SHOW\\s+PRIVILEGES|SHOW\\s+SCHEDULER\\s+STATUS|SHOW\\s+SLAVE\\s+HOSTS|SHOW\\s+SLAVE\\s+STATUS|SHOW\\s+TABLE\\s+STATUS|SHOW\\s+TRIGGERS|SHOW\\s+WARNINGS|SHOW|SIGNAL|START\\s+SLAVE|STOP\\s+SLAVE|UNINSTALL\\s+PLUGIN|UNION|UPDATE|USE)|(LOOP|LEAVE|ITERATE|WHILE)|(IF|ELSEIF)|(REPEAT|UNTIL)|(TRUNCATE(?:\\s+TABLE)?)|(START\\s+TRANSACTION|BEGIN|COMMIT|ROLLBACK)|(SAVEPOINT|ROLLBACK\\s+TO\\s+SAVEPOINT)|((?:UN)?LOCK\\s+TABLES?)|(SET\\s+NAMES|SET\\s+CHARACTER\\s+SET)|(ON\\s+DUPLICATE\\s+KEY\\s+UPDATE)|(IN\\s+BOOLEAN\\s+MODE|IN\\s+NATURAL\\s+LANGUAGE\\s+MODE|WITH\\s+QUERY\\s+EXPANSION)|(AUTO_INCREMENT)|(IS|IS\\s+NULL)|(BETWEEN|NOT\\s+BETWEEN|IN|NOT\\s+IN)|(ANY|SOME)|(ALL)|(EXISTS|NOT\\s+EXISTS)|(WITH\\s+ROLLUP)|(SOUNDS\\s+LIKE)|(LIKE|NOT\\s+LIKE)|(NOT\\s+REGEXP|REGEXP)|(RLIKE)|(NOT|AND|OR|XOR)|(CASE)|(DIV)|(BINARY)|(CURRENT_DATE|CURRENT_TIME|CURRENT_TIMESTAMP|LOCALTIME|LOCALTIMESTAMP|UTC_DATE|UTC_TIME|UTC_TIMESTAMP)|(INTERVAL)|(ACCESSIBLE|ADD|ALTER|ANALYZE|AS|ASC|ASENSITIVE|BEFORE|BOTH|BY|CASCADE|CHANGE|CHARACTER|CHECK|CLOSE|COLLATE|COLUMN|CONDITION|CONSTRAINT|CONTINUE|CONVERT|CREATE|CROSS|CURSOR|DATABASE|DATABASES|DAY_HOUR|DAY_MICROSECOND|DAY_MINUTE|DAY_SECOND|DECLARE|DEFAULT|DELAYED|DESC|DETERMINISTIC|DISTINCT|DISTINCTROW|DROP|DUAL|EACH|ELSE|ENCLOSED|ESCAPED|EXIT|FALSE|FETCH|FLOAT4|FLOAT8|FOR|FORCE|FOREIGN|FROM|FULLTEXT|GROUP|HAVING|HIGH_PRIORITY|HOUR_MICROSECOND|HOUR_MINUTE|HOUR_SECOND|IGNORE|INDEX|INFILE|INNER|INOUT|INSENSITIVE|INT1|INT2|INT3|INT4|INT8|INTO|KEY|KEYS|LEADING|LEFT|LIMIT|LINEAR|LINES|LOAD|LOCK|LONG|LOW_PRIORITY|MASTER_SSL_VERIFY_SERVER_CERT|MATCH|MIDDLEINT|MINUTE_MICROSECOND|MINUTE_SECOND|MODIFIES|NATURAL|NO_WRITE_TO_BINLOG|NULL|OFFSET|ON|OPEN|OPTIMIZE|OPTION|OPTIONALLY|ORDER|OUT|OUTER|OUTFILE|PRECISION|PRIMARY|PROCEDURE|PURGE|RANGE|READ|READS|READ_WRITE|REFERENCES|RELEASE|RENAME|REQUIRE|RESTRICT|RETURN|RIGHT|SCHEMA|SCHEMAS|SECOND_MICROSECOND|SENSITIVE|SEPARATOR|SPATIAL|SPECIFIC|SQL|SQLEXCEPTION|SQLSTATE|SQLWARNING|SQL_BIG_RESULT|SQL_CALC_FOUND_ROWS|SQL_SMALL_RESULT|SSL|STARTING|STRAIGHT_JOIN|TABLE|TERMINATED|THEN|TO|TRAILING|TRIGGER|TRUE|UNDO|UNIQUE|UNLOCK|UNSIGNED|USAGE|USING|VALUES|VARCHARACTER|VARYING|WHEN|WHERE|WITH|WRITE|XOR|YEAR_MONTH|ZEROFILL))\\b(?!\\()|\\b(bit|tinyint|bool|boolean|smallint|mediumint|int|integer|bigint|float|double\\s+precision|double|real|decimal|dec|numeric|fixed|(date|datetime|timestamp|time|year)|(char|varchar|binary|varbinary|tinyblob|tinytext|blob|text|mediumblob|mediumtext|longblob|longtext|enum|set)|(geometry|point|linestring|polygon|multipoint|multilinestring|multipolygon|geometrycollection)|(mod)|(CURRENT_USER)|(InnoDB|MyISAM|MEMORY|CSV|ARCHIVE|BLACKHOLE|MERGE|FEDERATED)|(MRG_MyISAM)|(PARTITION\\s+BY\\s+RANGE)|(PARTITION\\s+BY\\s+LIST)|(PARTITION\\s+BY\\s+COLUMNS)|(PARTITION\\s+BY\\s+HASH)|(PARTITION\\s+BY\\s+LINEAR\\s+HASH)|(PARTITION\\s+BY(?:\\s+LINEAR)?\\s+KEY))\\b|\\b(coalesce|greatest|isnull|interval|least|(if|ifnull|nullif)|(ascii|bin|bit_length|char|char_length|character_length|concat|concat_ws|conv|elt|export_set|field|find_in_set|format|hex|insert|instr|lcase|left|length|load_file|locate|lower|lpad|ltrim|make_set|mid|oct|octet_length|ord|position|quote|repeat|replace|reverse|right|rpad|rtrim|soundex|sounds_like|space|substr|substring|substring_index|trim|ucase|unhex|upper)|(strcmp)|(abs|acos|asin|atan|atan2|ceil|ceiling|cos|cot|crc32|degrees|exp|floor|ln|log|log2|log10|pi|pow|power|radians|rand|round|sign|sin|sqrt|tan|truncate)|(adddate|addtime|convert_tz|curdate|curtime|date|datediff|date_add|date_format|date_sub|day|dayname|dayofmonth|dayofweek|dayofyear|extract|from_days|from_unixtime|get_format|hour|last_day|makedate|maketime|microsecond|minute|month|monthname|now|period_add|period_diff|quarter|second|sec_to_time|str_to_date|subdate|subtime|sysdate|time|timediff|timestamp|timestampadd|timestampdiff|time_format|time_to_sec|to_days|to_seconds|unix_timestamp|week|weekday|weekofyear|year|yearweek)|(cast|convert)|(extractvalue|updatexml)|(bit_count)|(aes_encrypt|aes_decrypt|compress|decode|encode|des_decrypt|des_encrypt|encrypt|md5|old_password|password|sha|sha1|uncompress|uncompressed_length)|(benchmark|charset|coercibility|collation|connection_id|database|found_rows|last_insert_id|row_count|schema|session_user|system_user|user|version)|(default|get_lock|inet_aton|inet_ntoa|is_free_lock|is_used_lock|master_pos_wait|name_const|release_lock|sleep|uuid|uuid_short|values)|(avg|bit_and|bit_or|bit_xor|count|count_distinct|group_concat|min|max|std|stddev|stddev_pop|stddev_samp|sum|var_pop|var_samp|variance)|(asbinary|aswkb)|(astext|aswkt)|(mbrcontains|mbrdisjoint|mbrequal|mbrintersects|mbroverlaps|mbrtouches|mbrwithin|contains|crosses|disjoint|equals|intersects|overlaps|touches|within)|(buffer|convexhull|difference|intersection|symdifference)|(dimension|envelope|geometrytype|srid|boundary|isempty|issimple|x|y|endpoint|glength|numpoints|pointn|startpoint|isring|isclosed|area|exteriorring|interiorringn|numinteriorrings|centroid|geometryn|numgeometries)|(geomcollfromtext|geomfromtext|linefromtext|mlinefromtext|mpointfromtext|mpolyfromtext|pointfromtext|polyfromtext|bdmpolyfromtext|bdpolyfromtext|geomcollfromwkb|geomfromwkb|linefromwkb|mlinefromwkb|mpointfromwkb|mpolyfromwkb|pointfromwkb|polyfromwkb|bdmpolyfromwkb|bdpolyfromwkb|geometrycollection|linestring|multilinestring|multipoint|multipolygon|point|polygon)|(row)|(match|against))(\\s*\\(|\$)/gi; // collisions: char, set, union(), allow parenthesis - IN, ANY, ALL, SOME, NOT, AND, OR, XOR\njush.links2.sqlset = /(\\b)(ignore_builtin_innodb|innodb_adaptive_hash_index|innodb_additional_mem_pool_size|innodb_autoextend_increment|innodb_autoinc_lock_mode|innodb_buffer_pool_awe_mem_mb|innodb_buffer_pool_size|innodb_commit_concurrency|innodb_concurrency_tickets|innodb_data_file_path|innodb_data_home_dir|innodb_doublewrite|innodb_fast_shutdown|innodb_file_io_threads|innodb_file_per_table|innodb_flush_log_at_trx_commit|innodb_flush_method|innodb_force_recovery|innodb_checksums|innodb_lock_wait_timeout|innodb_locks_unsafe_for_binlog|innodb_log_arch_dir|innodb_log_archive|innodb_log_buffer_size|innodb_log_file_size|innodb_log_files_in_group|innodb_log_group_home_dir|innodb_max_dirty_pages_pct|innodb_max_purge_lag|innodb_mirrored_log_groups|innodb_open_files|innodb_rollback_on_timeout|innodb_stats_on_metadata|innodb_support_xa|innodb_sync_spin_loops|innodb_table_locks|innodb_thread_concurrency|innodb_thread_sleep_delay|innodb_use_legacy_cardinality_algorithm|(ndb[-_]batch[-_]size)|(ndb[-_]log[-_]update[-_]as[-_]write|ndb_log_updated_only)|(ndb_log_orig)|(slave[-_]allow[-_]batching)|(have_ndbcluster|multi_range_count|ndb_autoincrement_prefetch_sz|ndb_cache_check_time|ndb_extra_logging|ndb_force_send|ndb_use_copying_alter_table|ndb_use_exact_count|ndb_wait_connected)|(log[-_]bin[-_]trust[-_]function[-_]creators|log[-_]bin)|(binlog_cache_size|max_binlog_cache_size|max_binlog_size|sync_binlog)|(auto_increment_increment|auto_increment_offset)|(ndb_log_empty_epochs)|(log[-_]slave[-_]updates|report[-_]host|report[-_]password|report[-_]port|report[-_]user|slave[-_]net[-_]timeout|slave[-_]skip[-_]errors)|(init_slave|rpl_recovery_rank|slave_compressed_protocol|slave_exec_mode|slave_transaction_retries|sql_slave_skip_counter)|(master[-_]bind|slave[-_]load[-_]tmpdir|server[-_]id)|(sql_big_tables)|(basedir|big[-_]tables|binlog[-_]format|collation[-_]server|datadir|debug|delay[-_]key[-_]write|engine[-_]condition[-_]pushdown|event[-_]scheduler|general[-_]log|character[-_]set[-_]filesystem|character[-_]set[-_]server|character[-_]sets[-_]dir|init[-_]file|language|large[-_]pages|log[-_]error|log[-_]output|log[-_]queries[-_]not[-_]using[-_]indexes|log[-_]slow[-_]queries|log[-_]warnings|log|low[-_]priority[-_]updates|memlock|min[-_]examined[-_]row[-_]limit|old[-_]passwords|open[-_]files[-_]limit|pid[-_]file|port|safe[-_]show[-_]database|secure[-_]auth|secure[-_]file[-_]priv|skip[-_]external[-_]locking|skip[-_]networking|skip[-_]show[-_]database|slow[-_]query[-_]log|socket|sql[-_]mode|tmpdir|version)|(autocommit|error_count|foreign_key_checks|identity|insert_id|last_insert_id|profiling|profiling_history_size|rand_seed1|rand_seed2|sql_auto_is_null|sql_big_selects|sql_buffer_result|sql_log_bin|sql_log_off|sql_log_update|sql_notes|sql_quote_show_create|sql_safe_updates|sql_warnings|timestamp|unique_checks|warning_count)|(sql_low_priority_updates)|(sql_max_join_size)|(automatic_sp_privileges|back_log|bulk_insert_buffer_size|collation_connection|collation_database|completion_type|concurrent_insert|connect_timeout|date_format|datetime_format|default_week_format|delayed_insert_limit|delayed_insert_timeout|delayed_queue_size|div_precision_increment|expire_logs_days|flush|flush_time|ft_boolean_syntax|ft_max_word_len|ft_min_word_len|ft_query_expansion_limit|ft_stopword_file|general_log_file|group_concat_max_len|have_archive|have_blackhole_engine|have_compress|have_crypt|have_csv|have_dynamic_loading|have_example_engine|have_federated_engine|have_geometry|have_innodb|have_isam|have_merge_engine|have_openssl|have_partitioning|have_query_cache|have_raid|have_row_based_replication|have_rtree_keys|have_ssl|have_symlink|hostname|character_set_client|character_set_connection|character_set_database|character_set_results|character_set_system|init_connect|interactive_timeout|join_buffer_size|keep_files_on_create|key_buffer_size|key_cache_age_threshold|key_cache_block_size|key_cache_division_limit|large_page_size|lc_time_names|license|local_infile|locked_in_memory|log_bin|long_query_time|lower_case_file_system|lower_case_table_names|max_allowed_packet|max_connect_errors|max_connections|max_delayed_threads|max_error_count|max_heap_table_size|max_insert_delayed_threads|max_join_size|max_length_for_sort_data|max_prepared_stmt_count|max_relay_log_size|max_seeks_for_key|max_sort_length|max_sp_recursion_depth|max_tmp_tables|max_user_connections|max_write_lock_count|myisam_data_pointer_size|myisam_max_sort_file_size|myisam_recover_options|myisam_repair_threads|myisam_sort_buffer_size|myisam_stats_method|myisam_use_mmap|named_pipe|net_buffer_length|net_read_timeout|net_retry_count|net_write_timeout|new|old|optimizer_prune_level|optimizer_search_depth|optimizer_switch|plugin_dir|preload_buffer_size|prepared_stmt_count|protocol_version|pseudo_thread_id|query_alloc_block_size|query_cache_limit|query_cache_min_res_unit|query_cache_size|query_cache_type|query_cache_wlock_invalidate|query_prealloc_size|range_alloc_block_size|read_buffer_size|read_only|read_rnd_buffer_size|relay_log_purge|relay_log_space_limit|shared_memory|shared_memory_base_name|slow_launch_time|slow_query_log_file|sort_buffer_size|sql_select_limit|storage_engine|sync_frm|system_time_zone|table_cache|table_definition_cache|table_lock_wait_timeout|table_open_cache|table_type|thread_cache_size|thread_concurrency|thread_handling|thread_stack|time_format|time_zone|timed_mutexes|tmp_table_size|transaction_alloc_block_size|transaction_prealloc_size|tx_isolation|updatable_views_with_limit|version_comment|version_compile_machine|version_compile_os|wait_timeout)|(ssl[-_]ca|ssl[-_]capath|ssl[-_]cert|ssl[-_]cipher|ssl[-_]key))((?!-)\\b)/gi;\njush.links2.sqlstatus = /()(Com_.+|(.+))()/gi;\n";
} else {
	header("Content-Type: image/gif");
	switch ($_GET["file"]) {
		case "plus.gif": echo base64_decode('R0lGODlhEgASAIEAMe7u7gAAgJmZmQAAACH5BAEAAAEALAAAAAASABIAAQIhhI+py+0PTQjxzCopvm/6rykgCHGVGaFliLXuI8TyTMsFADs='); break;
		case "cross.gif": echo base64_decode('R0lGODlhEgASAIEAMe7u7gAAgJmZmQAAACH5BAEAAAEALAAAAAASABIAAQIjhI+py+0PIwph1kZvfnnDLoFfd2GU4THnsUruC0fCTNc2XQAAOw=='); break;
		case "up.gif": echo base64_decode('R0lGODlhEgASAIEAMe7u7gAAgJmZmQAAACH5BAEAAAEALAAAAAASABIAAQIghI+py+0PTQhRTgrvfRP0nmEVOIoReZphxbauAMfyHBcAOw=='); break;
		case "down.gif": echo base64_decode('R0lGODlhEgASAIEAMe7u7gAAgJmZmQAAACH5BAEAAAEALAAAAAASABIAAQIghI+py+0PTQjxzCopvltX/lyix0wm2ZwdxraVAMfyHBcAOw=='); break;
		case "arrow.gif": echo base64_decode('R0lGODlhCAAKAIAAAICAgP///yH5BAEAAAEALAAAAAAIAAoAAAIPBIJplrGLnpQRqtOy3rsAADs='); break;
	}
}
exit;

}

if ($_GET["script"] == "version") {
	$fp = file_open_lock(get_temp_dir() . "/adminer.version");
	if ($fp) {
		file_write_unlock($fp, serialize(array("signature" => $_POST["signature"], "version" => $_POST["version"])));
	}
	exit;
}

global $adminer, $connection, $driver, $drivers, $edit_functions, $enum_length, $error, $functions, $grouping, $HTTPS, $inout, $jush, $LANG, $langs, $on_actions, $permanent, $structured_types, $has_token, $token, $translations, $types, $unsigned, $VERSION; // allows including Adminer inside a function

if (!$_SERVER["REQUEST_URI"]) { // IIS 5 compatibility
	$_SERVER["REQUEST_URI"] = $_SERVER["ORIG_PATH_INFO"];
}
if (!strpos($_SERVER["REQUEST_URI"], '?') && $_SERVER["QUERY_STRING"] != "") { // IIS 7 compatibility
	$_SERVER["REQUEST_URI"] .= "?$_SERVER[QUERY_STRING]";
}
if ($_SERVER["HTTP_X_FORWARDED_PREFIX"]) {
	$_SERVER["REQUEST_URI"] = $_SERVER["HTTP_X_FORWARDED_PREFIX"] . $_SERVER["REQUEST_URI"];
}
$HTTPS = ($_SERVER["HTTPS"] && strcasecmp($_SERVER["HTTPS"], "off")) || ini_bool("session.cookie_secure"); // session.cookie_secure could be set on HTTP if we are behind a reverse proxy

@ini_set("session.use_trans_sid", false); // protect links in export, @ - may be disabled
if (!defined("SID")) {
	session_cache_limiter(""); // to allow restarting session
	session_name("adminer_sid"); // use specific session name to get own namespace
	$params = array(0, preg_replace('~\?.*~', '', $_SERVER["REQUEST_URI"]), "", $HTTPS);
	if (version_compare(PHP_VERSION, '5.2.0') >= 0) {
		$params[] = true; // HttpOnly
	}
	call_user_func_array('session_set_cookie_params', $params); // ini_set() may be disabled
	session_start();
}

// disable magic quotes to be able to use database escaping function
remove_slashes(array(&$_GET, &$_POST, &$_COOKIE), $filter);
if (function_exists("get_magic_quotes_runtime") && get_magic_quotes_runtime()) {
	set_magic_quotes_runtime(false);
}
@set_time_limit(0); // @ - can be disabled
@ini_set("zend.ze1_compatibility_mode", false); // @ - deprecated
@ini_set("precision", 15); // @ - can be disabled, 15 - internal PHP precision


// not used in a single language version

$langs = array(
	'en' => 'English', // Jakub Vrána - https://www.vrana.cz
	'ar' => 'العربية', // Y.M Amine - Algeria - nbr7@live.fr
	'bg' => 'Български', // Deyan Delchev
	'bn' => 'বাংলা', // Dipak Kumar - dipak.ndc@gmail.com
	'bs' => 'Bosanski', // Emir Kurtovic
	'ca' => 'Català', // Joan Llosas
	'cs' => 'Čeština', // Jakub Vrána - https://www.vrana.cz
	'da' => 'Dansk', // Jarne W. Beutnagel - jarne@beutnagel.dk
	'de' => 'Deutsch', // Klemens Häckel - http://clickdimension.wordpress.com
	'el' => 'Ελληνικά', // Dimitrios T. Tanis - jtanis@tanisfood.gr
	'es' => 'Español', // Klemens Häckel - http://clickdimension.wordpress.com
	'et' => 'Eesti', // Priit Kallas
	'fa' => 'فارسی', // mojtaba barghbani - Iran - mbarghbani@gmail.com, Nima Amini - http://nimlog.com
	'fi' => 'Suomi', // Finnish - Kari Eveli - http://www.lexitec.fi/
	'fr' => 'Français', // Francis Gagné, Aurélien Royer
	'gl' => 'Galego', // Eduardo Penabad Ramos
	'he' => 'עברית', // Binyamin Yawitz - https://stuff-group.com/
	'hu' => 'Magyar', // Borsos Szilárd (Borsosfi) - http://www.borsosfi.hu, info@borsosfi.hu
	'id' => 'Bahasa Indonesia', // Ivan Lanin - http://ivan.lanin.org
	'it' => 'Italiano', // Alessandro Fiorotto, Paolo Asperti
	'ja' => '日本語', // Hitoshi Ozawa - http://sourceforge.jp/projects/oss-ja-jpn/releases/
	'ka' => 'ქართული', // Saba Khmaladze skhmaladze@uglt.org
	'ko' => '한국어', // dalli - skcha67@gmail.com
	'lt' => 'Lietuvių', // Paulius Leščinskas - http://www.lescinskas.lt
	'ms' => 'Bahasa Melayu', // Pisyek
	'nl' => 'Nederlands', // Maarten Balliauw - http://blog.maartenballiauw.be
	'no' => 'Norsk', // Iver Odin Kvello, mupublishing.com
	'pl' => 'Polski', // Radosław Kowalewski - http://srsbiz.pl/
	'pt' => 'Português', // André Dias
	'pt-br' => 'Português (Brazil)', // Gian Live - gian@live.com, Davi Alexandre davi@davialexandre.com.br, RobertoPC - http://www.robertopc.com.br
	'ro' => 'Limba Română', // .nick .messing - dot.nick.dot.messing@gmail.com
	'ru' => 'Русский', // Maksim Izmaylov; Andre Polykanine - https://github.com/Oire/
	'sk' => 'Slovenčina', // Ivan Suchy - http://www.ivansuchy.com, Juraj Krivda - http://www.jstudio.cz
	'sl' => 'Slovenski', // Matej Ferlan - www.itdinamik.com, matej.ferlan@itdinamik.com
	'sr' => 'Српски', // Nikola Radovanović - cobisimo@gmail.com
	'sv' => 'Svenska', // rasmusolle - https://github.com/rasmusolle
	'ta' => 'த‌மிழ்', // G. Sampath Kumar, Chennai, India, sampathkumar11@gmail.com
	'th' => 'ภาษาไทย', // Panya Saraphi, elect.tu@gmail.com - http://www.opencart2u.com/
	'tr' => 'Türkçe', // Bilgehan Korkmaz - turktron.com
	'uk' => 'Українська', // Valerii Kryzhov
	'vi' => 'Tiếng Việt', // Giang Manh @ manhgd google mail
	'zh' => '简体中文', // Mr. Lodar, vea - urn2.net - vea.urn2@gmail.com
	'zh-tw' => '繁體中文', // http://tzangms.com
);

/** Get current language
* @return string
*/
function get_lang() {
	global $LANG;
	return $LANG;
}

/** Translate string
* @param string
* @param int
* @return string
*/
function lang($idf, $number = null) {
	if (is_string($idf)) { // compiled version uses numbers, string comes from a plugin
		// English translation is closest to the original identifiers //! pluralized translations are not found
		$pos = array_search($idf, get_translations("en")); //! this should be cached
		if ($pos !== false) {
			$idf = $pos;
		}
	}
	global $LANG, $translations;
	$translation = ($translations[$idf] ? $translations[$idf] : $idf);
	if (is_array($translation)) {
		$pos = ($number == 1 ? 0
			: ($LANG == 'cs' || $LANG == 'sk' ? ($number && $number < 5 ? 1 : 2) // different forms for 1, 2-4, other
			: ($LANG == 'fr' ? (!$number ? 0 : 1) // different forms for 0-1, other
			: ($LANG == 'pl' ? ($number % 10 > 1 && $number % 10 < 5 && $number / 10 % 10 != 1 ? 1 : 2) // different forms for 1, 2-4 except 12-14, other
			: ($LANG == 'sl' ? ($number % 100 == 1 ? 0 : ($number % 100 == 2 ? 1 : ($number % 100 == 3 || $number % 100 == 4 ? 2 : 3))) // different forms for 1, 2, 3-4, other
			: ($LANG == 'lt' ? ($number % 10 == 1 && $number % 100 != 11 ? 0 : ($number % 10 > 1 && $number / 10 % 10 != 1 ? 1 : 2)) // different forms for 1, 12-19, other
			: ($LANG == 'bs' || $LANG == 'ru' || $LANG == 'sr' || $LANG == 'uk' ? ($number % 10 == 1 && $number % 100 != 11 ? 0 : ($number % 10 > 1 && $number % 10 < 5 && $number / 10 % 10 != 1 ? 1 : 2)) // different forms for 1 except 11, 2-4 except 12-14, other
			: 1 // different forms for 1, other
		))))))); // http://www.gnu.org/software/gettext/manual/html_node/Plural-forms.html
		$translation = $translation[$pos];
	}
	$args = func_get_args();
	array_shift($args);
	$format = str_replace("%d", "%s", $translation);
	if ($format != $translation) {
		$args[0] = format_number($number);
	}
	return vsprintf($format, $args);
}

function switch_lang() {
	global $LANG, $langs;
	echo "<form action='' method='post'>\n<div id='lang'>";
	echo lang(19) . ": " . html_select("lang", $langs, $LANG, "this.form.submit();");
	echo " <input type='submit' value='" . lang(20) . "' class='hidden'>\n";
	echo "<input type='hidden' name='token' value='" . get_token() . "'>\n"; // $token may be empty in auth.inc.php
	echo "</div>\n</form>\n";
}

if (isset($_POST["lang"]) && verify_token()) { // $error not yet available
	cookie("adminer_lang", $_POST["lang"]);
	$_SESSION["lang"] = $_POST["lang"]; // cookies may be disabled
	$_SESSION["translations"] = array(); // used in compiled version
	redirect(remove_from_uri());
}

$LANG = "en";
if (isset($langs[$_COOKIE["adminer_lang"]])) {
	cookie("adminer_lang", $_COOKIE["adminer_lang"]);
	$LANG = $_COOKIE["adminer_lang"];
} elseif (isset($langs[$_SESSION["lang"]])) {
	$LANG = $_SESSION["lang"];
} else {
	$accept_language = array();
	preg_match_all('~([-a-z]+)(;q=([0-9.]+))?~', str_replace("_", "-", strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"])), $matches, PREG_SET_ORDER);
	foreach ($matches as $match) {
		$accept_language[$match[1]] = (isset($match[3]) ? $match[3] : 1);
	}
	arsort($accept_language);
	foreach ($accept_language as $key => $q) {
		if (isset($langs[$key])) {
			$LANG = $key;
			break;
		}
		$key = preg_replace('~-.*~', '', $key);
		if (!isset($accept_language[$key]) && isset($langs[$key])) {
			$LANG = $key;
			break;
		}
	}
}

function get_translations($lang) {
	switch ($lang) {
		case "en": return [
  'Are you sure?',
  '%.3f s',
  'Unable to upload a file.',
  'Maximum allowed file size is %sB.',
  'File does not exist.',
  ',',
  '0123456789',
  'empty',
  'original',
  'No tables.',
  'Edit',
  'Insert',
  'No rows.',
  'You have no privileges to update this table.',
  'Save',
  'Save and continue edit',
  'Save and insert next',
  'Saving',
  'Delete',
  'Language',
  'Use',
  'Unknown error.',
  'System',
  'Server',
  'Username',
  'Password',
  'Database',
  'Login',
  'Permanent login',
  'Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',
  'Select data',
  'Show structure',
  'Alter view',
  'Alter table',
  'New item',
  'Warnings',
  [
    '%d byte',
    '%d bytes',
  ],
  'Column',
  'Type',
  'Comment',
  'Auto Increment',
  'Default value',
  'Select',
  'Functions',
  'Aggregation',
  'Search',
  'anywhere',
  'Sort',
  'descending',
  'Limit',
  'Text length',
  'Action',
  'Full table scan',
  'SQL command',
  'open',
  'save',
  'Alter database',
  'Alter schema',
  'Create schema',
  'Database schema',
  'Privileges',
  'Import',
  'Export',
  'Create table',
  'database',
  'DB',
  'select',
  'Disable %s or enable %s or %s extensions.',
  'Strings',
  'Numbers',
  'Date and time',
  'Lists',
  'Binary',
  'Geometry',
  'ltr',
  'You are offline.',
  'Logout',
  [
    'Too many unsuccessful logins, try again in %d minute.',
    'Too many unsuccessful logins, try again in %d minutes.',
  ],
  'Logout successful.',
  'Thanks for using Adminer, consider <a href="https://www.adminer.org/en/donation/">donating</a>.',
  'Session expired, please login again.',
  'Master password expired. <a href="https://www.adminer.org/en/extension/"%s>Implement</a> %s method to make it permanent.',
  'Session support must be enabled.',
  'The action will be performed after successful login with the same credentials.',
  'No extension',
  'None of the supported PHP extensions (%s) are available.',
  'Connecting to privileged ports is not allowed.',
  'Invalid credentials.',
  'There is a space in the input password which might be the cause.',
  'Invalid CSRF token. Send the form again.',
  'Maximum number of allowed fields exceeded. Please increase %s.',
  'If you did not send this request from Adminer then close this page.',
  'Too big POST data. Reduce the data or increase the %s configuration directive.',
  'You can upload a big SQL file via FTP and import it from server.',
  'Foreign keys',
  'collation',
  'ON UPDATE',
  'ON DELETE',
  'Column name',
  'Parameter name',
  'Length',
  'Options',
  'Add next',
  'Move up',
  'Move down',
  'Remove',
  'Invalid database.',
  'Databases have been dropped.',
  'Select database',
  'Create database',
  'Process list',
  'Variables',
  'Status',
  '%s version: %s through PHP extension %s',
  'Logged as: %s',
  'Refresh',
  'Collation',
  'Tables',
  'Size',
  'Compute',
  'Selected',
  'Drop',
  'Materialized view',
  'View',
  'Table',
  'Indexes',
  'Alter indexes',
  'Source',
  'Target',
  'Alter',
  'Add foreign key',
  'Triggers',
  'Add trigger',
  'Permanent link',
  'Output',
  'Format',
  'Routines',
  'Events',
  'Data',
  'Create user',
  'ATTACH queries are not supported.',
  'Error in query',
  '%d / ',
  [
    '%d row',
    '%d rows',
  ],
  [
    'Query executed OK, %d row affected.',
    'Query executed OK, %d rows affected.',
  ],
  'No commands to execute.',
  [
    '%d query executed OK.',
    '%d queries executed OK.',
  ],
  'Execute',
  'Limit rows',
  'File upload',
  'File uploads are disabled.',
  'From server',
  'Webserver file %s',
  'Run file',
  'Stop on error',
  'Show only errors',
  'History',
  'Clear',
  'Edit all',
  'Item has been deleted.',
  'Item has been updated.',
  'Item%s has been inserted.',
  'Table has been dropped.',
  'Table has been altered.',
  'Table has been created.',
  'Table name',
  'engine',
  'Default values',
  'Drop %s?',
  'Partition by',
  'Partitions',
  'Partition name',
  'Values',
  'Indexes have been altered.',
  'Index Type',
  'Column (length)',
  'Name',
  'Database has been dropped.',
  'Database has been renamed.',
  'Database has been created.',
  'Database has been altered.',
  'Call',
  [
    'Routine has been called, %d row affected.',
    'Routine has been called, %d rows affected.',
  ],
  'Foreign key has been dropped.',
  'Foreign key has been altered.',
  'Foreign key has been created.',
  'Source and target columns must have the same data type, there must be an index on the target columns and referenced data must exist.',
  'Foreign key',
  'Target table',
  'Schema',
  'Change',
  'Add column',
  'View has been altered.',
  'View has been dropped.',
  'View has been created.',
  'Create view',
  'Event has been dropped.',
  'Event has been altered.',
  'Event has been created.',
  'Alter event',
  'Create event',
  'Start',
  'End',
  'Every',
  'On completion preserve',
  'Routine has been dropped.',
  'Routine has been altered.',
  'Routine has been created.',
  'Alter function',
  'Alter procedure',
  'Create function',
  'Create procedure',
  'Return type',
  'Trigger has been dropped.',
  'Trigger has been altered.',
  'Trigger has been created.',
  'Alter trigger',
  'Create trigger',
  'Time',
  'Event',
  'User has been dropped.',
  'User has been altered.',
  'User has been created.',
  'Hashed',
  'Routine',
  'Grant',
  'Revoke',
  [
    '%d process has been killed.',
    '%d processes have been killed.',
  ],
  'Clone',
  '%d in total',
  'Kill',
  [
    '%d item has been affected.',
    '%d items have been affected.',
  ],
  'Ctrl+click on a value to modify it.',
  'File must be in UTF-8 encoding.',
  [
    '%d row has been imported.',
    '%d rows have been imported.',
  ],
  'Unable to select the table',
  'Modify',
  'Relations',
  'edit',
  'Use edit link to modify this value.',
  'Load more data',
  'Loading',
  'Page',
  'last',
  'Whole result',
  'Tables have been truncated.',
  'Tables have been moved.',
  'Tables have been copied.',
  'Tables have been dropped.',
  'Tables have been optimized.',
  'Tables and views',
  'Search data in tables',
  'Engine',
  'Data Length',
  'Index Length',
  'Data Free',
  'Rows',
  'Vacuum',
  'Optimize',
  'Analyze',
  'Check',
  'Repair',
  'Truncate',
  'Move to other database',
  'Move',
  'Copy',
  'overwrite',
  'Schedule',
  'At given time',
  [
    '%d e-mail has been sent.',
    '%d e-mails have been sent.',
  ],
];
		case "ar": return [
  'هل أنت متأكد؟',
  '%.3f s',
  'يتعذر رفع ملف ما.',
  'حجم الملف الأقصى هو %sB.',
  'الملف غير موجود.',
  ',',
  '0123456789',
  'فارغ',
  'الأصلي',
  'لا توجد جداول.',
  'تعديل',
  'إنشاء',
  'لا توجد نتائج.',
  'You have no privileges to update this table.',
  'حفظ',
  'إحفظ و واصل التعديل',
  'جفظ و إنشاء التالي',
  'Saving',
  'مسح',
  'اللغة',
  'استعمال',
  'Unknown error.',
  'النظام',
  'الخادم',
  'اسم المستخدم',
  'كلمة المرور',
  'قاعدة بيانات',
  'تسجيل الدخول',
  'تسجيل دخول دائم',
  'Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',
  'عرض البيانات',
  'عرض التركيبة',
  'تعديل عرض',
  'تعديل الجدول',
  'عنصر جديد',
  'Warnings',
  '%d بايت',
  'عمود',
  'النوع',
  'تعليق',
  'تزايد تلقائي',
  'Default value',
  'اختيار',
  'الدوال',
  'تجميع',
  'بحث',
  'في اي مكان',
  'ترتيب',
  'تنازلي',
  'حد',
  'طول النص',
  'الإجراء',
  'Full table scan',
  'استعلام SQL',
  'فتح',
  'حفظ',
  'تعديل قاعدة البيانات',
  'تعديل المخطط',
  'إنشاء مخطط',
  'مخطط فاعدة البيانات',
  'الإمتيازات',
  'استيراد',
  'تصدير',
  'إنشاء جدول',
  'قاعدة بيانات',
  'DB',
  'تحديد',
  'Disable %s or enable %s or %s extensions.',
  'سلاسل',
  'أعداد',
  'التاريخ و الوقت',
  'قوائم',
  'ثنائية',
  'هندسة',
  'rtl',
  'You are offline.',
  'تسجيل الخروج',
  'Too many unsuccessful logins, try again in %d minute(s).',
  'تم تسجيل الخروج بنجاح.',
  'Thanks for using Adminer, consider <a href="https://www.adminer.org/en/donation/">donating</a>.',
  'إنتهت الجلسة، من فضلك أعد تسجيل الدخول.',
  'Master password expired. <a href="https://www.adminer.org/en/extension/"%s>Implement</a> %s method to make it permanent.',
  'عليك تفعيل نظام الجلسات.',
  'The action will be performed after successful login with the same credentials.',
  'امتداد غير موجود',
  'إمتدادات php المدعومة غير موجودة.',
  'Connecting to privileged ports is not allowed.',
  'بيانات الدخول غير صالحة.',
  'There is a space in the input password which might be the cause.',
  'رمز CSRF غير صالح. المرجو إرسال الاستمارة مرة أخرى.',
  'لقد تجاوزت العدد الأقصى للحقول. يرجى الرفع من %s.',
  'If you did not send this request from Adminer then close this page.',
  'معلومات POST كبيرة جدا. قم بتقليص حجم المعلومات أو قم بزيادة قيمة %s في خيارات ال PHP.',
  'You can upload a big SQL file via FTP and import it from server.',
  'مفاتيح أجنبية',
  'الترتيب',
  'ON UPDATE',
  'ON DELETE',
  'اسم العمود',
  'اسم المتغير',
  'الطول',
  'خيارات',
  'إضافة التالي',
  'نقل للأعلى',
  'نقل للأسفل',
  'مسح',
  'قاعدة البيانات غير صالحة.',
  'تم حذف قواعد البيانات.',
  'اختر قاعدة البيانات',
  'إنشاء قاعدة بيانات',
  'قائمة الإجراءات',
  'متغيرات',
  'حالة',
  'النسخة %s : %s عن طريق إمتداد ال PHP %s',
  'تم تسجيل الدخول باسم %s',
  'تحديث',
  'ترتيب',
  'جداول',
  'Size',
  'Compute',
  'Selected',
  'حذف',
  'Materialized view',
  'عرض',
  'جدول',
  'المؤشرات',
  'تعديل المؤشرات',
  'المصدر',
  'الهدف',
  'تعديل',
  'إضافة مفتاح أجنبي',
  'الزنادات',
  'إضافة زناد',
  'رابط دائم',
  'إخراج',
  'الصيغة',
  'الروتينات',
  'الأحداث',
  'معلومات',
  'إنشاء مستخدم',
  'ATTACH queries are not supported.',
  'هناك خطأ في الاستعلام',
  '%d / ',
  '%d أسطر',
  'تم تنفسذ الاستعلام, %d عدد الأسطر المعدلة.',
  'لا توجد أوامر للتنفيذ.',
  [
    'تم تنفيذ الاستعلام %d بنجاح.',
    'تم تنفيذ الاستعلامات %d بنجاح.',
  ],
  'تنفيذ',
  'Limit rows',
  'رفع ملف',
  'رفع الملفات غير مشغل.',
  'من الخادم',
  'ملف %s من خادم الويب',
  'نفذ الملف',
  'أوقف في حالة حدوث خطأ',
  'إظهار الأخطاء فقط',
  'تاريخ',
  'مسح',
  'تعديل الكل',
  'تم حذف العنصر.',
  'تم تعديل العنصر.',
  'تم إدراج العنصر.',
  'تم حذف الجدول.',
  'تم تعديل الجدول.',
  'تم إنشاء الجدول.',
  'اسم الجدول',
  'المحرك',
  'القيم الافتراضية',
  'Drop %s?',
  'مقسم بواسطة',
  'التقسيمات',
  'اسم التقسيم',
  'القيم',
  'تم تعديل المؤشر.',
  'نوع المؤشر',
  'العمود (الطول)',
  'الاسم',
  'تم حذف قاعدة البيانات.',
  'تمت إعادة تسمية فاعدة البيانات.',
  'تم إنشاء قاعدة البيانات.',
  'تم تعديل قاعدة البيانات.',
  'استدعاء',
  'تم استدعاء الروتين, عدد الأسطر المعدلة %d.',
  'تم مسح المفتاح الأجنبي.',
  'تم تعديل المفتاح الأجنبي.',
  'تم إنشاء المفتاح الأجنبي.',
  'أعمدة المصدر و الهدف يجب أن تكون بنفس النوع, يجب أن يكون هناك مؤشر في أعمدة الهدف و البيانات المرجعية يجب ان تكون موجودة.',
  'مفتاح أجنبي',
  'الجدول المستهدف',
  'المخطط',
  'تعديل',
  'إضافة عمودا',
  'تم تعديل العرض.',
  'تم مسح العرض.',
  'تم إنشاء العرض.',
  'إنشاء عرض',
  'تم مسح الحدث.',
  'تم تعديل الحدث.',
  'تم إنشاء الحدث.',
  'تعديل حدث',
  'إنشاء حدث',
  'إبدأ',
  'إنهاء',
  'كل',
  'حفظ عند الإنتهاء',
  'تم حذف الروتين.',
  'تم تعديل الروتين.',
  'تم إنشاء الروتين.',
  'تعديل الدالة',
  'تعديل الإجراء',
  'إنشاء دالة',
  'إنشاء إجراء',
  'نوع العودة',
  'تم حذف الزناد.',
  'تم تعديل الزناد.',
  'تم إنشاء الزناد.',
  'تعديل زناد',
  'إنشاء زناد',
  'الوقت',
  'الحدث',
  'تم حذف المستخدم.',
  'تم تعديل المستخدم.',
  'تم إنشاء المستخدم.',
  'تلبيد',
  'روتين',
  'موافق',
  'إلغاء',
  'عدد الإجراءات التي تم إيقافها %d.',
  'نسخ',
  '%d في المجموع',
  'إيقاف',
  'عدد العناصر المعدلة هو %d.',
  'Ctrl+click on a value to modify it.',
  'File must be in UTF-8 encoding.',
  'تم استيراد %d سطرا',
  'يتعذر اختيار الجدول',
  'Modify',
  'علاقات',
  'تعديل',
  'استعمل الرابط "تعديل" لتعديل هذه القيمة.',
  'Load more data',
  'Loading',
  'صفحة',
  'الأخيرة',
  'نتيجة كاملة',
  'تم قطع الجداول.',
  'تم نقل الجداول.',
  'تم نسخ الجداول.',
  'تم حذف الجداول.',
  'Tables have been optimized.',
  'الجداول و العروض',
  'بحث في الجداول',
  'المحرك',
  'طول المعطيات.',
  'طول المؤشر.',
  'المساحة الحرة',
  'الأسطر',
  'Vacuum',
  'تحسين',
  'تحليل',
  'فحص',
  'إصلاح',
  'قطع',
  'نقل إلى قاعدة بيانات أخرى',
  'نقل',
  'نسخ',
  'overwrite',
  'مواعيد',
  'في وقت محدد',
  'HH:MM:SS',
];
		case "bg": return [
  'Сигурни ли сте?',
  '%.3f s',
  'Неуспешно прикачване на файл.',
  'Максимално разрешената големина на файл е %sB.',
  'Файлът не съществува.',
  ',',
  '0123456789',
  'празно',
  'оригинал',
  'Няма таблици.',
  'Редактиране',
  'Вмъкване',
  'Няма редове.',
  'Нямате праве за обновяване на таблицата.',
  'Запис',
  'Запис и редакция',
  'Запис и нов',
  'Записване',
  'Изтриване',
  'Език',
  'Избор',
  'Unknown error.',
  'Система',
  'Сървър',
  'Потребител',
  'Парола',
  'База данни',
  'Вход',
  'Запаметяване',
  'Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',
  'Показване на данни',
  'Структура',
  'Промяна на изглед',
  'Промяна на таблица',
  'Нов елемент',
  'Warnings',
  [
    '%d байт',
    '%d байта',
  ],
  'Колона',
  'Вид',
  'Коментар',
  'Автоматично увеличаване',
  'Стойност по подразбиране',
  'Показване',
  'Функции',
  'Съвкупност',
  'Търсене',
  'навсякъде',
  'Сортиране',
  'низходящо',
  'Редове',
  'Текст',
  'Действие',
  'Пълно сканиране на таблицата',
  'SQL команда',
  'показване',
  'запис',
  'Промяна на база данни',
  'Промяна на схемата',
  'Създаване на схема',
  'Схема на базата данни',
  'Права',
  'Импорт',
  'Експорт',
  'Създаване на таблица',
  'база данни',
  'DB',
  'показване',
  'Disable %s or enable %s or %s extensions.',
  'Низове',
  'Числа',
  'Дата и час',
  'Списъци',
  'Двоични',
  'Геометрия',
  'ltr',
  'Вие сте офлайн.',
  'Изход',
  [
    'Прекалено много неуспешни опити за вход, опитайте пак след %d минута.',
    'Прекалено много неуспешни опити за вход, опитайте пак след %d минути.',
  ],
  'Излизането е успешно.',
  'Thanks for using Adminer, consider <a href="https://www.adminer.org/en/donation/">donating</a>.',
  'Сесията е изтекла; моля, влезте отново.',
  'Главната парола вече е невалидна. <a href="https://www.adminer.org/en/extension/"%s>Изберете</a> %s метод, за да я направите постоянна.',
  'Поддръжката на сесии трябва да е разрешена.',
  'The action will be performed after successful login with the same credentials.',
  'Няма разширение',
  'Никое от поддържаните PHP разширения (%s) не е налично.',
  'Connecting to privileged ports is not allowed.',
  'Невалидни потребителски данни.',
  'There is a space in the input password which might be the cause.',
  'Невалиден шифроващ ключ. Попълнете и изпратете формуляра отново.',
  'Максималния брой полета е превишен. Моля, увеличете %s.',
  'Ако не сте изпратили тази заявка през Adminer, затворете тази страница.',
  'Изпратени са прекалено много данни. Намалете обема на данните или увеличете %s управляващата директива.',
  'Можете да прикачите голям SQL файл чрез FTP и да го импортирате от сървъра.',
  'Препратки',
  'кодировка',
  'При промяна',
  'При изтриване',
  'Име на колоната',
  'Име на параметъра',
  'Големина',
  'Опции',
  'Добавяне на следващ',
  'Преместване нагоре',
  'Преместване надолу',
  'Премахване',
  'Невалидна база данни.',
  'Базите данни бяха премехнати.',
  'Избор на база данни',
  'Създаване на база данни',
  'Списък с процеси',
  'Променливи',
  'Състояние',
  '%s версия: %s през PHP разширение %s',
  'Текущ потребител: %s',
  'Обновяване',
  'Кодировка',
  'Таблици',
  'Големина',
  'Изчисляване',
  'Избран',
  'Премахване',
  'Запаметен изглед',
  'Изглед',
  'Таблица',
  'Индекси',
  'Промяна на индекси',
  'Източник',
  'Цел',
  'Промяна',
  'Добавяне на препратка',
  'Тригери',
  'Добавяне на тригер',
  'Постоянна препратка',
  'Резултат',
  'Формат',
  'Процедури',
  'Събития',
  'Данни',
  'Създаване на потребител',
  'ATTACH queries are not supported.',
  'Грешка в заявката',
  '%d / ',
  [
    '%d ред',
    '%d реда',
  ],
  [
    'Заявката е изпълнена, %d ред е засегнат.',
    'Заявката е изпълнена, %d редове са засегнати.',
  ],
  'Няма команди за изпълнение.',
  [
    '%d заявка е изпълнена.',
    '%d заявки са изпълнени.',
  ],
  'Изпълнение',
  'Лимит на редовете',
  'Прикачване на файл',
  'Прикачването на файлове е забранено.',
  'От сървър',
  'Сървърен файл %s',
  'Изпълнение на файл',
  'Спиране при грешка',
  'Показване само на грешките',
  'Хронология',
  'Изчистване',
  'Редактиране на всички',
  'Елемента беше изтрит.',
  'Елемента беше обновен.',
  'Елементи%s бяха вмъкнати.',
  'Таблицата беше премахната.',
  'Таблицата беше променена.',
  'Таблицата беше създадена.',
  'Име на таблица',
  'система',
  'Стойности по подразбиране',
  'Drop %s?',
  'Разделяне на',
  'Раздели',
  'Име на раздела',
  'Стойности',
  'Индексите бяха променени.',
  'Вид на индекса',
  'Колона (дължина)',
  'Име',
  'Базата данни беше премахната.',
  'Базата данни беше преименувана.',
  'Базата данни беше създадена.',
  'Базата данни беше променена.',
  'Прилагане',
  [
    'Беше приложена процедура, %d ред е засегнат.',
    'Беше приложена процедура, %d редове са засегнати.',
  ],
  'Препратката беше премахната.',
  'Препратката беше променена.',
  'Препратката беше създадена.',
  'Колоните източник и цел трябва да са от еднакъв вид, трябва да има индекс на колоните приемник и да има въведени данни.',
  'Препратка',
  'Таблица приемник',
  'Схема',
  'Промяна',
  'Добавяне на колона',
  'Изгледа беше променен.',
  'Изгледа беше премахнат.',
  'Изгледа беше създаден.',
  'Създаване на изглед',
  'Събитието беше премахнато.',
  'Събитието беше променено.',
  'Събитието беше създадено.',
  'Промяна на събитие',
  'Създаване на събитие',
  'Начало',
  'Край',
  'Всеки',
  'Запазване след завършване',
  'Процедурата беше премахната.',
  'Процедурата беше променена.',
  'Процедурата беше създадена.',
  'Промяна на функция',
  'Промяна на процедура',
  'Създаване на функция',
  'Създаване на процедура',
  'Резултат',
  'Тригера беше премахнат.',
  'Тригера беше променен.',
  'Тригера беше създаден.',
  'Промяна на тригер',
  'Създаване на тригер',
  'Време',
  'Събитие',
  'Потребителя беше премахнат.',
  'Потребителя беше променен.',
  'Потребителя беше създаден.',
  'Хеширан',
  'Процедура',
  'Осигуряване',
  'Отнемане',
  [
    '%d процес беше прекъснат.',
    '%d процеса бяха прекъснати.',
  ],
  'Клониране',
  '%d всичко',
  'Прекъсване',
  [
    '%d елемент беше засегнат.',
    '%d елемента бяха засегнати.',
  ],
  'Ctrl+щракване в стойността, за да я промените.',
  'Файла трябва да е с UTF-8 кодировка.',
  [
    '%d ред беше импортиран.',
    '%d реда бяха импортирани.',
  ],
  'Неуспешно показване на таблицата',
  'Промяна',
  'Зависимости',
  'редакция',
  'Използвайте "редакция" за промяна на данните.',
  'Зареждане на повече данни',
  'Зареждане',
  'Страница',
  'последен',
  'Пълен резултат',
  'Таблиците бяха изрязани.',
  'Таблиците бяха преместени.',
  'Таблиците бяха копирани.',
  'Таблиците бяха премахнати.',
  'Таблиците бяха оптимизирани.',
  'Таблици и изгледи',
  'Търсене на данни в таблиците',
  'Система',
  'Големина на данните',
  'Големина на индекса',
  'Свободно място',
  'Редове',
  'Консолидиране',
  'Оптимизиране',
  'Анализиране',
  'Проверка',
  'Поправка',
  'Изрязване',
  'Преместване в друга база данни',
  'Преместване',
  'Копиране',
  'overwrite',
  'Насрочване',
  'В зададено време',
  'Промяна на вид',
];
		case "bn": return [
  'তুমি কি নিশ্চিত?',
  '%.3f s',
  'ফাইল আপলোড করা সম্ভব হচ্ছে না।',
  'সর্বাধিক অনুমোদিত ফাইল সাইজ %sB.',
  'ফাইলের কোন অস্তিত্ব নেই।',
  ',',
  '০১২৩৪৫৬৭৮৯',
  'খালি',
  'প্রকৃত',
  'কোন টেবিল নাই।',
  'সম্পাদনা',
  'সংযোজন',
  'কোন সারি নাই।',
  'You have no privileges to update this table.',
  'সংরক্ষণ',
  'সংরক্ষণ করো এবং সম্পাদনা চালিয়ে যাও',
  'সংরক্ষন ও পরবর্তী সংযোজন',
  'Saving',
  'মুছে ফেলো',
  'ভাষা',
  'ব্যবহার',
  'Unknown error.',
  'সিস্টেম',
  'সার্ভার',
  'ইউজারের নাম',
  'পাসওয়ার্ড',
  'ডাটাবেজ',
  'লগইন',
  'স্থায়ী লগইন',
  'Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',
  'তথ্য নির্বাচন করো',
  'গঠন দেখাও',
  'ভিউ সম্পাদনা করো',
  'টেবিল সম্পাদনা',
  'নতুন বিষয়বস্তু',
  'Warnings',
  [
    '%d বাইট',
    '%d বাইটসমূহ',
  ],
  'কলাম',
  'টাইপ',
  'মন্তব্য',
  'স্বয়ংক্রিয় বৃদ্ধি',
  'Default value',
  'নির্বাচন',
  'ফাংশন সমূহ',
  'মোট পরিমাণ',
  'খোঁজ',
  'যে কোন স্থানে',
  'সাজানো',
  'ক্রমহ্রাস',
  'সীমা',
  'টেক্সট দৈর্ঘ্য',
  'ক্রিয়া',
  'Full table scan',
  'SQL-কোয়্যারী',
  'খোলা',
  'সংরক্ষণ',
  'ডাটাবেজ সম্পাদনা',
  'স্কিমা পরিবর্তন করো',
  'স্কিমা তৈরী করো',
  'ডাটাবেজ স্কিমা',
  'প্রিভিলেজেস',
  'ইমপোর্ট',
  'এক্সপোর্ট',
  'টেবিল তৈরী করো',
  'ডাটাবেজ',
  'DB',
  'নির্বাচন',
  'Disable %s or enable %s or %s extensions.',
  'স্ট্রিং',
  'সংখ্যা',
  'তারিখ এবং সময়',
  'তালিকা',
  'বাইনারি',
  'জ্যামিতি',
  'ltr',
  'You are offline.',
  'লগআউট',
  'Too many unsuccessful logins, try again in %d minute(s).',
  'লগআউট সম্পন্ন হয়েছে।',
  'Thanks for using Adminer, consider <a href="https://www.adminer.org/en/donation/">donating</a>.',
  'সেশানের মেয়াদ শেষ হয়েছে, আবার লগইন করুন।',
  'Master password expired. <a href="https://www.adminer.org/en/extension/"%s>Implement</a> %s method to make it permanent.',
  'সেশন সমর্থন সক্রিয় করা আবশ্যক।',
  'The action will be performed after successful login with the same credentials.',
  'কোন এক্সটেনশান নাই',
  'কোন PHP সমর্থিত এক্সটেনশন (%s) পাওয়া যায় নাই।',
  'Connecting to privileged ports is not allowed.',
  'ভুল পাসওয়ার্ড।',
  'There is a space in the input password which might be the cause.',
  'অবৈধ CSRF টোকেন। ফর্ম আবার পাঠাও।',
  'অনুমোদিত ফিল্ড এর সর্বাধিক সংখ্যা অতিক্রম করে গেছে। অনুগ্রহপূর্বক %s বৃদ্ধি করুন।',
  'If you did not send this request from Adminer then close this page.',
  'খুব বড় POST ডাটা। ডাটা সংক্ষিপ্ত করো অথবা %s কনফিগারেশন নির্দেশ বৃদ্ধি করো',
  'You can upload a big SQL file via FTP and import it from server.',
  'ফরেন কী',
  'কলোকেশন',
  'অন আপডেট',
  'অন ডিলিট',
  'কলামের নাম',
  'প্যারামিটারের নাম',
  'দৈর্ঘ্য',
  'অপশন',
  'সংযোজন',
  'উপরে স্থানান্তর',
  'নীচে স্থানান্তর',
  'অপসারণ',
  'ভুল ডাটাবেজ।',
  'ডাটাবেজসমূহ মুছে ফেলা হয়েছে।',
  'ডাটাবেজ নির্বাচন করো',
  'ডাটাবেজ তৈরী',
  'প্রসেস তালিকা',
  'চলকসমূহ',
  'স্ট্যাটাস',
  'ভার্সন %s: %s, %s PHP এক্সটেনশনের মধ্য দিয়ে',
  '%s হিসাবে লগড',
  'রিফ্রেশ',
  'কলোকেশন',
  'টেবিলসমূহ',
  'Size',
  'Compute',
  'Selected',
  'মুছে ফেলো',
  'Materialized view',
  'ভিউ',
  'টেবিল',
  'সূচীসমূহ',
  'সূচীসমূহ সম্পাদনা',
  'উৎস',
  'লক্ষ্য',
  'সম্পাদনা',
  'ফরেন কী সংযোজন করো',
  'ট্রিগার',
  'ট্রিগার সংযোজন করো',
  'স্থায়ী লিংক',
  'আউটপুট',
  'বিন্যাস',
  'রুটিনসমূহ',
  'ইভেন্টসমূহ',
  'ডাটা',
  'ইউজার তৈরী করো',
  'ATTACH queries are not supported.',
  'কোয়্যারীতে ভুল আছে।',
  '%d / ',
  [
    '%d সারি',
    '%d সারি সমূহ',
  ],
  [
    'কোয়্যারী সম্পাদন হয়েছে, %d সারি প্রভাবিত হয়েছে।',
    'কোয়্যারী সম্পাদন হয়েছে, %d সারি প্রভাবিত হয়েছে।',
  ],
  'সম্পাদন করার মত কোন নির্দেশ নাই।',
  [
    'SQL-কোয়্যারী সফলভাবে সম্পন্ন হয়েছে',
    '%d SQL-কোয়্যারীসমূহ সফলভাবে সম্পন্ন হয়েছে',
  ],
  'সম্পাদন করো',
  'Limit rows',
  'ফাইল আপলোড',
  'ফাইল আপলোড নিষ্ক্রিয় করা আছে।',
  'সার্ভার থেকে',
  'ওয়েবসার্ভার ফাইল %s',
  'ফাইল চালাও',
  'ত্রুটি পেলে থেমে যাও',
  'শুধুমাত্র ত্রুটি দেখাও',
  'ইতিহাস',
  'সাফ করো',
  'সকল সম্পাদনা করো',
  'বিষয়বস্তু মুছে ফেলা হয়েছে।',
  'বিষয়বস্তু আপডেট করা হয়েছে।',
  'বিষয়বস্তুসমূহ সংযোজন করা হয়েছে।',
  'টেবিল মুছে ফেলা হয়েছে।',
  'টেবিল সম্পাদনা করা হয়েছে।',
  'টেবিল তৈরী করা হয়েছে।',
  'টেবিলের নাম',
  'ইন্জিন',
  'ডিফল্ট মান',
  'Drop %s?',
  'পার্টিশন যার মাধ্যমে',
  'পার্টিশন',
  'পার্টিশনের নাম',
  'মানসমূহ',
  'সূচীসমূহ সম্পাদনা করা হয়েছে।',
  'সূচী-ধরণ',
  'কলাম (দৈর্ঘ্য)',
  'নাম',
  'ডাটাবেজ মুছে ফেলা হয়েছে।',
  'ডাটাবেজের নতুন নামকরণ করা হয়েছে।',
  'ডাটাবেজ তৈরী করা হয়েছে।',
  'ডাটাবেজ সম্পাদনা করা হয়েছে।',
  'কল',
  [
    'রুটিন কল করা হয়েছে, %d টি সারি (সমূহ) প্রভাবিত হয়েছে।',
    'রুটিন কল করা হয়েছে, %d টি সারি (সমূহ) প্রভাবিত হয়েছে।',
  ],
  'ফরেন কী মুছে ফেলা হয়েছে।',
  'ফরেন কী সম্পাদনা করা হয়েছে।',
  'ফরেন কী তৈরী করা হয়েছে।',
  'সোর্স এবং টার্গেট কলামে একই ডাটা টাইপ থাকতে হবে, টার্গেট কলামসমূহে একটি সূচী এবং রেফারেন্সড ডেটার উপস্থিতি থাকা আবশ্যক।',
  'ফরেন কী ',
  'টার্গেট টেবিল',
  'স্কিমা',
  'পরিবর্তন',
  'কলাম সংযোজন',
  'ভিউ সম্পাদনা করা হয়েছে।',
  'ভিউ মুছে ফেলা হয়েছে।',
  'ভিউ তৈরী করা হয়েছে।',
  'ভিউ তৈরী করো',
  'ইভেন্ট মুছে ফেলা হয়েছে।',
  'ইভেন্ট সম্পাদনা করা হয়েছে।',
  'ইভেন্ট তৈরী করা হয়েছে।',
  'ইভেন্ট সম্পাদনা করো',
  'ইভেন্ট তৈরী করো',
  'শুরু',
  'সমাপ্তি',
  'প্রত্যেক',
  'সমাপ্ত হওয়ার পর সংরক্ষন করো',
  'রুটিন মুছে ফেলা হয়েছে।',
  'রুটিন সম্পাদনা করা হয়েছে।',
  'রুটিন তৈরী করা হয়েছে।',
  'ফাংশন সম্পাদনা করো',
  'প্রসিডিওর সম্পাদনা করো',
  'ফাংশন তৈরী করো',
  'প্রসিডিওর তৈরী করো',
  'রিটার্ন টাইপ',
  'ট্রিগার মুছে ফেলা হয়েছে।',
  'ট্রিগার সম্পাদনা করা হয়েছে।',
  'ট্রিগার তৈরী করা হয়েছে।',
  'ট্রিগার সম্পাদনা করো',
  'ট্রিগার তৈরী করো',
  'সময়',
  'ইভেন্ট',
  'ইউজার মুছে ফেলা হয়েছে।',
  'ইউজার সম্পাদনা করা হয়েছে।',
  'ইউজার তৈরী করা হয়েছে।',
  'হ্যাসড',
  'রুটিন',
  'গ্র্যান্ট',
  'রিভোক',
  [
    '%d টি প্রসেস (সমূহ) বিনষ্ট করা হয়েছে।',
    '%d টি প্রসেস (সমূহ) বিনষ্ট করা হয়েছে।',
  ],
  'ক্লোন',
  'সর্বমোটঃ %d টি',
  'বিনষ্ট করো',
  '%d টি বিষয়বস্তু প্রভাবিত হয়েছে',
  'Ctrl+click on a value to modify it.',
  'File must be in UTF-8 encoding.',
  [
    '%d টি সারি (সমূহ) ইমপোর্ট করা হয়েছে।',
    '%d টি সারি (সমূহ) ইমপোর্ট করা হয়েছে।',
  ],
  'টেবিল নির্বাচন করতে অক্ষম',
  'Modify',
  'সম্পর্ক',
  'সম্পাদনা',
  'এই মান পরিবর্তনের জন্য সম্পাদনা লিঙ্ক ব্যবহার করো।',
  'Load more data',
  'Loading',
  'পৃষ্ঠা',
  'সর্বশেষ',
  'সম্পূর্ণ ফলাফল',
  'টেবিল ছাঁটাই করা হয়েছে',
  'টেবিল স্থানান্তর করা হয়েছে।',
  'টেবিল কপি করা হয়েছে।',
  'টেবিলসমূহ মুছে ফেলা হয়েছে।',
  'Tables have been optimized.',
  'টেবিল এবং ভিউ সমূহ',
  'টেবিলে খোঁজ করো',
  'ইঞ্জিন',
  'ডাটার দৈর্ঘ্য',
  'ইনডেক্স এর দৈর্ঘ্য',
  'তথ্য মুক্ত',
  'সারি',
  'Vacuum',
  'অপটিমাইজ',
  'বিশ্লেষণ',
  'পরীক্ষা',
  'মেরামত',
  'ছাঁটাই',
  'অন্য ডাটাবেজে স্থানান্তর করো',
  'স্থানান্তর করো',
  'কপি',
  'overwrite',
  'সময়সূচি',
  'প্রদত্ত সময়ে',
  'HH:MM:SS',
];
		case "bs": return [
  'Da li ste sigurni?',
  '%.3f s',
  'Slanje datoteke nije uspelo.',
  'Najveća dozvoljena veličina datoteke je %sB.',
  'Datoteka ne postoji.',
  ',',
  '0123456789',
  'prazno',
  'original',
  'Bez tabela.',
  'Izmijeni',
  'Umetni',
  'Bez redova.',
  'You have no privileges to update this table.',
  'Sačuvaj',
  'Sačuvaj i nastavi uređenje',
  'Sačuvaj i umijetni slijedeće',
  'Saving',
  'Izbriši',
  'Jezik',
  'Koristi',
  'Unknown error.',
  'Sistem',
  'Server',
  'Korisničko ime',
  'Lozinka',
  'Baza podataka',
  'Prijava',
  'Trajna prijava',
  'Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',
  'Izaberi podatke',
  'Prikaži strukturu',
  'Ažuriraj pogled',
  'Ažuriraj tabelu',
  'Nova stavka',
  'Warnings',
  [
    '%d bajt',
    '%d bajta',
    '%d bajtova',
  ],
  'kolumna',
  'Tip',
  'Komentar',
  'Auto-priraštaj',
  'Default value',
  'Izaberi',
  'Funkcije',
  'Sakupljanje',
  'Pretraga',
  'bilo gdje',
  'Poređaj',
  'opadajuće',
  'Granica',
  'Dužina teksta',
  'Akcija',
  'Skreniranje kompletne tabele',
  'SQL komanda',
  'otvori',
  'spasi',
  'Ažuriraj bazu podataka',
  'Ažuriraj šemu',
  'Formiraj šemu',
  'Šema baze podataka',
  'Dozvole',
  'Uvoz',
  'Izvoz',
  'Napravi tabelu',
  'baza podataka',
  'DB',
  'izaberi',
  'Disable %s or enable %s or %s extensions.',
  'Tekst',
  'Broj',
  'Datum i vrijeme',
  'Liste',
  'Binarno',
  'Geometrija',
  'ltr',
  'You are offline.',
  'Odjava',
  'Too many unsuccessful logins, try again in %d minute(s).',
  'Uspešna odjava.',
  'Thanks for using Adminer, consider <a href="https://www.adminer.org/en/donation/">donating</a>.',
  'Vaša sesija je istekla, prijavite se ponovo.',
  'Master password expired. <a href="https://www.adminer.org/en/extension/"%s>Implement</a> %s method to make it permanent.',
  'Morate omogućiti podršku za sesije.',
  'The action will be performed after successful login with the same credentials.',
  'Bez dodataka',
  'Nijedan od podržanih PHP dodataka nije dostupan.',
  'Connecting to privileged ports is not allowed.',
  'Nevažeće dozvole.',
  'There is a space in the input password which might be the cause.',
  'Nevažeći CSRF kod. Proslijedite ponovo formu.',
  'Premašen je maksimalni broj dozvoljenih polja. Molim uvećajte %s.',
  'If you did not send this request from Adminer then close this page.',
  'Preveliki POST podatak. Morate da smanjite podatak ili povećajte vrijednost konfiguracione direktive %s.',
  'You can upload a big SQL file via FTP and import it from server.',
  'Strani ključevi',
  'Sravnjivanje',
  'ON UPDATE (prilikom osvežavanja)',
  'ON DELETE (prilikom brisanja)',
  'Naziv kolumne',
  'Naziv parametra',
  'Dužina',
  'Opcije',
  'Dodaj slijedeći',
  'Pomijeri na gore',
  'Pomijeri na dole',
  'Ukloni',
  'Neispravna baza podataka.',
  'Baze podataka su izbrisane.',
  'Izaberite bazu',
  'Formiraj bazu podataka',
  'Spisak procesa',
  'Promijenljive',
  'Status',
  '%s verzija: %s pomoću PHP dodatka je %s',
  'Prijavi se kao: %s',
  'Osveži',
  'Sravnjivanje',
  'Tabele',
  'Size',
  'Compute',
  'Izabrano',
  'Izbriši',
  'Materialized view',
  'Pogled',
  'Tabela',
  'Indeksi',
  'Ažuriraj indekse',
  'Izvor',
  'Cilj',
  'Ažuriraj',
  'Dodaj strani ključ',
  'Okidači',
  'Dodaj okidač',
  'Trajna veza',
  'Ispis',
  'Format',
  'Rutine',
  'Događaji',
  'Podaci',
  'Novi korisnik',
  'ATTACH queries are not supported.',
  'Greška u upitu',
  '%d / ',
  [
    '%d red',
    '%d reda',
    '%d redova',
  ],
  [
    'Upit je uspiješno izvršen, %d red je ažuriran.',
    'Upit je uspiješno izvršen, %d reda su ažurirana.',
    'Upit je uspiješno izvršen, %d redova je ažurirano.',
  ],
  'Bez komandi za izvršavanje.',
  [
    '%d upit je uspiješno izvršen.',
    '%d upita su uspiješno izvršena.',
    '%d upita je uspiješno izvršeno.',
  ],
  'Izvrši',
  'Limit rows',
  'Slanje datoteka',
  'Onemogućeno je slanje datoteka.',
  'Sa servera',
  'Datoteka %s sa veb servera',
  'Pokreni datoteku',
  'Zaustavi prilikom greške',
  'Prikazuj samo greške',
  'Historijat',
  'Očisti',
  'Izmijeni sve',
  'Stavka je izbrisana.',
  'Stavka je izmijenjena.',
  'Stavka %s je spašena.',
  'Tabela je izbrisana.',
  'Tabela je izmijenjena.',
  'Tabela je spašena.',
  'Naziv tabele',
  'stroj',
  'Podrazumijevane vrijednosti',
  'Drop %s?',
  'Podijeli po',
  'Podijele',
  'Ime podijele',
  'Vrijednosti',
  'Indeksi su izmijenjeni.',
  'Tip indeksa',
  'kolumna (dužina)',
  'Ime',
  'Baza podataka je izbrisana.',
  'Baza podataka je preimenovana.',
  'Baza podataka je spašena.',
  'Baza podataka je izmijenjena.',
  'Pozovi',
  [
    'Pozvana je rutina, %d red je ažuriran.',
    'Pozvana je rutina, %d reda su ažurirani.',
    'Pozvana je rutina, %d redova je ažurirano.',
  ],
  'Strani ključ je izbrisan.',
  'Strani ključ je izmijenjen.',
  'Strani ključ je spašen.',
  'Izvorne i ciljne kolumne moraju biti istog tipa, ciljna kolumna mora biti indeksirana i izvorna tabela mora sadržati podatke iz ciljne.',
  'Strani ključ',
  'Ciljna tabela',
  'Šema',
  'izmijeni',
  'Dodaj kolumnu',
  'Pogled je izmijenjen.',
  'Pogled je izbrisan.',
  'Pogled je spašen.',
  'Napravi pogled',
  'Događaj je izbrisan.',
  'Događaj je izmijenjen.',
  'Događaj je spašen.',
  'Ažuriraj događaj',
  'Napravi događaj',
  'Početak',
  'Kraj',
  'Svaki',
  'Zadrži po završetku',
  'Rutina je izbrisana.',
  'Rutina je izmijenjena.',
  'Rutina je spašena.',
  'Ažuriraj funkciju',
  'Ažuriraj proceduru',
  'Formiraj funkciju',
  'Formiraj proceduru',
  'Povratni tip',
  'Okidač je izbrisan.',
  'Okidač je izmijenjen.',
  'Okidač je spašen.',
  'Ažuriraj okidač',
  'Formiraj okidač',
  'Vrijeme',
  'Događaj',
  'Korisnik je izbrisan.',
  'Korisnik je izmijenjen.',
  'korisnik je spašen.',
  'Heširano',
  'Rutina',
  'Dozvoli',
  'Opozovi',
  [
    '%d proces je ukinut.',
    '%d procesa su ukinuta.',
    '%d procesa je ukinuto.',
  ],
  'Dupliraj',
  'ukupno %d',
  'Ubij',
  [
    '%d stavka je ažurirana.',
    '%d stavke su ažurirane.',
    '%d stavki je ažurirano.',
  ],
  'Ctrl+klik na vrijednost za izmijenu.',
  'File must be in UTF-8 encoding.',
  [
    '%d red je uvežen.',
    '%d reda su uvežena.',
    '%d redova je uveženo.',
  ],
  'Ne mogu da izaberem tabelu',
  'Izmjene',
  'Odnosi',
  'izmijeni',
  'Koristi vezu za izmijenu ove vrijednosti.',
  'Učitavam još podataka',
  'Učitavam',
  'Strana',
  'poslijednja',
  'Ceo rezultat',
  'Tabele su ispražnjene.',
  'Tabele su premješćene.',
  'Tabele su umnožene.',
  'Tabele su izbrisane.',
  'Tabele su optimizovane.',
  'Tabele i pogledi',
  'Pretraži podatke u tabelama',
  'Stroj',
  'Dužina podataka',
  'Dužina indeksa',
  'Slobodno podataka',
  'Redova',
  'Vacuum',
  'Optimizuj',
  'Analiziraj',
  'Provjeri',
  'Popravi',
  'Isprazni',
  'Premijesti u drugu bazu podataka',
  'Premijesti',
  'Umnoži',
  'overwrite',
  'Raspored',
  'U zadato vrijeme',
  'Ažuriraj tip',
];
		case "ca": return [
  'Estàs segur?',
  '%.3f s',
  'Impossible adjuntar el fitxer.',
  'La mida màxima permesa del fitxer és de %sB.',
  'El fitxer no existeix.',
  ',',
  '0123456789',
  'buit',
  'original',
  'No hi ha cap taula.',
  'Edita',
  'Insereix',
  'No hi ha cap registre.',
  'You have no privileges to update this table.',
  'Desa',
  'Desa i segueix editant',
  'Desa i insereix el següent',
  'Saving',
  'Suprimeix',
  'Idioma',
  'Utilitza',
  'Unknown error.',
  'Sistema',
  'Servidor',
  'Nom d\'usuari',
  'Contrasenya',
  'Base de dades',
  'Inicia la sessió',
  'Sessió permanent',
  'Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',
  'Selecciona dades',
  'Mostra l\'estructura',
  'Modifica la vista',
  'Modifica la taula',
  'Nou element',
  'Warnings',
  [
    '%d byte',
    '%d bytes',
  ],
  'Columna',
  'Tipus',
  'Comentari',
  'Increment automàtic',
  'Default value',
  'Selecciona',
  'Funcions',
  'Agregació',
  'Cerca',
  'a qualsevol lloc',
  'Ordena',
  'descendent',
  'Límit',
  'Longitud del text',
  'Acció',
  'Full table scan',
  'Ordre SQL',
  'obre',
  'desa',
  'Modifica la base de dades',
  'Modifica l\'esquema',
  'Crea un esquema',
  'Esquema de la base de dades',
  'Privilegis',
  'Importa',
  'Exporta',
  'Crea una taula',
  'base de dades',
  'DB',
  'registres',
  'Disable %s or enable %s or %s extensions.',
  'Cadenes',
  'Nombres',
  'Data i hora',
  'Llistes',
  'Binari',
  'Geometria',
  'ltr',
  'You are offline.',
  'Desconnecta',
  'Too many unsuccessful logins, try again in %d minute(s).',
  'Desconnexió correcta.',
  'Thanks for using Adminer, consider <a href="https://www.adminer.org/en/donation/">donating</a>.',
  'La sessió ha expirat, torna a iniciar-ne una.',
  'Master password expired. <a href="https://www.adminer.org/en/extension/"%s>Implement</a> %s method to make it permanent.',
  'Cal que estigui permès l\'us de sessions.',
  'The action will be performed after successful login with the same credentials.',
  'Cap extensió',
  'No hi ha cap de les extensions PHP suportades (%s) disponible.',
  'Connecting to privileged ports is not allowed.',
  'Credencials invàlides.',
  'There is a space in the input password which might be the cause.',
  'Token CSRF invàlid. Torna a enviar el formulari.',
  'S\'ha assolit el nombre màxim de camps. Incrementa %s.',
  'If you did not send this request from Adminer then close this page.',
  'Les dades POST són massa grans. Redueix les dades o incrementa la directiva de configuració %s.',
  'You can upload a big SQL file via FTP and import it from server.',
  'Claus foranes',
  'compaginació',
  'ON UPDATE',
  'ON DELETE',
  'Nom de la columna',
  'Nom del paràmetre',
  'Llargada',
  'Opcions',
  'Afegeix el següent',
  'Mou a dalt',
  'Mou a baix',
  'Suprimeix',
  'Base de dades invàlida.',
  'S\'han suprimit les bases de dades.',
  'Selecciona base de dades',
  'Crea una base de dades',
  'Llista de processos',
  'Variables',
  'Estat',
  'Versió %s: %s amb l\'extensió de PHP %s',
  'Connectat com a: %s',
  'Refresca',
  'Compaginació',
  'Taules',
  'Size',
  'Compute',
  'Selected',
  'Suprimeix',
  'Materialized view',
  'Vista',
  'Taula',
  'Índexs',
  'Modifica els índex',
  'Font',
  'Destí',
  'Modifica',
  'Afegeix una clau forana',
  'Activadors',
  'Afegeix un activador',
  'Enllaç permanent',
  'Sortida',
  'Format',
  'Rutines',
  'Events',
  'Dades',
  'Crea un usuari',
  'ATTACH queries are not supported.',
  'Error en la consulta',
  '%d / ',
  [
    '%d registre',
    '%d registres',
  ],
  [
    'Consulta executada correctament, %d registre modificat.',
    'Consulta executada correctament, %d registres modificats.',
  ],
  'Cap comanda per executar.',
  [
    '%d consulta executada correctament.',
    '%d consultes executades correctament.',
  ],
  'Executa',
  'Limit rows',
  'Adjunta un fitxer',
  'La pujada de fitxers està desactivada.',
  'En el servidor',
  'Fitxer %s del servidor web',
  'Executa el fitxer',
  'Atura en trobar un error',
  'Mostra només els errors',
  'Història',
  'Suprimeix',
  'Edita-ho tot',
  'S\'ha suprimit l\'element.',
  'S\'ha actualitzat l\'element.',
  'S\'ha insertat l\'element%s.',
  'S\'ha suprimit la taula.',
  'S\'ha modificat la taula.',
  'S\'ha creat la taula.',
  'Nom de la taula',
  'motor',
  'Valors per defecte',
  'Drop %s?',
  'Fes particions segons',
  'Particions',
  'Nom de la partició',
  'Valors',
  'S\'han modificat els índex.',
  'Tipus d\'índex',
  'Columna (longitud)',
  'Nom',
  'S\'ha suprimit la base de dades.',
  'S\'ha canviat el nom de la base de dades.',
  'S\'ha creat la base de dades.',
  'S\'ha modificat la base de dades.',
  'Crida',
  [
    'S\'ha cridat la rutina, %d registre modificat.',
    'S\'ha cridat la rutina, %d registres modificats.',
  ],
  'S\'ha suprimit la clau forana.',
  'S\'ha modificat la clau forana.',
  'S\'ha creat la clau forana.',
  'Les columnes d\'origen i de destinació han de ser del mateix tipus, la columna de destinació ha d\'estar indexada i les dades referenciades han d\'existir.',
  'Clau forana',
  'Taula de destinació',
  'Esquema',
  'Canvi',
  'Afegeix una columna',
  'S\'ha modificat la vista.',
  'S\'ha suprimit la vista.',
  'S\'ha creat la vista.',
  'Crea una vista',
  'S\'ha suprimit l\'event.',
  'S\'ha modificat l\'event.',
  'S\'ha creat l\'event.',
  'Modifica l\'event',
  'Crea un event',
  'Comença',
  'Acaba',
  'Cada',
  'Conservar en completar',
  'S\'ha suprimit la rutina.',
  'S\'ha modificat la rutina.',
  'S\'ha creat la rutina.',
  'Modifica la funció',
  'Modifica el procediment',
  'Crea una funció',
  'Crea un procediment',
  'Tipus retornat',
  'S\'ha suprimit l\'activador.',
  'S\'ha modificat l\'activador.',
  'S\'ha creat l\'activador.',
  'Modifica l\'activador',
  'Crea un activador',
  'Temps',
  'Event',
  'S\'ha suprimit l\'usuari.',
  'S\'ha modificat l\'usuari.',
  'S\'ha creat l\'usuari.',
  'Hashed',
  'Rutina',
  'Grant',
  'Revoke',
  [
    'S\'ha aturat %d procés.',
    'S\'han aturat %d processos.',
  ],
  'Clona',
  '%d en total',
  'Atura',
  [
    'S\'ha modificat %d element.',
    'S\'han modificat %d elements.',
  ],
  'Fes un Ctrl+clic a un valor per modificar-lo.',
  'File must be in UTF-8 encoding.',
  [
    'S\'ha importat %d registre.',
    'S\'han importat %d registres.',
  ],
  'Impossible seleccionar la taula',
  'Modify',
  'Relacions',
  'edita',
  'Utilitza l\'enllaç d\'edició per modificar aquest valor.',
  'Load more data',
  'Loading',
  'Plana',
  'darrera',
  'Tots els resultats',
  'S\'han escapçat les taules.',
  'S\'han desplaçat les taules.',
  'S\'han copiat les taules.',
  'S\'han suprimit les taules.',
  'Tables have been optimized.',
  'Taules i vistes',
  'Cerca dades en les taules',
  'Motor',
  'Longitud de les dades',
  'Longitud de l\'índex',
  'Espai lliure',
  'Files',
  'Vacuum',
  'Optimitza',
  'Analitza',
  'Verifica',
  'Repara',
  'Escapça',
  'Desplaça a una altra base de dades',
  'Desplaça',
  'Còpia',
  'overwrite',
  'Horari',
  'A un moment donat',
  'HH:MM:SS',
];
		case "cs": return [
  'Opravdu?',
  '%.3f s',
  'Nepodařilo se nahrát soubor.',
  'Maximální povolená velikost souboru je %sB.',
  'Soubor neexistuje.',
  ' ',
  '0123456789',
  'prázdné',
  'původní',
  'Žádné tabulky.',
  'Upravit',
  'Vložit',
  'Žádné řádky.',
  'Nemáte oprávnění editovat tuto tabulku.',
  'Uložit',
  'Uložit a pokračovat v editaci',
  'Uložit a vložit další',
  'Ukládá se',
  'Smazat',
  'Jazyk',
  'Vybrat',
  'Neznámá chyba.',
  'Systém',
  'Server',
  'Uživatel',
  'Heslo',
  'Databáze',
  'Přihlásit se',
  'Trvalé přihlášení',
  'Adminer nepodporuje přístup k databázi bez hesla, <a href="https://www.adminer.org/cs/password/"%s>více informací</a>.',
  'Vypsat data',
  'Zobrazit strukturu',
  'Pozměnit pohled',
  'Pozměnit tabulku',
  'Nová položka',
  'Varování',
  [
    '%d bajt',
    '%d bajty',
    '%d bajtů',
  ],
  'Sloupec',
  'Typ',
  'Komentář',
  'Auto Increment',
  'Výchozí hodnota',
  'Vypsat',
  'Funkce',
  'Agregace',
  'Vyhledat',
  'kdekoliv',
  'Seřadit',
  'sestupně',
  'Limit',
  'Délka textů',
  'Akce',
  'Průchod celé tabulky',
  'SQL příkaz',
  'otevřít',
  'uložit',
  'Pozměnit databázi',
  'Pozměnit schéma',
  'Vytvořit schéma',
  'Schéma databáze',
  'Oprávnění',
  'Import',
  'Export',
  'Vytvořit tabulku',
  'databáze',
  'DB',
  'vypsat',
  'Zakažte %s nebo povolte extenze %s nebo %s.',
  'Řetězce',
  'Čísla',
  'Datum a čas',
  'Seznamy',
  'Binární',
  'Geometrie',
  'ltr',
  'Jste offline.',
  'Odhlásit',
  [
    'Příliš mnoho pokusů o přihlášení, zkuste to znovu za %d minutu.',
    'Příliš mnoho pokusů o přihlášení, zkuste to znovu za %d minuty.',
    'Příliš mnoho pokusů o přihlášení, zkuste to znovu za %d minut.',
  ],
  'Odhlášení proběhlo v pořádku.',
  'Díky za použití Admineru, <a href="https://www.adminer.org/cs/donation/">přispějte</a> na vývoj.',
  'Session vypršela, přihlašte se prosím znovu.',
  'Platnost hlavního hesla vypršela. <a href="https://www.adminer.org/cs/extension/"%s>Implementujte</a> metodu %s, aby platilo stále.',
  'Session proměnné musí být povolené.',
  'Akce bude provedena po úspěšném přihlášení se stejnými přihlašovacími údaji.',
  'Žádné rozšíření',
  'Není dostupné žádné z podporovaných PHP rozšíření (%s).',
  'Připojování k privilegovaným portům není povoleno.',
  'Neplatné přihlašovací údaje.',
  'Problém může být, že je v zadaném hesle mezera.',
  'Neplatný token CSRF. Odešlete formulář znovu.',
  'Byl překročen maximální povolený počet polí. Zvyšte prosím %s.',
  'Pokud jste tento požadavek neposlali z Adminera, tak tuto stránku zavřete.',
  'Příliš velká POST data. Zmenšete data nebo zvyšte hodnotu konfigurační direktivy %s.',
  'Velký SQL soubor můžete nahrát pomocí FTP a importovat ho ze serveru.',
  'Cizí klíče',
  'porovnávání',
  'Při změně',
  'Při smazání',
  'Název sloupce',
  'Název parametru',
  'Délka',
  'Volby',
  'Přidat další',
  'Přesunout nahoru',
  'Přesunout dolů',
  'Odebrat',
  'Nesprávná databáze.',
  'Databáze byly odstraněny.',
  'Vybrat databázi',
  'Vytvořit databázi',
  'Seznam procesů',
  'Proměnné',
  'Stav',
  'Verze %s: %s přes PHP rozšíření %s',
  'Přihlášen jako: %s',
  'Obnovit',
  'Porovnávání',
  'Tabulky',
  'Velikost',
  'Spočítat',
  'Označené',
  'Odstranit',
  'Materializovaný pohled',
  'Pohled',
  'Tabulka',
  'Indexy',
  'Pozměnit indexy',
  'Zdroj',
  'Cíl',
  'Změnit',
  'Přidat cizí klíč',
  'Triggery',
  'Přidat trigger',
  'Trvalý odkaz',
  'Výstup',
  'Formát',
  'Procedury a funkce',
  'Události',
  'Data',
  'Vytvořit uživatele',
  'Dotazy ATTACH nejsou podporované.',
  'Chyba v dotazu',
  '%d / ',
  [
    '%d řádek',
    '%d řádky',
    '%d řádků',
  ],
  [
    'Příkaz proběhl v pořádku, byl změněn %d záznam.',
    'Příkaz proběhl v pořádku, byly změněny %d záznamy.',
    'Příkaz proběhl v pořádku, bylo změněno %d záznamů.',
  ],
  'Žádné příkazy k vykonání.',
  [
    '%d příkaz proběhl v pořádku.',
    '%d příkazy proběhly v pořádku.',
    '%d příkazů proběhlo v pořádku.',
  ],
  'Provést',
  'Limit řádek',
  'Nahrání souboru',
  'Nahrávání souborů není povoleno.',
  'Ze serveru',
  'Soubor %s na webovém serveru',
  'Spustit soubor',
  'Zastavit při chybě',
  'Zobrazit pouze chyby',
  'Historie',
  'Vyčistit',
  'Upravit vše',
  'Položka byla smazána.',
  'Položka byla aktualizována.',
  'Položka%s byla vložena.',
  'Tabulka byla odstraněna.',
  'Tabulka byla změněna.',
  'Tabulka byla vytvořena.',
  'Název tabulky',
  'úložiště',
  'Výchozí hodnoty',
  'Odstranit %s?',
  'Rozdělit podle',
  'Oddíly',
  'Název oddílu',
  'Hodnoty',
  'Indexy byly změněny.',
  'Typ indexu',
  'Sloupec (délka)',
  'Název',
  'Databáze byla odstraněna.',
  'Databáze byla přejmenována.',
  'Databáze byla vytvořena.',
  'Databáze byla změněna.',
  'Zavolat',
  [
    'Procedura byla zavolána, byl změněn %d záznam.',
    'Procedura byla zavolána, byly změněny %d záznamy.',
    'Procedura byla zavolána, bylo změněno %d záznamů.',
  ],
  'Cizí klíč byl odstraněn.',
  'Cizí klíč byl změněn.',
  'Cizí klíč byl vytvořen.',
  'Zdrojové a cílové sloupce musí mít stejný datový typ, nad cílovými sloupci musí být definován index a odkazovaná data musí existovat.',
  'Cizí klíč',
  'Cílová tabulka',
  'Schéma',
  'Změnit',
  'Přidat sloupec',
  'Pohled byl změněn.',
  'Pohled byl odstraněn.',
  'Pohled byl vytvořen.',
  'Vytvořit pohled',
  'Událost byla odstraněna.',
  'Událost byla změněna.',
  'Událost byla vytvořena.',
  'Pozměnit událost',
  'Vytvořit událost',
  'Začátek',
  'Konec',
  'Každých',
  'Po dokončení zachovat',
  'Procedura byla odstraněna.',
  'Procedura byla změněna.',
  'Procedura byla vytvořena.',
  'Změnit funkci',
  'Změnit proceduru',
  'Vytvořit funkci',
  'Vytvořit proceduru',
  'Návratový typ',
  'Trigger byl odstraněn.',
  'Trigger byl změněn.',
  'Trigger byl vytvořen.',
  'Změnit trigger',
  'Vytvořit trigger',
  'Čas',
  'Událost',
  'Uživatel byl odstraněn.',
  'Uživatel byl změněn.',
  'Uživatel byl vytvořen.',
  'Zahašované',
  'Procedura',
  'Povolit',
  'Zakázat',
  [
    'Byl ukončen %d proces.',
    'Byly ukončeny %d procesy.',
    'Bylo ukončeno %d procesů.',
  ],
  'Klonovat',
  '%d celkem',
  'Ukončit',
  [
    'Byl ovlivněn %d záznam.',
    'Byly ovlivněny %d záznamy.',
    'Bylo ovlivněno %d záznamů.',
  ],
  'Ctrl+klikněte na políčko, které chcete změnit.',
  'Soubor musí být v kódování UTF-8.',
  [
    'Byl importován %d záznam.',
    'Byly importovány %d záznamy.',
    'Bylo importováno %d záznamů.',
  ],
  'Nepodařilo se vypsat tabulku',
  'Změnit',
  'Vztahy',
  'upravit',
  'Ke změně této hodnoty použijte odkaz upravit.',
  'Nahrát další data',
  'Nahrává se',
  'Stránka',
  'poslední',
  'Celý výsledek',
  'Tabulky byly vyprázdněny.',
  'Tabulky byly přesunuty.',
  'Tabulky byly zkopírovány.',
  'Tabulky byly odstraněny.',
  'Tabulky byly optimalizovány.',
  'Tabulky a pohledy',
  'Vyhledat data v tabulkách',
  'Úložiště',
  'Velikost dat',
  'Velikost indexů',
  'Volné místo',
  'Řádků',
  'Vyčistit',
  'Optimalizovat',
  'Analyzovat',
  'Zkontrolovat',
  'Opravit',
  'Vyprázdnit',
  'Přesunout do jiné databáze',
  'Přesunout',
  'Zkopírovat',
  'přepsat',
  'Plán',
  'V daný čas',
  'Pozměnit typ',
];
		case "da": return [
  'Er du sikker?',
  '%.3f s',
  'Kunne ikke uploade fil.',
  'Maksimum tilladte filstørrelse er %sB.',
  'Filen eksisterer ikke.',
  ' ',
  '0123456789',
  'tom',
  'original',
  'Ingen tabeller.',
  'Rediger',
  'Indsæt',
  'Ingen rækker.',
  'Du mangler rettigheder til at ændre denne tabellen.',
  'Gem',
  'Gem og fortsæt redigering',
  'Gem og indsæt næste',
  'Gemmer',
  'Slet',
  'Sprog',
  'Brug',
  'Unknown error.',
  'System',
  'Server',
  'Brugernavn',
  'Kodeord',
  'Database',
  'Log ind',
  'Permanent login',
  'Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',
  'Vælg data',
  'Vis struktur',
  'Ændre view',
  'Ændre tabel',
  'Nyt emne',
  'Warnings',
  [
    '%d byte',
    '%d bytes',
  ],
  'Kolonne',
  'Type',
  'Kommentarer',
  'Auto Increment',
  'Default value',
  'Vælg',
  'Funktioner',
  'Sammenfatning',
  'Søg',
  'hvorsomhelst',
  'Sorter',
  'faldende',
  'Limit',
  'Tekstlængde',
  'Handling',
  'Fuld tabel-scan',
  'SQL-kommando',
  'Åben',
  'Gem',
  'Ændre database',
  'Ændre skema',
  'Opret skema',
  'Databaseskema',
  'Privilegier',
  'Importer',
  'Eksport',
  'Opret tabel',
  'database',
  'DB',
  'Vis',
  'Disable %s or enable %s or %s extensions.',
  'Strenge',
  'Nummer',
  'Dato og tid',
  'Lister',
  'Binær',
  'Geometri',
  'ltr',
  'You are offline.',
  'Log ud',
  'Too many unsuccessful logins, try again in %d minute(s).',
  'Log af vellykket.',
  'Thanks for using Adminer, consider <a href="https://www.adminer.org/en/donation/">donating</a>.',
  'Sessionen er udløbet - Log venligst ind igen.',
  'Master-kodeordet er udløbet. <a href="https://www.adminer.org/en/extension/"%s>Implementer</a> en metode for %s for at gøre det permanent.',
  'Session support skal være slået til.',
  'The action will be performed after successful login with the same credentials.',
  'Ingen udvidelse',
  'Ingen af de understøttede PHP-udvidelser (%s) er tilgængelige.',
  'Connecting to privileged ports is not allowed.',
  'Ugyldige log ind oplysninger.',
  'There is a space in the input password which might be the cause.',
  'Ugyldigt CSRF-token - Genindsend formen.',
  'Maksimum antal feltnavne overskredet - øg venligst %s.',
  'If you did not send this request from Adminer then close this page.',
  'Maks POST data er overskredet. Reducer mængden af data eller øg størrelsen i %s-konfigurationen.',
  'Du kan uploade en stor SQL-fil via FTP og importere den fra serveren.',
  'Fremmednøgler',
  'sortering',
  'ON UPDATE',
  'ON DELETE',
  'Kolonnenavn',
  'Parameternavn',
  'Længde',
  'Valg',
  'Læg til næste',
  'Flyt op',
  'Flyt ned',
  'Fjern',
  'Ugyldig database.',
  'Databasene er blevet slettet.',
  'Vælg database',
  'Opret database',
  'Procesliste',
  'Variabler',
  'Status',
  '%s version: %s via PHP-udvidelse %s',
  'Logget ind som: %s',
  'Genindlæs',
  'Tekstsortering',
  'Tabeller',
  'Size',
  'Compute',
  'Valgt',
  'Drop',
  'Materialized view',
  'View',
  'Tabel',
  'Indekser',
  'Ændre indekser',
  'Kilde',
  'Mål',
  'Ændre',
  'Tilføj fremmednøgle',
  'Triggere',
  'Tilføj trigger',
  'Permanent link',
  'Resultat',
  'Format',
  'Rutiner',
  'Hændelser',
  'Data',
  'Opret bruger',
  'ATTACH queries are not supported.',
  'Fejl i forespørgelse',
  '%d / ',
  [
    '%d række',
    '%d rækker',
  ],
  [
    'Kald udført OK, %d række påvirket.',
    'Kald udført OK, %d rækker påvirket.',
  ],
  'Ingen kommandoer at udføre.',
  [
    '%d kald udført OK.',
    '%d kald udført OK.',
  ],
  'Kør',
  'Limit rows',
  'Fil upload',
  'Fil upload er slået fra.',
  'Fra server',
  'Webserver-fil %s',
  'Kør fil',
  'Stop ved fejl',
  'Vis kun fejl',
  'Historik',
  'Tøm',
  'Rediger alle',
  'Emnet er slettet.',
  'Emnet er opdateret.',
  'Emne%s er sat ind.',
  'Tabellen er slettet.',
  'Tabellen er ændret.',
  'Tabellen er oprettet.',
  'Tabelnavn',
  'motor',
  'Standardværdier',
  'Drop %s?',
  'Partition ved',
  'Partitioner',
  'Partitionsnavn',
  'Værdier',
  'Indekserne er ændret.',
  'Indekstype',
  'Kolonne (længde)',
  'Navn',
  'Databasen er blevet slettet.',
  'Databasen har fået nyt navn.',
  'Databasen er oprettet.',
  'Databasen er ændret.',
  'Kald',
  [
    'Rutinen er udført, %d række påvirket.',
    'Rutinen er udført, %d rækker påvirket.',
  ],
  'Fremmednøglen er slettet.',
  'Fremmednøglen er ændret.',
  'Fremmednøglen er oprettet.',
  'Kilde- og målkolonner skal have samme datatype, der skal være en indeks på mål-kolonnen, og data som refereres til skal eksistere.',
  'Fremmednøgle',
  'Måltabel',
  'Skema',
  'Ændre',
  'Tilføj kolonne',
  'Viewet er ændret.',
  'Viewet er slettet.',
  'Viewet er oprettet.',
  'Nyt view',
  'Hændelsen er slettet.',
  'Hændelsen er ændret.',
  'Hændelsen er oprettet.',
  'Ændre hændelse',
  'Opret hændelse',
  'Start',
  'Slut',
  'Hver',
  'Ved fuldførelse bevar',
  'Rutinen er slettet.',
  'Rutinen er ændret.',
  'Rutinen er oprettet.',
  'Ændre funktion',
  'Ændre procedure',
  'Opret funktion',
  'Opret procedure',
  'Returtype',
  'Triggeren er slettet.',
  'Triggeren er ændret.',
  'Triggeren er oprettet.',
  'Ændre trigger',
  'Opret trigger',
  'Tid',
  'Hændelse',
  'Brugeren slettet.',
  'Brugeren ændret.',
  'Brugeren oprettet.',
  'Hashet',
  'Rutine',
  'Giv privilegier',
  'Træk tilbage',
  [
    '%d proces afsluttet.',
    '%d processer afsluttet.',
  ],
  'Klon',
  '%d total',
  'Afslut',
  [
    '%d emne påvirket.',
    '%d emner påvirket.',
  ],
  'Ctrl+klik på en værdi for at ændre den.',
  'Filen skal være i UTF8-tegnkoding.',
  [
    '%d række er importeret.',
    '%d rækker er importeret.',
  ],
  'Kan ikke vælge tabellen',
  'Ændre',
  'Relationer',
  'rediger',
  'Brug rediger-link for at ændre dennne værdi.',
  'Indlæs mere data',
  'Indlæser',
  'Side',
  'sidste',
  'Hele resultatet',
  'Tabellerne er blevet afkortet.',
  'Tabellerne er blevet flyttet.',
  'Tabellerne er blevet kopiert.',
  'Tabellerne er slettet.',
  'Tabellerne er blevet optimaliseret.',
  'Tabeller og views',
  'Søg data i tabeller',
  'Motor',
  'Datalængde',
  'Indekslængde',
  'Fri data',
  'Rader',
  'Støvsug',
  'Optimaliser',
  'Analyser',
  'Tjek',
  'Reparer',
  'Afkort',
  'Flyt til anden database',
  'Flyt',
  'Kopier',
  'overwrite',
  'Tidsplan',
  'På givne tid',
  'Ændre type',
];
		case "de": return [
  'Sind Sie sicher?',
  '%.3f s',
  'Hochladen von Datei fehlgeschlagen.',
  'Maximal erlaubte Dateigröße ist %sB.',
  'Datei existiert nicht.',
  ' ',
  '0123456789',
  'leer',
  'Original',
  'Keine Tabellen.',
  'Bearbeiten',
  'Einfügen',
  'Keine Datensätze.',
  'Sie haben keine Rechte, um diese Tabelle zu aktualisieren.',
  'Speichern',
  'Speichern und weiter bearbeiten',
  'Speichern und nächsten einfügen',
  'Speichere',
  'Entfernen',
  'Sprache',
  'Benutzung',
  'Unknown error.',
  'Datenbank System',
  'Server',
  'Benutzer',
  'Passwort',
  'Datenbank',
  'Login',
  'Passwort speichern',
  'Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',
  'Daten auswählen',
  'Struktur anzeigen',
  'View ändern',
  'Tabelle ändern',
  'Neuer Datensatz',
  'Warnings',
  [
    '%d Byte',
    '%d Bytes',
  ],
  'Spalte',
  'Typ',
  'Kommentar',
  'Auto-Inkrement',
  'Vorgabewert festlegen',
  'Daten zeigen von',
  'Funktionen',
  'Aggregationen',
  'Suchen',
  'beliebig',
  'Ordnen',
  'absteigend',
  'Begrenzung',
  'Textlänge',
  'Aktion',
  'Full table scan',
  'SQL-Kommando',
  'anzeigen',
  'Datei',
  'Datenbank ändern',
  'Schema ändern',
  'Schema erstellen',
  'Datenbankschema',
  'Rechte',
  'Importieren',
  'Exportieren',
  'Tabelle erstellen',
  'Datenbank',
  'DB',
  'zeigen',
  'Disable %s or enable %s or %s extensions.',
  'Zeichenketten',
  'Zahlen',
  'Datum und Zeit',
  'Listen',
  'Binär',
  'Geometrie',
  'ltr',
  'Sie sind offline.',
  'Abmelden',
  [
    'Zu viele erfolglose Login-Versuche. Bitte probieren Sie es in %d Minute noch einmal.',
    'Zu viele erfolglose Login-Versuche. Bitte probieren Sie es in %d Minuten noch einmal.',
  ],
  'Abmeldung erfolgreich.',
  'Danke, dass Sie Adminer genutzt haben. <a href="https://www.adminer.org/de/donation/">Spenden willkommen!</a>',
  'Sitzungsdauer abgelaufen, bitte erneut anmelden.',
  'Das Master-Passwort ist abgelaufen. <a href="https://www.adminer.org/de/extension/"%s>Implementieren</a> Sie die %s Methode, um es permanent zu machen.',
  'Unterstüzung für PHP-Sessions muss aktiviert sein.',
  'The action will be performed after successful login with the same credentials.',
  'Keine Erweiterungen installiert',
  'Keine der unterstützten PHP-Erweiterungen (%s) ist vorhanden.',
  'Connecting to privileged ports is not allowed.',
  'Ungültige Anmelde-Informationen.',
  'There is a space in the input password which might be the cause.',
  'CSRF Token ungültig. Bitte die Formulardaten erneut abschicken.',
  'Die maximal erlaubte Anzahl der Felder ist überschritten. Bitte %s erhöhen.',
  'Wenn Sie diese Anfrage nicht von Adminer gesendet haben, schließen Sie diese Seite.',
  'POST-Daten sind zu groß. Reduzieren Sie die Größe oder vergrößern Sie den Wert %s in der Konfiguration.',
  'Sie können eine große SQL-Datei per FTP hochladen und dann vom Server importieren.',
  'Fremdschlüssel',
  'Kollation',
  'ON UPDATE',
  'ON DELETE',
  'Spaltenname',
  'Name des Parameters',
  'Länge',
  'Optionen',
  'Hinzufügen',
  'Nach oben',
  'Nach unten',
  'Entfernen',
  'Datenbank ungültig.',
  'Datenbanken wurden entfernt.',
  'Datenbank auswählen',
  'Datenbank erstellen',
  'Prozessliste',
  'Variablen',
  'Status',
  'Version %s: %s mit PHP-Erweiterung %s',
  'Angemeldet als: %s',
  'Aktualisieren',
  'Kollation',
  'Tabellen',
  'Größe',
  'kalkulieren',
  'Ausgewählte',
  'Entfernen',
  'Materialized view',
  'View',
  'Tabelle',
  'Indizes',
  'Indizes ändern',
  'Ursprung',
  'Ziel',
  'Ändern',
  'Fremdschlüssel hinzufügen',
  'Trigger',
  'Trigger hinzufügen',
  'Dauerhafter Link',
  'Ergebnis',
  'Format',
  'Routinen',
  'Ereignisse',
  'Daten',
  'Benutzer erstellen',
  'ATTACH queries are not supported.',
  'Fehler in der SQL-Abfrage',
  '%d / ',
  [
    '%d Datensatz',
    '%d Datensätze',
  ],
  [
    'Abfrage ausgeführt, %d Datensatz betroffen.',
    'Abfrage ausgeführt, %d Datensätze betroffen.',
  ],
  'Kein Kommando vorhanden.',
  [
    'SQL-Abfrage erfolgreich ausgeführt.',
    '%d SQL-Abfragen erfolgreich ausgeführt.',
  ],
  'Ausführen',
  'Datensätze begrenzen',
  'Datei importieren',
  'Importieren von Dateien abgeschaltet.',
  'Vom Server',
  'Webserver Datei %s',
  'Datei ausführen',
  'Bei Fehler anhalten',
  'Nur Fehler anzeigen',
  'History',
  'Löschen',
  'Alle bearbeiten',
  'Datensatz wurde gelöscht.',
  'Datensatz wurde geändert.',
  'Datensatz%s wurde eingefügt.',
  'Tabelle wurde entfernt.',
  'Tabelle wurde geändert.',
  'Tabelle wurde erstellt.',
  'Name der Tabelle',
  'Speicher-Engine',
  'Vorgabewerte festlegen',
  'Drop %s?',
  'Partitionieren um',
  'Partitionen',
  'Name der Partition',
  'Werte',
  'Indizes geändert.',
  'Index-Typ',
  'Spalte (Länge)',
  'Name',
  'Datenbank wurde entfernt.',
  'Datenbank wurde umbenannt.',
  'Datenbank wurde erstellt.',
  'Datenbank wurde geändert.',
  'Aufrufen',
  [
    'Routine wurde ausgeführt, %d Datensatz betroffen.',
    'Routine wurde ausgeführt, %d Datensätze betroffen.',
  ],
  'Fremdschlüssel wurde entfernt.',
  'Fremdschlüssel wurde geändert.',
  'Fremdschlüssel wurde erstellt.',
  'Quell- und Zielspalten müssen vom gleichen Datentyp sein, es muss unter den Zielspalten ein Index existieren und die referenzierten Daten müssen existieren.',
  'Fremdschlüssel',
  'Zieltabelle',
  'Schema',
  'Ändern',
  'Spalte hinzufügen',
  'View wurde geändert.',
  'View wurde entfernt.',
  'View wurde erstellt.',
  'View erstellen',
  'Ereignis wurde entfernt.',
  'Ereignis wurde geändert.',
  'Ereignis wurde erstellt.',
  'Ereignis ändern',
  'Ereignis erstellen',
  'Start',
  'Ende',
  'Jede',
  'Nach der Ausführung erhalten',
  'Routine wurde entfernt.',
  'Routine wurde geändert.',
  'Routine wurde erstellt.',
  'Funktion ändern',
  'Prozedur ändern',
  'Funktion erstellen',
  'Prozedur erstellen',
  'Typ des Rückgabewertes',
  'Trigger wurde entfernt.',
  'Trigger wurde geändert.',
  'Trigger wurde erstellt.',
  'Trigger ändern',
  'Trigger erstellen',
  'Zeitpunkt',
  'Ereignis',
  'Benutzer wurde entfernt.',
  'Benutzer wurde geändert.',
  'Benutzer wurde erstellt.',
  'Hashed',
  'Routine',
  'Erlauben',
  'Widerrufen',
  [
    '%d Prozess gestoppt.',
    '%d Prozesse gestoppt.',
  ],
  'Klonen',
  '%d insgesamt',
  'Anhalten',
  '%d Artikel betroffen.',
  'Ctrl+Klick zum Bearbeiten des Wertes.',
  'Die Datei muss UTF-8 kodiert sein.',
  [
    '%d Datensatz wurde importiert.',
    '%d Datensätze wurden importiert.',
  ],
  'Auswahl der Tabelle fehlgeschlagen',
  'Ändern',
  'Relationen',
  'bearbeiten',
  'Benutzen Sie den Link zum Bearbeiten dieses Wertes.',
  'Mehr Daten laden',
  'Lade',
  'Seite',
  'letzte',
  'Gesamtergebnis',
  'Tabellen wurden geleert (truncate).',
  'Tabellen verschoben.',
  'Tabellen wurden kopiert.',
  'Tabellen wurden entfernt (drop).',
  'Tabellen wurden optimiert.',
  'Tabellen und Views',
  'Suche in Tabellen',
  'Speicher-Engine',
  'Datengröße',
  'Indexgröße',
  'Freier Bereich',
  'Datensätze',
  'Vacuum',
  'Optimieren',
  'Analysieren',
  'Prüfen',
  'Reparieren',
  'Leeren (truncate)',
  'In andere Datenbank verschieben',
  'Verschieben',
  'Kopieren',
  'overwrite',
  'Zeitplan',
  'Zur angegebenen Zeit',
  'nein',
];
		case "el": return [
  'Είστε σίγουρος;',
  '%.3f s',
  'Αδυναμία μεταφόρτωσης αρχείου.',
  'Το μέγιστο επιτρεπόμενο μέγεθος αρχείου είναι %sB.',
  'Το αρχείο δεν υπάρχει.',
  '.',
  '0123456789',
  'κενό',
  'πρωτότυπο',
  'Χωρίς πίνακες.',
  'Επεξεργασία',
  'Εισαγωγή',
  'Χωρίς σειρές.',
  'Δεν έχετε δικαίωμα να τροποποιήσετε αυτό τον πίνακα.',
  'Αποθήκευση',
  'Αποθήκευση και συνέχεια επεξεργασίας',
  'Αποθήκευση και εισαγωγή επόμενου',
  'Γίνεται Αποθήκευση',
  'Διαγραφή',
  'Γλώσσα',
  'χρήση',
  'Unknown error.',
  'Σύστημα',
  'Διακομιστής',
  'Όνομα Χρήστη',
  'Κωδικός',
  'Β. Δεδομένων',
  'Σύνδεση',
  'Μόνιμη Σύνδεση',
  'Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',
  'Επιλέξτε δεδομένα',
  'Προβολή δομής',
  'Τροποποίηση προβολής',
  'Τροποποίηση πίνακα',
  'Νέα εγγραφή',
  'Warnings',
  [
    '%d byte',
    '%d bytes',
  ],
  'Στήλη',
  'Τύπος',
  'Σχόλιο',
  'Αυτόματη αρίθμηση',
  'Προεπιλεγμένη τιμή',
  'Επιλογή',
  'Λειτουργίες',
  'Άθροισμα',
  'Αναζήτηση',
  'παντού',
  'Ταξινόμηση',
  'Φθίνουσα',
  'Όριο',
  'Μήκος κειμένου',
  'Ενέργεια',
  'Πλήρης σάρωση πινάκων',
  'Εντολή SQL',
  'άνοιγμα',
  'αποθήκευση',
  'Τροποποίηση Β.Δ.',
  'Τροποποίηση σχήματος',
  'Δημιουργία σχήματος',
  'Σχήμα Β.Δ.',
  'Δικαιώματα',
  'Εισαγωγή',
  'Εξαγωγή',
  'Δημιουργία πίνακα',
  'β. δεδομένων',
  'DB',
  'επιλογή',
  'Disable %s or enable %s or %s extensions.',
  'Κείμενο',
  'Αριθμοί',
  'Ημερομηνία και ώρα',
  'Λίστες',
  'Δυαδικό',
  'Γεωμετρία',
  'ltr',
  'Βρίσκεστε εκτός σύνδεσης.',
  'Αποσύνδεση',
  [
    'Επανειλημμένες ανεπιτυχείς προσπάθειες σύνδεσης, δοκιμάστε ξανά σε %s λεπτό.',
    'Επανειλημμένες ανεπιτυχείς προσπάθειες σύνδεσης, δοκιμάστε ξανά σε %s λεπτά.',
  ],
  'Αποσυνδεθήκατε με επιτυχία.',
  'Thanks for using Adminer, consider <a href="https://www.adminer.org/en/donation/">donating</a>.',
  'Η συνεδρία έληξε, παρακαλώ συνδεθείτε ξανά.',
  'Έληξε ο Κύριος Κωδικός. <a href="https://www.adminer.org/en/extension/"%s>Ενεργοποιήστε</a> τη μέθοδο %s για να τον κάνετε μόνιμο.',
  'Πρέπει να είναι ενεργοποιημένη η υποστήριξη συνεδριών.',
  'The action will be performed after successful login with the same credentials.',
  'Καμία Επέκταση',
  'Καμία από τις υποστηριζόμενες επεκτάσεις PHP (%s) δεν είναι διαθέσιμη.',
  'Connecting to privileged ports is not allowed.',
  'Εσφαλμένα Διαπιστευτήρια.',
  'There is a space in the input password which might be the cause.',
  'Άκυρο κουπόνι CSRF. Στείλτε τη φόρμα ξανά.',
  'Υπέρβαση μέγιστου επιτρεπόμενου αριθμού πεδίων. Παρακαλώ αυξήστε %s.',
  'Αν δε στείλατε αυτό το αίτημα από το Adminer, τότε κλείστε αυτή τη σελίδα.',
  'Πολλά δεδομένα POST. Μείωστε τα περιεχόμενα ή αυξήστε την σχετική ρύθμιση %s.',
  'Μπορείτε να μεταφορτώσετε ένα μεγάλο αρχείο SQL μέσω FTP και να το εισάγετε από το διακομιστή.',
  'Εξαρτημένα κλειδιά',
  'collation',
  'ΚΑΤΑ ΤΗΝ ΑΛΛΑΓΗ',
  'ΚΑΤΑ ΤΗ ΔΙΑΓΡΑΦΗ',
  'Όνομα στήλης',
  'Όνομα παραμέτρου',
  'Μήκος',
  'Επιλογές',
  'Προσθήκη επόμενου',
  'Μετακίνηση προς τα επάνω',
  'Μετακίνηση προς τα κάτω',
  'Αφαίρεση',
  'Λανθασμένη Β.Δ.',
  'Οι Β.Δ. διαγράφηκαν.',
  'Επιλέξτε Β.Δ.',
  'Δημιουργία Β.Δ.',
  'Λίστα διεργασιών',
  'Μεταβλητές',
  'Κατάσταση',
  '%s έκδοση: %s μέσω επέκτασης PHP %s',
  'Συνδεθήκατε ως %s',
  'Ανανέωση',
  'Collation',
  'Πίνακες',
  'Μέγεθος',
  'Υπολογισμός',
  'Επιλεγμένα',
  'Διαγραφή',
  'Υλοποιημένη προβολή',
  'Προβολή',
  'Πίνακας',
  'Δείκτες',
  'Τροποποίηση δεικτών',
  'Πηγή',
  'Στόχος',
  'Τροποποίηση',
  'Προσθήκη εξαρτημένου κλειδιού',
  'Εναύσματα',
  'Προσθήκη εναύσματος',
  'Μόνιμος Σύνδεσμος',
  'Αποτέλεσμα',
  'Μορφή',
  'Ρουτίνες',
  'Γεγονός',
  'Δεδομένα',
  'Δημιουργία Χρήστη',
  'ATTACH queries are not supported.',
  'Σφάλμα στο ερώτημα',
  '%d / ',
  [
    '%d σειρά',
    '%d σειρές',
  ],
  [
    'Το ερώτημα εκτελέστηκε ΟΚ, επηρεάστηκε %d σειρά.',
    'Το ερώτημα εκτελέστηκε ΟΚ, επηρεάστηκαν %d σειρές.',
  ],
  'Δεν υπάρχουν εντολές να εκτελεστούν.',
  [
    'Το ερώτημα %d εκτελέστηκε ΟΚ.',
    'Τα ερώτηματα %d εκτελέστηκαν ΟΚ.',
  ],
  'Εκτέλεση',
  'Περιορισμός σειρών',
  'Μεταφόρτωση αρχείου',
  'Έχει απενεργοποιηθεί η μεταφόρτωση αρχείων.',
  'Από διακομιστή',
  'Αρχείο %s από διακομιστή web',
  'Εκτέλεση αρχείου',
  'Διακοπή όταν υπάρχει σφάλμα',
  'Να εμφανίζονται μόνο τα σφάλματα',
  'Ιστορικό',
  'Καθαρισμός',
  'Επεξεργασία όλων',
  'Η εγγραφή διαγράφηκε.',
  'Η εγγραφή ενημερώθηκε.',
  'Η εγγραφή%s εισήχθη.',
  'Ο πίνακας διαγράφηκε.',
  'Ο πίνακας τροποποιήθηκε.',
  'Ο πίνακας δημιουργήθηκε.',
  'Όνομα πίνακα',
  'μηχανή',
  'Προεπιλεγμένες τιμές',
  'Drop %s?',
  'Τμηματοποίηση ανά',
  'Τμήματα',
  'Όνομα Τμήματος',
  'Τιμές',
  'Οι δείκτες τροποποιήθηκαν.',
  'Τύπος δείκτη',
  'Στήλη (μήκος)',
  'Όνομα',
  'Η Β.Δ. διαγράφηκε.',
  'Η. Β.Δ. μετονομάστηκε.',
  'Η Β.Δ. δημιουργήθηκε.',
  'Η Β.Δ. τροποποιήθηκε.',
  'Εκτέλεση',
  [
    'Η ρουτίνα εκτελέστηκε, επηρεάστηκε %d σειρά.',
    'Η ρουτίνα εκτελέστηκε, επηρεάστηκαν %d σειρές.',
  ],
  'Το εξαρτημένο κλειδί διαγράφηκε.',
  'Το εξαρτημένο κλειδί τροποποιήθηκε.',
  'Το εξαρτημένο κλειδί δημιουργήθηκε.',
  'Οι στήλες στην πηγή και το στόχο πρέπει να έχουν τον ίδιο τύπο, πρέπει να υπάρχει δείκτης στη στήλη στόχο και να υπάρχουν εξαρτημένα δεδομένα.',
  'Εξαρτημένο κλειδί',
  'Πίνακας Στόχος',
  'Σχήμα',
  'Αλλαγή',
  'Προσθήκη στήλης',
  'Η προβολή τροποποιήθηκε.',
  'Η προβολή διαγράφηκε.',
  'Η προβολή δημιουργήθηκε.',
  'Δημιουργία προβολής',
  'Το γεγονός διαγράφηκε.',
  'Το γεγονός τροποποιήθηκε.',
  'Το γεγονός δημιουργήθηκε.',
  'Τροποποίηση γεγονότος',
  'Δημιουργία γεγονότος',
  'Έναρξη',
  'Λήξη',
  'Κάθε',
  'Κατά την ολοκλήρωση διατήρησε',
  'Η ρουτίνα διαγράφηκε.',
  'Η ρουτίνα τροποποιήθηκε.',
  'Η ρουτίνα δημιουργήθηκε.',
  'Τροποποίηση λειτουργίας',
  'Τροποποίηση διαδικασίας',
  'Δημιουργία Συνάρτησης',
  'Δημιουργία διαδικασίας',
  'Επιστρεφόμενος τύπος',
  'Το έναυσμα διαγράφηκε.',
  'Το έναυσμα τροποποιήθηκε.',
  'Το έναυσμα δημιουργήθηκε.',
  'Τροποποίηση εναύσματος',
  'Δημιουργία εναύσματος',
  'Ώρα',
  'Γεγονός',
  'Ο Χρήστης διαγράφηκε.',
  'Ο Χρήστης τροποποιήθηκε.',
  'Ο Χρήστης δημιουργήθηκε.',
  'Κωδικοποιήθηκε',
  'Ρουτίνα',
  'Παραχώρηση',
  'Ανάκληση',
  [
    'Τερματίστηκε %d διεργασία.',
    'Τερματίστηκαν %d διεργασίες.',
  ],
  'Κλωνοποίηση',
  '%d συνολικά',
  'Τερματισμός',
  [
    'Επηρεάστηκε %d εγγραφή.',
    'Επηρεάστηκαν %d εγγραφές.',
  ],
  'Πιέστε Ctrl+click σε μια τιμή για να την τροποποιήσετε.',
  'Το αρχείο πρέπει να έχει κωδικοποίηση UTF-8.',
  [
    '$d σειρά εισήχθη.',
    '%d σειρές εισήχθησαν.',
  ],
  'Δεν είναι δυνατή η επιλογή πίνακα',
  'Τροποποίηση',
  'Συσχετήσεις',
  'επεξεργασία',
  'Χρησιμοποιήστε το σύνδεσμο επεξεργασία για να τροποποιήσετε την τιμή.',
  'Φόρτωση κι άλλων δεδομένων',
  'Φορτώνει',
  'Σελίδα',
  'τελευταία',
  'Όλο το αποτέλεσμα',
  'Οι πίνακες περικόπηκαν.',
  'Οι πίνακες μεταφέρθηκαν.',
  'Οι πίνακες αντιγράφηκαν.',
  'Οι πίνακες διαγράφηκαν.',
  'Οι πίνακες βελτιστοποιήθηκαν.',
  'Πίνακες και Προβολές',
  'Αναζήτηση δεδομένων στους πίνακες',
  'Μηχανή',
  'Μήκος Δεδομένων',
  'Μήκος Δείκτη',
  'Δεδομένα Ελεύθερα',
  'Σειρές',
  'Καθαρισμός',
  'Βελτιστοποίηση',
  'Ανάλυση',
  'Έλεγχος',
  'Επιδιόρθωση',
  'Περικοπή',
  'Μεταφορά σε άλλη Β.Δ.',
  'Μεταφορά',
  'Αντιγραφή',
  'overwrite',
  'Προγραμματισμός',
  'Σε προκαθορισμένο χρόνο',
  'Τροποποίηση τύπου',
];
		case "es": return [
  '¿Está seguro?',
  '%.3f s',
  'No es posible importar el archivo.',
  'El tamaño máximo de archivo es %sB.',
  'Ese archivo no existe.',
  ' ',
  '0123456789',
  'ninguno',
  'original',
  'No existen tablas.',
  'Modificar',
  'Agregar',
  'No existen registros.',
  'You have no privileges to update this table.',
  'Guardar',
  'Guardar y continuar editando',
  'Guardar e insertar siguiente',
  'Saving',
  'Eliminar',
  'Idioma',
  'Usar',
  'Unknown error.',
  'Motor de base de datos',
  'Servidor',
  'Usuario',
  'Contraseña',
  'Base de datos',
  'Login',
  'Guardar contraseña',
  'Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',
  'Visualizar contenido',
  'Mostrar estructura',
  'Modificar vista',
  'Modificar tabla',
  'Nuevo Registro',
  'Warnings',
  [
    '%d byte',
    '%d bytes',
  ],
  'Columna',
  'Tipo',
  'Comentario',
  'Incremento automático',
  'Default value',
  'Mostrar',
  'Funciones',
  'Agregados',
  'Condición',
  'donde sea',
  'Ordenar',
  'descendiente',
  'Limite',
  'Longitud de texto',
  'Acción',
  'Full table scan',
  'Comando SQL',
  'mostrar',
  'archivo',
  'Modificar Base de datos',
  'Modificar esquema',
  'Crear esquema',
  'Esquema de base de datos',
  'Privilegios',
  'Importar',
  'Exportar',
  'Crear tabla',
  'base de datos',
  'DB',
  'registros',
  'Disable %s or enable %s or %s extensions.',
  'Cadena',
  'Números',
  'Fecha y hora',
  'Listas',
  'Binario',
  'Geometría',
  'ltr',
  'You are offline.',
  'Cerrar sesión',
  'Too many unsuccessful logins, try again in %d minute(s).',
  'Sesión finalizada con éxito.',
  'Thanks for using Adminer, consider <a href="https://www.adminer.org/en/donation/">donating</a>.',
  'Sesión caducada, por favor escriba su clave de nuevo.',
  'Master password expired. <a href="https://www.adminer.org/en/extension/"%s>Implement</a> %s method to make it permanent.',
  'Deben estar habilitadas las sesiones.',
  'The action will be performed after successful login with the same credentials.',
  'No hay extensión',
  'Ninguna de las extensiones PHP soportadas (%s) está disponible.',
  'Connecting to privileged ports is not allowed.',
  'Usuario y/o clave de acceso incorrecta.',
  'There is a space in the input password which might be the cause.',
  'Token CSRF inválido. Vuelva a enviar los datos del formulario.',
  'Excedida la cantidad máxima de campos permitidos. Por favor aumente %s.',
  'If you did not send this request from Adminer then close this page.',
  'POST data demasiado grande. Reduzca el tamaño o aumente la directiva de configuración %s.',
  'You can upload a big SQL file via FTP and import it from server.',
  'Claves externas',
  'colación',
  'AL ACTUALIZAR',
  'AL BORRAR',
  'Nombre de columna',
  'Nombre de Parámetro',
  'Longitud',
  'Opciones',
  'Agregar',
  'Mover arriba',
  'Mover abajo',
  'Eliminar',
  'Base de datos incorrecta.',
  'Bases de datos eliminadas.',
  'Seleccionar Base de datos',
  'Crear Base de datos',
  'Lista de procesos',
  'Variables',
  'Estado',
  'Versión %s: %s a través de la extensión de PHP %s',
  'Logueado como: %s',
  'Refrescar',
  'Colación',
  'Tablas',
  'Size',
  'Compute',
  'Selected',
  'Eliminar',
  'Materialized view',
  'Vista',
  'Tabla',
  'Índices',
  'Modificar índices',
  'Origen',
  'Destino',
  'Modificar',
  'Agregar clave externa',
  'Disparadores',
  'Agregar disparador',
  'Enlace permanente',
  'Salida',
  'Formato',
  'Procedimientos',
  'Eventos',
  'Datos',
  'Crear Usuario',
  'ATTACH queries are not supported.',
  'Error al ejecutar consulta',
  '%d / ',
  [
    '%d registro',
    '%d registros',
  ],
  [
    'Consulta ejecutada, %d registro afectado.',
    'Consulta ejecutada, %d registros afectados.',
  ],
  'No es posible ejecutar ningún comando.',
  [
    '%d sentencia SQL ejecutada correctamente.',
    '%d sentencias SQL ejecutadas correctamente.',
  ],
  'Ejecutar',
  'Limit rows',
  'Importar archivo',
  'Importación de archivos deshablilitada.',
  'Desde servidor',
  'Archivo de servidor web %s',
  'Ejecutar Archivo',
  'Parar en caso de error',
  'Mostrar solamente errores',
  'Histórico',
  'Vaciar',
  'Editar todos',
  'Registro eliminado.',
  'Registro modificado.',
  'Registro%s insertado.',
  'Tabla eliminada.',
  'Tabla modificada.',
  'Tabla creada.',
  'Nombre de la tabla',
  'motor',
  'Valores predeterminados',
  'Drop %s?',
  'Particionar por',
  'Particiones',
  'Nombre de partición',
  'Valores',
  'Índices actualizados.',
  'Tipo de índice',
  'Columna (longitud)',
  'Nombre',
  'Base de datos eliminada.',
  'Base de datos renombrada.',
  'Base de datos creada.',
  'Base de datos modificada.',
  'Llamar',
  [
    'Consulta ejecutada, %d registro afectado.',
    'Consulta ejecutada, %d registros afectados.',
  ],
  'Clave externa eliminada.',
  'Clave externa modificada.',
  'Clave externa creada.',
  'Las columnas de origen y destino deben ser del mismo tipo, debe existir un índice entre las columnas del destino y el registro referenciado debe existir también.',
  'Clave externa',
  'Tabla de destino',
  'Esquema',
  'Modificar',
  'Agregar columna',
  'Vista modificada.',
  'Vista eliminada.',
  'Vista creada.',
  'Crear vista',
  'Evento eliminado.',
  'Evento modificado.',
  'Evento creado.',
  'Modificar Evento',
  'Crear Evento',
  'Inicio',
  'Fin',
  'Cada',
  'Al completar mantener',
  'Procedimiento eliminado.',
  'Procedimiento modificado.',
  'Procedimiento creado.',
  'Modificar función',
  'Modificar procedimiento',
  'Crear función',
  'Crear procedimiento',
  'Tipo de valor de vuelta',
  'Disparador eliminado.',
  'Disparador modificado.',
  'Disparador creado.',
  'Modificar Disparador',
  'Agregar Disparador',
  'Tiempo',
  'Evento',
  'Usuario eliminado.',
  'Usuario modificado.',
  'Usuario creado.',
  'Hash',
  'Rutina',
  'Conceder',
  'Impedir',
  [
    '%d proceso detenido.',
    '%d procesos detenidos.',
  ],
  'Clonar',
  '%d en total',
  'Detener',
  [
    '%d elemento afectado.',
    '%d elementos afectados.',
  ],
  'Ctrl+clic sobre el valor para editarlo.',
  'File must be in UTF-8 encoding.',
  [
    '%d registro importado.',
    '%d registros importados.',
  ],
  'No es posible seleccionar la tabla',
  'Modify',
  'Relaciones',
  'modificar',
  'Utilice el enlace de edición para realizar cambios.',
  'Load more data',
  'Loading',
  'Página',
  'último',
  'Resultado completo',
  'Las tablas han sido vaciadas.',
  'Se movieron las tablas.',
  'Tablas copiadas.',
  'Tablas eliminadas.',
  'Tables have been optimized.',
  'Tablas y vistas',
  'Buscar datos en tablas',
  'Motor',
  'Longitud de datos',
  'Longitud de índice',
  'Espacio libre',
  'Registros',
  'Vacuum',
  'Optimizar',
  'Analizar',
  'Comprobar',
  'Reparar',
  'Vaciar',
  'Mover a otra base de datos',
  'Mover',
  'Copiar',
  'overwrite',
  'Agenda',
  'En el momento indicado',
  'HH:MM:SS',
];
		case "et": return [
  'Kas oled kindel?',
  '%.3f s',
  'Faili üleslaadimine pole võimalik.',
  'Maksimaalne failisuurus %sB.',
  'Faili ei leitud.',
  ',',
  '0123456789',
  'tühi',
  'originaal',
  'Tabeleid ei leitud.',
  'Muuda',
  'Sisesta',
  'Sissekanded puuduvad.',
  'You have no privileges to update this table.',
  'Salvesta',
  'Salvesta ja jätka muutmist',
  'Salvesta ja lisa järgmine',
  'Saving',
  'Kustuta',
  'Keel',
  'Kasuta',
  'Unknown error.',
  'Andmebaasimootor',
  'Server',
  'Kasutajanimi',
  'Parool',
  'Andmebaas',
  'Logi sisse',
  'Jäta mind meelde',
  'Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',
  'Vaata andmeid',
  'Näita struktuuri',
  'Muuda vaadet (VIEW)',
  'Muuda tabeli struktuuri',
  'Lisa kirje',
  'Warnings',
  [
    '%d bait',
    '%d baiti',
  ],
  'Veerg',
  'Tüüp',
  'Kommentaar',
  'Automaatselt suurenev',
  'Default value',
  'Kuva',
  'Funktsioonid',
  'Liitmine',
  'Otsi',
  'vahet pole',
  'Sorteeri',
  'kahanevalt',
  'Piira',
  'Teksti pikkus',
  'Tegevus',
  'Full table scan',
  'SQL-Päring',
  'näita brauseris',
  'salvesta failina',
  'Muuda andmebaasi',
  'Muuda struktuuri',
  'Loo struktuur',
  'Andmebaasi skeem',
  'Õigused',
  'Impordi',
  'Ekspordi',
  'Loo uus tabel',
  'andmebaas',
  'DB',
  'kuva',
  'Disable %s or enable %s or %s extensions.',
  'Tekstid',
  'Numbrilised',
  'Kuupäev ja kellaaeg',
  'Listid',
  'Binaar',
  'Geomeetria',
  'ltr',
  'You are offline.',
  'Logi välja',
  'Too many unsuccessful logins, try again in %d minute(s).',
  'Väljalogimine õnnestus.',
  'Thanks for using Adminer, consider <a href="https://www.adminer.org/en/donation/">donating</a>.',
  'Sessioon on aegunud, palun logige uuesti sisse.',
  'Master password expired. <a href="https://www.adminer.org/en/extension/"%s>Implement</a> %s method to make it permanent.',
  'Sessioonid peavad olema lubatud.',
  'The action will be performed after successful login with the same credentials.',
  'Ei leitud laiendust',
  'Serveris pole ühtegi toetatud PHP laiendustest (%s).',
  'Connecting to privileged ports is not allowed.',
  'Ebakorrektsed andmed.',
  'There is a space in the input password which might be the cause.',
  'Sobimatu CSRF, palun postitage vorm uuesti.',
  'Maksimaalne väljade arv ületatud. Palun suurendage %s.',
  'If you did not send this request from Adminer then close this page.',
  'POST-andmete maht on liialt suur. Palun vähendage andmeid või suurendage %s php-seadet.',
  'You can upload a big SQL file via FTP and import it from server.',
  'Võõrvõtmed (foreign key)',
  'tähetabel',
  'ON UPDATE',
  'ON DELETE',
  'Veeru nimi',
  'Parameetri nimi',
  'Pikkus',
  'Valikud',
  'Lisa järgmine',
  'Liiguta ülespoole',
  'Liiguta allapoole',
  'Eemalda',
  'Tundmatu andmebaas.',
  'Andmebaasid on edukalt kustutatud.',
  'Vali andmebaas',
  'Loo uus andmebaas',
  'Protsesside nimekiri',
  'Muutujad',
  'Staatus',
  '%s versioon: %s, kasutatud PHP moodul: %s',
  'Sisse logitud: %s',
  'Uuenda',
  'Tähetabel',
  'Tabelid',
  'Size',
  'Compute',
  'Selected',
  'Kustuta',
  'Materialized view',
  'Vaata',
  'Tabel',
  'Indeksid',
  'Muuda indekseid',
  'Allikas',
  'Sihtkoht',
  'Muuda',
  'Lisa võõrvõti',
  'Päästikud (trigger)',
  'Lisa päästik (TRIGGER)',
  'Püsilink',
  'Väljund',
  'Formaat',
  'Protseduurid',
  'Sündmused (EVENTS)',
  'Andmed',
  'Loo uus kasutaja',
  'ATTACH queries are not supported.',
  'Päringus esines viga',
  '%d / ',
  '%d rida',
  'Päring õnnestus, mõjutatatud ridu: %d.',
  'Käsk puudub.',
  [
    '%d päring edukalt käivitatud.',
    '%d päringut edukalt käivitatud.',
  ],
  'Käivita',
  'Limit rows',
  'Faili üleslaadimine',
  'Failide üleslaadimine on keelatud.',
  'Serverist',
  'Fail serveris: %s',
  'Käivita fail',
  'Peatuda vea esinemisel',
  'Kuva vaid veateateid',
  'Ajalugu',
  'Puhasta',
  'Muuda kõiki',
  'Kustutamine õnnestus.',
  'Uuendamine õnnestus.',
  'Kirje%s on edukalt lisatud.',
  'Tabel on edukalt kustutatud.',
  'Tabeli andmed on edukalt muudetud.',
  'Tabel on edukalt loodud.',
  'Tabeli nimi',
  'andmebaasimootor',
  'Vaikimisi väärtused',
  'Drop %s?',
  'Partitsiooni',
  'Partitsioonid',
  'Partitsiooni nimi',
  'Väärtused',
  'Indeksite andmed on edukalt uuendatud.',
  'Indeksi tüüp',
  'Veerg (pikkus)',
  'Nimi',
  'Andmebaas on edukalt kustutatud.',
  'Andmebaas on edukalt ümber nimetatud.',
  'Andmebaas on edukalt loodud.',
  'Andmebaasi struktuuri uuendamine õnnestus.',
  'Käivita',
  'Protseduur täideti edukalt, mõjutatud ridu: %d.',
  'Võõrvõti on edukalt kustutatud.',
  'Võõrvõtme andmed on edukalt muudetud.',
  'Võõrvõri on edukalt loodud.',
  'Lähte- ja sihtveerud peavad eksisteerima ja omama sama andmetüüpi, sihtveergudel peab olema määratud indeks ning viidatud andmed peavad eksisteerima.',
  'Võõrvõti',
  'Siht-tabel',
  'Struktuur',
  'Muuda',
  'Lisa veerg',
  'Vaade (VIEW) on edukalt muudetud.',
  'Vaade (VIEW) on edukalt kustutatud.',
  'Vaade (VIEW) on edukalt loodud.',
  'Loo uus vaade (VIEW)',
  'Sündmus on edukalt kustutatud.',
  'Sündmuse andmed on edukalt uuendatud.',
  'Sündmus on edukalt loodud.',
  'Muuda sündmuse andmeid',
  'Loo uus sündmus (EVENT)',
  'Alusta',
  'Lõpeta',
  'Iga',
  'Lõpetamisel jäta sündmus alles',
  'Protseduur on edukalt kustutatud.',
  'Protseduuri andmed on edukalt muudetud.',
  'Protseduur on edukalt loodud.',
  'Muuda funktsiooni',
  'Muuda protseduuri',
  'Loo uus funktsioon',
  'Loo uus protseduur',
  'Tagastustüüp',
  'Päästik on edukalt kustutatud.',
  'Päästiku andmed on edukalt uuendatud.',
  'Uus päästik on edukalt loodud.',
  'Muuda päästiku andmeid',
  'Loo uus päästik (TRIGGER)',
  'Aeg',
  'Sündmus',
  'Kasutaja on edukalt kustutatud.',
  'Kasutaja andmed on edukalt muudetud.',
  'Kasutaja on edukalt lisatud.',
  'Häshitud (Hashed)',
  'Protseduur',
  'Anna',
  'Eemalda',
  [
    'Protsess on edukalt peatatud (%d).',
    'Valitud protsessid (%d) on edukalt peatatud.',
  ],
  'Kloon',
  'Kokku: %d',
  'Peata',
  'Mõjutatud kirjeid: %d.',
  'Väärtuse muutmiseks Ctrl+kliki sellel.',
  'File must be in UTF-8 encoding.',
  'Imporditi %d rida.',
  'Tabeli valimine ebaõnnestus',
  'Modify',
  'Seosed',
  'muuda',
  'Väärtuse muutmiseks kasuta muutmislinki.',
  'Load more data',
  'Loading',
  'Lehekülg',
  'viimane',
  'Täielikud tulemused',
  'Validud tabelid on edukalt tühjendatud.',
  'Valitud tabelid on edukalt liigutatud.',
  'Tabelid on edukalt kopeeritud.',
  'Valitud tabelid on edukalt kustutatud.',
  'Tables have been optimized.',
  'Tabelid ja vaated',
  'Otsi kogu andmebaasist',
  'Implementatsioon',
  'Andmete pikkus',
  'Indeksi pikkus',
  'Vaba ruumi',
  'Ridu',
  'Vacuum',
  'Optimeeri',
  'Analüüsi',
  'Kontrolli',
  'Paranda',
  'Tühjenda',
  'Liiguta teise andmebaasi',
  'Liiguta',
  'Kopeeri',
  'overwrite',
  'Ajakava',
  'Antud ajahetkel',
  'HH:MM:SS',
];
		case "fa": return [
  'مطمئن هستید؟',
  '%.3f s',
  'قادر به بارگذاری فایل نیستید.',
  ' %sB حداکثر اندازه فایل.',
  'فایل وجود ندارد.',
  ' ',
  '۰۱۲۳۴۵۶۷۸۹',
  'خالی',
  'اصلی',
  'جدولی وجود ندارد',
  'ویرایش',
  'درج',
  'سطری وجود ندارد',
  'شما اختیار ویرایش این جدول را ندارید.',
  'ذخیره',
  'ذخیره و ادامه ویرایش',
  'ذخیره و درج بعدی',
  'Saving',
  'حذف',
  'زبان',
  'استفاده',
  'Unknown error.',
  'سیستم',
  'سرور',
  'نام کاربری',
  'کلمه عبور',
  'پایگاه داده',
  'ورود',
  'ورود دائم',
  'Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',
  'انتخاب داده',
  'نمایش ساختار',
  'حذف نمایش',
  'ویرایش جدول',
  'آیتم جدید',
  'Warnings',
  [
    '%d بایت',
    '%d بایت',
  ],
  'ستون',
  'نوع',
  'توضیح',
  'افزایش خودکار',
  'مقدار پیش فرض',
  'انتخاب',
  'توابع',
  'تجمع',
  'جستجو',
  'هرکجا',
  'مرتب کردن',
  'نزولی',
  'محدودیت',
  'طول متن',
  'عملیات',
  'اسکن کامل جدول',
  'دستور SQL',
  'بازکردن',
  'ذخیره',
  'ویرایش پایگاه داده',
  'ویرایش ساختار',
  'ایجاد ساختار',
  'ساختار پایگاه داده',
  'امتیازات',
  'وارد کردن',
  'استخراج',
  'ایجاد جدول',
  'پایگاه داده',
  'DB',
  'انتخاب',
  'Disable %s or enable %s or %s extensions.',
  'رشته ها',
  'اعداد',
  'تاریخ و زمان',
  'لیستها',
  'دودویی',
  'هندسه',
  'rtl',
  'شما آفلاین می باشید.',
  'خروج',
  [
    'ورودهای ناموفق بیش از حد، %d دقیقه دیگر تلاش نمایید.',
    'ورودهای ناموفق بیش از حد، %d دقیقه دیگر تلاش نمایید.',
  ],
  'با موفقیت خارج شدید.',
  'Thanks for using Adminer, consider <a href="https://www.adminer.org/en/donation/">donating</a>.',
  'نشست پایان یافته، لطفا دوباره وارد شوید.',
  'رمز اصلی باطل شده است. روش %s را <a href="https://www.adminer.org/en/extension/"%s>پیاده سازی</a> کرده تا آن را دائمی سازید.',
  'پشتیبانی از نشست بایستی فعال گردد.',
  'The action will be performed after successful login with the same credentials.',
  'پسوند نامعتبر',
  'هیچ کدام از افزونه های PHP پشتیبانی شده (%s) موجود نمی باشند.',
  'Connecting to privileged ports is not allowed.',
  'اعتبار سنجی نامعتبر.',
  'There is a space in the input password which might be the cause.',
  'CSRF token نامعتبر است. دوباره سعی کنید.',
  'حداکثر تعداد فیلدهای مجاز اشباع شد. لطفا %s را افزایش دهید.',
  'If you did not send this request from Adminer then close this page.',
  'حجم داده ارسالی برزگ است. حجم داده کاهش دهید و یا مقدار %s را در پیکربندی افزایش دهید.',
  'شما می توانید فایل SQL حجیم را از طریق FTP بارگزاری و از روی سرور وارد نمایید.',
  'کلیدهای خارجی',
  'تطبیق',
  'ON UPDATE',
  'ON DELETE',
  'نام ستون',
  'نام پارامتر',
  'طول',
  'اختیارات',
  'افرودن بعدی',
  'انتقال به بالا',
  'انتقال به پایین',
  'حذف',
  'پایگاه داده نامعتبر.',
  'پایگاه های داده حذف شدند.',
  'انتخاب پایگاه داده',
  'ایجاد پایگاه داده',
  'لیست فرآیند',
  'متغیرها',
  'وضعیت',
  'نسخه %s : %s توسعه پی اچ پی %s',
  'ورود به عنوان: %s',
  'بازیابی',
  'تطبیق',
  'جدولها',
  'حجم',
  'محاسبه',
  'انتخاب شده',
  'حذف',
  'نمایه مادی',
  'نمایش',
  'جدول',
  'ایندکسها',
  'ویرایش ایندکسها',
  'منبع',
  'هدف',
  'ویرایش',
  'افزودن کلید خارجی',
  'تریگرها',
  'افزودن تریگر',
  'ارتباط دائم',
  'خروجی',
  'حذف',
  'روالها',
  'رویدادها',
  'داده',
  'ایجاد کاربر',
  'ATTACH queries are not supported.',
  'خطا در کوئری',
  '%d / ',
  [
    '%d سطر',
    '%d سطر',
  ],
  'کوئری اجرا شد. %d سطر تغیر کرد.',
  'دستوری برای اجرا وجود ندارد.',
  '%d کوئری اجرا شد.',
  'اجرا',
  'محدودیت سطرها',
  'بارگذاری فایل',
  'بارگذاری غیر فعال است.',
  'از سرور',
  '%s فایل وب سرور',
  'اجرای فایل',
  'توقف بر روی خطا',
  'فقط نمایش خطاها',
  'تاریخ',
  'پاک کردن',
  'ویرایش همه',
  'آیتم حذف شد.',
  'آیتم بروز رسانی شد.',
  '%s آیتم درج شد.',
  'جدول حذف شد.',
  'جدول ویرایش شد.',
  'جدول ایجاد شد.',
  'نام جدول',
  'موتور',
  'مقادیر پیش فرض',
  'Drop %s?',
  'بخشبندی توسط',
  'بخشبندیها',
  'نام بخش',
  'مقادیر',
  'ایندکسها ویرایش شدند.',
  'نوع ایندکس',
  'ستون (طول)',
  'نام',
  'پایگاه داده حذف شد.',
  'نام پایگاه داده تغیر کرد.',
  'پایگاه داده ایجاد شد.',
  'پایگاه داده ویرایش شد.',
  'صدا زدن',
  [
    'روال فراخوانی شد %d سطر متاثر شد.',
    'روال فراخوانی شد %d سطر متاثر شد.',
  ],
  'کلید خارجی حذف شد.',
  'کلید خارجی ویرایش شد.',
  'کلید خارجی ایجاد شد.',
  'داده مبدا و مقصد ستونها بایستی شبیه هم باشند.',
  'کلید خارجی',
  'جدول هدف',
  'ساختار',
  'تغییر',
  'افزودن ستون',
  'نمایش ویرایش شد.',
  'نمایش حذف شد.',
  'نمایش ایجاد شد.',
  'ایجاد نمایش',
  'رویداد حذف شد.',
  'رویداد ویرایش شد.',
  'رویداد ایجاد شد.',
  'ویرایش رویداد',
  'ایجاد رویداد',
  'آغاز',
  'پایان',
  'همه',
  'تکمیل حفاظت فعال است',
  'روال حذف شد.',
  'روال ویرایش شد.',
  'روال ایجاد شد.',
  'ویرایش تابع',
  'ویرایش زیربرنامه',
  'ایجاد تابع',
  'ایجاد زیربرنامه',
  'برگرداندن نوع',
  'تریگر حذف شد.',
  'تریگر ویرایش شد.',
  'تریگر ایجاد شد.',
  'ویرایش تریگر',
  'ایجاد تریگر',
  'زمان',
  'رویداد',
  'کاربر حذف شد.',
  'کاربر ویرایش گردید.',
  'کاربر ایجاد شد.',
  'به هم ریخته',
  'روتین',
  'اعطا',
  'لغو کردن',
  '%d فرآیند متوقف شد.',
  'تکثیر',
  ' به طور کل %d ',
  'حذف فرآیند',
  [
    '%d آیتم متاثر شد.',
    '%d آیتم متاثر شد.',
  ],
  'برای ویرایش بر روی مقدار ctrl+click کنید.',
  'فرمت فایل باید UTF-8 باشید.',
  [
    '%d سطر وارد شد.',
    '%d سطر وارد شد.',
  ],
  'قادر به انتخاب جدول نیستید',
  'ویرایش',
  'رابطه ها',
  'ویرایش',
  'از لینک ویرایش برای ویرایش این مقدار استفاده کنید.',
  'بارگزاری اطلاعات بیشتر',
  'در حال بارگزاری',
  'صفحه',
  'آخری',
  'همه نتایج',
  'جدولها بریده شدند.',
  'جدولها انتقال داده شدند.',
  'جدولها کپی شدند.',
  'جدولها حذف شدند.',
  'جدولها بهینه شدند.',
  'جدولها و نمایه ها',
  'جستجوی داده در جدول',
  'موتور',
  'طول داده',
  'طول ایندکس',
  'داده اختیاری',
  'سطرها',
  'پاک سازی',
  'بهینه سازی',
  'تحلیل',
  'بررسی',
  'تعمیر',
  'کوتاه کردن',
  'انتقال به یک پایگاه داده دیگر',
  'انتقال',
  'کپی کردن',
  'overwrite',
  'زمانبندی',
  'زمان معین',
  'ویرایش نوع',
];
		case "fi": return [
  'Oletko varma?',
  '%.3f s',
  'Tiedostoa ei voida ladata palvelimelle.',
  'Suurin sallittu tiedostokoko on %sB.',
  'Tiedostoa ei ole.',
  ',',
  '0123456789',
  'tyhjä',
  'alkuperäinen',
  'Ei tauluja.',
  'Muokkaa',
  'Lisää',
  'Ei rivejä.',
  'Sinulla ei ole oikeutta päivittää tätä taulua.',
  'Tallenna',
  'Tallenna ja jatka muokkaamista',
  'Tallenna ja lisää seuraava',
  'Tallennetaan',
  'Poista',
  'Kieli',
  'Käytä',
  'Tuntematon virhe.',
  'Järjestelmä',
  'Palvelin',
  'Käyttäjänimi',
  'Salasana',
  'Tietokanta',
  'Kirjaudu',
  'Haluan pysyä kirjautuneena',
  'Adminer ei tue pääsyä tietokantaan ilman salasanaa, katso tarkemmin <a href="https://www.adminer.org/en/password/"%s>täältä</a>.',
  'Valitse data',
  'Näytä rakenne',
  'Muuta näkymää',
  'Muuta taulua',
  'Uusi tietue',
  'Varoitukset',
  [
    '%d tavu',
    '%d tavua',
  ],
  'Sarake',
  'Tyyppi',
  'Kommentit',
  'Automaattinen lisäys',
  'Oletusarvo',
  'Valitse',
  'Funktiot',
  'Aggregaatiot',
  'Hae',
  'kaikkialta',
  'Lajittele',
  'alenevasti',
  'Raja',
  'Tekstin pituus',
  'Toimenpide',
  'Koko taulun läpikäynti',
  'SQL-komento',
  'avaa',
  'tallenna',
  'Muuta tietokantaa',
  'Muuta kaavaa',
  'Luo kaava',
  'Tietokantakaava',
  'Oikeudet',
  'Tuonti',
  'Vienti',
  'Luo taulu',
  'tietokanta',
  'TK',
  'valitse',
  'Poista käytöstä %s tai ota käyttöön laajennus %s tai %s.',
  'Merkkijonot',
  'Numerot',
  'Päiväys ja aika',
  'Luettelot',
  'Binäärinen',
  'Geometria',
  'ltr',
  'Olet offline-tilassa.',
  'Kirjaudu ulos',
  [
    'Liian monta epäonnistunutta sisäänkirjautumisyritystä, kokeile uudestaan %d minuutin kuluttua.',
    'Liian monta epäonnistunutta sisäänkirjautumisyritystä, kokeile uudestaan %d minuutin kuluttua.',
  ],
  'Uloskirjautuminen onnistui.',
  'Kiitos, kun käytät Admineriä, voit <a href="https://www.adminer.org/en/donation/">tehdä lahjoituksen tästä</a>.',
  'Istunto vanhentunut, kirjaudu uudelleen.',
  'Master-salasana ei ole enää voimassa. <a href="https://www.adminer.org/en/extension/"%s>Toteuta</a> %s-metodi sen tekemiseksi pysyväksi.',
  'Istuntotuki on oltava päällä.',
  'Toiminto suoritetaan sen jälkeen, kun on onnistuttu kirjautumaan samoilla käyttäjätunnuksilla uudestaan.',
  'Ei laajennusta',
  'Mitään tuetuista PHP-laajennuksista (%s) ei ole käytettävissä.',
  'Yhteydet etuoikeutettuihin portteihin eivät ole sallittuja.',
  'Virheelliset kirjautumistiedot.',
  'Syynä voi olla syötetyssä salasanassa oleva välilyönti.',
  'Virheellinen CSRF-vastamerkki. Lähetä lomake uudelleen.',
  'Kenttien sallittu enimmäismäärä ylitetty. Kasvata arvoa %s.',
  'Jollet lähettänyt tämä pyyntö Adminerista, sulje tämä sivu.',
  'Liian suuri POST-datamäärä. Pienennä dataa tai kasvata arvoa %s konfigurointitiedostossa.',
  'Voit ladata suuren SQL-tiedoston FTP:n kautta ja tuoda sen sitten palvelimelta.',
  'Vieraat avaimet',
  'kollaatio',
  'ON UPDATE',
  'ON DELETE',
  'Sarakkeen nimi',
  'Parametrin nimi',
  'Pituus',
  'Asetukset',
  'Lisää seuraava',
  'Siirrä ylös',
  'Siirrä alas',
  'Poista',
  'Tietokanta ei kelpaa.',
  'Tietokannat on poistettu.',
  'Valitse tietokanta',
  'Luo tietokanta',
  'Prosessilista',
  'Muuttujat',
  'Tila',
  '%s versio: %s PHP-laajennuksella %s',
  'Olet kirjautunut käyttäjänä: %s',
  'Virkistä',
  'Kollaatio',
  'Taulut',
  'Koko',
  'Laske',
  'Valitut',
  'Poista',
  'Materialisoitunut näkymä',
  'Näkymä',
  'Taulu',
  'Indeksit',
  'Muuta indeksejä',
  'Lähde',
  'Kohde',
  'Muuta',
  'Lisää vieras avain',
  'Liipaisimet',
  'Lisää liipaisin',
  'Pysyvä linkki',
  'Tulos',
  'Muoto',
  'Rutiinit',
  'Tapahtumat',
  'Data',
  'Luo käyttäjä',
  'ATTACH-komennolla tehtyjä kyselyjä ei tueta.',
  'Virhe kyselyssä',
  '%d / ',
  [
    '%d rivi',
    '%d riviä',
  ],
  [
    'Kysely onnistui, kohdistui %d riviin.',
    'Kysely onnistui, kohdistui %d riviin.',
  ],
  'Ei komentoja suoritettavana.',
  [
    '%d kysely onnistui.',
    '%d kyselyä onnistui.',
  ],
  'Suorita',
  'Rajoita rivimäärää',
  'Tiedoston lataus palvelimelle',
  'Tiedostojen lataaminen palvelimelle on estetty.',
  'Verkkopalvelimella Adminer-kansiossa oleva tiedosto',
  'Verkkopalvelintiedosto %s',
  'Suorita tämä',
  'Pysähdy virheeseen',
  'Näytä vain virheet',
  'Historia',
  'Tyhjennä',
  'Muokkaa kaikkia',
  'Tietue poistettiin.',
  'Tietue päivitettiin.',
  'Tietue%s lisättiin.',
  'Taulu on poistettu.',
  'Taulua on muutettu.',
  'Taulu on luotu.',
  'Taulun nimi',
  'moottori',
  'Oletusarvot',
  'Poistetaanko %s?',
  'Osioi arvolla',
  'Osiot',
  'Osion nimi',
  'Arvot',
  'Indeksejä on muutettu.',
  'Indeksityyppi',
  'Sarake (pituus)',
  'Nimi',
  'Tietokanta on poistettu.',
  'Tietokanta on nimetty uudelleen.',
  'Tietokanta on luotu.',
  'Tietokantaa on muutettu.',
  'Kutsua',
  [
    'Rutiini kutsuttu, kohdistui %d riviin.',
    'Rutiini kutsuttu, kohdistui %d riviin.',
  ],
  'Vieras avain on poistettu.',
  'Vierasta avainta on muutettu.',
  'Vieras avain on luotu.',
  'Lähde- ja kohdesarakkeiden tulee olla samaa tietotyyppiä, kohdesarakkeisiin tulee olla indeksi ja dataa, johon viitataan, täytyy olla.',
  'Vieras avain',
  'Kohdetaulu',
  'Kaava',
  'Muuta',
  'Lisää sarake',
  'Näkymää on muutettu.',
  'Näkymä on poistettu.',
  'Näkymä on luotu.',
  'Luo näkymä',
  'Tapahtuma on poistettu.',
  'Tapahtumaa on muutettu.',
  'Tapahtuma on luotu.',
  'Muuta tapahtumaa',
  'Luo tapahtuma',
  'Aloitus',
  'Lopetus',
  'Joka',
  'Säilytä, kun valmis',
  'Rutiini on poistettu.',
  'Rutiinia on muutettu.',
  'Rutiini on luotu.',
  'Muuta funktiota',
  'Muuta proseduuria',
  'Luo funktio',
  'Luo proseduuri',
  'Palautustyyppi',
  'Liipaisin on poistettu.',
  'Liipaisinta on muutettu.',
  'Liipaisin on luotu.',
  'Muuta liipaisinta',
  'Luo liipaisin',
  'Aika',
  'Tapahtuma',
  'Käyttäjä poistettiin.',
  'Käyttäjää muutettiin.',
  'Käyttäjä luotiin.',
  'Hashed',
  'Rutiini',
  'Myönnä',
  'Kiellä',
  [
    '%d prosessi lopetettu.',
    '%d prosessia lopetettu..',
  ],
  'Kloonaa',
  '%d kaikkiaan',
  'Lopeta',
  [
    'Kohdistui %d tietueeseen.',
    'Kohdistui %d tietueeseen.',
  ],
  'Ctrl+napsauta arvoa muuttaaksesi.',
  'Tiedoston täytyy olla UTF-8-muodossa.',
  [
    '%d rivi tuotiin.',
    '%d riviä tuotiin.',
  ],
  'Taulua ei voitu valita',
  'Muuta',
  'Suhteet',
  'muokkaa',
  'Käytä muokkaa-linkkiä muuttaaksesi tätä arvoa.',
  'Lataa lisää dataa',
  'Ladataan',
  'Sivu',
  'viimeinen',
  'Koko tulos',
  'Taulujen sisältö on tyhjennetty.',
  'Taulut on siirretty.',
  'Taulut on kopioitu.',
  'Tauluja on poistettu.',
  'Taulut on optimoitu.',
  'Taulut ja näkymät',
  'Hae dataa tauluista',
  'Moottori',
  'Datan pituus',
  'Indeksin pituus',
  'Vapaa tila',
  'Riviä',
  'Siivoa',
  'Optimoi',
  'Analysoi',
  'Tarkista',
  'Korjaa',
  'Tyhjennä',
  'Siirrä toiseen tietokantaan',
  'Siirrä',
  'Kopioi',
  'kirjoittaen päälle',
  'Aikataulu',
  'Tiettynä aikana',
  'Tietokanta ei tue salasanaa.',
];
		case "fr": return [
  'Êtes-vous certain(e) ?',
  '%.3f s',
  'Impossible d\'importer le fichier.',
  'La taille maximale des fichiers est de %sB.',
  'Le fichier est introuvable.',
  ',',
  '0123456789',
  'vide',
  'original',
  'Aucune table.',
  'Modifier',
  'Insérer',
  'Aucun résultat.',
  'Vous n\'avez pas les droits pour mettre à jour cette table.',
  'Enregistrer',
  'Enr. et continuer édition',
  'Enr. et insérer prochain',
  'Enregistrement',
  'Effacer',
  'Langue',
  'Utiliser',
  'Unknown error.',
  'Système',
  'Serveur',
  'Utilisateur',
  'Mot de passe',
  'Base de données',
  'Authentification',
  'Authentification permanente',
  'Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',
  'Afficher les données',
  'Afficher la structure',
  'Modifier une vue',
  'Modifier la table',
  'Nouvel élément',
  'Warnings',
  [
    '%d octet',
    '%d octets',
  ],
  'Colonne',
  'Type',
  'Commentaire',
  'Incrément automatique',
  'Valeur par défaut',
  'Sélectionner',
  'Fonctions',
  'Agrégation',
  'Rechercher',
  'n\'importe où',
  'Trier',
  'décroissant',
  'Limite',
  'Longueur du texte',
  'Action',
  'Scan de toute la table',
  'Requête SQL',
  'ouvrir',
  'enregistrer',
  'Modifier la base de données',
  'Modifier le schéma',
  'Créer un schéma',
  'Schéma de la base de données',
  'Privilèges',
  'Importer',
  'Exporter',
  'Créer une table',
  'base de données',
  'DB',
  'select',
  'Disable %s or enable %s or %s extensions.',
  'Chaînes',
  'Nombres',
  'Date et heure',
  'Listes',
  'Binaires',
  'Géométrie',
  'ltr',
  'Vous êtes hors ligne.',
  'Déconnexion',
  [
    'Trop de connexions échouées, essayez à nouveau dans %d minute.',
    'Trop de connexions échouées, essayez à nouveau dans %d minutes.',
  ],
  'Au revoir !',
  'Thanks for using Adminer, consider <a href="https://www.adminer.org/en/donation/">donating</a>.',
  'Session expirée, veuillez vous authentifier à nouveau.',
  'Le mot de passe a expiré. <a href="https://www.adminer.org/en/extension/"%s>Implémentez</a> la méthode %s afin de le rendre permanent.',
  'Veuillez activer les sessions.',
  'The action will be performed after successful login with the same credentials.',
  'Extension introuvable',
  'Aucune des extensions PHP supportées (%s) n\'est disponible.',
  'Connecting to privileged ports is not allowed.',
  'Authentification échouée.',
  'There is a space in the input password which might be the cause.',
  'Token CSRF invalide. Veuillez renvoyer le formulaire.',
  'Le nombre maximum de champs est dépassé. Veuillez augmenter %s.',
  'Si vous n\'avez pas envoyé cette requête depuis Adminer, alors fermez cette page.',
  'Données POST trop grandes. Réduisez la taille des données ou augmentez la valeur de %s dans la configuration de PHP.',
  'Vous pouvez uploader un gros fichier SQL par FTP et ensuite l\'importer depuis le serveur.',
  'Clés étrangères',
  'interclassement',
  'ON UPDATE',
  'ON DELETE',
  'Nom de la colonne',
  'Nom du paramètre',
  'Longueur',
  'Options',
  'Ajouter le prochain',
  'Déplacer vers le haut',
  'Déplacer vers le bas',
  'Effacer',
  'Base de données invalide.',
  'Les bases de données ont été supprimées.',
  'Sélectionner la base de données',
  'Créer une base de données',
  'Liste des processus',
  'Variables',
  'Statut',
  'Version de %s : %s via l\'extension PHP %s',
  'Authentifié en tant que : %s',
  'Rafraîchir',
  'Interclassement',
  'Tables',
  'Taille',
  'Calcul',
  'Sélectionnée(s)',
  'Supprimer',
  'Vue matérialisée',
  'Vue',
  'Table',
  'Index',
  'Modifier les index',
  'Source',
  'Cible',
  'Modifier',
  'Ajouter une clé étrangère',
  'Déclencheurs',
  'Ajouter un déclencheur',
  'Lien permanent',
  'Sortie',
  'Format',
  'Routines',
  'Évènements',
  'Données',
  'Créer un utilisateur',
  'ATTACH queries are not supported.',
  'Erreur dans la requête',
  '%d / ',
  [
    '%d ligne',
    '%d lignes',
  ],
  [
    'Requête exécutée avec succès, %d ligne modifiée.',
    'Requête exécutée avec succès, %d lignes modifiées.',
  ],
  'Aucune commande à exécuter.',
  [
    '%d requête exécutée avec succès.',
    '%d requêtes exécutées avec succès.',
  ],
  'Exécuter',
  'Limiter les lignes',
  'Importer un fichier',
  'L\'importation de fichier est désactivée.',
  'Depuis le serveur',
  'Fichier %s du serveur Web',
  'Exécuter le fichier',
  'Arrêter en cas d\'erreur',
  'Montrer seulement les erreurs',
  'Historique',
  'Effacer',
  'Tout modifier',
  'L\'élément a été supprimé.',
  'L\'élément a été modifié.',
  'L\'élément%s a été inséré.',
  'La table a été effacée.',
  'La table a été modifiée.',
  'La table a été créée.',
  'Nom de la table',
  'moteur',
  'Valeurs par défaut',
  'Drop %s?',
  'Partitionner par',
  'Partitions',
  'Nom de la partition',
  'Valeurs',
  'Index modifiés.',
  'Type d\'index',
  'Colonne (longueur)',
  'Nom',
  'La base de données a été supprimée.',
  'La base de données a été renommée.',
  'La base de données a été créée.',
  'La base de données a été modifiée.',
  'Appeler',
  [
    'La routine a été exécutée, %d ligne modifiée.',
    'La routine a été exécutée, %d lignes modifiées.',
  ],
  'La clé étrangère a été effacée.',
  'La clé étrangère a été modifiée.',
  'La clé étrangère a été créée.',
  'Les colonnes de source et de destination doivent être du même type, il doit y avoir un index sur les colonnes de destination et les données référencées doivent exister.',
  'Clé étrangère',
  'Table visée',
  'Schéma',
  'Modifier',
  'Ajouter une colonne',
  'La vue a été modifiée.',
  'La vue a été effacée.',
  'La vue a été créée.',
  'Créer une vue',
  'L\'évènement a été supprimé.',
  'L\'évènement a été modifié.',
  'L\'évènement a été créé.',
  'Modifier un évènement',
  'Créer un évènement',
  'Démarrer',
  'Terminer',
  'Chaque',
  'Conserver quand complété',
  'La routine a été supprimée.',
  'La routine a été modifiée.',
  'La routine a été créée.',
  'Modifier la fonction',
  'Modifier la procédure',
  'Créer une fonction',
  'Créer une procédure',
  'Type de retour',
  'Le déclencheur a été supprimé.',
  'Le déclencheur a été modifié.',
  'Le déclencheur a été créé.',
  'Modifier un déclencheur',
  'Ajouter un déclencheur',
  'Temps',
  'Évènement',
  'L\'utilisateur a été effacé.',
  'L\'utilisateur a été modifié.',
  'L\'utilisateur a été créé.',
  'Haché',
  'Routine',
  'Grant',
  'Revoke',
  [
    '%d processus a été arrêté.',
    '%d processus ont été arrêtés.',
  ],
  'Cloner',
  '%d au total',
  'Arrêter',
  [
    '%d élément a été modifié.',
    '%d éléments ont été modifiés.',
  ],
  'Ctrl+cliquez sur une valeur pour la modifier.',
  'Les fichiers doivent être encodés en UTF-8.',
  [
    '%d ligne a été importée.',
    '%d lignes ont été importées.',
  ],
  'Impossible de sélectionner la table',
  'Modification',
  'Relations',
  'modifier',
  'Utilisez le lien "modifier" pour modifier cette valeur.',
  'Charger plus de données',
  'Chargement',
  'Page',
  'dernière',
  'Résultat entier',
  'Les tables ont été tronquées.',
  'Les tables ont été déplacées.',
  'Les tables ont été copiées.',
  'Les tables ont été effacées.',
  'Les tables ont bien été optimisées.',
  'Tables et vues',
  'Rechercher dans les tables',
  'Moteur',
  'Longueur des données',
  'Longueur de l\'index',
  'Espace inutilisé',
  'Lignes',
  'Vide',
  'Optimiser',
  'Analyser',
  'Vérifier',
  'Réparer',
  'Tronquer',
  'Déplacer vers une autre base de données',
  'Déplacer',
  'Copier',
  'overwrite',
  'Horaire',
  'À un moment précis',
  'non',
];
		case "gl": return [
  'Está seguro?',
  '%.3f s',
  'Non é posible importar o ficheiro.',
  'O tamaño máximo de ficheiro permitido é de %sB.',
  'O ficheiro non existe.',
  ' ',
  '0123456789',
  'baleiro',
  'orixinal',
  'Nengunha táboa.',
  'Editar',
  'Inserir',
  'Nengún resultado.',
  'Non tes privilexios para actualizar esta táboa',
  'Gardar',
  'Gardar se seguir editando',
  'Guardar e inserir seguinte',
  'Gardando',
  'Borrar',
  'Lingua',
  'Usar',
  'Unknown error.',
  'Sistema',
  'Servidor',
  'Usuario',
  'Contrasinal',
  'Base de datos',
  'Conectar',
  'Permanecer conectado',
  'Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',
  'Seleccionar datos',
  'Amosar estructura',
  'Modificar vista',
  'Modificar táboa',
  'Novo elemento',
  'Warnings',
  [
    '%d byte',
    '%d bytes',
  ],
  'Columna',
  'Tipo',
  'Comentario',
  'Incremento automático',
  'Valor por defecto',
  'Seleccionar',
  'Funcións',
  'Agregados',
  'Buscar',
  'onde sexa',
  'Ordenar',
  'descendente',
  'Límite',
  'Lonxitud do texto',
  'Acción',
  'Escaneo completo da táboa',
  'Comando SQL',
  'abrir',
  'gardar',
  'Modificar Base de datos',
  'Modificar esquema',
  'Crear esquema',
  'Esquema de base de datos',
  'Privilexios',
  'Importar',
  'Exportar',
  'Crear táboa',
  'base de datos',
  'DB',
  'selecciona',
  'Disable %s or enable %s or %s extensions.',
  'Cadea',
  'Números',
  'Data e hora',
  'Listas',
  'Binario',
  'Xeometría',
  'ltr',
  'Non tes conexión',
  'Pechar sesión',
  [
    'Demasiados intentos de conexión, intentao de novo en %d minuto',
    'Demasiados intentos de conexión, intentao de novo en %d minutos',
  ],
  'Pechouse a sesión con éxito.',
  'Thanks for using Adminer, consider <a href="https://www.adminer.org/en/donation/">donating</a>.',
  'Caducou a sesión, por favor acceda de novo.',
  'O contrasinal principal caducou. <a href="https://www.adminer.org/en/extension/"%s>Implementa</a> o método %s para facelo permanente.',
  'As sesións deben estar habilitadas.',
  'The action will be performed after successful login with the same credentials.',
  'Non ten extensión',
  'Ningunha das extensións PHP soportadas (%s) está dispoñible.',
  'Connecting to privileged ports is not allowed.',
  'Credenciais (usuario e/ou contrasinal) inválidos.',
  'There is a space in the input password which might be the cause.',
  'Token CSRF inválido. Envíe de novo os datos do formulario.',
  'Excedida o número máximo de campos permitidos. Por favor aumente %s.',
  'Se non enviaches esta petición dende o Adminer entón pecha esta páxina',
  'Datos POST demasiado grandes. Reduza os datos ou aumente a directiva de configuración %s.',
  'Podes subir un ficheiro SQL de gran tamaño vía FTP e importalo dende o servidor',
  'Chaves externas',
  'xogo de caracteres (collation)',
  'AO ACTUALIZAR (ON UPDATE)',
  'AO BORRAR (ON DELETE)',
  'Nome da columna',
  'Nome de Parámetro',
  'Lonxitude',
  'Opcións',
  'Engadir seguinte',
  'Mover arriba',
  'Mover abaixo',
  'Eliminar',
  'Base de datos incorrecta.',
  'Elimináronse as bases de datos.',
  'Seleccionar Base de datos',
  'Crear Base de datos',
  'Lista de procesos',
  'Variables',
  'Estado',
  'Versión %s: %s a través da extensión de PHP %s',
  'Conectado como: %s',
  'Refrescar',
  'Xogo de caracteres (collation)',
  'Táboas',
  'Tamaño',
  'Calcular',
  'Selección',
  'Eliminar',
  'Vista materializada',
  'Vista',
  'Táboa',
  'Índices',
  'Modificar índices',
  'Orixe',
  'Destino',
  'Modificar',
  'Engadir chave externa',
  'Disparadores',
  'Engadir disparador',
  'Ligazón permanente',
  'Salida',
  'Formato',
  'Rutinas',
  'Eventos',
  'Datos',
  'Crear Usuario',
  'ATTACH queries are not supported.',
  'Erro na consulta',
  [
    '%d / ',
    '%d / ',
  ],
  [
    '%d fila',
    '%d filas',
  ],
  [
    'Consulta executada, %d fila afectada.',
    'Consulta executada, %d filas afectadas.',
  ],
  'Non hai comandos para executar.',
  [
    '%d consulta executada correctamente.',
    '%d consultas executadas correctamente.',
  ],
  'Executar',
  'Limitar filas',
  'Importar ficheiro',
  'Importación de ficheiros deshablilitada.',
  'Desde o servidor',
  'Ficheiro de servidor web %s',
  'Executar ficheiro',
  'Parar en caso de erro',
  'Amosar só erros',
  'Histórico',
  'Baleirar',
  'Editar todo',
  'Eliminouse o elemento.',
  'Modificouse o elemento.',
  'Inseríuse o elemento%s.',
  'Eliminouse a táboa.',
  'Modificouse a táboa.',
  'Creouse a táboa.',
  'Nome da táboa',
  'motor',
  'Valores predeterminados',
  'Drop %s?',
  'Particionar por',
  'Particións',
  'Nome da Partición',
  'Valores',
  'Alteráronse os índices.',
  'Tipo de índice',
  'Columna (lonxitude)',
  'Nome',
  'Eliminouse a base de datos.',
  'Renomeouse a base de datos.',
  'Creouse a base de datos.',
  'Modificouse a base de datos.',
  'Chamar',
  [
    'Chamouse á rutina, %d fila afectada.',
    'Chamouse á rutina, %d filas afectadas.',
  ],
  'Eliminouse a chave externa.',
  'Modificouse a chave externa.',
  'Creouse a chave externa.',
  'As columnas de orixe e destino deben ser do mesmo tipo, debe existir un índice nas columnas de destino e os datos referenciados deben existir.',
  'Chave externa',
  'táboa de destino',
  'Esquema',
  'Cambiar',
  'Engadir columna',
  'Modificouse a vista.',
  'Eliminouse a vista.',
  'Creouse a vista.',
  'Crear vista',
  'Eliminouse o evento.',
  'Modificouse o evento.',
  'Creouse o evento.',
  'Modificar Evento',
  'Crear Evento',
  'Inicio',
  'Fin',
  'Cada',
  'Ao completar manter',
  'Eliminouse o procedemento.',
  'Alterouse o procedemento.',
  'Creouse o procedemento.',
  'Modificar Función',
  'Modificar procedemento',
  'Crear función',
  'Crear procedemento',
  'Tipo de valor devolto',
  'Eliminouse o disparador.',
  'Modificouse o disparador.',
  'Creouse o disparador.',
  'Modificar Disparador',
  'Crear Disparador',
  'Tempo',
  'Evento',
  'Eliminouse o usuario.',
  'Modificouse o usuario.',
  'Creouse o usuario.',
  'Hashed',
  'Rutina',
  'Conceder',
  'Revocar',
  [
    '%d proceso foi detido.',
    '%d procesos foron detidos.',
  ],
  'Clonar',
  '%d en total',
  'Deter',
  [
    '%d elemento afectado.',
    '%d elementos afectados.',
  ],
  'Ctrl+clic sobre o valor para editalo.',
  'O ficheiro ten que estar codificado con UTF-8',
  [
    '%d fila importada.',
    '%d filas importadas.',
  ],
  'No é posible seleccionar a táboa',
  'Modificar',
  'Relacins',
  'editar',
  'Use a ligazón de edición para modificar este valor.',
  'Cargar máis datos',
  'Cargando',
  'Páxina',
  'último',
  'Resultado completo',
  'Baleiráronse as táboas.',
  'Movéronse as táboas.',
  'Copiáronse as táboas.',
  'Elimináronse as táboas.',
  'Optimizáronse as táboas',
  'táboas e vistas',
  'Buscar datos en táboas',
  'Motor',
  'Lonxitude de datos',
  'Lonxitude de índice',
  'Espazo dispoñible',
  'Filas',
  'Baleirar',
  'Optimizar',
  'Analizar',
  'Comprobar',
  'Reparar',
  'Baleirar',
  'Mover a outra base de datos',
  'Mover',
  'Copiar',
  'overwrite',
  'Axenda',
  'No tempo indicado',
  'non',
];
		case "he": return [
  'האם אתה בטוח?',
  '%.3f s',
  'העלאת הקובץ נכשלה',
  'גודל מקסימלאי להעלאה: %sB',
  'הקובץ אינו קיים',
  ',',
  '0123456789',
  'ריק',
  'מקורי',
  'אין טבלאות',
  'ערוך',
  'הכנס',
  'אין שורות',
  'אין לך ההרשאות המתאימות לעדכן טבלה זו',
  'שמור',
  'שמור והמשך לערוך',
  'שמור והמשך להכניס',
  'שומר',
  'מחק',
  'שפה',
  'השתמש',
  'Unknown error.',
  'מערכת',
  'שרת',
  'שם משתמש',
  'סיסמה',
  'מסד נתונים',
  'התחברות',
  'התחבר לצמיתות',
  'Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',
  'בחר נתונים',
  'הראה מבנה',
  'שנה תצוגה',
  'שנה טבלה',
  'פריט חדש',
  'Warnings',
  '%d בתים',
  'עמודה',
  'סוג',
  'הערה',
  'הגדלה אוטומטית',
  'ערך ברירת מחדל',
  'בחר',
  'פונקציות',
  'צבירה',
  'חפש',
  'בכל מקום',
  'מיין',
  'סדר הפוך',
  'הגבל',
  'אורך הטקסט',
  'פעולות',
  'סריקה טבלה מלאה',
  'שאילתת SQL',
  'פתח',
  'שמור',
  'שנה מסד נתונים',
  'שנה סכמה',
  'צור סכמה',
  'סכמת מסד נתונים',
  'פריווילגיות',
  'יבא',
  'יצא',
  'צור טבלה',
  'מסד נתונים',
  'DB',
  'בחר',
  'Disable %s or enable %s or %s extensions.',
  'מחרוזות',
  'מספרים',
  'תאריך ושעה',
  'רשימות',
  'בינארי',
  'גיאומטריה',
  'rtl',
  'הינך לא מקוון',
  'התנתק',
  'יותר מידי נסיונות כניסה נכשלו, אנא נסה עוד %d דקות',
  'ההתחברות הצליחה',
  'תודה שהשתמש ב-adminer אנא שקול <a href="https://www.adminer.org/en/donation/">לתרום</a>.',
  'תם זמן ההפעלה, אנא התחבר שוב',
  'סיסמת המאסטר פגה <a href="https://www.adminer.org/en/extension/"%s>התקן תוסף</a> על מנת להפוך את זה לתמידי',
  'חובה להפעיל תמיכה בסשן',
  'The action will be performed after successful login with the same credentials.',
  'אין תוסף',
  'שום תוסף PHP (%s) זמין',
  'Connecting to privileged ports is not allowed.',
  'פרטי התחברות שגויים',
  'There is a space in the input password which might be the cause.',
  'כשל באבטחת נתונים, שלח טופס שוב',
  'הגעת למספר השדות המרבי. בבקשה הגדל את %s',
  'אם לא אתה שלחת בקשה ל-Adminer הינך יכול לסגור חלון זה',
  'מידע גדול מידי נשלח ב-POST. הקטן את את המידע הוא הגדלת את הגדרות ה-%s',
  'ניתן לעלות קבצים ב-FTP ואז למשוך אותם מהשרת',
  'מפתחות זרים',
  'קולקציה',
  'בעת עידכון',
  'בעת מחיקה',
  'שם עמודה',
  'שם הפרמטר',
  'אורך',
  'אפשרויות',
  'הוסף הבא',
  'הזז למעלה',
  'הזז למטה',
  'הסר',
  'מסד נתונים שגוי',
  'מסד הנתונים הושלך',
  'בחר מסד נתונים',
  'צור מסד נתונים',
  'רשימת תהליכים',
  'משתנים',
  'סטטוס',
  '%s גרסה: %s דרך תוסף PHP %s',
  'מחובר כ: %s',
  'רענן',
  'קולקציה',
  'טבלאות',
  'גודל',
  'חישוב',
  'נבחרים',
  'השלך',
  'תצוגת מימוש ',
  'הצג',
  'טבלה',
  'אינדקסים',
  'שנה אינדקסים',
  'מקור',
  'יעד',
  'שנה',
  'הוסף מפתח זר',
  'מפעילים',
  'הוסף טריגר',
  'קישור סופי',
  'פלט',
  'פורמט',
  'רוטינות',
  'אירועים',
  'נתונים',
  'צור משתמש',
  'שאילתת ATTACH אינה נתמכת',
  'שגיאה בשאילתה',
  '%d / ',
  '%d שורות',
  'השאילתה בוצעה כהלכה, %d שורות הושפעו',
  'לא נמצאו פקודות להרצה',
  '%d שאילתות בוצעו בהצלחה',
  'הרץ',
  'הגבל שורות',
  'העלה קובץ',
  'העלאת קבצים מבוטלת',
  'משרת',
  'קובץ השרת %s',
  'הרץ קובץ',
  'עצור בעת שגיאה',
  'הראה שגיאות בלבד',
  'היסטוריה',
  'נקה',
  'ערוך הכל',
  'הפריט נמחק',
  'הפריט עודכן',
  'הפריט %s הוזן בהצלחה',
  'הטבלה הושלכה',
  'הטבלה שונתה',
  'הטבלה נוצרה',
  'שם הטבלה',
  'מנוע',
  'ערכי ברירת מחדל',
  'Drop %s?',
  'מחיצות ע"י',
  'מחיצות',
  'שם מחיצה',
  'ערכים',
  'האינדקסים שונו',
  'סוג אינדקס',
  'עמודה (אורך)',
  'שם',
  'מסד הנתונים הושלך',
  'שם מסד הנתונים שונה',
  'מסד הנתונים נוצר',
  'מסד הנתונים שונה',
  'קרא',
  'הרוטינה נקראה, %d שורות הושפעו',
  'המפתח הזר הושלך',
  'המפתח הזר שונה',
  'המפתח הזר נוצר',
  'על עמודות המקור והיעד להיות מאותו טיפוס נתונים, חובה שיהיה אינדקס בעמודת היעד ושהמידע המתאים יהיה קיים',
  'מפתח זר',
  'טבלת יעד',
  'סכמה',
  'שנה',
  'הוסף עמודה',
  'התצוגה שונתה',
  'התצוגה הושלכה',
  'התצוגה נוצרה',
  'צור תצוגה',
  'האירוע הושלך',
  'האירוע שונה',
  'האירוע נוצר',
  'שנה אירוע',
  'צור אירוע',
  'התחלה',
  'סיום',
  'כל',
  'בעת סיום שמור',
  'הרוטינה הושלכה',
  'הרוטינה שונתה',
  'הרוטינה נוצרה',
  'שנה פונקציה',
  'שנה פרוצדורה',
  'צור פונקציה',
  'צור פרוצדורה',
  'סוג ערך מוחזר',
  'הטריגר הושלך',
  'הטריגר שונה',
  'הטריגר נוצר',
  'שנה טריגר',
  'צור טריגר',
  'זמן',
  'אירוע',
  'המשתמש הושלך',
  'המשתמש שונה',
  'המשתמש נוצר',
  'הצפנה',
  'רוטינה',
  'הענק',
  'שלול',
  '%d תהליכים חוסלו',
  'שכפל',
  '%d בסך הכל',
  'חסל',
  '%d פריטים הושפעו',
  'לחץ ctrl + לחיצת עכבר לערוך ערך זה',
  'על הקובץ להיות בקידוד utf-8',
  '%d שורות יובאו',
  'בחירת הטבלה נכשלה',
  'ערוך',
  'הקשרים',
  'ערוך',
  'השתמש בקישור העריכה בשביל לשנות את הערך',
  'טען נתונים נוספים',
  'טוען',
  'עמוד',
  'אחרון',
  'כל התוצאות',
  'הטבלה קוצרה',
  'הטבלה הועברה',
  'הטבלה הועתקה',
  'הטבלה הושלכה',
  'הטבלאות עברו אופטימיזציה',
  'טבלאות ותצוגות',
  'חפש מידע בטבלאות',
  'מנוע',
  'אורך נתונים',
  'אורך אינדקס',
  'נתונים משוחררים',
  'שורות',
  'וואקום',
  'יעל',
  'נתח',
  'בדוק',
  'תקן',
  'קצר',
  'העבר למסד נתונים אחר',
  'העבר',
  'העתק',
  'overwrite',
  'תזמן',
  'לפי זמן נתון',
  'לא',
];
		case "hu": return [
  'Biztos benne?',
  '%.3f másodperc',
  'Nem tudom feltölteni a fájlt.',
  'A maximális fájlméret %s B.',
  'A fájl nem létezik.',
  ' ',
  '0123456789',
  'üres',
  'eredeti',
  'Nincs tábla.',
  'Szerkeszt',
  'Beszúr',
  'Nincs megjeleníthető eredmény.',
  'You have no privileges to update this table.',
  'Mentés',
  'Mentés és szerkesztés folytatása',
  'Mentés és újat beszúr',
  'Saving',
  'Törlés',
  'Nyelv',
  'Használ',
  'Unknown error.',
  'Adatbázis',
  'Szerver',
  'Felhasználó',
  'Jelszó',
  'Adatbázis',
  'Belépés',
  'Emlékezz rám',
  'Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',
  'Tartalom',
  'Struktúra',
  'Nézet módosítása',
  'Tábla módosítása',
  'Új tétel',
  'Warnings',
  [
    '%d bájt',
    '%d bájt',
    '%d bájt',
  ],
  'Oszlop',
  'Típus',
  'Megjegyzés',
  'Automatikus növelés',
  'Default value',
  'Kiválasztás',
  'Funkciók',
  'Aggregálás',
  'Keresés',
  'bárhol',
  'Sorba rendezés',
  'csökkenő',
  'korlát',
  'Szöveg hossz',
  'Művelet',
  'Full table scan',
  'SQL parancs',
  'megnyit',
  'ment',
  'Adatbázis módosítása',
  'Séma módosítása',
  'Séma létrehozása',
  'Adatbázis séma',
  'Privilégiumok',
  'Importálás',
  'Export',
  'Tábla létrehozása',
  'adatbázis',
  'DB',
  'kiválasztás',
  'Disable %s or enable %s or %s extensions.',
  'Szöveg',
  'Szám',
  'Dátum és idő',
  'Lista',
  'Bináris',
  'Geometria',
  'ltr',
  'You are offline.',
  'Kilépés',
  'Too many unsuccessful logins, try again in %d minute(s).',
  'Sikeres kilépés.',
  'Thanks for using Adminer, consider <a href="https://www.adminer.org/en/donation/">donating</a>.',
  'Munkamenet lejárt, jelentkezz be újra.',
  'Master password expired. <a href="https://www.adminer.org/en/extension/"%s>Implement</a> %s method to make it permanent.',
  'A munkameneteknek (session) engedélyezve kell lennie.',
  'The action will be performed after successful login with the same credentials.',
  'Nincs kiterjesztés',
  'Nincs egy elérhető támogatott PHP kiterjesztés (%s) sem.',
  'Connecting to privileged ports is not allowed.',
  'Érvénytelen adatok.',
  'There is a space in the input password which might be the cause.',
  'Érvénytelen CSRF azonosító. Küldd újra az űrlapot.',
  'A maximális mezőszámot elérted. Növeld meg ezeket: %s.',
  'If you did not send this request from Adminer then close this page.',
  'Túl sok a POST adat! Csökkentsd az adat méretét, vagy növeld a %s beállítást.',
  'You can upload a big SQL file via FTP and import it from server.',
  'Idegen kulcs',
  'egybevetés',
  'frissítéskor',
  'törléskor',
  'Oszlop neve',
  'Paraméter neve',
  'Hossz',
  'Opciók',
  'Következő hozzáadása',
  'Felfelé',
  'Lefelé',
  'Eltávolítás',
  'Érvénytelen adatbázis.',
  'Adatbázis eldobva.',
  'Adatbázis kiválasztása',
  'Adatbázis létrehozása',
  'Folyamatok',
  'Változók',
  'Állapot',
  '%s verzió: %s, PHP: %s',
  'Belépve: %s',
  'Frissítés',
  'Egybevetés',
  'Táblák',
  'Size',
  'Compute',
  'Selected',
  'Eldob',
  'Materialized view',
  'Nézet',
  'Tábla',
  'Indexek',
  'Index módosítása',
  'Forrás',
  'Cél',
  'Módosítás',
  'Idegen kulcs hozzadása',
  'Trigger',
  'Trigger hozzáadása',
  'Hivatkozás',
  'Kimenet',
  'Formátum',
  'Rutinok',
  'Esemény',
  'Adat',
  'Felhasználó hozzáadása',
  'ATTACH queries are not supported.',
  'Hiba a lekérdezésben',
  '%d / ',
  [
    '%d sor',
    '%d sor',
    '%d sor',
  ],
  [
    'Lekérdezés sikeresen végrehajtva, %d sor érintett.',
    'Lekérdezés sikeresen végrehajtva, %d sor érintett.',
    'Lekérdezés sikeresen végrehajtva, %d sor érintett.',
  ],
  'Nincs végrehajtható parancs.',
  '%d sikeres lekérdezés.',
  'Végrehajt',
  'Limit rows',
  'Fájl feltöltése',
  'A fájl feltöltés le van tiltva.',
  'Szerverről',
  'Webszerver fájl %s',
  'Fájl futtatása',
  'Hiba esetén megáll',
  'Csak a hibák mutatása',
  'Történet',
  'Törlés',
  'Összes szerkesztése',
  'A tétel törölve.',
  'A tétel frissítve.',
  '%s tétel beszúrva.',
  'A tábla eldobva.',
  'A tábla módosult.',
  'A tábla létrejött.',
  'Tábla név',
  'motor',
  'Alapértelmezett értékek',
  'Drop %s?',
  'Particionálás ezzel',
  'Particiók',
  'Partició neve',
  'Értékek',
  'Az indexek megváltoztak.',
  'Index típusa',
  'Oszop (méret)',
  'Név',
  'Az adatbázis eldobva.',
  'Az adadtbázis átnevezve.',
  'Az adatbázis létrejött.',
  'Az adatbázis módosult.',
  'Meghív',
  [
    'Rutin meghívva, %d sor érintett.',
    'Rutin meghívva, %d sor érintett.',
    'Rutin meghívva, %d sor érintett.',
  ],
  'Idegen kulcs eldobva.',
  'Idegen kulcs módosult.',
  'Idegen kulcs létrejött.',
  'A forrás és cél oszlopoknak azonos típusúak legyenek, a cél oszlopok indexeltek legyenek, és a hivatkozott adatnak léteznie kell.',
  'Idegen kulcs',
  'Cél tábla',
  'Séma',
  'Változtat',
  'Oszlop hozzáadása',
  'A nézet módosult.',
  'A nézet eldobva.',
  'A nézet létrejött.',
  'Nézet létrehozása',
  'Az esemény eldobva.',
  'Az esemény módosult.',
  'Az esemény létrejött.',
  'Esemény módosítása',
  'Esemény létrehozása',
  'Kezd',
  'Vége',
  'Minden',
  'Befejezéskor megőrzi',
  'A rutin eldobva.',
  'A rutin módosult.',
  'A rutin létrejött.',
  'Funkció módosítása',
  'Eljárás módosítása',
  'Funkció létrehozása',
  'Eljárás létrehozása',
  'Visszatérési érték',
  'A trigger eldobva.',
  'A trigger módosult.',
  'A trigger létrejött.',
  'Trigger módosítása',
  'Trigger létrehozása',
  'Idő',
  'Esemény',
  'A felhasználó eldobva.',
  'A felhasználó módosult.',
  'A felhasználó létrejött.',
  'Hashed',
  'Rutin',
  'Engedélyezés',
  'Visszavonás',
  [
    '%d folyamat leállítva.',
    '%d folyamat leállítva.',
    '%d folyamat leállítva.',
  ],
  'Klónoz',
  'összesen %d',
  'Leállít',
  [
    '%d tétel érintett.',
    '%d tétel érintett.',
    '%d tétel érintett.',
  ],
  'Ctrl+click on a value to modify it.',
  'File must be in UTF-8 encoding.',
  [
    '%d sor importálva.',
    '%d sor importálva.',
    '%d sor importálva.',
  ],
  'Nem tudom kiválasztani a táblát',
  'Modify',
  'Reláció',
  'szerkeszt',
  'Használd a szerkesztés hivatkozást ezen érték módosításához.',
  'Load more data',
  'Loading',
  'oldal',
  'utolsó',
  'Összes eredményt mutatása',
  'A tábla felszabadítva.',
  'Táblák áthelyezve.',
  'Táblák átmásolva.',
  'Táblák eldobva.',
  'Tables have been optimized.',
  'Táblák és nézetek',
  'Keresés a táblákban',
  'Motor',
  'Méret',
  'Index hossz',
  'Adat szabad',
  'Sorok',
  'Vacuum',
  'Optimalizál',
  'Elemzés',
  'Ellenőrzés',
  'Javít',
  'Felszabadít',
  'Áthelyezés másik adatbázisba',
  'Áthelyez',
  'Másolás',
  'overwrite',
  'Ütemzés',
  'Megadott időben',
  'óó:pp:mm',
];
		case "id": return [
  'Anda yakin?',
  '%.3f s',
  'Tidak dapat mengunggah berkas.',
  'Besar berkas yang diizinkan adalah %sB.',
  'Berkas tidak ada.',
  '.',
  '0123456789',
  'kosong',
  'asli',
  'Tidak ada tabel.',
  'Sunting',
  'Sisipkan',
  'Tidak ada baris.',
  'You have no privileges to update this table.',
  'Simpan',
  'Simpan dan lanjut menyunting',
  'Simpan dan sisipkan berikutnya',
  'Saving',
  'Hapus',
  'Bahasa',
  'Gunakan',
  'Unknown error.',
  'Sistem',
  'Server',
  'Pengguna',
  'Sandi',
  'Basis data',
  'Masuk',
  'Masuk permanen',
  'Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',
  'Pilih data',
  'Lihat struktur',
  'Ubah tampilan',
  'Ubah tabel',
  'Entri baru',
  'Warnings',
  '%d bita',
  'Kolom',
  'Jenis',
  'Komentar',
  'Inkrementasi Otomatis',
  'Default value',
  'Pilih',
  'Fungsi',
  'Agregasi',
  'Cari',
  'di mana pun',
  'Urutkan',
  'menurun',
  'Batas',
  'Panjang teks',
  'Tindakan',
  'Pindai tabel lengkap',
  'Perintah SQL',
  'buka',
  'simpan',
  'Ubah basis data',
  'Ubah skema',
  'Buat skema',
  'Skema basis data',
  'Privilese',
  'Impor',
  'Ekspor',
  'Buat tabel',
  'basis data',
  'DB',
  'pilih',
  'Disable %s or enable %s or %s extensions.',
  'String',
  'Angka',
  'Tanggal dan waktu',
  'Daftar',
  'Binari',
  'Geometri',
  'ltr',
  'You are offline.',
  'Keluar',
  'Too many unsuccessful logins, try again in %d minute(s).',
  'Berhasil keluar.',
  'Thanks for using Adminer, consider <a href="https://www.adminer.org/en/donation/">donating</a>.',
  'Sesi habis, silakan masuk lagi.',
  'Master password expired. <a href="https://www.adminer.org/en/extension/"%s>Implement</a> %s method to make it permanent.',
  'Dukungan sesi harus aktif.',
  'The action will be performed after successful login with the same credentials.',
  'Ekstensi tidak ada',
  'Ekstensi PHP yang didukung (%s) tidak ada.',
  'Connecting to privileged ports is not allowed.',
  'Akses tidak sah.',
  'There is a space in the input password which might be the cause.',
  'Token CSRF tidak sah. Kirim ulang formulir.',
  'Sudah lebih dumlah ruas maksimum yang diizinkan. Harap naikkan %s.',
  'If you did not send this request from Adminer then close this page.',
  'Data POST terlalu besar. Kurangi data atau perbesar direktif konfigurasi %s.',
  'You can upload a big SQL file via FTP and import it from server.',
  'Kunci asing',
  'kolasi',
  'ON UPDATE',
  'ON DELETE',
  'Nama kolom',
  'Nama parameter',
  'Panjang',
  'Opsi',
  'Tambah setelahnya',
  'Naik',
  'Turun',
  'Hapus',
  'Basis data tidak sah.',
  'Basis data berhasil dihapus.',
  'Pilih basis data',
  'Buat basis data',
  'Daftar proses',
  'Variabel',
  'Status',
  'Versi %s: %s dengan ekstensi PHP %s',
  'Masuk sebagai: %s',
  'Segarkan',
  'Kolasi',
  'Tabel',
  'Size',
  'Compute',
  'Selected',
  'Hapus',
  'Materialized view',
  'Tampilan',
  'Tabel',
  'Indeks',
  'Ubah indeks',
  'Sumber',
  'Sasaran',
  'Ubah',
  'Tambah kunci asing',
  'Pemicu',
  'Tambah pemicu',
  'Pranala permanen',
  'Hasil',
  'Format',
  'Rutin',
  'Even',
  'Data',
  'Buat pengguna',
  'ATTACH queries are not supported.',
  'Galat dalam kueri',
  '%d / ',
  '%d baris',
  'Kueri berhasil, %d baris terpengaruh.',
  'Tidak ada perintah untuk dijalankan.',
  '%d kueri berhasil dijalankan.',
  'Jalankan',
  'Limit rows',
  'Unggah berkas',
  'Pengunggahan berkas dimatikan.',
  'Dari server',
  'Berkas server web %s',
  'Jalankan berkas',
  'Hentikan jika galat',
  'Hanya tampilkan galat',
  'Riwayat',
  'Bersihkan',
  'Sunting semua',
  'Entri berhasil dihapus.',
  'Entri berhasil diperbarui.',
  'Entri%s berhasil disisipkan.',
  'Tabel berhasil dihapus.',
  'Tabel berhasil diubah.',
  'Tabel berhasil dibuat.',
  'Nama tabel',
  'mesin',
  'Nilai bawaan',
  'Drop %s?',
  'Partisi menurut',
  'Partisi',
  'Nama partisi',
  'Nilai',
  'Indeks berhasil diubah.',
  'Jenis Indeks',
  'Kolom (panjang)',
  'Nama',
  'Basis data berhasil dihapus.',
  'Basis data berhasil diganti namanya.',
  'Basis data berhasil dibuat.',
  'Basis data berhasil diubah.',
  'Panggilan',
  'Rutin telah dipanggil, %d baris terpengaruh.',
  'Kunci asing berhasil dihapus.',
  'Kunci asing berhasil diubah.',
  'Kunci asing berhasil dibuat.',
  'Kolom sumber dan sasaran harus memiliki jenis data yang sama. Kolom sasaran harus memiliki indeks dan data rujukan harus ada.',
  'Kunci asing',
  'Tabel sasaran',
  'Skema',
  'Ubah',
  'Tambah kolom',
  'Tampilan berhasil diubah.',
  'Tampilan berhasil dihapus.',
  'Tampilan berhasil dibuat.',
  'Buat tampilan',
  'Even berhasil dihapus.',
  'Even berhasil diubah.',
  'Even berhasil dibuat.',
  'Ubah even',
  'Buat even',
  'Mulai',
  'Selesai',
  'Setiap',
  'Pertahankan saat selesai',
  'Rutin berhasil dihapus.',
  'Rutin berhasil diubah.',
  'Rutin berhasil dibuat.',
  'Ubah fungsi',
  'Ubah prosedur',
  'Buat fungsi',
  'Buat prosedur',
  'Jenis pengembalian',
  'Pemicu berhasil dihapus.',
  'Pemicu berhasil diubah.',
  'Pemicu berhasil dibuat.',
  'Ubah pemicu',
  'Buat pemicu',
  'Waktu',
  'Even',
  'Pengguna berhasil dihapus.',
  'Pengguna berhasil diubah.',
  'Pengguna berhasil dibuat.',
  'Hashed*',
  'Rutin',
  'Beri',
  'Tarik',
  '%d proses berhasil dihentikan.',
  'Gandakan',
  '%d total',
  'Hentikan',
  '%d entri terpengaruh.',
  'Ctrl+click on a value to modify it.',
  'File must be in UTF-8 encoding.',
  '%d baris berhasil diimpor.',
  'Gagal memilih tabel',
  'Modify',
  'Relasi',
  'sunting',
  'Gunakan pranala suntingan untuk mengubah nilai ini.',
  'Load more data',
  'Loading',
  'Halaman',
  'terakhir',
  'Seluruh hasil',
  'Tabel berhasil dikosongkan.',
  'Tabel berhasil dipindahkan.',
  'Tabel berhasil disalin.',
  'Tabel berhasil dihapus.',
  'Tabel berhasil dioptimalkan.',
  'Tabel dan tampilan',
  'Cari data dalam tabel',
  'Mesin',
  'Panjang Data',
  'Panjang Indeks',
  'Data Bebas',
  'Baris',
  'Vacuum',
  'Optimalkan',
  'Analisis',
  'Periksa',
  'Perbaiki',
  'Kosongkan',
  'Pindahkan ke basis data lain',
  'Pindahkan',
  'Salin',
  'overwrite',
  'Jadwal',
  'Pada waktu tertentu',
  'Ubah jenis',
];
		case "it": return [
  'Sicuro?',
  '%.3f s',
  'Caricamento del file non riuscito.',
  'La dimensione massima del file è %sB.',
  'Il file non esiste.',
  '.',
  '0123456789',
  'vuoto',
  'originale',
  'No tabelle.',
  'Modifica',
  'Inserisci',
  'Nessuna riga.',
  'You have no privileges to update this table.',
  'Salva',
  'Salva e continua',
  'Salva e inserisci un altro',
  'Saving',
  'Elimina',
  'Lingua',
  'Usa',
  'Unknown error.',
  'Sistema',
  'Server',
  'Utente',
  'Password',
  'Database',
  'Autenticazione',
  'Login permanente',
  'Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',
  'Visualizza dati',
  'Visualizza struttura',
  'Modifica vista',
  'Modifica tabella',
  'Nuovo elemento',
  'Warnings',
  [
    '%d byte',
    '%d bytes',
  ],
  'Colonna',
  'Tipo',
  'Commento',
  'Auto incremento',
  'Default value',
  'Seleziona',
  'Funzioni',
  'Aggregazione',
  'Cerca',
  'ovunque',
  'Ordina',
  'discendente',
  'Limite',
  'Lunghezza testo',
  'Azione',
  'Full table scan',
  'Comando SQL',
  'apri',
  'salva',
  'Modifica database',
  'Modifica schema',
  'Crea schema',
  'Schema database',
  'Privilegi',
  'Importa',
  'Esporta',
  'Crea tabella',
  'database',
  'DB',
  'seleziona',
  'Disable %s or enable %s or %s extensions.',
  'Stringhe',
  'Numeri',
  'Data e ora',
  'Liste',
  'Binari',
  'Geometria',
  'ltr',
  'You are offline.',
  'Esci',
  'Too many unsuccessful logins, try again in %d minute(s).',
  'Uscita effettuata con successo.',
  'Thanks for using Adminer, consider <a href="https://www.adminer.org/en/donation/">donating</a>.',
  'Sessione scaduta, autenticarsi di nuovo.',
  'Master password expired. <a href="https://www.adminer.org/en/extension/"%s>Implement</a> %s method to make it permanent.',
  'Le sessioni devono essere abilitate.',
  'The action will be performed after successful login with the same credentials.',
  'Estensioni non presenti',
  'Nessuna delle estensioni PHP supportate (%s) disponibile.',
  'Connecting to privileged ports is not allowed.',
  'Credenziali non valide.',
  'There is a space in the input password which might be the cause.',
  'Token CSRF non valido. Reinvia la richiesta.',
  'Troppi campi. Per favore aumentare %s.',
  'If you did not send this request from Adminer then close this page.',
  'Troppi dati via POST. Ridurre i dati o aumentare la direttiva di configurazione %s.',
  'You can upload a big SQL file via FTP and import it from server.',
  'Chiavi esterne',
  'collazione',
  'ON UPDATE',
  'ON DELETE',
  'Nome colonna',
  'Nome parametro',
  'Lunghezza',
  'Opzioni',
  'Aggiungi altro',
  'Sposta su',
  'Sposta giu',
  'Rimuovi',
  'Database non valido.',
  'Database eliminati.',
  'Seleziona database',
  'Crea database',
  'Elenco processi',
  'Variabili',
  'Stato',
  'Versione %s: %s via estensione PHP %s',
  'Autenticato come: %s',
  'Aggiorna',
  'Collazione',
  'Tabelle',
  'Size',
  'Compute',
  'Selected',
  'Elimina',
  'Materialized view',
  'Vedi',
  'Tabella',
  'Indici',
  'Modifica indici',
  'Sorgente',
  'Obiettivo',
  'Modifica',
  'Aggiungi foreign key',
  'Trigger',
  'Aggiungi trigger',
  'Link permanente',
  'Risultato',
  'Formato',
  'Routine',
  'Eventi',
  'Dati',
  'Crea utente',
  'ATTACH queries are not supported.',
  'Errore nella query',
  '%d / ',
  [
    '%d riga',
    '%d righe',
  ],
  [
    'Esecuzione della query OK, %d riga interessata.',
    'Esecuzione della query OK, %d righe interessate.',
  ],
  'Nessun commando da eseguire.',
  [
    '%d query eseguita con successo.',
    '%d query eseguite con successo.',
  ],
  'Esegui',
  'Limit rows',
  'Caricamento file',
  'Caricamento file disabilitato.',
  'Dal server',
  'Webserver file %s',
  'Esegui file',
  'Stop su errore',
  'Mostra solo gli errori',
  'Storico',
  'Pulisci',
  'Modifica tutto',
  'Elemento eliminato.',
  'Elemento aggiornato.',
  'Elemento%s inserito.',
  'Tabella eliminata.',
  'Tabella modificata.',
  'Tabella creata.',
  'Nome tabella',
  'motore',
  'Valori predefiniti',
  'Drop %s?',
  'Partiziona per',
  'Partizioni',
  'Nome partizione',
  'Valori',
  'Indici modificati.',
  'Tipo indice',
  'Colonna (lunghezza)',
  'Nome',
  'Database eliminato.',
  'Database rinominato.',
  'Database creato.',
  'Database modificato.',
  'Chiama',
  [
    'Routine chiamata, %d riga interessata.',
    'Routine chiamata, %d righe interessate.',
  ],
  'Foreign key eliminata.',
  'Foreign key modificata.',
  'Foreign key creata.',
  'Le colonne sorgente e destinazione devono essere dello stesso tipo e ci deve essere un indice sulla colonna di destinazione e sui dati referenziati.',
  'Foreign key',
  'Tabella obiettivo',
  'Schema',
  'Cambia',
  'Aggiungi colonna',
  'Vista modificata.',
  'Vista eliminata.',
  'Vista creata.',
  'Crea vista',
  'Evento eliminato.',
  'Evento modificato.',
  'Evento creato.',
  'Modifica evento',
  'Crea evento',
  'Inizio',
  'Fine',
  'Ogni',
  'Al termine preservare',
  'Routine eliminata.',
  'Routine modificata.',
  'Routine creata.',
  'Modifica funzione',
  'Modifica procedura',
  'Crea funzione',
  'Crea procedura',
  'Return type',
  'Trigger eliminato.',
  'Trigger modificato.',
  'Trigger creato.',
  'Modifica trigger',
  'Crea trigger',
  'Orario',
  'Evento',
  'Utente eliminato.',
  'Utente modificato.',
  'Utente creato.',
  'Hashed',
  'Routine',
  'Permetti',
  'Revoca',
  [
    '%d processo interrotto.',
    '%d processi interrotti.',
  ],
  'Clona',
  '%d in totale',
  'Interrompi',
  [
    'Il risultato consiste in %d elemento.',
    'Il risultato consiste in %d elementi.',
  ],
  'Fai Ctrl+click su un valore per modificarlo.',
  'File must be in UTF-8 encoding.',
  [
    '%d riga importata.',
    '%d righe importate.',
  ],
  'Selezione della tabella non riuscita',
  'Modify',
  'Relazioni',
  'modifica',
  'Usa il link modifica per modificare questo valore.',
  'Load more data',
  'Loading',
  'Pagina',
  'ultima',
  'Intero risultato',
  'Le tabelle sono state svuotate.',
  'Le tabelle sono state spostate.',
  'Le tabelle sono state copiate.',
  'Le tabelle sono state eliminate.',
  'Tables have been optimized.',
  'Tabelle e viste',
  'Cerca nelle tabelle',
  'Motore',
  'Lunghezza dato',
  'Lunghezza indice',
  'Dati liberi',
  'Righe',
  'Vacuum',
  'Ottimizza',
  'Analizza',
  'Controlla',
  'Ripara',
  'Svuota',
  'Sposta in altro database',
  'Sposta',
  'Copia',
  'overwrite',
  'Pianifica',
  'A tempo prestabilito',
  'HH:MM:SS',
];
		case "ja": return [
  '実行しますか？',
  '%.3f 秒',
  'ファイルをアップロードできません',
  '最大ファイルサイズ %sB',
  'ファイルは存在しません',
  ',',
  '0123456789',
  '空',
  '元',
  'テーブルがありません。',
  '編集',
  '挿入',
  '行がありません',
  'You have no privileges to update this table.',
  '保存',
  '保存して継続',
  '保存／追加',
  '保存中',
  '削除',
  '言語',
  '使用',
  'Unknown error.',
  'データベース種類',
  'サーバ',
  'ユーザ名',
  'パスワード',
  'データベース',
  'ログイン',
  '永続的にログイン',
  'Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',
  'データ',
  '構造',
  'ビューを変更',
  'テーブルの変更',
  '項目の作成',
  'Warnings',
  '%d バイト',
  '列',
  '型',
  'コメント',
  '連番',
  '既定値',
  '選択',
  '関数',
  '集合',
  '検索',
  '任意',
  'ソート',
  '降順',
  '制約',
  '文字列の長さ',
  '動作',
  'Full table scan',
  'SQLコマンド',
  '開く',
  '保存',
  'データベースを変更',
  'スキーマ変更',
  'スキーマ追加',
  '構造',
  '権限',
  'インポート',
  'エクスポート',
  'テーブルを作成',
  'データベース',
  'DB',
  '選択',
  'Disable %s or enable %s or %s extensions.',
  '文字列',
  '数字',
  '日時',
  'リスト',
  'バイナリ',
  'ジオメトリ型',
  'ltr',
  'You are offline.',
  'ログアウト',
  'Too many unsuccessful logins, try again in %d minute(s).',
  'ログアウト',
  'Thanks for using Adminer, consider <a href="https://www.adminer.org/en/donation/">donating</a>.',
  'セッションの期限切れ。ログインし直してください',
  'Master password expired. <a href="https://www.adminer.org/en/extension/"%s>Implement</a> %s method to make it permanent.',
  'セッションを有効にしてください',
  'The action will be performed after successful login with the same credentials.',
  '拡張機能がありません',
  'PHPの拡張機能（%s）がセットアップされていません',
  'Connecting to privileged ports is not allowed.',
  '不正なログイン',
  'There is a space in the input password which might be the cause.',
  '不正なCSRFトークン。再送信してください',
  '定義可能な最大フィールド数を越えました。%s を増やしてください。',
  'If you did not send this request from Adminer then close this page.',
  'POSTデータが大きすぎます。データサイズを小さくするか %s 設定を大きくしてください',
  'You can upload a big SQL file via FTP and import it from server.',
  '外部キー',
  '照合順序',
  'ON UPDATE',
  'ON DELETE',
  '列名',
  '参数名',
  '長さ',
  '設定',
  '追加',
  '上',
  '下',
  '除外',
  '不正なデータベース',
  'データベースを削除しました',
  'データベースを選択してください',
  'データベースを作成',
  'プロセス一覧',
  '変数',
  '状態',
  '%sバージョン：%s、 PHP拡張機能 %s',
  'ログ：%s',
  'リフレッシュ',
  '照合順序',
  'テーブル',
  'サイズ',
  '算出',
  '選択済',
  '削除',
  'Materialized view',
  'ビュー',
  'テーブル',
  '索引',
  '索引の変更',
  'ソース',
  'ターゲット',
  '変更',
  '外部キーを追加',
  'トリガー',
  'トリガーの追加',
  'パーマネントリンク',
  '出力',
  '形式',
  'ルーチン',
  'イベント',
  'データ',
  'ユーザを作成',
  'ATTACH queries are not supported.',
  'クエリーのエラー',
  '%d / ',
  '%d 行',
  'クエリーを実行しました。%d 行を変更しました',
  '実行するコマンドがありません',
  '%d クエリーを実行しました',
  '実行',
  'Limit rows',
  'ファイルをアップロード',
  'ファイルのアップロードが無効です',
  'サーバーから実行',
  'Webサーバファイル %s',
  'ファイルを実行',
  'エラーの場合は停止',
  'エラーのみ表示',
  '履歴',
  '消去',
  'すべて編集',
  '項目を削除しました',
  '項目を更新しました',
  '%s項目を挿入しました',
  'テーブルを削除しました',
  'テーブルを変更しました',
  'テーブルを作成しました',
  'テーブル名',
  'エンジン',
  '規定値',
  'Drop %s?',
  'パーティション',
  'パーティション',
  'パーティション名',
  '値',
  '索引を変更しました',
  '索引の型',
  '列（長さ）',
  '名称',
  'データベースを削除しました',
  'データベースの名前を変えました',
  'データベースを作成しました',
  'データベースを変更しました',
  '呼出し',
  'ルーチンを呼びました。%d 行を変更しました',
  '外部キーを削除しました',
  '外部キーを変更しました',
  '外部キーを作成しました',
  'ソースとターゲットの列は同じデータ型でなければなりません。ターゲット列に索引があり、データが存在しなければなりません。',
  '外キー',
  'テーブル',
  'スキーマ',
  '変更',
  '列を追加',
  'ビューを変更しました',
  'ビューを削除しました',
  'ビューを作成しました',
  'ビューを作成',
  '削除しました',
  '変更しました',
  '作成しました',
  '変更',
  '作成',
  '開始',
  '終了',
  '毎回',
  '完成後に保存',
  'ルーチンを作成',
  'ルーチンを変更',
  'ルーチンを作成',
  '関数の変更',
  'プロシージャの変更',
  '関数の作成',
  'プロシージャの作成',
  '戻り値の型',
  'トリガーを削除しました',
  'トリガーを変更しました',
  'トリガーを追加しました',
  'トリガーの変更',
  'トリガーの作成',
  '時間',
  'イベント',
  'ユーザを削除',
  'ユーザを変更',
  'ユーザを作成',
  'Hashed',
  'ルーチン',
  '権限の付与',
  '権限の取消し',
  '%d プロセスを強制終了しました',
  'クローン',
  '合計 %d',
  '強制終了',
  '%d を更新しました',
  'Ctrl+click on a value to modify it.',
  'File must be in UTF-8 encoding.',
  '%d 行をインポートしました',
  'テーブルを選択できません',
  '修正',
  '関係',
  '編集',
  'リンクを編集する',
  '続きを読み込み',
  '読み込み中',
  'ページ',
  '最終',
  '全結果',
  'テーブルをtruncateしました',
  'テーブルを移動しました',
  'テーブルをコピーしました',
  'テーブルを削除しました',
  'Tables have been optimized.',
  'テーブルとビュー',
  'データを検索する',
  'エンジン',
  'データ長',
  '索引長',
  '空き',
  '行数',
  'Vacuum',
  '最適化',
  '分析',
  'チェック',
  '修復',
  '空にする',
  '別のデータベースへ移動',
  '移動',
  'コピー',
  'overwrite',
  'スケジュール',
  '指定時刻',
  'いいえ',
];
		case "ka": return [
  'ნამდვილად?',
  '%.3f s',
  'ფაილი არ აიტვირთა სერვერზე.',
  'ფაილის მაქსიმალური ზომა - %sB.',
  'ასეთი ფაილი არ არსებობს.',
  ' ',
  '0123456789',
  'ცარიელი',
  'საწყისი',
  'ბაზაში ცხრილი არაა.',
  'შეცვლა',
  'ჩასმა',
  'ჩანაწერი არაა.',
  'ამ ცხრილის განახლების უფლება არ გაქვთ.',
  'შენახვა',
  'შენახვა და ცვლილების გაგრძელება',
  'შენახვა და სხვის ჩასმა',
  'შენახვა',
  'წაშლა',
  'ენა',
  'არჩევა',
  'უცნობი შეცდომა.',
  'სისტემა',
  'სერვერი',
  'მომხმარებელი',
  'პაროლი',
  'ბაზა',
  'შესვლა',
  'სისტემაში დარჩენა',
  'უპაროლო წვდომა ბაზასთან არაა დაშვებული Adminer-ში, მეტი ინფორმაციისთვის ეწვიეთ <a href="https://www.adminer.org/en/password/"%s>ბმულს</a>.',
  'არჩევა',
  'სტრუქტურის ჩვენება',
  'წარმოდგენის შეცვლა',
  'ცხრილის შეცვლა',
  'ახალი ჩანაწერი',
  'გაფრთხილება',
  '%d ბაიტი',
  'ველი',
  'სახეობა',
  'კომენტარები',
  'ავტომატურად გაზრდა',
  'სტანდარტული მნიშვნელობა',
  'არჩევა',
  'ფუნქციები',
  'აგრეგაცია',
  'ძებნა',
  'ნებისმიერ ადგილას',
  'დალაგება',
  'კლებადობით',
  'ზღვარი',
  'ტექსტის სიგრძე',
  'მოქმედება',
  'სრული ცხრილის ანალიზი',
  'SQL-ბრძანება',
  'გახსნა',
  'შენახვა',
  'ბაზის შეცვლა',
  'სქემის შეცვლა',
  'ახალი სქემა',
  'ბაზის სქემა',
  'უფლებამოსილება',
  'იმპორტი',
  'ექსპორტი',
  'ცხრილის შექმნა',
  'ბაზა',
  'ბაზა',
  'არჩევა',
  'გათიშეთ %s ან ჩართეთ %s ან %s გაფართოება.',
  'ველები',
  'ციფრები',
  'დრო და თარიღი',
  'სია',
  'ორობითი',
  'გეომეტრია',
  'ltr',
  'არ გაგივლიათ ავტორიზაცია.',
  'გასვლა',
  'ძალიან ბევრჯერ შეგეშალათ მომხმარებელი და პაროლი. სცადეთ %d წუთში.',
  'გამოხვედით სისტემიდან.',
  'მადლობას გიხდით Adminer-ით სარგებლობისთვის, გადახედეთ ბმულს <a href="https://www.adminer.org/en/donation/">შემოწირულობა</a>.',
  'სესიის მოქმედების დრო ამოიწურა, გაიარეთ ხელახალი ავტორიზაცია.',
  'ძირითად პაროლს ვადა გაუვიდა. <a href="https://www.adminer.org/en/extension/"%s>გამოიყენეთ</a> მეთოდი %s, რათა ის მუდმივი გახადოთ.',
  'ჩართული უნდა იყოს სესია.',
  'მოქმედება შესრულდება იგივე მომხმარებლით წარმატებული ავტორიზაციის შემდეგ.',
  'გაფართოება არაა',
  'არც ერთი მხარდაჭერილი გაფართოება არ მოიძებნა (%s).',
  'პრივილეგირებულ პორტთან წვდომა დაუშვებელია.',
  'არასწორი მომხმარებელი ან პაროლი.',
  'პაროლში არის გამოტოვება, შეიძლება ეს ქმნის პრობლემას.',
  'უმოქმედო CSRF-ტოკენი. ფორმის კიდევ ერთხელ გაგზავნა.',
  'მიღწეულია დაშვებული ველების მაქსიმალური რაოდენობა, გაზარდეთ %s.',
  'ეს მოთხოვნა თქვენ თუ არ გაგიგზავნაით Adminer-იდან, დახურეთ ეს ფანჯარა..',
  'POST ინფორმაცია ძალიან დიდია. შეამცირეთ ზომა ან გაზარდეს POST ინფორმაციის ზომა პარამეტრებიდან %s.',
  'დიდი ფაილი უნდა ატვირტოთ FTP-თი და შემდეგ გაუკეთოთ იმპორტი სერვერიდან.',
  'გარე გასაღები',
  'კოდირება',
  'განახლებისას',
  'წაშლისას',
  'ველი',
  'პარამეტრი',
  'სიგრძე',
  'მოქმედება',
  'კიდევ დამატება',
  'ზემოთ ატანა',
  'ქვემოთ ჩატანა',
  'წაშლა',
  'არასწორი ბაზა.',
  'ბაზა წაიშალა.',
  'ბაზა',
  'ბაზის შექმნა',
  'პროცესების სია',
  'ცვლადები',
  'მდგომარეობა',
  'ვერსია %s: %s PHP-გაფართოება %s',
  'შესული ხართ როგორც: %s',
  'განახლება',
  'კოდირება',
  'ცხრილები',
  'ზომა',
  'გამოთვლა',
  'არჩეული',
  'წაშლა',
  'მატერიალური ხედი',
  'ნახვა',
  'ცხრილი',
  'ინდექსები',
  'ინდექსის შეცვლა',
  'წყარო',
  'სამიზნე',
  'შეცვლა',
  'გარე გასაღები დამატება',
  'ტრიგერები',
  'ტრიგერის დამატება',
  'მუდმივი ბმული',
  'გამომავალი ინფორმაცია',
  'ფორმატი',
  'რუტინები',
  'ღონისძიება',
  'ინფორმაცია',
  'მომხმარებლის შექმან',
  'ATTACH-მოთხოვნები არაა მხარდაჭერილი.',
  'შეცდომა მოთხოვნაში',
  '%d / ',
  '%d რიგი',
  'მოთხოვდა შესრულდა, შეიცვალა %d ჩანაწერი.',
  'შესასრულებელი ბრძანება არაა.',
  '%d მოთხოვნა შესრულდა.',
  'შესრულება',
  'რიგების შეზღუდვა',
  'ფაილის ატვირთვა სერვერზე',
  'ფაილის სერვერზე ატვირთვა გათიშულია.',
  'სერვერიდან',
  'ფაილი %s ვებსერვერზე',
  'ფაილის გაშვება',
  'გაჩერება შეცდომისას',
  'მხოლოდ შეცდომები',
  'ისტორია',
  'გასუფთავება',
  'ყველას შეცვლა',
  'ჩანაწერი წაიშალა.',
  'ჩანაწერი განახლდა.',
  'ჩანაწერი%s ჩაჯდა.',
  'ცხრილი წაიშალა.',
  'ცხრილი შეიცვალა.',
  'ცხრილი შეიქმნა.',
  'სახელი',
  'სახეობა',
  'სტანდარტული მნიშვნელობა',
  'წაიშალოს %s?',
  'დაყოფა',
  'დანაყოფები',
  'დანაყოფის სახელი',
  'პარამეტრები',
  'შეიცვალა ინდექსები.',
  'ინდექსის სახეობა',
  'ველი (სიგრძე)',
  'სახელი',
  'ბაზა წაიშალა.',
  'ბაზას გადაერქვა.',
  'ბაზა შეიქმნა.',
  'ბაზა შეიცვალა.',
  'გამოძახეება',
  'გამოძახებულია პროცედურა, შეიცვალა %d ჩანაწერი.',
  'გარე გასაღები წაიშალა.',
  'გარე გასაღები შეიცვალა.',
  'გარე გასაღები შეიქმნა.',
  'საწყისი და მიზნობრივი ველები უნდა იყოს ერთიდაიგივე სახეობის, მიზნობრივ ველზე უნდა იყოს ინდექსი და უნდა არსებობდეს შესაბამისი ინფორმაცია.',
  'გარე გასაღები',
  'მიზნობრივი ცხრილი',
  'სქემა',
  'შეცვლა',
  'ველის დამატება',
  'წარმოდგენა შეიცვალა.',
  'წარმოდგენა წაიშალა.',
  'წარმოდგენა შეიქმნა.',
  'წარმოდგენის შექმნა',
  'ღონისძიება წაიშალა.',
  'ღონისძიება შეიცვალა.',
  'ღონისძიება შეიქმნა.',
  'ღონისძიების შეცვლა',
  'ღონისძიების შექმნა',
  'დასაწყისი',
  'დასასრული',
  'ყოველ',
  'შენახვა დასრულებისას',
  'პროცედურა წაიშალა.',
  'პროცედურა შეიცვალა.',
  'პროცედურა შეიქმნა.',
  'ფუნქციის შეცვლა',
  'პროცედურის შეცვლა',
  'ფუნქციის შექმნა',
  'პროცედურის შექმნა',
  'დაბრუნების სახეობა',
  'ტრიგერი წაიშალა.',
  'ტრიგერი შეიცვალა.',
  'ტრიგერი შეიქმნა.',
  'ტრიგერის შეცვლა',
  'ტრიგერის შექმნა',
  'დრო',
  'ღონისძიება',
  'მომხმარებელი წაიშალა.',
  'მომხმარებელი შეიცვალა.',
  'მომხმარებელი შეიქმნა.',
  'ჰეშირებული',
  'პროცედურა',
  'დაშვება',
  'შეზღუდვა',
  'გაითიშა %d პროცესი.',
  'კლონირება',
  'სულ %d',
  'დასრულება',
  'შეიცვალა %d ჩანაწერი.',
  'შესაცვლელად გამოიყენეთ Ctrl+თაგვის ღილაკი.',
  'ფაილი უნდა იყოს კოდირებაში UTF-8.',
  'დაიმპორტდა %d რიგი.',
  'ცხრილიდან ინფორმაცია ვერ მოვიპოვე',
  'შეცვლა',
  'ურთიერთობა',
  'რედაქტირება',
  'ამ მნიშვნელობის შესაცვლელად გამოიყენეთ ბმული «შეცვლა».',
  'მეტი ინფორმაციის ჩატვირთვა',
  'ჩატვირთვა',
  'გვერდი',
  'ბოლო',
  'სრული შედეგი',
  'ცხრილი გასუფთავდა.',
  'ცხრილი გადაადგილდა.',
  'ცხრილი დაკოპირდა.',
  'ცხრილები წაიშალა.',
  'ცხრილებს გაუკეთდა ოპტიმიზაცია.',
  'ცხრილები და წარმოდგენები',
  'ცხრილებში ძებნა',
  'ძრავი',
  'ინფორმაციის მოცულობა',
  'ინდექსების მოცულობა',
  'თავისუფალი სივრცე',
  'რიგი',
  'ვაკუუმი',
  'ოპტიმიზაცია',
  'ანალიზი',
  'შემოწმება',
  'გასწორება',
  'გასუფთავება',
  'გადატანა სხვა ბაზაში',
  'გადატანა',
  'კოპირება',
  'overwrite',
  'განრიგი',
  'მოცემულ დროში',
  'ბაზაში არაა მხარდაჭერილი პაროლი.',
];
		case "ko": return [
  '실행 하시겠습니까?',
  '%.3f 초',
  '파일을 업로드 할 수 없습니다.',
  '파일의 최대 크기 %sB',
  '파일이 존재하지 않습니다.',
  ',',
  '0123456789',
  '비어있음',
  '원본',
  '테이블이 없습니다.',
  '편집',
  '삽입',
  '행이 없습니다.',
  '이 테이블을 업데이트할 권한이 없습니다.',
  '저장',
  '저장하고 계속 편집하기',
  '저장하고 다음에 추가',
  'Saving',
  '삭제',
  '언어',
  '사용',
  'Unknown error.',
  '데이터베이스 형식',
  '서버',
  '사용자이름',
  '비밀번호',
  '데이터베이스',
  '로그인',
  '영구적으로 로그인',
  'Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',
  '데이터를 선택하십시오.',
  '구조 표시',
  '보기 변경',
  '테이블 변경',
  '항목 만들기',
  '경고',
  '%d 바이트',
  '열',
  '형',
  '주석',
  '자동 증가',
  'Default value',
  '선택',
  '함수',
  '집합',
  '검색',
  '모든',
  '정렬',
  '역순',
  '제약',
  '문자열의 길이',
  '실행',
  'Full table scan',
  'SQL 명령',
  '열',
  '저장',
  '데이터베이스 변경',
  '스키마 변경',
  '스키마 추가',
  '데이터베이스 구조',
  '권한',
  '가져 오기',
  '내보내기',
  '테이블 만들기',
  '데이터베이스',
  'DB',
  '선택',
  'Disable %s or enable %s or %s extensions.',
  '문자열',
  '숫자',
  '시간',
  '목록',
  '이진',
  '기하 형',
  'ltr',
  '오프라인입니다.',
  '로그아웃',
  'Too many unsuccessful logins, try again in %d minute(s).',
  '로그아웃을 성공했습니다.',
  'Thanks for using Adminer, consider <a href="https://www.adminer.org/en/donation/">donating</a>.',
  '세션이 만료되었습니다. 다시 로그인하십시오.',
  'Master password expired. <a href="https://www.adminer.org/en/extension/"%s>Implement</a> %s method to make it permanent.',
  '세션 지원을 사용해야만 합니다.',
  'The action will be performed after successful login with the same credentials.',
  '확장이 없습니다.',
  'PHP 확장(%s)이 설치되어 있지 않습니다.',
  'Connecting to privileged ports is not allowed.',
  '잘못된 로그인',
  'There is a space in the input password which might be the cause.',
  '잘못된 CSRF 토큰입니다. 다시 보내주십시오.',
  '정의 가능한 최대 필드 수를 초과했습니다. %s(을)를 늘리십시오.',
  'If you did not send this request from Adminer then close this page.',
  'POST 데이터가 너무 큽니다. 데이터 크기를 줄이거나 %s 설정을 늘리십시오.',
  '큰 SQL 파일은 FTP를 통하여 업로드하여 서버에서 가져올 수 있습니다.',
  '외부 키',
  '정렬',
  '업데이트할 때',
  '지울 때',
  '열 이름',
  '매개변수 이름',
  '길이',
  '설정',
  '다음 추가',
  '위로',
  '아래로',
  '제거',
  '잘못된 데이터베이스입니다.',
  '데이터베이스를 삭제했습니다.',
  '데이터베이스를 선택하십시오.',
  '데이터베이스 만들기',
  '프로세스 목록',
  '변수',
  '상태',
  '%s 버전 %s, PHP 확장 %s',
  '다음으로 로그인했습니다: %s',
  '새로 고침',
  '정렬',
  '테이블',
  '크기',
  '계산하기',
  '선택됨',
  '삭제',
  'Materialized view',
  '보기',
  '테이블',
  '색인',
  '색인 변경',
  '소스',
  '타겟',
  '변경',
  '외부 키를 추가',
  '트리거',
  '트리거 추가',
  '영구적으로 링크',
  '출력',
  '형식',
  '루틴',
  '이벤트',
  '데이터',
  '사용자 만들기',
  'ATTACH queries are not supported.',
  '쿼리의 오류',
  '%d / ',
  '%d개 행',
  '쿼리를 잘 실행했습니다. %d행을 변경했습니다.',
  '실행할 수 있는 명령이 없습니다.',
  '%d개 쿼리를 잘 실행했습니다.',
  '실행',
  '행 제약',
  '파일 올리기',
  '파일 업로드가 잘못되었습니다.',
  '서버에서 실행',
  '웹서버 파일 %s',
  '파일을 실행',
  '오류의 경우 중지',
  '오류 만 표시',
  '이력',
  '삭제',
  '모두 편집',
  '항목을 삭제했습니다.',
  '항목을 갱신했습니다.',
  '%s 항목을 삽입했습니다.',
  '테이블을 삭제했습니다.',
  '테이블을 변경했습니다.',
  '테이블을 만들었습니다.',
  '테이블 이름',
  '엔진',
  '기본값',
  'Drop %s?',
  '파티션',
  '파티션',
  '파티션 이름',
  '값',
  '색인을 변경했습니다.',
  '색인 형',
  '열 (길이)',
  '이름',
  '데이터베이스를 삭제했습니다.',
  '데이터베이스의 이름을 바꾸었습니다.',
  '데이터베이스를 만들었습니다.',
  '데이터베이스를 변경했습니다.',
  '호출',
  '루틴을 호출했습니다. %d 행을 변경했습니다.',
  '외부 키를 제거했습니다.',
  '외부 키를 변경했습니다.',
  '외부 키를 만들었습니다.',
  '원본과 대상 열은 동일한 데이터 형식이어야만 합니다. 목표 열에 색인과 데이터가 존재해야만 합니다.',
  '외부 키',
  '테이블',
  '스키마',
  '변경',
  '열 추가',
  '보기를 변경했습니다.',
  '보기를 삭제했습니다.',
  '보기를 만들었습니다.',
  '뷰 만들기',
  '삭제했습니다.',
  '변경했습니다.',
  '만들었습니다.',
  '이벤트 변경',
  '만들기',
  '시작',
  '종료',
  '매 번',
  '완성 후 저장',
  '루틴을 제거했습니다.',
  '루틴을 변경했습니다.',
  '루틴을 추가했습니다.',
  '함수 변경',
  '시저 변경',
  '함수 만들기',
  '시저 만들기',
  '반환 형식',
  '트리거를 제거했습니다.',
  '트리거를 변경했습니다.',
  '트리거를 추가했습니다.',
  '트리거 변경',
  '트리거 만들기',
  '시간',
  '이벤트',
  '사용자를 제거했습니다.',
  '사용자를 변경했습니다.',
  '사용자를 만들었습니다.',
  'Hashed',
  '루틴',
  '권한 부여',
  '권한 취소',
  '%d개 프로세스를 강제 종료하였습니다.',
  '복제',
  '총 %d개',
  '강제 종료',
  '%d개 항목을 갱신했습니다.',
  'Ctrl+click on a value to modify it.',
  'File must be in UTF-8 encoding.',
  '%d개 행을 가져 왔습니다.',
  '테이블을 선택할 수 없습니다.',
  '수정',
  '관계',
  '편집',
  '이 값을 수정하려면 편집 링크를 사용하십시오.',
  '더 많은 데이터 부르기',
  '부르는 중',
  '페이지',
  '마지막',
  '모든 결과',
  '테이블의 데이터 내용만 지웠습니다.',
  '테이블을 옮겼습니다.',
  '테이블을 복사했습니다',
  '테이블을 삭제했습니다.',
  'Tables have been optimized.',
  '테이블과 뷰',
  '테이블 내 데이터 검색',
  '엔진',
  '데이터 길이',
  '색인 길이',
  '데이터 여유',
  '행',
  '청소',
  '최적화',
  '분석',
  '확인',
  '복구',
  '데이터 내용만 지우기',
  '다른 데이터베이스로 이동',
  '이동',
  '복사',
  '덮어쓰기',
  '예약',
  '지정 시간',
  '네',
];
		case "lt": return [
  'Tikrai?',
  '%.3f s',
  'Nepavyko įkelti failo.',
  'Maksimalus failo dydis - %sB.',
  'Failas neegzistuoja.',
  ' ',
  '0123456789',
  'tuščia',
  'originalas',
  'Nėra lentelių.',
  'Redaguoti',
  'Įrašyti',
  'Nėra įrašų.',
  'You have no privileges to update this table.',
  'Išsaugoti',
  'Išsaugoti ir tęsti redagavimą',
  'Išsaugoti ir įrašyti kitą',
  'Saving',
  'Trinti',
  'Kalba',
  'Naudoti',
  'Unknown error.',
  'Sistema',
  'Serveris',
  'Vartotojas',
  'Slaptažodis',
  'Duomenų bazė',
  'Prisijungti',
  'Pastovus prisijungimas',
  'Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',
  'Atrinkti duomenis',
  'Rodyti struktūrą',
  'Redaguoti vaizdą',
  'Redaguoti lentelę',
  'Naujas įrašas',
  'Warnings',
  [
    '%d baitas',
    '%d baigai',
    '%d baitų',
  ],
  'Stulpelis',
  'Tipas',
  'Komentaras',
  'Auto Increment',
  'Default value',
  'Atrinkti',
  'Funkcijos',
  'Agregacija',
  'Ieškoti',
  'visur',
  'Rikiuoti',
  'mažėjimo tvarka',
  'Limitas',
  'Teksto ilgis',
  'Veiksmas',
  'Full table scan',
  'SQL užklausa',
  'atidaryti',
  'išsaugoti',
  'Redaguoti duomenų bazę',
  'Keisti schemą',
  'Sukurti schemą',
  'Duomenų bazės schema',
  'Privilegijos',
  'Importas',
  'Eksportas',
  'Sukurti lentelę',
  'duomenų bazė',
  'DB',
  'atrinkti',
  'Disable %s or enable %s or %s extensions.',
  'Tekstas',
  'Skaičiai',
  'Data ir laikas',
  'Sąrašai',
  'Dvejetainis',
  'Geometrija',
  'ltr',
  'You are offline.',
  'Atsijungti',
  'Too many unsuccessful logins, try again in %d minute(s).',
  'Jūs atsijungėte nuo sistemos.',
  'Thanks for using Adminer, consider <a href="https://www.adminer.org/en/donation/">donating</a>.',
  'Sesijos galiojimas baigėsi. Prisijunkite iš naujo.',
  'Master password expired. <a href="https://www.adminer.org/en/extension/"%s>Implement</a> %s method to make it permanent.',
  'Sesijų palaikymas turi būti įjungtas.',
  'The action will be performed after successful login with the same credentials.',
  'Nėra plėtiio',
  'Nėra nei vieno iš palaikomų PHP plėtinių (%s).',
  'Connecting to privileged ports is not allowed.',
  'Neteisingi prisijungimo duomenys.',
  'There is a space in the input password which might be the cause.',
  'Neteisingas CSRF tokenas. Bandykite siųsti formos duomenis dar kartą.',
  'Viršytas maksimalus leidžiamų stulpelių kiekis. Padidinkite %s.',
  'If you did not send this request from Adminer then close this page.',
  'Per daug POST duomenų. Sumažinkite duomenų kiekį arba padidinkite konfigūracijos nustatymą %s.',
  'You can upload a big SQL file via FTP and import it from server.',
  'Išoriniai raktai',
  'palyginimas',
  'Atnaujinant',
  'Ištrinant',
  'Stulpelio pavadinimas',
  'Parametro pavadinimas',
  'Ilgis',
  'Nustatymai',
  'Pridėti kitą',
  'Perkelti į viršų',
  'Perkelti žemyn',
  'Pašalinti',
  'Neteisinga duomenų bazė.',
  'Duomenų bazės panaikintos.',
  'Pasirinkti duomenų bazę',
  'Sukurti duomenų bazę',
  'Procesų sąrašas',
  'Kintamieji',
  'Būsena',
  '%s versija: %s per PHP plėtinį %s',
  'Prisijungęs kaip: %s',
  'Atnaujinti',
  'Lyginimas',
  'Lentelės',
  'Size',
  'Compute',
  'Selected',
  'Pašalinti',
  'Materialized view',
  'Vaizdas',
  'Lentelė',
  'Indeksai',
  'Redaguoti indeksus',
  'Šaltinis',
  'Tikslas',
  'Redaguoti',
  'Pridėti išorinį raktą',
  'Trigeriai',
  'Pridėti trigerį',
  'Pastovi nuoroda',
  'Išvestis',
  'Formatas',
  'Procedūros',
  'Įvykiai',
  'Duomenys',
  'Sukurti vartotoją',
  'ATTACH queries are not supported.',
  'Klaida užklausoje',
  '%d / ',
  [
    '%d įrašas',
    '%d įrašai',
    '%d įrašų',
  ],
  [
    'Užklausa įvykdyta. Pakeistas %d įrašas.',
    'Užklausa įvykdyta. Pakeisti %d įrašai.',
    'Užklausa įvykdyta. Pakeista %d įrašų.',
  ],
  'Nėra vykdomų užklausų.',
  [
    '%d užklausa įvykdyta.',
    '%d užklausos įvykdytos.',
    '%d užklausų įvykdyta.',
  ],
  'Vykdyti',
  'Limit rows',
  'Failo įkėlimas',
  'Failų įkėlimas išjungtas.',
  'Iš serverio',
  'Failas %s iš serverio',
  'Vykdyti failą',
  'Sustabdyti esant klaidai',
  'Rodyti tik klaidas',
  'Istorija',
  'Išvalyti',
  'Redaguoti visus',
  'Įrašas ištrintas.',
  'Įrašas pakeistas.',
  'Įrašas%s sukurtas.',
  'Lentelė pašalinta.',
  'Lentelė pakeista.',
  'Lentelė sukurta.',
  'Lentelės pavadinimas',
  'variklis',
  'Reikšmės pagal nutylėjimą',
  'Drop %s?',
  'Skirstyti pagal',
  'Skirsniai',
  'Skirsnio pavadinimas',
  'Reikšmės',
  'Indeksai pakeisti.',
  'Indekso tipas',
  'Stulpelis (ilgis)',
  'Pavadinimas',
  'Duomenų bazė panaikinta.',
  'Duomenų bazė pervadinta.',
  'Duomenų bazė sukurta.',
  'Duomenų bazė pakeista.',
  'Vykdyti',
  [
    'Procedūra įvykdyta. %d įrašas pakeistas.',
    'Procedūra įvykdyta. %d įrašai pakeisti.',
    'Procedūra įvykdyta. %d įrašų pakeista.',
  ],
  'Išorinis raktas pašalintas.',
  'Išorinis raktas pakeistas.',
  'Išorinis raktas sukurtas.',
  'Šaltinio ir tikslinis stulpelis turi būti to paties tipo, tiksliniame stulpelyje turi būti naudojamas indeksas ir duomenys turi egzistuoti.',
  'Išorinis raktas',
  'Tikslinė lentelė',
  'Schema',
  'Pakeisti',
  'Pridėti stulpelį',
  'Vaizdas pakeistas.',
  'Vaizdas pašalintas.',
  'Vaizdas sukurtas.',
  'Sukurti vaizdą',
  'Įvykis pašalintas.',
  'Įvykis pakeistas.',
  'Įvykis sukurtas.',
  'Redaguoti įvykį',
  'Sukurti įvykį',
  'Pradžia',
  'Pabaiga',
  'Kas',
  'Įvykdžius išsaugoti',
  'Procedūra pašalinta.',
  'Procedūra pakeista.',
  'Procedūra sukurta.',
  'Keisti funkciją',
  'Keiskti procedūrą',
  'Sukurti funkciją',
  'Sukurti procedūrą',
  'Grąžinimo tipas',
  'Trigeris pašalintas.',
  'Trigeris pakeistas.',
  'Trigeris sukurtas.',
  'Keisti trigerį',
  'Sukurti trigerį',
  'Laikas',
  'Įvykis',
  'Vartotojas ištrintas.',
  'Vartotojo duomenys pakeisti.',
  'Vartotojas sukurtas.',
  'Šifruotas',
  'Procedūra',
  'Suteikti',
  'Atšaukti',
  [
    '%d procesas nutrauktas.',
    '%d procesai nutraukti.',
    '%d procesų nutraukta.',
  ],
  'Klonuoti',
  '%d iš viso',
  'Nutraukti',
  [
    'Pakeistas %d įrašas.',
    'Pakeisti %d įrašai.',
    'Pakeistas %d įrašų.',
  ],
  'Ctrl+click on a value to modify it.',
  'File must be in UTF-8 encoding.',
  [
    '%d įrašas įkelta.',
    '%d įrašai įkelti.',
    '%d įrašų įkelta.',
  ],
  'Neįmanoma atrinkti lentelės',
  'Modify',
  'Ryšiai',
  'redaguoti',
  'Norėdami redaguoti reikšmę naudokite redagavimo nuorodą.',
  'Load more data',
  'Loading',
  'Puslapis',
  'paskutinis',
  'Visas rezultatas',
  'Lentelės buvo ištuštintos.',
  'Lentelės perkeltos.',
  'Lentelės nukopijuotos.',
  'Lentelės pašalintos.',
  'Tables have been optimized.',
  'Lentelės ir vaizdai',
  'Ieškoti duomenų lentelėse',
  'Variklis',
  'Duomenų ilgis',
  'Indekso ilgis',
  'Laisvos vietos',
  'Įrašai',
  'Vacuum',
  'Optimizuoti',
  'Analizuoti',
  'Patikrinti',
  'Pataisyti',
  'Tuštinti',
  'Perkelti į kitą duomenų bazę',
  'Perkelti',
  'Kopijuoti',
  'overwrite',
  'Grafikas',
  'Nurodytu laiku',
  'Keisti tipą',
];
		case "ms": return [
  'Anda pasti?',
  '%.3f s',
  'Muat naik fail gagal.',
  'Saiz fail maksimum yang dibenarkan adalah %sB.',
  'Fail tidak wujud.',
  ',',
  '0123456789',
  'kosong',
  'asli',
  'Tiada jadual.',
  'Ubah',
  'Masukkan',
  'Tiada baris.',
  'Anda tidak mempunyai keistimewaan untuk mengemaskini jadual ini.',
  'Simpan',
  'Simpan dan sambung ubah',
  'Simpan dan masukkan seterusnya',
  'Menyimpan',
  'Padam',
  'Bahasa',
  'Guna',
  'Unknown error.',
  'Sistem',
  'Pelayan',
  'Nama pengguna',
  'Kata laluan',
  'Pangkalan data',
  'Log masuk',
  'Log masuk kekal',
  'Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',
  'Pilih data',
  'Paparkan struktur',
  'Ubah paparan',
  'Ubah jadual',
  'Item baru',
  'Warnings',
  [
    '%d byte',
    '%d bytes',
  ],
  'Kolum',
  'Jenis',
  'Komen',
  'Kenaikan Auto',
  'Nilai lalai',
  'Pilih',
  'Fungsi',
  'Pengagregatan',
  'Cari',
  'di mana-mana',
  'Susun',
  'menurun',
  'Had',
  'Kepanjangan teks',
  'Aksi',
  'Imbasan penuh jadual',
  'Arahan SQL',
  'buka',
  'simpan',
  'Ubah pangkalan data',
  'Ubah skema',
  'Buat skema',
  'Skema pangkalan data',
  'Keistimewaan',
  'Import',
  'Eksport',
  'Bina jadual',
  'pangkalan data',
  'DB',
  'pilih',
  'Disable %s or enable %s or %s extensions.',
  'String',
  'Nombor',
  'Tarikh dan masa',
  'Senarai',
  'Binari',
  'Geometri',
  'ltr',
  'Anda sedang offline.',
  'Log keluar',
  'Terlalu banyak percubaan log masuk yang gagal, sila cuba lagi dalam masa %d minit.',
  'Log keluar berjaya.',
  'Terima kasih kerana menggunakan Adminer, pertimbangkan untuk <a href="https://www.adminer.org/en/donation/">menderma</a>.',
  'Sesi telah luput, sila log masuk kembali.',
  'Kata laluan utama telah luput. <a href="https://www.adminer.org/en/extension/"%s>Gunakan</a> cara %s untuk mengekalkannya.',
  'Sokongan sesi perlu diaktifkan.',
  'The action will be performed after successful login with the same credentials.',
  'Tiada sambungan',
  'Sambungan PHP yang (%s) disokong tidak wujud.',
  'Penyambungan ke port yang istimewa tidak dibenarkan.',
  'Akses tidak sah.',
  'There is a space in the input password which might be the cause.',
  'Token CSRF tidak sah. Sila hantar borang sekali lagi.',
  'Bilangan medan telah melebihi had yang dibenarkan. Sila tingkatkan %s.',
  'Jika anda tidak menghantar permintaan ini dari Adminer sila tutup halaman ini.',
  'Data POST terlalu besar. Kecilkan data atau tingkatkan tetapan %s.',
  'Anda boleh muat naik fail SQL yang besar melalui FTP dan import melalui pelayan.',
  'Kunci asing',
  'collation',
  'ON UPDATE',
  'ON DELETE',
  'Nama kolum',
  'Nama pembolehubah',
  'Kepanjangan',
  'Pilihan',
  'Tambah yang seterusnya',
  'Gerak ke atas',
  'Gerak ke bawah',
  'Buang',
  'Pangkalan data tidak sah.',
  'Pangkalan data telah dijatuhkan.',
  'Pilih pangkalan data',
  'Bina pangkalan data',
  'Senarai proses',
  'Pembolehubah',
  'Status',
  'Versi %s: %s melalui sambungan PHP %s',
  'Log masuk sebagai: %s',
  'Segar kembali',
  'Collation',
  'Jadual',
  'Saiz',
  'Kira',
  'Terpilih',
  'Jatuh',
  'Paparan yang menjadi kenyataan',
  'Papar',
  'Jadual',
  'Indeks',
  'Ubah indeks',
  'Sumber',
  'Sasaran',
  'Ubah',
  'Tambah kunci asing',
  ' Pencetus',
  'Tambah pencetus',
  'Pautan kekal',
  'Pengeluaran',
  'Format',
  'Rutin',
  'Peristiwa',
  'Data',
  'Bina pengguna',
  'Query berikut tidak disokong.',
  'Ralat pada query',
  '%d / ',
  '%d baris',
  'Query berjaya dilaksanakan, %d baris terjejas.',
  'Tiada arahan untuk dilaksanakan.',
  '%d query berjaya dilaksanakan.',
  'Laksana',
  'Had baris',
  'Muat naik fail',
  'Muat naik fail dihalang.',
  'Dari pelayan',
  'Fail pelayan sesawang %s',
  'Jalankan fail',
  'Berhenti jika ralat',
  'Paparkan jika ralat',
  'Sejarah',
  'Bersih',
  'Ubah semua',
  'Item telah dipadamkan.',
  'Item telah dikemaskini.',
  'Item%s telah dimasukkan.',
  'Jadual telah dijatuhkan.',
  'Jadual telah diubah.',
  'Jadual telah dibuat.',
  'Nama jadual',
  'enjin',
  'Nilai lalai',
  'Jatuhkan %s?',
  'Partition mengikut',
  'Partition',
  'Nama partition',
  'Nilai',
  'Indeks telah diubah.',
  'Jenis Indeks',
  'Kolum (kepanjangan)',
  'Nama',
  'Pangkalan data telah dijatuhkan.',
  'Pangkalan data telah ditukar nama.',
  'Pangkalan data telah dibuat.',
  'Pangkalan data telah diubah.',
  'Panggil',
  'Rutin telah dipanggil, %d baris terjejas.',
  'Kunci asing telah dijatuhkan.',
  'Kunci asing telah diubah.',
  'Kunci asing telah dibuat.',
  'Kolum sumber dan sasaran perlu mempunyai jenis data yang sama, indeks diperlukan pada kolum sasaran dan data yang dirujuk wujud.',
  'Kunci asing',
  'Jadual sasaran',
  'Skema',
  'Tukar',
  'Tambah kolum',
  'Paparan telah diubah.',
  'Paparan telah dijatuhkan.',
  'Paparan telah dibuat.',
  'Bina paparan',
  'Peristiwa telah dijatuhkan.',
  'Peristiwa telah diubah.',
  'Peristiwa telah dibuat.',
  'Ubah peristiwa',
  'Bina peristiwa',
  'Mula',
  'Habis',
  'Setiap',
  'Dalam melestarikan penyelesaian',
  'Rutin telah dijatuhkan.',
  'Rutin telah diubah.',
  'Rutin telah dibuat.',
  'Ubah fungsi',
  'Ubah prosedur',
  'Bina fungsi',
  'Bina prosedur',
  'Jenis Return',
  'Pencetus telah dijatuhkan.',
  'Pencetus telah diubah.',
  'Pencetus telah dibuat.',
  'Ubah pencetus',
  'Buat pencetus',
  'Masa',
  'Peristiwa',
  'Pengguna telah dijatuhkan.',
  'Pengguna telah diubah.',
  'Pengguna telah dibuat.',
  'Hashed',
  'Rutin',
  'Beri',
  'Batal',
  '%d proses telah dihentikan.',
  'Klon',
  '%d secara keseluruhan',
  'Henti',
  '%d item telah terjejas.',
  'Ctrl+click pada nilai untuk meminda.',
  'Fail mesti dalam pengekodan UTF-8.',
  '%d baris telah diimport.',
  'Pemilihan jadual tidak berjaya',
  'Pinda',
  'Hubungan',
  'ubah',
  'Guna pautan ubah untuk meminda nilai ini.',
  'Load lebih data',
  'Loading',
  'Halaman',
  'akhir',
  'Keputusan keseluruhan',
  'Jadual telah dimangkaskan.',
  'Jadual telah dipindahkan.',
  'Jadual telah disalin.',
  'Jadual telah dijatuhkan.',
  'Jadual telah dioptimumkan.',
  'Jadual dan pandangan',
  'Cari data dalam jadual',
  'Enjin',
  'Panjang Data',
  'Panjang Indeks',
  'Data Free',
  'Baris',
  'Vacuum',
  'Mengoptimum',
  'Menganalisis',
  'Periksa',
  'Baiki',
  'Memangkas',
  'Pindahkan ke pangkalan data yang lain',
  'Pindah',
  'Salin',
  'overwrite',
  'Jadual',
  'Pada masa tersebut',
  'Ubah jenis',
];
		case "nl": return [
  'Weet u het zeker?',
  '%.3f s',
  'Onmogelijk bestand te uploaden.',
  'Maximum toegelaten bestandsgrootte is %sB.',
  'Bestand niet gevonden.',
  '.',
  '0123456789',
  'leeg',
  'origineel',
  'Geen tabellen.',
  'Bewerk',
  'Toevoegen',
  'Geen rijen.',
  'You have no privileges to update this table.',
  'Opslaan',
  'Opslaan en verder bewerken',
  'Opslaan, daarna toevoegen',
  'Saving',
  'Verwijderen',
  'Taal',
  'Gebruik',
  'Unknown error.',
  'Databasesysteem',
  'Server',
  'Gebruikersnaam',
  'Wachtwoord',
  'Database',
  'Inloggen',
  'Blijf aangemeld',
  'Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',
  'Gegevens selecteren',
  'Toon structuur',
  'View aanpassen',
  'Tabel aanpassen',
  'Nieuw item',
  'Warnings',
  [
    '%d byte',
    '%d bytes',
  ],
  'Kolom',
  'Type',
  'Commentaar',
  'Auto nummering',
  'Default value',
  'Kies',
  'Functies',
  'Totalen',
  'Zoeken',
  'overal',
  'Sorteren',
  'Aflopend',
  'Beperk',
  'Tekst lengte',
  'Acties',
  'Full table scan',
  'SQL opdracht',
  'openen',
  'opslaan',
  'Database aanpassen',
  'Schema wijzigen',
  'Schema maken',
  'Database schema',
  'Rechten',
  'Importeren',
  'Exporteren',
  'Tabel aanmaken',
  'database',
  'DB',
  'kies',
  'Disable %s or enable %s or %s extensions.',
  'Tekst',
  'Getallen',
  'Datum en tijd',
  'Lijsten',
  'Binaire gegevens',
  'Geometrie',
  'ltr',
  'You are offline.',
  'Uitloggen',
  'Too many unsuccessful logins, try again in %d minute(s).',
  'Uitloggen geslaagd.',
  'Thanks for using Adminer, consider <a href="https://www.adminer.org/en/donation/">donating</a>.',
  'Uw sessie is verlopen. Gelieve opnieuw in te loggen.',
  'Master password expired. <a href="https://www.adminer.org/en/extension/"%s>Implement</a> %s method to make it permanent.',
  'Sessies moeten geactiveerd zijn.',
  'The action will be performed after successful login with the same credentials.',
  'Geen extensie',
  'Geen geldige PHP extensies beschikbaar (%s).',
  'Connecting to privileged ports is not allowed.',
  'Ongeldige logingegevens.',
  'There is a space in the input password which might be the cause.',
  'Ongeldig CSRF token. Verstuur het formulier opnieuw.',
  'Maximum aantal velden bereikt. Verhoog %s.',
  'If you did not send this request from Adminer then close this page.',
  'POST-data is te groot. Verklein de hoeveelheid data of verhoog de %s configuratie.',
  'You can upload a big SQL file via FTP and import it from server.',
  'Foreign keys',
  'collation',
  'ON UPDATE',
  'ON DELETE',
  'Kolomnaam',
  'Parameternaam',
  'Lengte',
  'Opties',
  'Volgende toevoegen',
  'Omhoog',
  'Omlaag',
  'Verwijderen',
  'Ongeldige database.',
  'Databases verwijderd.',
  'Database selecteren',
  'Database aanmaken',
  'Proceslijst',
  'Variabelen',
  'Status',
  '%s versie: %s met PHP extensie %s',
  'Aangemeld als: %s',
  'Vernieuwen',
  'Collatie',
  'Tabellen',
  'Size',
  'Compute',
  'Selected',
  'Verwijderen',
  'Materialized view',
  'View',
  'Tabel',
  'Indexen',
  'Indexen aanpassen',
  'Bron',
  'Doel',
  'Aanpassen',
  'Foreign key aanmaken',
  'Triggers',
  'Trigger aanmaken',
  'Permanente link',
  'Uitvoer',
  'Formaat',
  'Procedures',
  'Events',
  'Data',
  'Gebruiker aanmaken',
  'ATTACH queries are not supported.',
  'Fout in query',
  '%d / ',
  [
    '%d rij',
    '%d rijen',
  ],
  [
    'Query uitgevoerd, %d rij geraakt.',
    'Query uitgevoerd, %d rijen beïnvloed.',
  ],
  'Geen opdrachten uit te voeren.',
  [
    '%d query succesvol uitgevoerd.',
    '%d querys succesvol uitgevoerd',
  ],
  'Uitvoeren',
  'Limit rows',
  'Bestand uploaden',
  'Bestanden uploaden is uitgeschakeld.',
  'Van server',
  'Webserver bestand %s',
  'Bestand uitvoeren',
  'Stoppen bij fout',
  'Enkel fouten tonen',
  'Geschiedenis',
  'Wissen',
  'Alles bewerken',
  'Item verwijderd.',
  'Item aangepast.',
  'Item%s toegevoegd.',
  'Tabel verwijderd.',
  'Tabel aangepast.',
  'Tabel aangemaakt.',
  'Tabelnaam',
  'engine',
  'Standaard waarden',
  'Drop %s?',
  'Partitioneren op',
  'Partities',
  'Partitie naam',
  'Waarden',
  'Index aangepast.',
  'Index type',
  'Kolom (lengte)',
  'Naam',
  'Database verwijderd.',
  'Database hernoemd.',
  'Database aangemaakt.',
  'Database aangepast.',
  'Uitvoeren',
  [
    'Procedure uitgevoerd, %d rij geraakt.',
    'Procedure uitgevoerd, %d rijen geraakt.',
  ],
  'Foreign key verwijderd.',
  'Foreign key aangepast.',
  'Foreign key aangemaakt.',
  'Bron- en doelkolommen moeten van hetzelfde data type zijn, er moet een index bestaan op de gekozen kolommen en er moet gerelateerde data bestaan.',
  'Foreign key',
  'Doeltabel',
  'Schema',
  'Veranderen',
  'Kolom toevoegen',
  'View aangepast.',
  'View verwijderd.',
  'View aangemaakt.',
  'View aanmaken',
  'Event werd verwijderd.',
  'Event werd aangepast.',
  'Event werd aangemaakt.',
  'Event aanpassen',
  'Event aanmaken',
  'Start',
  'Stop',
  'Iedere',
  'Bewaren na voltooiing',
  'Procedure verwijderd.',
  'Procedure aangepast.',
  'Procedure aangemaakt.',
  'Functie aanpassen',
  'Procedure aanpassen',
  'Functie aanmaken',
  'Procedure aanmaken',
  'Return type',
  'Trigger verwijderd.',
  'Trigger aangepast.',
  'Trigger aangemaakt.',
  'Trigger aanpassen',
  'Trigger aanmaken',
  'Time',
  'Event',
  'Gebruiker verwijderd.',
  'Gebruiker aangepast.',
  'Gebruiker aangemaakt.',
  'Gehashed',
  'Routine',
  'Toekennen',
  'Intrekken',
  [
    '%d proces gestopt.',
    '%d processen gestopt.',
  ],
  'Dupliceer',
  '%d in totaal',
  'Stoppen',
  [
    '%d item aangepast.',
    '%d items aangepast.',
  ],
  'Ctrl+klik op een waarde om deze te bewerken.',
  'File must be in UTF-8 encoding.',
  [
    '%d rij werd geïmporteerd.',
    '%d rijen werden geïmporteerd.',
  ],
  'Onmogelijk tabel te selecteren',
  'Modify',
  'Relaties',
  'bewerk',
  'Gebruik de link "bewerk" om deze waarde te wijzigen.',
  'Load more data',
  'Loading',
  'Pagina',
  'laatste',
  'Volledig resultaat',
  'Tabellen werden geleegd.',
  'Tabellen werden verplaatst.',
  'De tabellen zijn gekopieerd.',
  'Tabellen werden verwijderd.',
  'Tables have been optimized.',
  'Tabellen en views',
  'Zoeken in database',
  'Engine',
  'Data lengte',
  'Index lengte',
  'Data Vrij',
  'Rijen',
  'Vacuum',
  'Optimaliseer',
  'Analyseer',
  'Controleer',
  'Herstel',
  'Legen',
  'Verplaats naar andere database',
  'Verplaats',
  'Kopieren',
  'overwrite',
  'Schedule',
  'Op aangegeven tijd',
  'HH:MM:SS',
];
		case "no": return [
  'Er du sikker?',
  '%.3f s',
  'Kunne ikke laste opp fil.',
  'Maksimum tillatte filstørrelse er %sB.',
  'Filen eksisterer ikke.',
  ' ',
  '0123456789',
  'tom',
  'original',
  'Ingen tabeller.',
  'Rediger',
  'Sett inn',
  'Ingen rader.',
  'Du mangler rettighetene som trengs for å endre denne tabellen.',
  'Lagre',
  'Lagre og fortsett å redigere',
  'Lagre og sett inn neste',
  'Lagrer',
  'Slett',
  'Språk',
  'Bruk',
  'Unknown error.',
  'System',
  'Server',
  'Brukernavn',
  'Passord',
  'Database',
  'Logg inn',
  'Permanent login',
  'Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',
  'Velg data',
  'Vis struktur',
  'Endre view',
  'Endre tabell',
  'Ny rad',
  'Warnings',
  [
    '%d byte',
    '%d bytes',
  ],
  'Kolonne',
  'Type',
  'Kommentarer',
  'Autoinkrement',
  'Default value',
  'Velg',
  'Funksjoner',
  'Sammenfatning',
  'Søk',
  'hvorsomhelst',
  'Sorter',
  'minkende',
  'Skranke',
  'Tekstlengde',
  'Handling',
  'Full tabell-scan',
  'SQL-kommando',
  'åpne',
  'lagre',
  'Endre database',
  'Endre skjema',
  'Opprett skjema',
  'Databaseskjema',
  'Privilegier',
  'Importer',
  'Eksport',
  'Opprett tabell',
  'database',
  'DB',
  'Vis',
  'Disable %s or enable %s or %s extensions.',
  'Strenger',
  'Nummer',
  'Dato og tid',
  'Lister',
  'Binære',
  'Geometri',
  'venstre-til-høyre',
  'You are offline.',
  'Logg ut',
  'Too many unsuccessful logins, try again in %d minute(s).',
  'Utlogging vellykket.',
  'Thanks for using Adminer, consider <a href="https://www.adminer.org/en/donation/">donating</a>.',
  'Økt utløpt - vennligst logg inn på nytt.',
  'Master-passord er utløpt. <a href="https://www.adminer.org/en/extension/"%s>Implementer</a> en metode for %s for å gjøre det permanent.',
  'Økt-støtte må være skrudd på.',
  'The action will be performed after successful login with the same credentials.',
  'Ingen utvidelse',
  'Ingen av de støttede PHP-utvidelsene (%s) er tilgjengelige.',
  'Connecting to privileged ports is not allowed.',
  'Ugylding innloggingsinformasjon.',
  'There is a space in the input password which might be the cause.',
  'Ugylding CSRF-token - Send inn skjemaet igjen.',
  'Maksimum antall feltnavn overskredet - venligst øk %s.',
  'If you did not send this request from Adminer then close this page.',
  'For stor datamengde i skjemaet. Reduser datamengden, eller øk størrelsen på %s-konfigurasjonsdirektivet.',
  'Du kan laste opp en stor SQL-fil via FTP og importere den fra serveren.',
  'Fremmednøkler',
  'sortering',
  'ON UPDATE',
  'ON DELETE',
  'Kolonnenavn',
  'Parameternavn',
  'Lengde',
  'Valg',
  'Legg til neste',
  'Flytt opp',
  'Flytt ned',
  'Fjern',
  'Ugyldig database.',
  'Databasene har blitt slettet.',
  'Velg database',
  'Opprett database',
  'Prosessliste',
  'Variabler',
  'Status',
  '%s versjon: %s via PHP-utvidelse %s',
  'Logget inn som: %s',
  'Gjenoppfrisk',
  'Tekstsortering',
  'Tabeller',
  'Size',
  'Compute',
  'Valgt',
  'Dropp',
  'Materialized view',
  'View',
  'Tabell',
  'Indekser',
  'Endre indekser',
  'Kilde',
  'Mål',
  'Endre',
  'Legg til fremmednøkkel',
  'Triggere',
  'Legg til trigger',
  'Permanent lenke',
  'Resultat',
  'Format',
  'Rutiner',
  'Eventer',
  'Data',
  'Lag bruker',
  'ATTACH queries are not supported.',
  'Feil i forespørsel',
  '%d / ',
  [
    '%d rad',
    '%d rader',
  ],
  [
    'Kall utført OK, %d rad påvirket.',
    'Kall utført OK, %d rader påvirket.',
  ],
  'Ingen kommandoer å utføre.',
  [
    '%d kall utført OK.',
    '%d kall utført OK.',
  ],
  'Kjør',
  'Limit rows',
  'Filopplasting',
  'Filopplastinger ikke tillatt.',
  'Fra server',
  'Webserver-fil %s',
  'Kjør fil',
  'Stopp ved feil',
  'Vis bare feil',
  'Historie',
  'Tøm skjema',
  'Rediger alle',
  'Raden er slettet.',
  'Raden er oppdatert.',
  'Rad%s er satt inn.',
  'Tabellen er slettet.',
  'Tabellen er endret.',
  'Tabellen er opprettet.',
  'Tabellnavn',
  'mottor',
  'Standardverdier',
  'Drop %s?',
  'Partisjoner ved',
  'Partisjoner',
  'Partisjonsnavn',
  'Verdier',
  'Indeksene er endret.',
  'Indekstype',
  'Kolonne (lengde)',
  'Navn',
  'Databasen har blitt slettet.',
  'Databasen har fått nytt navn.',
  'Databasen er opprettet.',
  'Databasen er endret.',
  'Kall',
  [
    'Rutinen er utført, %d rad påvirket.',
    'Rutinen er utført, %d rader påvirket.',
  ],
  'Fremmednøkkelen er slettet.',
  'Fremmednøkkelen er endret.',
  'Fremmednøkkelen er opprettet.',
  'Kilde- og mål-kolonner må ha samme datatype, det må være en indeks på mål-kolonnen, og dataene som refereres til må eksistere.',
  'Fremmednøkkel',
  'Måltabell',
  'Skjema',
  'Endre',
  'Legg til kolonne',
  'Viewet er endret.',
  'Viewet er slettet.',
  'Viewet er opprettet.',
  'Lag nytt view',
  'Eventen er slettet.',
  'Eventen er endret.',
  'Eventen er opprettet.',
  'Endre event',
  'Opprett event',
  'Start',
  'Slutt',
  'Hver',
  'Ved fullførelse bevar',
  'Rutinen er slettet.',
  'Rutinen er endret.',
  'Rutinen er opprettet.',
  'Endre funksjon',
  'Endre prosedyre',
  'Opprett funksjon',
  'Opprett prosedyre',
  'Returtype',
  'Triggeren er slettet.',
  'Triggeren er endret.',
  'Triggeren er opprettet.',
  'Endre trigger',
  'Opprett trigger',
  'Tid',
  'Hendelse',
  'Bruker slettet.',
  'Bruker endret.',
  'Bruker opprettet.',
  'Hashet',
  'Rutine',
  'Gi privilegier',
  'Trekk tilbake',
  [
    '%d prosess avsluttet.',
    '%d prosesser avsluttet.',
  ],
  'Klon',
  '%d totalt',
  'Avslutt',
  [
    '%d rad påvirket.',
    '%d rader påvirket.',
  ],
  'Ctrl+klikk på en verdi for å endre den.',
  'Filen må være i UTF8-tegnkoding.',
  [
    '%d rad er importert.',
    '%d rader er importert.',
  ],
  'Kan ikke velge tabellen',
  'Endre',
  'Relasjoner',
  'rediger',
  'Bruk rediger-lengde for å endre dennne verdien.',
  'Last mer data',
  'Laster',
  'Side',
  'siste',
  'Hele resultatet',
  'Tabellene har blitt avkortet.',
  'Tabellene har blitt flyttet.',
  'Tabellene har blitt kopiert.',
  'Tabellene er slettet.',
  'Tabellene er blitt optimalisert.',
  'Tabeller og views',
  'Søk data i tabeller',
  'Motor',
  'Datalengde',
  'Indekslengde',
  'Frie data',
  'Rader',
  'Støvsug',
  'Optimaliser',
  'Analyser',
  'Sjekk',
  'Reparer',
  'Avkort',
  'Flytt til annen database',
  'Flytt',
  'Kopier',
  'overwrite',
  'Tidsplan',
  'På gitte tid',
  'Endre type',
];
		case "pl": return [
  'Czy jesteś pewien?',
  '%.3f s',
  'Wgranie pliku było niemożliwe.',
  'Maksymalna wielkość pliku to %sB.',
  'Plik nie istnieje.',
  ' ',
  '0123456789',
  'puste',
  'bez zmian',
  'Brak tabel.',
  'Edytuj',
  'Dodaj',
  'Brak rekordów.',
  'Brak uprawnień do edycji tej tabeli',
  'Zapisz zmiany',
  'Zapisz i kontynuuj edycję',
  'Zapisz i dodaj następny',
  'Zapisywanie',
  'Usuń',
  'Język',
  'Wybierz',
  'Unknown error.',
  'Rodzaj bazy',
  'Serwer',
  'Użytkownik',
  'Hasło',
  'Baza danych',
  'Zaloguj się',
  'Zapamiętaj sesję',
  'Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',
  'Pokaż dane',
  'Struktura tabeli',
  'Zmień perspektywę',
  'Zmień tabelę',
  'Nowy rekord',
  'Warnings',
  [
    '%d bajt',
    '%d bajty',
    '%d bajtów',
  ],
  'Kolumna',
  'Typ',
  'Komentarz',
  'Auto Increment',
  'Wartość domyślna',
  'pokaż',
  'Funkcje',
  'Agregacje',
  'Szukaj',
  'gdziekolwiek',
  'Sortuj',
  'malejąco',
  'Limit',
  'Długość tekstu',
  'Czynność',
  'Wymaga pełnego przeskanowania tabeli',
  'Zapytanie SQL',
  'otwórz',
  'zapisz',
  'Zmień bazę danych',
  'Zmień schemat',
  'Utwórz schemat',
  'Schemat bazy danych',
  'Uprawnienia użytkowników',
  'Import',
  'Eksport',
  'Utwórz tabelę',
  'baza danych',
  'DB',
  'przeglądaj',
  'Disable %s or enable %s or %s extensions.',
  'Tekstowe',
  'Numeryczne',
  'Data i czas',
  'Listy',
  'Binarne',
  'Geometria',
  'ltr',
  'Jesteś offline.',
  'Wyloguj',
  [
    'Za dużo nieudanych prób logowania, spróbuj ponownie za %d minutę.',
    'Za dużo nieudanych prób logowania, spróbuj ponownie za %d minuty.',
    'Za dużo nieudanych prób logowania, spróbuj ponownie za %d minut.',
  ],
  'Wylogowano pomyślnie.',
  'Dziękujemy za używanie Adminera, rozważ proszę <a href="https://www.adminer.org/pl/donation/">dotację</a>.',
  'Sesja wygasła, zaloguj się ponownie.',
  'Ważność hasła głównego wygasła. <a href="https://www.adminer.org/pl/extension/"%s>Zaimplementuj</a> własną metodę %s, aby ustawić je na stałe.',
  'Wymagana jest obsługa sesji w PHP.',
  'The action will be performed after successful login with the same credentials.',
  'Brak rozszerzenia',
  'Żadne z rozszerzeń PHP umożliwiających połączenie się z bazą danych (%s) nie jest dostępne.',
  'Łączenie do portów uprzywilejowanych jest niedozwolone.',
  'Nieprawidłowe dane logowania.',
  'There is a space in the input password which might be the cause.',
  'Nieprawidłowy token CSRF. Spróbuj wysłać formularz ponownie.',
  'Przekroczono maksymalną liczbę pól. Zwiększ %s.',
  'Jeżeli nie wywołałeś tej strony z Adminera, zamknij to okno.',
  'Przesłano zbyt dużo danych. Zmniejsz objętość danych lub zwiększ zmienną konfiguracyjną %s.',
  'Większe pliki SQL możesz wgrać na serwer poprzez FTP przed zaimportowaniem.',
  'Klucze obce',
  'porównywanie znaków',
  'W przypadku zmiany',
  'W przypadku usunięcia',
  'Nazwa kolumny',
  'Nazwa parametru',
  'Długość',
  'Opcje',
  'Dodaj następny',
  'Przesuń w górę',
  'Przesuń w dół',
  'Usuń',
  'Nie znaleziono bazy danych.',
  'Bazy danych zostały usunięte.',
  'Wybierz bazę danych',
  'Utwórz bazę danych',
  'Lista procesów',
  'Zmienne',
  'Status',
  'Wersja %s: %s za pomocą %s',
  'Zalogowany jako: %s',
  'Odśwież',
  'Porównywanie znaków',
  'Tabele',
  'Wielkość',
  'Oblicz',
  'Zaznaczone',
  'Usuń',
  'Zmaterializowana perspektywa',
  'Perspektywa',
  'Tabela',
  'Indeksy',
  'Zmień indeksy',
  'Źródło',
  'Cel',
  'Zmień',
  'Dodaj klucz obcy',
  'Wyzwalacze',
  'Dodaj wyzwalacz',
  'Trwały link',
  'Rezultat',
  'Format',
  'Procedury i funkcje',
  'Wydarzenia',
  'Dane',
  'Dodaj użytkownika',
  'Zapytania ATTACH są niewspierane.',
  'Błąd w zapytaniu',
  '%d / ',
  [
    '%d rekord',
    '%d rekordy',
    '%d rekordów',
  ],
  [
    'Zapytanie wykonane pomyślnie, zmieniono %d rekord.',
    'Zapytanie wykonane pomyślnie, zmieniono %d rekordy.',
    'Zapytanie wykonane pomyślnie, zmieniono %d rekordów.',
  ],
  'Nic do wykonania.',
  [
    'Pomyślnie wykonano %d zapytanie.',
    'Pomyślnie wykonano %d zapytania.',
    'Pomyślnie wykonano %d zapytań.',
  ],
  'Wykonaj',
  'Limit rekordów',
  'Wgranie pliku',
  'Wgrywanie plików jest wyłączone.',
  'Z serwera',
  'Plik %s na serwerze',
  'Uruchom z pliku',
  'Zatrzymaj w przypadku błędu',
  'Pokaż tylko błędy',
  'Historia',
  'Wyczyść',
  'Edytuj wszystkie',
  'Rekord został usunięty.',
  'Rekord został zaktualizowany.',
  'Rekord%s został dodany.',
  'Tabela została usunięta.',
  'Tabela została zmieniona.',
  'Tabela została utworzona.',
  'Nazwa tabeli',
  'składowanie',
  'Wartości domyślne',
  'Usunąć %s?',
  'Partycjonowanie',
  'Partycje',
  'Nazwa partycji',
  'Wartości',
  'Indeksy zostały zmienione.',
  'Typ indeksu',
  'Kolumna (długość)',
  'Nazwa',
  'Baza danych została usunięta.',
  'Nazwa bazy danych została zmieniona.',
  'Baza danych została utworzona.',
  'Baza danych została zmieniona.',
  'Uruchom',
  [
    'Procedura została uruchomiona, zmieniono %d rekord.',
    'Procedura została uruchomiona, zmieniono %d rekordy.',
    'Procedura została uruchomiona, zmieniono %d rekordów.',
  ],
  'Klucz obcy został usunięty.',
  'Klucz obcy został zmieniony.',
  'Klucz obcy został utworzony.',
  'Źródłowa i docelowa kolumna muszą być tego samego typu, powinien istnieć indeks na docelowej kolumnie oraz muszą istnieć dane referencyjne.',
  'Klucz obcy',
  'Tabela docelowa',
  'Schemat',
  'Zmień',
  'Dodaj kolumnę',
  'Perspektywa została zmieniona.',
  'Perspektywa została usunięta.',
  'Perspektywa została utworzona.',
  'Utwórz perspektywę',
  'Wydarzenie zostało usunięte.',
  'Wydarzenie zostało zmienione.',
  'Wydarzenie zostało utworzone.',
  'Zmień wydarzenie',
  'Utwórz wydarzenie',
  'Początek',
  'Koniec',
  'Wykonuj co',
  'Nie kasuj wydarzenia po przeterminowaniu',
  'Procedura została usunięta.',
  'Procedura została zmieniona.',
  'Procedura została utworzona.',
  'Zmień funkcję',
  'Zmień procedurę',
  'Utwórz funkcję',
  'Utwórz procedurę',
  'Zwracany typ',
  'Wyzwalacz został usunięty.',
  'Wyzwalacz został zmieniony.',
  'Wyzwalacz został utworzony.',
  'Zmień wyzwalacz',
  'Utwórz wyzwalacz',
  'Czas',
  'Wydarzenie',
  'Użytkownik został usunięty.',
  'Użytkownik został zmieniony.',
  'Użytkownik został dodany.',
  'Zahashowane',
  'Procedura',
  'Uprawnienia',
  'Usuń uprawnienia',
  [
    'Przerwano %d wątek.',
    'Przerwano %d wątki.',
    'Przerwano %d wątków.',
  ],
  'Duplikuj',
  '%d w sumie',
  'Przerwij wykonywanie',
  [
    'Zmieniono %d rekord.',
    'Zmieniono %d rekordy.',
    'Zmieniono %d rekordów.',
  ],
  'Ctrl+kliknij wartość, aby ją edytować.',
  'Kodowanie pliku musi być ustawione na UTF-8.',
  [
    '%d rekord został zaimportowany.',
    '%d rekordy zostały zaimportowane.',
    '%d rekordów zostało zaimportowanych.',
  ],
  'Nie udało się pobrać danych z tabeli',
  'Zmień',
  'Relacje',
  'edytuj',
  'Użyj linku edycji aby zmienić tę wartość.',
  'Wczytaj więcej danych',
  'Wczytywanie',
  'Strona',
  'ostatni',
  'Wybierz wszystkie',
  'Tabele zostały opróżnione.',
  'Tabele zostały przeniesione.',
  'Tabele zostały skopiowane.',
  'Tabele zostały usunięte.',
  'Tabele zostały zoptymalizowane.',
  'Tabele i perspektywy',
  'Wyszukaj we wszystkich tabelach',
  'Składowanie',
  'Rozmiar danych',
  'Rozmiar indeksów',
  'Wolne miejsce',
  'Liczba rekordów',
  'Wyczyść',
  'Optymalizuj',
  'Analizuj',
  'Sprawdź',
  'Napraw',
  'Opróżnij',
  'Przenieś do innej bazy danych',
  'Przenieś',
  'Kopiuj',
  'overwrite',
  'Harmonogram',
  'O danym czasie',
  'Zmień typ',
];
		case "pt": return [
  'Tem a certeza?',
  '%.3f s',
  'Não é possível enviar o ficheiro.',
  'Tamanho máximo do ficheiro é %sB.',
  'Ficheiro não existe.',
  ' ',
  '0123456789',
  'vazio',
  'original',
  'Não existem tabelas.',
  'Modificar',
  'Inserir',
  'Não existem registos.',
  'You have no privileges to update this table.',
  'Guardar',
  'Guardar e continuar a edição',
  'Guardar e inserir outro',
  'Saving',
  'Eliminar',
  'Idioma',
  'Usar',
  'Unknown error.',
  'Motor de Base de dados',
  'Servidor',
  'Nome de utilizador',
  'Senha',
  'Base de dados',
  'Entrar',
  'Memorizar a senha',
  'Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',
  'Selecionar dados',
  'Mostrar estrutura',
  'Modificar vista',
  'Modificar estrutura',
  'Novo Registo',
  'Warnings',
  [
    '%d byte',
    '%d bytes',
  ],
  'Coluna',
  'Tipo',
  'Comentário',
  'Incremento Automático',
  'Default value',
  'Selecionar',
  'Funções',
  'Adições',
  'Procurar',
  'qualquer local',
  'Ordenar',
  'decrescente',
  'Limite',
  'Tamanho do texto',
  'Ação',
  'Full table scan',
  'Comando SQL',
  'abrir',
  'guardar',
  'Modificar Base de dados',
  'Modificar esquema',
  'Criar esquema',
  'Esquema de Base de dados',
  'Privilégios',
  'Importar',
  'Exportar',
  'Criar tabela',
  'base de dados',
  'DB',
  'registos',
  'Disable %s or enable %s or %s extensions.',
  'Cadeia',
  'Números',
  'Data e hora',
  'Listas',
  'Binário',
  'Geometria',
  'ltr',
  'You are offline.',
  'Terminar sessão',
  'Too many unsuccessful logins, try again in %d minute(s).',
  'Sessão terminada com sucesso.',
  'Thanks for using Adminer, consider <a href="https://www.adminer.org/en/donation/">donating</a>.',
  'Sessão expirada, por favor entre de novo.',
  'Master password expired. <a href="https://www.adminer.org/en/extension/"%s>Implement</a> %s method to make it permanent.',
  'As sessões devem estar ativas.',
  'The action will be performed after successful login with the same credentials.',
  'Não há extensão',
  'Nenhuma das extensões PHP suportadas (%s) está disponivel.',
  'Connecting to privileged ports is not allowed.',
  'Identificação inválida.',
  'There is a space in the input password which might be the cause.',
  'Token CSRF inválido. Enviar o formulario novamente.',
  'Quantidade máxima de campos permitidos excedidos. Por favor aumente %s.',
  'If you did not send this request from Adminer then close this page.',
  'POST data demasiado grande. Reduza o tamanho ou aumente a diretiva de configuração %s.',
  'You can upload a big SQL file via FTP and import it from server.',
  'Chaves estrangeiras',
  'collation',
  'ON UPDATE',
  'ON DELETE',
  'Nome da coluna',
  'Nome de Parâmetro',
  'Tamanho',
  'Opções',
  'Adicionar próximo',
  'Mover para cima',
  'Mover para baixo',
  'Remover',
  'Base de dados inválida.',
  'Bases de dados eliminadas.',
  'Selecionar Base de dados',
  'Criar Base de dados',
  'Lista de processos',
  'Variáveis',
  'Estado',
  'Versão %s: %s através da extensão PHP %s',
  'Ligado como: %s',
  'Atualizar',
  'Colação',
  'Tabelas',
  'Size',
  'Compute',
  'Selected',
  'Remover',
  'Materialized view',
  'Visualizar',
  'Tabela',
  'Índices',
  'Modificar índices',
  'Origem',
  'Destino',
  'Modificar',
  'Adicionar Chave estrangeira',
  'Triggers',
  'Adicionar trigger',
  'Permanent link',
  'Saída',
  'Formato',
  'Procedimentos',
  'Eventos',
  'Dados',
  'Criar utilizador',
  'ATTACH queries are not supported.',
  'Erro na consulta',
  '%d / ',
  [
    '%d registo',
    '%d registos',
  ],
  [
    'Consulta executada, %d registo afetado.',
    'Consulta executada, %d registos afetados.',
  ],
  'Nenhum comando para executar.',
  [
    '%d consulta sql executada corretamente.',
    '%d consultas sql executadas corretamente.',
  ],
  'Executar',
  'Limit rows',
  'Importar ficheiro',
  'Importação de ficheiros desativada.',
  'Do servidor',
  'Ficheiro do servidor web %s',
  'Executar ficheiro',
  'Parar em caso de erro',
  'Mostrar somente erros',
  'Histórico',
  'Limpar',
  'Edit all',
  'Registo eliminado.',
  'Registo modificado.',
  'Registo%s inserido.',
  'Tabela eliminada.',
  'Tabela modificada.',
  'Tabela criada.',
  'Nome da tabela',
  'motor',
  'Valores predeterminados',
  'Drop %s?',
  'Particionar por',
  'Partições',
  'Nome da Partição',
  'Valores',
  'Índices modificados.',
  'Tipo de índice',
  'coluna (tamanho)',
  'Nome',
  'Base de dados eliminada.',
  'Base de dados renomeada.',
  'Base de dados criada.',
  'Base de dados modificada.',
  'Chamar',
  [
    'Consulta executada, %d registo afetado.',
    'Consulta executada, %d registos afetados.',
  ],
  'Chave estrangeira eliminada.',
  'Chave estrangeira modificada.',
  'Chave estrangeira criada.',
  'As colunas de origen e destino devem ser do mesmo tipo, deve existir um índice entre as colunas de destino e o registo referenciado deve existir.',
  'Chave estrangeira',
  'Tabela de destino',
  'Esquema',
  'Modificar',
  'Adicionar coluna',
  'Vista modificada.',
  'Vista eliminada.',
  'Vista criada.',
  'Criar vista',
  'Evento eliminado.',
  'Evento modificado.',
  'Evento criado.',
  'Modificar Evento',
  'Criar Evento',
  'Início',
  'Fim',
  'Cada',
  'Preservar ao completar',
  'Procedimento eliminado.',
  'Procedimento modificado.',
  'Procedimento criado.',
  'Modificar Função',
  'Modificar procedimento',
  'Criar função',
  'Criar procedimento',
  'Tipo de valor de regresso',
  'Trigger eliminado.',
  'Trigger modificado.',
  'Trigger criado.',
  'Modificar Trigger',
  'Adicionar Trigger',
  'Tempo',
  'Evento',
  'Utilizador eliminado.',
  'Utilizador modificado.',
  'Utilizador criado.',
  'Hash',
  'Rotina',
  'Conceder',
  'Impedir',
  [
    '%d processo terminado.',
    '%d processos terminados.',
  ],
  'Clonar',
  '%d no total',
  'Parar',
  [
    '%d item afetado.',
    '%d itens afetados.',
  ],
  'Ctrl+clique vezes sobre o valor para edita-lo.',
  'File must be in UTF-8 encoding.',
  [
    '%d registo importado.',
    '%d registos importados.',
  ],
  'Não é possivel selecionar a Tabela',
  'Modify',
  'Relações',
  'modificar',
  'Utilize o link modificar para alterar.',
  'Load more data',
  'Loading',
  'Página',
  'último',
  'Resultado completo',
  'Tabelas truncadas (truncate).',
  'As Tabelas foram movidas.',
  'Tables have been copied.',
  'As tabelas foram eliminadas.',
  'Tables have been optimized.',
  'Tabelas e vistas',
  'Pesquisar dados nas Tabelas',
  'Motor',
  'Tamanho de dados',
  'Tamanho de índice',
  'Espaço Livre',
  'Registos',
  'Vacuum',
  'Otimizar',
  'Analizar',
  'Verificar',
  'Reparar',
  'Truncar',
  'Mover outra Base de dados',
  'Mover',
  'Copy',
  'overwrite',
  'Agenda',
  'À hora determinada',
  'agora',
];
		case "pt-br": return [
  'Você tem certeza?',
  '%.3f s',
  'Não é possível enviar o arquivo.',
  'Tamanho máximo do arquivo permitido é %sB.',
  'Arquivo não existe.',
  ' ',
  '0123456789',
  'vazio',
  'original',
  'Não existem tabelas.',
  'Editar',
  'Inserir',
  'Não existem registros.',
  'You have no privileges to update this table.',
  'Salvar',
  'Salvar e continuar editando',
  'Salvar e inserir outro',
  'Saving',
  'Deletar',
  'Idioma',
  'Usar',
  'Unknown error.',
  'Sistema',
  'Servidor',
  'Usuário',
  'Senha',
  'Base de dados',
  'Entrar',
  'Login permanente',
  'Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',
  'Selecionar dados',
  'Mostrar estrutura',
  'Alterar visão',
  'Alterar estrutura',
  'Novo Registro',
  'Warnings',
  [
    '%d byte',
    '%d bytes',
  ],
  'Coluna',
  'Tipo',
  'Comentário',
  'Incremento Automático',
  'Default value',
  'Selecionar',
  'Funções',
  'Adições',
  'Procurar',
  'qualquer local',
  'Ordenar',
  'decrescente',
  'Limite',
  'Tamanho de texto',
  'Ação',
  'Full table scan',
  'Comando SQL',
  'abrir',
  'salvar',
  'Alterar Base de dados',
  'Alterar esquema',
  'Criar esquema',
  'Esquema de Base de dados',
  'Privilégios',
  'Importar',
  'Exportar',
  'Criar tabela',
  'base de dados',
  'DB',
  'selecionar',
  'Disable %s or enable %s or %s extensions.',
  'Strings',
  'Números',
  'Data e hora',
  'Listas',
  'Binário',
  'Geometria',
  'ltr',
  'You are offline.',
  'Sair',
  'Too many unsuccessful logins, try again in %d minute(s).',
  'Saída bem sucedida.',
  'Thanks for using Adminer, consider <a href="https://www.adminer.org/en/donation/">donating</a>.',
  'Sessão expirada, por favor logue-se novamente.',
  'Master password expired. <a href="https://www.adminer.org/en/extension/"%s>Implement</a> %s method to make it permanent.',
  'Suporte a sessões deve estar habilitado.',
  'The action will be performed after successful login with the same credentials.',
  'Não há extension',
  'Nenhuma das extensões PHP suportadas (%s) está disponível.',
  'Connecting to privileged ports is not allowed.',
  'Identificação inválida.',
  'There is a space in the input password which might be the cause.',
  'Token CSRF inválido. Enviar o formulário novamente.',
  'Quantidade máxima de campos permitidos excedidos. Por favor aumente %s.',
  'If you did not send this request from Adminer then close this page.',
  'POST data demasiado grande. Reduza o tamanho ou aumente a diretiva de configuração %s.',
  'You can upload a big SQL file via FTP and import it from server.',
  'Chaves estrangeiras',
  'collation',
  'ON UPDATE',
  'ON DELETE',
  'Nome da coluna',
  'Nome de Parâmetro',
  'Tamanho',
  'Opções',
  'Adicionar próximo',
  'Mover acima',
  'Mover abaixo',
  'Remover',
  'Base de dados inválida.',
  'A Base de dados foi apagada.',
  'Selecionar Base de dados',
  'Criar Base de dados',
  'Lista de processos',
  'Variáveis',
  'Estado',
  'Versão %s: %s através da extensão PHP %s',
  'Logado como: %s',
  'Atualizar',
  'Colação',
  'Tabelas',
  'Size',
  'Compute',
  'Selected',
  'Apagar',
  'Materialized view',
  'Visão',
  'Tabela',
  'Índices',
  'Alterar índices',
  'Origem',
  'Destino',
  'Alterar',
  'Adicionar Chave Estrangeira',
  'Triggers',
  'Adicionar trigger',
  'Permanent link',
  'Saída',
  'Formato',
  'Rotinas',
  'Eventos',
  'Dados',
  'Criar Usuário',
  'ATTACH queries are not supported.',
  'Erro na consulta',
  '%d / ',
  [
    '%d registro',
    '%d registros',
  ],
  [
    'Consulta executada, %d registro afetado.',
    'Consulta executada, %d registros afetados.',
  ],
  'Nenhum comando para executar.',
  [
    '%d consulta sql executada corretamente.',
    '%d consultas sql executadas corretamente.',
  ],
  'Executar',
  'Limit rows',
  'Importar arquivo',
  'Importação de arquivos desabilitada.',
  'A partir do servidor',
  'Arquivo do servidor web %s',
  'Executar Arquivo',
  'Parar em caso de erro',
  'Mostrar somente erros',
  'Histórico',
  'Limpar',
  'Edit all',
  'O Registro foi deletado.',
  'O Registro foi atualizado.',
  'O Registro%s foi inserido.',
  'A Tabela foi eliminada.',
  'A Tabela foi alterada.',
  'A Tabela foi criada.',
  'Nome da tabela',
  'motor',
  'Valores padrões',
  'Drop %s?',
  'Particionar por',
  'Partições',
  'Nome da Partição',
  'Valores',
  'Os Índices foram alterados.',
  'Tipo de índice',
  'Coluna (tamanho)',
  'Nome',
  'A Base de dados foi apagada.',
  'A Base de dados foi renomeada.',
  'A Base de dados foi criada.',
  'A Base de dados foi alterada.',
  'Chamar',
  [
    'Rotina executada, %d registro afetado.',
    'Rotina executada, %d registros afetados.',
  ],
  'A Chave Estrangeira foi apagada.',
  'A Chave Estrangeira foi alterada.',
  'A Chave Estrangeira foi criada.',
  'As colunas de origen e destino devem ser do mesmo tipo, deve existir um índice entre as colunas de destino e o registro referenciado deve existir.',
  'Chave Estrangeira',
  'Tabela de destino',
  'Esquema',
  'Modificar',
  'Adicionar coluna',
  'A Visão foi alterada.',
  'A Visão foi apagada.',
  'A Visão foi criada.',
  'Criar visão',
  'O Evento foi apagado.',
  'O Evento foi alterado.',
  'O Evento foi criado.',
  'Modificar Evento',
  'Criar Evento',
  'Início',
  'Fim',
  'Cada',
  'Ao completar preservar',
  'A Rotina foi apagada.',
  'A Rotina foi alterada.',
  'A Rotina foi criada.',
  'Alterar função',
  'Alterar procedimento',
  'Criar função',
  'Criar procedimento',
  'Tipo de valor de retorno',
  'O Trigger foi apagado.',
  'O Trigger foi alterado.',
  'O Trigger foi criado.',
  'Alterar Trigger',
  'Adicionar Trigger',
  'Tempo',
  'Evento',
  'O Usuário foi apagado.',
  'O Usuário foi alterado.',
  'O Usuário foi criado.',
  'Hash',
  'Rotina',
  'Conceder',
  'Impedir',
  [
    '%d processo foi terminado.',
    '%d processos foram terminados.',
  ],
  'Clonar',
  '%d no total',
  'Parar',
  [
    '%d item foi afetado.',
    '%d itens foram afetados.',
  ],
  'Ctrl+clique sobre o valor para edita-lo.',
  'File must be in UTF-8 encoding.',
  [
    '%d registro foi importado.',
    '%d registros foram importados.',
  ],
  'Não é possível selecionar a Tabela',
  'Modify',
  'Relações',
  'editar',
  'Utilize o link editar para modificar este valor.',
  'Load more data',
  'Loading',
  'Página',
  'último',
  'Resultado completo',
  'As Tabelas foram truncadas.',
  'As Tabelas foram movidas.',
  'Tables have been copied.',
  'As Tabelas foram eliminadas.',
  'Tables have been optimized.',
  'Tabelas e Visões',
  'Buscar dados nas Tabelas',
  'Motor',
  'Tamanho de dados',
  'Tamanho de índice',
  'Espaço Livre',
  'Registros',
  'Vacuum',
  'Otimizar',
  'Analisar',
  'Verificar',
  'Reparar',
  'Truncar',
  'Mover para outra Base de dados',
  'Mover',
  'Copy',
  'overwrite',
  'Agenda',
  'A hora determinada',
  'agora',
];
		case "ro": return [
  'Sunteți sigur(ă)?',
  '%.3f s',
  'Nu am putut încărca fișierul pe server.',
  'Fișierul maxim admis - %sO.',
  'Acest fișier nu există.',
  ',',
  '0123456789',
  'gol',
  'original',
  'În baza de date nu sunt tabele.',
  'Editează',
  'Inserează',
  'Nu sunt înscrieri.',
  'You have no privileges to update this table.',
  'Salvează',
  'Salvează și continuă editarea',
  'Salvează și mai inserează',
  'Saving',
  'Șterge',
  'Limba',
  'Alege',
  'Unknown error.',
  'Sistem',
  'Server',
  'Nume de utilizator',
  'Parola',
  'Baza de date',
  'Intră',
  'Logare permanentă',
  'Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',
  'Selectează',
  'Arată structura',
  'Modifică reprezentarea',
  'Modifică tabelul',
  'Înscriere nouă',
  'Warnings',
  [
    '%d octet',
    '%d octeți',
  ],
  'Coloană',
  'Tip',
  'Comentariu',
  'Creșterea automată',
  'Default value',
  'Selectează',
  'Funcții',
  'Agregare',
  'Căutare',
  'oriunde',
  'Sortare',
  'descrescător',
  'Limit',
  'Lungimea textului',
  'Acțiune',
  'Full table scan',
  'SQL query',
  'deschide',
  'salvează',
  'Modifică baza de date',
  'Modifică schema',
  'Crează o schemă',
  'Schema bazei de date',
  'Privilegii',
  'Importă',
  'Export',
  'Crează tabel',
  'baza de date',
  'DB',
  'selectează',
  'Disable %s or enable %s or %s extensions.',
  'Șiruri de caractere',
  'Număr',
  'Data și timpul',
  'Liste',
  'Tip binar',
  'Geometrie',
  'ltr',
  'You are offline.',
  'Ieșire',
  'Too many unsuccessful logins, try again in %d minute(s).',
  'Ați ieșit cu succes.',
  'Thanks for using Adminer, consider <a href="https://www.adminer.org/en/donation/">donating</a>.',
  'Timpul sesiunii a expirat, rog să vă conectați din nou.',
  'Master password expired. <a href="https://www.adminer.org/en/extension/"%s>Implement</a> %s method to make it permanent.',
  'Sesiunile trebuie să fie pornite.',
  'The action will be performed after successful login with the same credentials.',
  'Nu este extensie',
  'Nu este aviabilă nici o extensie suportată (%s).',
  'Connecting to privileged ports is not allowed.',
  'Numele de utilizator sau parola este greșită.',
  'There is a space in the input password which might be the cause.',
  'CSRF token imposibil. Retrimite forma.',
  'Numărul maxim de înscrieri disponibile a fost atins. Majorați %s.',
  'If you did not send this request from Adminer then close this page.',
  'Mesajul POST este prea mare. Trimiteți mai puține date sau măriți parametrul configurației directivei %s.',
  'You can upload a big SQL file via FTP and import it from server.',
  'Chei externe',
  'colaționarea',
  'La modificare',
  'La ștergere',
  'Denumirea coloanei',
  'Numele parametrului',
  'Lungime',
  'Acțiune',
  'Adaugă încă',
  'Mișcă în sus',
  'Mișcă în jos',
  'Șterge',
  'Bază de deate invalidă.',
  'Bazele de date au fost șterse.',
  'Alege baza de date',
  'Crează baza de date',
  'Lista proceselor',
  'Variabile',
  'Stare',
  'Versiunea %s: %s cu extensia PHP %s',
  'Ați intrat ca: %s',
  'Împrospătează',
  'Colaționare',
  'Tabele',
  'Size',
  'Compute',
  'Selected',
  'Șterge',
  'Materialized view',
  'Reprezentare',
  'Tabel',
  'Indexuri',
  'Modifică indexuri',
  'Sursă',
  'Scop',
  'Modifică',
  'Adaugă cheie externă',
  'Declanșatoare',
  'Adaugă trigger (declanșator)',
  'Adresă permanentă',
  'Date de ieșire',
  'Format',
  'Proceduri și funcții salvate',
  'Evenimente',
  'Date',
  'Crează utilizator',
  'ATTACH queries are not supported.',
  'Greșeală în query',
  '%d / ',
  [
    '%d înscriere',
    '%d înscrieri',
  ],
  [
    'Query executat, %d înscriere modificată.',
    'Query executat, %d înscrieri modificate.',
  ],
  'Nu sunt comenzi de executat.',
  [
    '%d query executat.',
    '%d query-uri executate cu succes.',
  ],
  'Execută',
  'Limit rows',
  'Încarcă fișierul',
  'Încărcarea fișierelor este interzisă.',
  'De pe server',
  'Fișierul %s pe server',
  'Execută fișier',
  'Se oprește la greșeală',
  'Arată doar greșeli',
  'Istoria',
  'Curăță',
  'Editează tot',
  'Înregistrare a fost ștearsă.',
  'Înregistrare a fost înnoită.',
  'Înregistrarea%s a fost inserată.',
  'Tabelul a fost șters.',
  'Tabelul a fost modificat.',
  'Tabelul a fost creat.',
  'Denumirea tabelului',
  'tip',
  'Valoarea inițială',
  'Drop %s?',
  'Împarte',
  'Secțiuni',
  'Denumirea secțiunii',
  'Parametru',
  'Indexurile au fost modificate.',
  'Tipul indexului',
  'Coloană (lungimea)',
  'Titlu',
  'Baza de date a fost ștearsă.',
  'Baza de date a fost redenumită.',
  'Baza de date a fost creată.',
  'Baza de date a fost modificată.',
  'Apelează',
  [
    'A fost executată procedura, %d înscriere a fost modificată.',
    'A fost executată procedura, %d înscrieri au fost modificate.',
  ],
  'Cheia externă a fost ștearsă.',
  'Cheia externă a fost modificată.',
  'Cheia externă a fost creată.',
  'Coloanele ar trebui să aibă aceleaşi tipuri de date, trebuie să existe date de referinţă și un index pe coloanela-ţintă.',
  'Cheie externă',
  'Tabela scop',
  'Schema',
  'Modifică',
  'Adaugă coloană',
  'Reprezentarea a fost modificată.',
  'Reprezentarea a fost ștearsă.',
  'Reprezentarea a fost creată.',
  'Crează reprezentare',
  'Evenimentul a fost șters.',
  'Evenimentul a fost modificat.',
  'Evenimentul a fost adăugat.',
  'Modifică eveniment',
  'Creează evenimet',
  'Început',
  'Sfârșit',
  'Fiecare',
  'Salvează după finisare',
  'Procedura a fost ștearsă.',
  'Procedura a fost modificată.',
  'Procedura a fost creată.',
  'Modifică funcția',
  'Modifică procedura',
  'Crează funcție',
  'Crează procedură',
  'Tipul returnării',
  'Triggerul a fost șters.',
  'Triggerul a fost modificat.',
  'Triggerul a fost creat.',
  'Modifică trigger',
  'Crează trigger',
  'Timp',
  'Eveniment',
  'Utilizatorul a fost șters.',
  'Utilizatorul a fost modificat.',
  'Utilizatorul a fost creat.',
  'Hashed',
  'Procedură',
  'Permite',
  'Interzice',
  [
    'A fost terminat %d proces.',
    'Au fost terminate %d procese.',
  ],
  'Clonează',
  'În total %d',
  'Termină',
  [
    'A fost modificată %d înscriere.',
    'Au fost modificate %d înscrieri.',
  ],
  'Ctrl+click pe o valoare pentru a o modifica.',
  'File must be in UTF-8 encoding.',
  [
    '%d rînd importat.',
    '%d rînduri importate.',
  ],
  'Nu am putut selecta date din tabel',
  'Modify',
  'Relații',
  'editare',
  'Valoare poate fi modificată cu ajutorul butonului «modifică».',
  'Load more data',
  'Loading',
  'Pagina',
  'ultima',
  'Tot rezultatul',
  'Tabelele au fost curățate.',
  'Tabelele au fost mutate.',
  'Tabelele au fost copiate',
  'Tabelele au fost șterse.',
  'Tables have been optimized.',
  'Tabele și reprezentări',
  'Caută în tabele',
  'Tip',
  'Cantitatea de date',
  'Cantitatea de indecși',
  'Spațiu liber',
  'Înscrieri',
  'Vacuum',
  'Optimizează',
  'Analizează',
  'Controlează',
  'Repară',
  'Curăță',
  'Mută în altă bază de date',
  'Mută',
  'Copiază',
  'overwrite',
  'Program',
  'În timpul curent',
  'HH:MM:SS',
];
		case "ru": return [
  'Вы уверены?',
  '%.3f s',
  'Не удалось загрузить файл на сервер.',
  'Максимальный разрешённый размер файла — %sB.',
  'Такого файла не существует.',
  ' ',
  '0123456789',
  'пусто',
  'исходный',
  'В базе данных нет таблиц.',
  'Редактировать',
  'Вставить',
  'Нет записей.',
  'У вас нет прав на обновление этой таблицы.',
  'Сохранить',
  'Сохранить и продолжить редактирование',
  'Сохранить и вставить ещё',
  'Сохранение',
  'Стереть',
  'Язык',
  'Выбрать',
  'Неизвестная ошибка.',
  'Движок',
  'Сервер',
  'Имя пользователя',
  'Пароль',
  'База данных',
  'Войти',
  'Оставаться в системе',
  'Adminer не поддерживает доступ к базе данных без пароля, <a href="https://www.adminer.org/en/password/"%s>больше информации</a>.',
  'Выбрать',
  'Показать структуру',
  'Изменить представление',
  'Изменить таблицу',
  'Новая запись',
  'Предупреждения',
  [
    '%d байт',
    '%d байта',
    '%d байтов',
  ],
  'поле',
  'Тип',
  'Комментарий',
  'Автоматическое приращение',
  'Значение по умолчанию',
  'Выбрать',
  'Функции',
  'Агрегация',
  'Поиск',
  'в любом месте',
  'Сортировать',
  'по убыванию',
  'Лимит',
  'Длина текста',
  'Действие',
  'Анализ полной таблицы',
  'SQL-запрос',
  'открыть',
  'сохранить',
  'Изменить базу данных',
  'Изменить схему',
  'Новая схема',
  'Схема базы данных',
  'Полномочия',
  'Импорт',
  'Экспорт',
  'Создать таблицу',
  'база данных',
  'DB',
  'выбрать',
  'Disable %s or enable %s or %s extensions.',
  'Строки',
  'Числа',
  'Дата и время',
  'Списки',
  'Двоичный тип',
  'Геометрия',
  'ltr',
  'Вы не выполнили вход.',
  'Выйти',
  [
    'Слишком много неудачных попыток входа. Попробуйте снова через %d минуту.',
    'Слишком много неудачных попыток входа. Попробуйте снова через %d минуты.',
    'Слишком много неудачных попыток входа. Попробуйте снова через %d минут.',
  ],
  'Вы успешно покинули систему.',
  'Спасибо за использование Adminer, рассмотрите возможность <a href="https://www.adminer.org/en/donation/">пожертвования</a>.',
  'Срок действия сессии истёк, нужно снова войти в систему.',
  'Мастер-пароль истёк. <a href="https://www.adminer.org/en/extension/"%s>Реализуйте</a> метод %s, чтобы сделать его постоянным.',
  'Сессии должны быть включены.',
  'Действие будет выполнено после успешного входа в систему с теми же учетными данными.',
  'Нет расширений',
  'Недоступно ни одного расширения из поддерживаемых (%s).',
  'Подключение к привилегированным портам не допускается.',
  'Неправильное имя пользователя или пароль.',
  'В введеном пароле есть пробел, это может быть причиною.',
  'Недействительный CSRF-токен. Отправите форму ещё раз.',
  'Достигнуто максимальное значение количества доступных полей. Увеличьте %s.',
  'Если вы не посылали этот запрос из Adminer, закройте эту страницу.',
  'Слишком большой объем POST-данных. Пошлите меньший объём данных или увеличьте параметр конфигурационной директивы %s.',
  'Вы можете закачать большой SQL-файл по FTP и затем импортировать его с сервера.',
  'Внешние ключи',
  'режим сопоставления',
  'При обновлении',
  'При стирании',
  'Название поля',
  'Название параметра',
  'Длина',
  'Действие',
  'Добавить ещё',
  'Переместить вверх',
  'Переместить вниз',
  'Удалить',
  'Неверная база данных.',
  'Базы данных удалены.',
  'Выбрать базу данных',
  'Создать базу данных',
  'Список процессов',
  'Переменные',
  'Состояние',
  'Версия %s: %s с PHP-расширением %s',
  'Вы вошли как: %s',
  'Обновить',
  'Режим сопоставления',
  'Таблицы',
  'Размер',
  'Вычислить',
  'Выбранные',
  'Удалить',
  'Материализованное представление',
  'Представление',
  'Таблица',
  'Индексы',
  'Изменить индексы',
  'Источник',
  'Цель',
  'Изменить',
  'Добавить внешний ключ',
  'Триггеры',
  'Добавить триггер',
  'Постоянная ссылка',
  'Выходные данные',
  'Формат',
  'Хранимые процедуры и функции',
  'События',
  'Данные',
  'Создать пользователя',
  'ATTACH-запросы не поддерживаются.',
  'Ошибка в запросe',
  '%d / ',
  [
    '%d строка',
    '%d строки',
    '%d строк',
  ],
  [
    'Запрос завершён, изменена %d запись.',
    'Запрос завершён, изменены %d записи.',
    'Запрос завершён, изменено %d записей.',
  ],
  'Нет команд для выполнения.',
  [
    '%d запрос выполнен успешно.',
    '%d запроса выполнено успешно.',
    '%d запросов выполнено успешно.',
  ],
  'Выполнить',
  'Лимит строк',
  'Загрузить файл на сервер',
  'Загрузка файлов на сервер запрещена.',
  'С сервера',
  'Файл %s на вебсервере',
  'Запустить файл',
  'Остановить при ошибке',
  'Только ошибки',
  'История',
  'Очистить',
  'Редактировать всё',
  'Запись удалена.',
  'Запись обновлена.',
  'Запись%s была вставлена.',
  'Таблица была удалена.',
  'Таблица была изменена.',
  'Таблица была создана.',
  'Название таблицы',
  'Тип таблицы',
  'Значения по умолчанию',
  'Удалить %s?',
  'Разделить по',
  'Разделы',
  'Название раздела',
  'Параметры',
  'Индексы изменены.',
  'Тип индекса',
  'Поле (длина)',
  'Название',
  'База данных была удалена.',
  'База данных была переименована.',
  'База данных была создана.',
  'База данных была изменена.',
  'Вызвать',
  [
    'Была вызвана процедура, %d запись была изменена.',
    'Была вызвана процедура, %d записи было изменено.',
    'Была вызвана процедура, %d записей было изменено.',
  ],
  'Внешний ключ был удалён.',
  'Внешний ключ был изменён.',
  'Внешний ключ был создан.',
  'Поля должны иметь одинаковые типы данных, в результирующем поле должен быть индекс, данные для импорта должны существовать.',
  'Внешний ключ',
  'Результирующая таблица',
  'Схема',
  'Изменить',
  'Добавить поле',
  'Представление было изменено.',
  'Представление было удалено.',
  'Представление было создано.',
  'Создать представление',
  'Событие было удалено.',
  'Событие было изменено.',
  'Событие было создано.',
  'Изменить событие',
  'Создать событие',
  'Начало',
  'Конец',
  'Каждые',
  'После завершения сохранить',
  'Процедура была удалена.',
  'Процедура была изменена.',
  'Процедура была создана.',
  'Изменить функцию',
  'Изменить процедуру',
  'Создать функцию',
  'Создать процедуру',
  'Возвращаемый тип',
  'Триггер был удалён.',
  'Триггер был изменён.',
  'Триггер был создан.',
  'Изменить триггер',
  'Создать триггер',
  'Время',
  'Событие',
  'Пользователь был удалён.',
  'Пользователь был изменён.',
  'Пользователь был создан.',
  'Хешировано',
  'Процедура',
  'Позволить',
  'Запретить',
  [
    'Был завершён %d процесс.',
    'Было завершено %d процесса.',
    'Было завершено %d процессов.',
  ],
  'Клонировать',
  'Всего %d',
  'Завершить',
  [
    'Была изменена %d запись.',
    'Были изменены %d записи.',
    'Было изменено %d записей.',
  ],
  'Выполните Ctrl+Щелчок мышью по значению, чтобы его изменить.',
  'Файл должен быть в кодировке UTF-8.',
  [
    'Импортирована %d строка.',
    'Импортировано %d строки.',
    'Импортировано %d строк.',
  ],
  'Не удалось получить данные из таблицы',
  'Изменить',
  'Отношения',
  'редактировать',
  'Изменить это значение можно с помощью ссылки «изменить».',
  'Загрузить ещё данные',
  'Загрузка',
  'Страница',
  'последняя',
  'Весь результат',
  'Таблицы были очищены.',
  'Таблицы были перемещены.',
  'Таблицы скопированы.',
  'Таблицы были удалены.',
  'Таблицы оптимизированы.',
  'Таблицы и представления',
  'Поиск в таблицах',
  'Тип таблиц',
  'Объём данных',
  'Объём индексов',
  'Свободное место',
  'Строк',
  'Вакуум',
  'Оптимизировать',
  'Анализировать',
  'Проверить',
  'Исправить',
  'Очистить',
  'Переместить в другую базу данных',
  'Переместить',
  'Копировать',
  'перезаписать',
  'Расписание',
  'В данное время',
  'База данных не поддерживает пароль.',
];
		case "sk": return [
  'Naozaj?',
  '%.3f s',
  'Súbor sa nepodarilo nahrať.',
  'Maximálna povolená veľkosť súboru je %sB.',
  'Súbor neexistuje.',
  ' ',
  '0123456789',
  'prázdne',
  'originál',
  'Žiadne tabuľky.',
  'Upraviť',
  'Vložiť',
  'Žiadne riadky.',
  'You have no privileges to update this table.',
  'Uložiť',
  'Uložiť a pokračovať v úpravách',
  'Uložiť a vložiť ďalší',
  'Saving',
  'Zmazať',
  'Jazyk',
  'Vybrať',
  'Unknown error.',
  'Systém',
  'Server',
  'Používateľ',
  'Heslo',
  'Databáza',
  'Prihlásiť sa',
  'Trvalé prihlásenie',
  'Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',
  'Vypísať dáta',
  'Zobraziť štruktúru',
  'Zmeniť pohľad',
  'Zmeniť tabuľku',
  'Nová položka',
  'Warnings',
  [
    '%d bajt',
    '%d bajty',
    '%d bajtov',
  ],
  'Stĺpec',
  'Typ',
  'Komentár',
  'Auto Increment',
  'Default value',
  'Vypísať',
  'Funkcie',
  'Agregácia',
  'Vyhľadať',
  'kdekoľvek',
  'Zotriediť',
  'zostupne',
  'Limit',
  'Dĺžka textov',
  'Akcia',
  'Full table scan',
  'SQL príkaz',
  'otvoriť',
  'uložiť',
  'Zmeniť databázu',
  'Pozmeniť schému',
  'Vytvoriť schému',
  'Schéma databázy',
  'Oprávnenia',
  'Import',
  'Export',
  'Vytvoriť tabuľku',
  'databáza',
  'DB',
  'vypísať',
  'Disable %s or enable %s or %s extensions.',
  'Reťazce',
  'Čísla',
  'Dátum a čas',
  'Zoznamy',
  'Binárne',
  'Geometria',
  'ltr',
  'You are offline.',
  'Odhlásiť',
  'Too many unsuccessful logins, try again in %d minute(s).',
  'Odhlásenie prebehlo v poriadku.',
  'Thanks for using Adminer, consider <a href="https://www.adminer.org/en/donation/">donating</a>.',
  'Session vypršala, prihláste sa prosím znova.',
  'Master password expired. <a href="https://www.adminer.org/en/extension/"%s>Implement</a> %s method to make it permanent.',
  'Session premenné musia byť povolené.',
  'The action will be performed after successful login with the same credentials.',
  'Žiadne rozšírenie',
  'Nie je dostupné žiadne z podporovaných rozšírení (%s).',
  'Connecting to privileged ports is not allowed.',
  'Neplatné prihlasovacie údaje.',
  'There is a space in the input password which might be the cause.',
  'Neplatný token CSRF. Odošlite formulár znova.',
  'Bol prekročený maximálny počet povolených polí. Zvýšte prosím %s.',
  'If you did not send this request from Adminer then close this page.',
  'Príliš veľké POST dáta. Zmenšite dáta alebo zvýšte hodnotu konfiguračej direktívy %s.',
  'You can upload a big SQL file via FTP and import it from server.',
  'Cudzie kľúče',
  'porovnávanie',
  'ON UPDATE',
  'ON DELETE',
  'Názov stĺpca',
  'Názov parametra',
  'Dĺžka',
  'Voľby',
  'Pridať ďalší',
  'Presunúť hore',
  'Presunúť dolu',
  'Odobrať',
  'Nesprávna databáza.',
  'Databázy boli odstránené.',
  'Vybrať databázu',
  'Vytvoriť databázu',
  'Zoznam procesov',
  'Premenné',
  'Stav',
  'Verzia %s: %s cez PHP rozšírenie %s',
  'Prihlásený ako: %s',
  'Obnoviť',
  'Porovnávanie',
  'Tabuľky',
  'Size',
  'Compute',
  'Selected',
  'Odstrániť',
  'Materialized view',
  'Pohľad',
  'Tabuľka',
  'Indexy',
  'Zmeniť indexy',
  'Zdroj',
  'Cieľ',
  'Zmeniť',
  'Pridať cudzí kľúč',
  'Triggery',
  'Pridať trigger',
  'Permanentný odkaz',
  'Výstup',
  'Formát',
  'Procedúry',
  'Udalosti',
  'Dáta',
  'Vytvoriť používateľa',
  'ATTACH queries are not supported.',
  'Chyba v dotaze',
  '%d / ',
  [
    '%d riadok',
    '%d riadky',
    '%d riadkov',
  ],
  [
    'Príkaz prebehol v poriadku, bol zmenený %d záznam.',
    'Príkaz prebehol v poriadku boli zmenené %d záznamy.',
    'Príkaz prebehol v poriadku bolo zmenených %d záznamov.',
  ],
  'Žiadne príkazy na vykonanie.',
  [
    'Bol vykonaný %d dotaz.',
    'Boli vykonané %d dotazy.',
    'Bolo vykonaných %d dotazov.',
  ],
  'Vykonať',
  'Limit rows',
  'Nahranie súboru',
  'Nahrávánie súborov nie je povolené.',
  'Zo serveru',
  'Súbor %s na webovom serveri',
  'Spustiť súbor',
  'Zastaviť pri chybe',
  'Zobraziť iba chyby',
  'História',
  'Vyčistiť',
  'Upraviť všetko',
  'Položka bola vymazaná.',
  'Položka bola aktualizovaná.',
  'Položka%s bola vložená.',
  'Tabuľka bola odstránená.',
  'Tabuľka bola zmenená.',
  'Tabuľka bola vytvorená.',
  'Názov tabuľky',
  'úložisko',
  'Východzie hodnoty',
  'Drop %s?',
  'Rozdeliť podľa',
  'Oddiely',
  'Názov oddielu',
  'Hodnoty',
  'Indexy boli zmenené.',
  'Typ indexu',
  'Stĺpec (dĺžka)',
  'Názov',
  'Databáza bola odstránená.',
  'Databáza bola premenovaná.',
  'Databáza bola vytvorená.',
  'Databáza bola zmenená.',
  'Zavolať',
  [
    'Procedúra bola zavolaná, bol zmenený %d záznam.',
    'Procedúra bola zavolaná, boli zmenené %d záznamy.',
    'Procedúra bola zavolaná, bolo zmenených %d záznamov.',
  ],
  'Cudzí kľúč bol odstránený.',
  'Cudzí kľúč bol zmenený.',
  'Cudzí kľúč bol vytvorený.',
  'Zdrojové a cieľové stĺpce musia mať rovnaký datový typ, nad cieľovými stĺpcami musí byť definovaný index a odkazované dáta musia existovať.',
  'Cudzí kľúč',
  'Cieľová tabuľka',
  'Schéma',
  'Zmeniť',
  'Pridať stĺpec',
  'Pohľad bol zmenený.',
  'Pohľad bol odstránený.',
  'Pohľad bol vytvorený.',
  'Vytvoriť pohľad',
  'Udalosť bola odstránená.',
  'Udalosť bola zmenená.',
  'Udalosť bola vytvorená.',
  'Upraviť udalosť',
  'Vytvoriť udalosť',
  'Začiatok',
  'Koniec',
  'Každých',
  'Po dokončení zachovat',
  'Procedúra bola odstránená.',
  'Procedúra bola zmenená.',
  'Procedúra bola vytvorená.',
  'Zmeniť funkciu',
  'Zmeniť procedúru',
  'Vytvoriť funkciu',
  'Vytvoriť procedúru',
  'Návratový typ',
  'Trigger bol odstránený.',
  'Trigger bol zmenený.',
  'Trigger bol vytvorený.',
  'Zmeniť trigger',
  'Vytvoriť trigger',
  'Čas',
  'Udalosť',
  'Používateľ bol odstránený.',
  'Používateľ bol zmenený.',
  'Používateľ bol vytvorený.',
  'Zahašované',
  'Procedúra',
  'Povoliť',
  'Zakázať',
  [
    'Bol ukončený %d proces.',
    'Boli ukončené %d procesy.',
    'Bolo ukončených %d procesov.',
  ],
  'Klonovať',
  '%d celkom',
  'Ukončiť',
  '%d položiek bolo ovplyvnených.',
  'Ctrl+kliknite na políčko, ktoré chcete zmeniť.',
  'File must be in UTF-8 encoding.',
  [
    'Bol importovaný %d záznam.',
    'Boli importované %d záznamy.',
    'Bolo importovaných %d záznamov.',
  ],
  'Tabuľku sa nepodarilo vypísať',
  'Modify',
  'Vzťahy',
  'upraviť',
  'Pre zmenu tejto hodnoty použite odkaz upraviť.',
  'Load more data',
  'Loading',
  'Stránka',
  'posledný',
  'Celý výsledok',
  'Tabuľka bola vyprázdnená.',
  'Tabuľka bola presunutá.',
  'Tabuľky boli skopírované.',
  'Tabuľka bola odstránená.',
  'Tables have been optimized.',
  'Tabuľky a pohľady',
  'Vyhľadať dáta v tabuľkách',
  'Typ',
  'Veľkosť dát',
  'Veľkosť indexu',
  'Voľné miesto',
  'Riadky',
  'Vacuum',
  'Optimalizovať',
  'Analyzovať',
  'Skontrolovať',
  'Opraviť',
  'Vyprázdniť',
  'Presunúť do inej databázy',
  'Presunúť',
  'Kopírovať',
  'overwrite',
  'Plán',
  'V stanovený čas',
  'HH:MM:SS',
];
		case "sl": return [
  'Ste prepričani?',
  '%.3f s',
  'Ne morem naložiti datoteke.',
  'Največja velikost datoteke je %sB.',
  'Datoteka ne obstaja.',
  ' ',
  '0123456789',
  'prazno',
  'original',
  'Ni tabel.',
  'Uredi',
  'Vstavi',
  'Ni vrstic.',
  'You have no privileges to update this table.',
  'Shrani',
  'Shrani in nadaljuj z urejanjem',
  'Shrani in vstavi tekst',
  'Saving',
  'Izbriši',
  'Jezik',
  'Uporabi',
  'Unknown error.',
  'Sistem',
  'Strežnik',
  'Uporabniško ime',
  'Geslo',
  'Baza',
  'Prijavi se',
  'Trajna prijava',
  'Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',
  'Izberi podatke',
  'Pokaži zgradbo',
  'Spremeni pogled',
  'Spremeni tabelo',
  'Nov predmet',
  'Warnings',
  [
    '%d bajt',
    '%d bajta',
    '%d bajti',
    '%d bajtov',
  ],
  'Stolpec',
  'Tip',
  'Komentar',
  'Samodejno povečevanje',
  'Default value',
  'Izberi',
  'Funkcije',
  'Združitev',
  'Išči',
  'kjerkoli',
  'Sortiraj',
  'padajoče',
  'Limita',
  'Dolžina teksta',
  'Dejanje',
  'Full table scan',
  'Ukaz SQL',
  'odpri',
  'shrani',
  'Spremeni bazo',
  'Spremeni shemo',
  'Ustvari shemo',
  'Shema baze',
  'Pravice',
  'Uvozi',
  'Izvozi',
  'Ustvari tabelo',
  'baza',
  'DB',
  'izberi',
  'Disable %s or enable %s or %s extensions.',
  'Nizi',
  'Števila',
  'Datum in čas',
  'Seznami',
  'Binarni',
  'Geometrčni',
  'ltr',
  'You are offline.',
  'Odjavi se',
  'Too many unsuccessful logins, try again in %d minute(s).',
  'Prijava uspešna.',
  'Thanks for using Adminer, consider <a href="https://www.adminer.org/en/donation/">donating</a>.',
  'Seja je potekla. Prosimo, ponovno se prijavite.',
  'Master password expired. <a href="https://www.adminer.org/en/extension/"%s>Implement</a> %s method to make it permanent.',
  'Podpora za seje mora biti omogočena.',
  'The action will be performed after successful login with the same credentials.',
  'Brez dodatkov',
  'Noben od podprtih dodatkov za PHP (%s) ni na voljo.',
  'Connecting to privileged ports is not allowed.',
  'Neveljavne pravice.',
  'There is a space in the input password which might be the cause.',
  'Neveljaven token CSRF. Pošljite formular še enkrat.',
  'Največje število dovoljenih polje je preseženo. Prosimo, povečajte %s.',
  'If you did not send this request from Adminer then close this page.',
  'Preveliko podatkov za POST. Zmanjšajte število podatkov ali povečajte nastavitev za %s.',
  'You can upload a big SQL file via FTP and import it from server.',
  'Tuji ključi',
  'zbiranje',
  'pri posodabljanju',
  'pri brisanju',
  'Ime stolpca',
  'Ime parametra',
  'Dolžina',
  'Možnosti',
  'Dodaj naslednjega',
  'Premakni gor',
  'Premakni dol',
  'Odstrani',
  'Neveljavna baza.',
  'Baze so zavržene.',
  'Izberi bazo',
  'Ustvari bazo',
  'Seznam procesov',
  'Spremenljivke',
  'Stanje',
  'Verzija %s: %s preko dodatka za PHP %s',
  'Prijavljen kot: %s',
  'Osveži',
  'Zbiranje',
  'Tabele',
  'Size',
  'Compute',
  'Selected',
  'Zavrzi',
  'Materialized view',
  'Pogledi',
  'Tabela',
  'Indeksi',
  'Spremeni indekse',
  'Izvor',
  'Cilj',
  'Spremeni',
  'Dodaj tuj ključ',
  'Sprožilniki',
  'Dodaj sprožilnik',
  'Permanent link',
  'Izhod rezultata',
  'Format',
  'Postopki',
  'Dogodki',
  'Podatki',
  'Ustvari uporabnika',
  'ATTACH queries are not supported.',
  'Napaka v poizvedbi',
  '%d / ',
  [
    '%d vrstica',
    '%d vrstici',
    '%d vrstice',
    '%d vrstic',
  ],
  [
    'Poizvedba se je uspešno izvedla, spremenjena je %d vrstica.',
    'Poizvedba se je uspešno izvedla, spremenjeni sta %d vrstici.',
    'Poizvedba se je uspešno izvedla, spremenjene so %d vrstice.',
    'Poizvedba se je uspešno izvedla, spremenjenih je %d vrstic.',
  ],
  'Ni ukazov za izvedbo.',
  [
    'Uspešno se je končala %d poizvedba.',
    'Uspešno sta se končali %d poizvedbi.',
    'Uspešno so se končale %d poizvedbe.',
    'Uspešno se je končalo %d poizvedb.',
  ],
  'Izvedi',
  'Limit rows',
  'Naloži datoteko',
  'Nalaganje datotek je onemogočeno.',
  'z strežnika',
  'Datoteka na spletnem strežniku %s',
  'Zaženi datoteko',
  'Ustavi ob napaki',
  'Pokaži samo napake',
  'Zgodovina',
  'Počisti',
  'Edit all',
  'Predmet je izbrisan.',
  'Predmet je posodobljen.',
  'Predmet%s je vstavljen.',
  'Tabela je zavržena.',
  'Tabela je spremenjena.',
  'Tabela je ustvarjena.',
  'Ime tabele',
  'pogon',
  'Privzete vrednosti',
  'Drop %s?',
  'Porazdeli po',
  'Porazdelitve',
  'Ime porazdelitve',
  'Vrednosti',
  'Indeksi so spremenjeni.',
  'Tip indeksa',
  'Stolpec (dolžina)',
  'Naziv',
  'Baza je zavržena.',
  'Baza je preimenovana.',
  'Baza je ustvarjena.',
  'Baza je spremenjena.',
  'Pokliči',
  [
    'Klican je bil postopek, spremenjena je %d vrstica.',
    'Klican je bil postopek, spremenjeni sta %d vrstici.',
    'Klican je bil postopek, spremenjene so %d vrstice.',
    'Klican je bil postopek, spremenjenih je %d vrstic.',
  ],
  'Tuj ključ je zavržen.',
  'Tuj ključ je spremenjen.',
  'Tuj ključ je ustvarjen.',
  'Izvorni in ciljni stolpec mora imeti isti podatkovni tip. Obstajati mora indeks na ciljnih stolpcih in obstajati morajo referenčni podatki.',
  'Tuj ključ',
  'Ciljna tabela',
  'Shema',
  'Spremeni',
  'Dodaj stolpec',
  'Pogled je spremenjen.',
  'Pogled je zavržen.',
  'Pogled je ustvarjen.',
  'Ustvari pogled',
  'Dogodek je zavržen.',
  'Dogodek je spremenjen.',
  'Dogodek je ustvarjen.',
  'Spremeni dogodek',
  'Ustvari dogodek',
  'Začetek',
  'Konec',
  'vsake',
  'Po zaključku ohrani',
  'Postopek je zavržen.',
  'Postopek je spremenjen.',
  'Postopek je ustvarjen.',
  'Spremeni funkcijo',
  'Spremeni postopek',
  'Ustvari funkcijo',
  'Ustvari postopek',
  'Vračalni tip',
  'Sprožilnik je odstranjen.',
  'Sprožilnik je spremenjen.',
  'Sprožilnik je ustvarjen.',
  'Spremeni sprožilnik',
  'Ustvari sprožilnik',
  'Čas',
  'Dogodek',
  'Uporabnik je odstranjen.',
  'Uporabnik je spremenjen.',
  'Uporabnik je ustvarjen.',
  'Zakodirano',
  'Postopek',
  'Dovoli',
  'Odvzemi',
  [
    'Končan je %d proces.',
    'Končana sta %d procesa.',
    'Končani so %d procesi.',
    'Končanih je %d procesov.',
  ],
  'Kloniraj',
  'Skupaj %d',
  'Končaj',
  [
    'Spremenjen je %d predmet.',
    'Spremenjena sta %d predmeta.',
    'Spremenjeni so %d predmeti.',
    'Spremenjenih je %d predmetov.',
  ],
  'Ctrl+klik na vrednost za urejanje.',
  'File must be in UTF-8 encoding.',
  [
    'Uvožena je %d vrstica.',
    'Uvoženi sta %d vrstici.',
    'Uvožene so %d vrstice.',
    'Uvoženih je %d vrstic.',
  ],
  'Ne morem izbrati tabele',
  'Modify',
  'Relacijski',
  'uredi',
  'Uporabite urejanje povezave za spreminjanje te vrednosti.',
  'Load more data',
  'Loading',
  'Stran',
  'Zadnja',
  'Cel razultat',
  'Tabele so skrajšane.',
  'Tabele so premaknjene.',
  'Tabele so kopirane.',
  'Tabele so zavržene.',
  'Tables have been optimized.',
  'Tabele in pogledi',
  'Išče podatke po tabelah',
  'Pogon',
  'Velikost podatkov',
  'Velikost indeksa',
  'Podatkov prosto ',
  'Vrstic',
  'Vacuum',
  'Optimiziraj',
  'Analiziraj',
  'Preveri',
  'Popravi',
  'Skrajšaj',
  'Premakni v drugo bazo',
  'Premakni',
  'Kopiraj',
  'overwrite',
  'Urnik',
  'v danem času',
  'Spremeni tip',
];
		case "sr": return [
  'Да ли сте сигурни?',
  '%.3f s',
  'Слање датотеке није успело.',
  'Највећа дозвољена величина датотеке је %sB.',
  'Датотека не постоји.',
  ',',
  '0123456789',
  'празно',
  'оригинал',
  'Без табела.',
  'Измени',
  'Уметни',
  'Без редова.',
  'You have no privileges to update this table.',
  'Сачувај',
  'Сачувај и настави уређење',
  'Сачувај и уметни следеће',
  'Saving',
  'Избриши',
  'Језик',
  'Користи',
  'Unknown error.',
  'Систем',
  'Сервер',
  'Корисничко име',
  'Лозинка',
  'База података',
  'Пријава',
  'Трајна пријава',
  'Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',
  'Изабери податке',
  'Прикажи структуру',
  'Уреди поглед',
  'Уреди табелу',
  'Нова ставка',
  'Warnings',
  [
    '%d бајт',
    '%d бајта',
    '%d бајтова',
  ],
  'Колона',
  'Тип',
  'Коментар',
  'Ауто-прираштај',
  'Default value',
  'Изабери',
  'Функције',
  'Сакупљање',
  'Претрага',
  'било где',
  'Поређај',
  'опадајуће',
  'Граница',
  'Дужина текста',
  'Акција',
  'Скренирање комплетне табеле',
  'SQL команда',
  'отвори',
  'сачувај',
  'Уреди базу података',
  'Уреди шему',
  'Формирај шему',
  'Шема базе података',
  'Дозволе',
  'Увоз',
  'Извоз',
  'Направи табелу',
  'база података',
  'DB',
  'изабери',
  'Disable %s or enable %s or %s extensions.',
  'Текст',
  'Број',
  'Датум и време',
  'Листе',
  'Бинарно',
  'Геометрија',
  'ltr',
  'You are offline.',
  'Одјава',
  'Too many unsuccessful logins, try again in %d minute(s).',
  'Успешна одјава.',
  'Thanks for using Adminer, consider <a href="https://www.adminer.org/en/donation/">donating</a>.',
  'Ваша сесија је истекла, пријавите се поново.',
  'Master password expired. <a href="https://www.adminer.org/en/extension/"%s>Implement</a> %s method to make it permanent.',
  'Морате омогућити подршку за сесије.',
  'The action will be performed after successful login with the same credentials.',
  'Без додатака',
  'Ниједан од подржаних PHP додатака није доступан.',
  'Connecting to privileged ports is not allowed.',
  'Неважеће дозволе.',
  'There is a space in the input password which might be the cause.',
  'Неважећи CSRF код. Проследите поново форму.',
  'Премашен је максимални број дозвољених поља. Молим увећајте %s.',
  'If you did not send this request from Adminer then close this page.',
  'Превелики POST податак. Морате да смањите податак или повећајте вредност конфигурационе директиве %s.',
  'You can upload a big SQL file via FTP and import it from server.',
  'Страни кључеви',
  'Сравњивање',
  'ON UPDATE (приликом освежавања)',
  'ON DELETE (приликом брисања)',
  'Назив колоне',
  'Назив параметра',
  'Дужина',
  'Опције',
  'Додај следећи',
  'Помери на горе',
  'Помери на доле',
  'Уклони',
  'Неисправна база података.',
  'Базњ података су избрисане.',
  'Изаберите базу',
  'Формирај базу података',
  'Списак процеса',
  'Променљиве',
  'Статус',
  '%s верзија: %s помоћу PHP додатка је %s',
  'Пријави се као: %s',
  'Освежи',
  'Сравњивање',
  'Табеле',
  'Size',
  'Compute',
  'Selected',
  'Избриши',
  'Materialized view',
  'Поглед',
  'Табела',
  'Индекси',
  'Уреди индексе',
  'Извор',
  'Циљ',
  'Уреди',
  'Додај страни кључ',
  'Окидачи',
  'Додај окидач',
  'Трајна веза',
  'Испис',
  'Формат',
  'Рутине',
  'Догађаји',
  'Податци',
  'Направи корисника',
  'ATTACH queries are not supported.',
  'Грешка у упиту',
  '%d / ',
  [
    '%d ред',
    '%d реда',
    '%d редова',
  ],
  [
    'Упит је успешно извршен, %d ред је погођен.',
    'Упит је успешно извршен, %d реда су погођена.',
    'Упит је успешно извршен, %d редова је погођено.',
  ],
  'Без команди за извршавање.',
  [
    '%d упит је успешно извршен.',
    '%d упита су успешно извршена.',
    '%d упита је успешно извршено.',
  ],
  'Изврши',
  'Limit rows',
  'Слање датотека',
  'Онемогућено је слање датотека.',
  'Са сервера',
  'Датотека %s са веб сервера',
  'Покрени датотеку',
  'Заустави приликом грешке',
  'Приказуј само грешке',
  'Историјат',
  'Очисти',
  'Измени све',
  'Ставка је избрисана.',
  'Ставка је измењена.',
  'Ставка%s је додата.',
  'Табела је избрисана.',
  'Табела је измењена.',
  'Табела је креирана.',
  'Назив табеле',
  'механизам',
  'Подразумеване вредности',
  'Drop %s?',
  'Подели по',
  'Поделе',
  'Име поделе',
  'Вредности',
  'Индекси су измењени.',
  'Тип индекса',
  'Колона (дужина)',
  'Име',
  'База података је избрисана.',
  'База података је преименована.',
  'База података је креирана.',
  'База података је измењена.',
  'Позови',
  [
    'Позвана је рутина, %d ред је погођен.',
    'Позвана је рутина, %d реда су погођена.',
    'Позвана је рутина, %d редова је погођено.',
  ],
  'Страни кључ је избрисан.',
  'Страни кључ је измењен.',
  'Страни кључ је креиран.',
  'Изворне и циљне колоне морају бити истог типа, циљна колона мора бити индексирана и изворна табела мора садржати податке из циљне.',
  'Страни кључ',
  'Циљна табела',
  'Шема',
  'Измени',
  'Додај колону',
  'Поглед је измењен.',
  'Поглед је избрисан.',
  'Поглед је креиран.',
  'Направи поглед',
  'Догађај је избрисан.',
  'Догађај је измењен.',
  'Догађај је креиран.',
  'Уреди догађај',
  'Направи догађај',
  'Почетак',
  'Крај',
  'Сваки',
  'Задржи по завршетку',
  'Рутина је избрисана.',
  'Рутина је измењена.',
  'Рутина је креирана.',
  'Уреди функцију',
  'Уреди процедуру',
  'Формирај функцију',
  'Формирај процедуру',
  'Повратни тип',
  'Окидач је избрисан.',
  'Окидач је измењен.',
  'Окидач је креиран.',
  'Уреди окидач',
  'Формирај окидач',
  'Време',
  'Догађај',
  'Корисник је избрисан.',
  'Корисник је измењен.',
  'корисник је креиран.',
  'Хеширано',
  'Рутина',
  'Дозволи',
  'Опозови',
  [
    '%d процес је убијен.',
    '%d процеса су убијена.',
    '%d процеса је убијено.',
  ],
  'Дуплирај',
  'укупно %d',
  'Убиј',
  [
    '%d ставка је погођена.',
    '%d ставке су погођене.',
    '%d ставки је погођено.',
  ],
  'Ctrl+клик на вредност за измену.',
  'File must be in UTF-8 encoding.',
  [
    '%d ред је увежен.',
    '%d реда су увежена.',
    '%d редова је увежено.',
  ],
  'Не могу да изаберем табелу',
  'Modify',
  'Односи',
  'измени',
  'Користи везу за измену ове вредности.',
  'Учитавам још података',
  'Учитавам',
  'Страна',
  'последња',
  'Цео резултат',
  'Табеле су испражњене.',
  'Табеле су премешћене.',
  'Табеле су умножене.',
  'Табеле су избрисане.',
  'Табеле су оптимизоване.',
  'Табеле и погледи',
  'Претражи податке у табелама',
  'Механизам',
  'Дужина података',
  'Дужина индекса',
  'Слободно података',
  'Редова',
  'Vacuum',
  'Оптимизуј',
  'Анализирај',
  'Провери',
  'Поправи',
  'Испразни',
  'Премести у другу базу података',
  'Премести',
  'Умножи',
  'overwrite',
  'Распоред',
  'У задато време',
  'Уреди тип',
];
		case "sv": return [
  'Är du säker?',
  '%.3f s',
  'Det går inte add ladda upp filen.',
  'Högsta tillåtna storlek är %sB.',
  'Filen finns inte.',
  ',',
  '0123456789',
  'tom',
  'original',
  'Inga tabeller.',
  'Redigera',
  'Infoga',
  'Inga rader.',
  'Du har inga privilegier för att uppdatera den här tabellen.',
  'Spara',
  'Spara och fortsätt att redigera',
  'Spara och infoga nästa',
  'Sparar',
  'Ta bort',
  'Språk',
  'Använd',
  'Okänt fel.',
  'System',
  'Server',
  'Användarnamn',
  'Lösenord',
  'Databas',
  'Logga in',
  'Permanent inloggning',
  'Adminer tillåter inte att ansluta till en databas utan lösenord. <a href="https://www.adminer.org/en/password/"%s>Mer information</a>.',
  'Välj data',
  'Visa struktur',
  'Ändra vy',
  'Ändra tabell',
  'Ny sak',
  'Varningar',
  [
    '%d byte',
    '%d bytes',
  ],
  'Kolumn',
  'Typ',
  'Kommentar',
  'Automatisk uppräkning',
  'Standardvärde',
  'Välj',
  'Funktioner',
  'Aggregation',
  'Sök',
  'överallt',
  'Sortera',
  'Fallande',
  'Begränsning',
  'Textlängd',
  'Åtgärd',
  'Full tabellskanning',
  'SQL-kommando',
  'Öppna',
  'Spara',
  'Ändra databas',
  'Redigera schema',
  'Skapa schema',
  'Databasschema',
  'Privilegier',
  'Importera',
  'Exportera',
  'Skapa tabell',
  'databas',
  'DB',
  'välj',
  'Stäng av %s eller sätt på %s eller %s tilläggen.',
  'Strängar',
  'Nummer',
  'Datum och tid',
  'Listor',
  'Binärt',
  'Geometri',
  'ltr',
  'Du är offline.',
  'Logga ut',
  [
    'För många misslyckade inloggningar, försök igen om %d minut.',
    'För många misslyckade inloggningar, försök igen om %d minuter.',
  ],
  'Du är nu utloggad.',
  'Tack för att du använder Adminer, vänligen fundera över att <a href="https://www.adminer.org/en/donation/">donera</a>.',
  'Session har löpt ur, vänligen logga in igen.',
  'Huvudlösenordet har löpt ut. <a href="https://www.adminer.org/en/extension/"%s>Implementera</a> %s en metod för att göra det permanent.',
  'Support för sessioner måste vara på.',
  'Åtgärden kommer att utföras efter en lyckad inloggning med samma inloggningsuppgifter.',
  'Inget tillägg',
  'Inga av de PHP-tilläggen som stöds (%s) är tillgängliga.',
  'Anslutning till privilegierade portar är inte tillåtet.',
  'Ogiltiga inloggningsuppgifter.',
  'Det finns ett mellanslag i lösenordet, vilket kan vara anledningen.',
  'Ogiltig CSRF-token. Skicka formuläret igen.',
  'Högsta nummer tillåtna fält är överskridet. Vänligen höj %s.',
  'Om du inte skickade en förfrågan från Adminer så kan du stänga den här sidan.',
  'POST-datan är för stor. Minska det eller höj %s-direktivet.',
  'Du kan ladda upp en stor SQL-fil via FTP och importera det från servern.',
  'Främmande nycklar',
  'kollationering',
  'VID UPPDATERING',
  'VID BORTTAGNING',
  'Kolumnnamn',
  'Namn på parameter',
  'Längd',
  'Inställningar',
  'Lägg till nästa',
  'Flytta upp',
  'Flytta ner',
  'Ta bort',
  'Ogiltig databas.',
  'Databaserna har tagits bort.',
  'Välj databas',
  'Skapa databas',
  'Processlista',
  'Variabler',
  'Status',
  '%s version: %s genom PHP-tillägg %s',
  'Inloggad som: %s',
  'Ladda om',
  'Kollationering',
  'Tabeller',
  'Storlek',
  'Beräkna',
  'Vald',
  'Ta bort',
  'Materialiserad vy',
  'Vy',
  'Tabell',
  'Index',
  'Ändra index',
  'Källa',
  'Mål',
  'Ändra',
  'Lägg till främmande nyckel',
  'Avtryckare',
  'Lägg till avtryckare',
  'Permanent länk',
  'Utmatning',
  'Format',
  'Rutiner',
  'Event',
  'Data',
  'Skapa användare',
  'ATTACH-förfrågor stöds inte.',
  'Fel i förfrågan',
  '%d / ',
  [
    '%d rad',
    '%d rader',
  ],
  [
    'Förfrågan lyckades, %d rad påverkades.',
    'Förfrågan lyckades, %d rader påverkades.',
  ],
  'Inga kommandon att köra.',
  [
    '%d förfrågan lyckades.',
    '%d förfrågor lyckades.',
  ],
  'Kör',
  'Begränsa rader',
  'Ladda upp fil',
  'Filuppladdningar är avstängda.',
  'Från server',
  'Serverfil %s',
  'Kör fil',
  'Stanna på fel',
  'Visa bara fel',
  'Historia',
  'Rensa',
  'Redigera alla',
  'En sak har tagits bort.',
  'En sak har ändrats.',
  'Sak%s har skapats.',
  'Tabell har tagits bort.',
  'Tabell har ändrats.',
  'Tabell har skapats.',
  'Tabellnamn',
  'motor',
  'Standardvärden',
  'Ta bort %s?',
  'Partitionera om',
  'Partitioner',
  'Partition',
  'Värden',
  'Index har ändrats.',
  'Indextyp',
  'Kolumn (längd)',
  'Namn',
  'Databasen har tagits bort.',
  'Databasen har fått sitt namn ändrat.',
  'Databasen har skapats.',
  'Databasen har ändrats.',
  'Kalla',
  [
    'Rutin har kallats, %d rad påverkades.',
    'Rutin har kallats, %d rader påverkades.',
  ],
  'Främmande nyckel har tagits bort.',
  'Främmande nyckel har ändrats.',
  'Främmande nyckel har skapats.',
  'Käll- och mål-tabellen måste ha samma datatyp, ett index på målkolumnerna och refererad data måste finnas.',
  'Främmande nyckel',
  'Måltabell',
  'Schema',
  'Ändra',
  'Lägg till kolumn',
  'Vy har ändrats.',
  'Vy har tagits bort.',
  'Vy har skapats.',
  'Skapa vy',
  'Event har tagits bort.',
  'Event har ändrats.',
  'Event har skapats.',
  'Ändra event',
  'Skapa event',
  'Start',
  'Slut',
  'Varje',
  'Bibehåll vid slutet',
  'Rutin har tagits bort.',
  'Rutin har ändrats.',
  'Rutin har skapats.',
  'Ändra funktion',
  'Ändra procedur',
  'Skapa funktion',
  'Skapa procedur',
  'Återvändningstyp',
  'Avtryckare har tagits bort.',
  'Avtryckare har ändrats.',
  'Avtryckare har skapats.',
  'Ändra avtryckare',
  'Skapa avtryckare',
  'Tid',
  'Event',
  'Användare har blivit borttagen.',
  'Användare har blivit ändrad.',
  'Användare har blivit skapad.',
  'Hashad',
  'Rutin',
  'Tillåt',
  'Neka',
  [
    '%d process har avslutats.',
    '%d processer har avslutats.',
  ],
  'Klona',
  'totalt %d',
  'Avsluta',
  [
    '%d sak har blivit förändrad.',
    '%d saker har blivit förändrade.',
  ],
  'Ctrl+klicka på ett värde för att ändra det.',
  'Filer måste vara i UTF-8-format.',
  [
    '%d rad har importerats.',
    '%d rader har importerats.',
  ],
  'Kunde inte välja tabellen',
  'Ändra',
  'Relationer',
  'redigera',
  'Använd redigeringslänken för att ändra värdet.',
  'Ladda mer data',
  'Laddar',
  'Sida',
  'sist',
  'Hela resultatet',
  'Tabeller har blivit avkortade.',
  'Tabeller har flyttats.',
  'Tabeller har kopierats.',
  'Tabeller har tagits bort.',
  'Tabeller har optimerats.',
  'Tabeller och vyer',
  'Sök data i tabeller',
  'Motor',
  'Datalängd',
  'Indexlängd',
  'Ledig data',
  'Rader',
  'Städa',
  'Optimera',
  'Analysera',
  'Kolla',
  'Reparera',
  'Avkorta',
  'Flytta till en annan databas',
  'Flytta',
  'Kopiera',
  'Skriv över',
  'Schemalägga',
  'Vid en tid',
  'Ändra typ',
];
		case "ta": return [
  'நிச்ச‌ய‌மாக‌ ?',
  '%.3f s',
  'கோப்பை மேலேற்ற‌ம் (upload) செய்ய‌ இயல‌வில்லை.',
  'கோப்பின் அதிக‌ப‌ட்ச‌ அள‌வு %sB.',
  'கோப்பு இல்லை.',
  ',',
  '0123456789',
  'வெறுமை (empty)',
  'அச‌ல்',
  'அட்ட‌வ‌ணை இல்லை.',
  'தொகு',
  'புகுத்து',
  'வ‌ரிசை இல்லை.',
  'You have no privileges to update this table.',
  'சேமி',
  'சேமித்த‌ பிற‌கு தொகுப்ப‌தை தொட‌ர‌வும்',
  'சேமித்த‌ப் பின் அடுத்த‌தை புகுத்து',
  'Saving',
  'நீக்கு',
  'மொழி',
  'உப‌யோகி',
  'Unknown error.',
  'சிஸ்ட‌ம் (System)',
  'வ‌ழ‌ங்கி (Server)',
  'ப‌ய‌னாள‌ர் (User)',
  'க‌ட‌வுச்சொல்',
  'த‌க‌வ‌ல்த‌ள‌ம்',
  'நுழை',
  'நிர‌ந்த‌ர‌மாக‌ நுழைய‌வும்',
  'Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',
  'த‌க‌வ‌லை தேர்வு செய்',
  'க‌ட்ட‌மைப்பை காண்பிக்க‌வும்',
  'தோற்ற‌த்தை மாற்று',
  'அட்ட‌வ‌ணையை மாற்று',
  'புதிய‌ உருப்ப‌டி',
  'Warnings',
  [
    '%d பைட்',
    '%d பைட்டுக‌ள்',
  ],
  'நெடுவ‌ரிசை',
  'வ‌கை',
  'குறிப்பு',
  'ஏறுமான‌ம்',
  'Default value',
  'தேர்வு செய்',
  'Functions',
  'திர‌ள்வு (Aggregation)',
  'தேடு',
  'எங்காயினும்',
  'த‌ர‌ம் பிரி',
  'இற‌ங்குமுக‌மான‌',
  'வ‌ர‌ம்பு',
  'உரை நீள‌ம்',
  'செய‌ல்',
  'Full table scan',
  'SQL க‌ட்ட‌ளை',
  'திற‌',
  'சேமி',
  'த‌க‌வ‌ல்த‌ள‌த்தை மாற்று',
  'அமைப்புமுறையை மாற்று',
  'அமைப்புமுறையை உருவாக்கு',
  'த‌க‌வ‌ல்த‌ள‌ அமைப்பு முறைக‌ள்',
  'ச‌லுகைக‌ள் / சிற‌ப்புரிமைக‌ள்',
  'இற‌க்கும‌தி (Import)',
  'ஏற்றும‌தி',
  'அட்ட‌வ‌ணையை உருவாக்கு',
  'த‌க‌வ‌ல்த‌ள‌ம்',
  'DB',
  'தேர்வு செய்',
  'Disable %s or enable %s or %s extensions.',
  'ச‌ர‌ம் (String)',
  'எண்க‌ள்',
  'தேதி ம‌ற்றும் நேர‌ம்',
  'ப‌ட்டிய‌ல்',
  'பைன‌ரி',
  'வ‌டிவ‌விய‌ல் (Geometry)',
  'ltr',
  'You are offline.',
  'வெளியேறு',
  'Too many unsuccessful logins, try again in %d minute(s).',
  'வெற்றிக‌ர‌மாய் வெளியேறியாயிற்று.',
  'Thanks for using Adminer, consider <a href="https://www.adminer.org/en/donation/">donating</a>.',
  'செஷ‌ன் காலாவ‌தியாகி விட்ட‌து. மீண்டும் நுழைய‌வும்.',
  'Master password expired. <a href="https://www.adminer.org/en/extension/"%s>Implement</a> %s method to make it permanent.',
  'செஷ‌ன் ஆத‌ர‌வு இய‌க்க‌ப்ப‌ட‌ வேண்டும்.',
  'The action will be performed after successful login with the same credentials.',
  'விரிவு (extensஇஒன்) இல்லை ',
  'PHP ஆத‌ர‌வு விரிவுக‌ள் (%s) இல்லை.',
  'Connecting to privileged ports is not allowed.',
  'ச‌ரியான‌ விப‌ர‌ங்க‌ள் இல்லை.',
  'There is a space in the input password which might be the cause.',
  'CSRF டோக்க‌ன் செல்லாது. ப‌டிவ‌த்தை மீண்டும் அனுப்ப‌வும்.',
  'அனும‌திக்க‌ப்ப‌ட்ட‌ அதிக‌ப‌ட்ச‌ கோப்புக‌ளின் எண்ணிக்கை மீற‌ப்ப‌ட்ட‌து. த‌ய‌வு செய்து %s ம‌ற்றும் %s யை அதிக‌ரிக்க‌வும்.',
  'If you did not send this request from Adminer then close this page.',
  'மிக‌ அதிக‌மான‌ POST த‌க‌வ‌ல். த‌க‌வ‌லை குறைக்க‌வும் அல்ல‌து %s வ‌டிவ‌மைப்பை (configuration directive) மாற்ற‌வும்.',
  'You can upload a big SQL file via FTP and import it from server.',
  'வேற்று விசைக‌ள்',
  'கொலேச‌ன்',
  'ON UPDATE',
  'ON DELETE',
  'நெடுவ‌ரிசையின் பெய‌ர்',
  'அள‌புரு (Parameter) பெய‌ர்',
  'நீளம்',
  'வேண்டிய‌வ‌ற்றை ',
  'அடுத்த‌தை சேர்க்க‌வும்',
  'மேலே ந‌க‌ர்த்து',
  'கீழே நக‌ர்த்து',
  'நீக்கு',
  'த‌க‌வ‌ல்த‌ள‌ம் ச‌ரியானதல்ல‌.',
  'த‌க‌வ‌ல் த‌ள‌ங்க‌ள் நீக்க‌ப்ப‌ட்டன‌.',
  'த‌க‌வ‌ல்த‌ள‌த்தை தேர்வு செய்',
  'த‌க‌வ‌ல்த‌ள‌த்தை உருவாக்கு',
  'வேலைக‌ளின் ப‌ட்டி',
  'மாறிலிக‌ள் (Variables)',
  'நிக‌ழ்நிலை (Status)',
  '%s ப‌திப்பு: %s through PHP extension %s',
  'ப‌ய‌னாளர்: %s',
  'புதுப்பி (Refresh)',
  'கொலேச‌ன்',
  'அட்ட‌வ‌ணை',
  'Size',
  'Compute',
  'Selected',
  'நீக்கு',
  'Materialized view',
  'தோற்றம்',
  'அட்ட‌வ‌ணை',
  'அக‌வ‌ரிசைக‌ள் (Index) ',
  'அக‌வ‌ரிசையை (Index) மாற்று',
  'மூல‌ம்',
  'இல‌க்கு',
  'மாற்று',
  'வேற்று விசை சேர்க்க‌வும்',
  'தூண்டுத‌ல்க‌ள்',
  'தூண்டு விசையை சேர்',
  'நிரந்தர இணைப்பு',
  'வெளியீடு',
  'ஃபார்ம‌ட் (Format)',
  'ரொட்டீன் ',
  'நிக‌ழ்ச்சிக‌ள்',
  'த‌க‌வ‌ல்',
  'ப‌ய‌னாள‌ரை உருவாக்கு',
  'ATTACH queries are not supported.',
  'வின‌வ‌லில் த‌வ‌றுள்ள‌து',
  '%d / ',
  [
    '%d வ‌ரிசை',
    '%d வ‌ரிசைக‌ள்',
  ],
  [
    'வின‌வ‌ல் செய‌ல்ப‌டுத்த‌ப்ப‌ட்ட‌து, %d வ‌ரிசை மாற்ற‌ப்ப‌ட்ட‌து.',
    'வின‌வ‌ல் செய‌ல்ப‌டுத்த‌ப்ப‌ட்ட‌து, %d வ‌ரிசைக‌ள் மாற்றப்ப‌ட்ட‌ன‌.',
  ],
  'செய‌ல் ப‌டுத்த‌ எந்த‌ க‌ட்ட‌ளைக‌ளும் இல்லை.',
  [
    '%d வின‌வ‌ல் செய‌ல்ப‌டுத்த‌ப்ப‌ட்ட‌து.',
    '%d வின‌வ‌ல்க‌ள் செய‌ல்ப‌டுத்த‌ப்ப‌ட்ட‌ன‌.',
  ],
  'செய‌ல்ப‌டுத்து',
  'Limit rows',
  'கோப்பை மேலேற்று (upload) ',
  'கோப்புக‌ள் மேலேற்றம் (upload)முட‌க்க‌ப்ப‌ட்டுள்ள‌ன‌.',
  'செர்வ‌ரில் இருந்து',
  'வெப் ச‌ர்வ‌ர் கோப்பு %s',
  'கோப்பினை இய‌க்க‌வும்',
  'பிழை ஏற்ப‌டின் நிற்க‌',
  'பிழைக‌ளை ம‌ட்டும் காண்பிக்க‌வும்',
  'வ‌ர‌லாறு',
  'துடை (Clear)',
  'அனைத்தையும் தொகு',
  'உருப்படி நீக்க‌ப்ப‌ட்ட‌து.',
  'உருப்ப‌டி புதுப்பிக்க‌ப்ப‌ட்ட‌து.',
  'உருப்ப‌டி (Item) சேர்க்க‌ப்ப‌ட்ட‌து.',
  'அட்ட‌வ‌ணை நீக்க‌ப்ப‌ட்ட‌து.',
  'அட்ட‌வணை மாற்ற‌ப்ப‌ட்ட‌து.',
  'அட்ட‌வ‌ணை உருவாக்க‌ப்ப‌ட்ட‌து.',
  'அட்ட‌வ‌ணைப் பெய‌ர்',
  'எஞ்சின்',
  'உள்ளிருக்கும் (Default) ம‌திப்புக‌ள் ',
  'Drop %s?',
  'பிரித்த‌து',
  'பிரிவுக‌ள்',
  'பிரிவின் பெய‌ர்',
  'ம‌திப்புக‌ள்',
  'அக‌வ‌ரிசைக‌ள் (Indexes) மாற்ற‌ப்பட்ட‌து.',
  'அக‌வ‌ரிசை வ‌கை (Index Type)',
  'நெடுவ‌ரிசை (நீள‌ம்)',
  'பெய‌ர்',
  'த‌க‌வ‌ல்த‌ள‌ம் நீக்க‌ப்ப‌ட்ட‌து.',
  'த‌க‌வ‌ல்த‌ள‌ம் பெய‌ர் மாற்ற‌ப்ப‌ட்ட‌து.',
  'த‌க‌வ‌ல்த‌ள‌ம் உருவாக்க‌ப்ப‌ட்ட‌து.',
  'த‌க‌வ‌ல்த‌ள‌ம் மாற்ற‌ப்ப‌ட்ட‌து.',
  'அழை',
  [
    'ரொட்டீன்க‌ள் அழைக்க‌ப்பட்டுள்ள‌ன‌, %d வ‌ரிசை மாற்ற‌ம் அடைந்த‌து.',
    'ரொட்டீன்க‌ள் அழைக்க‌ப்ப‌ட்டுள்ள‌ன‌, %d வ‌ரிசைக‌ள் மாற்றம் அடைந்துள்ள‌ன‌.',
  ],
  'வேற்று விசை நீக்க‌ப்ப‌ட்ட‌து.',
  'வேற்று விசை மாற்ற‌ப்ப‌ட்ட‌து.',
  'வேற்று விசை உருவாக்க‌ப்ப‌ட்ட‌து.',
  'இல‌க்கு நெடுவ‌ரிசையில் அக‌வ‌ரிசை (Index) ம‌ற்றும் குறிக்க‌ப்ப‌ட்ட‌ த‌க‌வல் (Referenced DATA) க‌ண்டிப்பாக‌ இருத்த‌ல் வேண்டும். மூல‌ நெடுவ‌ரிசை ம‌ற்றும் இலக்கு நெடுவ‌ரிசையின் த‌க‌வ‌ல் வ‌டிவ‌ம் (DATA TYPE) ஒன்றாக‌ இருக்க‌ வேண்டும்.',
  'வேற்று விசை',
  'அட்ட‌வ‌ணை இல‌க்கு',
  'அமைப்புமுறை',
  'மாற்று',
  'நெடு வ‌ரிசையை சேர்க்க‌வும்',
  'தோற்றம் மாற்றப்ப‌ட்ட‌து.',
  'தோற்ற‌ம் நீக்க‌ப்ப‌ட்ட‌து.',
  'தோற்ற‌ம் உருவாக்க‌ப்ப‌ட்ட‌து.',
  'தோற்றத்தை உருவாக்கு',
  'நிக‌ழ்ச்சி (Event) நீக்க‌ப்ப‌ட்ட‌து.',
  'நிக‌ழ்ச்சி (Event) மாற்றப்ப‌ட்ட‌து.',
  'நிக‌ழ்ச்சி (Event) உருவாக்க‌‌ப்ப‌ட்ட‌து.',
  'நிக‌ழ்ச்சியை (Event) மாற்று',
  'நிக‌ழ்ச்சியை (Event) உருவாக்கு',
  'தொட‌ங்கு',
  'முடி (வு)',
  'ஒவ்வொரு',
  'முடிந்த‌தின் பின் பாதுகாக்க‌வும்',
  'ரொட்டீன் நீக்க‌ப்ப‌ட்ட‌து.',
  'ரொட்டீன் மாற்ற‌ப்ப‌ட்டது.',
  'ரொட்டீன் உருவாக்க‌ப்ப‌ட்ட‌து.',
  'Function மாற்று',
  'செய‌ல்முறையை மாற்று',
  'Function உருவாக்கு',
  'செய்முறையை உருவாக்கு',
  'திரும்பு வ‌கை',
  'தூண்டு விசை நீக்க‌ப்ப‌ட்ட‌து.',
  'தூண்டு விசை மாற்ற‌ப்ப‌ட்ட‌து.',
  'தூண்டு விசை உருவாக்க‌ப்ப‌ட்ட‌து.',
  'தூண்டு விசையை மாற்று',
  'தூண்டு விசையை உருவாக்கு',
  'நேர‌ம்',
  'நிக‌ழ்ச்சி',
  'ப‌யனீட்டாள‌ர் நீக்க‌ப்ப‌ட்டார்.',
  'ப‌யனீட்டாள‌ர் மாற்றப்ப‌ட்டார்.',
  'ப‌ய‌னீட்டாள‌ர் உருவாக்க‌ப்ப‌ட்ட‌து.',
  'Hashed',
  'ரொட்டீன்',
  'அனும‌திய‌ளி',
  'இர‌த்துச்செய்',
  [
    '%d வேலை வ‌லுவில் நிறுத்த‌ப‌ட்ட‌து.',
    '%d வேலைக‌ள் வ‌லுவில் நிறுத்த‌ப‌ட்ட‌ன‌.',
  ],
  'ந‌க‌லி (Clone)',
  'மொத்தம் %d ',
  'வ‌லுவில் நிறுத்து',
  [
    '%d உருப்ப‌டி மாற்ற‌ம‌டைந்தது.',
    '%d உருப்ப‌டிக‌ள் மாற்ற‌ம‌டைந்த‌ன‌.',
  ],
  'Ctrl+click on a value to modify it.',
  'File must be in UTF-8 encoding.',
  [
    '%d வ‌ரிசை இற‌க்கும‌தி (Import) செய்ய‌ப்ப‌ட்ட‌து.',
    '%d வ‌ரிசைக‌ள் இற‌க்கும‌தி (Import) செய்ய‌ப்ப‌ட்டன‌.',
  ],
  'அட்ட‌வ‌ணையை தேர்வு செய்ய‌ முடிய‌வில்லை',
  'Modify',
  'உற‌வுக‌ள் (Relations)',
  'தொகு',
  'இந்த‌ ம‌திப்பினை மாற்ற‌, தொகுப்பு இணைப்பினை உப‌யோகிக்க‌வும்.',
  'Load more data',
  'Loading',
  'ப‌க்க‌ம்',
  'க‌டைசி',
  'முழுமையான‌ முடிவு',
  'அட்ட‌வ‌ணை குறைக்க‌ப்ப‌ட்ட‌து (truncated).',
  'அட்ட‌வ‌ணை ந‌க‌ர்த்த‌ப்ப‌ட்ட‌து.',
  'அட்டவணைகள் நகலெடுக்கப் பட்டது.',
  'அட்ட‌வ‌ணை நீக்க‌ப்ப‌ட்ட‌து.',
  'Tables have been optimized.',
  'அட்ட‌வ‌ணைக‌ளும் பார்வைக‌ளும்',
  'த‌க‌வ‌லை அட்ட‌வ‌ணையில் தேடு',
  'எஞ்சின் (Engine)',
  'த‌க‌வ‌ல் நீள‌ம்',
  'Index நீள‌ம்',
  'Data Free',
  'வ‌ரிசைக‌ள்',
  'Vacuum',
  'உக‌ப்பாக்கு (Optimize)',
  'நுணுகி ஆராய‌வும்',
  'ப‌ரிசோதி',
  'ப‌ழுது பார்',
  'குறை (Truncate)',
  'ம‌ற்ற‌ த‌க‌வ‌ல் தள‌த்திற்க்கு ந‌க‌ர்த்து',
  'ந‌க‌ர்த்து',
  'நகல்',
  'overwrite',
  'கால‌ அட்ட‌வ‌ணை',
  'குறித்த‌ நேர‌த்தில்',
  'HH:MM:SS',
];
		case "th": return [
  'คุณแน่ใจแล้วหรือ',
  '%.3f วินาที',
  'ไม่สามารถอัปโหลดไฟล์ได้.',
  'ขนาดไฟล์สูงสุดที่อนุญาตให้ใช้งานคือ %sB.',
  'ไม่มีไฟล์.',
  ' ',
  '0123456789',
  'ว่างเปล่า',
  'ต้นฉบับ',
  'ไม่พบตาราง.',
  'แก้ไข',
  'เพิ่ม',
  'ไม่มีแถวของตาราง.',
  'You have no privileges to update this table.',
  'บันทึก',
  'บันทึกและแก้ไขข้อมูลอื่นๆต่อ',
  'บันทึกแล้วเพิ่มรายการถัดไป',
  'Saving',
  'ลบ',
  'ภาษา',
  'ใช้งาน',
  'Unknown error.',
  'ระบบ',
  'เซอเวอร์',
  'ชื่อผู้ใช้งาน',
  'รหัสผ่าน',
  'ฐานข้อมูล',
  'เข้าสู่ระบบ',
  'จดจำการเข้าสู่ระบบตลอดไป',
  'Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',
  'เลือกข้อมูล',
  'แสดงโครงสร้าง',
  'เปลี่ยนแปลงวิว',
  'เปลี่ยนแปลงตารางแล้ว',
  'รายการใหม่',
  'Warnings',
  '%d ไบท์',
  'คอลัมน์',
  'ชนิด',
  'หมายเหตุ',
  'เพิ่มลำดับโดยอัตโนมัติ',
  'Default value',
  'เลือก',
  'ฟังก์ชั่น',
  'รวบรวม',
  'ค้นหา',
  'ทุกแห่ง',
  'เรียงลำดับ',
  'มากไปน้อย',
  'จำกัด',
  'ความยาวของอักษร',
  'ดำเนินการ',
  'Full table scan',
  'คำสั่ง SQL',
  'เปิด',
  'บันทึก',
  'เปลี่ยนแปลงฐานข้อมูล',
  'เปลี่ยนแปลง schema',
  'สร้าง schema',
  'Schema ของฐานข้อมูล',
  'สิทธิ์',
  'นำเข้า',
  'ส่งออก',
  'สร้างตารางใหม่',
  'ฐานข้อมูล',
  'DB',
  'เลือก',
  'Disable %s or enable %s or %s extensions.',
  'ตัวอักษร',
  'ตัวเลข',
  'วันและเวลา',
  'รายการ',
  'เลขฐานสอง',
  'เรขาคณิต',
  'ltr',
  'You are offline.',
  'ออกจากระบบ',
  'Too many unsuccessful logins, try again in %d minute(s).',
  'ออกจากระบบเรียบร้อยแล้ว.',
  'Thanks for using Adminer, consider <a href="https://www.adminer.org/en/donation/">donating</a>.',
  'Session หมดอายุแล้ว กรุณาเข้าสู่ระบบใหม่อีกครั้ง.',
  'Master password expired. <a href="https://www.adminer.org/en/extension/"%s>Implement</a> %s method to make it permanent.',
  'ต้องเปิดใช้งาน Session.',
  'The action will be performed after successful login with the same credentials.',
  'ไม่พบส่วนเสริม',
  'ไม่มีส่วนเสริมของ PHP (%s) ที่สามารถใช้งานได้.',
  'Connecting to privileged ports is not allowed.',
  'ข้อมูลไม่ถูกต้อง.',
  'There is a space in the input password which might be the cause.',
  'เครื่องหมาย CSRF ไม่ถูกต้อง ส่งข้อมูลใหม่อีกครั้ง.',
  'จำนวนสูงสุดของฟิลด์อนุญาตให้เกิน กรุณาเพิ่มอีก %s.',
  'If you did not send this request from Adminer then close this page.',
  'ข้อมูลที่ส่งเข้ามีขนาดใหญ่เกิน คุณสามารถ เพิ่ม-ลดขนาดได้ที่ %s คำสั่งการตั้งค่า.',
  'You can upload a big SQL file via FTP and import it from server.',
  'คีย์คู่แข่ง',
  'การตรวจทาน',
  'ON UPDATE',
  'ON DELETE',
  'ชื่อคอลัมน์',
  'ชื่อพารามิเตอร์',
  'ความยาว',
  'ตัวเลือก',
  'เพิ่มรายการถัดไป',
  'ย้ายไปข้างบน',
  'ย้ายลงล่าง',
  'ลบ',
  'ฐานข้อมูลไม่ถูกต้อง.',
  'ฐานข้อมูลถูกลบแล้ว.',
  'เลือกฐานข้อมูล',
  'สร้างฐานข้อมูล',
  'รายการของกระบวนการ',
  'ตัวแปร',
  'สถานะ',
  '%s รุ่น: %s ผ่านส่วนขยาย PHP %s',
  'สวัสดีคุณ: %s',
  'โหลดใหม่',
  'การตรวจทาน',
  'ตาราง',
  'Size',
  'Compute',
  'Selected',
  'ลบ',
  'Materialized view',
  'วิว',
  'ตาราง',
  'ดัชนี',
  'เปลี่ยนแปลงดัชนี',
  'แหล่งข้อมูล',
  'เป้าหมาย',
  'เปลี่ยนแปลง',
  'เพิ่มคีย์คู่แข่ง',
  'ทริกเกอร์',
  'เพิ่ม trigger',
  'ลิงค์ถาวร',
  'ข้อมูลที่ส่งออก',
  'รูปแบบ',
  'รูทีน',
  'เหตุการณ์',
  'ข้อมูล',
  'สร้างผู้ใช้งาน',
  'ATTACH queries are not supported.',
  'คำสั่งไม่ถูกต้อง',
  '%d / ',
  '%d แถว',
  'ประมวลผลคำสั่งแล้ว มี %d ถูกดำเนินการ.',
  'ไม่มีคำสั่งที่จะประมวลผล.',
  '%d คำสั่งถูกดำเนินการแล้ว.',
  'ประมวลผล',
  'Limit rows',
  'อัปโหลดไฟล์',
  'การอัปโหลดไฟล์ถูกปิดการใช้งาน.',
  'จากเซเวอร์',
  'Webserver file %s',
  'ทำงานจากไฟล์',
  'หยุดการทำงานเมื่อเออเรอ',
  'แสดงเฉพาะเออเรอ',
  'ประวัติ',
  'เคลียร์',
  'แก้ไขทั้งหมด',
  'รายการถูกลบแล้ว.',
  'ปรับปรุงรายการแล้ว.',
  'มี%s รายการ ถูกเพิ่มแล้ว.',
  'ลบตารางแล้ว.',
  'แก้ไขตารางแล้ว.',
  'สร้างตารางใหม่แล้ว.',
  'ชื่อตาราง',
  'ชนิดของฐานข้อมูล',
  'ค่าเริ่มต้น',
  'Drop %s?',
  'พาร์ทิชันโดย',
  'พาร์ทิชัน',
  'ชื่อของพาร์ทิชัน',
  'ค่า',
  'เปลี่ยนแปลงดัชนีแล้ว.',
  'ชนิดของดัชนี',
  'คอลัมน์ (ความยาว)',
  'ชื่อ',
  'ฐานข้อมูลถูกลบแล้ว.',
  'เปลี่ยนชื่อฐานข้อมูลแล้ว.',
  'สร้างฐานข้อมูลใหม่แล้ว.',
  'เปลี่ยนแปลงฐานข้อมูลแล้ว.',
  'เรียกใช้งาน',
  'รูทีนถูกเรียกใช้งาน มี %d แถวถูกดำเนินการ.',
  'คีย์คู่แข่งถูกลบแล้ว.',
  'คีย์คู่แข่งถูกเปลี่ยนแปลงแล้ว.',
  'คีย์คู่แข่งถูกสร้างแล้ว.',
  'แหล่งที่มาและเป้าหมายของคอลมัน์ต้องมีชนิดข้อมูลเดียวกัน คือต้องมีดัชนีและข้อมูลอ้างอิงของคอลัมน์เป้าหมาย.',
  'คีย์คู่แข่ง',
  'คารางเป้าหมาย',
  'Schema',
  'แก้ไข',
  'เพิ่มคอลัมน์',
  'วิวถูกเปลี่ยนแปลงแล้ว.',
  'วิวถูกลบแล้ว.',
  'วิวถูกสร้างแล้ว.',
  'เพิ่มวิว',
  'เหตุการณ์ถูกลบแล้ว.',
  'เหตุการณ์ถูกเปลี่ยนแปลงแล้ว.',
  'เหตุการณ์ถูกสร้างแล้ว.',
  'เปลี่ยนแปลงเหตุการณ์',
  'สร้างเหตุการณ์',
  'เริ่มต้น',
  'สิ้นสุด',
  'ทุกๆ',
  'เมื่อเสร็จสิ้นการสงวน',
  'Routine ถูกลบแล้ว.',
  'Routine ถูกเปลี่ยนแปลงแล้ว.',
  'Routine ถูกสร้างแล้ว.',
  'เปลี่ยนแปลง Function',
  'เปลี่ยนแปลง procedure',
  'สร้าง Function',
  'สร้าง procedure',
  'ประเภทของค่าที่คืนกลับ',
  'Trigger ถูกลบแล้ว.',
  'Trigger ถูกเปลี่ยนแปลงแล้ว.',
  'Trigger ถูกสร้างแล้ว.',
  'เปลี่ยนแปลง Trigger',
  'สร้าง Trigger',
  'เวลา',
  'เหตุการณ์',
  'ลบผู้ใช้งานแล้ว.',
  'เปลี่ยนแปลงผู้ใช้งานแล้ว.',
  'สร้างผู้ใช้งานแล้ว.',
  'Hash',
  'รูทีน',
  'การอนุญาต',
  'ยกเลิก',
  'มี %d กระบวนการถูกทำลายแล้ว.',
  'ทำซ้ำ',
  '%d ของทั้งหมด',
  'ทำลาย',
  'มี %d รายการถูกดำเนินการแล้ว.',
  'กด Ctrl+click เพื่อแก้ไขค่า.',
  'File must be in UTF-8 encoding.',
  '%d แถวถูกนำเข้าแล้ว.',
  'ไม่สามารถเลือกตารางได้',
  'Modify',
  'ความสำพันธ์',
  'แก้ไข',
  'ใช้ลิงค์ แก้ไข เพื่อปรับเปลี่ยนค่านี้.',
  'Load more data',
  'Loading',
  'หน้า',
  'ล่าสุด',
  'รวมผล',
  'เคลียร์ตารางแล้ว (truncate).',
  'ตารางถูกย้ายแล้ว.',
  'ทำซ้ำตารางฐานข้อมูลแล้ว.',
  'ตารางถูกลบแล้ว.',
  'Tables have been optimized.',
  'ตารางและวิว',
  'ค้นหาในตาราง',
  'ชนิดของฐานข้อมูล',
  'ความยาวของข้อมูล',
  'ความยาวของดัชนี',
  'พื้นที่ว่าง',
  'แถว',
  'Vacuum',
  'เพิ่มประสิทธิภาพ',
  'วิเคราะห์',
  'ตรวจสอบ',
  'ซ่อมแซม',
  'ตัดทิ้ง',
  'ย้ายไปยังฐานข้อมูลอื่น',
  'ย้าย',
  'ทำซ้ำ',
  'overwrite',
  'กำหนดการณ์',
  'ในเวลาที่กำหนด',
  'HH:MM:SS',
];
		case "tr": return [
  'Emin misiniz?',
  '%.3f s',
  'Dosya gönderilemiyor.',
  'İzin verilen dosya boyutu sınırı %sB.',
  'Dosya mevcut değil.',
  ' ',
  '0123456789',
  'boş',
  'orijinal',
  'Tablo yok.',
  'Düzenle',
  'Ekle',
  'Kayıt yok.',
  'Bu tabloyu güncellemek için yetkiniz yok.',
  'Kaydet',
  'Kaydet ve düzenlemeye devam et',
  'Kaydet ve sonrakini ekle',
  'Saydediliyor',
  'Sil',
  'Dil',
  'Kullan',
  'Unknown error.',
  'Sistem',
  'Sunucu',
  'Kullanıcı',
  'Parola',
  'Veri Tabanı',
  'Giriş',
  'Beni hatırla',
  'Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',
  'Veri seç',
  'Yapıyı göster',
  'Görünümü değiştir',
  'Tabloyu değiştir',
  'Yeni kayıt',
  'Uyarılar',
  [
    '%d bayt',
    '%d bayt',
  ],
  'Kolon',
  'Tür',
  'Yorum',
  'Otomatik Artır',
  'Varsayılan değer',
  'Seç',
  'Fonksiyonlar',
  'Kümeleme',
  'Ara',
  'hiçbir yerde',
  'Sırala',
  'Azalan',
  'Limit',
  'Metin Boyutu',
  'İşlem',
  'Tam tablo taraması',
  'SQL komutu',
  'aç',
  'kaydet',
  'Veri tabanını değiştir',
  'Şemayı değiştir',
  'Şema oluştur',
  'Veri tabanı şeması',
  'İzinler',
  'İçeri Aktar',
  'Dışarı Aktar',
  'Tablo oluştur',
  'veri tabanı',
  'DB',
  'seç',
  'Disable %s or enable %s or %s extensions.',
  'Dizge',
  'Sayılar',
  'Tarih ve zaman',
  'Listeler',
  'İkili',
  'Geometri',
  'ltr',
  'Çevrimdışısınız.',
  'Çıkış',
  [
    'Çok fazla oturum açma denemesi yapıldı.',
    '%d Dakika sonra tekrar deneyiniz.',
  ],
  'Oturum başarıyla sonlandı.',
  'Adminer kullandığınız için teşekkür ederiz <a href="https://www.adminer.org/en/donation/">bağış yapmayı düşünün</a>.',
  'Oturum süresi doldu, lütfen tekrar giriş yapın.',
  'Ana şifrenin süresi doldu. Kalıcı olması için <a href="https://www.adminer.org/en/extension/"%s>%s medodunu</a> kullanın.',
  'Oturum desteği etkin olmalıdır.',
  'İşlem, aynı kimlik bilgileriyle başarıyla oturum açıldıktan sonra gerçekleştirilecektir.',
  'Uzantı yok',
  'Desteklenen PHP eklentilerinden (%s) hiçbiri mevcut değil.',
  'Ayrıcalıklı bağlantı noktalarına bağlanmaya izin verilmiyor.',
  'Geçersiz kimlik bilgileri.',
  'There is a space in the input password which might be the cause.',
  'Geçersiz (CSRF) jetonu. Formu tekrar yolla.',
  'İzin verilen en fazla alan sayısı aşıldı. Lütfen %s değerlerini artırın.',
  'Bu isteği Adminer\'den göndermediyseniz bu sayfayı kapatın.',
  'Çok büyük POST verisi, veriyi azaltın ya da %s ayar yönergesini uygun olarak yapılandırın.',
  'FTP yoluyla büyük bir SQL dosyası yükleyebilir ve sunucudan içe aktarabilirsiniz.',
  'Dış anahtarlar',
  'karşılaştırma',
  'ON UPDATE (Hedefteki Kayıt Değiştirilirse)',
  'ON DELETE (Hedefteki Kayıt Silinirse)',
  'Kolon adı',
  'Parametre adı',
  'Uzunluk',
  'Seçenekler',
  'Bundan sonra ekle',
  'Yukarı taşı',
  'Aşağı taşı',
  'Sil',
  'Geçersiz veri tabanı.',
  'Veritabanları silindi.',
  'Veri tabanı seç',
  'Veri tabanı oluştur',
  'İşlem listesi',
  'Değişkenler',
  'Durum',
  '%s sürüm: %s, %s PHP eklentisi ile',
  '%s olarak giriş yapıldı.',
  'Tazele',
  'Karşılaştırma',
  'Tablolar',
  'Boyut',
  'Hesapla',
  'Seçildi',
  'Sil',
  'Materialized Görünüm',
  'Görünüm',
  'Tablo',
  'İndeksler',
  'İndeksleri değiştir',
  'Kaynak',
  'Hedef',
  'Değiştir',
  'Dış anahtar ekle',
  'Tetikler',
  'Tetik ekle',
  'Kalıcı bağlantı',
  'Çıktı',
  'Biçim',
  'Yordamlar',
  'Olaylar',
  'Veri',
  'Kullanıcı oluştur',
  'ATTACH sorguları desteklenmiyor.',
  'Sorguda hata',
  '%d / ',
  [
    '%d kayıt',
    '%d adet kayıt',
  ],
  [
    'Sorgu başarıyla çalıştırıldı, %d adet kayıt etkilendi.',
    'Sorgu başarıyla çalıştırıldı, %d adet kayıt etkilendi.',
  ],
  'Çalıştırılacak komut yok.',
  [
    '%d sorgu başarıyla çalıştırıldı.',
    '%d adet sorgu başarıyla çalıştırıldı.',
  ],
  'Çalıştır',
  'Satır Limiti',
  'Dosya gönder',
  'Dosya gönderimi etkin değil.',
  'Sunucudan',
  '%s web sunucusu dosyası',
  'Dosyayı çalıştır',
  'Hata oluşursa dur',
  'Sadece hataları göster.',
  'Geçmiş',
  'Temizle',
  'Tümünü düzenle',
  'Kayıt silindi.',
  'Kayıt güncellendi.',
  'Kayıt%s eklendi.',
  'Tablo silindi.',
  'Tablo değiştirildi.',
  'Tablo oluşturuldu.',
  'Tablo adı',
  'motor',
  'Varsayılan değerler',
  'Sil %s?',
  'Bununla bölümle',
  'Bölümler',
  'Bölüm adı',
  'Değerler',
  'İndeksler değiştirildi.',
  'İndex Türü',
  'Kolon (uzunluğu)',
  'Ad',
  'Veri tabanı silindi.',
  'Veri tabanının ismi değiştirildi.',
  'Veri tabanı oluşturuldu.',
  'Veri tabanı değiştirildi.',
  'Çağır',
  [
    'Yordam çağrıldı, %d adet kayıt etkilendi.',
    'Yordam çağrıldı, %d kayıt etkilendi.',
  ],
  'Dış anahtar silindi.',
  'Dış anahtar değiştirildi.',
  'Dış anahtar oluşturuldu.',
  'Kaynak ve hedef kolonlar aynı veri türünde olmalı, hedef kolonlarda dizin bulunmalı ve başvurulan veri mevcut olmalı.',
  'Dış anahtar',
  'Hedef tablo',
  'Şema',
  'Değiştir',
  'Kolon ekle',
  'Görünüm değiştirildi.',
  'Görünüm silindi.',
  'Görünüm oluşturuldu.',
  'Görünüm oluştur',
  'Olay silindi.',
  'Olay değiştirildi.',
  'Olay oluşturuldu.',
  'Olayı değiştir',
  'Olay oluştur',
  'Başla',
  'Son',
  'Her zaman',
  'Tamamlama koruması',
  'Yordam silindi.',
  'Yordam değiştirildi.',
  'Yordam oluşturuldu.',
  'Fonksyionu değiştir',
  'Yöntemi değiştir',
  'Fonksiyon oluştur',
  'Yöntem oluştur',
  'Geri dönüş türü',
  'Tetik silindi.',
  'Tetik değiştirildi.',
  'Tetik oluşturuldu.',
  'Tetiği değiştir.',
  'Tetik oluştur',
  'Zaman',
  'Olay',
  'Kullanıcı silindi.',
  'Kullanıcı değiştirildi.',
  'Kullanıcı oluşturuldu.',
  'Harmanlandı',
  'Yordam',
  'Yetki Ver',
  'Yetki Kaldır',
  [
    '%d işlem sonlandırıldı.',
    '%d adet işlem sonlandırıldı.',
  ],
  'Kopyala',
  'toplam %d',
  'Sonlandır',
  [
    '%d kayıt etkilendi.',
    '%d adet kayıt etkilendi.',
  ],
  'Bir değeri değiştirmek için üzerine Ctrl+tıklayın.',
  'Dosya UTF-8 kodlamasında olmalıdır.',
  [
    '%d kayıt içeri aktarıldı.',
    '%d adet kayıt içeri aktarıldı.',
  ],
  'Tablo seçilemedi',
  'Düzenle',
  'İlişkiler',
  'düzenle',
  'Değeri değiştirmek için düzenleme bağlantısını kullanın.',
  'Daha fazla veri yükle',
  'Yükleniyor',
  'Sayfa',
  'son',
  'Tüm sonuç',
  'Tablolar boşaltıldı.',
  'Tablolar taşındı.',
  'Tablolar kopyalandı.',
  'Tablolar silindi.',
  'Tablolar en uygun hale getirildi.',
  'Tablolar ve görünümler',
  'Tablolarda veri ara',
  'Motor',
  'Veri Uzunluğu',
  'İndex Uzunluğu',
  'Boş Veri',
  'Kayıtlar',
  'Vakumla',
  'Optimize Et',
  'Çözümle',
  'Denetle',
  'Tamir Et',
  'Boşalt',
  'Başka veri tabanına taşı',
  'Taşı',
  'Kopyala',
  'overwrite',
  'Takvimli',
  'Verilen zamanda',
  'Türü değiştir',
];
		case "uk": return [
  'Ви впевнені?',
  '%.3f s',
  'Неможливо завантажити файл.',
  'Максимально допустимий розмір файлу %sБ.',
  'Файл не існує.',
  ' ',
  '0123456789',
  'порожньо',
  'початковий',
  'Нема таблиць.',
  'Редагувати',
  'Вставити',
  'Нема рядків.',
  'Ви не маєте привілеїв для оновлення цієї таблиці.',
  'Зберегти',
  'Зберегти і продовжити редагування',
  'Зберегти і вставити знову',
  'Збереження',
  'Видалити',
  'Мова',
  'Обрати',
  'Невідома помилка.',
  'Система Бази Даних',
  'Сервер',
  'Користувач',
  'Пароль',
  'База даних',
  'Увійти',
  'Пам\'ятати сесію',
  'Adminer не підтримує доступ до бази даних без пароля, <a href="https://www.adminer.org/en/password/"%s>більше інформації</a>.',
  'Вибрати дані',
  'Показати структуру',
  'Змінити вигляд',
  'Змінити таблицю',
  'Новий запис',
  'Попередження',
  [
    '%d байт',
    '%d байта',
    '%d байтів',
  ],
  'Колонка',
  'Тип',
  'Коментарі',
  'Автоматичне збільшення',
  'Значення за замовчуванням',
  'Вибрати',
  'Функції',
  'Агрегація',
  'Пошук',
  'будь-де',
  'Сортувати',
  'по спаданню',
  'Обмеження',
  'Довжина тексту',
  'Дія',
  'Повне сканування таблиці',
  'SQL запит',
  'відкрити',
  'зберегти',
  'Змінити базу даних',
  'Змінити схему',
  'Створити схему',
  'Схема бази даних',
  'Привілеї',
  'Імпортувати',
  'Експорт',
  'Створити таблицю',
  'база даних',
  'DB',
  'вибрати',
  'Disable %s or enable %s or %s extensions.',
  'Рядки',
  'Числа',
  'Дата і час',
  'Списки',
  'Двійкові',
  'Геометрія',
  'ltr',
  'Ви офлайн.',
  'Вийти',
  [
    'Занадто багато невдалих спроб входу. Спробуйте знову через %d хвилину.',
    'Занадто багато невдалих спроб входу. Спробуйте знову через %d хвилини.',
    'Занадто багато невдалих спроб входу. Спробуйте знову через %d хвилин.',
  ],
  'Ви вдало вийшли з системи.',
  'Дякуємо, що користуєтесь Adminer, подумайте про <a href="https://www.adminer.org/en/donation/">внесок</a>.',
  'Сесія закінчилась, будь ласка, увійдіть в систему знову.',
  'Термін дії майстер пароля минув. <a href="https://www.adminer.org/en/extension/"%s>Реалізуйте</a> метод %s, щоб зробити його постійним.',
  'Сесії повинні бути дозволені.',
  'Дія буде виконуватися після успішного входу в систему з тими ж обліковими даними.',
  'Нема розширень',
  'Жодне з PHP-розширень (%s), що підтримуються, не доступне.',
  'Підключення до привілейованих портів заборонено.',
  'Неправильні дані входу.',
  'У вхідному паролі є пробіл, який може бути причиною.',
  'Недійсний CSRF токен. Надішліть форму ще раз.',
  'Досягнута максимальна кількість доступних полів. Будь ласка, збільшіть %s.',
  'Якщо ви не посилали цей запит з Adminer, закрийте цю сторінку.',
  'Занадто великий об\'єм POST-даних. Зменшіть об\'єм або збільшіть параметр директиви %s конфигурації.',
  'Ви можете завантажити великий файл SQL через FTP та імпортувати його з сервера.',
  'Зовнішні ключі',
  'співставлення',
  'ПРИ ЗМІНІ',
  'ПРИ ВИДАЛЕННІ',
  'Назва стовпця',
  'Назва параметра',
  'Довжина',
  'Опції',
  'Додати ще',
  'Пересунути вгору',
  'Пересунути вниз',
  'Видалити',
  'Погана база даних.',
  'Бази даних були видалені.',
  'Обрати базу даних',
  'Створити базу даних',
  'Перелік процесів',
  'Змінні',
  'Статус',
  'Версія %s: %s з PHP-розширенням %s',
  'Ви увійшли як: %s',
  'Оновити',
  'Співставлення',
  'Таблиці',
  'Розмір',
  'Обчислити',
  'Вибрані',
  'Видалити',
  'Матеріалізований вигляд',
  'Вигляд',
  'Таблиця',
  'Індекси',
  'Змінити індексування',
  'Джерело',
  'Ціль',
  'Змінити',
  'Додати зовнішній ключ',
  'Тригери',
  'Додати тригер',
  'Постійне посилання',
  'Вихідні дані',
  'Формат',
  'Збережені процедури',
  'Події',
  'Дані',
  'Створити користувача',
  'ATTACH-запити не підтримуються.',
  'Помилка в запиті',
  '%d / ',
  [
    '%d рядок',
    '%d рядки',
    '%d рядків',
  ],
  [
    'Запит виконано успішно, змінено %d рядок.',
    'Запит виконано успішно, змінено %d рядки.',
    'Запит виконано успішно, змінено %d рядків.',
  ],
  'Нема запитів до виконання.',
  [
    '%d запит виконано успішно.',
    '%d запити виконано успішно.',
    '%d запитів виконано успішно.',
  ],
  'Виконати',
  'Обмеження рядків',
  'Завантажити файл',
  'Завантаження файлів заборонене.',
  'З сервера',
  'Файл %s на вебсервері',
  'Запустити файл',
  'Зупинитись при помилці',
  'Показувати тільки помилки',
  'Історія',
  'Очистити',
  'Редагувати все',
  'Запис було видалено.',
  'Запис було змінено.',
  'Запис%s було вставлено.',
  'Таблицю було видалено.',
  'Таблица була змінена.',
  'Таблиця була створена.',
  'Назва таблиці',
  'рушій',
  'Значення за замовчуванням',
  'Вилучити %s?',
  'Розділити по',
  'Розділи',
  'Назва розділу',
  'Значення',
  'Індексування було змінено.',
  'Тип індексу',
  'Стовпець (довжина)',
  'Назва',
  'Базу даних було видалено.',
  'Базу даних було переіменовано.',
  'Базу даних було створено.',
  'Базу даних було змінено.',
  'Викликати',
  [
    'Була викликана процедура, %d запис було змінено.',
    'Була викликана процедура, %d записи було змінено.',
    'Була викликана процедура, %d записів було змінено.',
  ],
  'Зовнішній ключ було видалено.',
  'Зовнішній ключ було змінено.',
  'Зовнішній ключ було створено.',
  'Стовпці повинні мати той самий тип даних, цільові стовпці повинні бути проіндексовані і дані, на які посилаються повинні існувати.',
  'Зовнішній ключ',
  'Цільова таблиця',
  'Схема',
  'Змінити',
  'Додати стовпець',
  'Вигляд було змінено.',
  'Вигляд було видалено.',
  'Вигляд було створено.',
  'Створити вигляд',
  'Подію було видалено.',
  'Подію було змінено.',
  'Подію було створено.',
  'Змінити подію',
  'Створити подію',
  'Початок',
  'Кінець',
  'Кожного',
  'Після завершення зберегти',
  'Процедуру було видалено.',
  'Процедуру було змінено.',
  'Процедуру було створено.',
  'Змінити функцію',
  'Змінити процедуру',
  'Створити функцію',
  'Створити процедуру',
  'Тип, що повернеться',
  'Тригер було видалено.',
  'Тригер було змінено.',
  'Тригер було створено.',
  'Змінити тригер',
  'Створити тригер',
  'Час',
  'Подія',
  'Користувача було видалено.',
  'Користувача було змінено.',
  'Користувача було створено.',
  'Хешовано',
  'Процедура',
  'Дозволити',
  'Заборонити',
  [
    'Було завершено %d процес.',
    'Було завершено %d процеси.',
    'Було завершёно %d процесів.',
  ],
  'Клонувати',
  '%d всього',
  'Завершити процес',
  [
    'Було змінено %d запис.',
    'Було змінено %d записи.',
    'Було змінено %d записів.',
  ],
  'Ctrl+клікніть на значенні щоб змінити його.',
  'Файл повинен бути в кодуванні UTF-8.',
  [
    '%d рядок було імпортовано.',
    '%d рядки було імпортовано.',
    '%d рядків було імпортовано.',
  ],
  'Неможливо вибрати таблицю',
  'Змінити',
  'Зв\'язки',
  'редагувати',
  'Використовуйте посилання щоб змінити це значення.',
  'Завантажити ще дані',
  'Завантаження',
  'Сторінка',
  'остання',
  'Весь результат',
  'Таблиці було очищено.',
  'Таблиці було перенесено.',
  'Таблиці було зкопійовано.',
  'Таблиці були видалені.',
  'Таблиці були оптимізовані.',
  'Таблиці і вигляди',
  'Шукати дані в таблицях',
  'Рушій',
  'Об\'єм даних',
  'Об\'єм індексів',
  'Вільне місце',
  'Рядків',
  'Vacuum',
  'Оптимізувати',
  'Аналізувати',
  'Перевірити',
  'Виправити',
  'Очистити',
  'Перенести до іншої бази даних',
  'Перенести',
  'копіювати',
  'перезаписати',
  'Розклад',
  'В даний час',
  'База даних не підтримує пароль.',
];
		case "vi": return [
  'Bạn có chắc',
  '%.3f s',
  'Không thể tải tệp lên.',
  'Kích thước tệp tối đa là %sB.',
  'Tệp không tồn tại.',
  ',',
  '0123456789',
  'trống',
  'bản gốc',
  'Không có bảng nào.',
  'Sửa',
  'Thêm',
  'Không có dòng dữ liệu nào.',
  'Bạn không có quyền sửa bảng này.',
  'Lưu',
  'Lưu và tiếp tục sửa',
  'Lưu và thêm tiếp',
  'Saving',
  'Xoá',
  'Ngôn ngữ',
  'Sử dụng',
  'Unknown error.',
  'Hệ thống',
  'Máy chủ',
  'Tên người dùng',
  'Mật khẩu',
  'Cơ sở dữ liệu',
  'Đăng nhập',
  'Giữ đăng nhập một thời gian',
  'Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',
  'Xem dữ liệu',
  'Hiện cấu trúc',
  'Sửa khung nhìn',
  'Sửa bảng',
  'Thêm',
  'Warnings',
  '%d byte(s)',
  'Cột',
  'Loại',
  'Chú thích',
  'Tăng tự động',
  'Default value',
  'Xem',
  'Các chức năng',
  'Tổng hợp',
  'Tìm kiếm',
  'bất cứ đâu',
  'Sắp xếp',
  'giảm dần',
  'Giới hạn',
  'Chiều dài văn bản',
  'Hành động',
  'Quét toàn bộ bảng',
  'Câu lệnh SQL',
  'xem',
  'lưu',
  'Thay đổi CSDL',
  'Thay đổi schema',
  'Tạo schema',
  'Cấu trúc CSDL',
  'Quyền truy cập',
  'Nhập khẩu',
  'Xuất',
  'Tạo bảng',
  'cơ sở dữ liệu',
  'DB',
  'xem',
  'Disable %s or enable %s or %s extensions.',
  'Chuỗi',
  'Số',
  'Ngày giờ',
  'Danh sách',
  'Mã máy',
  'Toạ độ',
  'ltr',
  'You are offline.',
  'Thoát',
  'Bạn gõ sai tài khoản quá nhiều lần, hãy thử lại sau %d phút nữa.',
  'Đã thoát xong.',
  'Thanks for using Adminer, consider <a href="https://www.adminer.org/en/donation/">donating</a>.',
  'Phiên làm việc đã hết, hãy đăng nhập lại.',
  'Mật khẩu đã hết hạn. <a href="https://www.adminer.org/en/extension/"%s>Thử cách làm</a> để giữ cố định.',
  'Cần phải bật session.',
  'The action will be performed after successful login with the same credentials.',
  'Không có phần mở rộng',
  'Bản cài đặt PHP thiếu hỗ trợ cho %s.',
  'Connecting to privileged ports is not allowed.',
  'Tài khoản sai.',
  'There is a space in the input password which might be the cause.',
  'Mã kiểm tra CSRF sai, hãy nhập lại biểu mẫu.',
  'Thiết lập %s cần tăng thêm. (Đã vượt giới hạnố trường tối đa cho phép trong một biểu mẫu).',
  'If you did not send this request from Adminer then close this page.',
  'Dữ liệu tải lên/POST quá lớn. Hãy giảm kích thước tệp hoặc tăng cấu hình (hiện tại %s).',
  'Bạn có thể tải tệp lên dùng FTP và nhập vào cơ sở dữ liệu.',
  'Các khoá ngoại',
  'bảng mã',
  'Khi cập nhật',
  'Khi xoá',
  'Tên cột',
  'Tham số',
  'Độ dài',
  'Tuỳ chọn',
  'Thêm tiếp',
  'Chuyển lên trên',
  'Chuyển xuống dưới',
  'Xoá',
  'CSDL sai.',
  'Các CSDL đã bị xoá.',
  'Chọn CSDL',
  'Tạo CSDL',
  'Danh sách tiến trình',
  'Biến',
  'Trạng thái',
  'Phiên bản %s: %s (PHP extension: %s)',
  'Vào dưới tên: %s',
  'Làm mới',
  'Bộ mã',
  'Các bảng',
  'Kích thước',
  'Tính',
  'Chọn',
  'Xoá',
  'Materialized view',
  'Khung nhìn',
  'Bảng',
  'Chỉ mục',
  'Sửa chỉ mục',
  'Nguồn',
  'Đích',
  'Sửa',
  'Thêm khoá ngoại',
  'Phản xạ',
  'Thêm phản xạ',
  'Liên kết cố định',
  'Kết quả',
  'Định dạng',
  'Routines',
  'Sự kiện',
  'Dữ liệu',
  'Tạo người dùng',
  'ATTACH queries are not supported.',
  'Có lỗi trong câu lệnh',
  '%d / ',
  '%s dòng',
  'Đã thực hiện xong, ảnh hưởng đến %d dòng.',
  'Chẳng có gì để thực hiện!.',
  '%d câu lệnh đã chạy thành công.',
  'Thực hiện',
  'Limit rows',
  'Tải tệp lên',
  'Chức năng tải tệp lên đã bị cấm.',
  'Dùng tệp trên máy chủ',
  'Tệp trên máy chủ',
  'Chạy tệp',
  'Dừng khi có lỗi',
  'Chỉ hiện lỗi',
  'Lịch sử',
  'Xoá',
  'Sửa tất cả',
  'Đã xoá.',
  'Đã cập nhật.',
  'Đã thêm%s.',
  'Bảng đã bị xoá.',
  'Bảng đã thay đổi.',
  'Bảng đã được tạo.',
  'Tên bảng',
  'cơ chế lưu trữ',
  'Giá trị mặc định',
  'Drop %s?',
  'Phân chia bằng',
  'Phân hoạch',
  'Tên phân hoạch',
  'Giá trị',
  'Chỉ mục đã được sửa.',
  'Loại chỉ mục',
  'Cột (độ dài)',
  'Tên',
  'CSDL đã bị xoá.',
  'Đã đổi tên CSDL.',
  'Đã tạo CSDL.',
  'Đã thay đổi CSDL.',
  'Gọi',
  'Đã chạy routine, thay đổi %d dòng.',
  'Khoá ngoại đã bị xoá.',
  'Khoá ngoại đã được sửa.',
  'Khoá ngoại đã được tạo.',
  'Cột gốc và cột đích phải cùng kiểu, phải đặt chỉ mục trong cột đích và dữ liệu tham chiếu phải tồn tại.',
  'Khoá ngoại',
  'Bảng đích',
  'Schema',
  'Thay đổi',
  'Thêm cột',
  'Khung nhìn đã được sửa.',
  'Khung nhìn đã bị xoá.',
  'Khung nhìn đã được tạo.',
  'Tạo khung nhìn',
  'Đã xoá sự kiện.',
  'Đã thay đổi sự kiện.',
  'Đã tạo sự kiện.',
  'Sửa sự kiện',
  'Tạo sự kiện',
  'Bắt đầu',
  'Kết thúc',
  'Mỗi',
  'Khi kết thúc, duy trì',
  'Đã xoá routine.',
  'Đã thay đổi routine.',
  'Đã tạo routine.',
  'Thay đổi hàm',
  'Thay đổi thủ tục',
  'Tạo hàm',
  'Tạo lệnh',
  'Giá trị trả về',
  'Đã xoá phản xạ.',
  'Đã sửa phản xạ.',
  'Đã tạo phản xạ.',
  'Sửa phản xạ',
  'Tạo phản xạ',
  'Thời gian',
  'Sự kiện',
  'Đã xoá người dùng.',
  'Đã sửa người dùng.',
  'Đã tạo người dùng.',
  'Mã hoá',
  'Hàm tích hợp',
  'Cấp quyền',
  'Tước quyền',
  '%d tiến trình đã dừng.',
  'Sao chép',
  '%s',
  'Dừng',
  '%d phần đã thay đổi.',
  'Nhấn Ctrl và bấm vào giá trị để sửa.',
  'Tệp phải mã hoá bằng chuẩn UTF-8.',
  'Đã nhập % dòng dữ liệu.',
  'Không thể xem dữ liệu',
  'Sửa',
  'Quan hệ',
  'sửa',
  'Dùng nút sửa để thay đổi giá trị này.',
  'Xem thêm dữ liệu',
  'Đang nạp',
  'trang',
  'cuối',
  'Toàn bộ kết quả',
  'Bảng đã bị làm rỗng.',
  'Bảng.',
  'Bảng đã được sao chép.',
  'Các bảng đã bị xoá.',
  'Bảng đã được tối ưu.',
  'Bảng và khung nhìn',
  'Tìm kiếm dữ liệu trong các bảng',
  'Cơ chế lưu trữ',
  'Kích thước dữ liệu',
  'Kích thước chỉ mục',
  'Dữ liệu trống',
  'Số dòng',
  'Dọn dẹp',
  'Tối ưu',
  'Phân tích',
  'Kiểm tra',
  'Sửa chữa',
  'Làm rỗng',
  'Chuyển tới cơ sở dữ liệu khác',
  'Chuyển đi',
  'Sao chép',
  'overwrite',
  'Đặt lịch',
  'Vào thời gian xác định',
  'Sửa kiểu dữ liệu',
];
		case "zh": return [
  '您确定吗？',
  '%.3f 秒',
  '不能上传文件。',
  '最多允许的文件大小为 %sB。',
  '文件不存在。',
  ',',
  '0123456789',
  '空',
  '原始',
  '没有表。',
  '编辑',
  '插入',
  '无数据。',
  '您没有权限更新这个表。',
  '保存',
  '保存并继续编辑',
  '保存并插入下一个',
  '保存中',
  '删除',
  '语言',
  '使用',
  '未知错误。',
  '系统',
  '服务器',
  '用户名',
  '密码',
  '数据库',
  '登录',
  '保持登录',
  'Adminer默认不支持访问没有密码的数据库，<a href="https://www.adminer.org/en/password/"%s>详情见这里</a>.',
  '选择数据',
  '显示结构',
  '修改视图',
  '修改表',
  '新建数据',
  '警告',
  '%d 字节',
  '列',
  '类型',
  '注释',
  '自动增量',
  '默认值',
  '选择',
  '函数',
  '集合',
  '搜索',
  '任意位置',
  '排序',
  '降序',
  '范围',
  '文本显示限制',
  '动作',
  '全表扫描',
  'SQL命令',
  '打开',
  '保存',
  '修改数据库',
  '修改模式',
  '创建模式',
  '数据库概要',
  '权限',
  '导入',
  '导出',
  '创建表',
  '数据库',
  '数据库',
  '选择',
  '禁用 %s 或启用 %s 或 %s 扩展。',
  '字符串',
  '数字',
  '日期时间',
  '列表',
  '二进制',
  '几何图形',
  'ltr',
  '您离线了。',
  '登出',
  '登录失败次数过多，请 %d 分钟后重试。',
  '成功登出。',
  '感谢使用Adminer，请考虑为我们<a href="https://www.adminer.org/en/donation/">捐款（英文页面）</a>.',
  '会话已过期，请重新登录。',
  '主密码已过期。<a href="https://www.adminer.org/en/extension/"%s>请扩展</a> %s 方法让它永久化。',
  '必须启用会话支持。',
  '此操作将在成功使用相同的凭据登录后执行。',
  '没有扩展',
  '没有支持的 PHP 扩展可用（%s）。',
  '不允许连接到特权端口。',
  '无效凭据。',
  '您输入的密码中有一个空格，这可能是导致问题的原因。',
  '无效 CSRF 令牌。请重新发送表单。',
  '超过最多允许的字段数量。请增加 %s。',
  '如果您并没有从Adminer发送请求，请关闭此页面。',
  'POST 数据太大。请减少数据或者增加 %s 配置命令。',
  '您可以通过FTP上传大型SQL文件并从服务器导入。',
  '外键',
  '校对',
  'ON UPDATE',
  'ON DELETE',
  '字段名',
  '参数名',
  '长度',
  '选项',
  '下一行插入',
  '上移',
  '下移',
  '移除',
  '无效数据库。',
  '已删除数据库。',
  '选择数据库',
  '创建数据库',
  '进程列表',
  '变量',
  '状态',
  '%s 版本：%s， 使用PHP扩展 %s',
  '登录用户：%s',
  '刷新',
  '校对',
  '表',
  '大小',
  '计算',
  '已选中',
  '删除',
  '物化视图',
  '视图',
  '表',
  '索引',
  '修改索引',
  '源',
  '目标',
  '修改',
  '添加外键',
  '触发器',
  '创建触发器',
  '固定链接',
  '输出',
  '格式',
  '子程序',
  '事件',
  '数据',
  '创建用户',
  '不支持ATTACH查询。',
  '查询出错',
  '%d / ',
  '%d 行',
  '查询执行完毕，%d 行受影响。',
  '没有命令被执行。',
  '%d 条查询已成功执行。',
  '执行',
  '限制行数',
  '文件上传',
  '文件上传被禁用。',
  '来自服务器',
  'Web服务器文件 %s',
  '运行文件',
  '出错时停止',
  '仅显示错误',
  '历史',
  '清除',
  '编辑全部',
  '已删除项目。',
  '已更新项目。',
  '已插入项目%s。',
  '已删除表。',
  '已修改表。',
  '已创建表。',
  '表名',
  '引擎',
  '默认值',
  '删除 %s?',
  '分区类型',
  '分区',
  '分区名',
  '值',
  '已修改索引。',
  '索引类型',
  '列（长度）',
  '名称',
  '已删除数据库。',
  '已重命名数据库。',
  '已创建数据库。',
  '已修改数据库。',
  '调用',
  '子程序被调用，%d 行被影响。',
  '已删除外键。',
  '已修改外键。',
  '已创建外键。',
  '源列和目标列必须具有相同的数据类型，在目标列上必须有一个索引并且引用的数据必须存在。',
  '外键',
  '目标表',
  '模式',
  '修改',
  '增加列',
  '已修改视图。',
  '已删除视图。',
  '已创建视图。',
  '创建视图',
  '已删除事件。',
  '已修改事件。',
  '已创建事件。',
  '修改事件',
  '创建事件',
  '开始',
  '结束',
  '每',
  '完成后仍保留',
  '已删除子程序。',
  '已修改子程序。',
  '已创建子程序。',
  '修改函数',
  '修改过程',
  '创建函数',
  '创建过程',
  '返回类型',
  '已删除触发器。',
  '已修改触发器。',
  '已创建触发器。',
  '修改触发器',
  '创建触发器',
  '时间',
  '事件',
  '已删除用户。',
  '已修改用户。',
  '已创建用户。',
  'Hashed',
  '子程序',
  '授权',
  '废除',
  '%d 个进程被终止',
  '复制',
  '共计 %d',
  '终止',
  '%d 个项目受到影响。',
  '按住Ctrl并单击某个值进行修改。',
  '文件必须使用UTF-8编码。',
  '%d 行已导入。',
  '不能选择该表',
  '修改',
  '关联信息',
  '编辑',
  '使用编辑链接修改该值。',
  '加载更多数据',
  '加载中',
  '页面',
  '最后',
  '所有结果',
  '已清空表。',
  '已转移表。',
  '已复制表。',
  '已删除表。',
  '已优化表。',
  '表和视图',
  '在表中搜索数据',
  '引擎',
  '数据长度',
  '索引长度',
  '数据空闲',
  '行数',
  '整理（Vacuum）',
  '优化',
  '分析',
  '检查',
  '修复',
  '清空',
  '转移到其它数据库',
  '转移',
  '复制',
  '覆盖',
  '调度',
  '在指定时间',
  '修改类型',
];
		case "zh-tw": return [
  '你確定嗎？',
  '%.3f 秒',
  '無法上傳檔案。',
  '允許的檔案上限大小為 %sB',
  '檔案不存在',
  ',',
  '0123456789',
  '空值',
  '原始',
  '沒有資料表。',
  '編輯',
  '新增',
  '沒有資料行。',
  '您沒有許可權更新這個資料表。',
  '儲存',
  '儲存並繼續編輯',
  '儲存並新增下一筆',
  '保存中',
  '刪除',
  '語言',
  '使用',
  '未知錯誤。',
  '資料庫系統',
  '伺服器',
  '帳號',
  '密碼',
  '資料庫',
  '登入',
  '永久登入',
  'Adminer預設不支援訪問沒有密碼的資料庫，<a href="https://www.adminer.org/en/password/"%s>詳情見這裡</a>.',
  '選擇資料',
  '顯示結構',
  '修改檢視表',
  '修改資料表',
  '新增項目',
  '警告',
  '%d byte(s)',
  '欄位',
  '類型',
  '註解',
  '自動遞增',
  '預設值',
  '選擇',
  '函式',
  '集合',
  '搜尋',
  '任意位置',
  '排序',
  '降冪 (遞減)',
  '限定',
  'Text 長度',
  '動作',
  '全資料表掃描',
  'SQL 命令',
  '打開',
  '儲存',
  '修改資料庫',
  '修改資料表結構',
  '建立資料表結構',
  '資料庫結構',
  '權限',
  '匯入',
  '匯出',
  '建立資料表',
  '資料庫',
  '資料庫',
  '選擇',
  '禁用 %s 或啟用 %s 或 %s 擴充模組。',
  '字串',
  '數字',
  '日期時間',
  '列表',
  '二進位',
  '幾何',
  'ltr',
  '您離線了。',
  '登出',
  '登錄失敗次數過多，請 %d 分鐘後重試。',
  '成功登出。',
  '感謝使用Adminer，請考慮為我們<a href="https://www.adminer.org/en/donation/">捐款（英文網頁）</a>.',
  'Session 已過期，請重新登入。',
  '主密碼已過期。<a href="https://www.adminer.org/en/extension/"%s>請擴展</a> %s 方法讓它永久化。',
  'Session 必須被啟用。',
  '此操作將在成功使用相同的憑據登錄後執行。',
  '無擴充模組',
  '沒有任何支援的 PHP 擴充模組（%s）。',
  '不允許連接到特權埠。',
  '無效的憑證。',
  '您輸入的密碼中有一個空格，這可能是導致問題的原因。',
  '無效的 CSRF token。請重新發送表單。',
  '超過允許的字段數量的最大值。請增加 %s。',
  '如果您並沒有從Adminer發送請求，請關閉此頁面。',
  'POST 資料太大。減少資料或者增加 %s 的設定值。',
  '您可以通過FTP上傳大型SQL檔並從伺服器導入。',
  '外來鍵',
  '校對',
  'ON UPDATE',
  'ON DELETE',
  '欄位名稱',
  '參數名稱',
  '長度',
  '選項',
  '新增下一筆',
  '上移',
  '下移',
  '移除',
  '無效的資料庫。',
  '資料庫已刪除。',
  '選擇資料庫',
  '建立資料庫',
  '處理程序列表',
  '變數',
  '狀態',
  '%s 版本：%s 透過 PHP 擴充模組 %s',
  '登錄為： %s',
  '重新載入',
  '校對',
  '資料表',
  '大小',
  '計算',
  '已選中',
  '刪除',
  '物化視圖',
  '檢視表',
  '資料表',
  '索引',
  '修改索引',
  '來源',
  '目標',
  '修改',
  '新增外來鍵',
  '觸發器',
  '建立觸發器',
  '永久連結',
  '輸出',
  '格式',
  '程序',
  '事件',
  '資料',
  '建立使用者',
  '不支援ATTACH查詢。',
  '查詢發生錯誤',
  '%d / ',
  '%d 行',
  '執行查詢 OK，%d 行受影響。',
  '沒有命令可執行。',
  '已順利執行 %d 個查詢。',
  '執行',
  '限制行數',
  '檔案上傳',
  '檔案上傳已經被停用。',
  '從伺服器',
  '網頁伺服器檔案 %s',
  '執行檔案',
  '出錯時停止',
  '僅顯示錯誤訊息',
  '紀錄',
  '清除',
  '編輯全部',
  '該項目已被刪除',
  '已更新項目。',
  '已新增項目 %s。',
  '已經刪除資料表。',
  '資料表已修改。',
  '資料表已建立。',
  '資料表名稱',
  '引擎',
  '預設值',
  '刪除 %s?',
  '分區類型',
  '分區',
  '分區名稱',
  '值',
  '已修改索引。',
  '索引類型',
  '欄位（長度）',
  '名稱',
  '資料庫已刪除。',
  '已重新命名資料庫。',
  '已建立資料庫。',
  '已修改資料庫。',
  '呼叫',
  '程序已被執行，%d 行被影響',
  '已刪除外來鍵。',
  '已修改外來鍵。',
  '已建立外來鍵。',
  '來源列和目標列必須具有相同的資料類型，在目標列上必須有一個索引並且引用的資料必須存在。',
  '外來鍵',
  '目標資料表',
  '資料表結構',
  '變更',
  '新增欄位',
  '已修改檢視表。',
  '已刪除檢視表。',
  '已建立檢視表。',
  '建立檢視表',
  '已刪除事件。',
  '已修改事件。',
  '已建立事件。',
  '修改事件',
  '建立事件',
  '開始',
  '結束',
  '每',
  '在完成後儲存',
  '已刪除程序。',
  '已修改子程序。',
  '已建立子程序。',
  '修改函式',
  '修改預存程序',
  '建立函式',
  '建立預存程序',
  '回傳類型',
  '已刪除觸發器。',
  '已修改觸發器。',
  '已建立觸發器。',
  '修改觸發器',
  '建立觸發器',
  '時間',
  '事件',
  '已刪除使用者。',
  '已修改使用者。',
  '已建立使用者。',
  'Hashed',
  '程序',
  '授權',
  '廢除',
  '%d 個 Process(es) 被終止',
  '複製',
  '總共 %d 個',
  '終止',
  '%d 個項目受到影響。',
  '按住Ctrl並按一下某個值進行修改。',
  '檔必須使用UTF-8編碼。',
  '已匯入 %d 行。',
  '無法選擇該資料表',
  '修改',
  '關聯',
  '編輯',
  '使用編輯連結來修改。',
  '載入更多資料',
  '載入中',
  '頁',
  '最後一頁',
  '所有結果',
  '已清空資料表。',
  '已轉移資料表。',
  '資料表已經複製',
  '已經將資料表刪除。',
  '已優化資料表。',
  '資料表和檢視表',
  '在資料庫搜尋',
  '引擎',
  '資料長度',
  '索引長度',
  '資料空閒',
  '行數',
  '整理（Vacuum）',
  '最佳化',
  '分析',
  '檢查',
  '修復',
  '清空',
  '轉移到其它資料庫',
  '轉移',
  '複製',
  '覆蓋',
  '排程',
  '在指定時間',
  '修改類型',
];
	}
	return array();
}

$translations = get_translations($LANG);


// PDO can be used in several database drivers
if (extension_loaded('pdo')) {
	/*abstract*/ class Min_PDO {
		var $_result, $server_info, $affected_rows, $errno, $error, $pdo;
		
		function __construct() {
			global $adminer;
			$pos = array_search("SQL", $adminer->operators);
			if ($pos !== false) {
				unset($adminer->operators[$pos]);
			}
		}
		
		function dsn($dsn, $username, $password, $options = array()) {
			$options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_SILENT;
			$options[PDO::ATTR_STATEMENT_CLASS] = array('Min_PDOStatement');
			try {
				$this->pdo = new PDO($dsn, $username, $password, $options);
			} catch (Exception $ex) {
				auth_error(h($ex->getMessage()));
			}
			$this->server_info = @$this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
		}
		
		/*abstract function select_db($database);*/
		
		function quote($string) {
			return $this->pdo->quote($string);
		}
		
		function query($query, $unbuffered = false) {
			$result = $this->pdo->query($query);
			$this->error = "";
			if (!$result) {
				list(, $this->errno, $this->error) = $this->pdo->errorInfo();
				if (!$this->error) {
					$this->error = lang(21);
				}
				return false;
			}
			$this->store_result($result);
			return $result;
		}
		
		function multi_query($query) {
			return $this->_result = $this->query($query);
		}
		
		function store_result($result = null) {
			if (!$result) {
				$result = $this->_result;
				if (!$result) {
					return false;
				}
			}
			if ($result->columnCount()) {
				$result->num_rows = $result->rowCount(); // is not guaranteed to work with all drivers
				return $result;
			}
			$this->affected_rows = $result->rowCount();
			return true;
		}
		
		function next_result() {
			if (!$this->_result) {
				return false;
			}
			$this->_result->_offset = 0;
			return @$this->_result->nextRowset(); // @ - PDO_PgSQL doesn't support it
		}
		
		function result($query, $field = 0) {
			$result = $this->query($query);
			if (!$result) {
				return false;
			}
			$row = $result->fetch();
			return $row[$field];
		}
	}
	
	class Min_PDOStatement extends PDOStatement {
		var $_offset = 0, $num_rows;
		
		function fetch_assoc() {
			return $this->fetch(PDO::FETCH_ASSOC);
		}
		
		function fetch_row() {
			return $this->fetch(PDO::FETCH_NUM);
		}
		
		function fetch_field() {
			$row = (object) $this->getColumnMeta($this->_offset++);
			$row->orgtable = $row->table;
			$row->orgname = $row->name;
			$row->charsetnr = (in_array("blob", (array) $row->flags) ? 63 : 0);
			return $row;
		}
	}
}


$drivers = array();

/** Add a driver
* @param string
* @param string
* @return null
*/
function add_driver($id, $name) {
	global $drivers;
	$drivers[$id] = $name;
}

/*abstract*/ class Min_SQL {
	var $_conn;
	
	/** Create object for performing database operations
	* @param Min_DB
	*/
	function __construct($connection) {
		$this->_conn = $connection;
	}
	
	/** Select data from table
	* @param string
	* @param array result of $adminer->selectColumnsProcess()[0]
	* @param array result of $adminer->selectSearchProcess()
	* @param array result of $adminer->selectColumnsProcess()[1]
	* @param array result of $adminer->selectOrderProcess()
	* @param int result of $adminer->selectLimitProcess()
	* @param int index of page starting at zero
	* @param bool whether to print the query
	* @return Min_Result
	*/
	function select($table, $select, $where, $group, $order = array(), $limit = 1, $page = 0, $print = false) {
		global $adminer, $jush;
		$is_group = (count($group) < count($select));
		$query = $adminer->selectQueryBuild($select, $where, $group, $order, $limit, $page);
		if (!$query) {
			$query = "SELECT" . limit(
				($_GET["page"] != "last" && $limit != "" && $group && $is_group && $jush == "sql" ? "SQL_CALC_FOUND_ROWS " : "") . implode(", ", $select) . "\nFROM " . table($table),
				($where ? "\nWHERE " . implode(" AND ", $where) : "") . ($group && $is_group ? "\nGROUP BY " . implode(", ", $group) : "") . ($order ? "\nORDER BY " . implode(", ", $order) : ""),
				($limit != "" ? +$limit : null),
				($page ? $limit * $page : 0),
				"\n"
			);
		}
		$start = microtime(true);
		$return = $this->_conn->query($query);
		if ($print) {
			echo $adminer->selectQuery($query, $start, !$return);
		}
		return $return;
	}
	
	/** Delete data from table
	* @param string
	* @param string " WHERE ..."
	* @param int 0 or 1
	* @return bool
	*/
	function delete($table, $queryWhere, $limit = 0) {
		$query = "FROM " . table($table);
		return queries("DELETE" . ($limit ? limit1($table, $query, $queryWhere) : " $query$queryWhere"));
	}
	
	/** Update data in table
	* @param string
	* @param array escaped columns in keys, quoted data in values
	* @param string " WHERE ..."
	* @param int 0 or 1
	* @param string
	* @return bool
	*/
	function update($table, $set, $queryWhere, $limit = 0, $separator = "\n") {
		$values = array();
		foreach ($set as $key => $val) {
			$values[] = "$key = $val";
		}
		$query = table($table) . " SET$separator" . implode(",$separator", $values);
		return queries("UPDATE" . ($limit ? limit1($table, $query, $queryWhere, $separator) : " $query$queryWhere"));
	}
	
	/** Insert data into table
	* @param string
	* @param array escaped columns in keys, quoted data in values
	* @return bool
	*/
	function insert($table, $set) {
		return queries("INSERT INTO " . table($table) . ($set
			? " (" . implode(", ", array_keys($set)) . ")\nVALUES (" . implode(", ", $set) . ")"
			: " DEFAULT VALUES"
		));
	}
	
	/** Insert or update data in table
	* @param string
	* @param array
	* @param array of arrays with escaped columns in keys and quoted data in values
	* @return bool
	*/
	/*abstract*/ function insertUpdate($table, $rows, $primary) {
		return false;
	}
	
	/** Begin transaction
	* @return bool
	*/
	function begin() {
		return queries("BEGIN");
	}
	
	/** Commit transaction
	* @return bool
	*/
	function commit() {
		return queries("COMMIT");
	}
	
	/** Rollback transaction
	* @return bool
	*/
	function rollback() {
		return queries("ROLLBACK");
	}
	
	/** Return query with a timeout
	* @param string
	* @param int seconds
	* @return string or null if the driver doesn't support query timeouts
	*/
	function slowQuery($query, $timeout) {
	}
	
	/** Convert column to be searchable
	* @param string escaped column name
	* @param array array("op" => , "val" => )
	* @param array
	* @return string
	*/
	function convertSearch($idf, $val, $field) {
		return $idf;
	}

	/** Convert value returned by database to actual value
	* @param string
	* @param array
	* @return string
	*/
	function value($val, $field) {
		return (method_exists($this->_conn, 'value')
			? $this->_conn->value($val, $field)
			: (is_resource($val) ? stream_get_contents($val) : $val)
		);
	}

	/** Quote binary string
	* @param string
	* @return string
	*/
	function quoteBinary($s) {
		return q($s);
	}
	
	/** Get warnings about the last command
	* @return string HTML
	*/
	function warnings() {
		return '';
	}
	
	/** Get help link for table
	* @param string
	* @return string relative URL or null
	*/
	function tableHelp($name) {
	}
	
}


// any method change in this file should be transferred to editor/include/adminer.inc.php and plugins/plugin.php

class Adminer {
	/** @var array operators used in select, null for all operators */
	var $operators;

	/** Name in title and navigation
	* @return string HTML code
	*/
	function name() {
		return "<a href='https://www.adminer.org/'" . target_blank() . " id='h1'>Adminer</a>";
	}

	/** Connection parameters
	* @return array ($server, $username, $password)
	*/
	function credentials() {
		return array(SERVER, $_GET["username"], get_password());
	}

	/** Get SSL connection options
	* @return array array("key" => filename, "cert" => filename, "ca" => filename) or null
	*/
	function connectSsl() {
	}

	/** Get key used for permanent login
	* @param bool
	* @return string cryptic string which gets combined with password or false in case of an error
	*/
	function permanentLogin($create = false) {
		return password_file($create);
	}

	/** Return key used to group brute force attacks; behind a reverse proxy, you want to return the last part of X-Forwarded-For
	* @return string
	*/
	function bruteForceKey() {
		return $_SERVER["REMOTE_ADDR"];
	}
	
	/** Get server name displayed in breadcrumbs
	* @param string
	* @return string HTML code or null
	*/
	function serverName($server) {
		return h($server);
	}

	/** Identifier of selected database
	* @return string
	*/
	function database() {
		// should be used everywhere instead of DB
		return DB;
	}

	/** Get cached list of databases
	* @param bool
	* @return array
	*/
	function databases($flush = true) {
		return get_databases($flush);
	}

	/** Get list of schemas
	* @return array
	*/
	function schemas() {
		return schemas();
	}

	/** Specify limit for waiting on some slow queries like DB list
	* @return float number of seconds
	*/
	function queryTimeout() {
		return 2;
	}

	/** Headers to send before HTML output
	* @return null
	*/
	function headers() {
	}

	/** Get Content Security Policy headers
	* @return array of arrays with directive name in key, allowed sources in value
	*/
	function csp() {
		return csp();
	}

	/** Print HTML code inside <head>
	* @return bool true to link favicon.ico and adminer.css if exists
	*/
	function head() {
		
		return true;
	}

	/** Get URLs of the CSS files
	* @return array of strings
	*/
	function css() {
		$return = array();
		$filename = "adminer.css";
		if (file_exists($filename)) {
			$return[] = "$filename?v=" . crc32(file_get_contents($filename));
		}
		return $return;
	}

	/** Print login form
	* @return null
	*/
	function loginForm() {
		global $drivers;
		echo "<table cellspacing='0' class='layout'>\n";
		echo $this->loginFormField('driver', '<tr><th>' . lang(22) . '<td>', html_select("auth[driver]", $drivers, DRIVER, "loginDriver(this);") . "\n");
		echo $this->loginFormField('server', '<tr><th>' . lang(23) . '<td>', '<input name="auth[server]" value="' . h(SERVER) . '" title="hostname[:port]" placeholder="localhost" autocapitalize="off">' . "\n");
		echo $this->loginFormField('username', '<tr><th>' . lang(24) . '<td>', '<input name="auth[username]" id="username" value="' . h($_GET["username"]) . '" autocomplete="username" autocapitalize="off">' . script("focus(qs('#username')); qs('#username').form['auth[driver]'].onchange();"));
		echo $this->loginFormField('password', '<tr><th>' . lang(25) . '<td>', '<input type="password" name="auth[password]" autocomplete="current-password">' . "\n");
		echo $this->loginFormField('db', '<tr><th>' . lang(26) . '<td>', '<input name="auth[db]" value="' . h($_GET["db"]) . '" autocapitalize="off">' . "\n");
		echo "</table>\n";
		echo "<p><input type='submit' value='" . lang(27) . "'>\n";
		echo checkbox("auth[permanent]", 1, $_COOKIE["adminer_permanent"], lang(28)) . "\n";
	}
	
	/** Get login form field
	* @param string
	* @param string HTML
	* @param string HTML
	* @return string
	*/
	function loginFormField($name, $heading, $value) {
		return $heading . $value;
	}

	/** Authorize the user
	* @param string
	* @param string
	* @return mixed true for success, string for error message, false for unknown error
	*/
	function login($login, $password) {
		if ($password == "") {
			return lang(29, target_blank());
		}
		return true;
	}

	/** Table caption used in navigation and headings
	* @param array result of SHOW TABLE STATUS
	* @return string HTML code, "" to ignore table
	*/
	function tableName($tableStatus) {
		return h($tableStatus["Name"]);
	}

	/** Field caption used in select and edit
	* @param array single field returned from fields()
	* @param int order of column in select
	* @return string HTML code, "" to ignore field
	*/
	function fieldName($field, $order = 0) {
		return '<span title="' . h($field["full_type"]) . '">' . h($field["field"]) . '</span>';
	}

	/** Print links after select heading
	* @param array result of SHOW TABLE STATUS
	* @param string new item options, NULL for no new item
	* @return null
	*/
	function selectLinks($tableStatus, $set = "") {
		global $jush, $driver;
		echo '<p class="links">';
		$links = array("select" => lang(30));
		if (support("table") || support("indexes")) {
			$links["table"] = lang(31);
		}
		if (support("table")) {
			if (is_view($tableStatus)) {
				$links["view"] = lang(32);
			} else {
				$links["create"] = lang(33);
			}
		}
		if ($set !== null) {
			$links["edit"] = lang(34);
		}
		$name = $tableStatus["Name"];
		foreach ($links as $key => $val) {
			echo " <a href='" . h(ME) . "$key=" . urlencode($name) . ($key == "edit" ? $set : "") . "'" . bold(isset($_GET[$key])) . ">$val</a>";
		}
		echo doc_link(array($jush => $driver->tableHelp($name)), "?");
		echo "\n";
	}

	/** Get foreign keys for table
	* @param string
	* @return array same format as foreign_keys()
	*/
	function foreignKeys($table) {
		return foreign_keys($table);
	}

	/** Find backward keys for table
	* @param string
	* @param string
	* @return array $return[$target_table]["keys"][$key_name][$target_column] = $source_column; $return[$target_table]["name"] = $this->tableName($target_table);
	*/
	function backwardKeys($table, $tableName) {
		return array();
	}

	/** Print backward keys for row
	* @param array result of $this->backwardKeys()
	* @param array
	* @return null
	*/
	function backwardKeysPrint($backwardKeys, $row) {
	}

	/** Query printed in select before execution
	* @param string query to be executed
	* @param float start time of the query
	* @param bool
	* @return string
	*/
	function selectQuery($query, $start, $failed = false) {
		global $jush, $driver;
		$return = "</p>\n"; // required for IE9 inline edit
		if (!$failed && ($warnings = $driver->warnings())) {
			$id = "warnings";
			$return = ", <a href='#$id'>" . lang(35) . "</a>" . script("qsl('a').onclick = partial(toggle, '$id');", "")
				. "$return<div id='$id' class='hidden'>\n$warnings</div>\n"
			;
		}
		return "<p><code class='jush-$jush'>" . h(str_replace("\n", " ", $query)) . "</code> <span class='time'>(" . format_time($start) . ")</span>"
			. (support("sql") ? " <a href='" . h(ME) . "sql=" . urlencode($query) . "'>" . lang(10) . "</a>" : "")
			. $return
		;
	}

	/** Query printed in SQL command before execution
	* @param string query to be executed
	* @return string escaped query to be printed
	*/
	function sqlCommandQuery($query)
	{
		return shorten_utf8(trim($query), 1000);
	}

	/** Description of a row in a table
	* @param string
	* @return string SQL expression, empty string for no description
	*/
	function rowDescription($table) {
		return "";
	}

	/** Get descriptions of selected data
	* @param array all data to print
	* @param array
	* @return array
	*/
	function rowDescriptions($rows, $foreignKeys) {
		return $rows;
	}

	/** Get a link to use in select table
	* @param string raw value of the field
	* @param array single field returned from fields()
	* @return string or null to create the default link
	*/
	function selectLink($val, $field) {
	}

	/** Value printed in select table
	* @param string HTML-escaped value to print
	* @param string link to foreign key
	* @param array single field returned from fields()
	* @param array original value before applying editVal() and escaping
	* @return string
	*/
	function selectVal($val, $link, $field, $original) {
		$return = ($val === null ? "<i>NULL</i>" : (preg_match("~char|binary|boolean~", $field["type"]) && !preg_match("~var~", $field["type"]) ? "<code>$val</code>" : $val));
		if (preg_match('~blob|bytea|raw|file~', $field["type"]) && !is_utf8($val)) {
			$return = "<i>" . lang(36, strlen($original)) . "</i>";
		}
		if (preg_match('~json~', $field["type"])) {
			$return = "<code class='jush-js'>$return</code>";
		}
		return ($link ? "<a href='" . h($link) . "'" . (is_url($link) ? target_blank() : "") . ">$return</a>" : $return);
	}

	/** Value conversion used in select and edit
	* @param string
	* @param array single field returned from fields()
	* @return string
	*/
	function editVal($val, $field) {
		return $val;
	}

	/** Print table structure in tabular format
	* @param array data about individual fields
	* @return null
	*/
	function tableStructurePrint($fields) {
		echo "<div class='scrollable'>\n";
		echo "<table cellspacing='0' class='nowrap'>\n";
		echo "<thead><tr><th>" . lang(37) . "<td>" . lang(38) . (support("comment") ? "<td>" . lang(39) : "") . "</thead>\n";
		foreach ($fields as $field) {
			echo "<tr" . odd() . "><th>" . h($field["field"]);
			echo "<td><span title='" . h($field["collation"]) . "'>" . h($field["full_type"]) . "</span>";
			echo ($field["null"] ? " <i>NULL</i>" : "");
			echo ($field["auto_increment"] ? " <i>" . lang(40) . "</i>" : "");
			echo (isset($field["default"]) ? " <span title='" . lang(41) . "'>[<b>" . h($field["default"]) . "</b>]</span>" : "");
			echo (support("comment") ? "<td>" . h($field["comment"]) : "");
			echo "\n";
		}
		echo "</table>\n";
		echo "</div>\n";
	}

	/** Print list of indexes on table in tabular format
	* @param array data about all indexes on a table
	* @return null
	*/
	function tableIndexesPrint($indexes) {
		echo "<table cellspacing='0'>\n";
		foreach ($indexes as $name => $index) {
			ksort($index["columns"]); // enforce correct columns order
			$print = array();
			foreach ($index["columns"] as $key => $val) {
				$print[] = "<i>" . h($val) . "</i>"
					. ($index["lengths"][$key] ? "(" . $index["lengths"][$key] . ")" : "")
					. ($index["descs"][$key] ? " DESC" : "")
				;
			}
			echo "<tr title='" . h($name) . "'><th>$index[type]<td>" . implode(", ", $print) . "\n";
		}
		echo "</table>\n";
	}

	/** Print columns box in select
	* @param array result of selectColumnsProcess()[0]
	* @param array selectable columns
	* @return null
	*/
	function selectColumnsPrint($select, $columns) {
		global $functions, $grouping;
		print_fieldset("select", lang(42), $select);
		$i = 0;
		$select[""] = array();
		foreach ($select as $key => $val) {
			$val = $_GET["columns"][$key];
			$column = select_input(
				" name='columns[$i][col]'",
				$columns,
				$val["col"],
				($key !== "" ? "selectFieldChange" : "selectAddRow")
			);
			echo "<div>" . ($functions || $grouping ? "<select name='columns[$i][fun]'>"
				. optionlist(array(-1 => "") + array_filter(array(lang(43) => $functions, lang(44) => $grouping)), $val["fun"]) . "</select>"
				. on_help("getTarget(event).value && getTarget(event).value.replace(/ |\$/, '(') + ')'", 1)
				. script("qsl('select').onchange = function () { helpClose();" . ($key !== "" ? "" : " qsl('select, input', this.parentNode).onchange();") . " };", "")
				. "($column)" : $column) . "</div>\n";
			$i++;
		}
		echo "</div></fieldset>\n";
	}

	/** Print search box in select
	* @param array result of selectSearchProcess()
	* @param array selectable columns
	* @param array
	* @return null
	*/
	function selectSearchPrint($where, $columns, $indexes) {
		print_fieldset("search", lang(45), $where);
		foreach ($indexes as $i => $index) {
			if ($index["type"] == "FULLTEXT") {
				echo "<div>(<i>" . implode("</i>, <i>", array_map('h', $index["columns"])) . "</i>) AGAINST";
				echo " <input type='search' name='fulltext[$i]' value='" . h($_GET["fulltext"][$i]) . "'>";
				echo script("qsl('input').oninput = selectFieldChange;", "");
				echo checkbox("boolean[$i]", 1, isset($_GET["boolean"][$i]), "BOOL");
				echo "</div>\n";
			}
		}
		$change_next = "this.parentNode.firstChild.onchange();";
		foreach (array_merge((array) $_GET["where"], array(array())) as $i => $val) {
			if (!$val || ("$val[col]$val[val]" != "" && in_array($val["op"], $this->operators))) {
				echo "<div>" . select_input(
					" name='where[$i][col]'",
					$columns,
					$val["col"],
					($val ? "selectFieldChange" : "selectAddRow"),
					"(" . lang(46) . ")"
				);
				echo html_select("where[$i][op]", $this->operators, $val["op"], $change_next);
				echo "<input type='search' name='where[$i][val]' value='" . h($val["val"]) . "'>";
				echo script("mixin(qsl('input'), {oninput: function () { $change_next }, onkeydown: selectSearchKeydown, onsearch: selectSearchSearch});", "");
				echo "</div>\n";
			}
		}
		echo "</div></fieldset>\n";
	}

	/** Print order box in select
	* @param array result of selectOrderProcess()
	* @param array selectable columns
	* @param array
	* @return null
	*/
	function selectOrderPrint($order, $columns, $indexes) {
		print_fieldset("sort", lang(47), $order);
		$i = 0;
		foreach ((array) $_GET["order"] as $key => $val) {
			if ($val != "") {
				echo "<div>" . select_input(" name='order[$i]'", $columns, $val, "selectFieldChange");
				echo checkbox("desc[$i]", 1, isset($_GET["desc"][$key]), lang(48)) . "</div>\n";
				$i++;
			}
		}
		echo "<div>" . select_input(" name='order[$i]'", $columns, "", "selectAddRow");
		echo checkbox("desc[$i]", 1, false, lang(48)) . "</div>\n";
		echo "</div></fieldset>\n";
	}

	/** Print limit box in select
	* @param string result of selectLimitProcess()
	* @return null
	*/
	function selectLimitPrint($limit) {
		echo "<fieldset><legend>" . lang(49) . "</legend><div>"; // <div> for easy styling
		echo "<input type='number' name='limit' class='size' value='" . h($limit) . "'>";
		echo script("qsl('input').oninput = selectFieldChange;", "");
		echo "</div></fieldset>\n";
	}

	/** Print text length box in select
	* @param string result of selectLengthProcess()
	* @return null
	*/
	function selectLengthPrint($text_length) {
		if ($text_length !== null) {
			echo "<fieldset><legend>" . lang(50) . "</legend><div>";
			echo "<input type='number' name='text_length' class='size' value='" . h($text_length) . "'>";
			echo "</div></fieldset>\n";
		}
	}

	/** Print action box in select
	* @param array
	* @return null
	*/
	function selectActionPrint($indexes) {
		echo "<fieldset><legend>" . lang(51) . "</legend><div>";
		echo "<input type='submit' value='" . lang(42) . "'>";
		echo " <span id='noindex' title='" . lang(52) . "'></span>";
		echo "<script" . nonce() . ">\n";
		echo "var indexColumns = ";
		$columns = array();
		foreach ($indexes as $index) {
			$current_key = reset($index["columns"]);
			if ($index["type"] != "FULLTEXT" && $current_key) {
				$columns[$current_key] = 1;
			}
		}
		$columns[""] = 1;
		foreach ($columns as $key => $val) {
			json_row($key);
		}
		echo ";\n";
		echo "selectFieldChange.call(qs('#form')['select']);\n";
		echo "</script>\n";
		echo "</div></fieldset>\n";
	}
	
	/** Print command box in select
	* @return bool whether to print default commands
	*/
	function selectCommandPrint() {
		return !information_schema(DB);
	}

	/** Print import box in select
	* @return bool whether to print default import
	*/
	function selectImportPrint() {
		return !information_schema(DB);
	}

	/** Print extra text in the end of a select form
	* @param array fields holding e-mails
	* @param array selectable columns
	* @return null
	*/
	function selectEmailPrint($emailFields, $columns) {
	}

	/** Process columns box in select
	* @param array selectable columns
	* @param array
	* @return array (array(select_expressions), array(group_expressions))
	*/
	function selectColumnsProcess($columns, $indexes) {
		global $functions, $grouping;
		$select = array(); // select expressions, empty for *
		$group = array(); // expressions without aggregation - will be used for GROUP BY if an aggregation function is used
		foreach ((array) $_GET["columns"] as $key => $val) {
			if ($val["fun"] == "count" || ($val["col"] != "" && (!$val["fun"] || in_array($val["fun"], $functions) || in_array($val["fun"], $grouping)))) {
				$select[$key] = apply_sql_function($val["fun"], ($val["col"] != "" ? idf_escape($val["col"]) : "*"));
				if (!in_array($val["fun"], $grouping)) {
					$group[] = $select[$key];
				}
			}
		}
		return array($select, $group);
	}

	/** Process search box in select
	* @param array
	* @param array
	* @return array expressions to join by AND
	*/
	function selectSearchProcess($fields, $indexes) {
		global $connection, $driver;
		$return = array();
		foreach ($indexes as $i => $index) {
			if ($index["type"] == "FULLTEXT" && $_GET["fulltext"][$i] != "") {
				$return[] = "MATCH (" . implode(", ", array_map('idf_escape', $index["columns"])) . ") AGAINST (" . q($_GET["fulltext"][$i]) . (isset($_GET["boolean"][$i]) ? " IN BOOLEAN MODE" : "") . ")";
			}
		}
		foreach ((array) $_GET["where"] as $key => $val) {
			if ("$val[col]$val[val]" != "" && in_array($val["op"], $this->operators)) {
				$prefix = "";
				$cond = " $val[op]";
				if (preg_match('~IN$~', $val["op"])) {
					$in = process_length($val["val"]);
					$cond .= " " . ($in != "" ? $in : "(NULL)");
				} elseif ($val["op"] == "SQL") {
					$cond = " $val[val]"; // SQL injection
				} elseif ($val["op"] == "LIKE %%") {
					$cond = " LIKE " . $this->processInput($fields[$val["col"]], "%$val[val]%");
				} elseif ($val["op"] == "ILIKE %%") {
					$cond = " ILIKE " . $this->processInput($fields[$val["col"]], "%$val[val]%");
				} elseif ($val["op"] == "FIND_IN_SET") {
					$prefix = "$val[op](" . q($val["val"]) . ", ";
					$cond = ")";
				} elseif (!preg_match('~NULL$~', $val["op"])) {
					$cond .= " " . $this->processInput($fields[$val["col"]], $val["val"]);
				}
				if ($val["col"] != "") {
					$return[] = $prefix . $driver->convertSearch(idf_escape($val["col"]), $val, $fields[$val["col"]]) . $cond;
				} else {
					// find anywhere
					$cols = array();
					foreach ($fields as $name => $field) {
						if ((preg_match('~^[-\d.' . (preg_match('~IN$~', $val["op"]) ? ',' : '') . ']+$~', $val["val"]) || !preg_match('~' . number_type() . '|bit~', $field["type"]))
							&& (!preg_match("~[\x80-\xFF]~", $val["val"]) || preg_match('~char|text|enum|set~', $field["type"]))
							&& (!preg_match('~date|timestamp~', $field["type"]) || preg_match('~^\d+-\d+-\d+~', $val["val"]))
						) {
							$cols[] = $prefix . $driver->convertSearch(idf_escape($name), $val, $field) . $cond;
						}
					}
					$return[] = ($cols ? "(" . implode(" OR ", $cols) . ")" : "1 = 0");
				}
			}
		}
		return $return;
	}

	/** Process order box in select
	* @param array
	* @param array
	* @return array expressions to join by comma
	*/
	function selectOrderProcess($fields, $indexes) {
		$return = array();
		foreach ((array) $_GET["order"] as $key => $val) {
			if ($val != "") {
				$return[] = (preg_match('~^((COUNT\(DISTINCT |[A-Z0-9_]+\()(`(?:[^`]|``)+`|"(?:[^"]|"")+")\)|COUNT\(\*\))$~', $val) ? $val : idf_escape($val)) //! MS SQL uses []
					. (isset($_GET["desc"][$key]) ? " DESC" : "")
				;
			}
		}
		return $return;
	}

	/** Process limit box in select
	* @return string expression to use in LIMIT, will be escaped
	*/
	function selectLimitProcess() {
		return (isset($_GET["limit"]) ? $_GET["limit"] : "50");
	}

	/** Process length box in select
	* @return string number of characters to shorten texts, will be escaped
	*/
	function selectLengthProcess() {
		return (isset($_GET["text_length"]) ? $_GET["text_length"] : "100");
	}

	/** Process extras in select form
	* @param array AND conditions
	* @param array
	* @return bool true if processed, false to process other parts of form
	*/
	function selectEmailProcess($where, $foreignKeys) {
		return false;
	}

	/** Build SQL query used in select
	* @param array result of selectColumnsProcess()[0]
	* @param array result of selectSearchProcess()
	* @param array result of selectColumnsProcess()[1]
	* @param array result of selectOrderProcess()
	* @param int result of selectLimitProcess()
	* @param int index of page starting at zero
	* @return string empty string to use default query
	*/
	function selectQueryBuild($select, $where, $group, $order, $limit, $page) {
		return "";
	}

	/** Query printed after execution in the message
	* @param string executed query
	* @param string elapsed time
	* @param bool
	* @return string
	*/
	function messageQuery($query, $time, $failed = false) {
		global $jush, $driver;
		restart_session();
		$history = &get_session("queries");
		if (!$history[$_GET["db"]]) {
			$history[$_GET["db"]] = array();
		}
		if (strlen($query) > 1e6) {
			$query = preg_replace('~[\x80-\xFF]+$~', '', substr($query, 0, 1e6)) . "\n…"; // [\x80-\xFF] - valid UTF-8, \n - can end by one-line comment
		}
		$history[$_GET["db"]][] = array($query, time(), $time); // not DB - $_GET["db"] is changed in database.inc.php //! respect $_GET["ns"]
		$sql_id = "sql-" . count($history[$_GET["db"]]);
		$return = "<a href='#$sql_id' class='toggle'>" . lang(53) . "</a>\n";
		if (!$failed && ($warnings = $driver->warnings())) {
			$id = "warnings-" . count($history[$_GET["db"]]);
			$return = "<a href='#$id' class='toggle'>" . lang(35) . "</a>, $return<div id='$id' class='hidden'>\n$warnings</div>\n";
		}
		return " <span class='time'>" . @date("H:i:s") . "</span>" // @ - time zone may be not set
			. " $return<div id='$sql_id' class='hidden'><pre><code class='jush-$jush'>" . shorten_utf8($query, 1000) . "</code></pre>"
			. ($time ? " <span class='time'>($time)</span>" : '')
			. (support("sql") ? '<p><a href="' . h(str_replace("db=" . urlencode(DB), "db=" . urlencode($_GET["db"]), ME) . 'sql=&history=' . (count($history[$_GET["db"]]) - 1)) . '">' . lang(10) . '</a>' : '')
			. '</div>'
		;
	}

	/** Print before edit form
	* @param string
	* @param array
	* @param mixed
	* @param bool
	* @return null
	*/
	function editRowPrint($table, $fields, $row, $update) {
	}

	/** Functions displayed in edit form
	* @param array single field from fields()
	* @return array
	*/
	function editFunctions($field) {
		global $edit_functions;
		$return = ($field["null"] ? "NULL/" : "");
		$update = isset($_GET["select"]) || where($_GET);
		foreach ($edit_functions as $key => $functions) {
			if (!$key || (!isset($_GET["call"]) && $update)) { // relative functions
				foreach ($functions as $pattern => $val) {
					if (!$pattern || preg_match("~$pattern~", $field["type"])) {
						$return .= "/$val";
					}
				}
			}
			if ($key && !preg_match('~set|blob|bytea|raw|file|bool~', $field["type"])) {
				$return .= "/SQL";
			}
		}
		if ($field["auto_increment"] && !$update) {
			$return = lang(40);
		}
		return explode("/", $return);
	}

	/** Get options to display edit field
	* @param string table name
	* @param array single field from fields()
	* @param string attributes to use inside the tag
	* @param string
	* @return string custom input field or empty string for default
	*/
	function editInput($table, $field, $attrs, $value) {
		if ($field["type"] == "enum") {
			return (isset($_GET["select"]) ? "<label><input type='radio'$attrs value='-1' checked><i>" . lang(8) . "</i></label> " : "")
				. ($field["null"] ? "<label><input type='radio'$attrs value=''" . ($value !== null || isset($_GET["select"]) ? "" : " checked") . "><i>NULL</i></label> " : "")
				. enum_input("radio", $attrs, $field, $value, 0) // 0 - empty
			;
		}
		return "";
	}

	/** Get hint for edit field
	* @param string table name
	* @param array single field from fields()
	* @param string
	* @return string
	*/
	function editHint($table, $field, $value) {
		return "";
	}

	/** Process sent input
	* @param array single field from fields()
	* @param string
	* @param string
	* @return string expression to use in a query
	*/
	function processInput($field, $value, $function = "") {
		if ($function == "SQL") {
			return $value; // SQL injection
		}
		$name = $field["field"];
		$return = q($value);
		if (preg_match('~^(now|getdate|uuid)$~', $function)) {
			$return = "$function()";
		} elseif (preg_match('~^current_(date|timestamp)$~', $function)) {
			$return = $function;
		} elseif (preg_match('~^([+-]|\|\|)$~', $function)) {
			$return = idf_escape($name) . " $function $return";
		} elseif (preg_match('~^[+-] interval$~', $function)) {
			$return = idf_escape($name) . " $function " . (preg_match("~^(\\d+|'[0-9.: -]') [A-Z_]+\$~i", $value) ? $value : $return);
		} elseif (preg_match('~^(addtime|subtime|concat)$~', $function)) {
			$return = "$function(" . idf_escape($name) . ", $return)";
		} elseif (preg_match('~^(md5|sha1|password|encrypt)$~', $function)) {
			$return = "$function($return)";
		}
		return unconvert_field($field, $return);
	}

	/** Returns export output options
	* @return array
	*/
	function dumpOutput() {
		$return = array('text' => lang(54), 'file' => lang(55));
		if (function_exists('gzencode')) {
			$return['gz'] = 'gzip';
		}
		return $return;
	}

	/** Returns export format options
	* @return array empty to disable export
	*/
	function dumpFormat() {
		return array('sql' => 'SQL', 'csv' => 'CSV,', 'csv;' => 'CSV;', 'tsv' => 'TSV');
	}

	/** Export database structure
	* @param string
	* @return null prints data
	*/
	function dumpDatabase($db) {
	}

	/** Export table structure
	* @param string
	* @param string
	* @param int 0 table, 1 view, 2 temporary view table
	* @return null prints data
	*/
	function dumpTable($table, $style, $is_view = 0) {
		if ($_POST["format"] != "sql") {
			echo "\xef\xbb\xbf"; // UTF-8 byte order mark
			if ($style) {
				dump_csv(array_keys(fields($table)));
			}
		} else {
			if ($is_view == 2) {
				$fields = array();
				foreach (fields($table) as $name => $field) {
					$fields[] = idf_escape($name) . " $field[full_type]";
				}
				$create = "CREATE TABLE " . table($table) . " (" . implode(", ", $fields) . ")";
			} else {
				$create = create_sql($table, $_POST["auto_increment"], $style);
			}
			set_utf8mb4($create);
			if ($style && $create) {
				if ($style == "DROP+CREATE" || $is_view == 1) {
					echo "DROP " . ($is_view == 2 ? "VIEW" : "TABLE") . " IF EXISTS " . table($table) . ";\n";
				}
				if ($is_view == 1) {
					$create = remove_definer($create);
				}
				echo "$create;\n\n";
			}
		}
	}

	/** Export table data
	* @param string
	* @param string
	* @param string
	* @return null prints data
	*/
	function dumpData($table, $style, $query) {
		global $connection, $jush;
		$max_packet = ($jush == "sqlite" ? 0 : 1048576); // default, minimum is 1024
		if ($style) {
			if ($_POST["format"] == "sql") {
				if ($style == "TRUNCATE+INSERT") {
					echo truncate_sql($table) . ";\n";
				}
				$fields = fields($table);
			}
			$result = $connection->query($query, 1); // 1 - MYSQLI_USE_RESULT //! enum and set as numbers
			if ($result) {
				$insert = "";
				$buffer = "";
				$keys = array();
				$suffix = "";
				$fetch_function = ($table != '' ? 'fetch_assoc' : 'fetch_row');
				while ($row = $result->$fetch_function()) {
					if (!$keys) {
						$values = array();
						foreach ($row as $val) {
							$field = $result->fetch_field();
							$keys[] = $field->name;
							$key = idf_escape($field->name);
							$values[] = "$key = VALUES($key)";
						}
						$suffix = ($style == "INSERT+UPDATE" ? "\nON DUPLICATE KEY UPDATE " . implode(", ", $values) : "") . ";\n";
					}
					if ($_POST["format"] != "sql") {
						if ($style == "table") {
							dump_csv($keys);
							$style = "INSERT";
						}
						dump_csv($row);
					} else {
						if (!$insert) {
							$insert = "INSERT INTO " . table($table) . " (" . implode(", ", array_map('idf_escape', $keys)) . ") VALUES";
						}
						foreach ($row as $key => $val) {
							$field = $fields[$key];
							$row[$key] = ($val !== null
								? unconvert_field($field, preg_match(number_type(), $field["type"]) && !preg_match('~\[~', $field["full_type"]) && is_numeric($val) ? $val : q(($val === false ? 0 : $val)))
								: "NULL"
							);
						}
						$s = ($max_packet ? "\n" : " ") . "(" . implode(",\t", $row) . ")";
						if (!$buffer) {
							$buffer = $insert . $s;
						} elseif (strlen($buffer) + 4 + strlen($s) + strlen($suffix) < $max_packet) { // 4 - length specification
							$buffer .= ",$s";
						} else {
							echo $buffer . $suffix;
							$buffer = $insert . $s;
						}
					}
				}
				if ($buffer) {
					echo $buffer . $suffix;
				}
			} elseif ($_POST["format"] == "sql") {
				echo "-- " . str_replace("\n", " ", $connection->error) . "\n";
			}
		}
	}

	/** Set export filename
	* @param string
	* @return string filename without extension
	*/
	function dumpFilename($identifier) {
		return friendly_url($identifier != "" ? $identifier : (SERVER != "" ? SERVER : "localhost"));
	}

	/** Send headers for export
	* @param string
	* @param bool
	* @return string extension
	*/
	function dumpHeaders($identifier, $multi_table = false) {
		$output = $_POST["output"];
		$ext = (preg_match('~sql~', $_POST["format"]) ? "sql" : ($multi_table ? "tar" : "csv")); // multiple CSV packed to TAR
		header("Content-Type: " .
			($output == "gz" ? "application/x-gzip" :
			($ext == "tar" ? "application/x-tar" :
			($ext == "sql" || $output != "file" ? "text/plain" : "text/csv") . "; charset=utf-8"
		)));
		if ($output == "gz") {
			ob_start('ob_gzencode', 1e6);
		}
		return $ext;
	}

	/** Set the path of the file for webserver load
	* @return string path of the sql dump file
	*/
	function importServerPath() {
		return "adminer.sql";
	}

	/** Print homepage
	* @return bool whether to print default homepage
	*/
	function homepage() {
		echo '<p class="links">' . ($_GET["ns"] == "" && support("database") ? '<a href="' . h(ME) . 'database=">' . lang(56) . "</a>\n" : "");
		echo (support("scheme") ? "<a href='" . h(ME) . "scheme='>" . ($_GET["ns"] != "" ? lang(57) : lang(58)) . "</a>\n" : "");
		echo ($_GET["ns"] !== "" ? '<a href="' . h(ME) . 'schema=">' . lang(59) . "</a>\n" : "");
		echo (support("privileges") ? "<a href='" . h(ME) . "privileges='>" . lang(60) . "</a>\n" : "");
		return true;
	}

	/** Prints navigation after Adminer title
	* @param string can be "auth" if there is no database connection, "db" if there is no database selected, "ns" with invalid schema
	* @return null
	*/
	function navigation($missing) {
		global $VERSION, $jush, $drivers, $connection;
		?>
<h1>
<?php echo $this->name(); ?> <span class="version"><?php echo $VERSION; ?></span>
<a href="https://www.adminer.org/#download"<?php echo target_blank(); ?> id="version"><?php echo (version_compare($VERSION, $_COOKIE["adminer_version"]) < 0 ? h($_COOKIE["adminer_version"]) : ""); ?></a>
</h1>
<?php
		if ($missing == "auth") {
			$output = "";
			foreach ((array) $_SESSION["pwds"] as $vendor => $servers) {
				foreach ($servers as $server => $usernames) {
					foreach ($usernames as $username => $password) {
						if ($password !== null) {
							$dbs = $_SESSION["db"][$vendor][$server][$username];
							foreach (($dbs ? array_keys($dbs) : array("")) as $db) {
								$output .= "<li><a href='" . h(auth_url($vendor, $server, $username, $db)) . "'>($drivers[$vendor]) " . h($username . ($server != "" ? "@" . $this->serverName($server) : "") . ($db != "" ? " - $db" : "")) . "</a>\n";
							}
						}
					}
				}
			}
			if ($output) {
				echo "<ul id='logins'>\n$output</ul>\n" . script("mixin(qs('#logins'), {onmouseover: menuOver, onmouseout: menuOut});");
			}
		} else {
			$tables = array();
			if ($_GET["ns"] !== "" && !$missing && DB != "") {
				$connection->select_db(DB);
				$tables = table_status('', true);
			}
			echo script_src(preg_replace("~\\?.*~", "", ME) . "?file=jush.js&version=4.8.1");
			if (support("sql")) {
				?>
<script<?php echo nonce(); ?>>
<?php
				if ($tables) {
					$links = array();
					foreach ($tables as $table => $type) {
						$links[] = preg_quote($table, '/');
					}
					echo "var jushLinks = { $jush: [ '" . js_escape(ME) . (support("table") ? "table=" : "select=") . "\$&', /\\b(" . implode("|", $links) . ")\\b/g ] };\n";
					foreach (array("bac", "bra", "sqlite_quo", "mssql_bra") as $val) {
						echo "jushLinks.$val = jushLinks.$jush;\n";
					}
				}
				$server_info = $connection->server_info;
				?>
bodyLoad('<?php echo (is_object($connection) ? preg_replace('~^(\d\.?\d).*~s', '\1', $server_info) : ""); ?>'<?php echo (preg_match('~MariaDB~', $server_info) ? ", true" : ""); ?>);
</script>
<?php
			}
			$this->databasesPrint($missing);
			if (DB == "" || !$missing) {
				echo "<p class='links'>" . (support("sql") ? "<a href='" . h(ME) . "sql='" . bold(isset($_GET["sql"]) && !isset($_GET["import"])) . ">" . lang(53) . "</a>\n<a href='" . h(ME) . "import='" . bold(isset($_GET["import"])) . ">" . lang(61) . "</a>\n" : "") . "";
				if (support("dump")) {
					echo "<a href='" . h(ME) . "dump=" . urlencode(isset($_GET["table"]) ? $_GET["table"] : $_GET["select"]) . "' id='dump'" . bold(isset($_GET["dump"])) . ">" . lang(62) . "</a>\n";
				}
			}
			if ($_GET["ns"] !== "" && !$missing && DB != "") {
				echo '<a href="' . h(ME) . 'create="' . bold($_GET["create"] === "") . ">" . lang(63) . "</a>\n";
				if (!$tables) {
					echo "<p class='message'>" . lang(9) . "\n";
				} else {
					$this->tablesPrint($tables);
				}
			}
		}
	}

	/** Prints databases list in menu
	* @param string
	* @return null
	*/
	function databasesPrint($missing) {
		global $adminer, $connection;
		$databases = $this->databases();
		if (DB && $databases && !in_array(DB, $databases)) {
			array_unshift($databases, DB);
		}
		?>
<form action="">
<p id="dbs">
<?php
		hidden_fields_get();
		$db_events = script("mixin(qsl('select'), {onmousedown: dbMouseDown, onchange: dbChange});");
		echo "<span title='" . lang(64) . "'>" . lang(65) . "</span>: " . ($databases
			? "<select name='db'>" . optionlist(array("" => "") + $databases, DB) . "</select>$db_events"
			: "<input name='db' value='" . h(DB) . "' autocapitalize='off'>\n"
		);
		echo "<input type='submit' value='" . lang(20) . "'" . ($databases ? " class='hidden'" : "") . ">\n";

		foreach (array("import", "sql", "schema", "dump", "privileges") as $val) {
			if (isset($_GET[$val])) {
				echo "<input type='hidden' name='$val' value=''>";
				break;
			}
		}
		echo "</p></form>\n";
	}

	/** Prints table list in menu
	* @param array result of table_status('', true)
	* @return null
	*/
	function tablesPrint($tables) {
		echo "<ul id='tables'>" . script("mixin(qs('#tables'), {onmouseover: menuOver, onmouseout: menuOut});");
		foreach ($tables as $table => $status) {
			$name = $this->tableName($status);
			if ($name != "") {
				echo '<li><a href="' . h(ME) . 'select=' . urlencode($table) . '"'
					. bold($_GET["select"] == $table || $_GET["edit"] == $table, "select")
					. " title='" . lang(30) . "'>" . lang(66) . "</a> "
				;
				echo (support("table") || support("indexes")
					? '<a href="' . h(ME) . 'table=' . urlencode($table) . '"'
						. bold(in_array($table, array($_GET["table"], $_GET["create"], $_GET["indexes"], $_GET["foreign"], $_GET["trigger"])), (is_view($status) ? "view" : "structure"))
						. " title='" . lang(31) . "'>$name</a>"
					: "<span>$name</span>"
				) . "\n";
			}
		}
		echo "</ul>\n";
	}

}

$adminer = (function_exists('adminer_object') ? adminer_object() : new Adminer);

$drivers = array("server" => "MySQL") + $drivers;

if (!defined("DRIVER")) {
	define("DRIVER", "server"); // server - backwards compatibility
	// MySQLi supports everything, MySQL doesn't support multiple result sets, PDO_MySQL doesn't support orgtable
	if (extension_loaded("mysqli")) {
		class Min_DB extends MySQLi {
			var $extension = "MySQLi";

			function __construct() {
				parent::init();
			}

			function connect($server = "", $username = "", $password = "", $database = null, $port = null, $socket = null) {
				global $adminer;
				mysqli_report(MYSQLI_REPORT_OFF); // stays between requests, not required since PHP 5.3.4
				list($host, $port) = explode(":", $server, 2); // part after : is used for port or socket
				$ssl = $adminer->connectSsl();
				if ($ssl) {
					$this->ssl_set($ssl['key'], $ssl['cert'], $ssl['ca'], '', '');
				}
				$return = @$this->real_connect(
					($server != "" ? $host : ini_get("mysqli.default_host")),
					($server . $username != "" ? $username : ini_get("mysqli.default_user")),
					($server . $username . $password != "" ? $password : ini_get("mysqli.default_pw")),
					$database,
					(is_numeric($port) ? $port : ini_get("mysqli.default_port")),
					(!is_numeric($port) ? $port : $socket),
					($ssl ? 64 : 0) // 64 - MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT (not available before PHP 5.6.16)
				);
				$this->options(MYSQLI_OPT_LOCAL_INFILE, false);
				return $return;
			}

			function set_charset($charset) {
				if (parent::set_charset($charset)) {
					return true;
				}
				// the client library may not support utf8mb4
				parent::set_charset('utf8');
				return $this->query("SET NAMES $charset");
			}

			function result($query, $field = 0) {
				$result = $this->query($query);
				if (!$result) {
					return false;
				}
				$row = $result->fetch_array();
				return $row[$field];
			}
			
			function quote($string) {
				return "'" . $this->escape_string($string) . "'";
			}
		}

	} elseif (extension_loaded("mysql") && !((ini_bool("sql.safe_mode") || ini_bool("mysql.allow_local_infile")) && extension_loaded("pdo_mysql"))) {
		class Min_DB {
			var
				$extension = "MySQL", ///< @var string extension name
				$server_info, ///< @var string server version
				$affected_rows, ///< @var int number of affected rows
				$errno, ///< @var int last error code
				$error, ///< @var string last error message
				$_link, $_result ///< @access private
			;

			/** Connect to server
			* @param string
			* @param string
			* @param string
			* @return bool
			*/
			function connect($server, $username, $password) {
				if (ini_bool("mysql.allow_local_infile")) {
					$this->error = lang(67, "'mysql.allow_local_infile'", "MySQLi", "PDO_MySQL");
					return false;
				}
				$this->_link = @mysql_connect(
					($server != "" ? $server : ini_get("mysql.default_host")),
					("$server$username" != "" ? $username : ini_get("mysql.default_user")),
					("$server$username$password" != "" ? $password : ini_get("mysql.default_password")),
					true,
					131072 // CLIENT_MULTI_RESULTS for CALL
				);
				if ($this->_link) {
					$this->server_info = mysql_get_server_info($this->_link);
				} else {
					$this->error = mysql_error();
				}
				return (bool) $this->_link;
			}

			/** Sets the client character set
			* @param string
			* @return bool
			*/
			function set_charset($charset) {
				if (function_exists('mysql_set_charset')) {
					if (mysql_set_charset($charset, $this->_link)) {
						return true;
					}
					// the client library may not support utf8mb4
					mysql_set_charset('utf8', $this->_link);
				}
				return $this->query("SET NAMES $charset");
			}

			/** Quote string to use in SQL
			* @param string
			* @return string escaped string enclosed in '
			*/
			function quote($string) {
				return "'" . mysql_real_escape_string($string, $this->_link) . "'";
			}

			/** Select database
			* @param string
			* @return bool
			*/
			function select_db($database) {
				return mysql_select_db($database, $this->_link);
			}

			/** Send query
			* @param string
			* @param bool
			* @return mixed bool or Min_Result
			*/
			function query($query, $unbuffered = false) {
				$result = @($unbuffered ? mysql_unbuffered_query($query, $this->_link) : mysql_query($query, $this->_link)); // @ - mute mysql.trace_mode
				$this->error = "";
				if (!$result) {
					$this->errno = mysql_errno($this->_link);
					$this->error = mysql_error($this->_link);
					return false;
				}
				if ($result === true) {
					$this->affected_rows = mysql_affected_rows($this->_link);
					$this->info = mysql_info($this->_link);
					return true;
				}
				return new Min_Result($result);
			}

			/** Send query with more resultsets
			* @param string
			* @return bool
			*/
			function multi_query($query) {
				return $this->_result = $this->query($query);
			}

			/** Get current resultset
			* @return Min_Result
			*/
			function store_result() {
				return $this->_result;
			}

			/** Fetch next resultset
			* @return bool
			*/
			function next_result() {
				// MySQL extension doesn't support multiple results
				return false;
			}

			/** Get single field from result
			* @param string
			* @param int
			* @return string
			*/
			function result($query, $field = 0) {
				$result = $this->query($query);
				if (!$result || !$result->num_rows) {
					return false;
				}
				return mysql_result($result->_result, 0, $field);
			}
		}

		class Min_Result {
			var
				$num_rows, ///< @var int number of rows in the result
				$_result, $_offset = 0 ///< @access private
			;

			/** Constructor
			* @param resource
			*/
			function __construct($result) {
				$this->_result = $result;
				$this->num_rows = mysql_num_rows($result);
			}

			/** Fetch next row as associative array
			* @return array
			*/
			function fetch_assoc() {
				return mysql_fetch_assoc($this->_result);
			}

			/** Fetch next row as numbered array
			* @return array
			*/
			function fetch_row() {
				return mysql_fetch_row($this->_result);
			}

			/** Fetch next field
			* @return object properties: name, type, orgtable, orgname, charsetnr
			*/
			function fetch_field() {
				$return = mysql_fetch_field($this->_result, $this->_offset++); // offset required under certain conditions
				$return->orgtable = $return->table;
				$return->orgname = $return->name;
				$return->charsetnr = ($return->blob ? 63 : 0);
				return $return;
			}

			/** Free result set
			*/
			function __destruct() {
				mysql_free_result($this->_result);
			}
		}

	} elseif (extension_loaded("pdo_mysql")) {
		class Min_DB extends Min_PDO {
			var $extension = "PDO_MySQL";

			function connect($server, $username, $password) {
				global $adminer;
				$options = array(PDO::MYSQL_ATTR_LOCAL_INFILE => false);
				$ssl = $adminer->connectSsl();
				if ($ssl) {
					if (!empty($ssl['key'])) {
						$options[PDO::MYSQL_ATTR_SSL_KEY] = $ssl['key'];
					}
					if (!empty($ssl['cert'])) {
						$options[PDO::MYSQL_ATTR_SSL_CERT] = $ssl['cert'];
					}
					if (!empty($ssl['ca'])) {
						$options[PDO::MYSQL_ATTR_SSL_CA] = $ssl['ca'];
					}
				}
				$this->dsn(
					"mysql:charset=utf8;host=" . str_replace(":", ";unix_socket=", preg_replace('~:(\d)~', ';port=\1', $server)),
					$username,
					$password,
					$options
				);
				return true;
			}

			function set_charset($charset) {
				$this->query("SET NAMES $charset"); // charset in DSN is ignored before PHP 5.3.6
			}

			function select_db($database) {
				// database selection is separated from the connection so dbname in DSN can't be used
				return $this->query("USE " . idf_escape($database));
			}

			function query($query, $unbuffered = false) {
				$this->pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, !$unbuffered);
				return parent::query($query, $unbuffered);
			}
		}

	}



	class Min_Driver extends Min_SQL {

		function insert($table, $set) {
			return ($set ? parent::insert($table, $set) : queries("INSERT INTO " . table($table) . " ()\nVALUES ()"));
		}

		function insertUpdate($table, $rows, $primary) {
			$columns = array_keys(reset($rows));
			$prefix = "INSERT INTO " . table($table) . " (" . implode(", ", $columns) . ") VALUES\n";
			$values = array();
			foreach ($columns as $key) {
				$values[$key] = "$key = VALUES($key)";
			}
			$suffix = "\nON DUPLICATE KEY UPDATE " . implode(", ", $values);
			$values = array();
			$length = 0;
			foreach ($rows as $set) {
				$value = "(" . implode(", ", $set) . ")";
				if ($values && (strlen($prefix) + $length + strlen($value) + strlen($suffix) > 1e6)) { // 1e6 - default max_allowed_packet
					if (!queries($prefix . implode(",\n", $values) . $suffix)) {
						return false;
					}
					$values = array();
					$length = 0;
				}
				$values[] = $value;
				$length += strlen($value) + 2; // 2 - strlen(",\n")
			}
			return queries($prefix . implode(",\n", $values) . $suffix);
		}
		
		function slowQuery($query, $timeout) {
			if (min_version('5.7.8', '10.1.2')) {
				if (preg_match('~MariaDB~', $this->_conn->server_info)) {
					return "SET STATEMENT max_statement_time=$timeout FOR $query";
				} elseif (preg_match('~^(SELECT\b)(.+)~is', $query, $match)) {
					return "$match[1] /*+ MAX_EXECUTION_TIME(" . ($timeout * 1000) . ") */ $match[2]";
				}
			}
		}

		function convertSearch($idf, $val, $field) {
			return (preg_match('~char|text|enum|set~', $field["type"]) && !preg_match("~^utf8~", $field["collation"]) && preg_match('~[\x80-\xFF]~', $val['val'])
				? "CONVERT($idf USING " . charset($this->_conn) . ")"
				: $idf
			);
		}
		
		function warnings() {
			$result = $this->_conn->query("SHOW WARNINGS");
			if ($result && $result->num_rows) {
				ob_start();
				select($result); // select() usually needs to print a big table progressively
				return ob_get_clean();
			}
		}

		function tableHelp($name) {
			$maria = preg_match('~MariaDB~', $this->_conn->server_info);
			if (information_schema(DB)) {
				return strtolower(($maria ? "information-schema-$name-table/" : str_replace("_", "-", $name) . "-table.html"));
			}
			if (DB == "mysql") {
				return ($maria ? "mysql$name-table/" : "system-database.html"); //! more precise link
			}
		}

	}



	/** Escape database identifier
	* @param string
	* @return string
	*/
	function idf_escape($idf) {
		return "`" . str_replace("`", "``", $idf) . "`";
	}

	/** Get escaped table name
	* @param string
	* @return string
	*/
	function table($idf) {
		return idf_escape($idf);
	}

	/** Connect to the database
	* @return mixed Min_DB or string for error
	*/
	function connect() {
		global $adminer, $types, $structured_types;
		$connection = new Min_DB;
		$credentials = $adminer->credentials();
		if ($connection->connect($credentials[0], $credentials[1], $credentials[2])) {
			$connection->set_charset(charset($connection)); // available in MySQLi since PHP 5.0.5
			$connection->query("SET sql_quote_show_create = 1, autocommit = 1");
			if (min_version('5.7.8', 10.2, $connection)) {
				$structured_types[lang(68)][] = "json";
				$types["json"] = 4294967295;
			}
			return $connection;
		}
		$return = $connection->error;
		if (function_exists('iconv') && !is_utf8($return) && strlen($s = iconv("windows-1250", "utf-8", $return)) > strlen($return)) { // windows-1250 - most common Windows encoding
			$return = $s;
		}
		return $return;
	}

	/** Get cached list of databases
	* @param bool
	* @return array
	*/
	function get_databases($flush) {
		// SHOW DATABASES can take a very long time so it is cached
		$return = get_session("dbs");
		if ($return === null) {
			$query = (min_version(5)
				? "SELECT SCHEMA_NAME FROM information_schema.SCHEMATA ORDER BY SCHEMA_NAME"
				: "SHOW DATABASES"
			); // SHOW DATABASES can be disabled by skip_show_database
			$return = ($flush ? slow_query($query) : get_vals($query));
			restart_session();
			set_session("dbs", $return);
			stop_session();
		}
		return $return;
	}

	/** Formulate SQL query with limit
	* @param string everything after SELECT
	* @param string including WHERE
	* @param int
	* @param int
	* @param string
	* @return string
	*/
	function limit($query, $where, $limit, $offset = 0, $separator = " ") {
		return " $query$where" . ($limit !== null ? $separator . "LIMIT $limit" . ($offset ? " OFFSET $offset" : "") : "");
	}

	/** Formulate SQL modification query with limit 1
	* @param string
	* @param string everything after UPDATE or DELETE
	* @param string
	* @param string
	* @return string
	*/
	function limit1($table, $query, $where, $separator = "\n") {
		return limit($query, $where, 1, 0, $separator);
	}

	/** Get database collation
	* @param string
	* @param array result of collations()
	* @return string
	*/
	function db_collation($db, $collations) {
		global $connection;
		$return = null;
		$create = $connection->result("SHOW CREATE DATABASE " . idf_escape($db), 1);
		if (preg_match('~ COLLATE ([^ ]+)~', $create, $match)) {
			$return = $match[1];
		} elseif (preg_match('~ CHARACTER SET ([^ ]+)~', $create, $match)) {
			// default collation
			$return = $collations[$match[1]][-1];
		}
		return $return;
	}

	/** Get supported engines
	* @return array
	*/
	function engines() {
		$return = array();
		foreach (get_rows("SHOW ENGINES") as $row) {
			if (preg_match("~YES|DEFAULT~", $row["Support"])) {
				$return[] = $row["Engine"];
			}
		}
		return $return;
	}

	/** Get logged user
	* @return string
	*/
	function logged_user() {
		global $connection;
		return $connection->result("SELECT USER()");
	}

	/** Get tables list
	* @return array array($name => $type)
	*/
	function tables_list() {
		return get_key_vals(min_version(5)
			? "SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME"
			: "SHOW TABLES"
		);
	}

	/** Count tables in all databases
	* @param array
	* @return array array($db => $tables)
	*/
	function count_tables($databases) {
		$return = array();
		foreach ($databases as $db) {
			$return[$db] = count(get_vals("SHOW TABLES IN " . idf_escape($db)));
		}
		return $return;
	}

	/** Get table status
	* @param string
	* @param bool return only "Name", "Engine" and "Comment" fields
	* @return array array($name => array("Name" => , "Engine" => , "Comment" => , "Oid" => , "Rows" => , "Collation" => , "Auto_increment" => , "Data_length" => , "Index_length" => , "Data_free" => )) or only inner array with $name
	*/
	function table_status($name = "", $fast = false) {
		$return = array();
		foreach (get_rows($fast && min_version(5)
			? "SELECT TABLE_NAME AS Name, ENGINE AS Engine, TABLE_COMMENT AS Comment FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() " . ($name != "" ? "AND TABLE_NAME = " . q($name) : "ORDER BY Name")
			: "SHOW TABLE STATUS" . ($name != "" ? " LIKE " . q(addcslashes($name, "%_\\")) : "")
		) as $row) {
			if ($row["Engine"] == "InnoDB") {
				// ignore internal comment, unnecessary since MySQL 5.1.21
				$row["Comment"] = preg_replace('~(?:(.+); )?InnoDB free: .*~', '\1', $row["Comment"]);
			}
			if (!isset($row["Engine"])) {
				$row["Comment"] = "";
			}
			if ($name != "") {
				return $row;
			}
			$return[$row["Name"]] = $row;
		}
		return $return;
	}

	/** Find out whether the identifier is view
	* @param array
	* @return bool
	*/
	function is_view($table_status) {
		return $table_status["Engine"] === null;
	}

	/** Check if table supports foreign keys
	* @param array result of table_status
	* @return bool
	*/
	function fk_support($table_status) {
		return preg_match('~InnoDB|IBMDB2I~i', $table_status["Engine"])
			|| (preg_match('~NDB~i', $table_status["Engine"]) && min_version(5.6));
	}

	/** Get information about fields
	* @param string
	* @return array array($name => array("field" => , "full_type" => , "type" => , "length" => , "unsigned" => , "default" => , "null" => , "auto_increment" => , "on_update" => , "collation" => , "privileges" => , "comment" => , "primary" => ))
	*/
	function fields($table) {
		$return = array();
		foreach (get_rows("SHOW FULL COLUMNS FROM " . table($table)) as $row) {
			preg_match('~^([^( ]+)(?:\((.+)\))?( unsigned)?( zerofill)?$~', $row["Type"], $match);
			$return[$row["Field"]] = array(
				"field" => $row["Field"],
				"full_type" => $row["Type"],
				"type" => $match[1],
				"length" => $match[2],
				"unsigned" => ltrim($match[3] . $match[4]),
				"default" => ($row["Default"] != "" || preg_match("~char|set~", $match[1]) ? (preg_match('~text~', $match[1]) ? stripslashes(preg_replace("~^'(.*)'\$~", '\1', $row["Default"])) : $row["Default"]) : null),
				"null" => ($row["Null"] == "YES"),
				"auto_increment" => ($row["Extra"] == "auto_increment"),
				"on_update" => (preg_match('~^on update (.+)~i', $row["Extra"], $match) ? $match[1] : ""), //! available since MySQL 5.1.23
				"collation" => $row["Collation"],
				"privileges" => array_flip(preg_split('~, *~', $row["Privileges"])),
				"comment" => $row["Comment"],
				"primary" => ($row["Key"] == "PRI"),
				// https://mariadb.com/kb/en/library/show-columns/, https://github.com/vrana/adminer/pull/359#pullrequestreview-276677186
				"generated" => preg_match('~^(VIRTUAL|PERSISTENT|STORED)~', $row["Extra"]),
			);
		}
		return $return;
	}

	/** Get table indexes
	* @param string
	* @param string Min_DB to use
	* @return array array($key_name => array("type" => , "columns" => array(), "lengths" => array(), "descs" => array()))
	*/
	function indexes($table, $connection2 = null) {
		$return = array();
		foreach (get_rows("SHOW INDEX FROM " . table($table), $connection2) as $row) {
			$name = $row["Key_name"];
			$return[$name]["type"] = ($name == "PRIMARY" ? "PRIMARY" : ($row["Index_type"] == "FULLTEXT" ? "FULLTEXT" : ($row["Non_unique"] ? ($row["Index_type"] == "SPATIAL" ? "SPATIAL" : "INDEX") : "UNIQUE")));
			$return[$name]["columns"][] = $row["Column_name"];
			$return[$name]["lengths"][] = ($row["Index_type"] == "SPATIAL" ? null : $row["Sub_part"]);
			$return[$name]["descs"][] = null;
		}
		return $return;
	}

	/** Get foreign keys in table
	* @param string
	* @return array array($name => array("db" => , "ns" => , "table" => , "source" => array(), "target" => array(), "on_delete" => , "on_update" => ))
	*/
	function foreign_keys($table) {
		global $connection, $on_actions;
		static $pattern = '(?:`(?:[^`]|``)+`|"(?:[^"]|"")+")';
		$return = array();
		$create_table = $connection->result("SHOW CREATE TABLE " . table($table), 1);
		if ($create_table) {
			preg_match_all("~CONSTRAINT ($pattern) FOREIGN KEY ?\\(((?:$pattern,? ?)+)\\) REFERENCES ($pattern)(?:\\.($pattern))? \\(((?:$pattern,? ?)+)\\)(?: ON DELETE ($on_actions))?(?: ON UPDATE ($on_actions))?~", $create_table, $matches, PREG_SET_ORDER);
			foreach ($matches as $match) {
				preg_match_all("~$pattern~", $match[2], $source);
				preg_match_all("~$pattern~", $match[5], $target);
				$return[idf_unescape($match[1])] = array(
					"db" => idf_unescape($match[4] != "" ? $match[3] : $match[4]),
					"table" => idf_unescape($match[4] != "" ? $match[4] : $match[3]),
					"source" => array_map('idf_unescape', $source[0]),
					"target" => array_map('idf_unescape', $target[0]),
					"on_delete" => ($match[6] ? $match[6] : "RESTRICT"),
					"on_update" => ($match[7] ? $match[7] : "RESTRICT"),
				);
			}
		}
		return $return;
	}

	/** Get view SELECT
	* @param string
	* @return array array("select" => )
	*/
	function view($name) {
		global $connection;
		return array("select" => preg_replace('~^(?:[^`]|`[^`]*`)*\s+AS\s+~isU', '', $connection->result("SHOW CREATE VIEW " . table($name), 1)));
	}

	/** Get sorted grouped list of collations
	* @return array
	*/
	function collations() {
		$return = array();
		foreach (get_rows("SHOW COLLATION") as $row) {
			if ($row["Default"]) {
				$return[$row["Charset"]][-1] = $row["Collation"];
			} else {
				$return[$row["Charset"]][] = $row["Collation"];
			}
		}
		ksort($return);
		foreach ($return as $key => $val) {
			asort($return[$key]);
		}
		return $return;
	}

	/** Find out if database is information_schema
	* @param string
	* @return bool
	*/
	function information_schema($db) {
		return (min_version(5) && $db == "information_schema")
			|| (min_version(5.5) && $db == "performance_schema");
	}

	/** Get escaped error message
	* @return string
	*/
	function error() {
		global $connection;
		return h(preg_replace('~^You have an error.*syntax to use~U', "Syntax error", $connection->error));
	}

	/** Create database
	* @param string
	* @param string
	* @return string
	*/
	function create_database($db, $collation) {
		return queries("CREATE DATABASE " . idf_escape($db) . ($collation ? " COLLATE " . q($collation) : ""));
	}

	/** Drop databases
	* @param array
	* @return bool
	*/
	function drop_databases($databases) {
		$return = apply_queries("DROP DATABASE", $databases, 'idf_escape');
		restart_session();
		set_session("dbs", null);
		return $return;
	}

	/** Rename database from DB
	* @param string new name
	* @param string
	* @return bool
	*/
	function rename_database($name, $collation) {
		$return = false;
		if (create_database($name, $collation)) {
			$tables = array();
			$views = array();
			foreach (tables_list() as $table => $type) {
				if ($type == 'VIEW') {
					$views[] = $table;
				} else {
					$tables[] = $table;
				}
			}
			$return = (!$tables && !$views) || move_tables($tables, $views, $name);
			drop_databases($return ? array(DB) : array());
		}
		return $return;
	}

	/** Generate modifier for auto increment column
	* @return string
	*/
	function auto_increment() {
		$auto_increment_index = " PRIMARY KEY";
		// don't overwrite primary key by auto_increment
		if ($_GET["create"] != "" && $_POST["auto_increment_col"]) {
			foreach (indexes($_GET["create"]) as $index) {
				if (in_array($_POST["fields"][$_POST["auto_increment_col"]]["orig"], $index["columns"], true)) {
					$auto_increment_index = "";
					break;
				}
				if ($index["type"] == "PRIMARY") {
					$auto_increment_index = " UNIQUE";
				}
			}
		}
		return " AUTO_INCREMENT$auto_increment_index";
	}

	/** Run commands to create or alter table
	* @param string "" to create
	* @param string new name
	* @param array of array($orig, $process_field, $after)
	* @param array of strings
	* @param string
	* @param string
	* @param string
	* @param string number
	* @param string
	* @return bool
	*/
	function alter_table($table, $name, $fields, $foreign, $comment, $engine, $collation, $auto_increment, $partitioning) {
		$alter = array();
		foreach ($fields as $field) {
			$alter[] = ($field[1]
				? ($table != "" ? ($field[0] != "" ? "CHANGE " . idf_escape($field[0]) : "ADD") : " ") . " " . implode($field[1]) . ($table != "" ? $field[2] : "")
				: "DROP " . idf_escape($field[0])
			);
		}
		$alter = array_merge($alter, $foreign);
		$status = ($comment !== null ? " COMMENT=" . q($comment) : "")
			. ($engine ? " ENGINE=" . q($engine) : "")
			. ($collation ? " COLLATE " . q($collation) : "")
			. ($auto_increment != "" ? " AUTO_INCREMENT=$auto_increment" : "")
		;
		if ($table == "") {
			return queries("CREATE TABLE " . table($name) . " (\n" . implode(",\n", $alter) . "\n)$status$partitioning");
		}
		if ($table != $name) {
			$alter[] = "RENAME TO " . table($name);
		}
		if ($status) {
			$alter[] = ltrim($status);
		}
		return ($alter || $partitioning ? queries("ALTER TABLE " . table($table) . "\n" . implode(",\n", $alter) . $partitioning) : true);
	}

	/** Run commands to alter indexes
	* @param string escaped table name
	* @param array of array("index type", "name", array("column definition", ...)) or array("index type", "name", "DROP")
	* @return bool
	*/
	function alter_indexes($table, $alter) {
		foreach ($alter as $key => $val) {
			$alter[$key] = ($val[2] == "DROP"
				? "\nDROP INDEX " . idf_escape($val[1])
				: "\nADD $val[0] " . ($val[0] == "PRIMARY" ? "KEY " : "") . ($val[1] != "" ? idf_escape($val[1]) . " " : "") . "(" . implode(", ", $val[2]) . ")"
			);
		}
		return queries("ALTER TABLE " . table($table) . implode(",", $alter));
	}

	/** Run commands to truncate tables
	* @param array
	* @return bool
	*/
	function truncate_tables($tables) {
		return apply_queries("TRUNCATE TABLE", $tables);
	}

	/** Drop views
	* @param array
	* @return bool
	*/
	function drop_views($views) {
		return queries("DROP VIEW " . implode(", ", array_map('table', $views)));
	}

	/** Drop tables
	* @param array
	* @return bool
	*/
	function drop_tables($tables) {
		return queries("DROP TABLE " . implode(", ", array_map('table', $tables)));
	}

	/** Move tables to other schema
	* @param array
	* @param array
	* @param string
	* @return bool
	*/
	function move_tables($tables, $views, $target) {
		global $connection;
		$rename = array();
		foreach ($tables as $table) {
			$rename[] = table($table) . " TO " . idf_escape($target) . "." . table($table);
		}
		if (!$rename || queries("RENAME TABLE " . implode(", ", $rename))) {
			$definitions = array();
			foreach ($views as $table) {
				$definitions[table($table)] = view($table);
			}
			$connection->select_db($target);
			$db = idf_escape(DB);
			foreach ($definitions as $name => $view) {
				if (!queries("CREATE VIEW $name AS " . str_replace(" $db.", " ", $view["select"])) || !queries("DROP VIEW $db.$name")) {
					return false;
				}
			}
			return true;
		}
		//! move triggers
		return false;
	}

	/** Copy tables to other schema
	* @param array
	* @param array
	* @param string
	* @return bool
	*/
	function copy_tables($tables, $views, $target) {
		queries("SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");
		foreach ($tables as $table) {
			$name = ($target == DB ? table("copy_$table") : idf_escape($target) . "." . table($table));
			if (($_POST["overwrite"] && !queries("\nDROP TABLE IF EXISTS $name"))
				|| !queries("CREATE TABLE $name LIKE " . table($table))
				|| !queries("INSERT INTO $name SELECT * FROM " . table($table))
			) {
				return false;
			}
			foreach (get_rows("SHOW TRIGGERS LIKE " . q(addcslashes($table, "%_\\"))) as $row) {
				$trigger = $row["Trigger"];
				if (!queries("CREATE TRIGGER " . ($target == DB ? idf_escape("copy_$trigger") : idf_escape($target) . "." . idf_escape($trigger)) . " $row[Timing] $row[Event] ON $name FOR EACH ROW\n$row[Statement];")) {
					return false;
				}
			}
		}
		foreach ($views as $table) {
			$name = ($target == DB ? table("copy_$table") : idf_escape($target) . "." . table($table));
			$view = view($table);
			if (($_POST["overwrite"] && !queries("DROP VIEW IF EXISTS $name"))
				|| !queries("CREATE VIEW $name AS $view[select]")) { //! USE to avoid db.table
				return false;
			}
		}
		return true;
	}

	/** Get information about trigger
	* @param string trigger name
	* @return array array("Trigger" => , "Timing" => , "Event" => , "Of" => , "Type" => , "Statement" => )
	*/
	function trigger($name) {
		if ($name == "") {
			return array();
		}
		$rows = get_rows("SHOW TRIGGERS WHERE `Trigger` = " . q($name));
		return reset($rows);
	}

	/** Get defined triggers
	* @param string
	* @return array array($name => array($timing, $event))
	*/
	function triggers($table) {
		$return = array();
		foreach (get_rows("SHOW TRIGGERS LIKE " . q(addcslashes($table, "%_\\"))) as $row) {
			$return[$row["Trigger"]] = array($row["Timing"], $row["Event"]);
		}
		return $return;
	}

	/** Get trigger options
	* @return array ("Timing" => array(), "Event" => array(), "Type" => array())
	*/
	function trigger_options() {
		return array(
			"Timing" => array("BEFORE", "AFTER"),
			"Event" => array("INSERT", "UPDATE", "DELETE"),
			"Type" => array("FOR EACH ROW"),
		);
	}

	/** Get information about stored routine
	* @param string
	* @param string "FUNCTION" or "PROCEDURE"
	* @return array ("fields" => array("field" => , "type" => , "length" => , "unsigned" => , "inout" => , "collation" => ), "returns" => , "definition" => , "language" => )
	*/
	function routine($name, $type) {
		global $connection, $enum_length, $inout, $types;
		$aliases = array("bool", "boolean", "integer", "double precision", "real", "dec", "numeric", "fixed", "national char", "national varchar");
		$space = "(?:\\s|/\\*[\s\S]*?\\*/|(?:#|-- )[^\n]*\n?|--\r?\n)";
		$type_pattern = "((" . implode("|", array_merge(array_keys($types), $aliases)) . ")\\b(?:\\s*\\(((?:[^'\")]|$enum_length)++)\\))?\\s*(zerofill\\s*)?(unsigned(?:\\s+zerofill)?)?)(?:\\s*(?:CHARSET|CHARACTER\\s+SET)\\s*['\"]?([^'\"\\s,]+)['\"]?)?";
		$pattern = "$space*(" . ($type == "FUNCTION" ? "" : $inout) . ")?\\s*(?:`((?:[^`]|``)*)`\\s*|\\b(\\S+)\\s+)$type_pattern";
		$create = $connection->result("SHOW CREATE $type " . idf_escape($name), 2);
		preg_match("~\\(((?:$pattern\\s*,?)*)\\)\\s*" . ($type == "FUNCTION" ? "RETURNS\\s+$type_pattern\\s+" : "") . "(.*)~is", $create, $match);
		$fields = array();
		preg_match_all("~$pattern\\s*,?~is", $match[1], $matches, PREG_SET_ORDER);
		foreach ($matches as $param) {
			$fields[] = array(
				"field" => str_replace("``", "`", $param[2]) . $param[3],
				"type" => strtolower($param[5]),
				"length" => preg_replace_callback("~$enum_length~s", 'normalize_enum', $param[6]),
				"unsigned" => strtolower(preg_replace('~\s+~', ' ', trim("$param[8] $param[7]"))),
				"null" => 1,
				"full_type" => $param[4],
				"inout" => strtoupper($param[1]),
				"collation" => strtolower($param[9]),
			);
		}
		if ($type != "FUNCTION") {
			return array("fields" => $fields, "definition" => $match[11]);
		}
		return array(
			"fields" => $fields,
			"returns" => array("type" => $match[12], "length" => $match[13], "unsigned" => $match[15], "collation" => $match[16]),
			"definition" => $match[17],
			"language" => "SQL", // available in information_schema.ROUTINES.PARAMETER_STYLE
		);
	}

	/** Get list of routines
	* @return array ("SPECIFIC_NAME" => , "ROUTINE_NAME" => , "ROUTINE_TYPE" => , "DTD_IDENTIFIER" => )
	*/
	function routines() {
		return get_rows("SELECT ROUTINE_NAME AS SPECIFIC_NAME, ROUTINE_NAME, ROUTINE_TYPE, DTD_IDENTIFIER FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = " . q(DB));
	}

	/** Get list of available routine languages
	* @return array
	*/
	function routine_languages() {
		return array(); // "SQL" not required
	}

	/** Get routine signature
	* @param string
	* @param array result of routine()
	* @return string
	*/
	function routine_id($name, $row) {
		return idf_escape($name);
	}

	/** Get last auto increment ID
	* @return string
	*/
	function last_id() {
		global $connection;
		return $connection->result("SELECT LAST_INSERT_ID()"); // mysql_insert_id() truncates bigint
	}

	/** Explain select
	* @param Min_DB
	* @param string
	* @return Min_Result
	*/
	function explain($connection, $query) {
		return $connection->query("EXPLAIN " . (min_version(5.1) && !min_version(5.7) ? "PARTITIONS " : "") . $query);
	}

	/** Get approximate number of rows
	* @param array
	* @param array
	* @return int or null if approximate number can't be retrieved
	*/
	function found_rows($table_status, $where) {
		return ($where || $table_status["Engine"] != "InnoDB" ? null : $table_status["Rows"]);
	}

	/** Get user defined types
	* @return array
	*/
	function types() {
		return array();
	}

	/** Get existing schemas
	* @return array
	*/
	function schemas() {
		return array();
	}

	/** Get current schema
	* @return string
	*/
	function get_schema() {
		return "";
	}

	/** Set current schema
	* @param string
	* @param Min_DB
	* @return bool
	*/
	function set_schema($schema, $connection2 = null) {
		return true;
	}

	/** Get SQL command to create table
	* @param string
	* @param bool
	* @param string
	* @return string
	*/
	function create_sql($table, $auto_increment, $style) {
		global $connection;
		$return = $connection->result("SHOW CREATE TABLE " . table($table), 1);
		if (!$auto_increment) {
			$return = preg_replace('~ AUTO_INCREMENT=\d+~', '', $return); //! skip comments
		}
		return $return;
	}

	/** Get SQL command to truncate table
	* @param string
	* @return string
	*/
	function truncate_sql($table) {
		return "TRUNCATE " . table($table);
	}

	/** Get SQL command to change database
	* @param string
	* @return string
	*/
	function use_sql($database) {
		return "USE " . idf_escape($database);
	}

	/** Get SQL commands to create triggers
	* @param string
	* @return string
	*/
	function trigger_sql($table) {
		$return = "";
		foreach (get_rows("SHOW TRIGGERS LIKE " . q(addcslashes($table, "%_\\")), null, "-- ") as $row) {
			$return .= "\nCREATE TRIGGER " . idf_escape($row["Trigger"]) . " $row[Timing] $row[Event] ON " . table($row["Table"]) . " FOR EACH ROW\n$row[Statement];;\n";
		}
		return $return;
	}

	/** Get server variables
	* @return array ($name => $value)
	*/
	function show_variables() {
		return get_key_vals("SHOW VARIABLES");
	}

	/** Get process list
	* @return array ($row)
	*/
	function process_list() {
		return get_rows("SHOW FULL PROCESSLIST");
	}

	/** Get status variables
	* @return array ($name => $value)
	*/
	function show_status() {
		return get_key_vals("SHOW STATUS");
	}

	/** Convert field in select and edit
	* @param array one element from fields()
	* @return string
	*/
	function convert_field($field) {
		if (preg_match("~binary~", $field["type"])) {
			return "HEX(" . idf_escape($field["field"]) . ")";
		}
		if ($field["type"] == "bit") {
			return "BIN(" . idf_escape($field["field"]) . " + 0)"; // + 0 is required outside MySQLnd
		}
		if (preg_match("~geometry|point|linestring|polygon~", $field["type"])) {
			return (min_version(8) ? "ST_" : "") . "AsWKT(" . idf_escape($field["field"]) . ")";
		}
	}

	/** Convert value in edit after applying functions back
	* @param array one element from fields()
	* @param string
	* @return string
	*/
	function unconvert_field($field, $return) {
		if (preg_match("~binary~", $field["type"])) {
			$return = "UNHEX($return)";
		}
		if ($field["type"] == "bit") {
			$return = "CONV($return, 2, 10) + 0";
		}
		if (preg_match("~geometry|point|linestring|polygon~", $field["type"])) {
			$return = (min_version(8) ? "ST_" : "") . "GeomFromText($return, SRID($field[field]))";
		}
		return $return;
	}

	/** Check whether a feature is supported
	* @param string "comment", "copy", "database", "descidx", "drop_col", "dump", "event", "indexes", "kill", "materializedview", "partitioning", "privileges", "procedure", "processlist", "routine", "scheme", "sequence", "status", "table", "trigger", "type", "variables", "view", "view_trigger"
	* @return bool
	*/
	function support($feature) {
		return !preg_match("~scheme|sequence|type|view_trigger|materializedview" . (min_version(8) ? "" : "|descidx" . (min_version(5.1) ? "" : "|event|partitioning" . (min_version(5) ? "" : "|routine|trigger|view"))) . "~", $feature);
	}

	/** Kill a process
	* @param int
	* @return bool
	*/
	function kill_process($val) {
		return queries("KILL " . number($val));
	}

	/** Return query to get connection ID
	* @return string
	*/
	function connection_id(){
		return "SELECT CONNECTION_ID()";
	}

	/** Get maximum number of connections
	* @return int
	*/
	function max_connections() {
		global $connection;
		return $connection->result("SELECT @@max_connections");
	}

	/** Get driver config
	* @return array array('possible_drivers' => , 'jush' => , 'types' => , 'structured_types' => , 'unsigned' => , 'operators' => , 'functions' => , 'grouping' => , 'edit_functions' => )
	*/
	function driver_config() {
		$types = array(); ///< @var array ($type => $maximum_unsigned_length, ...)
		$structured_types = array(); ///< @var array ($description => array($type, ...), ...)
		foreach (array(
			lang(69) => array("tinyint" => 3, "smallint" => 5, "mediumint" => 8, "int" => 10, "bigint" => 20, "decimal" => 66, "float" => 12, "double" => 21),
			lang(70) => array("date" => 10, "datetime" => 19, "timestamp" => 19, "time" => 10, "year" => 4),
			lang(68) => array("char" => 255, "varchar" => 65535, "tinytext" => 255, "text" => 65535, "mediumtext" => 16777215, "longtext" => 4294967295),
			lang(71) => array("enum" => 65535, "set" => 64),
			lang(72) => array("bit" => 20, "binary" => 255, "varbinary" => 65535, "tinyblob" => 255, "blob" => 65535, "mediumblob" => 16777215, "longblob" => 4294967295),
			lang(73) => array("geometry" => 0, "point" => 0, "linestring" => 0, "polygon" => 0, "multipoint" => 0, "multilinestring" => 0, "multipolygon" => 0, "geometrycollection" => 0),
		) as $key => $val) {
			$types += $val;
			$structured_types[$key] = array_keys($val);
		}
		return array(
			'possible_drivers' => array("MySQLi", "MySQL", "PDO_MySQL"),
			'jush' => "sql", ///< @var string JUSH identifier
			'types' => $types,
			'structured_types' => $structured_types,
			'unsigned' => array("unsigned", "zerofill", "unsigned zerofill"), ///< @var array number variants
			'operators' => array("=", "<", ">", "<=", ">=", "!=", "LIKE", "LIKE %%", "REGEXP", "IN", "FIND_IN_SET", "IS NULL", "NOT LIKE", "NOT REGEXP", "NOT IN", "IS NOT NULL", "SQL"), ///< @var array operators used in select
			'functions' => array("char_length", "date", "from_unixtime", "lower", "round", "floor", "ceil", "sec_to_time", "time_to_sec", "upper"), ///< @var array functions used in select
			'grouping' => array("avg", "count", "count distinct", "group_concat", "max", "min", "sum"), ///< @var array grouping functions used in select
			'edit_functions' => array( ///< @var array of array("$type|$type2" => "$function/$function2") functions used in editing, [0] - edit and insert, [1] - edit only
				array(
					"char" => "md5/sha1/password/encrypt/uuid",
					"binary" => "md5/sha1",
					"date|time" => "now",
				), array(
					number_type() => "+/-",
					"date" => "+ interval/- interval",
					"time" => "addtime/subtime",
					"char|text" => "concat",
				)
			),
		);
	}
}
 // must be included as last driver

$config = driver_config();
$possible_drivers = $config['possible_drivers'];
$jush = $config['jush'];
$types = $config['types'];
$structured_types = $config['structured_types'];
$unsigned = $config['unsigned'];
$operators = $config['operators'];
$functions = $config['functions'];
$grouping = $config['grouping'];
$edit_functions = $config['edit_functions'];
if ($adminer->operators === null) {
	$adminer->operators = $operators;
}

define("SERVER", $_GET[DRIVER]); // read from pgsql=localhost
define("DB", $_GET["db"]); // for the sake of speed and size
define("ME", preg_replace('~\?.*~', '', relative_uri()) . '?'
	. (sid() ? SID . '&' : '')
	. (SERVER !== null ? DRIVER . "=" . urlencode(SERVER) . '&' : '')
	. (isset($_GET["username"]) ? "username=" . urlencode($_GET["username"]) . '&' : '')
	. (DB != "" ? 'db=' . urlencode(DB) . '&' . (isset($_GET["ns"]) ? "ns=" . urlencode($_GET["ns"]) . "&" : "") : '')
);


$VERSION = "4.8.1";


/** Print HTML header
* @param string used in title, breadcrumb and heading, should be HTML escaped
* @param string
* @param mixed array("key" => "link", "key2" => array("link", "desc")), null for nothing, false for driver only, true for driver and server
* @param string used after colon in title and heading, should be HTML escaped
* @return null
*/
function page_header($title, $error = "", $breadcrumb = array(), $title2 = "") {
	global $LANG, $VERSION, $adminer, $drivers, $jush;
	page_headers();
	if (is_ajax() && $error) {
		page_messages($error);
		exit;
	}
	$title_all = $title . ($title2 != "" ? ": $title2" : "");
	$title_page = strip_tags($title_all . (SERVER != "" && SERVER != "localhost" ? h(" - " . SERVER) : "") . " - " . $adminer->name());
	?>
<!DOCTYPE html>
<html lang="<?php echo $LANG; ?>" dir="<?php echo lang(74); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="robots" content="noindex">
<title><?php echo $title_page; ?></title>
<link rel="stylesheet" type="text/css" href="<?php echo h(preg_replace("~\\?.*~", "", ME) . "?file=default.css&version=4.8.1"); ?>">
<?php echo script_src(preg_replace("~\\?.*~", "", ME) . "?file=functions.js&version=4.8.1");  if ($adminer->head()) { ?>
<link rel="shortcut icon" type="image/x-icon" href="<?php echo h(preg_replace("~\\?.*~", "", ME) . "?file=favicon.ico&version=4.8.1"); ?>">
<link rel="apple-touch-icon" href="<?php echo h(preg_replace("~\\?.*~", "", ME) . "?file=favicon.ico&version=4.8.1"); ?>">
<?php foreach ($adminer->css() as $css) { ?>
<link rel="stylesheet" type="text/css" href="<?php echo h($css); ?>">
<?php }  } ?>

<body class="<?php echo lang(74); ?> nojs">
<?php
	$filename = get_temp_dir() . "/adminer.version";
	if (!$_COOKIE["adminer_version"] && function_exists('openssl_verify') && file_exists($filename) && filemtime($filename) + 86400 > time()) { // 86400 - 1 day in seconds
		$version = unserialize(file_get_contents($filename));
		$public = "-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAwqWOVuF5uw7/+Z70djoK
RlHIZFZPO0uYRezq90+7Amk+FDNd7KkL5eDve+vHRJBLAszF/7XKXe11xwliIsFs
DFWQlsABVZB3oisKCBEuI71J4kPH8dKGEWR9jDHFw3cWmoH3PmqImX6FISWbG3B8
h7FIx3jEaw5ckVPVTeo5JRm/1DZzJxjyDenXvBQ/6o9DgZKeNDgxwKzH+sw9/YCO
jHnq1cFpOIISzARlrHMa/43YfeNRAm/tsBXjSxembBPo7aQZLAWHmaj5+K19H10B
nCpz9Y++cipkVEiKRGih4ZEvjoFysEOdRLj6WiD/uUNky4xGeA6LaJqh5XpkFkcQ
fQIDAQAB
-----END PUBLIC KEY-----
";
		if (openssl_verify($version["version"], base64_decode($version["signature"]), $public) == 1) {
			$_COOKIE["adminer_version"] = $version["version"]; // doesn't need to send to the browser
		}
	}
	?>
<script<?php echo nonce(); ?>>
mixin(document.body, {onkeydown: bodyKeydown, onclick: bodyClick<?php
	echo (isset($_COOKIE["adminer_version"]) ? "" : ", onload: partial(verifyVersion, '$VERSION', '" . js_escape(ME) . "', '" . get_token() . "')"); // $token may be empty in auth.inc.php
	?>});
document.body.className = document.body.className.replace(/ nojs/, ' js');
var offlineMessage = '<?php echo js_escape(lang(75)); ?>';
var thousandsSeparator = '<?php echo js_escape(lang(5)); ?>';
</script>

<div id="help" class="jush-<?php echo $jush; ?> jsonly hidden"></div>
<?php echo script("mixin(qs('#help'), {onmouseover: function () { helpOpen = 1; }, onmouseout: helpMouseout});"); ?>

<div id="content">
<?php
	if ($breadcrumb !== null) {
		$link = substr(preg_replace('~\b(username|db|ns)=[^&]*&~', '', ME), 0, -1);
		echo '<p id="breadcrumb"><a href="' . h($link ? $link : ".") . '">' . $drivers[DRIVER] . '</a> &raquo; ';
		$link = substr(preg_replace('~\b(db|ns)=[^&]*&~', '', ME), 0, -1);
		$server = $adminer->serverName(SERVER);
		$server = ($server != "" ? $server : lang(23));
		if ($breadcrumb === false) {
			echo "$server\n";
		} else {
			echo "<a href='" . h($link) . "' accesskey='1' title='Alt+Shift+1'>$server</a> &raquo; ";
			if ($_GET["ns"] != "" || (DB != "" && is_array($breadcrumb))) {
				echo '<a href="' . h($link . "&db=" . urlencode(DB) . (support("scheme") ? "&ns=" : "")) . '">' . h(DB) . '</a> &raquo; ';
			}
			if (is_array($breadcrumb)) {
				if ($_GET["ns"] != "") {
					echo '<a href="' . h(substr(ME, 0, -1)) . '">' . h($_GET["ns"]) . '</a> &raquo; ';
				}
				foreach ($breadcrumb as $key => $val) {
					$desc = (is_array($val) ? $val[1] : h($val));
					if ($desc != "") {
						echo "<a href='" . h(ME . "$key=") . urlencode(is_array($val) ? $val[0] : $val) . "'>$desc</a> &raquo; ";
					}
				}
			}
			echo "$title\n";
		}
	}
	echo "<h2>$title_all</h2>\n";
	echo "<div id='ajaxstatus' class='jsonly hidden'></div>\n";
	restart_session();
	page_messages($error);
	$databases = &get_session("dbs");
	if (DB != "" && $databases && !in_array(DB, $databases, true)) {
		$databases = null;
	}
	stop_session();
	define("PAGE_HEADER", 1);
}

/** Send HTTP headers
* @return null
*/
function page_headers() {
	global $adminer;
	header("Content-Type: text/html; charset=utf-8");
	header("Cache-Control: no-cache");
	header("X-Frame-Options: deny"); // ClickJacking protection in IE8, Safari 4, Chrome 2, Firefox 3.6.9
	header("X-XSS-Protection: 0"); // prevents introducing XSS in IE8 by removing safe parts of the page
	header("X-Content-Type-Options: nosniff");
	header("Referrer-Policy: origin-when-cross-origin");
	foreach ($adminer->csp() as $csp) {
		$header = array();
		foreach ($csp as $key => $val) {
			$header[] = "$key $val";
		}
		header("Content-Security-Policy: " . implode("; ", $header));
	}
	$adminer->headers();
}

/** Get Content Security Policy headers
* @return array of arrays with directive name in key, allowed sources in value
*/
function csp() {
	return array(
		array(
			"script-src" => "'self' 'unsafe-inline' 'nonce-" . get_nonce() . "' 'strict-dynamic'", // 'self' is a fallback for browsers not supporting 'strict-dynamic', 'unsafe-inline' is a fallback for browsers not supporting 'nonce-'
			"connect-src" => "'self'",
			"frame-src" => "https://www.adminer.org",
			"object-src" => "'none'",
			"base-uri" => "'none'",
			"form-action" => "'self'",
		),
	);
}

/** Get a CSP nonce
* @return string Base64 value
*/
function get_nonce() {
	static $nonce;
	if (!$nonce) {
		$nonce = base64_encode(rand_string());
	}
	return $nonce;
}

/** Print flash and error messages
* @param string
* @return null
*/
function page_messages($error) {
	$uri = preg_replace('~^[^?]*~', '', $_SERVER["REQUEST_URI"]);
	$messages = $_SESSION["messages"][$uri];
	if ($messages) {
		echo "<div class='message'>" . implode("</div>\n<div class='message'>", $messages) . "</div>" . script("messagesPrint();");
		unset($_SESSION["messages"][$uri]);
	}
	if ($error) {
		echo "<div class='error'>$error</div>\n";
	}
}

/** Print HTML footer
* @param string "auth", "db", "ns"
* @return null
*/
function page_footer($missing = "") {
	global $adminer, $token;
	?>
</div>

<?php switch_lang();  if ($missing != "auth") { ?>
<form action="" method="post">
<p class="logout">
<input type="submit" name="logout" value="<?php echo lang(76); ?>" id="logout">
<input type="hidden" name="token" value="<?php echo $token; ?>">
</p>
</form>
<?php } ?>
<div id="menu">
<?php $adminer->navigation($missing); ?>
</div>
<?php
	echo script("setupSubmitHighlight(document);");
}


/** PHP implementation of XXTEA encryption algorithm
* @author Ma Bingyao <andot@ujn.edu.cn>
* @link http://www.coolcode.cn/?action=show&id=128
*/

function int32($n) {
	while ($n >= 2147483648) {
		$n -= 4294967296;
	}
	while ($n <= -2147483649) {
		$n += 4294967296;
	}
	return (int) $n;
}

function long2str($v, $w) {
	$s = '';
	foreach ($v as $val) {
		$s .= pack('V', $val);
	}
	if ($w) {
		return substr($s, 0, end($v));
	}
	return $s;
}

function str2long($s, $w) {
	$v = array_values(unpack('V*', str_pad($s, 4 * ceil(strlen($s) / 4), "\0")));
	if ($w) {
		$v[] = strlen($s);
	}
	return $v;
}

function xxtea_mx($z, $y, $sum, $k) {
	return int32((($z >> 5 & 0x7FFFFFF) ^ $y << 2) + (($y >> 3 & 0x1FFFFFFF) ^ $z << 4)) ^ int32(($sum ^ $y) + ($k ^ $z));
}

/** Cipher
* @param string plain-text password
* @param string
* @return string binary cipher
*/
function encrypt_string($str, $key) {
	if ($str == "") {
		return "";
	}
	$key = array_values(unpack("V*", pack("H*", md5($key))));
	$v = str2long($str, true);
	$n = count($v) - 1;
	$z = $v[$n];
	$y = $v[0];
	$q = floor(6 + 52 / ($n + 1));
	$sum = 0;
	while ($q-- > 0) {
		$sum = int32($sum + 0x9E3779B9);
		$e = $sum >> 2 & 3;
		for ($p=0; $p < $n; $p++) {
			$y = $v[$p + 1];
			$mx = xxtea_mx($z, $y, $sum, $key[$p & 3 ^ $e]);
			$z = int32($v[$p] + $mx);
			$v[$p] = $z;
		}
		$y = $v[0];
		$mx = xxtea_mx($z, $y, $sum, $key[$p & 3 ^ $e]);
		$z = int32($v[$n] + $mx);
		$v[$n] = $z;
	}
	return long2str($v, false);
}

/** Decipher
* @param string binary cipher
* @param string
* @return string plain-text password
*/
function decrypt_string($str, $key) {
	if ($str == "") {
		return "";
	}
	if (!$key) {
		return false;
	}
	$key = array_values(unpack("V*", pack("H*", md5($key))));
	$v = str2long($str, false);
	$n = count($v) - 1;
	$z = $v[$n];
	$y = $v[0];
	$q = floor(6 + 52 / ($n + 1));
	$sum = int32($q * 0x9E3779B9);
	while ($sum) {
		$e = $sum >> 2 & 3;
		for ($p=$n; $p > 0; $p--) {
			$z = $v[$p - 1];
			$mx = xxtea_mx($z, $y, $sum, $key[$p & 3 ^ $e]);
			$y = int32($v[$p] - $mx);
			$v[$p] = $y;
		}
		$z = $v[$n];
		$mx = xxtea_mx($z, $y, $sum, $key[$p & 3 ^ $e]);
		$y = int32($v[0] - $mx);
		$v[0] = $y;
		$sum = int32($sum - 0x9E3779B9);
	}
	return long2str($v, true);
}


$connection = '';

$has_token = $_SESSION["token"];
if (!$has_token) {
	$_SESSION["token"] = rand(1, 1e6); // defense against cross-site request forgery
}
$token = get_token(); ///< @var string CSRF protection

$permanent = array();
if ($_COOKIE["adminer_permanent"]) {
	foreach (explode(" ", $_COOKIE["adminer_permanent"]) as $val) {
		list($key) = explode(":", $val);
		$permanent[$key] = $val;
	}
}

function add_invalid_login() {
	global $adminer;
	$fp = file_open_lock(get_temp_dir() . "/adminer.invalid");
	if (!$fp) {
		return;
	}
	$invalids = unserialize(stream_get_contents($fp));
	$time = time();
	if ($invalids) {
		foreach ($invalids as $ip => $val) {
			if ($val[0] < $time) {
				unset($invalids[$ip]);
			}
		}
	}
	$invalid = &$invalids[$adminer->bruteForceKey()];
	if (!$invalid) {
		$invalid = array($time + 30*60, 0); // active for 30 minutes
	}
	$invalid[1]++;
	file_write_unlock($fp, serialize($invalids));
}

function check_invalid_login() {
	global $adminer;
	$invalids = unserialize(@file_get_contents(get_temp_dir() . "/adminer.invalid")); // @ - may not exist
	$invalid = ($invalids ? $invalids[$adminer->bruteForceKey()] : array());
	$next_attempt = ($invalid[1] > 29 ? $invalid[0] - time() : 0); // allow 30 invalid attempts
	if ($next_attempt > 0) { //! do the same with permanent login
		auth_error(lang(77, ceil($next_attempt / 60)));
	}
}

$auth = $_POST["auth"];
if ($auth) {
	session_regenerate_id(); // defense against session fixation
	$vendor = $auth["driver"];
	$server = $auth["server"];
	$username = $auth["username"];
	$password = (string) $auth["password"];
	$db = $auth["db"];
	set_password($vendor, $server, $username, $password);
	$_SESSION["db"][$vendor][$server][$username][$db] = true;
	if ($auth["permanent"]) {
		$key = base64_encode($vendor) . "-" . base64_encode($server) . "-" . base64_encode($username) . "-" . base64_encode($db);
		$private = $adminer->permanentLogin(true);
		$permanent[$key] = "$key:" . base64_encode($private ? encrypt_string($password, $private) : "");
		cookie("adminer_permanent", implode(" ", $permanent));
	}
	if (count($_POST) == 1 // 1 - auth
		|| DRIVER != $vendor
		|| SERVER != $server
		|| $_GET["username"] !== $username // "0" == "00"
		|| DB != $db
	) {
		redirect(auth_url($vendor, $server, $username, $db));
	}
	
} elseif ($_POST["logout"] && (!$has_token || verify_token())) {
	foreach (array("pwds", "db", "dbs", "queries") as $key) {
		set_session($key, null);
	}
	unset_permanent();
	redirect(substr(preg_replace('~\b(username|db|ns)=[^&]*&~', '', ME), 0, -1), lang(78) . ' ' . lang(79));
	
} elseif ($permanent && !$_SESSION["pwds"]) {
	session_regenerate_id();
	$private = $adminer->permanentLogin();
	foreach ($permanent as $key => $val) {
		list(, $cipher) = explode(":", $val);
		list($vendor, $server, $username, $db) = array_map('base64_decode', explode("-", $key));
		set_password($vendor, $server, $username, decrypt_string(base64_decode($cipher), $private));
		$_SESSION["db"][$vendor][$server][$username][$db] = true;
	}
}

function unset_permanent() {
	global $permanent;
	foreach ($permanent as $key => $val) {
		list($vendor, $server, $username, $db) = array_map('base64_decode', explode("-", $key));
		if ($vendor == DRIVER && $server == SERVER && $username == $_GET["username"] && $db == DB) {
			unset($permanent[$key]);
		}
	}
	cookie("adminer_permanent", implode(" ", $permanent));
}

/** Renders an error message and a login form
* @param string plain text
* @return null exits
*/
function auth_error($error) {
	global $adminer, $has_token;
	$session_name = session_name();
	if (isset($_GET["username"])) {
		header("HTTP/1.1 403 Forbidden"); // 401 requires sending WWW-Authenticate header
		if (($_COOKIE[$session_name] || $_GET[$session_name]) && !$has_token) {
			$error = lang(80);
		} else {
			restart_session();
			add_invalid_login();
			$password = get_password();
			if ($password !== null) {
				if ($password === false) {
					$error .= ($error ? '<br>' : '') . lang(81, target_blank(), '<code>permanentLogin()</code>');
				}
				set_password(DRIVER, SERVER, $_GET["username"], null);
			}
			unset_permanent();
		}
	}
	if (!$_COOKIE[$session_name] && $_GET[$session_name] && ini_bool("session.use_only_cookies")) {
		$error = lang(82);
	}
	$params = session_get_cookie_params();
	cookie("adminer_key", ($_COOKIE["adminer_key"] ? $_COOKIE["adminer_key"] : rand_string()), $params["lifetime"]);
	page_header(lang(27), $error, null);
	echo "<form action='' method='post'>\n";
	echo "<div>";
	if (hidden_fields($_POST, array("auth"))) { // expired session
		echo "<p class='message'>" . lang(83) . "\n";
	}
	echo "</div>\n";
	$adminer->loginForm();
	echo "</form>\n";
	page_footer("auth");
	exit;
}

if (isset($_GET["username"]) && !class_exists("Min_DB")) {
	unset($_SESSION["pwds"][DRIVER]);
	unset_permanent();
	page_header(lang(84), lang(85, implode(", ", $possible_drivers)), false);
	page_footer("auth");
	exit;
}

stop_session(true);

if (isset($_GET["username"]) && is_string(get_password())) {
	list($host, $port) = explode(":", SERVER, 2);
	if (preg_match('~^\s*([-+]?\d+)~', $port, $match) && ($match[1] < 1024 || $match[1] > 65535)) { // is_numeric('80#') would still connect to port 80
		auth_error(lang(86));
	}
	check_invalid_login();
	$connection = connect();
	$driver = new Min_Driver($connection);
}

$login = null;
if (!is_object($connection) || ($login = $adminer->login($_GET["username"], get_password())) !== true) {
	$error = (is_string($connection) ? h($connection) : (is_string($login) ? $login : lang(87)));
	auth_error($error . (preg_match('~^ | $~', get_password()) ? '<br>' . lang(88) : ''));
}

if ($_POST["logout"] && $has_token && !verify_token()) {
	page_header(lang(76), lang(89));
	page_footer("db");
	exit;
}

if ($auth && $_POST["token"]) {
	$_POST["token"] = $token; // reset token after explicit login
}

$error = ''; ///< @var string
if ($_POST) {
	if (!verify_token()) {
		$ini = "max_input_vars";
		$max_vars = ini_get($ini);
		if (extension_loaded("suhosin")) {
			foreach (array("suhosin.request.max_vars", "suhosin.post.max_vars") as $key) {
				$val = ini_get($key);
				if ($val && (!$max_vars || $val < $max_vars)) {
					$ini = $key;
					$max_vars = $val;
				}
			}
		}
		$error = (!$_POST["token"] && $max_vars
			? lang(90, "'$ini'")
			: lang(89) . ' ' . lang(91)
		);
	}
	
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
	// posted form with no data means that post_max_size exceeded because Adminer always sends token at least
	$error = lang(92, "'post_max_size'");
	if (isset($_GET["sql"])) {
		$error .= ' ' . lang(93);
	}
}


/** Print select result
* @param Min_Result
* @param Min_DB connection to examine indexes
* @param array
* @param int
* @return array $orgtables
*/
function select($result, $connection2 = null, $orgtables = array(), $limit = 0) {
	global $jush;
	$links = array(); // colno => orgtable - create links from these columns
	$indexes = array(); // orgtable => array(column => colno) - primary keys
	$columns = array(); // orgtable => array(column => ) - not selected columns in primary key
	$blobs = array(); // colno => bool - display bytes for blobs
	$types = array(); // colno => type - display char in <code>
	$return = array(); // table => orgtable - mapping to use in EXPLAIN
	odd(''); // reset odd for each result
	for ($i=0; (!$limit || $i < $limit) && ($row = $result->fetch_row()); $i++) {
		if (!$i) {
			echo "<div class='scrollable'>\n";
			echo "<table cellspacing='0' class='nowrap'>\n";
			echo "<thead><tr>";
			for ($j=0; $j < count($row); $j++) {
				$field = $result->fetch_field();
				$name = $field->name;
				$orgtable = $field->orgtable;
				$orgname = $field->orgname;
				$return[$field->table] = $orgtable;
				if ($orgtables && $jush == "sql") { // MySQL EXPLAIN
					$links[$j] = ($name == "table" ? "table=" : ($name == "possible_keys" ? "indexes=" : null));
				} elseif ($orgtable != "") {
					if (!isset($indexes[$orgtable])) {
						// find primary key in each table
						$indexes[$orgtable] = array();
						foreach (indexes($orgtable, $connection2) as $index) {
							if ($index["type"] == "PRIMARY") {
								$indexes[$orgtable] = array_flip($index["columns"]);
								break;
							}
						}
						$columns[$orgtable] = $indexes[$orgtable];
					}
					if (isset($columns[$orgtable][$orgname])) {
						unset($columns[$orgtable][$orgname]);
						$indexes[$orgtable][$orgname] = $j;
						$links[$j] = $orgtable;
					}
				}
				if ($field->charsetnr == 63) { // 63 - binary
					$blobs[$j] = true;
				}
				$types[$j] = $field->type;
				echo "<th" . ($orgtable != "" || $field->name != $orgname ? " title='" . h(($orgtable != "" ? "$orgtable." : "") . $orgname) . "'" : "") . ">" . h($name)
					. ($orgtables ? doc_link(array(
						'sql' => "explain-output.html#explain_" . strtolower($name),
						'mariadb' => "explain/#the-columns-in-explain-select",
					)) : "")
				;
			}
			echo "</thead>\n";
		}
		echo "<tr" . odd() . ">";
		foreach ($row as $key => $val) {
			$link = "";
			if (isset($links[$key]) && !$columns[$links[$key]]) {
				if ($orgtables && $jush == "sql") { // MySQL EXPLAIN
					$table = $row[array_search("table=", $links)];
					$link = ME . $links[$key] . urlencode($orgtables[$table] != "" ? $orgtables[$table] : $table);
				} else {
					$link = ME . "edit=" . urlencode($links[$key]);
					foreach ($indexes[$links[$key]] as $col => $j) {
						$link .= "&where" . urlencode("[" . bracket_escape($col) . "]") . "=" . urlencode($row[$j]);
					}
				}
			} elseif (is_url($val)) {
				$link = $val;
			}
			if ($val === null) {
				$val = "<i>NULL</i>";
			} elseif ($blobs[$key] && !is_utf8($val)) {
				$val = "<i>" . lang(36, strlen($val)) . "</i>"; //! link to download
			} else {
				$val = h($val);
				if ($types[$key] == 254) { // 254 - char
					$val = "<code>$val</code>";
				}
			}
			if ($link) {
				$val = "<a href='" . h($link) . "'" . (is_url($link) ? target_blank() : '') . ">$val</a>";
			}
			echo "<td>$val";
		}
	}
	echo ($i ? "</table>\n</div>" : "<p class='message'>" . lang(12)) . "\n";
	return $return;
}

/** Get referencable tables with single column primary key except self
* @param string
* @return array ($table_name => $field)
*/
function referencable_primary($self) {
	$return = array(); // table_name => field
	foreach (table_status('', true) as $table_name => $table) {
		if ($table_name != $self && fk_support($table)) {
			foreach (fields($table_name) as $field) {
				if ($field["primary"]) {
					if ($return[$table_name]) { // multi column primary key
						unset($return[$table_name]);
						break;
					}
					$return[$table_name] = $field;
				}
			}
		}
	}
	return $return;
}

/** Get settings stored in a cookie
* @return array
*/
function adminer_settings() {
	parse_str($_COOKIE["adminer_settings"], $settings);
	return $settings;
}

/** Get setting stored in a cookie
* @param string
* @return array
*/
function adminer_setting($key) {
	$settings = adminer_settings();
	return $settings[$key];
}

/** Store settings to a cookie
* @param array
* @return bool
*/
function set_adminer_settings($settings) {
	return cookie("adminer_settings", http_build_query($settings + adminer_settings()));
}

/** Print SQL <textarea> tag
* @param string
* @param string or array in which case [0] of every element is used
* @param int
* @param int
* @return null
*/
function textarea($name, $value, $rows = 10, $cols = 80) {
	global $jush;
	echo "<textarea name='$name' rows='$rows' cols='$cols' class='sqlarea jush-$jush' spellcheck='false' wrap='off'>";
	if (is_array($value)) {
		foreach ($value as $val) { // not implode() to save memory
			echo h($val[0]) . "\n\n\n"; // $val == array($query, $time, $elapsed)
		}
	} else {
		echo h($value);
	}
	echo "</textarea>";
}

/** Print table columns for type edit
* @param string
* @param array
* @param array
* @param array returned by referencable_primary()
* @param array extra types to prepend
* @return null
*/
function edit_type($key, $field, $collations, $foreign_keys = array(), $extra_types = array()) {
	global $structured_types, $types, $unsigned, $on_actions;
	$type = $field["type"];
	?>
<td><select name="<?php echo h($key); ?>[type]" class="type" aria-labelledby="label-type"><?php
if ($type && !isset($types[$type]) && !isset($foreign_keys[$type]) && !in_array($type, $extra_types)) {
	$extra_types[] = $type;
}
if ($foreign_keys) {
	$structured_types[lang(94)] = $foreign_keys;
}
echo optionlist(array_merge($extra_types, $structured_types), $type);
?></select><td><input name="<?php echo h($key); ?>[length]" value="<?php echo h($field["length"]); ?>" size="3"<?php echo (!$field["length"] && preg_match('~var(char|binary)$~', $type) ? " class='required'" : ""); //! type="number" with enabled JavaScript ?> aria-labelledby="label-length"><td class="options"><?php
	echo "<select name='" . h($key) . "[collation]'" . (preg_match('~(char|text|enum|set)$~', $type) ? "" : " class='hidden'") . '><option value="">(' . lang(95) . ')' . optionlist($collations, $field["collation"]) . '</select>';
	echo ($unsigned ? "<select name='" . h($key) . "[unsigned]'" . (!$type || preg_match(number_type(), $type) ? "" : " class='hidden'") . '><option>' . optionlist($unsigned, $field["unsigned"]) . '</select>' : '');
	echo (isset($field['on_update']) ? "<select name='" . h($key) . "[on_update]'" . (preg_match('~timestamp|datetime~', $type) ? "" : " class='hidden'") . '>' . optionlist(array("" => "(" . lang(96) . ")", "CURRENT_TIMESTAMP"), (preg_match('~^CURRENT_TIMESTAMP~i', $field["on_update"]) ? "CURRENT_TIMESTAMP" : $field["on_update"])) . '</select>' : '');
	echo ($foreign_keys ? "<select name='" . h($key) . "[on_delete]'" . (preg_match("~`~", $type) ? "" : " class='hidden'") . "><option value=''>(" . lang(97) . ")" . optionlist(explode("|", $on_actions), $field["on_delete"]) . "</select> " : " "); // space for IE
}

/** Filter length value including enums
* @param string
* @return string
*/
function process_length($length) {
	global $enum_length;
	return (preg_match("~^\\s*\\(?\\s*$enum_length(?:\\s*,\\s*$enum_length)*+\\s*\\)?\\s*\$~", $length) && preg_match_all("~$enum_length~", $length, $matches)
		? "(" . implode(",", $matches[0]) . ")"
		: preg_replace('~^[0-9].*~', '(\0)', preg_replace('~[^-0-9,+()[\]]~', '', $length))
	);
}

/** Create SQL string from field type
* @param array
* @param string
* @return string
*/
function process_type($field, $collate = "COLLATE") {
	global $unsigned;
	return " $field[type]"
		. process_length($field["length"])
		. (preg_match(number_type(), $field["type"]) && in_array($field["unsigned"], $unsigned) ? " $field[unsigned]" : "")
		. (preg_match('~char|text|enum|set~', $field["type"]) && $field["collation"] ? " $collate " . q($field["collation"]) : "")
	;
}

/** Create SQL string from field
* @param array basic field information
* @param array information about field type
* @return array array("field", "type", "NULL", "DEFAULT", "ON UPDATE", "COMMENT", "AUTO_INCREMENT")
*/
function process_field($field, $type_field) {
	return array(
		idf_escape(trim($field["field"])),
		process_type($type_field),
		($field["null"] ? " NULL" : " NOT NULL"), // NULL for timestamp
		default_value($field),
		(preg_match('~timestamp|datetime~', $field["type"]) && $field["on_update"] ? " ON UPDATE $field[on_update]" : ""),
		(support("comment") && $field["comment"] != "" ? " COMMENT " . q($field["comment"]) : ""),
		($field["auto_increment"] ? auto_increment() : null),
	);
}

/** Get default value clause
* @param array
* @return string
*/
function default_value($field) {
	$default = $field["default"];
	return ($default === null ? "" : " DEFAULT " . (preg_match('~char|binary|text|enum|set~', $field["type"]) || preg_match('~^(?![a-z])~i', $default) ? q($default) : $default));
}

/** Get type class to use in CSS
* @param string
* @return string class=''
*/
function type_class($type) {
	foreach (array(
		'char' => 'text',
		'date' => 'time|year',
		'binary' => 'blob',
		'enum' => 'set',
	) as $key => $val) {
		if (preg_match("~$key|$val~", $type)) {
			return " class='$key'";
		}
	}
}

/** Print table interior for fields editing
* @param array
* @param array
* @param string TABLE or PROCEDURE
* @param array returned by referencable_primary()
* @return null
*/
function edit_fields($fields, $collations, $type = "TABLE", $foreign_keys = array()) {
	global $inout;
	$fields = array_values($fields);
	$default_class = (($_POST ? $_POST["defaults"] : adminer_setting("defaults")) ? "" : " class='hidden'");
	$comment_class = (($_POST ? $_POST["comments"] : adminer_setting("comments")) ? "" : " class='hidden'");
	?>
<thead><tr>
<?php if ($type == "PROCEDURE") { ?><td><?php } ?>
<th id="label-name"><?php echo ($type == "TABLE" ? lang(98) : lang(99)); ?>
<td id="label-type"><?php echo lang(38); ?><textarea id="enum-edit" rows="4" cols="12" wrap="off" style="display: none;"></textarea><?php echo script("qs('#enum-edit').onblur = editingLengthBlur;"); ?>
<td id="label-length"><?php echo lang(100); ?>
<td><?php echo lang(101); /* no label required, options have their own label */  if ($type == "TABLE") { ?>
<td id="label-null">NULL
<td><input type="radio" name="auto_increment_col" value=""><acronym id="label-ai" title="<?php echo lang(40); ?>">AI</acronym><?php echo doc_link(array(
	'sql' => "example-auto-increment.html",
	'mariadb' => "auto_increment/",
	
	
	
)); ?>
<td id="label-default"<?php echo $default_class; ?>><?php echo lang(41);  echo (support("comment") ? "<td id='label-comment'$comment_class>" . lang(39) : "");  } ?>
<td><?php echo "<input type='image' class='icon' name='add[" . (support("move_col") ? 0 : count($fields)) . "]' src='" . h(preg_replace("~\\?.*~", "", ME) . "?file=plus.gif&version=4.8.1") . "' alt='+' title='" . lang(102) . "'>" . script("row_count = " . count($fields) . ";"); ?>
</thead>
<tbody>
<?php
	echo script("mixin(qsl('tbody'), {onclick: editingClick, onkeydown: editingKeydown, oninput: editingInput});");
	foreach ($fields as $i => $field) {
		$i++;
		$orig = $field[($_POST ? "orig" : "field")];
		$display = (isset($_POST["add"][$i-1]) || (isset($field["field"]) && !$_POST["drop_col"][$i])) && (support("drop_col") || $orig == "");
		?>
<tr<?php echo ($display ? "" : " style='display: none;'"); ?>>
<?php echo ($type == "PROCEDURE" ? "<td>" . html_select("fields[$i][inout]", explode("|", $inout), $field["inout"]) : ""); ?>
<th><?php if ($display) { ?><input name="fields[<?php echo $i; ?>][field]" value="<?php echo h($field["field"]); ?>" data-maxlength="64" autocapitalize="off" aria-labelledby="label-name"><?php } ?>
<input type="hidden" name="fields[<?php echo $i; ?>][orig]" value="<?php echo h($orig); ?>"><?php edit_type("fields[$i]", $field, $collations, $foreign_keys);  if ($type == "TABLE") { ?>
<td><?php echo checkbox("fields[$i][null]", 1, $field["null"], "", "", "block", "label-null"); ?>
<td><label class="block"><input type="radio" name="auto_increment_col" value="<?php echo $i; ?>"<?php if ($field["auto_increment"]) { ?> checked<?php } ?> aria-labelledby="label-ai"></label><td<?php echo $default_class; ?>><?php
			echo checkbox("fields[$i][has_default]", 1, $field["has_default"], "", "", "", "label-default"); ?><input name="fields[<?php echo $i; ?>][default]" value="<?php echo h($field["default"]); ?>" aria-labelledby="label-default"><?php
			echo (support("comment") ? "<td$comment_class><input name='fields[$i][comment]' value='" . h($field["comment"]) . "' data-maxlength='" . (min_version(5.5) ? 1024 : 255) . "' aria-labelledby='label-comment'>" : "");
		}
		echo "<td>";
		echo (support("move_col") ?
			"<input type='image' class='icon' name='add[$i]' src='" . h(preg_replace("~\\?.*~", "", ME) . "?file=plus.gif&version=4.8.1") . "' alt='+' title='" . lang(102) . "'> "
			. "<input type='image' class='icon' name='up[$i]' src='" . h(preg_replace("~\\?.*~", "", ME) . "?file=up.gif&version=4.8.1") . "' alt='↑' title='" . lang(103) . "'> "
			. "<input type='image' class='icon' name='down[$i]' src='" . h(preg_replace("~\\?.*~", "", ME) . "?file=down.gif&version=4.8.1") . "' alt='↓' title='" . lang(104) . "'> "
		: "");
		echo ($orig == "" || support("drop_col") ? "<input type='image' class='icon' name='drop_col[$i]' src='" . h(preg_replace("~\\?.*~", "", ME) . "?file=cross.gif&version=4.8.1") . "' alt='x' title='" . lang(105) . "'>" : "");
	}
}

/** Move fields up and down or add field
* @param array
* @return bool
*/
function process_fields(&$fields) {
	$offset = 0;
	if ($_POST["up"]) {
		$last = 0;
		foreach ($fields as $key => $field) {
			if (key($_POST["up"]) == $key) {
				unset($fields[$key]);
				array_splice($fields, $last, 0, array($field));
				break;
			}
			if (isset($field["field"])) {
				$last = $offset;
			}
			$offset++;
		}
	} elseif ($_POST["down"]) {
		$found = false;
		foreach ($fields as $key => $field) {
			if (isset($field["field"]) && $found) {
				unset($fields[key($_POST["down"])]);
				array_splice($fields, $offset, 0, array($found));
				break;
			}
			if (key($_POST["down"]) == $key) {
				$found = $field;
			}
			$offset++;
		}
	} elseif ($_POST["add"]) {
		$fields = array_values($fields);
		array_splice($fields, key($_POST["add"]), 0, array(array()));
	} elseif (!$_POST["drop_col"]) {
		return false;
	}
	return true;
}

/** Callback used in routine()
* @param array
* @return string
*/
function normalize_enum($match) {
	return "'" . str_replace("'", "''", addcslashes(stripcslashes(str_replace($match[0][0] . $match[0][0], $match[0][0], substr($match[0], 1, -1))), '\\')) . "'";
}

/** Issue grant or revoke commands
* @param string GRANT or REVOKE
* @param array
* @param string
* @param string
* @return bool
*/
function grant($grant, $privileges, $columns, $on) {
	if (!$privileges) {
		return true;
	}
	if ($privileges == array("ALL PRIVILEGES", "GRANT OPTION")) {
		// can't be granted or revoked together
		return ($grant == "GRANT"
			? queries("$grant ALL PRIVILEGES$on WITH GRANT OPTION")
			: queries("$grant ALL PRIVILEGES$on") && queries("$grant GRANT OPTION$on")
		);
	}
	return queries("$grant " . preg_replace('~(GRANT OPTION)\([^)]*\)~', '\1', implode("$columns, ", $privileges) . $columns) . $on);
}

/** Drop old object and create a new one
* @param string drop old object query
* @param string create new object query
* @param string drop new object query
* @param string create test object query
* @param string drop test object query
* @param string
* @param string
* @param string
* @param string
* @param string
* @param string
* @return null redirect in success
*/
function drop_create($drop, $create, $drop_created, $test, $drop_test, $location, $message_drop, $message_alter, $message_create, $old_name, $new_name) {
	if ($_POST["drop"]) {
		query_redirect($drop, $location, $message_drop);
	} elseif ($old_name == "") {
		query_redirect($create, $location, $message_create);
	} elseif ($old_name != $new_name) {
		$created = queries($create);
		queries_redirect($location, $message_alter, $created && queries($drop));
		if ($created) {
			queries($drop_created);
		}
	} else {
		queries_redirect(
			$location,
			$message_alter,
			queries($test) && queries($drop_test) && queries($drop) && queries($create)
		);
	}
}

/** Generate SQL query for creating trigger
* @param string
* @param array result of trigger()
* @return string
*/
function create_trigger($on, $row) {
	global $jush;
	$timing_event = " $row[Timing] $row[Event]" . (preg_match('~ OF~', $row["Event"]) ? " $row[Of]" : ""); // SQL injection
	return "CREATE TRIGGER "
		. idf_escape($row["Trigger"])
		. ($jush == "mssql" ? $on . $timing_event : $timing_event . $on)
		. rtrim(" $row[Type]\n$row[Statement]", ";")
		. ";"
	;
}

/** Generate SQL query for creating routine
* @param string "PROCEDURE" or "FUNCTION"
* @param array result of routine()
* @return string
*/
function create_routine($routine, $row) {
	global $inout, $jush;
	$set = array();
	$fields = (array) $row["fields"];
	ksort($fields); // enforce fields order
	foreach ($fields as $field) {
		if ($field["field"] != "") {
			$set[] = (preg_match("~^($inout)\$~", $field["inout"]) ? "$field[inout] " : "") . idf_escape($field["field"]) . process_type($field, "CHARACTER SET");
		}
	}
	$definition = rtrim("\n$row[definition]", ";");
	return "CREATE $routine "
		. idf_escape(trim($row["name"]))
		. " (" . implode(", ", $set) . ")"
		. (isset($_GET["function"]) ? " RETURNS" . process_type($row["returns"], "CHARACTER SET") : "")
		. ($row["language"] ? " LANGUAGE $row[language]" : "")
		. ($jush == "pgsql" ? " AS " . q($definition) : "$definition;")
	;
}

/** Remove current user definer from SQL command
* @param string
* @return string
*/
function remove_definer($query) {
	return preg_replace('~^([A-Z =]+) DEFINER=`' . preg_replace('~@(.*)~', '`@`(%|\1)', logged_user()) . '`~', '\1', $query); //! proper escaping of user
}

/** Format foreign key to use in SQL query
* @param array ("db" => string, "ns" => string, "table" => string, "source" => array, "target" => array, "on_delete" => one of $on_actions, "on_update" => one of $on_actions)
* @return string
*/
function format_foreign_key($foreign_key) {
	global $on_actions;
	$db = $foreign_key["db"];
	$ns = $foreign_key["ns"];
	return " FOREIGN KEY (" . implode(", ", array_map('idf_escape', $foreign_key["source"])) . ") REFERENCES "
		. ($db != "" && $db != $_GET["db"] ? idf_escape($db) . "." : "")
		. ($ns != "" && $ns != $_GET["ns"] ? idf_escape($ns) . "." : "")
		. table($foreign_key["table"])
		. " (" . implode(", ", array_map('idf_escape', $foreign_key["target"])) . ")" //! reuse $name - check in older MySQL versions
		. (preg_match("~^($on_actions)\$~", $foreign_key["on_delete"]) ? " ON DELETE $foreign_key[on_delete]" : "")
		. (preg_match("~^($on_actions)\$~", $foreign_key["on_update"]) ? " ON UPDATE $foreign_key[on_update]" : "")
	;
}

/** Add a file to TAR
* @param string
* @param TmpFile
* @return null prints the output
*/
function tar_file($filename, $tmp_file) {
	$return = pack("a100a8a8a8a12a12", $filename, 644, 0, 0, decoct($tmp_file->size), decoct(time()));
	$checksum = 8*32; // space for checksum itself
	for ($i=0; $i < strlen($return); $i++) {
		$checksum += ord($return[$i]);
	}
	$return .= sprintf("%06o", $checksum) . "\0 ";
	echo $return;
	echo str_repeat("\0", 512 - strlen($return));
	$tmp_file->send();
	echo str_repeat("\0", 511 - ($tmp_file->size + 511) % 512);
}

/** Get INI bytes value
* @param string
* @return int
*/
function ini_bytes($ini) {
	$val = ini_get($ini);
	switch (strtolower(substr($val, -1))) {
		case 'g': $val *= 1024; // no break
		case 'm': $val *= 1024; // no break
		case 'k': $val *= 1024;
	}
	return $val;
}

/** Create link to database documentation
* @param array $jush => $path
* @param string HTML code
* @return string HTML code
*/
function doc_link($paths, $text = "<sup>?</sup>") {
	global $jush, $connection;
	$server_info = $connection->server_info;
	$version = preg_replace('~^(\d\.?\d).*~s', '\1', $server_info); // two most significant digits
	$urls = array(
		'sql' => "https://dev.mysql.com/doc/refman/$version/en/",
		'sqlite' => "https://www.sqlite.org/",
		'pgsql' => "https://www.postgresql.org/docs/$version/",
		'mssql' => "https://msdn.microsoft.com/library/",
		'oracle' => "https://www.oracle.com/pls/topic/lookup?ctx=db" . preg_replace('~^.* (\d+)\.(\d+)\.\d+\.\d+\.\d+.*~s', '\1\2', $server_info) . "&id=",
	);
	if (preg_match('~MariaDB~', $server_info)) {
		$urls['sql'] = "https://mariadb.com/kb/en/library/";
		$paths['sql'] = (isset($paths['mariadb']) ? $paths['mariadb'] : str_replace(".html", "/", $paths['sql']));
	}
	return ($paths[$jush] ? "<a href='" . h($urls[$jush] . $paths[$jush]) . "'" . target_blank() . ">$text</a>" : "");
}

/** Wrap gzencode() for usage in ob_start()
* @param string
* @return string
*/
function ob_gzencode($string) {
	// ob_start() callback recieves an optional parameter $phase but gzencode() accepts optional parameter $level
	return gzencode($string);
}

/** Compute size of database
* @param string
* @return string formatted
*/
function db_size($db) {
	global $connection;
	if (!$connection->select_db($db)) {
		return "?";
	}
	$return = 0;
	foreach (table_status() as $table_status) {
		$return += $table_status["Data_length"] + $table_status["Index_length"];
	}
	return format_number($return);
}

/** Print SET NAMES if utf8mb4 might be needed
* @param string
* @return null
*/
function set_utf8mb4($create) {
	global $connection;
	static $set = false;
	if (!$set && preg_match('~\butf8mb4~i', $create)) { // possible false positive
		$set = true;
		echo "SET NAMES " . charset($connection) . ";\n\n";
	}
}


function connect_error() {
	global $adminer, $connection, $token, $error, $drivers;
	if (DB != "") {
		header("HTTP/1.1 404 Not Found");
		page_header(lang(26) . ": " . h(DB), lang(106), true);
	} else {
		if ($_POST["db"] && !$error) {
			queries_redirect(substr(ME, 0, -1), lang(107), drop_databases($_POST["db"]));
		}
		
		page_header(lang(108), $error, false);
		echo "<p class='links'>\n";
		foreach (array(
			'database' => lang(109),
			'privileges' => lang(60),
			'processlist' => lang(110),
			'variables' => lang(111),
			'status' => lang(112),
		) as $key => $val) {
			if (support($key)) {
				echo "<a href='" . h(ME) . "$key='>$val</a>\n";
			}
		}
		echo "<p>" . lang(113, $drivers[DRIVER], "<b>" . h($connection->server_info) . "</b>", "<b>$connection->extension</b>") . "\n";
		echo "<p>" . lang(114, "<b>" . h(logged_user()) . "</b>") . "\n";
		$databases = $adminer->databases();
		if ($databases) {
			$scheme = support("scheme");
			$collations = collations();
			echo "<form action='' method='post'>\n";
			echo "<table cellspacing='0' class='checkable'>\n";
			echo script("mixin(qsl('table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});");
			echo "<thead><tr>"
				. (support("database") ? "<td>" : "")
				. "<th>" . lang(26) . " - <a href='" . h(ME) . "refresh=1'>" . lang(115) . "</a>"
				. "<td>" . lang(116)
				. "<td>" . lang(117)
				. "<td>" . lang(118) . " - <a href='" . h(ME) . "dbsize=1'>" . lang(119) . "</a>" . script("qsl('a').onclick = partial(ajaxSetHtml, '" . js_escape(ME) . "script=connect');", "")
				. "</thead>\n"
			;
			
			$databases = ($_GET["dbsize"] ? count_tables($databases) : array_flip($databases));
			
			foreach ($databases as $db => $tables) {
				$root = h(ME) . "db=" . urlencode($db);
				$id = h("Db-" . $db);
				echo "<tr" . odd() . ">" . (support("database") ? "<td>" . checkbox("db[]", $db, in_array($db, (array) $_POST["db"]), "", "", "", $id) : "");
				echo "<th><a href='$root' id='$id'>" . h($db) . "</a>";
				$collation = h(db_collation($db, $collations));
				echo "<td>" . (support("database") ? "<a href='$root" . ($scheme ? "&amp;ns=" : "") . "&amp;database=' title='" . lang(56) . "'>$collation</a>" : $collation);
				echo "<td align='right'><a href='$root&amp;schema=' id='tables-" . h($db) . "' title='" . lang(59) . "'>" . ($_GET["dbsize"] ? $tables : "?") . "</a>";
				echo "<td align='right' id='size-" . h($db) . "'>" . ($_GET["dbsize"] ? db_size($db) : "?");
				echo "\n";
			}
			
			echo "</table>\n";
			echo (support("database")
				? "<div class='footer'><div>\n"
					. "<fieldset><legend>" . lang(120) . " <span id='selected'></span></legend><div>\n"
					. "<input type='hidden' name='all' value=''>" . script("qsl('input').onclick = function () { selectCount('selected', formChecked(this, /^db/)); };") // used by trCheck()
					. "<input type='submit' name='drop' value='" . lang(121) . "'>" . confirm() . "\n"
					. "</div></fieldset>\n"
					. "</div></div>\n"
				: ""
			);
			echo "<input type='hidden' name='token' value='$token'>\n";
			echo "</form>\n";
			echo script("tableCheck();");
		}
	}
	
	page_footer("db");
}

if (isset($_GET["status"])) {
	$_GET["variables"] = $_GET["status"];
}
if (isset($_GET["import"])) {
	$_GET["sql"] = $_GET["import"];
}

if (!(DB != "" ? $connection->select_db(DB) : isset($_GET["sql"]) || isset($_GET["dump"]) || isset($_GET["database"]) || isset($_GET["processlist"]) || isset($_GET["privileges"]) || isset($_GET["user"]) || isset($_GET["variables"]) || $_GET["script"] == "connect" || $_GET["script"] == "kill")) {
	if (DB != "" || $_GET["refresh"]) {
		restart_session();
		set_session("dbs", null);
	}
	connect_error(); // separate function to catch SQLite error
	exit;
}




$on_actions = "RESTRICT|NO ACTION|CASCADE|SET NULL|SET DEFAULT"; ///< @var string used in foreign_keys()



class TmpFile {
	var $handler;
	var $size;
	
	function __construct() {
		$this->handler = tmpfile();
	}
	
	function write($contents) {
		$this->size += strlen($contents);
		fwrite($this->handler, $contents);
	}
	
	function send() {
		fseek($this->handler, 0);
		fpassthru($this->handler);
		fclose($this->handler);
	}
	
}


$enum_length = "'(?:''|[^'\\\\]|\\\\.)*'";
$inout = "IN|OUT|INOUT";

if (isset($_GET["select"]) && ($_POST["edit"] || $_POST["clone"]) && !$_POST["save"]) {
	$_GET["edit"] = $_GET["select"];
}
if (isset($_GET["callf"])) {
	$_GET["call"] = $_GET["callf"];
}
if (isset($_GET["function"])) {
	$_GET["procedure"] = $_GET["function"];
}

if (isset($_GET["download"])) {
	
$TABLE = $_GET["download"];
$fields = fields($TABLE);
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=" . friendly_url("$TABLE-" . implode("_", $_GET["where"])) . "." . friendly_url($_GET["field"]));
$select = array(idf_escape($_GET["field"]));
$result = $driver->select($TABLE, $select, array(where($_GET, $fields)), $select);
$row = ($result ? $result->fetch_row() : array());
echo $driver->value($row[0], $fields[$_GET["field"]]);
exit; // don't output footer

} elseif (isset($_GET["table"])) {
	
$TABLE = $_GET["table"];
$fields = fields($TABLE);
if (!$fields) {
	$error = error();
}
$table_status = table_status1($TABLE, true);
$name = $adminer->tableName($table_status);

page_header(($fields && is_view($table_status) ? $table_status['Engine'] == 'materialized view' ? lang(122) : lang(123) : lang(124)) . ": " . ($name != "" ? $name : h($TABLE)), $error);

$adminer->selectLinks($table_status);
$comment = $table_status["Comment"];
if ($comment != "") {
	echo "<p class='nowrap'>" . lang(39) . ": " . h($comment) . "\n";
}

if ($fields) {
	$adminer->tableStructurePrint($fields);
}

if (!is_view($table_status)) {
	if (support("indexes")) {
		echo "<h3 id='indexes'>" . lang(125) . "</h3>\n";
		$indexes = indexes($TABLE);
		if ($indexes) {
			$adminer->tableIndexesPrint($indexes);
		}
		echo '<p class="links"><a href="' . h(ME) . 'indexes=' . urlencode($TABLE) . '">' . lang(126) . "</a>\n";
	}
	
	if (fk_support($table_status)) {
		echo "<h3 id='foreign-keys'>" . lang(94) . "</h3>\n";
		$foreign_keys = foreign_keys($TABLE);
		if ($foreign_keys) {
			echo "<table cellspacing='0'>\n";
			echo "<thead><tr><th>" . lang(127) . "<td>" . lang(128) . "<td>" . lang(97) . "<td>" . lang(96) . "<td></thead>\n";
			foreach ($foreign_keys as $name => $foreign_key) {
				echo "<tr title='" . h($name) . "'>";
				echo "<th><i>" . implode("</i>, <i>", array_map('h', $foreign_key["source"])) . "</i>";
				echo "<td><a href='" . h($foreign_key["db"] != "" ? preg_replace('~db=[^&]*~', "db=" . urlencode($foreign_key["db"]), ME) : ($foreign_key["ns"] != "" ? preg_replace('~ns=[^&]*~', "ns=" . urlencode($foreign_key["ns"]), ME) : ME)) . "table=" . urlencode($foreign_key["table"]) . "'>"
					. ($foreign_key["db"] != "" ? "<b>" . h($foreign_key["db"]) . "</b>." : "") . ($foreign_key["ns"] != "" ? "<b>" . h($foreign_key["ns"]) . "</b>." : "") . h($foreign_key["table"])
					. "</a>"
				;
				echo "(<i>" . implode("</i>, <i>", array_map('h', $foreign_key["target"])) . "</i>)";
				echo "<td>" . h($foreign_key["on_delete"]) . "\n";
				echo "<td>" . h($foreign_key["on_update"]) . "\n";
				echo '<td><a href="' . h(ME . 'foreign=' . urlencode($TABLE) . '&name=' . urlencode($name)) . '">' . lang(129) . '</a>';
			}
			echo "</table>\n";
		}
		echo '<p class="links"><a href="' . h(ME) . 'foreign=' . urlencode($TABLE) . '">' . lang(130) . "</a>\n";
	}
}

if (support(is_view($table_status) ? "view_trigger" : "trigger")) {
	echo "<h3 id='triggers'>" . lang(131) . "</h3>\n";
	$triggers = triggers($TABLE);
	if ($triggers) {
		echo "<table cellspacing='0'>\n";
		foreach ($triggers as $key => $val) {
			echo "<tr valign='top'><td>" . h($val[0]) . "<td>" . h($val[1]) . "<th>" . h($key) . "<td><a href='" . h(ME . 'trigger=' . urlencode($TABLE) . '&name=' . urlencode($key)) . "'>" . lang(129) . "</a>\n";
		}
		echo "</table>\n";
	}
	echo '<p class="links"><a href="' . h(ME) . 'trigger=' . urlencode($TABLE) . '">' . lang(132) . "</a>\n";
}

} elseif (isset($_GET["schema"])) {
	
page_header(lang(59), "", array(), h(DB . ($_GET["ns"] ? ".$_GET[ns]" : "")));

$table_pos = array();
$table_pos_js = array();
$SCHEMA = ($_GET["schema"] ? $_GET["schema"] : $_COOKIE["adminer_schema-" . str_replace(".", "_", DB)]); // $_COOKIE["adminer_schema"] was used before 3.2.0 //! ':' in table name
preg_match_all('~([^:]+):([-0-9.]+)x([-0-9.]+)(_|$)~', $SCHEMA, $matches, PREG_SET_ORDER);
foreach ($matches as $i => $match) {
	$table_pos[$match[1]] = array($match[2], $match[3]);
	$table_pos_js[] = "\n\t'" . js_escape($match[1]) . "': [ $match[2], $match[3] ]";
}

$top = 0;
$base_left = -1;
$schema = array(); // table => array("fields" => array(name => field), "pos" => array(top, left), "references" => array(table => array(left => array(source, target))))
$referenced = array(); // target_table => array(table => array(left => target_column))
$lefts = array(); // float => bool
foreach (table_status('', true) as $table => $table_status) {
	if (is_view($table_status)) {
		continue;
	}
	$pos = 0;
	$schema[$table]["fields"] = array();
	foreach (fields($table) as $name => $field) {
		$pos += 1.25;
		$field["pos"] = $pos;
		$schema[$table]["fields"][$name] = $field;
	}
	$schema[$table]["pos"] = ($table_pos[$table] ? $table_pos[$table] : array($top, 0));
	foreach ($adminer->foreignKeys($table) as $val) {
		if (!$val["db"]) {
			$left = $base_left;
			if ($table_pos[$table][1] || $table_pos[$val["table"]][1]) {
				$left = min(floatval($table_pos[$table][1]), floatval($table_pos[$val["table"]][1])) - 1;
			} else {
				$base_left -= .1;
			}
			while ($lefts[(string) $left]) {
				// find free $left
				$left -= .0001;
			}
			$schema[$table]["references"][$val["table"]][(string) $left] = array($val["source"], $val["target"]);
			$referenced[$val["table"]][$table][(string) $left] = $val["target"];
			$lefts[(string) $left] = true;
		}
	}
	$top = max($top, $schema[$table]["pos"][0] + 2.5 + $pos);
}

?>
<div id="schema" style="height: <?php echo $top; ?>em;">
<script<?php echo nonce(); ?>>
qs('#schema').onselectstart = function () { return false; };
var tablePos = {<?php echo implode(",", $table_pos_js) . "\n"; ?>};
var em = qs('#schema').offsetHeight / <?php echo $top; ?>;
document.onmousemove = schemaMousemove;
document.onmouseup = partialArg(schemaMouseup, '<?php echo js_escape(DB); ?>');
</script>
<?php
foreach ($schema as $name => $table) {
	echo "<div class='table' style='top: " . $table["pos"][0] . "em; left: " . $table["pos"][1] . "em;'>";
	echo '<a href="' . h(ME) . 'table=' . urlencode($name) . '"><b>' . h($name) . "</b></a>";
	echo script("qsl('div').onmousedown = schemaMousedown;");
	
	foreach ($table["fields"] as $field) {
		$val = '<span' . type_class($field["type"]) . ' title="' . h($field["full_type"] . ($field["null"] ? " NULL" : '')) . '">' . h($field["field"]) . '</span>';
		echo "<br>" . ($field["primary"] ? "<i>$val</i>" : $val);
	}
	
	foreach ((array) $table["references"] as $target_name => $refs) {
		foreach ($refs as $left => $ref) {
			$left1 = $left - $table_pos[$name][1];
			$i = 0;
			foreach ($ref[0] as $source) {
				echo "\n<div class='references' title='" . h($target_name) . "' id='refs$left-" . ($i++) . "' style='left: $left1" . "em; top: " . $table["fields"][$source]["pos"] . "em; padding-top: .5em;'><div style='border-top: 1px solid Gray; width: " . (-$left1) . "em;'></div></div>";
			}
		}
	}
	
	foreach ((array) $referenced[$name] as $target_name => $refs) {
		foreach ($refs as $left => $columns) {
			$left1 = $left - $table_pos[$name][1];
			$i = 0;
			foreach ($columns as $target) {
				echo "\n<div class='references' title='" . h($target_name) . "' id='refd$left-" . ($i++) . "' style='left: $left1" . "em; top: " . $table["fields"][$target]["pos"] . "em; height: 1.25em; background: url(" . h(preg_replace("~\\?.*~", "", ME) . "?file=arrow.gif) no-repeat right center;&version=4.8.1") . "'><div style='height: .5em; border-bottom: 1px solid Gray; width: " . (-$left1) . "em;'></div></div>";
			}
		}
	}
	
	echo "\n</div>\n";
}

foreach ($schema as $name => $table) {
	foreach ((array) $table["references"] as $target_name => $refs) {
		foreach ($refs as $left => $ref) {
			$min_pos = $top;
			$max_pos = -10;
			foreach ($ref[0] as $key => $source) {
				$pos1 = $table["pos"][0] + $table["fields"][$source]["pos"];
				$pos2 = $schema[$target_name]["pos"][0] + $schema[$target_name]["fields"][$ref[1][$key]]["pos"];
				$min_pos = min($min_pos, $pos1, $pos2);
				$max_pos = max($max_pos, $pos1, $pos2);
			}
			echo "<div class='references' id='refl$left' style='left: $left" . "em; top: $min_pos" . "em; padding: .5em 0;'><div style='border-right: 1px solid Gray; margin-top: 1px; height: " . ($max_pos - $min_pos) . "em;'></div></div>\n";
		}
	}
}
?>
</div>
<p class="links"><a href="<?php echo h(ME . "schema=" . urlencode($SCHEMA)); ?>" id="schema-link"><?php echo lang(133); ?></a>
<?php
} elseif (isset($_GET["dump"])) {
	
$TABLE = $_GET["dump"];

if ($_POST && !$error) {
	$cookie = "";
	foreach (array("output", "format", "db_style", "routines", "events", "table_style", "auto_increment", "triggers", "data_style") as $key) {
		$cookie .= "&$key=" . urlencode($_POST[$key]);
	}
	cookie("adminer_export", substr($cookie, 1));
	$tables = array_flip((array) $_POST["tables"]) + array_flip((array) $_POST["data"]);
	$ext = dump_headers(
		(count($tables) == 1 ? key($tables) : DB),
		(DB == "" || count($tables) > 1));
	$is_sql = preg_match('~sql~', $_POST["format"]);

	if ($is_sql) {
		echo "-- Adminer $VERSION " . $drivers[DRIVER] . " " . str_replace("\n", " ", $connection->server_info) . " dump\n\n";
		if ($jush == "sql") {
			echo "SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
" . ($_POST["data_style"] ? "SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';
" : "") . "
";
			$connection->query("SET time_zone = '+00:00'");
			$connection->query("SET sql_mode = ''");
		}
	}

	$style = $_POST["db_style"];
	$databases = array(DB);
	if (DB == "") {
		$databases = $_POST["databases"];
		if (is_string($databases)) {
			$databases = explode("\n", rtrim(str_replace("\r", "", $databases), "\n"));
		}
	}

	foreach ((array) $databases as $db) {
		$adminer->dumpDatabase($db);
		if ($connection->select_db($db)) {
			if ($is_sql && preg_match('~CREATE~', $style) && ($create = $connection->result("SHOW CREATE DATABASE " . idf_escape($db), 1))) {
				set_utf8mb4($create);
				if ($style == "DROP+CREATE") {
					echo "DROP DATABASE IF EXISTS " . idf_escape($db) . ";\n";
				}
				echo "$create;\n";
			}
			if ($is_sql) {
				if ($style) {
					echo use_sql($db) . ";\n\n";
				}
				$out = "";

				if ($_POST["routines"]) {
					foreach (array("FUNCTION", "PROCEDURE") as $routine) {
						foreach (get_rows("SHOW $routine STATUS WHERE Db = " . q($db), null, "-- ") as $row) {
							$create = remove_definer($connection->result("SHOW CREATE $routine " . idf_escape($row["Name"]), 2));
							set_utf8mb4($create);
							$out .= ($style != 'DROP+CREATE' ? "DROP $routine IF EXISTS " . idf_escape($row["Name"]) . ";;\n" : "") . "$create;;\n\n";
						}
					}
				}

				if ($_POST["events"]) {
					foreach (get_rows("SHOW EVENTS", null, "-- ") as $row) {
						$create = remove_definer($connection->result("SHOW CREATE EVENT " . idf_escape($row["Name"]), 3));
						set_utf8mb4($create);
						$out .= ($style != 'DROP+CREATE' ? "DROP EVENT IF EXISTS " . idf_escape($row["Name"]) . ";;\n" : "") . "$create;;\n\n";
					}
				}

				if ($out) {
					echo "DELIMITER ;;\n\n$out" . "DELIMITER ;\n\n";
				}
			}

			if ($_POST["table_style"] || $_POST["data_style"]) {
				$views = array();
				foreach (table_status('', true) as $name => $table_status) {
					$table = (DB == "" || in_array($name, (array) $_POST["tables"]));
					$data = (DB == "" || in_array($name, (array) $_POST["data"]));
					if ($table || $data) {
						if ($ext == "tar") {
							$tmp_file = new TmpFile;
							ob_start(array($tmp_file, 'write'), 1e5);
						}

						$adminer->dumpTable($name, ($table ? $_POST["table_style"] : ""), (is_view($table_status) ? 2 : 0));
						if (is_view($table_status)) {
							$views[] = $name;
						} elseif ($data) {
							$fields = fields($name);
							$adminer->dumpData($name, $_POST["data_style"], "SELECT *" . convert_fields($fields, $fields) . " FROM " . table($name));
						}
						if ($is_sql && $_POST["triggers"] && $table && ($triggers = trigger_sql($name))) {
							echo "\nDELIMITER ;;\n$triggers\nDELIMITER ;\n";
						}

						if ($ext == "tar") {
							ob_end_flush();
							tar_file((DB != "" ? "" : "$db/") . "$name.csv", $tmp_file);
						} elseif ($is_sql) {
							echo "\n";
						}
					}
				}

				// add FKs after creating tables (except in MySQL which uses SET FOREIGN_KEY_CHECKS=0)
				if (function_exists('foreign_keys_sql')) {
					foreach (table_status('', true) as $name => $table_status) {
						$table = (DB == "" || in_array($name, (array) $_POST["tables"]));
						if ($table && !is_view($table_status)) {
							echo foreign_keys_sql($name);
						}
					}
				}

				foreach ($views as $view) {
					$adminer->dumpTable($view, $_POST["table_style"], 1);
				}

				if ($ext == "tar") {
					echo pack("x512");
				}
			}
		}
	}

	if ($is_sql) {
		echo "-- " . $connection->result("SELECT NOW()") . "\n";
	}
	exit;
}

page_header(lang(62), $error, ($_GET["export"] != "" ? array("table" => $_GET["export"]) : array()), h(DB));
?>

<form action="" method="post">
<table cellspacing="0" class="layout">
<?php
$db_style = array('', 'USE', 'DROP+CREATE', 'CREATE');
$table_style = array('', 'DROP+CREATE', 'CREATE');
$data_style = array('', 'TRUNCATE+INSERT', 'INSERT');
if ($jush == "sql") { //! use insertUpdate() in all drivers
	$data_style[] = 'INSERT+UPDATE';
}
parse_str($_COOKIE["adminer_export"], $row);
if (!$row) {
	$row = array("output" => "text", "format" => "sql", "db_style" => (DB != "" ? "" : "CREATE"), "table_style" => "DROP+CREATE", "data_style" => "INSERT");
}
if (!isset($row["events"])) { // backwards compatibility
	$row["routines"] = $row["events"] = ($_GET["dump"] == "");
	$row["triggers"] = $row["table_style"];
}

echo "<tr><th>" . lang(134) . "<td>" . html_select("output", $adminer->dumpOutput(), $row["output"], 0) . "\n"; // 0 - radio

echo "<tr><th>" . lang(135) . "<td>" . html_select("format", $adminer->dumpFormat(), $row["format"], 0) . "\n"; // 0 - radio

echo ($jush == "sqlite" ? "" : "<tr><th>" . lang(26) . "<td>" . html_select('db_style', $db_style, $row["db_style"])
	. (support("routine") ? checkbox("routines", 1, $row["routines"], lang(136)) : "")
	. (support("event") ? checkbox("events", 1, $row["events"], lang(137)) : "")
);

echo "<tr><th>" . lang(117) . "<td>" . html_select('table_style', $table_style, $row["table_style"])
	. checkbox("auto_increment", 1, $row["auto_increment"], lang(40))
	. (support("trigger") ? checkbox("triggers", 1, $row["triggers"], lang(131)) : "")
;

echo "<tr><th>" . lang(138) . "<td>" . html_select('data_style', $data_style, $row["data_style"]);
?>
</table>
<p><input type="submit" value="<?php echo lang(62); ?>">
<input type="hidden" name="token" value="<?php echo $token; ?>">

<table cellspacing="0">
<?php
echo script("qsl('table').onclick = dumpClick;");
$prefixes = array();
if (DB != "") {
	$checked = ($TABLE != "" ? "" : " checked");
	echo "<thead><tr>";
	echo "<th style='text-align: left;'><label class='block'><input type='checkbox' id='check-tables'$checked>" . lang(117) . "</label>" . script("qs('#check-tables').onclick = partial(formCheck, /^tables\\[/);", "");
	echo "<th style='text-align: right;'><label class='block'>" . lang(138) . "<input type='checkbox' id='check-data'$checked></label>" . script("qs('#check-data').onclick = partial(formCheck, /^data\\[/);", "");
	echo "</thead>\n";

	$views = "";
	$tables_list = tables_list();
	foreach ($tables_list as $name => $type) {
		$prefix = preg_replace('~_.*~', '', $name);
		$checked = ($TABLE == "" || $TABLE == (substr($TABLE, -1) == "%" ? "$prefix%" : $name)); //! % may be part of table name
		$print = "<tr><td>" . checkbox("tables[]", $name, $checked, $name, "", "block");
		if ($type !== null && !preg_match('~table~i', $type)) {
			$views .= "$print\n";
		} else {
			echo "$print<td align='right'><label class='block'><span id='Rows-" . h($name) . "'></span>" . checkbox("data[]", $name, $checked) . "</label>\n";
		}
		$prefixes[$prefix]++;
	}
	echo $views;

	if ($tables_list) {
		echo script("ajaxSetHtml('" . js_escape(ME) . "script=db');");
	}

} else {
	echo "<thead><tr><th style='text-align: left;'>";
	echo "<label class='block'><input type='checkbox' id='check-databases'" . ($TABLE == "" ? " checked" : "") . ">" . lang(26) . "</label>";
	echo script("qs('#check-databases').onclick = partial(formCheck, /^databases\\[/);", "");
	echo "</thead>\n";
	$databases = $adminer->databases();
	if ($databases) {
		foreach ($databases as $db) {
			if (!information_schema($db)) {
				$prefix = preg_replace('~_.*~', '', $db);
				echo "<tr><td>" . checkbox("databases[]", $db, $TABLE == "" || $TABLE == "$prefix%", $db, "", "block") . "\n";
				$prefixes[$prefix]++;
			}
		}
	} else {
		echo "<tr><td><textarea name='databases' rows='10' cols='20'></textarea>";
	}
}
?>
</table>
</form>
<?php
$first = true;
foreach ($prefixes as $key => $val) {
	if ($key != "" && $val > 1) {
		echo ($first ? "<p>" : " ") . "<a href='" . h(ME) . "dump=" . urlencode("$key%") . "'>" . h($key) . "</a>";
		$first = false;
	}
}

} elseif (isset($_GET["privileges"])) {
	
page_header(lang(60));

echo '<p class="links"><a href="' . h(ME) . 'user=">' . lang(139) . "</a>";

$result = $connection->query("SELECT User, Host FROM mysql." . (DB == "" ? "user" : "db WHERE " . q(DB) . " LIKE Db") . " ORDER BY Host, User");
$grant = $result;
if (!$result) {
	// list logged user, information_schema.USER_PRIVILEGES lists just the current user too
	$result = $connection->query("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', 1) AS User, SUBSTRING_INDEX(CURRENT_USER, '@', -1) AS Host");
}

echo "<form action=''><p>\n";
hidden_fields_get();
echo "<input type='hidden' name='db' value='" . h(DB) . "'>\n";
echo ($grant ? "" : "<input type='hidden' name='grant' value=''>\n");
echo "<table cellspacing='0'>\n";
echo "<thead><tr><th>" . lang(24) . "<th>" . lang(23) . "<th></thead>\n";

while ($row = $result->fetch_assoc()) {
	echo '<tr' . odd() . '><td>' . h($row["User"]) . "<td>" . h($row["Host"]) . '<td><a href="' . h(ME . 'user=' . urlencode($row["User"]) . '&host=' . urlencode($row["Host"])) . '">' . lang(10) . "</a>\n";
}

if (!$grant || DB != "") {
	echo "<tr" . odd() . "><td><input name='user' autocapitalize='off'><td><input name='host' value='localhost' autocapitalize='off'><td><input type='submit' value='" . lang(10) . "'>\n";
}

echo "</table>\n";
echo "</form>\n";

} elseif (isset($_GET["sql"])) {
	
if (!$error && $_POST["export"]) {
	dump_headers("sql");
	$adminer->dumpTable("", "");
	$adminer->dumpData("", "table", $_POST["query"]);
	exit;
}

restart_session();
$history_all = &get_session("queries");
$history = &$history_all[DB];
if (!$error && $_POST["clear"]) {
	$history = array();
	redirect(remove_from_uri("history"));
}

page_header((isset($_GET["import"]) ? lang(61) : lang(53)), $error);

if (!$error && $_POST) {
	$fp = false;
	if (!isset($_GET["import"])) {
		$query = $_POST["query"];
	} elseif ($_POST["webfile"]) {
		$sql_file_path = $adminer->importServerPath();
		$fp = @fopen((file_exists($sql_file_path)
			? $sql_file_path
			: "compress.zlib://$sql_file_path.gz"
		), "rb");
		$query = ($fp ? fread($fp, 1e6) : false);
	} else {
		$query = get_file("sql_file", true);
	}

	if (is_string($query)) { // get_file() returns error as number, fread() as false
		if (function_exists('memory_get_usage')) {
			@ini_set("memory_limit", max(ini_bytes("memory_limit"), 2 * strlen($query) + memory_get_usage() + 8e6)); // @ - may be disabled, 2 - substr and trim, 8e6 - other variables
		}

		if ($query != "" && strlen($query) < 1e6) { // don't add big queries
			$q = $query . (preg_match("~;[ \t\r\n]*\$~", $query) ? "" : ";"); //! doesn't work with DELIMITER |
			if (!$history || reset(end($history)) != $q) { // no repeated queries
				restart_session();
				$history[] = array($q, time()); //! add elapsed time
				set_session("queries", $history_all); // required because reference is unlinked by stop_session()
				stop_session();
			}
		}

		$space = "(?:\\s|/\\*[\s\S]*?\\*/|(?:#|-- )[^\n]*\n?|--\r?\n)";
		$delimiter = ";";
		$offset = 0;
		$empty = true;
		$connection2 = connect(); // connection for exploring indexes and EXPLAIN (to not replace FOUND_ROWS()) //! PDO - silent error
		if (is_object($connection2) && DB != "") {
			$connection2->select_db(DB);
			if ($_GET["ns"] != "") {
				set_schema($_GET["ns"], $connection2);
			}
		}
		$commands = 0;
		$errors = array();
		$parse = '[\'"' . ($jush == "sql" ? '`#' : ($jush == "sqlite" ? '`[' : ($jush == "mssql" ? '[' : ''))) . ']|/\*|-- |$' . ($jush == "pgsql" ? '|\$[^$]*\$' : '');
		$total_start = microtime(true);
		parse_str($_COOKIE["adminer_export"], $adminer_export);
		$dump_format = $adminer->dumpFormat();
		unset($dump_format["sql"]);

		while ($query != "") {
			if (!$offset && preg_match("~^$space*+DELIMITER\\s+(\\S+)~i", $query, $match)) {
				$delimiter = $match[1];
				$query = substr($query, strlen($match[0]));
			} else {
				preg_match('(' . preg_quote($delimiter) . "\\s*|$parse)", $query, $match, PREG_OFFSET_CAPTURE, $offset); // should always match
				list($found, $pos) = $match[0];
				if (!$found && $fp && !feof($fp)) {
					$query .= fread($fp, 1e5);
				} else {
					if (!$found && rtrim($query) == "") {
						break;
					}
					$offset = $pos + strlen($found);

					if ($found && rtrim($found) != $delimiter) { // find matching quote or comment end
						while (preg_match('(' . ($found == '/*' ? '\*/' : ($found == '[' ? ']' : (preg_match('~^-- |^#~', $found) ? "\n" : preg_quote($found) . "|\\\\."))) . '|$)s', $query, $match, PREG_OFFSET_CAPTURE, $offset)) { //! respect sql_mode NO_BACKSLASH_ESCAPES
							$s = $match[0][0];
							if (!$s && $fp && !feof($fp)) {
								$query .= fread($fp, 1e5);
							} else {
								$offset = $match[0][1] + strlen($s);
								if ($s[0] != "\\") {
									break;
								}
							}
						}

					} else { // end of a query
						$empty = false;
						$q = substr($query, 0, $pos);
						$commands++;
						$print = "<pre id='sql-$commands'><code class='jush-$jush'>" . $adminer->sqlCommandQuery($q) . "</code></pre>\n";
						if ($jush == "sqlite" && preg_match("~^$space*+ATTACH\\b~i", $q, $match)) {
							// PHP doesn't support setting SQLITE_LIMIT_ATTACHED
							echo $print;
							echo "<p class='error'>" . lang(140) . "\n";
							$errors[] = " <a href='#sql-$commands'>$commands</a>";
							if ($_POST["error_stops"]) {
								break;
							}
						} else {
							if (!$_POST["only_errors"]) {
								echo $print;
								ob_flush();
								flush(); // can take a long time - show the running query
							}
							$start = microtime(true);
							//! don't allow changing of character_set_results, convert encoding of displayed query
							if ($connection->multi_query($q) && is_object($connection2) && preg_match("~^$space*+USE\\b~i", $q)) {
								$connection2->query($q);
							}

							do {
								$result = $connection->store_result();

								if ($connection->error) {
									echo ($_POST["only_errors"] ? $print : "");
									echo "<p class='error'>" . lang(141) . ($connection->errno ? " ($connection->errno)" : "") . ": " . error() . "\n";
									$errors[] = " <a href='#sql-$commands'>$commands</a>";
									if ($_POST["error_stops"]) {
										break 2;
									}

								} else {
									$time = " <span class='time'>(" . format_time($start) . ")</span>"
										. (strlen($q) < 1000 ? " <a href='" . h(ME) . "sql=" . urlencode(trim($q)) . "'>" . lang(10) . "</a>" : "") // 1000 - maximum length of encoded URL in IE is 2083 characters
									;
									$affected = $connection->affected_rows; // getting warnigns overwrites this
									$warnings = ($_POST["only_errors"] ? "" : $driver->warnings());
									$warnings_id = "warnings-$commands";
									if ($warnings) {
										$time .= ", <a href='#$warnings_id'>" . lang(35) . "</a>" . script("qsl('a').onclick = partial(toggle, '$warnings_id');", "");
									}
									$explain = null;
									$explain_id = "explain-$commands";
									if (is_object($result)) {
										$limit = $_POST["limit"];
										$orgtables = select($result, $connection2, array(), $limit);
										if (!$_POST["only_errors"]) {
											echo "<form action='' method='post'>\n";
											$num_rows = $result->num_rows;
											echo "<p>" . ($num_rows ? ($limit && $num_rows > $limit ? lang(142, $limit) : "") . lang(143, $num_rows) : "");
											echo $time;
											if ($connection2 && preg_match("~^($space|\\()*+SELECT\\b~i", $q) && ($explain = explain($connection2, $q))) {
												echo ", <a href='#$explain_id'>Explain</a>" . script("qsl('a').onclick = partial(toggle, '$explain_id');", "");
											}
											$id = "export-$commands";
											echo ", <a href='#$id'>" . lang(62) . "</a>" . script("qsl('a').onclick = partial(toggle, '$id');", "") . "<span id='$id' class='hidden'>: "
												. html_select("output", $adminer->dumpOutput(), $adminer_export["output"]) . " "
												. html_select("format", $dump_format, $adminer_export["format"])
												. "<input type='hidden' name='query' value='" . h($q) . "'>"
												. " <input type='submit' name='export' value='" . lang(62) . "'><input type='hidden' name='token' value='$token'></span>\n"
												. "</form>\n"
											;
										}

									} else {
										if (preg_match("~^$space*+(CREATE|DROP|ALTER)$space++(DATABASE|SCHEMA)\\b~i", $q)) {
											restart_session();
											set_session("dbs", null); // clear cache
											stop_session();
										}
										if (!$_POST["only_errors"]) {
											echo "<p class='message' title='" . h($connection->info) . "'>" . lang(144, $affected) . "$time\n";
										}
									}
									echo ($warnings ? "<div id='$warnings_id' class='hidden'>\n$warnings</div>\n" : "");
									if ($explain) {
										echo "<div id='$explain_id' class='hidden'>\n";
										select($explain, $connection2, $orgtables);
										echo "</div>\n";
									}
								}

								$start = microtime(true);
							} while ($connection->next_result());
						}

						$query = substr($query, $offset);
						$offset = 0;
					}

				}
			}
		}

		if ($empty) {
			echo "<p class='message'>" . lang(145) . "\n";
		} elseif ($_POST["only_errors"]) {
			echo "<p class='message'>" . lang(146, $commands - count($errors));
			echo " <span class='time'>(" . format_time($total_start) . ")</span>\n";
		} elseif ($errors && $commands > 1) {
			echo "<p class='error'>" . lang(141) . ": " . implode("", $errors) . "\n";
		}
		//! MS SQL - SET SHOWPLAN_ALL OFF

	} else {
		echo "<p class='error'>" . upload_error($query) . "\n";
	}
}
?>

<form action="" method="post" enctype="multipart/form-data" id="form">
<?php
$execute = "<input type='submit' value='" . lang(147) . "' title='Ctrl+Enter'>";
if (!isset($_GET["import"])) {
	$q = $_GET["sql"]; // overwrite $q from if ($_POST) to save memory
	if ($_POST) {
		$q = $_POST["query"];
	} elseif ($_GET["history"] == "all") {
		$q = $history;
	} elseif ($_GET["history"] != "") {
		$q = $history[$_GET["history"]][0];
	}
	echo "<p>";
	textarea("query", $q, 20);
	echo script(($_POST ? "" : "qs('textarea').focus();\n") . "qs('#form').onsubmit = partial(sqlSubmit, qs('#form'), '" . js_escape(remove_from_uri("sql|limit|error_stops|only_errors|history")) . "');");
	echo "<p>$execute\n";
	echo lang(148) . ": <input type='number' name='limit' class='size' value='" . h($_POST ? $_POST["limit"] : $_GET["limit"]) . "'>\n";
	
} else {
	echo "<fieldset><legend>" . lang(149) . "</legend><div>";
	$gz = (extension_loaded("zlib") ? "[.gz]" : "");
	echo (ini_bool("file_uploads")
		? "SQL$gz (&lt; " . ini_get("upload_max_filesize") . "B): <input type='file' name='sql_file[]' multiple>\n$execute" // ignore post_max_size because it is for all form fields together and bytes computing would be necessary
		: lang(150)
	);
	echo "</div></fieldset>\n";
	$importServerPath = $adminer->importServerPath();
	if ($importServerPath) {
		echo "<fieldset><legend>" . lang(151) . "</legend><div>";
		echo lang(152, "<code>" . h($importServerPath) . "$gz</code>");
		echo ' <input type="submit" name="webfile" value="' . lang(153) . '">';
		echo "</div></fieldset>\n";
	}
	echo "<p>";
}

echo checkbox("error_stops", 1, ($_POST ? $_POST["error_stops"] : isset($_GET["import"]) || $_GET["error_stops"]), lang(154)) . "\n";
echo checkbox("only_errors", 1, ($_POST ? $_POST["only_errors"] : isset($_GET["import"]) || $_GET["only_errors"]), lang(155)) . "\n";
echo "<input type='hidden' name='token' value='$token'>\n";

if (!isset($_GET["import"]) && $history) {
	print_fieldset("history", lang(156), $_GET["history"] != "");
	for ($val = end($history); $val; $val = prev($history)) { // not array_reverse() to save memory
		$key = key($history);
		list($q, $time, $elapsed) = $val;
		echo '<a href="' . h(ME . "sql=&history=$key") . '">' . lang(10) . "</a>"
			. " <span class='time' title='" . @date('Y-m-d', $time) . "'>" . @date("H:i:s", $time) . "</span>" // @ - time zone may be not set
			. " <code class='jush-$jush'>" . shorten_utf8(ltrim(str_replace("\n", " ", str_replace("\r", "", preg_replace('~^(#|-- ).*~m', '', $q)))), 80, "</code>")
			. ($elapsed ? " <span class='time'>($elapsed)</span>" : "")
			. "<br>\n"
		;
	}
	echo "<input type='submit' name='clear' value='" . lang(157) . "'>\n";
	echo "<a href='" . h(ME . "sql=&history=all") . "'>" . lang(158) . "</a>\n";
	echo "</div></fieldset>\n";
}
?>
</form>
<?php
} elseif (isset($_GET["edit"])) {
	
$TABLE = $_GET["edit"];
$fields = fields($TABLE);
$where = (isset($_GET["select"]) ? ($_POST["check"] && count($_POST["check"]) == 1 ? where_check($_POST["check"][0], $fields) : "") : where($_GET, $fields));
$update = (isset($_GET["select"]) ? $_POST["edit"] : $where);
foreach ($fields as $name => $field) {
	if (!isset($field["privileges"][$update ? "update" : "insert"]) || $adminer->fieldName($field) == "" || $field["generated"]) {
		unset($fields[$name]);
	}
}

if ($_POST && !$error && !isset($_GET["select"])) {
	$location = $_POST["referer"];
	if ($_POST["insert"]) { // continue edit or insert
		$location = ($update ? null : $_SERVER["REQUEST_URI"]);
	} elseif (!preg_match('~^.+&select=.+$~', $location)) {
		$location = ME . "select=" . urlencode($TABLE);
	}

	$indexes = indexes($TABLE);
	$unique_array = unique_array($_GET["where"], $indexes);
	$query_where = "\nWHERE $where";

	if (isset($_POST["delete"])) {
		queries_redirect(
			$location,
			lang(159),
			$driver->delete($TABLE, $query_where, !$unique_array)
		);

	} else {
		$set = array();
		foreach ($fields as $name => $field) {
			$val = process_input($field);
			if ($val !== false && $val !== null) {
				$set[idf_escape($name)] = $val;
			}
		}

		if ($update) {
			if (!$set) {
				redirect($location);
			}
			queries_redirect(
				$location,
				lang(160),
				$driver->update($TABLE, $set, $query_where, !$unique_array)
			);
			if (is_ajax()) {
				page_headers();
				page_messages($error);
				exit;
			}
		} else {
			$result = $driver->insert($TABLE, $set);
			$last_id = ($result ? last_id() : 0);
			queries_redirect($location, lang(161, ($last_id ? " $last_id" : "")), $result); //! link
		}
	}
}

$row = null;
if ($_POST["save"]) {
	$row = (array) $_POST["fields"];
} elseif ($where) {
	$select = array();
	foreach ($fields as $name => $field) {
		if (isset($field["privileges"]["select"])) {
			$as = convert_field($field);
			if ($_POST["clone"] && $field["auto_increment"]) {
				$as = "''";
			}
			if ($jush == "sql" && preg_match("~enum|set~", $field["type"])) {
				$as = "1*" . idf_escape($name);
			}
			$select[] = ($as ? "$as AS " : "") . idf_escape($name);
		}
	}
	$row = array();
	if (!support("table")) {
		$select = array("*");
	}
	if ($select) {
		$result = $driver->select($TABLE, $select, array($where), $select, array(), (isset($_GET["select"]) ? 2 : 1));
		if (!$result) {
			$error = error();
		} else {
			$row = $result->fetch_assoc();
			if (!$row) { // MySQLi returns null
				$row = false;
			}
		}
		if (isset($_GET["select"]) && (!$row || $result->fetch_assoc())) { // $result->num_rows != 1 isn't available in all drivers
			$row = null;
		}
	}
}

if (!support("table") && !$fields) {
	if (!$where) { // insert
		$result = $driver->select($TABLE, array("*"), $where, array("*"));
		$row = ($result ? $result->fetch_assoc() : false);
		if (!$row) {
			$row = array($driver->primary => "");
		}
	}
	if ($row) {
		foreach ($row as $key => $val) {
			if (!$where) {
				$row[$key] = null;
			}
			$fields[$key] = array("field" => $key, "null" => ($key != $driver->primary), "auto_increment" => ($key == $driver->primary));
		}
	}
}

edit_form($TABLE, $fields, $row, $update);

} elseif (isset($_GET["create"])) {
	
$TABLE = $_GET["create"];
$partition_by = array();
foreach (array('HASH', 'LINEAR HASH', 'KEY', 'LINEAR KEY', 'RANGE', 'LIST') as $key) {
	$partition_by[$key] = $key;
}

$referencable_primary = referencable_primary($TABLE);
$foreign_keys = array();
foreach ($referencable_primary as $table_name => $field) {
	$foreign_keys[str_replace("`", "``", $table_name) . "`" . str_replace("`", "``", $field["field"])] = $table_name; // not idf_escape() - used in JS
}

$orig_fields = array();
$table_status = array();
if ($TABLE != "") {
	$orig_fields = fields($TABLE);
	$table_status = table_status($TABLE);
	if (!$table_status) {
		$error = lang(9);
	}
}

$row = $_POST;
$row["fields"] = (array) $row["fields"];
if ($row["auto_increment_col"]) {
	$row["fields"][$row["auto_increment_col"]]["auto_increment"] = true;
}

if ($_POST) {
	set_adminer_settings(array("comments" => $_POST["comments"], "defaults" => $_POST["defaults"]));
}

if ($_POST && !process_fields($row["fields"]) && !$error) {
	if ($_POST["drop"]) {
		queries_redirect(substr(ME, 0, -1), lang(162), drop_tables(array($TABLE)));
	} else {
		$fields = array();
		$all_fields = array();
		$use_all_fields = false;
		$foreign = array();
		$orig_field = reset($orig_fields);
		$after = " FIRST";

		foreach ($row["fields"] as $key => $field) {
			$foreign_key = $foreign_keys[$field["type"]];
			$type_field = ($foreign_key !== null ? $referencable_primary[$foreign_key] : $field); //! can collide with user defined type
			if ($field["field"] != "") {
				if (!$field["has_default"]) {
					$field["default"] = null;
				}
				if ($key == $row["auto_increment_col"]) {
					$field["auto_increment"] = true;
				}
				$process_field = process_field($field, $type_field);
				$all_fields[] = array($field["orig"], $process_field, $after);
				if (!$orig_field || $process_field != process_field($orig_field, $orig_field)) {
					$fields[] = array($field["orig"], $process_field, $after);
					if ($field["orig"] != "" || $after) {
						$use_all_fields = true;
					}
				}
				if ($foreign_key !== null) {
					$foreign[idf_escape($field["field"])] = ($TABLE != "" && $jush != "sqlite" ? "ADD" : " ") . format_foreign_key(array(
						'table' => $foreign_keys[$field["type"]],
						'source' => array($field["field"]),
						'target' => array($type_field["field"]),
						'on_delete' => $field["on_delete"],
					));
				}
				$after = " AFTER " . idf_escape($field["field"]);
			} elseif ($field["orig"] != "") {
				$use_all_fields = true;
				$fields[] = array($field["orig"]);
			}
			if ($field["orig"] != "") {
				$orig_field = next($orig_fields);
				if (!$orig_field) {
					$after = "";
				}
			}
		}

		$partitioning = "";
		if ($partition_by[$row["partition_by"]]) {
			$partitions = array();
			if ($row["partition_by"] == 'RANGE' || $row["partition_by"] == 'LIST') {
				foreach (array_filter($row["partition_names"]) as $key => $val) {
					$value = $row["partition_values"][$key];
					$partitions[] = "\n  PARTITION " . idf_escape($val) . " VALUES " . ($row["partition_by"] == 'RANGE' ? "LESS THAN" : "IN") . ($value != "" ? " ($value)" : " MAXVALUE"); //! SQL injection
				}
			}
			$partitioning .= "\nPARTITION BY $row[partition_by]($row[partition])" . ($partitions // $row["partition"] can be expression, not only column
				? " (" . implode(",", $partitions) . "\n)"
				: ($row["partitions"] ? " PARTITIONS " . (+$row["partitions"]) : "")
			);
		} elseif (support("partitioning") && preg_match("~partitioned~", $table_status["Create_options"])) {
			$partitioning .= "\nREMOVE PARTITIONING";
		}

		$message = lang(163);
		if ($TABLE == "") {
			cookie("adminer_engine", $row["Engine"]);
			$message = lang(164);
		}
		$name = trim($row["name"]);

		queries_redirect(ME . (support("table") ? "table=" : "select=") . urlencode($name), $message, alter_table(
			$TABLE,
			$name,
			($jush == "sqlite" && ($use_all_fields || $foreign) ? $all_fields : $fields),
			$foreign,
			($row["Comment"] != $table_status["Comment"] ? $row["Comment"] : null),
			($row["Engine"] && $row["Engine"] != $table_status["Engine"] ? $row["Engine"] : ""),
			($row["Collation"] && $row["Collation"] != $table_status["Collation"] ? $row["Collation"] : ""),
			($row["Auto_increment"] != "" ? number($row["Auto_increment"]) : ""),
			$partitioning
		));
	}
}

page_header(($TABLE != "" ? lang(33) : lang(63)), $error, array("table" => $TABLE), h($TABLE));

if (!$_POST) {
	$row = array(
		"Engine" => $_COOKIE["adminer_engine"],
		"fields" => array(array("field" => "", "type" => (isset($types["int"]) ? "int" : (isset($types["integer"]) ? "integer" : "")), "on_update" => "")),
		"partition_names" => array(""),
	);

	if ($TABLE != "") {
		$row = $table_status;
		$row["name"] = $TABLE;
		$row["fields"] = array();
		if (!$_GET["auto_increment"]) { // don't prefill by original Auto_increment for the sake of performance and not reusing deleted ids
			$row["Auto_increment"] = "";
		}
		foreach ($orig_fields as $field) {
			$field["has_default"] = isset($field["default"]);
			$row["fields"][] = $field;
		}

		if (support("partitioning")) {
			$from = "FROM information_schema.PARTITIONS WHERE TABLE_SCHEMA = " . q(DB) . " AND TABLE_NAME = " . q($TABLE);
			$result = $connection->query("SELECT PARTITION_METHOD, PARTITION_ORDINAL_POSITION, PARTITION_EXPRESSION $from ORDER BY PARTITION_ORDINAL_POSITION DESC LIMIT 1");
			list($row["partition_by"], $row["partitions"], $row["partition"]) = $result->fetch_row();
			$partitions = get_key_vals("SELECT PARTITION_NAME, PARTITION_DESCRIPTION $from AND PARTITION_NAME != '' ORDER BY PARTITION_ORDINAL_POSITION");
			$partitions[""] = "";
			$row["partition_names"] = array_keys($partitions);
			$row["partition_values"] = array_values($partitions);
		}
	}
}

$collations = collations();
$engines = engines();
// case of engine may differ
foreach ($engines as $engine) {
	if (!strcasecmp($engine, $row["Engine"])) {
		$row["Engine"] = $engine;
		break;
	}
}
?>

<form action="" method="post" id="form">
<p>
<?php if (support("columns") || $TABLE == "") {  echo lang(165); ?>: <input name="name" data-maxlength="64" value="<?php echo h($row["name"]); ?>" autocapitalize="off">
<?php if ($TABLE == "" && !$_POST) { echo script("focus(qs('#form')['name']);"); }  echo ($engines ? "<select name='Engine'>" . optionlist(array("" => "(" . lang(166) . ")") + $engines, $row["Engine"]) . "</select>" . on_help("getTarget(event).value", 1) . script("qsl('select').onchange = helpClose;") : ""); ?>
 <?php echo ($collations && !preg_match("~sqlite|mssql~", $jush) ? html_select("Collation", array("" => "(" . lang(95) . ")") + $collations, $row["Collation"]) : ""); ?>
 <input type="submit" value="<?php echo lang(14); ?>">
<?php } ?>

<?php if (support("columns")) { ?>
<div class="scrollable">
<table cellspacing="0" id="edit-fields" class="nowrap">
<?php
edit_fields($row["fields"], $collations, "TABLE", $foreign_keys);
?>
</table>
<?php echo script("editFields();"); ?>
</div>
<p>
<?php echo lang(40); ?>: <input type="number" name="Auto_increment" size="6" value="<?php echo h($row["Auto_increment"]); ?>">
<?php echo checkbox("defaults", 1, ($_POST ? $_POST["defaults"] : adminer_setting("defaults")), lang(167), "columnShow(this.checked, 5)", "jsonly");  echo (support("comment")
	? checkbox("comments", 1, ($_POST ? $_POST["comments"] : adminer_setting("comments")), lang(39), "editingCommentsClick(this, true);", "jsonly")
		. ' <input name="Comment" value="' . h($row["Comment"]) . '" data-maxlength="' . (min_version(5.5) ? 2048 : 60) . '">'
	: '')
; ?>
<p>
<input type="submit" value="<?php echo lang(14); ?>">
<?php } ?>

<?php if ($TABLE != "") { ?><input type="submit" name="drop" value="<?php echo lang(121); ?>"><?php echo confirm(lang(168, $TABLE));  } 
if (support("partitioning")) {
	$partition_table = preg_match('~RANGE|LIST~', $row["partition_by"]);
	print_fieldset("partition", lang(169), $row["partition_by"]);
	?>
<p>
<?php echo "<select name='partition_by'>" . optionlist(array("" => "") + $partition_by, $row["partition_by"]) . "</select>" . on_help("getTarget(event).value.replace(/./, 'PARTITION BY \$&')", 1) . script("qsl('select').onchange = partitionByChange;"); ?>
(<input name="partition" value="<?php echo h($row["partition"]); ?>">)
<?php echo lang(170); ?>: <input type="number" name="partitions" class="size<?php echo ($partition_table || !$row["partition_by"] ? " hidden" : ""); ?>" value="<?php echo h($row["partitions"]); ?>">
<table cellspacing="0" id="partition-table"<?php echo ($partition_table ? "" : " class='hidden'"); ?>>
<thead><tr><th><?php echo lang(171); ?><th><?php echo lang(172); ?></thead>
<?php
foreach ($row["partition_names"] as $key => $val) {
	echo '<tr>';
	echo '<td><input name="partition_names[]" value="' . h($val) . '" autocapitalize="off">';
	echo ($key == count($row["partition_names"]) - 1 ? script("qsl('input').oninput = partitionNameChange;") : '');
	echo '<td><input name="partition_values[]" value="' . h($row["partition_values"][$key]) . '">';
}
?>
</table>
</div></fieldset>
<?php
}
?>
<input type="hidden" name="token" value="<?php echo $token; ?>">
</form>
<?php
} elseif (isset($_GET["indexes"])) {
	
$TABLE = $_GET["indexes"];
$index_types = array("PRIMARY", "UNIQUE", "INDEX");
$table_status = table_status($TABLE, true);
if (preg_match('~MyISAM|M?aria' . (min_version(5.6, '10.0.5') ? '|InnoDB' : '') . '~i', $table_status["Engine"])) {
	$index_types[] = "FULLTEXT";
}
if (preg_match('~MyISAM|M?aria' . (min_version(5.7, '10.2.2') ? '|InnoDB' : '') . '~i', $table_status["Engine"])) {
	$index_types[] = "SPATIAL";
}
$indexes = indexes($TABLE);
$primary = array();
if ($jush == "mongo") { // doesn't support primary key
	$primary = $indexes["_id_"];
	unset($index_types[0]);
	unset($indexes["_id_"]);
}
$row = $_POST;

if ($_POST && !$error && !$_POST["add"] && !$_POST["drop_col"]) {
	$alter = array();
	foreach ($row["indexes"] as $index) {
		$name = $index["name"];
		if (in_array($index["type"], $index_types)) {
			$columns = array();
			$lengths = array();
			$descs = array();
			$set = array();
			ksort($index["columns"]);
			foreach ($index["columns"] as $key => $column) {
				if ($column != "") {
					$length = $index["lengths"][$key];
					$desc = $index["descs"][$key];
					$set[] = idf_escape($column) . ($length ? "(" . (+$length) . ")" : "") . ($desc ? " DESC" : "");
					$columns[] = $column;
					$lengths[] = ($length ? $length : null);
					$descs[] = $desc;
				}
			}

			if ($columns) {
				$existing = $indexes[$name];
				if ($existing) {
					ksort($existing["columns"]);
					ksort($existing["lengths"]);
					ksort($existing["descs"]);
					if ($index["type"] == $existing["type"]
						&& array_values($existing["columns"]) === $columns
						&& (!$existing["lengths"] || array_values($existing["lengths"]) === $lengths)
						&& array_values($existing["descs"]) === $descs
					) {
						// skip existing index
						unset($indexes[$name]);
						continue;
					}
				}
				$alter[] = array($index["type"], $name, $set);
			}
		}
	}

	// drop removed indexes
	foreach ($indexes as $name => $existing) {
		$alter[] = array($existing["type"], $name, "DROP");
	}
	if (!$alter) {
		redirect(ME . "table=" . urlencode($TABLE));
	}
	queries_redirect(ME . "table=" . urlencode($TABLE), lang(173), alter_indexes($TABLE, $alter));
}

page_header(lang(125), $error, array("table" => $TABLE), h($TABLE));

$fields = array_keys(fields($TABLE));
if ($_POST["add"]) {
	foreach ($row["indexes"] as $key => $index) {
		if ($index["columns"][count($index["columns"])] != "") {
			$row["indexes"][$key]["columns"][] = "";
		}
	}
	$index = end($row["indexes"]);
	if ($index["type"] || array_filter($index["columns"], 'strlen')) {
		$row["indexes"][] = array("columns" => array(1 => ""));
	}
}
if (!$row) {
	foreach ($indexes as $key => $index) {
		$indexes[$key]["name"] = $key;
		$indexes[$key]["columns"][] = "";
	}
	$indexes[] = array("columns" => array(1 => ""));
	$row["indexes"] = $indexes;
}
?>

<form action="" method="post">
<div class="scrollable">
<table cellspacing="0" class="nowrap">
<thead><tr>
<th id="label-type"><?php echo lang(174); ?>
<th><input type="submit" class="wayoff"><?php echo lang(175); ?>
<th id="label-name"><?php echo lang(176); ?>
<th><noscript><?php echo "<input type='image' class='icon' name='add[0]' src='" . h(preg_replace("~\\?.*~", "", ME) . "?file=plus.gif&version=4.8.1") . "' alt='+' title='" . lang(102) . "'>"; ?></noscript>
</thead>
<?php
if ($primary) {
	echo "<tr><td>PRIMARY<td>";
	foreach ($primary["columns"] as $key => $column) {
		echo select_input(" disabled", $fields, $column);
		echo "<label><input disabled type='checkbox'>" . lang(48) . "</label> ";
	}
	echo "<td><td>\n";
}
$j = 1;
foreach ($row["indexes"] as $index) {
	if (!$_POST["drop_col"] || $j != key($_POST["drop_col"])) {
		echo "<tr><td>" . html_select("indexes[$j][type]", array(-1 => "") + $index_types, $index["type"], ($j == count($row["indexes"]) ? "indexesAddRow.call(this);" : 1), "label-type");

		echo "<td>";
		ksort($index["columns"]);
		$i = 1;
		foreach ($index["columns"] as $key => $column) {
			echo "<span>" . select_input(
				" name='indexes[$j][columns][$i]' title='" . lang(37) . "'",
				($fields ? array_combine($fields, $fields) : $fields),
				$column,
				"partial(" . ($i == count($index["columns"]) ? "indexesAddColumn" : "indexesChangeColumn") . ", '" . js_escape($jush == "sql" ? "" : $_GET["indexes"] . "_") . "')"
			);
			echo ($jush == "sql" || $jush == "mssql" ? "<input type='number' name='indexes[$j][lengths][$i]' class='size' value='" . h($index["lengths"][$key]) . "' title='" . lang(100) . "'>" : "");
			echo (support("descidx") ? checkbox("indexes[$j][descs][$i]", 1, $index["descs"][$key], lang(48)) : "");
			echo " </span>";
			$i++;
		}

		echo "<td><input name='indexes[$j][name]' value='" . h($index["name"]) . "' autocapitalize='off' aria-labelledby='label-name'>\n";
		echo "<td><input type='image' class='icon' name='drop_col[$j]' src='" . h(preg_replace("~\\?.*~", "", ME) . "?file=cross.gif&version=4.8.1") . "' alt='x' title='" . lang(105) . "'>" . script("qsl('input').onclick = partial(editingRemoveRow, 'indexes\$1[type]');");
	}
	$j++;
}
?>
</table>
</div>
<p>
<input type="submit" value="<?php echo lang(14); ?>">
<input type="hidden" name="token" value="<?php echo $token; ?>">
</form>
<?php
} elseif (isset($_GET["database"])) {
	
$row = $_POST;

if ($_POST && !$error && !isset($_POST["add_x"])) { // add is an image and PHP changes add.x to add_x
	$name = trim($row["name"]);
	if ($_POST["drop"]) {
		$_GET["db"] = ""; // to save in global history
		queries_redirect(remove_from_uri("db|database"), lang(177), drop_databases(array(DB)));
	} elseif (DB !== $name) {
		// create or rename database
		if (DB != "") {
			$_GET["db"] = $name;
			queries_redirect(preg_replace('~\bdb=[^&]*&~', '', ME) . "db=" . urlencode($name), lang(178), rename_database($name, $row["collation"]));
		} else {
			$databases = explode("\n", str_replace("\r", "", $name));
			$success = true;
			$last = "";
			foreach ($databases as $db) {
				if (count($databases) == 1 || $db != "") { // ignore empty lines but always try to create single database
					if (!create_database($db, $row["collation"])) {
						$success = false;
					}
					$last = $db;
				}
			}
			restart_session();
			set_session("dbs", null);
			queries_redirect(ME . "db=" . urlencode($last), lang(179), $success);
		}
	} else {
		// alter database
		if (!$row["collation"]) {
			redirect(substr(ME, 0, -1));
		}
		query_redirect("ALTER DATABASE " . idf_escape($name) . (preg_match('~^[a-z0-9_]+$~i', $row["collation"]) ? " COLLATE $row[collation]" : ""), substr(ME, 0, -1), lang(180));
	}
}

page_header(DB != "" ? lang(56) : lang(109), $error, array(), h(DB));

$collations = collations();
$name = DB;
if ($_POST) {
	$name = $row["name"];
} elseif (DB != "") {
	$row["collation"] = db_collation(DB, $collations);
} elseif ($jush == "sql") {
	// propose database name with limited privileges
	foreach (get_vals("SHOW GRANTS") as $grant) {
		if (preg_match('~ ON (`(([^\\\\`]|``|\\\\.)*)%`\.\*)?~', $grant, $match) && $match[1]) {
			$name = stripcslashes(idf_unescape("`$match[2]`"));
			break;
		}
	}
}
?>

<form action="" method="post">
<p>
<?php
echo ($_POST["add_x"] || strpos($name, "\n")
	? '<textarea id="name" name="name" rows="10" cols="40">' . h($name) . '</textarea><br>'
	: '<input name="name" id="name" value="' . h($name) . '" data-maxlength="64" autocapitalize="off">'
) . "\n" . ($collations ? html_select("collation", array("" => "(" . lang(95) . ")") + $collations, $row["collation"]) . doc_link(array(
	'sql' => "charset-charsets.html",
	'mariadb' => "supported-character-sets-and-collations/",
	
)) : "");
echo script("focus(qs('#name'));");
?>
<input type="submit" value="<?php echo lang(14); ?>">
<?php
if (DB != "") {
	echo "<input type='submit' name='drop' value='" . lang(121) . "'>" . confirm(lang(168, DB)) . "\n";
} elseif (!$_POST["add_x"] && $_GET["db"] == "") {
	echo "<input type='image' class='icon' name='add' src='" . h(preg_replace("~\\?.*~", "", ME) . "?file=plus.gif&version=4.8.1") . "' alt='+' title='" . lang(102) . "'>\n";
}
?>
<input type="hidden" name="token" value="<?php echo $token; ?>">
</form>
<?php
} elseif (isset($_GET["call"])) {
	
$PROCEDURE = ($_GET["name"] ? $_GET["name"] : $_GET["call"]);
page_header(lang(181) . ": " . h($PROCEDURE), $error);

$routine = routine($_GET["call"], (isset($_GET["callf"]) ? "FUNCTION" : "PROCEDURE"));
$in = array();
$out = array();
foreach ($routine["fields"] as $i => $field) {
	if (substr($field["inout"], -3) == "OUT") {
		$out[$i] = "@" . idf_escape($field["field"]) . " AS " . idf_escape($field["field"]);
	}
	if (!$field["inout"] || substr($field["inout"], 0, 2) == "IN") {
		$in[] = $i;
	}
}

if (!$error && $_POST) {
	$call = array();
	foreach ($routine["fields"] as $key => $field) {
		if (in_array($key, $in)) {
			$val = process_input($field);
			if ($val === false) {
				$val = "''";
			}
			if (isset($out[$key])) {
				$connection->query("SET @" . idf_escape($field["field"]) . " = $val");
			}
		}
		$call[] = (isset($out[$key]) ? "@" . idf_escape($field["field"]) : $val);
	}
	
	$query = (isset($_GET["callf"]) ? "SELECT" : "CALL") . " " . table($PROCEDURE) . "(" . implode(", ", $call) . ")";
	$start = microtime(true);
	$result = $connection->multi_query($query);
	$affected = $connection->affected_rows; // getting warnigns overwrites this
	echo $adminer->selectQuery($query, $start, !$result);
	
	if (!$result) {
		echo "<p class='error'>" . error() . "\n";
	} else {
		$connection2 = connect();
		if (is_object($connection2)) {
			$connection2->select_db(DB);
		}
		
		do {
			$result = $connection->store_result();
			if (is_object($result)) {
				select($result, $connection2);
			} else {
				echo "<p class='message'>" . lang(182, $affected)
					. " <span class='time'>" . @date("H:i:s") . "</span>\n" // @ - time zone may be not set
				;
			}
		} while ($connection->next_result());
		
		if ($out) {
			select($connection->query("SELECT " . implode(", ", $out)));
		}
	}
}
?>

<form action="" method="post">
<?php
if ($in) {
	echo "<table cellspacing='0' class='layout'>\n";
	foreach ($in as $key) {
		$field = $routine["fields"][$key];
		$name = $field["field"];
		echo "<tr><th>" . $adminer->fieldName($field);
		$value = $_POST["fields"][$name];
		if ($value != "") {
			if ($field["type"] == "enum") {
				$value = +$value;
			}
			if ($field["type"] == "set") {
				$value = array_sum($value);
			}
		}
		input($field, $value, (string) $_POST["function"][$name]); // param name can be empty
		echo "\n";
	}
	echo "</table>\n";
}
?>
<p>
<input type="submit" value="<?php echo lang(181); ?>">
<input type="hidden" name="token" value="<?php echo $token; ?>">
</form>
<?php
} elseif (isset($_GET["foreign"])) {
	
$TABLE = $_GET["foreign"];
$name = $_GET["name"];
$row = $_POST;

if ($_POST && !$error && !$_POST["add"] && !$_POST["change"] && !$_POST["change-js"]) {
	$message = ($_POST["drop"] ? lang(183) : ($name != "" ? lang(184) : lang(185)));
	$location = ME . "table=" . urlencode($TABLE);
	
	if (!$_POST["drop"]) {
		$row["source"] = array_filter($row["source"], 'strlen');
		ksort($row["source"]); // enforce input order
		$target = array();
		foreach ($row["source"] as $key => $val) {
			$target[$key] = $row["target"][$key];
		}
		$row["target"] = $target;
	}
	
	if ($jush == "sqlite") {
		queries_redirect($location, $message, recreate_table($TABLE, $TABLE, array(), array(), array(" $name" => ($_POST["drop"] ? "" : " " . format_foreign_key($row)))));
	} else {
		$alter = "ALTER TABLE " . table($TABLE);
		$drop = "\nDROP " . ($jush == "sql" ? "FOREIGN KEY " : "CONSTRAINT ") . idf_escape($name);
		if ($_POST["drop"]) {
			query_redirect($alter . $drop, $location, $message);
		} else {
			query_redirect($alter . ($name != "" ? "$drop," : "") . "\nADD" . format_foreign_key($row), $location, $message);
			$error = lang(186) . "<br>$error"; //! no partitioning
		}
	}
}

page_header(lang(187), $error, array("table" => $TABLE), h($TABLE));

if ($_POST) {
	ksort($row["source"]);
	if ($_POST["add"]) {
		$row["source"][] = "";
	} elseif ($_POST["change"] || $_POST["change-js"]) {
		$row["target"] = array();
	}
} elseif ($name != "") {
	$foreign_keys = foreign_keys($TABLE);
	$row = $foreign_keys[$name];
	$row["source"][] = "";
} else {
	$row["table"] = $TABLE;
	$row["source"] = array("");
}
?>

<form action="" method="post">
<?php
$source = array_keys(fields($TABLE)); //! no text and blob
if ($row["db"] != "") {
	$connection->select_db($row["db"]);
}
if ($row["ns"] != "") {
	set_schema($row["ns"]);
}
$referencable = array_keys(array_filter(table_status('', true), 'fk_support'));
$target = array_keys(fields(in_array($row["table"], $referencable) ? $row["table"] : reset($referencable)));
$onchange = "this.form['change-js'].value = '1'; this.form.submit();";
echo "<p>" . lang(188) . ": " . html_select("table", $referencable, $row["table"], $onchange) . "\n";
if ($jush == "pgsql") {
	echo lang(189) . ": " . html_select("ns", $adminer->schemas(), $row["ns"] != "" ? $row["ns"] : $_GET["ns"], $onchange);
} elseif ($jush != "sqlite") {
	$dbs = array();
	foreach ($adminer->databases() as $db) {
		if (!information_schema($db)) {
			$dbs[] = $db;
		}
	}
	echo lang(65) . ": " . html_select("db", $dbs, $row["db"] != "" ? $row["db"] : $_GET["db"], $onchange);
}
?>
<input type="hidden" name="change-js" value="">
<noscript><p><input type="submit" name="change" value="<?php echo lang(190); ?>"></noscript>
<table cellspacing="0">
<thead><tr><th id="label-source"><?php echo lang(127); ?><th id="label-target"><?php echo lang(128); ?></thead>
<?php
$j = 0;
foreach ($row["source"] as $key => $val) {
	echo "<tr>";
	echo "<td>" . html_select("source[" . (+$key) . "]", array(-1 => "") + $source, $val, ($j == count($row["source"]) - 1 ? "foreignAddRow.call(this);" : 1), "label-source");
	echo "<td>" . html_select("target[" . (+$key) . "]", $target, $row["target"][$key], 1, "label-target");
	$j++;
}
?>
</table>
<p>
<?php echo lang(97); ?>: <?php echo html_select("on_delete", array(-1 => "") + explode("|", $on_actions), $row["on_delete"]); ?>
 <?php echo lang(96); ?>: <?php echo html_select("on_update", array(-1 => "") + explode("|", $on_actions), $row["on_update"]);  echo doc_link(array(
	'sql' => "innodb-foreign-key-constraints.html",
	'mariadb' => "foreign-keys/",
	
	
	
)); ?>
<p>
<input type="submit" value="<?php echo lang(14); ?>">
<noscript><p><input type="submit" name="add" value="<?php echo lang(191); ?>"></noscript>
<?php if ($name != "") { ?><input type="submit" name="drop" value="<?php echo lang(121); ?>"><?php echo confirm(lang(168, $name));  } ?>
<input type="hidden" name="token" value="<?php echo $token; ?>">
</form>
<?php
} elseif (isset($_GET["view"])) {
	
$TABLE = $_GET["view"];
$row = $_POST;
$orig_type = "VIEW";
if ($jush == "pgsql" && $TABLE != "") {
	$status = table_status($TABLE);
	$orig_type = strtoupper($status["Engine"]);
}

if ($_POST && !$error) {
	$name = trim($row["name"]);
	$as = " AS\n$row[select]";
	$location = ME . "table=" . urlencode($name);
	$message = lang(192);

	$type = ($_POST["materialized"] ? "MATERIALIZED VIEW" : "VIEW");

	if (!$_POST["drop"] && $TABLE == $name && $jush != "sqlite" && $type == "VIEW" && $orig_type == "VIEW") {
		query_redirect(($jush == "mssql" ? "ALTER" : "CREATE OR REPLACE") . " VIEW " . table($name) . $as, $location, $message);
	} else {
		$temp_name = $name . "_adminer_" . uniqid();
		drop_create(
			"DROP $orig_type " . table($TABLE),
			"CREATE $type " . table($name) . $as,
			"DROP $type " . table($name),
			"CREATE $type " . table($temp_name) . $as,
			"DROP $type " . table($temp_name),
			($_POST["drop"] ? substr(ME, 0, -1) : $location),
			lang(193),
			$message,
			lang(194),
			$TABLE,
			$name
		);
	}
}

if (!$_POST && $TABLE != "") {
	$row = view($TABLE);
	$row["name"] = $TABLE;
	$row["materialized"] = ($orig_type != "VIEW");
	if (!$error) {
		$error = error();
	}
}

page_header(($TABLE != "" ? lang(32) : lang(195)), $error, array("table" => $TABLE), h($TABLE));
?>

<form action="" method="post">
<p><?php echo lang(176); ?>: <input name="name" value="<?php echo h($row["name"]); ?>" data-maxlength="64" autocapitalize="off">
<?php echo (support("materializedview") ? " " . checkbox("materialized", 1, $row["materialized"], lang(122)) : ""); ?>
<p><?php textarea("select", $row["select"]); ?>
<p>
<input type="submit" value="<?php echo lang(14); ?>">
<?php if ($TABLE != "") { ?><input type="submit" name="drop" value="<?php echo lang(121); ?>"><?php echo confirm(lang(168, $TABLE));  } ?>
<input type="hidden" name="token" value="<?php echo $token; ?>">
</form>
<?php
} elseif (isset($_GET["event"])) {
	
$EVENT = $_GET["event"];
$intervals = array("YEAR", "QUARTER", "MONTH", "DAY", "HOUR", "MINUTE", "WEEK", "SECOND", "YEAR_MONTH", "DAY_HOUR", "DAY_MINUTE", "DAY_SECOND", "HOUR_MINUTE", "HOUR_SECOND", "MINUTE_SECOND");
$statuses = array("ENABLED" => "ENABLE", "DISABLED" => "DISABLE", "SLAVESIDE_DISABLED" => "DISABLE ON SLAVE");
$row = $_POST;

if ($_POST && !$error) {
	if ($_POST["drop"]) {
		query_redirect("DROP EVENT " . idf_escape($EVENT), substr(ME, 0, -1), lang(196));
	} elseif (in_array($row["INTERVAL_FIELD"], $intervals) && isset($statuses[$row["STATUS"]])) {
		$schedule = "\nON SCHEDULE " . ($row["INTERVAL_VALUE"]
			? "EVERY " . q($row["INTERVAL_VALUE"]) . " $row[INTERVAL_FIELD]"
			. ($row["STARTS"] ? " STARTS " . q($row["STARTS"]) : "")
			. ($row["ENDS"] ? " ENDS " . q($row["ENDS"]) : "") //! ALTER EVENT doesn't drop ENDS - MySQL bug #39173
			: "AT " . q($row["STARTS"])
			) . " ON COMPLETION" . ($row["ON_COMPLETION"] ? "" : " NOT") . " PRESERVE"
		;
		
		queries_redirect(substr(ME, 0, -1), ($EVENT != "" ? lang(197) : lang(198)), queries(($EVENT != ""
			? "ALTER EVENT " . idf_escape($EVENT) . $schedule
			. ($EVENT != $row["EVENT_NAME"] ? "\nRENAME TO " . idf_escape($row["EVENT_NAME"]) : "")
			: "CREATE EVENT " . idf_escape($row["EVENT_NAME"]) . $schedule
			) . "\n" . $statuses[$row["STATUS"]] . " COMMENT " . q($row["EVENT_COMMENT"])
			. rtrim(" DO\n$row[EVENT_DEFINITION]", ";") . ";"
		));
	}
}

page_header(($EVENT != "" ? lang(199) . ": " . h($EVENT) : lang(200)), $error);

if (!$row && $EVENT != "") {
	$rows = get_rows("SELECT * FROM information_schema.EVENTS WHERE EVENT_SCHEMA = " . q(DB) . " AND EVENT_NAME = " . q($EVENT));
	$row = reset($rows);
}
?>

<form action="" method="post">
<table cellspacing="0" class="layout">
<tr><th><?php echo lang(176); ?><td><input name="EVENT_NAME" value="<?php echo h($row["EVENT_NAME"]); ?>" data-maxlength="64" autocapitalize="off">
<tr><th title="datetime"><?php echo lang(201); ?><td><input name="STARTS" value="<?php echo h("$row[EXECUTE_AT]$row[STARTS]"); ?>">
<tr><th title="datetime"><?php echo lang(202); ?><td><input name="ENDS" value="<?php echo h($row["ENDS"]); ?>">
<tr><th><?php echo lang(203); ?><td><input type="number" name="INTERVAL_VALUE" value="<?php echo h($row["INTERVAL_VALUE"]); ?>" class="size"> <?php echo html_select("INTERVAL_FIELD", $intervals, $row["INTERVAL_FIELD"]); ?>
<tr><th><?php echo lang(112); ?><td><?php echo html_select("STATUS", $statuses, $row["STATUS"]); ?>
<tr><th><?php echo lang(39); ?><td><input name="EVENT_COMMENT" value="<?php echo h($row["EVENT_COMMENT"]); ?>" data-maxlength="64">
<tr><th><td><?php echo checkbox("ON_COMPLETION", "PRESERVE", $row["ON_COMPLETION"] == "PRESERVE", lang(204)); ?>
</table>
<p><?php textarea("EVENT_DEFINITION", $row["EVENT_DEFINITION"]); ?>
<p>
<input type="submit" value="<?php echo lang(14); ?>">
<?php if ($EVENT != "") { ?><input type="submit" name="drop" value="<?php echo lang(121); ?>"><?php echo confirm(lang(168, $EVENT));  } ?>
<input type="hidden" name="token" value="<?php echo $token; ?>">
</form>
<?php
} elseif (isset($_GET["procedure"])) {
	
$PROCEDURE = ($_GET["name"] ? $_GET["name"] : $_GET["procedure"]);
$routine = (isset($_GET["function"]) ? "FUNCTION" : "PROCEDURE");
$row = $_POST;
$row["fields"] = (array) $row["fields"];

if ($_POST && !process_fields($row["fields"]) && !$error) {
	$orig = routine($_GET["procedure"], $routine);
	$temp_name = "$row[name]_adminer_" . uniqid();
	drop_create(
		"DROP $routine " . routine_id($PROCEDURE, $orig),
		create_routine($routine, $row),
		"DROP $routine " . routine_id($row["name"], $row),
		create_routine($routine, array("name" => $temp_name) + $row),
		"DROP $routine " . routine_id($temp_name, $row),
		substr(ME, 0, -1),
		lang(205),
		lang(206),
		lang(207),
		$PROCEDURE,
		$row["name"]
	);
}

page_header(($PROCEDURE != "" ? (isset($_GET["function"]) ? lang(208) : lang(209)) . ": " . h($PROCEDURE) : (isset($_GET["function"]) ? lang(210) : lang(211))), $error);

if (!$_POST && $PROCEDURE != "") {
	$row = routine($_GET["procedure"], $routine);
	$row["name"] = $PROCEDURE;
}

$collations = get_vals("SHOW CHARACTER SET");
sort($collations);
$routine_languages = routine_languages();
?>

<form action="" method="post" id="form">
<p><?php echo lang(176); ?>: <input name="name" value="<?php echo h($row["name"]); ?>" data-maxlength="64" autocapitalize="off">
<?php echo ($routine_languages ? lang(19) . ": " . html_select("language", $routine_languages, $row["language"]) . "\n" : ""); ?>
<input type="submit" value="<?php echo lang(14); ?>">
<div class="scrollable">
<table cellspacing="0" class="nowrap">
<?php
edit_fields($row["fields"], $collations, $routine);
if (isset($_GET["function"])) {
	echo "<tr><td>" . lang(212);
	edit_type("returns", $row["returns"], $collations, array(), ($jush == "pgsql" ? array("void", "trigger") : array()));
}
?>
</table>
<?php echo script("editFields();"); ?>
</div>
<p><?php textarea("definition", $row["definition"]); ?>
<p>
<input type="submit" value="<?php echo lang(14); ?>">
<?php if ($PROCEDURE != "") { ?><input type="submit" name="drop" value="<?php echo lang(121); ?>"><?php echo confirm(lang(168, $PROCEDURE));  } ?>
<input type="hidden" name="token" value="<?php echo $token; ?>">
</form>
<?php
} elseif (isset($_GET["trigger"])) {
	
$TABLE = $_GET["trigger"];
$name = $_GET["name"];
$trigger_options = trigger_options();
$row = (array) trigger($name, $TABLE) + array("Trigger" => $TABLE . "_bi");

if ($_POST) {
	if (!$error && in_array($_POST["Timing"], $trigger_options["Timing"]) && in_array($_POST["Event"], $trigger_options["Event"]) && in_array($_POST["Type"], $trigger_options["Type"])) {
		// don't use drop_create() because there may not be more triggers for the same action
		$on = " ON " . table($TABLE);
		$drop = "DROP TRIGGER " . idf_escape($name) . ($jush == "pgsql" ? $on : "");
		$location = ME . "table=" . urlencode($TABLE);
		if ($_POST["drop"]) {
			query_redirect($drop, $location, lang(213));
		} else {
			if ($name != "") {
				queries($drop);
			}
			queries_redirect(
				$location,
				($name != "" ? lang(214) : lang(215)),
				queries(create_trigger($on, $_POST))
			);
			if ($name != "") {
				queries(create_trigger($on, $row + array("Type" => reset($trigger_options["Type"]))));
			}
		}
	}
	$row = $_POST;
}

page_header(($name != "" ? lang(216) . ": " . h($name) : lang(217)), $error, array("table" => $TABLE));
?>

<form action="" method="post" id="form">
<table cellspacing="0" class="layout">
<tr><th><?php echo lang(218); ?><td><?php echo html_select("Timing", $trigger_options["Timing"], $row["Timing"], "triggerChange(/^" . preg_quote($TABLE, "/") . "_[ba][iud]$/, '" . js_escape($TABLE) . "', this.form);"); ?>
<tr><th><?php echo lang(219); ?><td><?php echo html_select("Event", $trigger_options["Event"], $row["Event"], "this.form['Timing'].onchange();");  echo (in_array("UPDATE OF", $trigger_options["Event"]) ? " <input name='Of' value='" . h($row["Of"]) . "' class='hidden'>": ""); ?>
<tr><th><?php echo lang(38); ?><td><?php echo html_select("Type", $trigger_options["Type"], $row["Type"]); ?>
</table>
<p><?php echo lang(176); ?>: <input name="Trigger" value="<?php echo h($row["Trigger"]); ?>" data-maxlength="64" autocapitalize="off">
<?php echo script("qs('#form')['Timing'].onchange();"); ?>
<p><?php textarea("Statement", $row["Statement"]); ?>
<p>
<input type="submit" value="<?php echo lang(14); ?>">
<?php if ($name != "") { ?><input type="submit" name="drop" value="<?php echo lang(121); ?>"><?php echo confirm(lang(168, $name));  } ?>
<input type="hidden" name="token" value="<?php echo $token; ?>">
</form>
<?php
} elseif (isset($_GET["user"])) {
	
$USER = $_GET["user"];
$privileges = array("" => array("All privileges" => ""));
foreach (get_rows("SHOW PRIVILEGES") as $row) {
	foreach (explode(",", ($row["Privilege"] == "Grant option" ? "" : $row["Context"])) as $context) {
		$privileges[$context][$row["Privilege"]] = $row["Comment"];
	}
}
$privileges["Server Admin"] += $privileges["File access on server"];
$privileges["Databases"]["Create routine"] = $privileges["Procedures"]["Create routine"]; // MySQL bug #30305
unset($privileges["Procedures"]["Create routine"]);
$privileges["Columns"] = array();
foreach (array("Select", "Insert", "Update", "References") as $val) {
	$privileges["Columns"][$val] = $privileges["Tables"][$val];
}
unset($privileges["Server Admin"]["Usage"]);
foreach ($privileges["Tables"] as $key => $val) {
	unset($privileges["Databases"][$key]);
}

$new_grants = array();
if ($_POST) {
	foreach ($_POST["objects"] as $key => $val) {
		$new_grants[$val] = (array) $new_grants[$val] + (array) $_POST["grants"][$key];
	}
}
$grants = array();
$old_pass = "";

if (isset($_GET["host"]) && ($result = $connection->query("SHOW GRANTS FOR " . q($USER) . "@" . q($_GET["host"])))) { //! use information_schema for MySQL 5 - column names in column privileges are not escaped
	while ($row = $result->fetch_row()) {
		if (preg_match('~GRANT (.*) ON (.*) TO ~', $row[0], $match) && preg_match_all('~ *([^(,]*[^ ,(])( *\([^)]+\))?~', $match[1], $matches, PREG_SET_ORDER)) { //! escape the part between ON and TO
			foreach ($matches as $val) {
				if ($val[1] != "USAGE") {
					$grants["$match[2]$val[2]"][$val[1]] = true;
				}
				if (preg_match('~ WITH GRANT OPTION~', $row[0])) { //! don't check inside strings and identifiers
					$grants["$match[2]$val[2]"]["GRANT OPTION"] = true;
				}
			}
		}
		if (preg_match("~ IDENTIFIED BY PASSWORD '([^']+)~", $row[0], $match)) {
			$old_pass = $match[1];
		}
	}
}

if ($_POST && !$error) {
	$old_user = (isset($_GET["host"]) ? q($USER) . "@" . q($_GET["host"]) : "''");
	if ($_POST["drop"]) {
		query_redirect("DROP USER $old_user", ME . "privileges=", lang(220));
	} else {
		$new_user = q($_POST["user"]) . "@" . q($_POST["host"]); // if $_GET["host"] is not set then $new_user is always different
		$pass = $_POST["pass"];
		if ($pass != '' && !$_POST["hashed"] && !min_version(8)) {
			// compute hash in a separate query so that plain text password is not saved to history
			$pass = $connection->result("SELECT PASSWORD(" . q($pass) . ")");
			$error = !$pass;
		}

		$created = false;
		if (!$error) {
			if ($old_user != $new_user) {
				$created = queries((min_version(5) ? "CREATE USER" : "GRANT USAGE ON *.* TO") . " $new_user IDENTIFIED BY " . (min_version(8) ? "" : "PASSWORD ") . q($pass));
				$error = !$created;
			} elseif ($pass != $old_pass) {
				queries("SET PASSWORD FOR $new_user = " . q($pass));
			}
		}

		if (!$error) {
			$revoke = array();
			foreach ($new_grants as $object => $grant) {
				if (isset($_GET["grant"])) {
					$grant = array_filter($grant);
				}
				$grant = array_keys($grant);
				if (isset($_GET["grant"])) {
					// no rights to mysql.user table
					$revoke = array_diff(array_keys(array_filter($new_grants[$object], 'strlen')), $grant);
				} elseif ($old_user == $new_user) {
					$old_grant = array_keys((array) $grants[$object]);
					$revoke = array_diff($old_grant, $grant);
					$grant = array_diff($grant, $old_grant);
					unset($grants[$object]);
				}
				if (preg_match('~^(.+)\s*(\(.*\))?$~U', $object, $match) && (
					!grant("REVOKE", $revoke, $match[2], " ON $match[1] FROM $new_user") //! SQL injection
					|| !grant("GRANT", $grant, $match[2], " ON $match[1] TO $new_user")
				)) {
					$error = true;
					break;
				}
			}
		}

		if (!$error && isset($_GET["host"])) {
			if ($old_user != $new_user) {
				queries("DROP USER $old_user");
			} elseif (!isset($_GET["grant"])) {
				foreach ($grants as $object => $revoke) {
					if (preg_match('~^(.+)(\(.*\))?$~U', $object, $match)) {
						grant("REVOKE", array_keys($revoke), $match[2], " ON $match[1] FROM $new_user");
					}
				}
			}
		}

		queries_redirect(ME . "privileges=", (isset($_GET["host"]) ? lang(221) : lang(222)), !$error);

		if ($created) {
			// delete new user in case of an error
			$connection->query("DROP USER $new_user");
		}
	}
}

page_header((isset($_GET["host"]) ? lang(24) . ": " . h("$USER@$_GET[host]") : lang(139)), $error, array("privileges" => array('', lang(60))));

if ($_POST) {
	$row = $_POST;
	$grants = $new_grants;
} else {
	$row = $_GET + array("host" => $connection->result("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', -1)")); // create user on the same domain by default
	$row["pass"] = $old_pass;
	if ($old_pass != "") {
		$row["hashed"] = true;
	}
	$grants[(DB == "" || $grants ? "" : idf_escape(addcslashes(DB, "%_\\"))) . ".*"] = array();
}

?>
<form action="" method="post">
<table cellspacing="0" class="layout">
<tr><th><?php echo lang(23); ?><td><input name="host" data-maxlength="60" value="<?php echo h($row["host"]); ?>" autocapitalize="off">
<tr><th><?php echo lang(24); ?><td><input name="user" data-maxlength="80" value="<?php echo h($row["user"]); ?>" autocapitalize="off">
<tr><th><?php echo lang(25); ?><td><input name="pass" id="pass" value="<?php echo h($row["pass"]); ?>" autocomplete="new-password">
<?php if (!$row["hashed"]) { echo script("typePassword(qs('#pass'));"); }  echo (min_version(8) ? "" : checkbox("hashed", 1, $row["hashed"], lang(223), "typePassword(this.form['pass'], this.checked);")); ?>
</table>

<?php
//! MAX_* limits, REQUIRE
echo "<table cellspacing='0'>\n";
echo "<thead><tr><th colspan='2'>" . lang(60) . doc_link(array('sql' => "grant.html#priv_level"));
$i = 0;
foreach ($grants as $object => $grant) {
	echo '<th>' . ($object != "*.*" ? "<input name='objects[$i]' value='" . h($object) . "' size='10' autocapitalize='off'>" : "<input type='hidden' name='objects[$i]' value='*.*' size='10'>*.*"); //! separate db, table, columns, PROCEDURE|FUNCTION, routine
	$i++;
}
echo "</thead>\n";

foreach (array(
	"" => "",
	"Server Admin" => lang(23),
	"Databases" => lang(26),
	"Tables" => lang(124),
	"Columns" => lang(37),
	"Procedures" => lang(224),
) as $context => $desc) {
	foreach ((array) $privileges[$context] as $privilege => $comment) {
		echo "<tr" . odd() . "><td" . ($desc ? ">$desc<td" : " colspan='2'") . ' lang="en" title="' . h($comment) . '">' . h($privilege);
		$i = 0;
		foreach ($grants as $object => $grant) {
			$name = "'grants[$i][" . h(strtoupper($privilege)) . "]'";
			$value = $grant[strtoupper($privilege)];
			if ($context == "Server Admin" && $object != (isset($grants["*.*"]) ? "*.*" : ".*")) {
				echo "<td>";
			} elseif (isset($_GET["grant"])) {
				echo "<td><select name=$name><option><option value='1'" . ($value ? " selected" : "") . ">" . lang(225) . "<option value='0'" . ($value == "0" ? " selected" : "") . ">" . lang(226) . "</select>";
			} else {
				echo "<td align='center'><label class='block'>";
				echo "<input type='checkbox' name=$name value='1'" . ($value ? " checked" : "") . ($privilege == "All privileges"
					? " id='grants-$i-all'>" //! uncheck all except grant if all is checked
					: ">" . ($privilege == "Grant option" ? "" : script("qsl('input').onclick = function () { if (this.checked) formUncheck('grants-$i-all'); };")));
				echo "</label>";
			}
			$i++;
		}
	}
}

echo "</table>\n";
?>
<p>
<input type="submit" value="<?php echo lang(14); ?>">
<?php if (isset($_GET["host"])) { ?><input type="submit" name="drop" value="<?php echo lang(121); ?>"><?php echo confirm(lang(168, "$USER@$_GET[host]"));  } ?>
<input type="hidden" name="token" value="<?php echo $token; ?>">
</form>
<?php
} elseif (isset($_GET["processlist"])) {
	
if (support("kill")) {
	if ($_POST && !$error) {
		$killed = 0;
		foreach ((array) $_POST["kill"] as $val) {
			if (kill_process($val)) {
				$killed++;
			}
		}
		queries_redirect(ME . "processlist=", lang(227, $killed), $killed || !$_POST["kill"]);
	}
}

page_header(lang(110), $error);
?>

<form action="" method="post">
<div class="scrollable">
<table cellspacing="0" class="nowrap checkable">
<?php
echo script("mixin(qsl('table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});");
// HTML valid because there is always at least one process
$i = -1;
foreach (process_list() as $i => $row) {

	if (!$i) {
		echo "<thead><tr lang='en'>" . (support("kill") ? "<th>" : "");
		foreach ($row as $key => $val) {
			echo "<th>$key" . doc_link(array(
				'sql' => "show-processlist.html#processlist_" . strtolower($key),
				
				
			));
		}
		echo "</thead>\n";
	}
	echo "<tr" . odd() . ">" . (support("kill") ? "<td>" . checkbox("kill[]", $row[$jush == "sql" ? "Id" : "pid"], 0) : "");
	foreach ($row as $key => $val) {
		echo "<td>" . (
			($jush == "sql" && $key == "Info" && preg_match("~Query|Killed~", $row["Command"]) && $val != "") ||
			($jush == "pgsql" && $key == "current_query" && $val != "<IDLE>") ||
			($jush == "oracle" && $key == "sql_text" && $val != "")
			? "<code class='jush-$jush'>" . shorten_utf8($val, 100, "</code>") . ' <a href="' . h(ME . ($row["db"] != "" ? "db=" . urlencode($row["db"]) . "&" : "") . "sql=" . urlencode($val)) . '">' . lang(228) . '</a>'
			: h($val)
		);
	}
	echo "\n";
}
?>
</table>
</div>
<p>
<?php
if (support("kill")) {
	echo ($i + 1) . "/" . lang(229, max_connections());
	echo "<p><input type='submit' value='" . lang(230) . "'>\n";
}
?>
<input type="hidden" name="token" value="<?php echo $token; ?>">
</form>
<?php echo script("tableCheck();"); 
} elseif (isset($_GET["select"])) {
	
$TABLE = $_GET["select"];
$table_status = table_status1($TABLE);
$indexes = indexes($TABLE);
$fields = fields($TABLE);
$foreign_keys = column_foreign_keys($TABLE);
$oid = $table_status["Oid"];
parse_str($_COOKIE["adminer_import"], $adminer_import);

$rights = array(); // privilege => 0
$columns = array(); // selectable columns
$text_length = null;
foreach ($fields as $key => $field) {
	$name = $adminer->fieldName($field);
	if (isset($field["privileges"]["select"]) && $name != "") {
		$columns[$key] = html_entity_decode(strip_tags($name), ENT_QUOTES);
		if (is_shortable($field)) {
			$text_length = $adminer->selectLengthProcess();
		}
	}
	$rights += $field["privileges"];
}

list($select, $group) = $adminer->selectColumnsProcess($columns, $indexes);
$is_group = count($group) < count($select);
$where = $adminer->selectSearchProcess($fields, $indexes);
$order = $adminer->selectOrderProcess($fields, $indexes);
$limit = $adminer->selectLimitProcess();

if ($_GET["val"] && is_ajax()) {
	header("Content-Type: text/plain; charset=utf-8");
	foreach ($_GET["val"] as $unique_idf => $row) {
		$as = convert_field($fields[key($row)]);
		$select = array($as ? $as : idf_escape(key($row)));
		$where[] = where_check($unique_idf, $fields);
		$return = $driver->select($TABLE, $select, $where, $select);
		if ($return) {
			echo reset($return->fetch_row());
		}
	}
	exit;
}

$primary = $unselected = null;
foreach ($indexes as $index) {
	if ($index["type"] == "PRIMARY") {
		$primary = array_flip($index["columns"]);
		$unselected = ($select ? $primary : array());
		foreach ($unselected as $key => $val) {
			if (in_array(idf_escape($key), $select)) {
				unset($unselected[$key]);
			}
		}
		break;
	}
}
if ($oid && !$primary) {
	$primary = $unselected = array($oid => 0);
	$indexes[] = array("type" => "PRIMARY", "columns" => array($oid));
}

if ($_POST && !$error) {
	$where_check = $where;
	if (!$_POST["all"] && is_array($_POST["check"])) {
		$checks = array();
		foreach ($_POST["check"] as $check) {
			$checks[] = where_check($check, $fields);
		}
		$where_check[] = "((" . implode(") OR (", $checks) . "))";
	}
	$where_check = ($where_check ? "\nWHERE " . implode(" AND ", $where_check) : "");
	if ($_POST["export"]) {
		cookie("adminer_import", "output=" . urlencode($_POST["output"]) . "&format=" . urlencode($_POST["format"]));
		dump_headers($TABLE);
		$adminer->dumpTable($TABLE, "");
		$from = ($select ? implode(", ", $select) : "*")
			. convert_fields($columns, $fields, $select)
			. "\nFROM " . table($TABLE);
		$group_by = ($group && $is_group ? "\nGROUP BY " . implode(", ", $group) : "") . ($order ? "\nORDER BY " . implode(", ", $order) : "");
		if (!is_array($_POST["check"]) || $primary) {
			$query = "SELECT $from$where_check$group_by";
		} else {
			$union = array();
			foreach ($_POST["check"] as $val) {
				// where is not unique so OR can't be used
				$union[] = "(SELECT" . limit($from, "\nWHERE " . ($where ? implode(" AND ", $where) . " AND " : "") . where_check($val, $fields) . $group_by, 1) . ")";
			}
			$query = implode(" UNION ALL ", $union);
		}
		$adminer->dumpData($TABLE, "table", $query);
		exit;
	}

	if (!$adminer->selectEmailProcess($where, $foreign_keys)) {
		if ($_POST["save"] || $_POST["delete"]) { // edit
			$result = true;
			$affected = 0;
			$set = array();
			if (!$_POST["delete"]) {
				foreach ($columns as $name => $val) { //! should check also for edit or insert privileges
					$val = process_input($fields[$name]);
					if ($val !== null && ($_POST["clone"] || $val !== false)) {
						$set[idf_escape($name)] = ($val !== false ? $val : idf_escape($name));
					}
				}
			}
			if ($_POST["delete"] || $set) {
				if ($_POST["clone"]) {
					$query = "INTO " . table($TABLE) . " (" . implode(", ", array_keys($set)) . ")\nSELECT " . implode(", ", $set) . "\nFROM " . table($TABLE);
				}
				if ($_POST["all"] || ($primary && is_array($_POST["check"])) || $is_group) {
					$result = ($_POST["delete"]
						? $driver->delete($TABLE, $where_check)
						: ($_POST["clone"]
							? queries("INSERT $query$where_check")
							: $driver->update($TABLE, $set, $where_check)
						)
					);
					$affected = $connection->affected_rows;
				} else {
					foreach ((array) $_POST["check"] as $val) {
						// where is not unique so OR can't be used
						$where2 = "\nWHERE " . ($where ? implode(" AND ", $where) . " AND " : "") . where_check($val, $fields);
						$result = ($_POST["delete"]
							? $driver->delete($TABLE, $where2, 1)
							: ($_POST["clone"]
								? queries("INSERT" . limit1($TABLE, $query, $where2))
								: $driver->update($TABLE, $set, $where2, 1)
							)
						);
						if (!$result) {
							break;
						}
						$affected += $connection->affected_rows;
					}
				}
			}
			$message = lang(231, $affected);
			if ($_POST["clone"] && $result && $affected == 1) {
				$last_id = last_id();
				if ($last_id) {
					$message = lang(161, " $last_id");
				}
			}
			queries_redirect(remove_from_uri($_POST["all"] && $_POST["delete"] ? "page" : ""), $message, $result);
			if (!$_POST["delete"]) {
				edit_form($TABLE, $fields, (array) $_POST["fields"], !$_POST["clone"]);
				page_footer();
				exit;
			}

		} elseif (!$_POST["import"]) { // modify
			if (!$_POST["val"]) {
				$error = lang(232);
			} else {
				$result = true;
				$affected = 0;
				foreach ($_POST["val"] as $unique_idf => $row) {
					$set = array();
					foreach ($row as $key => $val) {
						$key = bracket_escape($key, 1); // 1 - back
						$set[idf_escape($key)] = (preg_match('~char|text~', $fields[$key]["type"]) || $val != "" ? $adminer->processInput($fields[$key], $val) : "NULL");
					}
					$result = $driver->update(
						$TABLE,
						$set,
						" WHERE " . ($where ? implode(" AND ", $where) . " AND " : "") . where_check($unique_idf, $fields),
						!$is_group && !$primary,
						" "
					);
					if (!$result) {
						break;
					}
					$affected += $connection->affected_rows;
				}
				queries_redirect(remove_from_uri(), lang(231, $affected), $result);
			}

		} elseif (!is_string($file = get_file("csv_file", true))) {
			$error = upload_error($file);
		} elseif (!preg_match('~~u', $file)) {
			$error = lang(233);
		} else {
			cookie("adminer_import", "output=" . urlencode($adminer_import["output"]) . "&format=" . urlencode($_POST["separator"]));
			$result = true;
			$cols = array_keys($fields);
			preg_match_all('~(?>"[^"]*"|[^"\r\n]+)+~', $file, $matches);
			$affected = count($matches[0]);
			$driver->begin();
			$separator = ($_POST["separator"] == "csv" ? "," : ($_POST["separator"] == "tsv" ? "\t" : ";"));
			$rows = array();
			foreach ($matches[0] as $key => $val) {
				preg_match_all("~((?>\"[^\"]*\")+|[^$separator]*)$separator~", $val . $separator, $matches2);
				if (!$key && !array_diff($matches2[1], $cols)) { //! doesn't work with column names containing ",\n
					// first row corresponds to column names - use it for table structure
					$cols = $matches2[1];
					$affected--;
				} else {
					$set = array();
					foreach ($matches2[1] as $i => $col) {
						$set[idf_escape($cols[$i])] = ($col == "" && $fields[$cols[$i]]["null"] ? "NULL" : q(str_replace('""', '"', preg_replace('~^"|"$~', '', $col))));
					}
					$rows[] = $set;
				}
			}
			$result = (!$rows || $driver->insertUpdate($TABLE, $rows, $primary));
			if ($result) {
				$result = $driver->commit();
			}
			queries_redirect(remove_from_uri("page"), lang(234, $affected), $result);
			$driver->rollback(); // after queries_redirect() to not overwrite error

		}
	}
}

$table_name = $adminer->tableName($table_status);
if (is_ajax()) {
	page_headers();
	ob_start();
} else {
	page_header(lang(42) . ": $table_name", $error);
}

$set = null;
if (isset($rights["insert"]) || !support("table")) {
	$set = "";
	foreach ((array) $_GET["where"] as $val) {
		if ($foreign_keys[$val["col"]] && count($foreign_keys[$val["col"]]) == 1 && ($val["op"] == "="
			|| (!$val["op"] && !preg_match('~[_%]~', $val["val"])) // LIKE in Editor
		)) {
			$set .= "&set" . urlencode("[" . bracket_escape($val["col"]) . "]") . "=" . urlencode($val["val"]);
		}
	}
}
$adminer->selectLinks($table_status, $set);

if (!$columns && support("table")) {
	echo "<p class='error'>" . lang(235) . ($fields ? "." : ": " . error()) . "\n";
} else {
	echo "<form action='' id='form'>\n";
	echo "<div style='display: none;'>";
	hidden_fields_get();
	echo (DB != "" ? '<input type="hidden" name="db" value="' . h(DB) . '">' . (isset($_GET["ns"]) ? '<input type="hidden" name="ns" value="' . h($_GET["ns"]) . '">' : "") : ""); // not used in Editor
	echo '<input type="hidden" name="select" value="' . h($TABLE) . '">';
	echo "</div>\n";
	$adminer->selectColumnsPrint($select, $columns);
	$adminer->selectSearchPrint($where, $columns, $indexes);
	$adminer->selectOrderPrint($order, $columns, $indexes);
	$adminer->selectLimitPrint($limit);
	$adminer->selectLengthPrint($text_length);
	$adminer->selectActionPrint($indexes);
	echo "</form>\n";

	$page = $_GET["page"];
	if ($page == "last") {
		$found_rows = $connection->result(count_rows($TABLE, $where, $is_group, $group));
		$page = floor(max(0, $found_rows - 1) / $limit);
	}

	$select2 = $select;
	$group2 = $group;
	if (!$select2) {
		$select2[] = "*";
		$convert_fields = convert_fields($columns, $fields, $select);
		if ($convert_fields) {
			$select2[] = substr($convert_fields, 2);
		}
	}
	foreach ($select as $key => $val) {
		$field = $fields[idf_unescape($val)];
		if ($field && ($as = convert_field($field))) {
			$select2[$key] = "$as AS $val";
		}
	}
	if (!$is_group && $unselected) {
		foreach ($unselected as $key => $val) {
			$select2[] = idf_escape($key);
			if ($group2) {
				$group2[] = idf_escape($key);
			}
		}
	}
	$result = $driver->select($TABLE, $select2, $where, $group2, $order, $limit, $page, true);

	if (!$result) {
		echo "<p class='error'>" . error() . "\n";
	} else {
		if ($jush == "mssql" && $page) {
			$result->seek($limit * $page);
		}
		$email_fields = array();
		echo "<form action='' method='post' enctype='multipart/form-data'>\n";
		$rows = array();
		while ($row = $result->fetch_assoc()) {
			if ($page && $jush == "oracle") {
				unset($row["RNUM"]);
			}
			$rows[] = $row;
		}

		// use count($rows) without LIMIT, COUNT(*) without grouping, FOUND_ROWS otherwise (slowest)
		if ($_GET["page"] != "last" && $limit != "" && $group && $is_group && $jush == "sql") {
			$found_rows = $connection->result(" SELECT FOUND_ROWS()"); // space to allow mysql.trace_mode
		}

		if (!$rows) {
			echo "<p class='message'>" . lang(12) . "\n";
		} else {
			$backward_keys = $adminer->backwardKeys($TABLE, $table_name);

			echo "<div class='scrollable'>";
			echo "<table id='table' cellspacing='0' class='nowrap checkable'>";
			echo script("mixin(qs('#table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true), onkeydown: editingKeydown});");
			echo "<thead><tr>" . (!$group && $select
				? ""
				: "<td><input type='checkbox' id='all-page' class='jsonly'>" . script("qs('#all-page').onclick = partial(formCheck, /check/);", "")
					. " <a href='" . h($_GET["modify"] ? remove_from_uri("modify") : $_SERVER["REQUEST_URI"] . "&modify=1") . "'>" . lang(236) . "</a>");
			$names = array();
			$functions = array();
			reset($select);
			$rank = 1;
			foreach ($rows[0] as $key => $val) {
				if (!isset($unselected[$key])) {
					$val = $_GET["columns"][key($select)];
					$field = $fields[$select ? ($val ? $val["col"] : current($select)) : $key];
					$name = ($field ? $adminer->fieldName($field, $rank) : ($val["fun"] ? "*" : $key));
					if ($name != "") {
						$rank++;
						$names[$key] = $name;
						$column = idf_escape($key);
						$href = remove_from_uri('(order|desc)[^=]*|page') . '&order%5B0%5D=' . urlencode($key);
						$desc = "&desc%5B0%5D=1";
						echo "<th id='th[" . h(bracket_escape($key)) . "]'>" . script("mixin(qsl('th'), {onmouseover: partial(columnMouse), onmouseout: partial(columnMouse, ' hidden')});", "");
						echo '<a href="' . h($href . ($order[0] == $column || $order[0] == $key || (!$order && $is_group && $group[0] == $column) ? $desc : '')) . '">'; // $order[0] == $key - COUNT(*)
						echo apply_sql_function($val["fun"], $name) . "</a>"; //! columns looking like functions
						echo "<span class='column hidden'>";
						echo "<a href='" . h($href . $desc) . "' title='" . lang(48) . "' class='text'> ↓</a>";
						if (!$val["fun"]) {
							echo '<a href="#fieldset-search" title="' . lang(45) . '" class="text jsonly"> =</a>';
							echo script("qsl('a').onclick = partial(selectSearch, '" . js_escape($key) . "');");
						}
						echo "</span>";
					}
					$functions[$key] = $val["fun"];
					next($select);
				}
			}

			$lengths = array();
			if ($_GET["modify"]) {
				foreach ($rows as $row) {
					foreach ($row as $key => $val) {
						$lengths[$key] = max($lengths[$key], min(40, strlen(utf8_decode($val))));
					}
				}
			}

			echo ($backward_keys ? "<th>" . lang(237) : "") . "</thead>\n";

			if (is_ajax()) {
				if ($limit % 2 == 1 && $page % 2 == 1) {
					odd();
				}
				ob_end_clean();
			}

			foreach ($adminer->rowDescriptions($rows, $foreign_keys) as $n => $row) {
				$unique_array = unique_array($rows[$n], $indexes);
				if (!$unique_array) {
					$unique_array = array();
					foreach ($rows[$n] as $key => $val) {
						if (!preg_match('~^(COUNT\((\*|(DISTINCT )?`(?:[^`]|``)+`)\)|(AVG|GROUP_CONCAT|MAX|MIN|SUM)\(`(?:[^`]|``)+`\))$~', $key)) { //! columns looking like functions
							$unique_array[$key] = $val;
						}
					}
				}
				$unique_idf = "";
				foreach ($unique_array as $key => $val) {
					if (($jush == "sql" || $jush == "pgsql") && preg_match('~char|text|enum|set~', $fields[$key]["type"]) && strlen($val) > 64) {
						$key = (strpos($key, '(') ? $key : idf_escape($key)); //! columns looking like functions
						$key = "MD5(" . ($jush != 'sql' || preg_match("~^utf8~", $fields[$key]["collation"]) ? $key : "CONVERT($key USING " . charset($connection) . ")") . ")";
						$val = md5($val);
					}
					$unique_idf .= "&" . ($val !== null ? urlencode("where[" . bracket_escape($key) . "]") . "=" . urlencode($val) : "null%5B%5D=" . urlencode($key));
				}
				echo "<tr" . odd() . ">" . (!$group && $select ? "" : "<td>"
					. checkbox("check[]", substr($unique_idf, 1), in_array(substr($unique_idf, 1), (array) $_POST["check"]))
					. ($is_group || information_schema(DB) ? "" : " <a href='" . h(ME . "edit=" . urlencode($TABLE) . $unique_idf) . "' class='edit'>" . lang(238) . "</a>")
				);

				foreach ($row as $key => $val) {
					if (isset($names[$key])) {
						$field = $fields[$key];
						$val = $driver->value($val, $field);
						if ($val != "" && (!isset($email_fields[$key]) || $email_fields[$key] != "")) {
							$email_fields[$key] = (is_mail($val) ? $names[$key] : ""); //! filled e-mails can be contained on other pages
						}

						$link = "";
						if (preg_match('~blob|bytea|raw|file~', $field["type"]) && $val != "") {
							$link = ME . 'download=' . urlencode($TABLE) . '&field=' . urlencode($key) . $unique_idf;
						}
						if (!$link && $val !== null) { // link related items
							foreach ((array) $foreign_keys[$key] as $foreign_key) {
								if (count($foreign_keys[$key]) == 1 || end($foreign_key["source"]) == $key) {
									$link = "";
									foreach ($foreign_key["source"] as $i => $source) {
										$link .= where_link($i, $foreign_key["target"][$i], $rows[$n][$source]);
									}
									$link = ($foreign_key["db"] != "" ? preg_replace('~([?&]db=)[^&]+~', '\1' . urlencode($foreign_key["db"]), ME) : ME) . 'select=' . urlencode($foreign_key["table"]) . $link; // InnoDB supports non-UNIQUE keys
									if ($foreign_key["ns"]) {
										$link = preg_replace('~([?&]ns=)[^&]+~', '\1' . urlencode($foreign_key["ns"]), $link);
									}
									if (count($foreign_key["source"]) == 1) {
										break;
									}
								}
							}
						}
						if ($key == "COUNT(*)") { //! columns looking like functions
							$link = ME . "select=" . urlencode($TABLE);
							$i = 0;
							foreach ((array) $_GET["where"] as $v) {
								if (!array_key_exists($v["col"], $unique_array)) {
									$link .= where_link($i++, $v["col"], $v["val"], $v["op"]);
								}
							}
							foreach ($unique_array as $k => $v) {
								$link .= where_link($i++, $k, $v);
							}
						}
						
						$val = select_value($val, $link, $field, $text_length);
						$id = h("val[$unique_idf][" . bracket_escape($key) . "]");
						$value = $_POST["val"][$unique_idf][bracket_escape($key)];
						$editable = !is_array($row[$key]) && is_utf8($val) && $rows[$n][$key] == $row[$key] && !$functions[$key];
						$text = preg_match('~text|lob~', $field["type"]);
						echo "<td id='$id'";
						if (($_GET["modify"] && $editable) || $value !== null) {
							$h_value = h($value !== null ? $value : $row[$key]);
							echo ">" . ($text ? "<textarea name='$id' cols='30' rows='" . (substr_count($row[$key], "\n") + 1) . "'>$h_value</textarea>" : "<input name='$id' value='$h_value' size='$lengths[$key]'>");
						} else {
							$long = strpos($val, "<i>…</i>");
							echo " data-text='" . ($long ? 2 : ($text ? 1 : 0)) . "'"
								. ($editable ? "" : " data-warning='" . h(lang(239)) . "'")
								. ">$val</td>"
							;
						}
					}
				}

				if ($backward_keys) {
					echo "<td>";
				}
				$adminer->backwardKeysPrint($backward_keys, $rows[$n]);
				echo "</tr>\n"; // close to allow white-space: pre
			}

			if (is_ajax()) {
				exit;
			}
			echo "</table>\n";
			echo "</div>\n";
		}

		if (!is_ajax()) {
			if ($rows || $page) {
				$exact_count = true;
				if ($_GET["page"] != "last") {
					if ($limit == "" || (count($rows) < $limit && ($rows || !$page))) {
						$found_rows = ($page ? $page * $limit : 0) + count($rows);
					} elseif ($jush != "sql" || !$is_group) {
						$found_rows = ($is_group ? false : found_rows($table_status, $where));
						if ($found_rows < max(1e4, 2 * ($page + 1) * $limit)) {
							// slow with big tables
							$found_rows = reset(slow_query(count_rows($TABLE, $where, $is_group, $group)));
						} else {
							$exact_count = false;
						}
					}
				}

				$pagination = ($limit != "" && ($found_rows === false || $found_rows > $limit || $page));
				if ($pagination) {
					echo (($found_rows === false ? count($rows) + 1 : $found_rows - $page * $limit) > $limit
						? '<p><a href="' . h(remove_from_uri("page") . "&page=" . ($page + 1)) . '" class="loadmore">' . lang(240) . '</a>'
							. script("qsl('a').onclick = partial(selectLoadMore, " . (+$limit) . ", '" . lang(241) . "…');", "")
						: ''
					);
					echo "\n";
				}
			}
			
			echo "<div class='footer'><div>\n";
			if ($rows || $page) {
				if ($pagination) {
					// display first, previous 4, next 4 and last page
					$max_page = ($found_rows === false
						? $page + (count($rows) >= $limit ? 2 : 1)
						: floor(($found_rows - 1) / $limit)
					);
					echo "<fieldset>";
					if ($jush != "simpledb") {
						echo "<legend><a href='" . h(remove_from_uri("page")) . "'>" . lang(242) . "</a></legend>";
						echo script("qsl('a').onclick = function () { pageClick(this.href, +prompt('" . lang(242) . "', '" . ($page + 1) . "')); return false; };");
						echo pagination(0, $page) . ($page > 5 ? " …" : "");
						for ($i = max(1, $page - 4); $i < min($max_page, $page + 5); $i++) {
							echo pagination($i, $page);
						}
						if ($max_page > 0) {
							echo ($page + 5 < $max_page ? " …" : "");
							echo ($exact_count && $found_rows !== false
								? pagination($max_page, $page)
								: " <a href='" . h(remove_from_uri("page") . "&page=last") . "' title='~$max_page'>" . lang(243) . "</a>"
							);
						}
					} else {
						echo "<legend>" . lang(242) . "</legend>";
						echo pagination(0, $page) . ($page > 1 ? " …" : "");
						echo ($page ? pagination($page, $page) : "");
						echo ($max_page > $page ? pagination($page + 1, $page) . ($max_page > $page + 1 ? " …" : "") : "");
					}
					echo "</fieldset>\n";
				}
				
				echo "<fieldset>";
				echo "<legend>" . lang(244) . "</legend>";
				$display_rows = ($exact_count ? "" : "~ ") . $found_rows;
				echo checkbox("all", 1, 0, ($found_rows !== false ? ($exact_count ? "" : "~ ") . lang(143, $found_rows) : ""), "var checked = formChecked(this, /check/); selectCount('selected', this.checked ? '$display_rows' : checked); selectCount('selected2', this.checked || !checked ? '$display_rows' : checked);") . "\n";
				echo "</fieldset>\n";

				if ($adminer->selectCommandPrint()) {
					?>
<fieldset<?php echo ($_GET["modify"] ? '' : ' class="jsonly"'); ?>><legend><?php echo lang(236); ?></legend><div>
<input type="submit" value="<?php echo lang(14); ?>"<?php echo ($_GET["modify"] ? '' : ' title="' . lang(232) . '"'); ?>>
</div></fieldset>
<fieldset><legend><?php echo lang(120); ?> <span id="selected"></span></legend><div>
<input type="submit" name="edit" value="<?php echo lang(10); ?>">
<input type="submit" name="clone" value="<?php echo lang(228); ?>">
<input type="submit" name="delete" value="<?php echo lang(18); ?>"><?php echo confirm(); ?>
</div></fieldset>
<?php
				}

				$format = $adminer->dumpFormat();
				foreach ((array) $_GET["columns"] as $column) {
					if ($column["fun"]) {
						unset($format['sql']);
						break;
					}
				}
				if ($format) {
					print_fieldset("export", lang(62) . " <span id='selected2'></span>");
					$output = $adminer->dumpOutput();
					echo ($output ? html_select("output", $output, $adminer_import["output"]) . " " : "");
					echo html_select("format", $format, $adminer_import["format"]);
					echo " <input type='submit' name='export' value='" . lang(62) . "'>\n";
					echo "</div></fieldset>\n";
				}

				$adminer->selectEmailPrint(array_filter($email_fields, 'strlen'), $columns);
			}

			echo "</div></div>\n";

			if ($adminer->selectImportPrint()) {
				echo "<div>";
				echo "<a href='#import'>" . lang(61) . "</a>";
				echo script("qsl('a').onclick = partial(toggle, 'import');", "");
				echo "<span id='import' class='hidden'>: ";
				echo "<input type='file' name='csv_file'> ";
				echo html_select("separator", array("csv" => "CSV,", "csv;" => "CSV;", "tsv" => "TSV"), $adminer_import["format"], 1); // 1 - select
				echo " <input type='submit' name='import' value='" . lang(61) . "'>";
				echo "</span>";
				echo "</div>";
			}

			echo "<input type='hidden' name='token' value='$token'>\n";
			echo "</form>\n";
			echo (!$group && $select ? "" : script("tableCheck();"));
		}
	}
}

if (is_ajax()) {
	ob_end_clean();
	exit;
}

} elseif (isset($_GET["variables"])) {
	
$status = isset($_GET["status"]);
page_header($status ? lang(112) : lang(111));

$variables = ($status ? show_status() : show_variables());
if (!$variables) {
	echo "<p class='message'>" . lang(12) . "\n";
} else {
	echo "<table cellspacing='0'>\n";
	foreach ($variables as $key => $val) {
		echo "<tr>";
		echo "<th><code class='jush-" . $jush . ($status ? "status" : "set") . "'>" . h($key) . "</code>";
		echo "<td>" . h($val);
	}
	echo "</table>\n";
}

} elseif (isset($_GET["script"])) {
	
header("Content-Type: text/javascript; charset=utf-8");

if ($_GET["script"] == "db") {
	$sums = array("Data_length" => 0, "Index_length" => 0, "Data_free" => 0);
	foreach (table_status() as $name => $table_status) {
		json_row("Comment-$name", h($table_status["Comment"]));
		if (!is_view($table_status)) {
			foreach (array("Engine", "Collation") as $key) {
				json_row("$key-$name", h($table_status[$key]));
			}
			foreach ($sums + array("Auto_increment" => 0, "Rows" => 0) as $key => $val) {
				if ($table_status[$key] != "") {
					$val = format_number($table_status[$key]);
					json_row("$key-$name", ($key == "Rows" && $val && $table_status["Engine"] == ($sql == "pgsql" ? "table" : "InnoDB")
						? "~ $val"
						: $val
					));
					if (isset($sums[$key])) {
						// ignore innodb_file_per_table because it is not active for tables created before it was enabled
						$sums[$key] += ($table_status["Engine"] != "InnoDB" || $key != "Data_free" ? $table_status[$key] : 0);
					}
				} elseif (array_key_exists($key, $table_status)) {
					json_row("$key-$name");
				}
			}
		}
	}
	foreach ($sums as $key => $val) {
		json_row("sum-$key", format_number($val));
	}
	json_row("");

} elseif ($_GET["script"] == "kill") {
	$connection->query("KILL " . number($_POST["kill"]));

} else { // connect
	foreach (count_tables($adminer->databases()) as $db => $val) {
		json_row("tables-$db", $val);
		json_row("size-$db", db_size($db));
	}
	json_row("");
}

exit; // don't print footer

} else {
	
$tables_views = array_merge((array) $_POST["tables"], (array) $_POST["views"]);

if ($tables_views && !$error && !$_POST["search"]) {
	$result = true;
	$message = "";
	if ($jush == "sql" && $_POST["tables"] && count($_POST["tables"]) > 1 && ($_POST["drop"] || $_POST["truncate"] || $_POST["copy"])) {
		queries("SET foreign_key_checks = 0"); // allows to truncate or drop several tables at once
	}

	if ($_POST["truncate"]) {
		if ($_POST["tables"]) {
			$result = truncate_tables($_POST["tables"]);
		}
		$message = lang(245);
	} elseif ($_POST["move"]) {
		$result = move_tables((array) $_POST["tables"], (array) $_POST["views"], $_POST["target"]);
		$message = lang(246);
	} elseif ($_POST["copy"]) {
		$result = copy_tables((array) $_POST["tables"], (array) $_POST["views"], $_POST["target"]);
		$message = lang(247);
	} elseif ($_POST["drop"]) {
		if ($_POST["views"]) {
			$result = drop_views($_POST["views"]);
		}
		if ($result && $_POST["tables"]) {
			$result = drop_tables($_POST["tables"]);
		}
		$message = lang(248);
	} elseif ($jush != "sql") {
		$result = ($jush == "sqlite"
			? queries("VACUUM")
			: apply_queries("VACUUM" . ($_POST["optimize"] ? "" : " ANALYZE"), $_POST["tables"])
		);
		$message = lang(249);
	} elseif (!$_POST["tables"]) {
		$message = lang(9);
	} elseif ($result = queries(($_POST["optimize"] ? "OPTIMIZE" : ($_POST["check"] ? "CHECK" : ($_POST["repair"] ? "REPAIR" : "ANALYZE"))) . " TABLE " . implode(", ", array_map('idf_escape', $_POST["tables"])))) {
		while ($row = $result->fetch_assoc()) {
			$message .= "<b>" . h($row["Table"]) . "</b>: " . h($row["Msg_text"]) . "<br>";
		}
	}

	queries_redirect(substr(ME, 0, -1), $message, $result);
}

page_header(($_GET["ns"] == "" ? lang(26) . ": " . h(DB) : lang(189) . ": " . h($_GET["ns"])), $error, true);

if ($adminer->homepage()) {
	if ($_GET["ns"] !== "") {
		echo "<h3 id='tables-views'>" . lang(250) . "</h3>\n";
		$tables_list = tables_list();
		if (!$tables_list) {
			echo "<p class='message'>" . lang(9) . "\n";
		} else {
			echo "<form action='' method='post'>\n";
			if (support("table")) {
				echo "<fieldset><legend>" . lang(251) . " <span id='selected2'></span></legend><div>";
				echo "<input type='search' name='query' value='" . h($_POST["query"]) . "'>";
				echo script("qsl('input').onkeydown = partialArg(bodyKeydown, 'search');", "");
				echo " <input type='submit' name='search' value='" . lang(45) . "'>\n";
				echo "</div></fieldset>\n";
				if ($_POST["search"] && $_POST["query"] != "") {
					$_GET["where"][0]["op"] = "LIKE %%";
					search_tables();
				}
			}
			echo "<div class='scrollable'>\n";
			echo "<table cellspacing='0' class='nowrap checkable'>\n";
			echo script("mixin(qsl('table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});");
			echo '<thead><tr class="wrap">';
			echo '<td><input id="check-all" type="checkbox" class="jsonly">' . script("qs('#check-all').onclick = partial(formCheck, /^(tables|views)\[/);", "");
			echo '<th>' . lang(124);
			echo '<td>' . lang(252) . doc_link(array('sql' => 'storage-engines.html'));
			echo '<td>' . lang(116) . doc_link(array('sql' => 'charset-charsets.html', 'mariadb' => 'supported-character-sets-and-collations/'));
			echo '<td>' . lang(253) . doc_link(array('sql' => 'show-table-status.html',  ));
			echo '<td>' . lang(254) . doc_link(array('sql' => 'show-table-status.html', ));
			echo '<td>' . lang(255) . doc_link(array('sql' => 'show-table-status.html'));
			echo '<td>' . lang(40) . doc_link(array('sql' => 'example-auto-increment.html', 'mariadb' => 'auto_increment/'));
			echo '<td>' . lang(256) . doc_link(array('sql' => 'show-table-status.html',  ));
			echo (support("comment") ? '<td>' . lang(39) . doc_link(array('sql' => 'show-table-status.html', )) : '');
			echo "</thead>\n";

			$tables = 0;
			foreach ($tables_list as $name => $type) {
				$view = ($type !== null && !preg_match('~table|sequence~i', $type));
				$id = h("Table-" . $name);
				echo '<tr' . odd() . '><td>' . checkbox(($view ? "views[]" : "tables[]"), $name, in_array($name, $tables_views, true), "", "", "", $id);
				echo '<th>' . (support("table") || support("indexes") ? "<a href='" . h(ME) . "table=" . urlencode($name) . "' title='" . lang(31) . "' id='$id'>" . h($name) . '</a>' : h($name));
				if ($view) {
					echo '<td colspan="6"><a href="' . h(ME) . "view=" . urlencode($name) . '" title="' . lang(32) . '">' . (preg_match('~materialized~i', $type) ? lang(122) : lang(123)) . '</a>';
					echo '<td align="right"><a href="' . h(ME) . "select=" . urlencode($name) . '" title="' . lang(30) . '">?</a>';
				} else {
					foreach (array(
						"Engine" => array(),
						"Collation" => array(),
						"Data_length" => array("create", lang(33)),
						"Index_length" => array("indexes", lang(126)),
						"Data_free" => array("edit", lang(34)),
						"Auto_increment" => array("auto_increment=1&create", lang(33)),
						"Rows" => array("select", lang(30)),
					) as $key => $link) {
						$id = " id='$key-" . h($name) . "'";
						echo ($link ? "<td align='right'>" . (support("table") || $key == "Rows" || (support("indexes") && $key != "Data_length")
							? "<a href='" . h(ME . "$link[0]=") . urlencode($name) . "'$id title='$link[1]'>?</a>"
							: "<span$id>?</span>"
						) : "<td id='$key-" . h($name) . "'>");
					}
					$tables++;
				}
				echo (support("comment") ? "<td id='Comment-" . h($name) . "'>" : "");
			}

			echo "<tr><td><th>" . lang(229, count($tables_list));
			echo "<td>" . h($jush == "sql" ? $connection->result("SELECT @@default_storage_engine") : "");
			echo "<td>" . h(db_collation(DB, collations()));
			foreach (array("Data_length", "Index_length", "Data_free") as $key) {
				echo "<td align='right' id='sum-$key'>";
			}

			echo "</table>\n";
			echo "</div>\n";
			if (!information_schema(DB)) {
				echo "<div class='footer'><div>\n";
				$vacuum = "<input type='submit' value='" . lang(257) . "'> " . on_help("'VACUUM'");
				$optimize = "<input type='submit' name='optimize' value='" . lang(258) . "'> " . on_help($jush == "sql" ? "'OPTIMIZE TABLE'" : "'VACUUM OPTIMIZE'");
				echo "<fieldset><legend>" . lang(120) . " <span id='selected'></span></legend><div>"
				. ($jush == "sqlite" ? $vacuum
				: ($jush == "pgsql" ? $vacuum . $optimize
				: ($jush == "sql" ? "<input type='submit' value='" . lang(259) . "'> " . on_help("'ANALYZE TABLE'") . $optimize
					. "<input type='submit' name='check' value='" . lang(260) . "'> " . on_help("'CHECK TABLE'")
					. "<input type='submit' name='repair' value='" . lang(261) . "'> " . on_help("'REPAIR TABLE'")
				: "")))
				. "<input type='submit' name='truncate' value='" . lang(262) . "'> " . on_help($jush == "sqlite" ? "'DELETE'" : "'TRUNCATE" . ($jush == "pgsql" ? "'" : " TABLE'")) . confirm()
				. "<input type='submit' name='drop' value='" . lang(121) . "'>" . on_help("'DROP TABLE'") . confirm() . "\n";
				$databases = (support("scheme") ? $adminer->schemas() : $adminer->databases());
				if (count($databases) != 1 && $jush != "sqlite") {
					$db = (isset($_POST["target"]) ? $_POST["target"] : (support("scheme") ? $_GET["ns"] : DB));
					echo "<p>" . lang(263) . ": ";
					echo ($databases ? html_select("target", $databases, $db) : '<input name="target" value="' . h($db) . '" autocapitalize="off">');
					echo " <input type='submit' name='move' value='" . lang(264) . "'>";
					echo (support("copy") ? " <input type='submit' name='copy' value='" . lang(265) . "'> " . checkbox("overwrite", 1, $_POST["overwrite"], lang(266)) : "");
					echo "\n";
				}
				echo "<input type='hidden' name='all' value=''>"; // used by trCheck()
				echo script("qsl('input').onclick = function () { selectCount('selected', formChecked(this, /^(tables|views)\[/));" . (support("table") ? " selectCount('selected2', formChecked(this, /^tables\[/) || $tables);" : "") . " }");
				echo "<input type='hidden' name='token' value='$token'>\n";
				echo "</div></fieldset>\n";
				echo "</div></div>\n";
			}
			echo "</form>\n";
			echo script("tableCheck();");
		}

		echo '<p class="links"><a href="' . h(ME) . 'create=">' . lang(63) . "</a>\n";
		echo (support("view") ? '<a href="' . h(ME) . 'view=">' . lang(195) . "</a>\n" : "");

		if (support("routine")) {
			echo "<h3 id='routines'>" . lang(136) . "</h3>\n";
			$routines = routines();
			if ($routines) {
				echo "<table cellspacing='0'>\n";
				echo '<thead><tr><th>' . lang(176) . '<td>' . lang(38) . '<td>' . lang(212) . "<td></thead>\n";
				odd('');
				foreach ($routines as $row) {
					$name = ($row["SPECIFIC_NAME"] == $row["ROUTINE_NAME"] ? "" : "&name=" . urlencode($row["ROUTINE_NAME"])); // not computed on the pages to be able to print the header first
					echo '<tr' . odd() . '>';
					echo '<th><a href="' . h(ME . ($row["ROUTINE_TYPE"] != "PROCEDURE" ? 'callf=' : 'call=') . urlencode($row["SPECIFIC_NAME"]) . $name) . '">' . h($row["ROUTINE_NAME"]) . '</a>';
					echo '<td>' . h($row["ROUTINE_TYPE"]);
					echo '<td>' . h($row["DTD_IDENTIFIER"]);
					echo '<td><a href="' . h(ME . ($row["ROUTINE_TYPE"] != "PROCEDURE" ? 'function=' : 'procedure=') . urlencode($row["SPECIFIC_NAME"]) . $name) . '">' . lang(129) . "</a>";
				}
				echo "</table>\n";
			}
			echo '<p class="links">'
				. (support("procedure") ? '<a href="' . h(ME) . 'procedure=">' . lang(211) . '</a>' : '')
				. '<a href="' . h(ME) . 'function=">' . lang(210) . "</a>\n"
			;
		}





		if (support("event")) {
			echo "<h3 id='events'>" . lang(137) . "</h3>\n";
			$rows = get_rows("SHOW EVENTS");
			if ($rows) {
				echo "<table cellspacing='0'>\n";
				echo "<thead><tr><th>" . lang(176) . "<td>" . lang(267) . "<td>" . lang(201) . "<td>" . lang(202) . "<td></thead>\n";
				foreach ($rows as $row) {
					echo "<tr>";
					echo "<th>" . h($row["Name"]);
					echo "<td>" . ($row["Execute at"] ? lang(268) . "<td>" . $row["Execute at"] : lang(203) . " " . $row["Interval value"] . " " . $row["Interval field"] . "<td>$row[Starts]");
					echo "<td>$row[Ends]";
					echo '<td><a href="' . h(ME) . 'event=' . urlencode($row["Name"]) . '">' . lang(129) . '</a>';
				}
				echo "</table>\n";
				$event_scheduler = $connection->result("SELECT @@event_scheduler");
				if ($event_scheduler && $event_scheduler != "ON") {
					echo "<p class='error'><code class='jush-sqlset'>event_scheduler</code>: " . h($event_scheduler) . "\n";
				}
			}
			echo '<p class="links"><a href="' . h(ME) . 'event=">' . lang(200) . "</a>\n";
		}

		if ($tables_list) {
			echo script("ajaxSetHtml('" . js_escape(ME) . "script=db');");
		}
	}
}

}

// each page calls its own page_header(), if the footer should not be called then the page exits
page_footer();
