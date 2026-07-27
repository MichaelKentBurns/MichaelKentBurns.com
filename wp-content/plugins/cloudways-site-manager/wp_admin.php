<?php

if (!defined('ABSPATH')) exit;
if (!class_exists('CWMGRWPAdmin')) :

class CWMGRWPAdmin {
	public $settings;
	public $siteinfo;
	public $bvinfo;
	public $bvapi;

	function __construct($settings, $siteinfo, $bvapi = null) {
		$this->settings = $settings;
		$this->siteinfo = $siteinfo;
		$this->bvapi = $bvapi;
		$this->bvinfo = new CWMGRInfo($this->settings);
	}

	function removeAdminNotices() {
		if (CWMGRHelper::getRawParam('REQUEST', 'page') === $this->bvinfo->plugname) {
			remove_all_actions('admin_notices');
			remove_all_actions('all_admin_notices');
		}
	}

	public function initHandler() {
		if (!current_user_can('activate_plugins'))
			return;
	}

	public function hidePluginDetails($plugin_metas, $slug) {
		$brand = $this->bvinfo->getBrandInfo();
		$bvslug = $this->bvinfo->slug;

		if ($slug === $bvslug && is_array($brand) && array_key_exists('hide_plugin_details', $brand)) {
			foreach ($plugin_metas as $pluginKey => $pluginValue) {
				// phpcs:ignore WordPress.WP.I18n.MissingArgDomain
				if (strpos($pluginValue, sprintf('>%s<', __('View details')))) {
					unset($plugin_metas[$pluginKey]);
					break;
				}
			}
		}
		return $plugin_metas;
	}

	public function menu() {
	}

	public function settingsLink($links, $file) {
		return $links;
	}

	public function getPluginLogo() {
		$brand = $this->bvinfo->getBrandInfo();
		if ($brand && array_key_exists('logo', $brand)) {
			return $brand['logo'];
		}
		return $this->bvinfo->logo;
	}

	public function getWebPage() {
		$brand = $this->bvinfo->getBrandInfo();
		if ($brand && array_key_exists('webpage', $brand)) {
			return $brand['webpage'];
		}
		return $this->bvinfo->webpage;
	}

	public function siteInfoTags() {
		require_once dirname( __FILE__ ) . '/recover.php';
		$secret = CWMGRRecover::defaultSecret($this->settings);
		$public = CWMGRAccount::getApiPublicKey($this->settings);
		$server_ip = CWMGRHelper::getStringParamEscaped('SERVER', 'SERVER_ADDR', 'attr');
		$tags = "<input type='hidden' name='url' value='".esc_attr($this->siteinfo->wpurl())."'/>\n".
				"<input type='hidden' name='homeurl' value='".esc_attr($this->siteinfo->homeurl())."'/>\n".
				"<input type='hidden' name='siteurl' value='".esc_attr($this->siteinfo->siteurl())."'/>\n".
				"<input type='hidden' name='dbsig' value='".esc_attr($this->siteinfo->dbsig(false))."'/>\n".
				"<input type='hidden' name='plug' value='".esc_attr($this->bvinfo->plugname)."'/>\n".
				"<input type='hidden' name='bvversion' value='".esc_attr($this->bvinfo->version)."'/>\n".
	 			"<input type='hidden' name='serverip' value='".$server_ip."'/>\n".
				"<input type='hidden' name='abspath' value='".esc_attr(ABSPATH)."'/>\n".
				"<input type='hidden' name='secret' value='".esc_attr($secret)."'/>\n".
				"<input type='hidden' name='public' value='".esc_attr($public)."'/>\n";
		return $tags;
	}

	public function initWhitelabel($plugins) {
		if (!is_array($plugins) || !$this->bvinfo->canWhiteLabel()) {
			return $plugins;
		}
		$whitelabel_infos = $this->bvinfo->getPluginsWhitelabelInfos();
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