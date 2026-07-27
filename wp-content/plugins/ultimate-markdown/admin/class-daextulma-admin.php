<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package ultimate-markdown
 */

/**
 * This class should be used to work with the administrative side of WordPress.
 */
class Daextulma_Admin {

	/**
	 * The instance of this class.
	 *
	 * @var null
	 */
	protected static $instance = null;

	/**
	 * The instance of the shared class.
	 *
	 * @var Daextulma_Shared|null
	 */
	private $shared = null;

	/**
	 * The screen id of the "Documents" menu.
	 *
	 * @var null
	 */
	private $screen_id_documents = null;

	/**
	 * The screen id of the "Tools" menu.
	 *
	 * @var null
	 */
	private $screen_id_tools = null;

	/**
	 * The screen id of the "Options" menu.
	 *
	 * @var null
	 */
	private $screen_id_options = null;

	/**
	 * Instance of the class used to handle Front Matter.
	 *
	 * @var null
	 */
	private $front_matter = null;

	/**
	 * Instance of the class used to generate the back-end menus.
	 *
	 * @var null
	 */
	private $menu_elements = null;

	/**
	 * Constructor.
	 */
	private function __construct() {

		// assign an instance of the plugin info.
		$this->shared = Daextulma_Shared::get_instance();

		// Load admin stylesheets and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the admin menu.
		add_action( 'admin_menu', array( $this, 'me_add_admin_menu' ) );

		// this hook is triggered during the creation of a new blog.
		add_action( 'wpmu_new_blog', array( $this, 'new_blog_create_options_and_tables' ), 10, 6 );

		// this hook is triggered during the deletion of a blog.
		add_action( 'delete_blog', array( $this, 'delete_blog_delete_options_and_tables' ), 10, 1 );

		// Require and instantiate the class used to handle the exports.
		require_once $this->shared->get( 'dir' ) . 'admin/inc/class-daextulma-export.php';
		Daextulma_Export::get_instance();

		// Require and instantiate the class used to handle Front Matter.
		require_once $this->shared->get( 'dir' ) . 'admin/inc/class-daextulma-front-matter.php';
		$this->front_matter = new Daextulma_Front_Matter( $this->shared );

		// Register the support of the 'custom-fields' to all the post type with UI.
		add_action( 'init', array( $this, 'register_support_on_post_types' ), 100 );

		// Require and instantiate the related classes used to handle the menus.
		add_action( 'init', array( $this, 'handle_menus' ) );

	}

	/**
	 * Return an instance of this class.
	 *
	 * @return self|null
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * If we are in one of the plugin back-end menus require and instantiate the related class used to handle the menu.
	 *
	 * @return void
	 */
	public function handle_menus() {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce non-necessary for menu selection.
		$page_query_param = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : null;

		// Require and instantiate the class used to register the menu options.
		if ( null !== $page_query_param ) {

			$config = array(
				'admin_toolbar' => array(
					'items'      => array(
						array(
							'link_text' => __( 'Documents', 'ultimate-markdown' ),
							'link_url'  => admin_url( 'admin.php?page=daextulma-documents' ),
							'icon'      => 'file-06',
							'menu_slug' => 'daextulma-document',
						),
						array(
							'link_text' => __( 'Tools', 'ultimate-markdown' ),
							'link_url'  => admin_url( 'admin.php?page=daextulma-tools' ),
							'icon'      => 'tool-02',
							'menu_slug' => 'daextulma-tool',
						),
						array(
							'link_text' => __( 'Options', 'ultimate-markdown' ),
							'link_url'  => admin_url( 'admin.php?page=daextulma-options' ),
							'icon'      => 'settings-01',
							'menu_slug' => 'daextulma-options',
						),
					),
					'more_items' => array(
						array(
							'link_text' => __( 'Categories', 'ultimate-markdown' ),
							'link_url'  => 'https://daext.com/ultimate-markdown/#features',
							'pro_badge' => true,
						),
						array(
							'link_text' => __( 'Import to Classic Editor', 'ultimate-markdown' ),
							'link_url'  => 'https://daext.com/ultimate-markdown/#features',
							'pro_badge' => true,
						),
						array(
							'link_text' => __( 'Export Single Posts', 'ultimate-markdown' ),
							'link_url'  => 'https://daext.com/ultimate-markdown/#features',
							'pro_badge' => true,
						),
						array(
							'link_text' => __( 'Bulk Export Posts', 'ultimate-markdown' ),
							'link_url'  => 'https://daext.com/ultimate-markdown/#features',
							'pro_badge' => true,
						),
						array(
							'link_text' => __( 'Automatic Image Upload', 'ultimate-markdown' ),
							'link_url'  => 'https://daext.com/ultimate-markdown/#features',
							'pro_badge' => true,
						),
						array(
							'link_text' => __( 'REST API Endpoints', 'ultimate-markdown' ),
							'link_url'  => 'https://daext.com/ultimate-markdown/#features',
							'pro_badge' => true,
						),
						array(
							'link_text' => __( 'Markdown Parser Configuration', 'ultimate-markdown' ),
							'link_url'  => 'https://daext.com/ultimate-markdown/#features',
							'pro_badge' => true,
						),
						array(
							'link_text' => __( 'Front Matter', 'ultimate-markdown' ),
							'link_url'  => 'https://daext.com/ultimate-markdown/#features',
							'pro_badge' => true,
						),
					),
				),
			);

			// The parent class.
			require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/class-daextulma-menu-elements.php';

			// Use the correct child class based on the page query parameter.
			if ( 'daextulma-documents' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-daextulma-document-menu-elements.php';
				$this->menu_elements = new Daextulma_Document_Menu_Elements( $this->shared, $page_query_param, $config );
			}
			if ( 'daextulma-tools' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-daextulma-tools-menu-elements.php';
				$this->menu_elements = new Daextulma_Tools_Menu_Elements( $this->shared, $page_query_param, $config );
			}
			if ( 'daextulma-options' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-daextulma-options-menu-elements.php';
				$this->menu_elements = new Daextulma_Options_Menu_Elements( $this->shared, $page_query_param, $config );
			}
		}

	}

	/**
	 * Register the support of the 'custom-fields' to all the post type with UI.
	 *
	 * The 'custom-fields' support is required by the sidebar components that use meta data. Without the
	 * 'custom-fields' support associated with the posts, the following meta data can't be used by the sidebar
	 * components and a JavaScript error breaks the editor.
	 *
	 * - _import_markdown_pro_load_document_selector
	 * - _import_markdown_pro_submit_text_textarea
	 *
	 * See: https://developer.wordpress.org/reference/functions/add_post_type_support/
	 */
	public function register_support_on_post_types() {
		// Get the post types with UI.
		$available_post_types_a = get_post_types(
			array(
				'show_ui' => true,
			)
		);

		// Add the 'custom-fields' support to the post types with UI.
		foreach ( $available_post_types_a as $available_post_type ) {
			add_post_type_support( $available_post_type, 'custom-fields' );
		}
	}

	/**
	 * Enqueue admin-specific styles.
	 *
	 * @return void
	 */
	public function enqueue_admin_styles() {

		$screen = get_current_screen();

		// Menu documents.
		if ( $screen->id === $this->screen_id_documents ) {

			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework-menu/main.css', array(), $this->shared->get( 'ver' ) );

		}

		// Menu Tools.
		if ( $screen->id === $this->screen_id_tools ) {

			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework-menu/main.css', array(), $this->shared->get( 'ver' ) );

		}

		// Menu Options.
		if ( $screen->id === $this->screen_id_options ) {

			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework-menu/main.css', array( 'wp-components' ), $this->shared->get( 'ver' ) );

		}
	}

	/**
	 * Enqueue admin-specific JavaScript.
	 *
	 * @return void
	 */
	public function enqueue_admin_scripts() {

		$screen = get_current_screen();

		$wp_localize_script_data = array(
			'deleteText' => wp_strip_all_tags( __( 'Delete', 'ultimate-markdown' ) ),
			'cancelText' => wp_strip_all_tags( __( 'Cancel', 'ultimate-markdown' ) ),
		);

		// General.
		wp_enqueue_script( $this->shared->get( 'slug' ) . '-general', $this->shared->get( 'url' ) . 'admin/assets/js/general.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

		// Menu documents.
		if ( $screen->id === $this->screen_id_documents ) {

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-menu/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-marked',
				$this->shared->get( 'url' ) . 'admin/assets/inc/marked/marked.min.js',
				array(),
				$this->shared->get( 'ver' ),
				true
			);

			// DOMPurify.
			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-dompurify',
				$this->shared->get( 'url' ) . 'admin/assets/inc/DOMPurify/dist/purify.min.js',
				array( 'jquery' ),
				$this->shared->get( 'ver' ),
				true
			);

			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-menu-documents',
				$this->shared->get( 'url' ) . 'admin/assets/js/menu-documents.js',
				array(
					'jquery',
					'jquery-ui-dialog',
					$this->shared->get( 'slug' ) . '-marked',
					$this->shared->get( 'slug' ) . '-dompurify',
					'wp-api-fetch'
				),
				$this->shared->get( 'ver' ),
				true
			);

			wp_localize_script(
				$this->shared->get( 'slug' ) . '-menu-documents',
				'objectL10n',
				$wp_localize_script_data
			);

			// Store the JavaScript parameters in the window.DAEXTULMA_PARAMETERS object.
			$initialization_script  = 'window.DAEXTULMA_PARAMETERS = {';
			$initialization_script .= 'adminUrl: "' . admin_url() . '",';
			$initialization_script .= 'livePreviewMarkdownParser: "' . get_option('daextulma_live_preview_markdown_parser') . '",';
			$initialization_script .= 'livePreviewPhpAutoRefresh: ' . (1 === intval(get_option('daextulma_live_preview_php_auto_refresh')) ? 'true' : 'false') . ',';
			$initialization_script .= 'livePreviewPhpDebounceDelay: "' . get_option('daextulma_live_preview_php_debounce_delay') . '"';
			$initialization_script .= '};';

			wp_add_inline_script( $this->shared->get( 'slug' ) . '-menu-documents', $initialization_script, 'before' );

		}

		// Menu Tools.
		if ( $screen->id === $this->screen_id_tools ) {

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-menu/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

			// Store the JavaScript parameters in the window.DAEXTULMA_PARAMETERS object.
			$initialization_script  = 'window.DAEXTULMA_PARAMETERS = {';
			$initialization_script .= 'maxFileUploads: "' . intval(ini_get('max_file_uploads'), 10) . '"';
			$initialization_script .= '};';

			wp_add_inline_script( $this->shared->get( 'slug' ) . '-menu', $initialization_script, 'before' );


		}

		// Menu Options.
		if ( $screen->id === $this->screen_id_options ) {

			// Store the JavaScript parameters in the window.DAEXTULMA_PARAMETERS object.
			$initialization_script  = 'window.DAEXTULMA_PARAMETERS = {';
			$initialization_script .= 'ajax_url: "' . admin_url( 'admin-ajax.php' ) . '",';
			$initialization_script .= 'admin_url: "' . get_admin_url() . '",';
			$initialization_script .= 'site_url: "' . get_site_url() . '",';
			$initialization_script .= 'plugin_url: "' . $this->shared->get( 'url' ) . '",';
			$initialization_script .= 'options_configuration_pages: ' . wp_json_encode( $this->shared->menu_options_configuration() );
			$initialization_script .= '};';

			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-menu-options',
				$this->shared->get( 'url' ) . 'admin/react/options-menu/build/index.js',
				array( 'wp-element', 'wp-api-fetch', 'wp-i18n', 'wp-components' ),
				$this->shared->get( 'ver' ),
				true
			);

			wp_add_inline_script( $this->shared->get( 'slug' ) . '-menu-options', $initialization_script, 'before' );

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-menu/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

		}

		// Load the assets for the post editor.
		$available_post_types_a = get_post_types(
			array(
				'show_ui' => true,
			)
		);

		// Remove the "attachment" post type.
		$available_post_types_a = array_diff( $available_post_types_a, array( 'attachment' ) );

		/**
		 * Enqueue Marked and DOMPurify only for post types with a UI.
		 *
		 * These libraries are required by the three block editor sidebar sections and by the three classic editor meta
		 * boxes.
		 */
		if ( in_array( $screen->id, $available_post_types_a, true ) ) {

			// Marked.
			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-marked',
				$this->shared->get( 'url' ) . 'admin/assets/inc/marked/marked.min.js',
				array(),
				$this->shared->get( 'ver' ),
				true
			);

			// DOMPurify.
			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-dompurify',
				$this->shared->get( 'url' ) . 'admin/assets/inc/DOMPurify/dist/purify.min.js',
				array( 'jquery' ),
				$this->shared->get( 'ver' ),
				true
			);

		}
	}

	/**
	 * Plugin activation.
	 *
	 * @param bool $networkwide True if the plugin is being activated network-wide.
	 *
	 * @return void
	 */
	public static function ac_activate( $networkwide ) {

		/**
		 * Delete options and tables for all the sites in the network.
		 */
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			/**
			 * If this is a "Network Activation" create the options and tables
			 * for each blog.
			 */
			if ( $networkwide ) {

				// Get the current blog id.
				global $wpdb;
				$current_blog = $wpdb->blogid;

				// Create an array with all the blog ids.
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

				// Iterate through all the blogs.
				foreach ( $blogids as $blog_id ) {

					// Switch to the iterated blog.
					switch_to_blog( $blog_id );

					// Create options and tables for the iterated blog.
					self::ac_initialize_options();
					self::ac_create_database_tables();

				}

				// Switch to the current blog.
				switch_to_blog( $current_blog );

			} else {

				/**
				 * If this is not a "Network Activation" create options and
				 * tables only for the current blog.
				 */
				self::ac_initialize_options();
				self::ac_create_database_tables();

			}
		} else {

			/**
			 * If this is not a multisite installation create options and
			 * tables only for the current blog.
			 */
			self::ac_initialize_options();
			self::ac_create_database_tables();

		}
	}

	/**
	 * Create the options and tables for the newly created blog.
	 *
	 * @param int $blog_id The id of the blog.
	 *
	 * @return void
	 */
	public function new_blog_create_options_and_tables( $blog_id ) {

		global $wpdb;

		/**
		 * If the plugin is "Network Active" create the options and tables for
		 * this new blog.
		 */
		if ( is_plugin_active_for_network( 'offline-writer/init.php' ) ) {

			// Get the id of the current blog.
			$current_blog = $wpdb->blogid;

			// Switch to the blog that is being activated.
			switch_to_blog( $blog_id );

			// Create options and database tables for the new blog.
			$this->ac_initialize_options();
			$this->ac_create_database_tables();

			// Switch to the current blog.
			switch_to_blog( $current_blog );

		}
	}

	/**
	 * Delete options and tables for the deleted blog.
	 *
	 * @param int $blog_id The id of the blog.
	 *
	 * @return void
	 */
	public function delete_blog_delete_options_and_tables( $blog_id ) {

		global $wpdb;

		// Get the id of the current blog.
		$current_blog = $wpdb->blogid;

		// Switch to the blog that is being activated.
		switch_to_blog( $blog_id );

		// Delete options and database tables for the deleted blog.
		$this->un_delete_options();
		$this->un_delete_database_tables();

		// Switch to the current blog.
		switch_to_blog( $current_blog );
	}

	/**
	 * Initialize plugin options.
	 *
	 * @return void
	 */
	public static function ac_initialize_options() {

		if ( intval( get_option( 'daextulma_options_version' ), 10 ) < 2 ) {

			// assign an instance of Daextulma_Shared.
			$shared = Daextulma_Shared::get_instance();

			foreach ( $shared->get( 'options' ) as $key => $value ) {
				add_option( $key, $value );
			}

			// Update options version.
			update_option( 'daextulma_options_version', '2' );

		}
	}

	/**
	 * Create the plugin database tables.
	 *
	 * @return void
	 */
	public static function ac_create_database_tables() {

		// assign an instance of Daextulma_Shared.
		$shared = Daextulma_Shared::get_instance();

		global $wpdb;

		// Get the database character collate that will be appended at the end of each query.
		$charset_collate = $wpdb->get_charset_collate();

		// Check database version and create the database.
		if ( intval( get_option( $shared->get( 'slug' ) . '_database_version' ), 10 ) < 1 ) {

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			// Create *prefix*_document.
			$table_name = $wpdb->prefix . $shared->get( 'slug' ) . '_document';
			$sql        = "CREATE TABLE $table_name (
                document_id BIGINT(20) UNSIGNED AUTO_INCREMENT,
                title TEXT NOT NULL DEFAULT '',
                content LONGTEXT NOT NULL DEFAULT '',
                PRIMARY KEY  (document_id)
            ) $charset_collate";
			dbDelta( $sql );

			// Update database version.
			update_option( $shared->get( 'slug' ) . '_database_version', '1' );

		}
	}

	/**
	 * Plugin delete.
	 *
	 * @return void
	 */
	public static function un_delete() {

		/**
		 * Delete options and tables for all the sites in the network.
		 */
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			// Get the current blog id.
			global $wpdb;
			$current_blog = $wpdb->blogid;

			// Create an array with all the blog ids.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

			// Iterate through all the blogs.
			foreach ( $blogids as $blog_id ) {

				// Switch to the iterated blog.
				switch_to_blog( $blog_id );

				// Delete options and tables for the iterated blog.
				self::un_delete_options();
				self::un_delete_database_tables();

			}

			// Switch to the current blog.
			switch_to_blog( $current_blog );

		} else {

			/**
			 * *f this is not a multisite installation delete options and
			 * tables only for the current blog.
			 */
			self::un_delete_options();
			self::un_delete_database_tables();

		}
	}

	/**
	 * Delete plugin options.
	 *
	 * @return void
	 */
	public static function un_delete_options() {

		// Assign an instance of Daextulma_Shared.
		$shared = Daextulma_Shared::get_instance();

		foreach ( $shared->get( 'options' ) as $key => $value ) {
			delete_option( $key );
		}

		// Delete the option used by the DAEXT_Notices_Manager class to persist the notices state.
		delete_option( $shared->get( 'slug' ) . '_notices_state' );

	}

	/**
	 * Delete plugin database tables.
	 *
	 * @return void
	 */
	public static function un_delete_database_tables() {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( "DROP TABLE {$wpdb->prefix}daextulma_document" );
	}

	/**
	 * Register the admin menu.
	 *
	 * @return void
	 */
	public function me_add_admin_menu() {

		$icon_svg = '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
		     viewBox="0 0 40 40" style="enable-background:new 0 0 40 40;" xml:space="preserve">
		<style type="text/css">
		    .st1{fill:#fff;}
		</style>
		    <g id="Layer_5_copy">
			<g>
				<path class="st1" d="M2.9,9.6c-0.5,0-1,0.4-1,1v18.8c0,0.5,0.4,1,1,1h34.2c0.5,0,1-0.4,1-1V10.6c0-0.5-0.4-1-1-1H2.9z M0,10.6
					C0,9,1.3,7.7,2.9,7.7h34.2c1.6,0,2.9,1.3,2.9,2.9v18.8c0,1.6-1.3,2.9-2.9,2.9H2.9C1.3,32.3,0,31,0,29.4V10.6z"/>
		        <path class="st1" d="M5.8,26.5V13.5h3.8l3.8,4.8l3.8-4.8h3.8v13.1h-3.8V19l-3.8,4.8L9.6,19v7.5H5.8z M29.8,26.5L24,20.2h3.8v-6.7h3.8v6.7h3.8
					L29.8,26.5z"/>
			</g>
		</g>
		</svg>';

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Base64 encoding is used to embed the SVG in the HTML.
		$icon_svg = 'data:image/svg+xml;base64,' . base64_encode( $icon_svg );

		add_menu_page(
			esc_html__( 'UM', 'ultimate-markdown' ),
			esc_html__( 'Markdown', 'ultimate-markdown' ),
			get_option( $this->shared->get( 'slug' ) . '_documents_menu_required_capability' ),
			$this->shared->get( 'slug' ) . '-documents',
			array( $this, 'me_display_menu_documents' ),
			$icon_svg
		);

		$this->screen_id_documents = add_submenu_page(
			$this->shared->get( 'slug' ) . '-documents',
			esc_html__( 'UM - Documents', 'ultimate-markdown' ),
			esc_html__( 'Documents', 'ultimate-markdown' ),
			get_option( $this->shared->get( 'slug' ) . '_documents_menu_required_capability' ),
			$this->shared->get( 'slug' ) . '-documents',
			array( $this, 'me_display_menu_documents' )
		);

		$this->screen_id_tools = add_submenu_page(
			$this->shared->get( 'slug' ) . '-documents',
			esc_html__( 'UM - Tools', 'ultimate-markdown' ),
			esc_html__( 'Tools', 'ultimate-markdown' ),
			get_option( $this->shared->get( 'slug' ) . '_tools_menu_required_capability' ),
			$this->shared->get( 'slug' ) . '-tools',
			array( $this, 'me_display_menu_tools' )
		);

		$this->screen_id_options = add_submenu_page(
			$this->shared->get( 'slug' ) . '-documents',
			esc_html__( 'UM - Options', 'ultimate-markdown' ),
			esc_html__( 'Options', 'ultimate-markdown' ),
			'manage_options',
			$this->shared->get( 'slug' ) . '-options',
			array( $this, 'me_display_menu_options' )
		);

		add_submenu_page(
			$this->shared->get( 'slug' ) . '-documents',
			esc_html__( 'Help & Support', 'ultimate-markdown' ),
			esc_html__( 'Help & Support', 'ultimate-markdown' ) . '<i class="dashicons dashicons-external" style="font-size:12px;vertical-align:-2px;height:10px;"></i>',
			'manage_options',
			'https://daext.com/kb/ultimate-markdown/',
		);
	}

	/**
	 * Includes the documents view.
	 *
	 * @return void
	 */
	public function me_display_menu_documents() {
		include_once 'view/documents.php';
	}

	/**
	 * Includes the tools view.
	 *
	 * @return void
	 */
	public function me_display_menu_tools() {
		include_once 'view/tools.php';
	}

	/**
	 * Includes the options view.
	 *
	 * @return void
	 */
	public function me_display_menu_options() {
		include_once 'view/options.php';
	}

}
