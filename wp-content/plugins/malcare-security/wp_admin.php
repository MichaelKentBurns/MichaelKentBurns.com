<?php

if (!defined('ABSPATH')) exit;
if (!class_exists('MCWPAdmin')) :
class MCWPAdmin {
	public $settings;
	public $siteinfo;
	public $account;
	public $bvapi;
	public $bvinfo;

	function __construct($settings, $siteinfo, $bvapi = null) {
		$this->settings = $settings;
		$this->siteinfo = $siteinfo;
		$this->bvapi = new MCWPAPI($settings);
		$this->bvinfo = new MCInfo($this->settings);
	}

	public function mainUrl($_params = '') {
		if (function_exists('network_admin_url')) {
			return network_admin_url('admin.php?page='.$this->bvinfo->plugname.$_params);
		} else {
			return admin_url('admin.php?page='.$this->bvinfo->plugname.$_params);
		}
	}

	public function removeAdminNotices() {
		if (array_key_exists('page', $_REQUEST) && $_REQUEST['page'] == $this->bvinfo->plugname) {
			remove_all_actions('admin_notices');
			remove_all_actions('all_admin_notices');
		}
	}

	public function cwBrandInfo() {
		return array(
			'name' => "Bot Protection",
			'title' => "Wordpress Security",
			'description' => "WordPress Security, Bot Protection",
			'authoruri' => "https://www.malcare.com",
			'author' => "MalCare Security",
			'authorname' => "Malcare Security",
			'pluginuri' => "https://www.malcare.com",
			'menuname' => "Bot Protection",
			'brand_icon' => "/img/cw_icon.png"
		);
	}

	public function initHandler() {
		if (!current_user_can('activate_plugins'))
			return;

		if (array_key_exists('bvnonce', $_REQUEST) &&
				wp_verify_nonce($_REQUEST['bvnonce'], "bvnonce") &&
				array_key_exists('blogvaultkey', $_REQUEST) &&
				(strlen(MCAccount::sanitizeKey($_REQUEST['blogvaultkey'])) == 64) &&
				(array_key_exists('page', $_REQUEST) &&
				$_REQUEST['page'] == $this->bvinfo->plugname)) {
			$keys = str_split($_REQUEST['blogvaultkey'], 32);
			$pubkey = $keys[0];
			MCAccount::addAccount($this->settings, $keys[0], $keys[1]);
			if (array_key_exists('redirect', $_REQUEST)) {
				$location = $_REQUEST['redirect'];
				$this->account = MCAccount::find($this->settings, $pubkey);
				wp_redirect($this->account->authenticatedUrl('/malcare/access/welcome'));
				exit();
			}
		}
		if ($this->bvinfo->isActivateRedirectSet()) {
			$this->settings->updateOption($this->bvinfo->plug_redirect, 'no');
			##ACTIVATEREDIRECTCODE##
			wp_redirect($this->mainUrl());
		}
	}

	public function mcsecAdminMenu($hook) {
		if ($hook === 'toplevel_page_malcare' || MCHelper::safePregMatch("/bv_add_account$/", $hook) ||
				MCHelper::safePregMatch("/bv_account_details$/", $hook)) {
			wp_enqueue_style( 'bootstrap', plugins_url('css/bootstrap.min.css', __FILE__));
			wp_enqueue_style( 'bvplugin', plugins_url('css/bvplugin.min.css', __FILE__));
		}
	}

	public function menu() {
		add_submenu_page('', 'Malcare', 'Malcare', 'manage_options', 'bv_add_account',
			array($this, 'showAddAccountPage'));
		add_submenu_page('', 'Malcare', 'Malcare', 'manage_options', 'bv_account_details',
			array($this, 'showAccountDetailsPage'));

		$brand = $this->bvinfo->getPluginWhitelabelInfo();
		if (!$this->bvinfo->canSetCWBranding() && (!is_array($brand) || (!array_key_exists('hide', $brand) && !array_key_exists('hide_from_menu', $brand)))) {
			$bname = $this->bvinfo->getBrandName();
			$icon = $this->bvinfo->getBrandIcon();

			$pub_key = MCAccount::getApiPublicKey($this->settings);
			if ($pub_key && isset($pub_key)) {
				$this->account = MCAccount::find($this->settings, $pub_key);
			}

			add_menu_page($bname, $bname, 'manage_options', $this->bvinfo->plugname,
				array($this, 'adminPage'), plugins_url($icon,  __FILE__ ));
		}
	}

	public function hidePluginUpdate($plugins) {
		if (!$this->bvinfo->canWhiteLabel()) {
			return $plugins;
		}
		$whitelabel_infos = $this->bvinfo->getPluginsWhitelabelInfos();
		foreach ($whitelabel_infos as $slug => $brand) {
			if ($this->bvinfo->canWhiteLabel($slug) && isset($plugins->response[$slug]) && is_array($brand)) {
				if (array_key_exists('hide_from_menu', $brand) || array_key_exists('hide', $brand)) {
					unset($plugins->response[$slug]);
				}
			}
		}
		return $plugins;
	}

	public function hidePluginDetails($plugin_metas, $slug) {
		if (!is_array($plugin_metas) || !$this->bvinfo->canWhiteLabel($slug)) {
			return $plugin_metas;
		}
		$whitelabel_info = $this->bvinfo->getPluginWhitelabelInfo($slug);
		if (array_key_exists('hide_plugin_details', $whitelabel_info)) {
			foreach ($plugin_metas as $pluginKey => $pluginValue) {
				if (strpos($pluginValue, sprintf('>%s<', translate('View details')))) {
					unset($plugin_metas[$pluginKey]);
					break;
				}
			}
		}
		return $plugin_metas;
	}

	public function handlePluginHealthInfo($plugins) {
		if (!isset($plugins["wp-plugins-active"]) ||
			!isset($plugins["wp-plugins-active"]["fields"]) || !$this->bvinfo->canWhiteLabel()) {
			return $plugins;
		}

		$whitelabel_infos_by_title = $this->bvinfo->getPluginsWhitelabelInfoByTitle();

		foreach ($whitelabel_infos_by_title as $title => $brand) {
			if (is_array($brand) && array_key_exists('slug', $brand) && $this->bvinfo->canWhiteLabel($brand["slug"])) {
				if (array_key_exists('hide', $brand)) {
					unset($plugins["wp-plugins-active"]["fields"][$title]);
				} else {
					$plugin = $plugins["wp-plugins-active"]["fields"][$title];
					$author = $brand['default_author'];
					if (array_key_exists('name', $brand)) {
						$plugin["label"] = $brand['name'];
					}
					if (array_key_exists('author', $brand)) {
						$plugin["value"] = str_replace($author, $brand['author'], $plugin["value"]);
					}
					if (array_key_exists('description', $brand)) {
						$plugin["debug"] = str_replace($author, $brand['author'], $plugin["debug"]);
					}
					$plugins["wp-plugins-active"]["fields"][$title] = $plugin;
				}
			}
		}
		return $plugins;
	}

	public function settingsLink($links, $file) {
		#XNOTE: Fix this
		if ( $file == plugin_basename( dirname(__FILE__).'/malcare.php' ) ) {
			if (!$this->bvinfo->canSetCWBranding()) {
				$brand = $this->bvinfo->getPluginWhitelabelInfo();
				if (!is_array($brand) || !array_key_exists('hide_from_menu', $brand)) {
					$settings_link = '<a href="'.$this->mainUrl().'">'.__( 'Settings' ).'</a>';
					array_unshift($links, $settings_link);
					$account_details = '<a href="'.$this->mainUrl('&account_details=true').'">'.__( 'Account Details' ).'</a>';
					array_unshift($links, $account_details);
				}
			}
		}
		return $links;
	}

	public function getPluginLogo() {
		$brand = $this->bvinfo->getPluginWhitelabelInfo();
		if (array_key_exists('logo', $brand)) {
			return $brand['logo'];
		}
		return $this->bvinfo->logo;
	}

	public function getWebPage() {
		$brand = $this->bvinfo->getPluginWhitelabelInfo();
		if (array_key_exists('webpage', $brand)) {
			return $brand['webpage'];
		}
		return $this->bvinfo->webpage;
	}

	public function siteInfoTags() {
		require_once dirname( __FILE__ ) . '/recover.php';
		$bvnonce = wp_create_nonce("bvnonce");
		$public = MCAccount::getApiPublicKey($this->settings);
		$secret = MCRecover::defaultSecret($this->settings);
		$tags = "<input type='hidden' name='url' value='".esc_attr($this->siteinfo->wpurl())."'/>\n".
				"<input type='hidden' name='homeurl' value='".esc_attr($this->siteinfo->homeurl())."'/>\n".
				"<input type='hidden' name='siteurl' value='".esc_attr($this->siteinfo->siteurl())."'/>\n".
				"<input type='hidden' name='dbsig' value='".esc_attr($this->siteinfo->dbsig(false))."'/>\n".
				"<input type='hidden' name='plug' value='".esc_attr($this->bvinfo->plugname)."'/>\n".
				"<input type='hidden' name='adminurl' value='".esc_attr($this->mainUrl())."'/>\n".
				"<input type='hidden' name='bvversion' value='".esc_attr($this->bvinfo->version)."'/>\n".
	 			"<input type='hidden' name='serverip' value='".esc_attr($_SERVER["SERVER_ADDR"])."'/>\n".
				"<input type='hidden' name='abspath' value='".esc_attr(ABSPATH)."'/>\n".
				"<input type='hidden' name='secret' value='".esc_attr($secret)."'/>\n".
				"<input type='hidden' name='public' value='".esc_attr($public)."'/>\n".
				"<input type='hidden' name='bvnonce' value='".esc_attr($bvnonce)."'/>\n";
		return $tags;
	}

	public function activateWarning() {
		global $hook_suffix;
		if (!MCAccount::isConfigured($this->settings) && $hook_suffix == 'index.php' ) {
?>
			<div id="message" class="updated" style="padding: 8px; font-size: 16px; background-color: #dff0d8">
						<a class="button-primary" href="<?php echo esc_url($this->mainUrl()); ?>">Activate MalCare</a>
						&nbsp;&nbsp;&nbsp;<b>Almost Done:</b> Activate your Malcare account to secure your site.
			</div>
<?php
		}
	}

	public function showAddAccountPage() {
		require_once dirname( __FILE__ ) . "/admin/add_new_account.php";
	}

	public function showAccountDetailsPage() {
		require_once dirname( __FILE__ ) . "/admin/account_details.php";
	}

	public function showDashboard() {
		require_once dirname( __FILE__ ) . "/admin/dashboard.php";
	}

	public function adminPage() {
		if (isset($_REQUEST['bvnonce']) && wp_verify_nonce( $_REQUEST['bvnonce'], 'bvnonce' )) {
			$info = array();
			$this->siteinfo->basic($info);
			$this->bvapi->pingbv('/bvapi/disconnect', $info, MCAccount::sanitizeKey($_REQUEST['pubkey']));
			MCAccount::remove($this->settings, MCAccount::sanitizeKey($_REQUEST['pubkey']));
		}

		if (isset($_REQUEST['account_details'])) {
			$this->showAccountDetailsPage();
		} else if (isset($_REQUEST['add_account'])) {
			$this->showAddAccountPage();
		} else if(MCAccount::isConfigured($this->settings)) {
			$this->showDashboard();
		} else {
			$this->showAddAccountPage();
		}
	}

	public function initWhitelabel($plugins) {
		if (!is_array($plugins) || !$this->bvinfo->canWhiteLabel()) {
			return $plugins;
		}
		$whitelabel_infos = $this->bvinfo->getPluginsWhitelabelInfos();
		if ($this->bvinfo->canSetCWBranding()) {
			$whitelabel_infos[$this->bvinfo->slug] = $this->cwBrandInfo();
		}

		foreach ($whitelabel_infos as $slug => $brand) {
			if (!isset($slug) || !$this->bvinfo->canWhiteLabel($slug) || !array_key_exists($slug, $plugins) || !is_array($brand)) {
				continue;
			}
			if (array_key_exists('hide', $brand)) {
				unset($plugins[$slug]);
			} else {
				if (array_key_exists('name', $brand)) {
					$plugins[$slug]['Name'] = $brand['name'];
				}
				if (array_key_exists('title', $brand)) {
					$plugins[$slug]['Title'] = $brand['title'];
				}
				if (array_key_exists('description', $brand)) {
					$plugins[$slug]['Description'] = $brand['description'];
				}
				if (array_key_exists('authoruri', $brand)) {
					$plugins[$slug]['AuthorURI'] = $brand['authoruri'];
				}
				if (array_key_exists('author', $brand)) {
					$plugins[$slug]['Author'] = $brand['author'];
				}
				if (array_key_exists('authorname', $brand)) {
					$plugins[$slug]['AuthorName'] = $brand['authorname'];
				}
				if (array_key_exists('pluginuri', $brand)) {
					$plugins[$slug]['PluginURI'] = $brand['pluginuri'];
				}
			}
		}
		return $plugins;
	}
}
endif;