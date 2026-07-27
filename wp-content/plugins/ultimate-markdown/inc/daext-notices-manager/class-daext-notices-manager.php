<?php
/**
 * Plugin Notices manager.
 *
 * Provides a reusable, configurable system for displaying contextual admin
 * notices (documentation and review prompts) for a specific plugin.
 *
 * This class is designed to be embedded in a host plugin and configured via
 * a small set of parameters (plugin slug, main file path, documentation URL,
 * review URL, and screen identifiers). Once initialized, it:
 *
 * - Tracks basic usage metrics in a dedicated option, such as:
 *   - Activation timestamp (used to delay review prompts).
 *   - Number of visits to the plugin settings screen.
 *   - Whether a key feature has actually been used (via the `feature_used`
 *     flag passed in the configuration array).
 * - Decides when to show a **documentation / quick‑start** notice to new
 *   users, ensuring it is displayed only on the plugin’s own admin screens
 *   and only until it has been read or explicitly dismissed.
 * - Decides when to show a **review** notice, using conservative thresholds
 *   (plugin active for a certain time, settings visited several times,
 *   feature used, and no prior dismissal or “never show again” choice).
 * - Ensures that only **one** notice type (documentation or review) is shown
 *   during a single request, so the admin UI is not cluttered with multiple
 *   prompts at once.
 * - Renders notices in a **custom location** via the `daextpnm_after_header_bar`
 *   action hook, so they appear immediately after the plugin’s own header /
 *   toolbar instead of the generic `admin_notices` area.
 * - Exposes a small AJAX API (namespaced per plugin slug) used by the bundled
 *   JavaScript (`admin-notices.js`) to persist user actions such as:
 *   - Marking the docs notice as dismissed.
 *   - Snoozing the review notice ("Remind me later").
 *   - Permanently hiding the review notice ("Never show again").
 *
 * Internally, the class:
 *
 * - Uses a **singleton** pattern via `DAEXT_Notices_Manager::get_instance()` so
 *   that a single configuration is shared and only one instance manages the
 *   notice state for the host plugin.
 * - Names its internal option based on the plugin slug to avoid collisions
 *   when used by multiple plugins.
 * - Uses the `plugin_prefix` and `settings_screen_id` values from the config
 *   to determine where usage should be tracked and where notices should be
 *   displayed.
 * - Enqueues a small JS/CSS bundle only on relevant admin screens, and passes
 *   the plugin slug to JavaScript so it can call the correct, slug‑specific
 *   AJAX action.
 *
 * Typical usage in a host plugin is:
 *
 * - Call `PluginNoticesManager::get_instance( $config )` once during admin
 *   bootstrap, where `$config` includes keys such as `plugin_slug`,
 *   `plugin_file`, `docs_url`, `review_url`, `plugin_prefix`,
 *   `settings_screen_id`, and `feature_used`.
 * - Add a `do_action( 'daextpnm_after_header_bar' );` call in the plugin’s
 *   admin header/template where the notices should be rendered.
 *
 * Important notes:
 *
 * - When embedding this library in different plugins, it is recommended to
 * Add a segment after the `Daext` portion of the namespace below with the
 * host plugin’s unique prefix (for example, `Daext\MyPluginPrefix\NoticesManager`). The same modified namespace should
 * also be used at the beginning of the file where the singleton instance of the class is created.
 * This further reduces the risk of class name collisions if two plugins
 * include their own copy of this class.
 *
 * - The option used to store notice state is not automatically removed when
 * the plugin is uninstalled. Host plugins should delete the option
 * "{$plugin_prefix}_notices_state" during uninstall.
 *
 * - This class is intended to be **manually copied** into the plugin that uses
 * it, rather than installed as a shared Composer dependency. Using Composer
 * for this utility in typical WordPress environments can introduce issues.
 *
 * @package Daext\NoticesManager
 */

/**
 * Add a segment after the `Daext` portion of the namespace below with the
 *  host plugin’s unique prefix (for example, `Daext\MyPluginPrefix\NoticesManager`). The same modified namespace should
 *  also be used at the beginning of the file where the singleton instance of the class is created.
 */
namespace Daext\Daextulma\NoticesManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Version 1.0.16
 */
class DAEXT_Notices_Manager {

	/**
	 * Holds the singleton instance.
	 *
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * Unique slug used to identify the host plugin (also used to build option names and AJAX actions).
	 *
	 * @var string
	 */
	private $plugin_slug;

	/**
	 * Absolute path to the main plugin file, used for hooks like register_activation_hook().
	 *
	 * @var string
	 */
	private $plugin_file;

	/**
	 * URL of the plugin documentation or quick start guide.
	 *
	 * @var string
	 */
	private $docs_url;

	/**
	 * URL of the plugin review page (for example on WordPress.org).
	 *
	 * @var string
	 */
	private $review_url;

	/**
	 * Name of the wp_options entry where this manager stores its state.
	 *
	 * @var string
	 */
	private $option_name;

	/**
	 * Array of persisted notice data (activation time, settings visits, notice flags).
	 *
	 * @var array
	 */
	private $data = array();

	/**
	 * Whether the host plugin reports that its key feature has been used (drives review notice visibility).
	 *
	 * @var bool
	 */
	private $feature_used = false;

	/**
	 * Current version of this notice manager library, used for script/style cache busting.
	 *
	 * @var string
	 */
	private $version = '1.0.16';

	/**
	 * Flag used to track whether the documentation notice has already been displayed
	 * during the current request. This prevents the review notice from being shown
	 * at the same time as the documentation notice.
	 *
	 * @var bool
	 */
	private $doc_notice_displayed = false;

	/**
	 * Identify plugin pages by checking if the current screen ID contains this prefix. This is used to only show the
	 * notices in the plugin screens, and not in all the WordPress admin pages.
	 *
	 * @var mixed|null
	 */
	private $plugin_prefix;

	/**
	 * Identifies the options page of the plugin.
	 *
	 * @var mixed|null
	 */
	private $settings_screen_id;

	/**
	 * Initialize the plugin notices manager.
	 *
	 * @param array $config Configuration array for the notices manager.
	 */
	private function __construct( array $config ) {

		$this->plugin_slug        = $config['plugin_slug'];
		$this->plugin_file        = $config['plugin_file'];
		$this->docs_url           = $config['docs_url'];
		$this->review_url         = $config['review_url'];
		$this->plugin_prefix      = $config['plugin_prefix'] ?? null;
		$this->settings_screen_id = $config['settings_screen_id'] ?? null;
		$this->option_name        = $config['plugin_prefix'] . '_notices_state';
		$this->feature_used       = $config['feature_used'] ?? false;

		$this->load_data();

		register_activation_hook( $this->plugin_file, array( $this, 'on_activation' ) );

		add_action( 'current_screen', array( $this, 'track_settings_visit' ) );

		/**
		 * Render plugin notices in a custom location, immediately after the Interlinks Manager admin toolbar, instead
		 * of relying on the generic WordPress admin_notices area.
		 */
		add_action( 'daextpnm_after_header_bar', array( $this, 'print_notices' ) );

		add_action( 'wp_ajax_daext_notice_action_' . $this->plugin_prefix, array( $this, 'handle_ajax' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Retrieve the singleton instance of the DAEXT_Notices_Manager.
	 *
	 * This method must be called with the configuration array the first time it is used.
	 * Subsequent calls can omit the configuration or pass an empty array.
	 *
	 * @param array $config Configuration array used on first initialization.
	 *
	 * @return self
	 */
	public static function get_instance( array $config = array() ) {

		if ( null === self::$instance ) {
			self::$instance = new self( $config );
		}

		return self::$instance;
	}

	/**
	 * Enqueue the JavaScript and CSS assets used to render and handle plugin
	 * notices in the WordPress admin.
	 *
	 * Assets are only loaded on screens that belong to the host plugin, as
	 * determined by is_plugin_screen(), and are versioned using the internal
	 * $version property for cache busting. The method also localizes the
	 * plugin slug so JavaScript can build the correct, plugin-specific AJAX
	 * action name.
	 */
	public function enqueue_assets() {

		if ( ! $this->is_plugin_screen() ) {
			return;
		}

		wp_enqueue_script(
			'daextpnm-plugin-notices',
			plugins_url( 'assets/js/admin-notices.js', __FILE__ ),
			array( 'jquery' ),
			$this->version,
			true
		);

		/**
		 * Pass dynamic data to the JS file, including the plugin slug used to build the correct AJAX action for this plugin instance.
		 */
		wp_localize_script(
			'daextpnm-plugin-notices',
			'daextpnmNoticeData',
			array(
				'pluginPrefix' => $this->plugin_prefix,
			)
		);

		wp_enqueue_style(
			'daextpnm-plugin-notices',
			plugins_url( 'assets/css/admin-notices.css', __FILE__ ),
			array(),
			$this->version
		);
	}

	/**
	 * Load the plugin data from the WordPress options table using the get_option function. If the option doesn't exist,
	 * create the option with default values.
	 *
	 * @return void
	 */
	private function load_data() {

		$option_data = get_option( $this->option_name );

		if ( false === $option_data ) {

			$option_data = array(
				'activated_at'    => 0,
				'settings_visits' => 0,
				'doc_notice'      => array(
					'dismissed' => false,
				),
				'review_notice'   => array(
					'never_show' => false,
					'next_show'  => 0,
				),
			);

			update_option( $this->option_name, $option_data );
		}

		$this->data = $option_data;
	}

	/**
	 * Save the current plugin data to the WordPress options table using the update_option function.
	 *
	 * @return void
	 */
	private function save_data() {

		update_option( $this->option_name, $this->data );
	}

	/**
	 * Set the "activated_at" timestamp in the plugin data when the plugin is activated for the first time. This
	 * timestamp is used to determine when to start showing the review notice, which is only shown after 12 days from
	 * the plugin activation.
	 *
	 * @return void
	 */
	public function on_activation() {

		$this->data['activated_at'] = time();
		$this->save_data();
	}

	/**
	 * Track the visits to the plugin settings page by incrementing the "settings_visits" counter in the plugin data.
	 *
	 * @param \WP_Screen $screen The current admin screen object used to check if we are on the plugin settings page.
	 *
	 * @return void
	 */
	public function track_settings_visit( $screen ) {

		if ( ! $this->settings_screen_id ) {
			return;
		}

		if ( $screen->id !== $this->settings_screen_id ) {
			return;
		}

		++$this->data['settings_visits'];

		$this->save_data();
	}

	/**
	 * Check if the current admin screen is one of the plugin screens by checking if the
	 * specified screen prefix.
	 *
	 * @return bool
	 */
	private function is_plugin_screen() {

		if ( ! $this->plugin_prefix ) {
			return false;
		}

		$screen = get_current_screen();

		if ( ! $screen ) {
			return false;
		}

		return strpos( $screen->id, $this->plugin_prefix ) !== false;
	}

	/**
	 * Print all notices for the current plugin screen.
	 *
	 * This method is intended to be called by the custom `daextpnm_after_header_bar` hook so that notices appear right
	 * after the plugin admin toolbar.
	 *
	 * @return void
	 */
	public function print_notices() {

		if ( ! $this->is_plugin_screen() ) {
			return;
		}

		wp_nonce_field( 'daextpnm_notice', 'daextpnm_notice_nonce' );

		$this->maybe_show_doc_notice();
		$this->maybe_show_review_notice();
	}

	/**
	 * Show the documentation notice if it hasn't been dismissed.
	 *
	 * @return void
	 */
	private function maybe_show_doc_notice() {

		// Only users who can edit posts should see plugin notices.
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		$plugin = $this->data;

		$doc_notice = $plugin['doc_notice'];

		if ( $doc_notice['dismissed'] ) {
			return;
		}

		// Mark that the documentation notice has been displayed in this request.
		$this->doc_notice_displayed = true;

		echo '<div class="daextpnm-notice daextpnm-notice-info">';
		echo '<p>';
		// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain
		echo '<strong>' . esc_html__( 'New to the plugin?', $this->plugin_slug ) . '</strong><br>';
		// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain
		echo esc_html__( 'Start with the Quick Start Guide.', $this->plugin_slug );
		echo '</p>';

		echo '<div class="daextpnm-notice-actions">';

		// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain
		echo '<a class="button button-primary daextpnm-button" target="_blank" href="' . esc_url( $this->docs_url ) . '">' . esc_html__( 'View quick start guide', $this->plugin_slug ) . '</a> ';

		// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain
		echo '<a href="#" class="daextpnm-dismiss-doc daextpnm-button-link" data-plugin="' . esc_attr( $this->plugin_slug ) . '">' . esc_html__( 'Dismiss', $this->plugin_slug ) . '</a>';

		echo '</div>';

		echo '</div>';
	}

	/**
	 * Show the review notice if the following conditions are met:
	 *
	 * - The notice hasn't been already rated by clicking the "Rate Now" button
	 * - The notice hasn't been already dismissed using the "Never Show" button
	 * - The plugin has activated at least 12 days ago.
	 * - The plugin settings has been visited at least 3 times
	 * - The current time is greater than the "next_show" timestamp (if set), which is updated when the user clicks the
	 * "Ask me again later" button
	 *
	 * @return void
	 */
	private function maybe_show_review_notice() {

		// Only users who can edit posts should see plugin notices.
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		$plugin = $this->data;
		$review = $plugin['review_notice'];

		/**
		 * If the documentation notice has been displayed in this request, do not show the review notice to avoid
		 * showing both notices at the same time.
		 */
		if ( $this->doc_notice_displayed ) {
			return;
		}

		if ( $review['never_show'] ) {
			return;
		}

		if ( time() < $plugin['activated_at'] + ( 12 * DAY_IN_SECONDS ) ) {
			return;
		}

		if ( $plugin['settings_visits'] < 3 ) {
			return;
		}

		if ( ! $this->feature_used ) {
			return;
		}

		if ( $review['next_show'] && time() < $review['next_show'] ) {
			return;
		}

		echo '<div class="daextpnm-notice daextpnm-notice-info">';
		echo '<p>';
		// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain
		echo '<strong>' . esc_html__( 'Enjoying the plugin?', $this->plugin_slug ) . '</strong><br>';
		// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain
		echo esc_html__( 'If you find it helpful, please consider leaving a review on WordPress.org. Your feedback helps us improve and reach more users.', $this->plugin_slug );
		echo '</p>';

		echo '<div class="daextpnm-notice-actions">';

		// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain
		echo '<a class="button button-primary daextpnm-button" target="_blank" href="' . esc_url( $this->review_url ) . '">' . esc_html__( 'Rate now', $this->plugin_slug ) . '</a> ';

		// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain
		echo '<a href="#" class="daextpnm-review-later button daextpnm-button daextpnm-button-secondary" data-plugin="' . esc_attr( $this->plugin_slug ) . '">' . esc_html__( 'Remind me later', $this->plugin_slug ) . '</a>';

		// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain
		echo '<a href="#" class="daextpnm-review-never daextpnm-button-link" data-plugin="' . esc_attr( $this->plugin_slug ) . '">' . esc_html__( 'Never show again', $this->plugin_slug ) . '</a>';

		echo '</div>';

		echo '</div>';
	}

	/**
	 * Ajax handler used to update the notice data when the user interacts with the notices by clicking the "Dismiss",
	 * "Rate now", "Ask me again later" or "Never show again" buttons.
	 *
	 * @return void
	 */
	public function handle_ajax() {

		// Only users who can edit posts should be able to perform notice actions.
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die();
		}

		// Nonce verification.
		check_admin_referer( 'daextpnm_notice', 'nonce' );

		$action = isset( $_POST['notice_action'] ) ? sanitize_text_field( wp_unslash( $_POST['notice_action'] ) ) : null;

		// Check if $action has one of the valid values.
		if ( ! in_array( $action, array( 'doc_dismiss', 'review_later', 'review_never' ), true ) ) {
			wp_die();
		}

		if ( ! isset( $this->data ) ) {
			wp_die();
		}

		if ( 'doc_dismiss' === $action ) {

			$this->data['doc_notice']['dismissed'] = true;
		}

		if ( 'review_later' === $action ) {

			$this->data['review_notice']['next_show'] = time() + ( 30 * DAY_IN_SECONDS );
		}

		if ( 'review_never' === $action ) {

			$this->data['review_notice']['never_show'] = true;
		}

		$this->save_data();

		wp_die();
	}
}
