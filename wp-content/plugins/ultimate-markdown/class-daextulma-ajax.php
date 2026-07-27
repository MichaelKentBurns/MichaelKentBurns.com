<?php
/**
 * This file contains the class Daextulma_Ajax, used to include ajax actions.
 *
 * @package ultimate-markdown
 */

/**
 * This class should be used to include ajax actions.
 */
class Daextulma_Ajax {

	/**
	 * The instance of the Daextulma_Ajax class.
	 *
	 * @var Daextulma_Ajax
	 */
	protected static $instance = null;

	/**
	 * The instance of the Daextulma_Shared class.
	 *
	 * @var Daextulma_Shared
	 */
	private $shared = null;

	/**
	 * An instance of the Front Matter class.
	 *
	 * @var Daextulma_Front_Matter|null
	 */
	private $front_matter = null;

	/**
	 * The constructor of the Daextulma_Ajax class.
	 */
	private function __construct() {

		// Assign an instance of the plugin info.
		$this->shared = Daextulma_Shared::get_instance();

		// Ajax requests for logged-in users --------------------------------------------------------------------------.
		add_action( 'wp_ajax_daextulma_import_document', array( $this, 'daextulma_import_document' ) );
		add_action( 'wp_ajax_daextulma_load_document', array( $this, 'daextulma_load_document' ) );
		add_action( 'wp_ajax_daextulma_submit_markdown', array( $this, 'daextulma_submit_markdown' ) );

		// Require and instantiate the class used to handle Front Matter.
		require_once $this->shared->get( 'dir' ) . 'admin/inc/class-daextulma-front-matter.php';
		$this->front_matter = new Daextulma_Front_Matter( $this->shared );
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
	 * Ajax handler used to retrieve the title and the content of the uploaded markdown file.
	 *
	 * This method is called when the "Import" button available in the post editor sidebar is clicked and then a markdown
	 * file is selected.
	 *
	 * @return void
	 */
	public function daextulma_import_document() {

		// Check the referer.
		if ( ! check_ajax_referer( 'daextulma', 'security', false ) ) {
			echo esc_html__( 'Invalid AJAX Request', 'ultimate-markdown');
			die();
		}

		// Check the capability.
		if ( ! current_user_can( 'edit_posts' ) ) {
			echo esc_html__( 'Invalid Capability', 'ultimate-markdown');
			die();
		}

		if ( isset( $_FILES['uploaded_file'] ) ) {

			// Sanitize the uploaded file.
			//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- The sanitization is performed with sanitize_uploaded_file().
			$file_data = $this->shared->sanitize_uploaded_file( $_FILES['uploaded_file'] );

			// Get the file content.
			$document = $this->shared->get_markdown_file_content( $file_data );

			// Get the Front Matter data.
			$front_matter = $this->front_matter->get( $document );

			// if the title is set with the YAML data use it. Otherwise, obtain the title from the file name.
			$title = isset( $front_matter['title'] ) ?
				$front_matter['title'] :
				sanitize_text_field( $this->shared->get_title_from_file_name( $file_data['name'] ) );

			// Echo the JSON data associated with the Markdown document.
			$this->shared->generate_markdown_document_json( $title, $document, $front_matter );

		}

	}

	/**
	 *
	 * Ajax handler used to return the title and the content of the submitted document id.
	 *
	 * This method is called when the user selects a document with the selector available in the "Load Document"
	 * component available in the post sidebar of the editor.
	 *
	 * @return void
	 */
	public function daextulma_load_document() {

		// Check the referer.
		if ( ! check_ajax_referer( 'daextulma', 'security', false ) ) {
			echo esc_html__( 'Invalid AJAX Request', 'ultimate-markdown');
			die();
		}

		// Check the capability.
		if ( ! current_user_can( 'edit_posts' ) ) {
			echo esc_html__( 'Invalid Capability', 'ultimate-markdown');
			die();
		}

		$document_id = isset( $_POST['document_id'] ) ? intval( $_POST['document_id'], 10 ) : 0;

		// Get the document object.
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$document_obj = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}daextulma_document WHERE document_id = %d ", $document_id )
		);

		if ( false !== $document_obj ) {

			// Get the Front Matter data.
			$front_matter = $this->front_matter->get( stripslashes( $document_obj->content ) );

			// if the title is set with Front Matter use it. Otherwise, use the file name as title.
			$title = isset( $front_matter['title'] ) ? stripslashes( $front_matter['title'] ) : $document_obj->title;

			// Echo the JSON data associated with the Markdown document.
			$this->shared->generate_markdown_document_json( $title, $document_obj->content, $front_matter );

		} else {

			die();

		}

	}

	/**
	 * Ajax handler used to return the title and the content of the submitted document.
	 *
	 * This method is called when the user clicks the "Submit Text" button of the "Load Document" sidebar section
	 * available in the post sidebar of the editor.
	 *
	 * @return void
	 */
	public function daextulma_submit_markdown() {

		// Check the referer.
		if ( ! check_ajax_referer( 'daextulma', 'security', false ) ) {
			echo esc_html__( 'Invalid AJAX Request', 'ultimate-markdown');
			die();
		}

		// Check the capability.
		if ( ! current_user_can( 'edit_posts' ) ) {
			echo esc_html__( 'Invalid Capability', 'ultimate-markdown');
			die();
		}

		/**
		 * Get raw markdown content, without sanitize_textarea_field(). This is because sanitize_textarea_field() is not
		 * applicable to the Markdown content, as it would remove or alter some of the Markdown syntax.
		 */
		$content = isset( $_POST['markdowntext'] ) ? wp_unslash( $_POST['markdowntext'] ) : '';

		// Get the Front Matter data.
		$front_matter = $this->front_matter->get( $content );

		// if the title is set with Front Matter use it. Otherwise, use the file name as title.
		$title = isset( $front_matter['title'] ) ? $front_matter['title'] : null;

		// Echo the JSON data associated with the Markdown document.
		$this->shared->generate_markdown_document_json( $title, $content, $front_matter );

	}
}
