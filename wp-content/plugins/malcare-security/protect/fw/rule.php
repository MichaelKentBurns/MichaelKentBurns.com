<?php
if (!defined('ABSPATH') && !defined('MCDATAPATH')) exit;

if (!class_exists('MCProtectFWRule_V542')) :
class MCProtectFWRule_V542 {
	public $id;
	public $logic;
	public $actions;
	public $execute_on;
	public $min_rule_engine_ver;
	public $max_rule_engine_ver;
	public $config = array();
	public $opts = array();

	const EXE_ON_BOOT = 1;
	const EXE_ON_PRE_UPDATE_OPTION = 2;
	const EXE_ON_PRE_DELETE_POST = 3;
	const EXE_ON_WP_INSERT_POST_EMPTY_CONTENT = 4;
	const EXE_ON_INSERT_USER_META = 5;
	const EXE_ON_DELETE_OPTION = 6;
	const EXE_ON_DELETE_USER = 7;
	const EXE_ON_PASSWORD_RESET = 8;
	const EXE_ON_SEND_AUTH_COOKIES = 9;
	const EXE_ON_SET_AUTH_COOKIE = 10;
	const EXE_ON_INIT = 11;
	const EXE_ON_USER_REGISTER = 12;
	const EXE_ON_ADD_USER_META = 13;
	const EXE_ON_UPDATE_USER_METADATA = 14;
	const EXE_ON_UPDATE_USER_META = 15;
	const EXE_ON_ADD_OPTION = 16;
	const EXE_ON_WP_PRE_INSERT_USER_DATA = 17;

	const SQLIREGEX = '/(?:[^\\w<]|\\/\\*\\![0-9]*|^)(?:
		@@HOSTNAME|
		ALTER|ANALYZE|ASENSITIVE|
		BEFORE|BENCHMARK|BETWEEN|BIGINT|BINARY|BLOB|
		CALL|CASE|CHANGE|CHAR|CHARACTER|CHAR_LENGTH|COLLATE|COLUMN|CONCAT|CONDITION|CONSTRAINT|CONTINUE|CONVERT|CREATE|CROSS|CURRENT_DATE|CURRENT_TIME|CURRENT_TIMESTAMP|CURRENT_USER|CURSOR|
		DATABASE|DATABASES|DAY_HOUR|DAY_MICROSECOND|DAY_MINUTE|DAY_SECOND|DECIMAL|DECLARE|DEFAULT|DELAYED|DELETE|DESCRIBE|DETERMINISTIC|DISTINCT|DISTINCTROW|DOUBLE|DROP|DUAL|DUMPFILE|
		EACH|ELSE|ELSEIF|ELT|ENCLOSED|ESCAPED|EXISTS|EXIT|EXPLAIN|EXTRACTVALUE|
		FETCH|FLOAT|FLOAT4|FLOAT8|FORCE|FOREIGN|FROM|FULLTEXT|
		GRANT|GROUP|HAVING|HEX|HIGH_PRIORITY|HOUR_MICROSECOND|HOUR_MINUTE|HOUR_SECOND|
		IFNULL|IGNORE|INDEX|INFILE|INNER|INOUT|INSENSITIVE|INSERT|INTERVAL|ISNULL|ITERATE|
		JOIN|KILL|LEADING|LEAVE|LIMIT|LINEAR|LINES|LOAD|LOAD_FILE|LOCALTIME|LOCALTIMESTAMP|LOCK|LONG|LONGBLOB|LONGTEXT|LOOP|LOW_PRIORITY|
		MASTER_SSL_VERIFY_SERVER_CERT|MATCH|MAXVALUE|MEDIUMBLOB|MEDIUMINT|MEDIUMTEXT|MID|MIDDLEINT|MINUTE_MICROSECOND|MINUTE_SECOND|MODIFIES|
		NATURAL|NO_WRITE_TO_BINLOG|NULL|NUMERIC|OPTION|ORD|ORDER|OUTER|OUTFILE|
		PRECISION|PRIMARY|PRIVILEGES|PROCEDURE|PROCESSLIST|PURGE|
		RANGE|READ_WRITE|REGEXP|RELEASE|REPEAT|REQUIRE|RESIGNAL|RESTRICT|RETURN|REVOKE|RLIKE|ROLLBACK|
		SCHEMA|SCHEMAS|SECOND_MICROSECOND|SELECT|SENSITIVE|SEPARATOR|SHOW|SIGNAL|SLEEP|SMALLINT|SPATIAL|SPECIFIC|SQLEXCEPTION|SQLSTATE|SQLWARNING|SQL_BIG_RESULT|SQL_CALC_FOUND_ROWS|SQL_SMALL_RESULT|STARTING|STRAIGHT_JOIN|SUBSTR|
		TABLE|TERMINATED|TINYBLOB|TINYINT|TINYTEXT|TRAILING|TRANSACTION|TRIGGER|
		UNDO|UNHEX|UNION|UNLOCK|UNSIGNED|UPDATE|UPDATEXML|USAGE|USING|UTC_DATE|UTC_TIME|UTC_TIMESTAMP|
		VALUES|VARBINARY|VARCHAR|VARCHARACTER|VARYING|WHEN|WHERE|WHILE|WRITE|YEAR_MONTH|ZEROFILL)(?=[^\\w]|$)/ix';
	const XSSREGEX = '/(?:
		#tags
		(?:\\<|\\+ADw\\-|\\xC2\\xBC)(script|iframe|svg|object|embed|applet|link|style|meta|\\/\\/|\\?xml\\-stylesheet)(?:[^\\w]|\\xC2\\xBE)|
		#protocols
		(?:^|[^\\w])(?:(?:\\s*(?:&\\#(?:x0*6a|0*106)|j)\\s*(?:&\\#(?:x0*61|0*97)|a)\\s*(?:&\\#(?:x0*76|0*118)|v)\\s*(?:&\\#(?:x0*61|0*97)|a)|\\s*(?:&\\#(?:x0*76|0*118)|v)\\s*(?:&\\#(?:x0*62|0*98)|b)|\\s*(?:&\\#(?:x0*65|0*101)|e)\\s*(?:&\\#(?:x0*63|0*99)|c)\\s*(?:&\\#(?:x0*6d|0*109)|m)\\s*(?:&\\#(?:x0*61|0*97)|a)|\\s*(?:&\\#(?:x0*6c|0*108)|l)\\s*(?:&\\#(?:x0*69|0*105)|i)\\s*(?:&\\#(?:x0*76|0*118)|v)\\s*(?:&\\#(?:x0*65|0*101)|e))\\s*(?:&\\#(?:x0*73|0*115)|s)\\s*(?:&\\#(?:x0*63|0*99)|c)\\s*(?:&\\#(?:x0*72|0*114)|r)\\s*(?:&\\#(?:x0*69|0*105)|i)\\s*(?:&\\#(?:x0*70|0*112)|p)\\s*(?:&\\#(?:x0*74|0*116)|t)|\\s*(?:&\\#(?:x0*6d|0*109)|m)\\s*(?:&\\#(?:x0*68|0*104)|h)\\s*(?:&\\#(?:x0*74|0*116)|t)\\s*(?:&\\#(?:x0*6d|0*109)|m)\\s*(?:&\\#(?:x0*6c|0*108)|l)|\\s*(?:&\\#(?:x0*6d|0*109)|m)\\s*(?:&\\#(?:x0*6f|0*111)|o)\\s*(?:&\\#(?:x0*63|0*99)|c)\\s*(?:&\\#(?:x0*68|0*104)|h)\\s*(?:&\\#(?:x0*61|0*97)|a)|\\s*(?:&\\#(?:x0*64|0*100)|d)\\s*(?:&\\#(?:x0*61|0*97)|a)\\s*(?:&\\#(?:x0*74|0*116)|t)\\s*(?:&\\#(?:x0*61|0*97)|a)(?!(?:&\\#(?:x0*3a|0*58)|\\:)(?:&\\#(?:x0*69|0*105)|i)(?:&\\#(?:x0*6d|0*109)|m)(?:&\\#(?:x0*61|0*97)|a)(?:&\\#(?:x0*67|0*103)|g)(?:&\\#(?:x0*65|0*101)|e)(?:&\\#(?:x0*2f|0*47)|\\/)(?:(?:&\\#(?:x0*70|0*112)|p)(?:&\\#(?:x0*6e|0*110)|n)(?:&\\#(?:x0*67|0*103)|g)|(?:&\\#(?:x0*62|0*98)|b)(?:&\\#(?:x0*6d|0*109)|m)(?:&\\#(?:x0*70|0*112)|p)|(?:&\\#(?:x0*67|0*103)|g)(?:&\\#(?:x0*69|0*105)|i)(?:&\\#(?:x0*66|0*102)|f)|(?:&\\#(?:x0*70|0*112)|p)?(?:&\\#(?:x0*6a|0*106)|j)(?:&\\#(?:x0*70|0*112)|p)(?:&\\#(?:x0*65|0*101)|e)(?:&\\#(?:x0*67|0*103)|g)|(?:&\\#(?:x0*74|0*116)|t)(?:&\\#(?:x0*69|0*105)|i)(?:&\\#(?:x0*66|0*102)|f)(?:&\\#(?:x0*66|0*102)|f)|(?:&\\#(?:x0*73|0*115)|s)(?:&\\#(?:x0*76|0*118)|v)(?:&\\#(?:x0*67|0*103)|g)(?:&\\#(?:x0*2b|0*43)|\\+)(?:&\\#(?:x0*78|0*120)|x)(?:&\\#(?:x0*6d|0*109)|m)(?:&\\#(?:x0*6c|0*108)|l))(?:(?:&\\#(?:x0*3b|0*59)|;)(?:&\\#(?:x0*63|0*99)|c)(?:&\\#(?:x0*68|0*104)|h)(?:&\\#(?:x0*61|0*97)|a)(?:&\\#(?:x0*72|0*114)|r)(?:&\\#(?:x0*73|0*115)|s)(?:&\\#(?:x0*65|0*101)|e)(?:&\\#(?:x0*74|0*116)|t)(?:&\\#(?:x0*3d|0*61)|=)[\\-a-z0-9]+)?(?:(?:&\\#(?:x0*3b|0*59)|;)(?:&\\#(?:x0*62|0*98)|b)(?:&\\#(?:x0*61|0*97)|a)(?:&\\#(?:x0*73|0*115)|s)(?:&\\#(?:x0*65|0*101)|e)(?:&\\#(?:x0*36|0*54)|6)(?:&\\#(?:x0*34|0*52)|4))?(?:&\\#(?:x0*2c|0*44)|,)))\\s*(?:&\\#(?:x0*3a|0*58)|&colon|\\:)|
		#css expression
		(?:^|[^\\w])(?:(?:\\\\0*65|\\\\0*45|e)(?:\\/\\*.*?\\*\\/)*(?:\\\\0*78|\\\\0*58|x)(?:\\/\\*.*?\\*\\/)*(?:\\\\0*70|\\\\0*50|p)(?:\\/\\*.*?\\*\\/)*(?:\\\\0*72|\\\\0*52|r)(?:\\/\\*.*?\\*\\/)*(?:\\\\0*65|\\\\0*45|e)(?:\\/\\*.*?\\*\\/)*(?:\\\\0*73|\\\\0*53|s)(?:\\/\\*.*?\\*\\/)*(?:\\\\0*73|\\\\0*53|s)(?:\\/\\*.*?\\*\\/)*(?:\\\\0*69|\\\\0*49|i)(?:\\/\\*.*?\\*\\/)*(?:\\\\0*6f|\\\\0*4f|o)(?:\\/\\*.*?\\*\\/)*(?:\\\\0*6e|\\\\0*4e|n))[^\\w]*?(?:\\\\0*28|\\()|
		#css properties
		(?:^|[^\\w])(?:(?:(?:\\\\0*62|\\\\0*42|b)(?:\\/\\*.*?\\*\\/)*(?:\\\\0*65|\\\\0*45|e)(?:\\/\\*.*?\\*\\/)*(?:\\\\0*68|\\\\0*48|h)(?:\\/\\*.*?\\*\\/)*(?:\\\\0*61|\\\\0*41|a)(?:\\/\\*.*?\\*\\/)*(?:\\\\0*76|\\\\0*56|v)(?:\\/\\*.*?\\*\\/)*(?:\\\\0*69|\\\\0*49|i)(?:\\/\\*.*?\\*\\/)*(?:\\\\0*6f|\\\\0*4f|o)(?:\\/\\*.*?\\*\\/)*(?:\\\\0*72|\\\\0*52|r)(?:\\/\\*.*?\\*\\/)*)|(?:(?:\\\\0*2d|\\\\0*2d|-)(?:\\/\\*.*?\\*\\/)*(?:\\\\0*6d|\\\\0*4d|m)(?:\\/\\*.*?\\*\\/)*(?:\\\\0*6f|\\\\0*4f|o)(?:\\/\\*.*?\\*\\/)*(?:\\\\0*7a|\\\\0*5a|z)(?:\\/\\*.*?\\*\\/)*(?:\\\\0*2d|\\\\0*2d|-)(?:\\/\\*.*?\\*\\/)*(?:\\\\0*62|\\\\0*42|b)(?:\\/\\*.*?\\*\\/)*(?:\\\\0*69|\\\\0*49|i)(?:\\/\\*.*?\\*\\/)*(?:\\\\0*6e|\\\\0*4e|n)(?:\\/\\*.*?\\*\\/)*(?:\\\\0*64|\\\\0*44|d)(?:\\/\\*.*?\\*\\/)*(?:\\\\0*69|\\\\0*49|i)(?:\\/\\*.*?\\*\\/)*(?:\\\\0*6e|\\\\0*4e|n)(?:\\/\\*.*?\\*\\/)*(?:\\\\0*67|\\\\0*47|g)(?:\\/\\*.*?\\*\\/)*))[^\\w]*(?:\\\\0*3a|\\\\0*3a|:)[^\\w]*(?:\\\\0*75|\\\\0*55|u)(?:\\\\0*72|\\\\0*52|r)(?:\\\\0*6c|\\\\0*4c|l)|
		#properties
		(?:^|[^\\w])(?:on(?:abort|activate|afterprint|afterupdate|autocomplete|autocompleteerror|beforeactivate|beforecopy|beforecut|beforedeactivate|beforeeditfocus|beforepaste|beforeprint|beforeunload|beforeupdate|blur|bounce|cancel|canplay|canplaythrough|cellchange|change|click|close|contextmenu|controlselect|copy|cuechange|cut|dataavailable|datasetchanged|datasetcomplete|dblclick|deactivate|drag|dragend|dragenter|dragleave|dragover|dragstart|drop|durationchange|emptied|encrypted|ended|error|errorupdate|filterchange|finish|focus|focusin|focusout|formchange|forminput|hashchange|help|input|invalid|keydown|keypress|keyup|languagechange|layoutcomplete|load|loadeddata|loadedmetadata|loadstart|losecapture|message|mousedown|mouseenter|mouseleave|mousemove|mouseout|mouseover|mouseup|mousewheel|move|moveend|movestart|mozfullscreenchange|mozfullscreenerror|mozpointerlockchange|mozpointerlockerror|offline|online|page|pagehide|pageshow|paste|pause|play|playing|popstate|progress|propertychange|ratechange|readystatechange|reset|resize|resizeend|resizestart|rowenter|rowexit|rowsdelete|rowsinserted|scroll|search|seeked|seeking|select|selectstart|show|stalled|start|storage|submit|suspend|timer|timeupdate|toggle|unload|volumechange|waiting|webkitfullscreenchange|webkitfullscreenerror|wheel)|formaction|data\\-bind|ev:event)[^\\w]
		)/ix';

	public function __construct($attributes) {
		$this->id = $attributes['id'];
		$this->logic = $attributes['rule_logic'];
		$this->actions = $attributes['actions'];
		$this->execute_on = $attributes['execute_on'];
		$this->min_rule_engine_ver = $attributes['min_rule_engine_ver'];

		if (array_key_exists('max_rule_engine_ver', $attributes)) {
			$this->max_rule_engine_ver = $attributes['max_rule_engine_ver'];
		}

		if (array_key_exists('config', $attributes) && is_array($attributes['config'])) {
			$this->config = $attributes['config'];
		}

		if (array_key_exists('opts', $attributes) && is_array($attributes['opts'])) {
			$this->opts = $attributes['opts'];
		}
	}

	public static function init($attributes) {
		if (!is_array($attributes)) {
			return false;
		}

		if (!array_key_exists('min_rule_engine_ver', $attributes) || !is_float($attributes['min_rule_engine_ver']) ||
				$attributes['min_rule_engine_ver'] > MCProtectFWRuleEngine_V542::VERSION) {

			return false;
		}

		if (array_key_exists('max_rule_engine_ver', $attributes) && (!is_float($attributes['max_rule_engine_ver']) ||
				$attributes['max_rule_engine_ver'] < MCProtectFWRuleEngine_V542::VERSION)) {

			return false;
		}

		if (!array_key_exists('id', $attributes) || (is_int($attributes['id'])  && $attributes['id'] <= 0)) {
			return false;
		}

		if (!array_key_exists('rule_logic', $attributes) || !is_array($attributes['rule_logic'])) {
			return false;
		}

		if (!array_key_exists('actions', $attributes) || !is_array($attributes['actions'])) {
			return false;
		}

		if (!array_key_exists('execute_on', $attributes)) {
			if (array_key_exists('level', $attributes) && $attributes['level'] === 1) {
				$attributes['execute_on'] = MCProtectFWRule_V542::EXE_ON_BOOT;
			} else {
				return false;
			}
		}

		return new MCProtectFWRule_V542($attributes);
	}

	public function isExeOnBoot() {
		return ($this->execute_on === MCProtectFWRule_V542::EXE_ON_BOOT);
	}

	public function isExeOnPreUpdateOption() {
		return ($this->execute_on === MCProtectFWRule_V542::EXE_ON_PRE_UPDATE_OPTION);
	}

	public function isExeOnPreDeletePost() {
		return ($this->execute_on === MCProtectFWRule_V542::EXE_ON_PRE_DELETE_POST);
	}

	public function isExeOnWPInsertPostEmptyContent() {
		return ($this->execute_on === MCProtectFWRule_V542::EXE_ON_WP_INSERT_POST_EMPTY_CONTENT);
	}

	public function isExeOnInsertUserMeta() {
		return ($this->execute_on === MCProtectFWRule_V542::EXE_ON_INSERT_USER_META);
	}

	public function isExeOnDeleteOption() {
		return ($this->execute_on === MCProtectFWRule_V542::EXE_ON_DELETE_OPTION);
	}

	public function isExeOnDeleteUser() {
		return ($this->execute_on === MCProtectFWRule_V542::EXE_ON_DELETE_USER);
	}

	public function isExeOnPasswordReset() {
		return ($this->execute_on === MCProtectFWRule_V542::EXE_ON_PASSWORD_RESET);
	}

	public function isExeOnSendAuthCookies() {
		return ($this->execute_on === MCProtectFWRule_V542::EXE_ON_SEND_AUTH_COOKIES);
	}

	public function isExeOnSetAuthCookie() {
		return ($this->execute_on === MCProtectFWRule_V542::EXE_ON_SET_AUTH_COOKIE);
	}

	public function isExeOnInit() {
		return ($this->execute_on === MCProtectFWRule_V542::EXE_ON_INIT);
	}

	public function isExeOnUserRegister() {
		return ($this->execute_on === MCProtectFWRule_V542::EXE_ON_USER_REGISTER);
	}

	public function isExeOnAddUserMeta() {
		return ($this->execute_on === MCProtectFWRule_V542::EXE_ON_ADD_USER_META);
	}

	public function isExeOnUpdateUserMetadata() {
		return ($this->execute_on === MCProtectFWRule_V542::EXE_ON_UPDATE_USER_METADATA);
	}

	public function isExeOnUpdateUserMeta() {
		return ($this->execute_on === MCProtectFWRule_V542::EXE_ON_UPDATE_USER_META);
	}

	public function isExeOnAddOption() {
		return ($this->execute_on === MCProtectFWRule_V542::EXE_ON_ADD_OPTION);
	}

	public function isExeOnWpPreInsertUserData() {
		return ($this->execute_on === MCProtectFWRule_V542::EXE_ON_WP_PRE_INSERT_USER_DATA);
	}
}
endif;