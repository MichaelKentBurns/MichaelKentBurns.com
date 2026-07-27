<?php
/**
 * The meta boxes functionality of the plugin.
 *
 * @package ultimate-markdown
 */

/**
 * This class should be used to handle the meta boxes in the post editor.
 */
class Daextulma_Meta_Boxes {

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
	 * Constructor.
	 */
	private function __construct() {

		// Assign an instance of the shared class.
		$this->shared = Daextulma_Shared::get_instance();

		// Register meta boxes.
		add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ) );

		// Enqueue meta box assets.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_meta_box_assets' ) );

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
	 * Register all meta boxes.
	 *
	 * @return void
	 */
	public function register_meta_boxes() {

		// Check capability before registering meta boxes.
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		// Get post types with UI.
		$post_types = get_post_types( array( 'show_ui' => true ) );

		// Remove attachment post type.
		$post_types = array_diff( $post_types, array( 'attachment' ) );

		// Ref: https://make.wordpress.org/core/2018/11/07/meta-box-compatibility-flags/ .
		$classic_editor_args =
			array(

				/*
				 * It's not confirmed that this meta box works in the block editor.
				 */
				'__block_editor_compatible_meta_box' => false,

				/*
				 * This meta box should only be loaded in the classic editor interface, and the block editor
				 * should not display it.
				 */
				'__back_compat_meta_box'             => true,

			);

		foreach ( $post_types as $post_type ) {

			// Import Document meta box.
			add_meta_box(
				'daextulma-import-document',
				__( 'Import Markdown', 'ultimate-markdown' ),
				array( $this, 'render_import_document_meta_box' ),
				$post_type,
				'side',
				'default',
				$classic_editor_args
			);

			// Load Document meta box.
			add_meta_box(
				'daextulma-load-document',
				__( 'Load Markdown', 'ultimate-markdown' ),
				array( $this, 'render_load_document_meta_box' ),
				$post_type,
				'side',
				'default',
				$classic_editor_args
			);

			// Submit Text meta box.
			add_meta_box(
				'daextulma-submit-text',
				__( 'Insert Markdown', 'ultimate-markdown' ),
				array( $this, 'render_submit_text_meta_box' ),
				$post_type,
				'side',
				'default',
				$classic_editor_args
			);

		}

	}

	/**
	 * Render the Import Document meta box.
	 *
	 * @return void
	 */
	public function render_import_document_meta_box() {

		// Check capability before enqueuing assets.
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		?>
		<div class="daextulma-import-document-metabox">
			<div class="daextulma-upload-wrapper">
				<input
					type="file"
					id="daextulma-import-file"
					accept=".md,.markdown,.mdown,.mkdn,.mkd,.mdwn,.mdtxt,.mdtext,.text,.txt,.zip"
					style="display: none;"
				/>
				<button type="button" class="button button-secondary" id="daextulma-import-trigger">
					<?php esc_html_e( 'Upload file and import', 'ultimate-markdown' ); ?>
				</button>
			</div>
			<div class="daextulma-import-status" style="margin-top: 10px; display: none;"></div>
			<p class="daextulma-import-document-metabox-description description">
				<?php esc_html_e( 'Uploads a Markdown file and imports its content into the editor.', 'ultimate-markdown' ); ?>
			</p>
		</div>
		<?php

	}

	/**
	 * Render the Load Document meta box.
	 *
	 * @param WP_Post $post The post object.
	 * @return void
	 */
	public function render_load_document_meta_box( $post ) {

		// Get the stored document ID.
		$document_id = get_post_meta( $post->ID, '_import_markdown_pro_load_document_selector', true );

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$document_a = $wpdb->get_results(
				"SELECT document_id, title FROM {$wpdb->prefix}daextulma_document ORDER BY document_id DESC",
				ARRAY_A
		);

		$document_a_alt   = array();
		$document_a_alt[] = array(
				'value' => 0,
				'label' => __( 'Select a document…', 'ultimate-markdown' ),
		);
		foreach ( $document_a as $value ) {
			$document_a_alt[] = array(
					'value' => intval( $value['document_id'], 10 ),
					'label' => stripslashes( $value['title'] ),
			);
		}

		?>
		<div class="daextulma-load-document-metabox">
			<p class="daextulma-document-selector-label-wrapper">
				<label class="daextulma-document-selector-label" for="daextulma-document-selector"><?php esc_html_e('Document', 'ultimate-markdown'); ?></label>
			</p>
			<div class="daextulma-document-selector-wrapper">
				<select id="daextulma-document-selector" name="daextulma_document_id" class="widefat">
					<?php
					// Get documents from the same source as the block editor.
					$documents = $document_a_alt;
					foreach ( $documents as $document ) {
						printf(
								'<option value="%s"%s>%s</option>',
								esc_attr( $document['value'] ),
								selected( $document_id, $document['value'], false ),
								esc_html( $document['label'] )
						);
					}
					?>
				</select>
				<a href="#" id="daextulma-load-document-trigger" class=" button"><?php esc_html_e('Load', 'ultimate-markdown'); ?></a>
			</div>
			<div class="daextulma-load-status notice notice-success inline" style="display: none;"></div>
			<p class="daextulma-load-document-metabox-description description">
				<?php esc_html_e( 'Loads the selected Markdown document into the editor.', 'ultimate-markdown' ); ?>
			</p>
		</div>
		<?php

	}

	/**
	 * Render the Submit Text meta box.
	 *
	 * @return void
	 */
	public function render_submit_text_meta_box() {

		?>
		<div class="daextulma-submit-text-metabox">
			<p class="daextulma-submit-text-textarea-label-wrapper">
				<label class="daextulma-submit-text-textarea-label" for="daextulma-submit-text-textarea"><?php esc_html_e('Markdown', 'ultimate-markdown'); ?></label>
			</p>
			<div class="daextulma-submit-text-textarea-wrapper">
			<textarea
					id="daextulma-submit-text-textarea"
					class="widefat"
					rows="10"
			></textarea>
				<div class="daextulma-submit-text-actions">
					<a href="#" id="daextulma-submit-text-trigger" class="button"><?php esc_html_e( 'Insert', 'ultimate-markdown' ); ?></a>
				</div>
			</div>
			<div class="daextulma-submit-text-status notice notice-success inline" style="display: none;"></div>
			<p class="daextulma-submit-text-metabox-description description">
				<?php esc_html_e( 'Inserts the Markdown above into the editor.', 'ultimate-markdown' ); ?>
			</p>
		</div>
		<?php

	}

	/**
	 * Enqueue scripts and styles for meta boxes.
	 *
	 * @param string $hook The current admin page hook.
	 * @return void
	 */
	public function enqueue_meta_box_assets( $hook ) {

		// Only load on post edit screens.
		if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
			return;
		}

		// Ensure we are in the classic editor, not the block editor.
		$screen = get_current_screen();
		if ( $screen && method_exists( $screen, 'is_block_editor' ) && $screen->is_block_editor() ) {
			return;
		}

		// Enqueue CSS.
		wp_enqueue_style(
			$this->shared->get( 'slug' ) . '-meta-boxes',
			$this->shared->get( 'url' ) . 'admin/assets/css/meta-boxes.css',
			array(),
			$this->shared->get( 'ver' )
		);

		// Enqueue JS.
		wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-meta-boxes-utilities',
				$this->shared->get( 'url' ) . 'admin/assets/js/meta-boxes-utilities.js',
				array(),
				$this->shared->get( 'ver' ),
				true
		);
		wp_enqueue_script(
			$this->shared->get( 'slug' ) . '-meta-boxes',
			$this->shared->get( 'url' ) . 'admin/assets/js/meta-boxes.js',
			array(
					$this->shared->get( 'slug' ) . '-meta-boxes-utilities',
					'jquery'
			),
			$this->shared->get( 'ver' ),
			true
		);

		// Get the documents used to populate the selector in the "Load Document" meta box.
		$document_a_alt = $this->shared->get_documents_for_selector();

		// Store the JavaScript parameters in the window.DAEXTULMA_PARAMETERS object.
		$initialization_script  = 'window.DAEXTULMA_PARAMETERS = {';
		$initialization_script .= 'documents: ' . wp_json_encode( $document_a_alt ) . ',';
		$initialization_script .= 'ajaxUrl: "' . admin_url( 'admin-ajax.php' ) . '",';
		$initialization_script .= 'pluginDirectoryUrl: "' . $this->shared->get( 'url' ) . '",';
		$initialization_script .= 'editorMarkdownParser: "' . esc_js( get_option('daextulma_editor_markdown_parser') ) . '",';
		$initialization_script .= 'nonce: "' . wp_create_nonce( 'daextulma' ) . '",';
		$initialization_script .= 'restUrl: "' . esc_url_raw( rest_url() ) . '",';
		$initialization_script .= 'restNonce: "' . wp_create_nonce( 'wp_rest' ) . '",';
		$initialization_script .= '};';
		wp_add_inline_script( $this->shared->get( 'slug' ) . '-meta-boxes', $initialization_script, 'before' );

	}

}
