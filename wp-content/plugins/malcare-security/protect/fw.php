<?php
if (!defined('ABSPATH') && !defined('MCDATAPATH')) exit;

if (!class_exists('MCProtectFW_V542')) :
require_once dirname( __FILE__ ) . '/fw/rule/errors.php';
require_once dirname( __FILE__ ) . '/fw/rule/engine.php';
require_once dirname( __FILE__ ) . '/fw/rule.php';

class MCProtectFW_V542 {
	private $brand_name;
	private $protect_mode;
	private $request;
	private $ipstore;
	private $logger;

	private $is_shutdown_cb_set  = false;
	private $is_rule_initialized = false;
	private $is_wpf_rule_initialized = false;
	private $is_ip_cookie_set    = false;
	private $is_request_profiled = false;
	private $is_on_boot_rules_executed = false;
	private $is_ip_checked_for_blacklisted = false;
	private $has_valid_bypass_cookie;

	private $mode = MCProtectFW_V542::MODE_DISABLED;
	private $ip_cookie_mode = MCProtectFW_V542::IP_COOKIE_MODE_DISABLED;
	private $admin_cookie_mode = MCProtectFW_V542::ADMIN_COOKIE_MODE_DISABLED;
	private $bypass_level = MCProtectFW_V542::WP_USER_ROLE_LEVEL_CONTRIBUTOR;
	private $wpf_rule_init_mode = MCProtectFW_V542::WPF_RULE_INIT_MODE_WP;
	private $custom_roles = array();
	private $cookie_key = "";
	private $cookie_path = "";
	private $cookie_domain = "";
	private $can_set_cache_prevention_cookie = false;
	private $rules_mode = MCProtectFW_V542::RULES_MODE_DISABLED;
	private $is_geo_blocking = false;
	private $is_wp_user_cookie_enabled = false;
	private $log_config = array();
	private $request_profiling_mode = MCProtectFW_V542::REQ_PROFILING_MODE_DISABLED;
	private $logging_mode = MCProtectFW_V542::LOGGING_MODE_VISITOR;
	private $skip_log_config = array();
	private $skip_log_cookies = array();
	private $skip_log_headers = array();
	private $skip_log_post_params = array();
	private $wp_user_caps_to_consider = array();

	private $request_profiled_data = array();
	private $rules = array();
	private $wpf_rules = array();
	private $rule_log = array();
	private $matched_rules = array();
	private $break_rule_matching = false;

	private static $instance = null;

	const MODE_DISABLED = 1;
	const MODE_AUDIT    = 2;
	const MODE_PROTECT  = 3;

	const RULES_MODE_DISABLED = 1;
	const RULES_MODE_AUDIT    = 2;
	const RULES_MODE_PROTECT  = 3;

	const REQ_PROFILING_MODE_DISABLED = 1;
	const REQ_PROFILING_MODE_NORMAL   = 2;
	const REQ_PROFILING_MODE_DEBUG    = 3;

	const IP_COOKIE_MODE_ENABLED  = 1;
	const IP_COOKIE_MODE_DISABLED = 2;

	const WPF_RULE_INIT_MODE_PREPEND = 1;
	const WPF_RULE_INIT_MODE_WP      = 2;

	const ADMIN_COOKIE_MODE_ENABLED  = 1;
	const ADMIN_COOKIE_MODE_DISABLED = 2;

	const WP_USER_ROLE_LEVEL_UNKNOWN     = 0;
	const WP_USER_ROLE_LEVEL_SUBSCRIBER  = 1;
	const WP_USER_ROLE_LEVEL_CONTRIBUTOR = 2;
	const WP_USER_ROLE_LEVEL_AUTHOR      = 3;
	const WP_USER_ROLE_LEVEL_EDITOR      = 4;
	const WP_USER_ROLE_LEVEL_ADMIN       = 5;
	const WP_USER_ROLE_LEVEL_CUSTOM      = 6;

	#XNOTE: Need clarity.
	const WS_CONF_MODE_APACHEMODPHP = 1;
	const WS_CONF_MODE_APACHESUPHP  = 2;
	const WS_CONF_MODE_CGI_FASTCGI  = 3;
	const WS_CONF_MODE_NGINX        = 4;
	const WS_CONF_MODE_LITESPEED    = 5;
	const WS_CONF_MODE_IIS          = 6;

	const LOGGING_MODE_VISITOR  = 1;
	const LOGGING_MODE_COMPLETE = 2;
	const LOGGING_MODE_DISABLED = 3;

	const DEFAULT_WP_USER_ROLE_LEVELS = array(
		'administrator' => MCProtectFW_V542::WP_USER_ROLE_LEVEL_ADMIN,
		'editor'        => MCProtectFW_V542::WP_USER_ROLE_LEVEL_EDITOR,
		'author'        => MCProtectFW_V542::WP_USER_ROLE_LEVEL_AUTHOR,
		'contributor'   => MCProtectFW_V542::WP_USER_ROLE_LEVEL_CONTRIBUTOR,
		'subscriber'    => MCProtectFW_V542::WP_USER_ROLE_LEVEL_SUBSCRIBER
	);

	const EXTRA_WP_USER_ROLE_LEVELS = array(
		'custom'        => MCProtectFW_V542::WP_USER_ROLE_LEVEL_CUSTOM,
		'unknown'       => MCProtectFW_V542::WP_USER_ROLE_LEVEL_UNKNOWN
	);

	const TABLE_NAME                = "fw_requests";
	const IP_COOKIE_NAME            = "mcfw-ip-cookie";
	const BYPASS_COOKIE_NAME        = "mcfw-bypass-cookie";
	const PREVENT_CACHE_COOKIE_NAME = "wp-mcfw-prevent-cache-cookie";

	private function __construct($protect_mode, $request, $config, $brand_name) {
		$this->request = $request;
		$this->brand_name = $brand_name;
		$this->protect_mode = $protect_mode;

		if (array_key_exists('mode', $config) && is_int($config['mode'])) {
			$this->mode = $config['mode'];
		}

		if (array_key_exists('ipcookiemode', $config) && is_int($config['ipcookiemode'])) {
			$this->ip_cookie_mode = $config['ipcookiemode'];
		}

		if (array_key_exists('admincookiemode', $config) && is_int($config['admincookiemode'])) {
			$this->admin_cookie_mode = $config['admincookiemode'];
		}

		if (array_key_exists('iswpusercookieenabled', $config) &&
				is_bool($config['iswpusercookieenabled'])) {

			$this->is_wp_user_cookie_enabled = $config['iswpusercookieenabled'];
		}

		if (array_key_exists('bypasslevel', $config) && is_int($config['bypasslevel'])) {
			$this->bypass_level = $config['bypasslevel'];
		}

		if (array_key_exists('wpfruleinitmode', $config) && is_int($config['wpfruleinitmode'])) {
			$this->wpf_rule_init_mode = $config['wpfruleinitmode'];
		}

		if (array_key_exists('customroles', $config) && is_array($config['customroles'])) {
			$this->custom_roles = $config['customroles'];
		}

		if (array_key_exists('wpusercapstoconsider', $config) &&
				is_array($config['wpusercapstoconsider'])) {

			$this->wp_user_caps_to_consider = $config['wpusercapstoconsider'];
		}

		if (array_key_exists('cookiekey', $config) && is_string($config['cookiekey'])) {
			$this->cookie_key = $config['cookiekey'];
		}

		if (array_key_exists('cookiepath', $config) && is_string($config['cookiepath'])) {
			$this->cookie_path = $config['cookiepath'];
		}

		if (array_key_exists('cookiedomain', $config) && is_string($config['cookiedomain'])) {
			$this->cookie_domain = $config['cookiedomain'];
		}

		if (array_key_exists('cansetcachepreventioncookie', $config) &&
				is_bool($config['cansetcachepreventioncookie'])) {

			$this->can_set_cache_prevention_cookie = $config['cansetcachepreventioncookie'];
		}

		if (array_key_exists('rulesmode', $config) && is_int($config['rulesmode'])) {
			$this->rules_mode = $config['rulesmode'];
		}

		if (array_key_exists('isgeoblocking', $config) && is_bool($config['isgeoblocking'])) {
			$this->is_geo_blocking = $config['isgeoblocking'];
		}

		if (array_key_exists('logconfig', $config) && is_array($config['logconfig'])) {
			$this->log_config = $config['logconfig'];
		}

		if (array_key_exists('reqprofilingmode', $this->log_config) &&
				is_int($this->log_config['reqprofilingmode'])) {

			$this->request_profiling_mode = $this->log_config['reqprofilingmode'];
		}

		if (array_key_exists('loggingmode', $this->log_config) &&
				is_int($this->log_config['loggingmode'])) {

			$this->logging_mode = $this->log_config['loggingmode'];
		}

		if (array_key_exists('except', $this->log_config) && is_array($this->log_config['except'])) {
			$this->skip_log_config = $this->log_config['except'];
		}

		if (array_key_exists('cookies', $this->skip_log_config) &&
				is_array($this->skip_log_config['cookies'])) {

			$this->skip_log_cookies = $this->skip_log_config['cookies'];
		}

		if (array_key_exists('headers', $this->skip_log_config) &&
				is_array($this->skip_log_config['headers'])) {

			$this->skip_log_headers = $this->skip_log_config['headers'];
		}

		if (array_key_exists('post', $this->skip_log_config) &&
				is_array($this->skip_log_config['post'])) {

			$this->skip_log_post_params = $this->skip_log_config['post'];
		}

		if ($this->isPrependMode()) {
			$log_file = MCDATAPATH . MCCONFKEY . '-mc.log';
			$this->ipstore = new MCProtectIpstore_V542(MCProtectIpstore_V542::STORAGE_TYPE_FS);
			$this->logger = new MCProtectLogger_V542($log_file, MCProtectLogger_V542::TYPE_FS);
		} else {
			$this->ipstore = new MCProtectIpstore_V542(MCProtectIpstore_V542::STORAGE_TYPE_DB);
			$this->logger = new MCProtectLogger_V542(MCProtectFW_V542::TABLE_NAME, MCProtectLogger_V542::TYPE_DB);
		}

		if ($this->is_wp_user_cookie_enabled) {
			$this->loadWPUser();
		}

		$this->initRules();
	}

	public static function getInstance($protect_mode, $request, $config, $brand_name) {
		if (!isset(self::$instance)) {
			self::$instance = new self($protect_mode, $request, $config, $brand_name);
		} elseif (self::$instance->protect_mode != $protect_mode && $protect_mode == MCProtect_V542::MODE_WP) {
			self::$instance->protect_mode = $protect_mode;
			self::$instance->brand_name = $brand_name;
			self::$instance->ipstore = new MCProtectIpstore_V542(MCProtectIpstore_V542::STORAGE_TYPE_DB);
			self::$instance->initRules();
		}

		return self::$instance;
	}

	public static function uninstall() {
		MCProtect_V542::$db->dropBVTable(MCProtectFW_V542::TABLE_NAME);
	}

	public function init() {
		if (!$this->isModeDisabled()) {
			$this->setShutdownCallback();
			$this->profileRequest();
			$this->setAdminCookie();
			$this->setWPUserCookie();
			$this->setIPCookie();
			$this->blockRequestForBlacklistedIP();
			if (!$this->is_on_boot_rules_executed) {
				$this->handleRequestOnRuleMatch($this->rules);

				$this->is_on_boot_rules_executed = true;
			}
		}
	}

	private function isPrependMode() {
		return ($this->protect_mode === MCProtect_V542::MODE_PREPEND);
	}

	private function isWPMode() {
		return ($this->protect_mode === MCProtect_V542::MODE_WP);
	}

	private function isModeDisabled() {
		return ($this->mode === MCProtectFW_V542::MODE_DISABLED);
	}

	private function isModeProtect() {
		return ($this->mode === MCProtectFW_V542::MODE_PROTECT);
	}

	private function isAdminCookieEnabled() {
		return ($this->admin_cookie_mode === MCProtectFW_V542::ADMIN_COOKIE_MODE_ENABLED);
	}

	private function isIPCookieEnabled() {
		return ($this->ip_cookie_mode === MCProtectFW_V542::IP_COOKIE_MODE_ENABLED);
	}

	private function isRequestProfilingDisabled() {
		return ($this->request_profiling_mode === MCProtectFW_V542::REQ_PROFILING_MODE_DISABLED);
	}

	private function isRequestProfilingModeDebug() {
		return ($this->request_profiling_mode === MCProtectFW_V542::REQ_PROFILING_MODE_DEBUG);
	}

	private function isRequestHasValidBypassCookie() {
		if (!isset($this->has_valid_bypass_cookie)) {
			$cookie = (string) $this->request->getCookies(MCProtectFW_V542::BYPASS_COOKIE_NAME);
			$new_cookie = $this->generateBypassCookie();
			$is_valid = ($this->isAdminCookieEnabled() && $new_cookie && ($cookie === $new_cookie));
			$this->has_valid_bypass_cookie = $is_valid;
		}

		return $this->has_valid_bypass_cookie;
	}

	private function isRulesModeProtect() {
		return ($this->rules_mode === MCProtectFW_V542::RULES_MODE_PROTECT);
	}

	public function isLoggingModeComplete() {
		return ($this->logging_mode === MCProtectFW_V542::LOGGING_MODE_COMPLETE);
	}

	public function isLoggingModeVisitor() {
		return ($this->logging_mode === MCProtectFW_V542::LOGGING_MODE_VISITOR);
	}

	public function isGeoBlockingEnabled() {
		return ($this->is_geo_blocking === true);
	}

	private function isWPFRuleInitModePrepend() {
		return ($this->wpf_rule_init_mode === MCProtectFW_V542::WPF_RULE_INIT_MODE_PREPEND);
	}

	private function isWPFRuleInitModeWP() {
		return ($this->wpf_rule_init_mode === MCProtectFW_V542::WPF_RULE_INIT_MODE_WP);
	}

	private function canInitWPFRules() {
		if (!$this->isWPFRuleInitModePrepend() && $this->isPrependMode()) {
			return false;
		}

		return true;
	}

	private function generateBypassCookie() {
		$time = floor(time() / 43200);

		return hash('sha256', $this->bypass_level . $time . $this->cookie_key);
	}

	private function getWPFRules($action_name) {
		if (!array_key_exists($action_name, $this->wpf_rules)) {
			return array();
		}
		return $this->wpf_rules[$action_name];
	}

	public function setWPUserCookieHandler() {
		if (function_exists('is_user_logged_in') && is_user_logged_in()) {
			$current_wp_user = $this->getCurrentWPUser();

			if (!$current_wp_user->isIdentical($this->request->wp_user)) {
				$serialized_wp_user = MCProtectWPUser_V542::_serialize($current_wp_user);
				$cookie_val = $serialized_wp_user . '_' .
					MCProtectUtils_V542::signMessage($serialized_wp_user, $this->cookie_key);
				$cookie_val = base64_encode($cookie_val);

				$this->setcookie(MCProtectWPUser_V542::COOKIE_NAME, $cookie_val, time() + 43200);
			}
		} elseif ($this->request->wp_user->isLoggedIn()) {
			$this->request->wp_user = MCProtectWPUser_V542::defaultUser();
			$this->unsetCookie(MCProtectWPUser_V542::COOKIE_NAME);
		}
	}

	private function getCurrentWPUser() {
		$id = 0;
		$role_level = 0;
		$capabilities = array();
		$time = (int) floor(time() / 43200);

		if (function_exists('wp_get_current_user')) {
			$user = wp_get_current_user();
			$id = $user->ID;
			$role_level = $this->getCurrentWPUserRoleLevel();
			$capabilities = $this->getCurrentWPUserCapabilities();
		}

		return (new MCProtectWPUser_V542($id, $role_level, $capabilities, $time));
	}

	private function getCurrentWPUserCapabilities() {
		$capabilities = array();

		if (function_exists('current_user_can')) {
			foreach ($this->wp_user_caps_to_consider as $capability => $id) {
				if (current_user_can($capability)) {
					$capabilities[] = $id;
				}
			}
			sort($capabilities);
		}

		return $capabilities;
	}

	private function loadWPUser() {
		$this->request->wp_user = MCProtectWPUser_V542::defaultUser();

		$cookie_val = $this->request->getCookies(MCProtectWPUser_V542::COOKIE_NAME);
		if (!is_string($cookie_val)) {
			return;
		}

		$cookie_val = base64_decode($cookie_val, true);
		if ($cookie_val === false) {
			return;
		}

		$cookie_val_array = explode('_', $cookie_val);
		if (count($cookie_val_array) !== 2) {
			return;
		}
		list($serialized_user, $signature) = $cookie_val_array;

		if (MCProtectUtils_V542::verifyMessage($serialized_user, $signature, $this->cookie_key) === true) {
			$wp_user = MCProtectWPUser_V542::_unserialize($serialized_user);

			if (!isset($wp_user) || $wp_user->time !== (int) floor(time() / 43200)) {
				return;
			}

			$this->request->wp_user = $wp_user;

			$capability_names = array_flip($this->wp_user_caps_to_consider);
			foreach ($this->request->wp_user->capabilities as $capability) {
				if (array_key_exists($capability, $capability_names)) {
					$this->request->wp_user->capability_names[] = $capability_names[$capability];
				}
			}

			$role_by_level = array_flip(array_merge(MCProtectFW_V542::DEFAULT_WP_USER_ROLE_LEVELS,
					MCProtectFW_V542::EXTRA_WP_USER_ROLE_LEVELS));
			$this->request->wp_user->role = $role_by_level[$this->request->wp_user->role_level];
		}
	}

	private function pushWPFRule($action_name, $rule) {
		if (!array_key_exists($action_name, $this->wpf_rules)) {
			$this->wpf_rules[$action_name] = array();
		}

		$this->wpf_rules[$action_name][] = $rule;
	}

	private function initRules() {
		if (!$this->isRulesModeProtect() || $this->isRequestIPWhitelisted()) {
			return;
		}

		if ($this->is_rule_initialized && $this->is_wpf_rule_initialized) {
			return;
		}

		if ($this->isPrependMode()) {
			$rules_file = MCDATAPATH . MCCONFKEY . '-' . 'mc_rules.json';
			$rule_arrays = MCProtectUtils_V542::parseFile($rules_file);
		} else {
			$rule_arrays = MCProtect_V542::$settings->getOption('bvruleset');
			if(!is_array($rule_arrays)) {
				$rule_arrays = array();
			}
		}

		if (empty($rule_arrays)) {
			$this->updateRuleLog('errors', 'ruleset', 'Invalid RuleSet');
			return;
		}

		foreach($rule_arrays as $rule_array) {
			$rule = MCProtectFWRule_V542::init($rule_array);

			if ($rule) {
				if (!$this->is_rule_initialized && $rule->isExeOnBoot()) {
					if (!$this->isRequestHasValidBypassCookie()) {
						$this->initRule($rule);
					}
				} elseif (!$this->is_wpf_rule_initialized && $this->canInitWPFRules()) {
					$this->initWPFRule($rule);
				}
			}
		}

		$this->is_rule_initialized = true;
		if ($this->canInitWPFRules()) {
			$this->is_wpf_rule_initialized = true;
		}
	}

	private function initRule($rule) {
		$this->rules[] = $rule;
	}

	private function initWPFRule($rule) {
		if ($rule->isExeOnPreUpdateOption()) {
			$this->addWPHook($rule, 'pre_update_option', 'handleRequestOnPreUpdateOption', 3);
		} elseif ($rule->isExeOnPreDeletePost()) {
			$this->addWPHook($rule, 'pre_delete_post', 'handleRequestOnPreDeletePost', 3);
		} elseif ($rule->isExeOnWPInsertPostEmptyContent()) {
			$this->addWPHook($rule, 'wp_insert_post_empty_content', 'handleRequestOnWPInsertPostEmptyContent', 2);
		} elseif ($rule->isExeOnInsertUserMeta()) {
			$this->addWPHook($rule, 'insert_user_meta', 'handleRequestOnInsertUserMeta', 4);
		} elseif ($rule->isExeOnDeleteOption()) {
			$this->addWPHook($rule, 'delete_option', 'handleRequestOnDeleteOption', 1, 'action');
		} elseif ($rule->isExeOnDeleteUser()) {
			$this->addWPHook($rule, 'delete_user', 'handleRequestOnDeleteUser', 3, 'action');
		} elseif ($rule->isExeOnPasswordReset()) {
			$this->addWPHook($rule, 'password_reset', 'handleRequestOnPasswordReset', 2, 'action');
		} elseif ($rule->isExeOnSendAuthCookies()) {
			$this->addWPHook($rule, 'send_auth_cookies', 'handleRequestOnSendAuthCookies', 6);
		} elseif ($rule->isExeOnSetAuthCookie()) {
			$this->addWPHook($rule, 'set_auth_cookie', 'handleRequestOnSetAuthCookie', 6, 'action');
		} elseif ($rule->isExeOnInit()) {
			$this->addWPHook($rule, 'init', 'handleRequestOnInit', 0, 'action');
		} elseif ($rule->isExeOnUserRegister()) {
			$this->addWPHook($rule, 'user_register', 'handleRequestOnUserRegister', 2, 'action');
		} elseif ($rule->isExeOnAddUserMeta()) {
			$this->addWPHook($rule, 'add_user_meta', 'handleRequestOnAddUserMeta', 3, 'action');
		} elseif ($rule->isExeOnUpdateUserMetadata()) {
			$this->addWPHook($rule, 'update_user_metadata', 'handleRequestOnUpdateUserMetadata', 5);
		} elseif ($rule->isExeOnUpdateUserMeta()) {
			$this->addWPHook($rule, 'update_user_meta', 'handleRequestOnUpdateUserMeta', 4, 'action');
		} elseif ($rule->isExeOnAddOption()) {
			$this->addWPHook($rule, 'add_option', 'handleRequestOnAddOption', 2, 'action');
		} elseif ($rule->isExeOnWPPreInsertUserData()) {
			$this->addWPHook($rule, 'wp_pre_insert_user_data', 'handleRequestOnWPPreInsertUserData', 4);
		}
	}

	private function addWPHook($rule, $hook_name, $function_name, $accepted_args, $hook_type = 'filter') {
		//Initialize the hook once for all rule of the same type.
		if (empty($this->getWPFRules($function_name))) {
			$callback = array($this, $function_name);

			if ($this->isWPMode()) {
				if ($hook_type == 'action') {
					add_action($hook_name, $callback, -9999999, $accepted_args);
				} else {
					add_filter($hook_name, $callback, -9999999, $accepted_args);
				}
			} else {
				MCProtectUtils_V542::preInitWPHook($hook_name, $callback, -9999999, $accepted_args);
			}
		}

		$this->pushWPFRule($function_name, $rule);
	}

	public function handleRequestOnPreUpdateOption($value, $option, $old_value) {
		$rules = $this->getWPFRules('handleRequestOnPreUpdateOption');

		if (!empty($rules)) {
			$variables = array('value' => $value, 'option' => $option, 'old_value' => $old_value);
			$log_data = $variables;
			$this->handleRequestOnRuleMatch($rules, $variables, $log_data);
		}

		return $value;
	}

	public function handleRequestOnPreDeletePost($delete, $post, $force_delete) {
		$rules = $this->getWPFRules('handleRequestOnPreDeletePost');

		if (!empty($rules)) {
			$variables = array('delete' => $delete, 'post' => $post, 'force_delete' => $force_delete);

			$log_data = array(
				'id' => $post->ID,
				'post_type' => $post->post_type,
				'post_status' => $post->post_status
			);

			$this->handleRequestOnRuleMatch($rules, $variables, $log_data);
		}

		return $delete;
	}

	public function handleRequestOnWPInsertPostEmptyContent($maybe_empty, $postarr) {
		$rules = $this->getWPFRules('handleRequestOnWPInsertPostEmptyContent');

		if (!empty($rules)) {
			$variables = array('maybe_empty' => $maybe_empty, 'postarr' => $postarr);

			$log_data = array();
			if (isset($postarr['post_type'])) {
				$log_data['post_type'] = $postarr['post_type'];
			}
			if (isset($postarr['ID'])) {
				$log_data['id'] = $postarr['ID'];
			}

			$this->handleRequestOnRuleMatch($rules, $variables, $log_data);
		}

		return $maybe_empty;
	}

	public function handleRequestOnInsertUserMeta($meta, $user, $update, $userdata = null) {
		$rules = $this->getWPFRules('handleRequestOnInsertUserMeta');

		if (!empty($rules)) {
			$variables = array(
				'meta' => $meta,
				'update' => $update
			);
			$log_data = $variables;

			$variables['userdata'] = $userdata;
			if (isset($userdata['user_login']) && is_string($userdata['user_login'])) {
				$log_data['username'] = sanitize_user($userdata['user_login'], true);
			}
			if (isset($userdata['role'])) {
				$log_data['role'] = $userdata['role'];
			}

			$variables['user'] = $user;
			$log_data['user'] = $this->getUserLogData($user);

			$this->handleRequestOnRuleMatch($rules, $variables, $log_data);
		}

		return $meta;
	}

	public function handleRequestOnDeleteOption($option) {
		$rules = $this->getWPFRules('handleRequestOnDeleteOption');

		if (!empty($rules)) {
			$variables = array('option' => $option);
			$log_data = $variables;
			$this->handleRequestOnRuleMatch($rules, $variables, $log_data);
		}
	}

	public function handleRequestOnDeleteUser($id, $reassign, $user = null) {
		$rules = $this->getWPFRules('handleRequestOnDeleteUser');

		if (!empty($rules)) {
			if(is_null($user)) {
				$user = $this->getUserBy('id', $id);
			}

			$variables = array('id' => $id, 'reassign' => $reassign);
			$log_data = $variables;

			$variables['user'] = $user;
			$log_data['user'] = $this->getUserLogData($user);

			$this->handleRequestOnRuleMatch($rules, $variables, $log_data);
		}
	}

	public function handleRequestOnPasswordReset($user, $new_pass) {
		$rules = $this->getWPFRules('handleRequestOnPasswordReset');

		if (!empty($rules)) {
			$variables = array('user' => $user, 'new_pass' => $new_pass);
			$log_data = array(
				'new_pass' => "MD5: " . md5($new_pass),
				'user' => $this->getUserLogData($user)
			);
			$this->handleRequestOnRuleMatch($rules, $variables, $log_data);
		}
	}

	public function handleRequestOnSendAuthCookies($send, $expire = null,
			$expiration = null, $user_id = null, $scheme = null, $token = null) {
		$rules = $this->getWPFRules('handleRequestOnSendAuthCookies');

		if (!empty($rules)) {
			$user = $this->getUserBy('id', $user_id);

			$variables = array(
				'user_id' => $user_id,
				'send' => $send,
				'expire' => $expire,
				'expiration' => $expiration,
				'scheme' => $scheme
			);

			$log_data = $variables;
			$variables['token'] = $token;
			$log_data['token'] = "MD5: " . md5($token);

			$variables['user'] = $user;
			$log_data['user'] = $this->getUserLogData($user);

			$this->handleRequestOnRuleMatch($rules, $variables, $log_data);
		}

		return $send;
	}

	public function handleRequestOnSetAuthCookie($auth_cookie, $expire, $expiration, $user_id, $scheme, $token = null) {
		$rules = $this->getWPFRules('handleRequestOnSetAuthCookie');

		if (!empty($rules)) {
			$user = $this->getUserBy('id', $user_id);

			$variables = array(
				'user_id' => $user_id,
				'auth_cookie' => md5($auth_cookie),
				'expire' => $expire,
				'expiration' => $expiration,
				'scheme' => $scheme
			);

			$log_data = $variables;

			$variables['token'] = $token;
			$log_data['token'] = "MD5: " . md5($token);

			$variables['user'] = $user;
			$log_data['user'] = $this->getUserLogData($user);

			$this->handleRequestOnRuleMatch($rules, $variables, $log_data);
		}
	}

	public function handleRequestOnInit() {
		$rules = $this->getWPFRules('handleRequestOnInit');

		if (!empty($rules)) {
			$variables = array();
			$this->handleRequestOnRuleMatch($rules, $variables);
		}
	}

	public function handleRequestOnUserRegister($user_id, $userdata = null) {
		$rules = $this->getWPFRules('handleRequestOnUserRegister');

		if (!empty($rules)) {
			$user = $this->getUserBy('id', $user_id);

			$variables = array(
				'user_id' => $user_id,
			);

			$log_data = $variables;

			$variables['userdata'] = $userdata;
			$log_data['user'] = $this->getUserLogData($user);

			$this->handleRequestOnRuleMatch($rules, $variables, $log_data);
		}
	}

	public function handleRequestOnAddUserMeta($object_id, $meta_key, $meta_value) {
		$rules = $this->getWPFRules('handleRequestOnAddUserMeta');

		if (!empty($rules)) {
			$user = $this->getUserBy('id', $object_id);

			$variables = array(
				'object_id' => $object_id,
				'meta_key' => $meta_key,
				'meta_value' => $meta_value
			);
			$log_data = $variables;

			$variables['user'] = $user;
			$log_data['user'] = $this->getUserLogData($user);

			$this->handleRequestOnRuleMatch($rules, $variables, $log_data);
		}
	}

	public function handleRequestOnUpdateUserMetadata($check, $object_id, $meta_key, $meta_value, $prev_value) {
		$rules = $this->getWPFRules('handleRequestOnUpdateUserMetadata');

		if (!empty($rules)) {
			$user = $this->getUserBy('id', $object_id);

			$variables = array(
				'check' => $check,
				'object_id' => $object_id,
				'meta_key' => $meta_key,
				'meta_value' => $meta_value,
				'prev_value' => $prev_value
			);

			$log_data = $variables;

			$variables['user'] = $user;
			$log_data['user'] = $this->getUserLogData($user);

			$this->handleRequestOnRuleMatch($rules, $variables, $log_data);
		}

		return $check;
	}

	public function handleRequestOnUpdateUserMeta($meta_id, $object_id, $meta_key, $meta_value) {
		$rules = $this->getWPFRules('handleRequestOnUpdateUserMeta');

		if (!empty($rules)) {
			$user = $this->getUserBy('id', $object_id);

			$variables = array(
				'meta_id' => $meta_id,
				'object_id' => $object_id,
				'meta_key' => $meta_key,
				'meta_value' => $meta_value
			);

			$log_data = $variables;

			$variables['user'] = $user;
			$log_data['user'] = $this->getUserLogData($user);

			$this->handleRequestOnRuleMatch($rules, $variables, $log_data);
		}
	}

	public function handleRequestOnWPPreInsertUserData($data, $update, $user_id, $userdata = null) {
		$rules = $this->getWPFRules('handleRequestOnWPPreInsertUserData');

		if (!empty($rules)) {
			$user = $this->getUserBy('id', $user_id);

			$variables = array(
				'update' => $update,
				'user_id' => $user_id,
			);
			$log_data = $variables;

			$variables['data'] = $data;
			$variables['userdata'] = $userdata;

			$log_data['data'] = array();
			if (isset($data['user_login'])) {
				$log_data['data']['user_login'] = $data['user_login'];
			}
			if (isset($data['user_email'])) {
				$log_data['data']['user_email'] = $data['user_email'];
			}

			$log_data['userdata'] = array();
			if (isset($userdata['role'])) {
				$log_data['userdata']['role'] = $userdata['role'];
			}

			$variables['user'] = $user;
			$log_data['user'] = $this->getUserLogData($user);

			$this->handleRequestOnRuleMatch($rules, $variables, $log_data);
		}

		return $data;
	}

	public function handleRequestOnAddOption($option, $value) {
		$rules = $this->getWPFRules('handleRequestOnAddOption');

		if (!empty($rules)) {
			$variables = array(
				'option' => $option,
				'value' => $value
			);
			$log_data = $variables;

			$this->handleRequestOnRuleMatch($rules, $variables, $log_data);
		}
	}

	private function setShutdownCallback() {
		if (!$this->is_shutdown_cb_set) {
			register_shutdown_function(array($this, 'log'));
			$this->is_shutdown_cb_set = true;
		}
	}

	private function setCookie($name, $value, $expire) {
		$path = $this->cookie_path;
		$cookie_domain = $this->cookie_domain;

		if (version_compare(PHP_VERSION, '5.2.0') >= 0) {
			$secure = function_exists('is_ssl') ? is_ssl() : false;
			@setcookie($name, $value, $expire, $path, $cookie_domain, $secure, true);
		} else {
			@setcookie($name, $value, $expire, $path);
		}
	}

	private function unsetCookie($name) {
		$pastTime = time() - 3600;
		$this->setCookie($name, '', $pastTime);
	}

	private function setAdminCookie() {
		if ($this->isWPMode() && $this->isAdminCookieEnabled()) {
			add_action('init', array($this, 'setBypassCookie'));
		}
	}

	private function setWPUserCookie() {
		if ($this->isWPMode() && $this->is_wp_user_cookie_enabled) {
			add_action('init', array($this, 'setWPUserCookieHandler'), -9999999);
		}
	}

	private function setIPCookie() {
		if (!$this->is_ip_cookie_set && $this->isIPCookieEnabled() &&
				!$this->request->getCookies(MCProtectFW_V542::IP_COOKIE_NAME)) {

			$time = floor(time() / 86400);
			$cookie = hash('sha256', $this->request->ip . $time . $this->cookie_key);
			if ($cookie) {
				$this->setCookie(MCProtectFW_V542::IP_COOKIE_NAME, $cookie, time() + 86400);
			}
		}
	}

	private function getCurrentWPUserRoleLevel() {
		if (function_exists('current_user_can')) {
			if (function_exists('is_super_admin') &&  is_super_admin()) {
				return MCProtectFW_V542::WP_USER_ROLE_LEVEL_ADMIN;
			}

			foreach ($this->custom_roles as $role) {
				if (current_user_can($role)) {
					return MCProtectFW_V542::WP_USER_ROLE_LEVEL_CUSTOM;
				}
			}

			foreach (MCProtectFW_V542::DEFAULT_WP_USER_ROLE_LEVELS as $role => $level) {
				if (current_user_can($role)) {
					return $level;
				}
			}
		}

		return 0;
	}

	public function canLogRequest() {
		$can_log = false;

		if ($this->isLoggingModeComplete()) {
			$can_log = true;
		} elseif ($this->isLoggingModeVisitor()) {
			$can_log = (!empty($this->matched_rules) || !$this->isRequestHasValidBypassCookie());
		}

		return $can_log;
	}

	public function log() {
		if ($this->canLogRequest()) {
			$this->logger->log($this->getRequestDataToLog());
		}
	}

	private function canLogValue($key, $prefix) {
		if ($prefix === 'BODY[') {
			return $this->canLogPostValue($key);
		} elseif ($prefix === 'COOKIES[') {
			return $this->canLogCookieValue($key);
		}

		return true;
	}

	private function canLogPostValue($key) {
		if (is_string($key) && in_array($key, $this->skip_log_post_params)) {
			return false;
		}

		return true;
	}

	private function canLogCookieValue($key) {
		if (is_string($key) && in_array($key, $this->skip_log_cookies)) {
			return false;
		}

		return true;
	}

	private function canLogHeaderValue($key) {
		if (is_string($key) && in_array($key, $this->skip_log_headers)) {
			return false;
		}

		return true;
	}

	private function getPostParamsToLog($params) {
		$loggable_params = array();

		if (is_array($params)) {
			foreach ($params as $key => $value) {
				if (is_array($value)) {
					$loggable_params[$key] = $this->getPostParamsToLog($value);
				} else {
					if (!$this->canLogPostValue($key)) {
						$loggable_params[$key] = "Sensitive Data";
					} else {
						$valsize = $this->getLength($value);
						if ($valsize > 1024) {
							$value = substr($value, 0, 1024);
							$loggable_params[$key] = "Data too long: {$valsize} : {$value}";
						} else {
							$loggable_params[$key] = $value;
						}
					}
				}
			}
		}

		return $loggable_params;
	}

	private function getBVCookies() {
		$cookies = array();

		if ($this->request->getCookies(MCProtectFW_V542::IP_COOKIE_NAME) !== NULL) {
			$cookie_val = (string) $this->request->getCookies(MCProtectFW_V542::IP_COOKIE_NAME);
			$cookies[MCProtectFW_V542::IP_COOKIE_NAME] = $cookie_val;
		}

		return $cookies;
	}

	private function getCookiesToLog($cookies) {
		$loggable_cookies = array();

		if (is_array($cookies)) {
			foreach ($cookies as $key => $value) {
				if (!$this->canLogCookieValue($key)) {
					$loggable_cookies[$key] = "SensitiveData:" . md5($value);
				} else {
					$loggable_cookies[$key] = $value;
				}
			}
		}

		return $loggable_cookies;
	}

	private function getHeadersToLog($headers) {
		$loggable_headers = array();

		if (is_array($headers)) {
			foreach ($headers as $key => $value) {
				if (!$this->canLogHeaderValue($key)) {
					$loggable_headers[$key] = "SensitiveData:" . md5($value);
				} else {
					$loggable_headers[$key] = $value;
				}
			}
		}

		return $loggable_headers;
	}

	private function getRequestDataToLog() {
		$referer = $this->request->getHeader('Referer') ? $this->request->getHeader('Referer') : '';
		$user_agent = $this->request->getHeader('User-Agent')
			? $this->request->getHeader('User-Agent') : '';

		$rule_log = serialize($this->rule_log);
		if (strlen($rule_log) > 64000) {
			$rule_log = substr($rule_log, 0, 64000);
		}

		$request_profiled_data = serialize($this->request_profiled_data);
		if (strlen($request_profiled_data) > 16000) {
			$request_profiled_data = serialize(array("keys" => array_keys($this->request_profiled_data)));
			if (strlen($request_profiled_data) > 16000) {
				$request_profiled_data = serialize(array("bv_over_size" => true));
			}
		}

		$data = array(
			"path"         => $this->request->path,
			"filenames"    => serialize($this->request->file_names),
			"host"         => $this->request->host,
			"time"         => $this->request->timestamp,
			"ip"           => $this->request->ip,
			"method"       => $this->request->method,
			"query_string" => $request_profiled_data,
			"user_agent"   => $user_agent,
			"resp_code"    => $this->request->getRespCode(),
			"referer"      => $referer,
			"status"       => $this->request->status,
			"category"     => $this->request->category,
			"rules_info"   => $rule_log,
			"request_id"   => $this->request->getRequestID(),
			"matched_rules"=> serialize($this->matched_rules)
		);

		return $data;
	}

	private function getLength($val) {
		$length = 0;

		if (is_array($val)) {
			foreach ($val as $e) {
				$length += $this->getLength($e);
			}

			return $length;
		} else {
			return strlen((string) $val);
		}
	}

	private function matchCount($pattern, $subject) {
		$count = 0;
		if (is_array($subject)) {
			foreach ($subject as $val) {
				$count += $this->matchCount($pattern, $val);
			}
			return $count;
		} else {
			$count = preg_match_all((string) $pattern, (string) $subject, $matches);
			return ($count === false ? 0 : $count);
		}
	}

	private function updateRuleLog($category, $sub_category, $value) {
		$category_data  = array();
		$sub_category_data  = array();

		if (array_key_exists($category, $this->rule_log)) {
			$category_data = $this->rule_log[$category];
		}

		if (array_key_exists($sub_category, $category_data)) {
			$sub_category_data = $category_data[$sub_category];
		}

		$sub_category_data[] = $value;
		$category_data[$sub_category] = $sub_category_data;

		$this->rule_log[$category] = $category_data;
	}

	private function inspectRequest() {
		$this->updateRuleLog('inspect', "headers", $this->getHeadersToLog($this->request->getHeaders()));

		if (isset($this->request->wp_user)) {
			$this->updateRuleLog('inspect', "wpUserInfo", $this->request->wp_user->getInfo());
		}

		$this->updateRuleLog('inspect', "getParams", $this->request->getGetParams());
		$this->updateRuleLog('inspect', "postParams", $this->getPostParamsToLog($this->request->getPostParams()));
		$this->updateRuleLog('inspect', "cookies", $this->getCookiesToLog($this->request->getCookies()));
	}

	private function getUserBy($attribute, $value) {
		if (isset($value) && function_exists('get_user_by') && MCProtectUtils_V542::havePluginsLoaded()) {
			return get_user_by($attribute, $value);
		}
	}

	private function getUserLogData($user) {
		$user_data = array();

		if (is_a($user, "WP_User")) {
			$user_data = array(
				'id' => $user->ID,
				'user_login' => $user->user_login,
				'user_email' => $user->user_email,
				'allcaps' => $user->allcaps,
				'roles' => $user->roles
			);
		}

		return $user_data;
	}

	private function profileRequestData($params, $debug = false, $prefix = '', $obraces = 1) {
		$profiled_data = array();

		if (is_array($params)) {
			foreach ($params as $key => $value) {
				$original_key = $key;
				$key = $prefix . $key;
				if (is_array($value)) {
					$profiled_data = $profiled_data + $this->profileRequestData($value, $debug, $key . '[', $obraces + 1);
				} else {
					$key = $key . str_repeat(']', $obraces);
					$profiled_data[$key] = array();
					$valsize = $this->getLength($value);
					$profiled_data[$key]["size"] = $valsize;
					if ($debug === true && $valsize < 256 && $this->canLogValue($original_key, $prefix)) {
						$profiled_data[$key]["value"] = $value;
						continue;
					}

					if (MCHelper::safePregMatch('/^\d+$/', $value)) {
						$profiled_data[$key]["numeric"] = true;
					} elseif (MCHelper::safePregMatch('/^\w+$/', $value)) {
						$profiled_data[$key]["regular_word"] = true;
					} elseif (MCHelper::safePregMatch('/^\S+$/', $value)) {
						$profiled_data[$key]["special_word"] = true;
					} elseif (MCHelper::safePregMatch('/^[\w\s]+$/', $value)) {
						$profiled_data[$key]["regular_sentence"] = true;
					} elseif (MCHelper::safePregMatch('/^[\w\W]+$/', $value)) {
						$profiled_data[$key]["special_chars_sentence"] = true;
					}

					if (MCHelper::safePregMatch('/^\b((25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])\.){3}
						(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])\b$/x', $value)) {
						$profiled_data[$key]["ipv4"] = true;
					} elseif (MCHelper::safePregMatch('/\b((25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])\.){3}
						(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])\b/x', $value)) {
						$profiled_data[$key]["embeded_ipv4"] = true;
					} elseif (MCHelper::safePregMatch('/^(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|
						([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|
						([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}
						(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|
						([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|
						:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|
						::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3}
						(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|
						(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))$/x', $value)) {
						$profiled_data[$key]["ipv6"] = true;
					} elseif (MCHelper::safePregMatch('/(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|
						([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|
						([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}
						(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|
						([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|
						:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|
						::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3}
						(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|
						(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))/x', $value)) {
						$profiled_data[$key]["embeded_ipv6"] = true;
					}

					if (MCHelper::safePregMatch('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/', $value)) {
						$profiled_data[$key]["email"] = true;
					} elseif (MCHelper::safePregMatch('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}/', $value)) {
						$profiled_data[$key]["embeded_email"] = true;
					}

					if (MCHelper::safePregMatch('/^(http|ftp)s?:\/\/\S+$/i', $value)) {
						$profiled_data[$key]["link"] = true;
					} elseif (MCHelper::safePregMatch('/(http|ftp)s?:\/\/\S+$/i', $value)) {
						$profiled_data[$key]["embeded_link"] = true;
					}

					if (MCHelper::safePregMatch('/<(html|head|title|base|link|meta|style|picture|source|img|
						iframe|embed|object|param|video|audio|track|map|area|form|label|input|button|
						select|datalist|optgroup|option|textarea|output|progress|meter|fieldset|legend|
						script|noscript|template|slot|canvas)/ix', $value)) {
						$profiled_data[$key]["embeded_html"] = true;
					}

					if (MCHelper::safePregMatch('/\.(jpg|jpeg|png|gif|ico|pdf|doc|docx|ppt|pptx|pps|ppsx|odt|xls|zip|gzip|
						xlsx|psd|mp3|m4a|ogg|wav|mp4|m4v|mov|wmv|avi|mpg|ogv|3gp|3g2|php|html|phtml|js|css)/ix', $value)) {
						$profiled_data[$key]["file"] = true;
					}

					if ($this->matchCount(MCProtectFWRule_V542::SQLIREGEX, $value) > 2) {
						$profiled_data[$key]["sql"] = true;
					}

					if (MCHelper::safePregMatch('/(?:\.{2}[\/]+)/', $value)) {
						$profiled_data[$key]["path_traversal"] = true;
					}

					if (MCHelper::safePregMatch('/\\b(?i:eval)\\s*\\(\\s*(?i:base64_decode|exec|file_get_contents|gzinflate|passthru|shell_exec|stripslashes|system)\\s*\\(/', $value)) {
						$profiled_data[$key]["php_eval"] = true;
					}
				}
			}
		}

		return $profiled_data;
	}

	private function profileRequest() {
		if (!$this->is_request_profiled && !$this->isRequestProfilingDisabled()) {
			$profiled_data = array();

			$is_debug_mode = $this->isRequestProfilingModeDebug();
			$cookies = $is_debug_mode ? $this->request->getCookies() : $this->getBVCookies();
			$cookies = $this->getCookiesToLog($cookies);

			$action = $this->request->getAction();
			if (isset($action)) {
				$profiled_data += $this->profileRequestData(array("action" => $action), true, 'ACTION[');
			}
			if (isset($this->request->wp_user)) {
				$wp_user_info = array(
					'id' => $this->request->wp_user->id
				);
				$profiled_data += $this->profileRequestData($wp_user_info, true, 'WP_USER[');
			}
			$profiled_data += $this->profileRequestData($this->request->getPostParams(), $is_debug_mode, 'BODY[');
			$profiled_data += $this->profileRequestData($this->request->getGetParams(), true, 'GET[');
			$profiled_data += $this->profileRequestData($this->request->getFiles(), true, 'FILES[');
			$profiled_data += $this->profileRequestData($cookies, true, 'COOKIES[');

			$this->request_profiled_data = $profiled_data;
			$this->is_request_profiled = true;
		}
	}

	private function isRequestIPWhitelisted() {
		return $this->ipstore->isFWIPWhitelisted($this->request->ip);
	}

	private function canRequestBypassFirewall() {
		if ($this->isRequestIPWhitelisted() || $this->isRequestHasValidBypassCookie()) {
			$this->request->category = MCProtectRequest_V542::CATEGORY_WHITELISTED;
			$this->request->status = MCProtectRequest_V542::STATUS_BYPASSED;

			return true;
		} elseif (MCProtectUtils_V542::isPrivateIP($this->request->ip)) {
			$this->request->category = MCProtectRequest_V542::CATEGORY_PRIVATEIP;
			$this->request->status = MCProtectRequest_V542::STATUS_BYPASSED;

			return true;
		}

		return false;
	}

	private function blockRequestForBlacklistedIP() {
		if (!$this->canRequestBypassFirewall() && $this->isModeProtect()) {
			if (!$this->is_ip_checked_for_blacklisted ||
					($this->isWPMode() && $this->isGeoBlockingEnabled())) {

				$ip_category = $this->ipstore->getTypeIfBlacklistedIP($this->request->ip);
				if ($ip_category) {
					$this->terminateRequest($ip_category);
				}

				$this->is_ip_checked_for_blacklisted = true;
			}
		}
	}

	private function handleRequestOnRuleMatch($rules, $engine_vars = array(), $log_data = array()) {
		foreach ($rules as $rule) {
			if ($this->break_rule_matching) {
				break;
			}

			$_engine_vars = $engine_vars;
			if (array_key_exists('variables', $rule->opts)) {
				$_engine_vars = array_merge($_engine_vars, $rule->opts['variables']);
			}

			$rule_engine = new MCProtectFWRuleEngine_V542($this->request, $_engine_vars);

			if ($rule_engine->evaluate($rule) && !$rule_engine->hasError()) {
				if (!empty($log_data)) {
					$this->updateRuleLog("info", (string) $rule->id, $log_data);
				}

				$this->matched_rules[] = $rule->id;

				foreach($rule->actions as $action) {
					switch ($action["type"]) {
					case "ALLOW":
						$this->break_rule_matching = true;
						$this->request->category = MCProtectRequest_V542::CATEGORY_RULE_ALLOWED;
						return;
					case "BLOCK":
						if ($this->isModeProtect()) {
							$this->terminateRequest(MCProtectRequest_V542::CATEGORY_RULE_BLOCKED);
						}
						return;
					case "INSPECT":
						$this->inspectRequest();
						break;
					}
				}
			} elseif ($rule_engine->hasError()) {
				$this->updateRuleLog("errors", (string) $rule->id, $rule_engine->getErrorMessage());
			}
		}
	}

	private function terminateRequest($category) {
		$this->request->category = $category;
		$this->request->status = MCProtectRequest_V542::STATUS_BLOCKED;
		$this->request->setRespCode(403);

		if ($this->can_set_cache_prevention_cookie &&
			!$this->request->getCookies(MCProtectFW_V542::PREVENT_CACHE_COOKIE_NAME)) {
			$value = "Prevent Caching Response.";
			$this->setCookie(MCProtectFW_V542::PREVENT_CACHE_COOKIE_NAME, $value, time() + 43200);
		}

		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
		header("Expires: 0");
		header('HTTP/1.0 403 Forbidden');
		die("
				<div style='height: 98vh;'>
					<div style='text-align: center; padding: 10% 0; font-family: Arial, Helvetica, sans-serif;'>
						<div><p>" . $this->brand_name . " Firewall</p></div>
						<p>Blocked because of Malicious Activities</p>
						<p>Reference ID: " . $this->request->getRequestID() . "</p>
					</div>
				</div>
			");
	}

	public function setBypassCookie() {
		if (function_exists('is_user_logged_in') && is_user_logged_in() &&
				!$this->isRequestHasValidBypassCookie()) {

			$role_level = $this->getCurrentWPUserRoleLevel();
			if ($role_level >= $this->bypass_level) {
				$cookie = $this->generateBypassCookie();
				if ($cookie) {
					$this->setCookie(MCProtectFW_V542::BYPASS_COOKIE_NAME, $cookie, time() + 43200);
				}
			}
		}
	}
}
endif;