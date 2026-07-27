<?php
/**
 * This class should be used to handle the export of the documents stored in the plugin.
 *
 * @package ultimate-markdown
 */

/**
 * This class should be used to handle the export of the documents stored in the plugin.
 */
class Daextulma_Export {

	/**
	 * The instance of the class.
	 *
	 * @var null
	 */
	protected static $instance = null;

	/**
	 * An instance of the shared class.
	 *
	 * @var Daextulma_Shared
	 */
	private $shared = null;

	/**
	 * The constructor.
	 */
	private function __construct() {

		// Assign an instance of the plugin info.
		$this->shared = Daextulma_Shared::get_instance();

		// Export controller.
		add_action( 'init', array( $this, 'export_controller' ) );
	}

	/**
	 * Get the singleton instance of the class.
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
	 * The click on the "Export" button available in the "Export" menu is intercepted and the
	 *  method that generates the downloadable ZIP file that includes all the markdown files generated from the
	 *  documents stored in the plugin.
	 *
	 * @return void
	 */
	public function export_controller() {

		/**
		 * Intercept requests that come from the "Export" button of the
		 * "Ultimate Markdown -> Export" menu and generate the downloadable ZIP file.
		 */
		if ( isset( $_POST['daextulma_export'] ) ) {

			// Nonce verification.
			check_admin_referer( 'daextulma_tools_export', 'daextulma_tools_export_nonce' );

			// Verify capability.
			if ( ! current_user_can( get_option( $this->shared->get( 'slug' ) . '_tools_menu_required_capability' ) ) ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'ultimate-markdown' ) );
			}

			// Generates a ZIP file that includes all the documents stored in the plugin.
			$this->export_documents();

		}
	}

	/**
	 * Generates a ZIP file that includes all the documents stored in the plugin.
	 *
	 * @return false|void
	 */
	private function export_documents() {

		// Get the data from the 'connect' db.
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$document_a = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}daextulma_document ORDER BY document_id ASC", ARRAY_A );

		// Stripslash title and content in the document array.
		foreach ( $document_a as $key => $document ) {
			$document_a[ $key ]['title']   = stripslashes( $document['title'] );
			$document_a[ $key ]['content'] = stripslashes( $document['content'] );
		}

		// Create the zip archive.
		$this->generate_zip_with_markdown_files( $document_a );
	}

	/**
	 * Generates a ZIP file with all the documents.
	 *
	 * @param array $document_a The array of documents.
	 */
	private function generate_zip_with_markdown_files( $document_a ) {

		// Export Documents -------------------------------------------------------------------------------------------.
		$written_file_names = array();
		$archive_file_name = 'data-' . time() . '.zip';
		$file_path         = WP_CONTENT_DIR . '/uploads/daextulma_uploads/';

		// Create the upload directory of the plugin if the directory doesn't exist.
		wp_mkdir_p( $file_path );

		// If there are data iterate the array.
		if ( count( $document_a ) > 0 ) {

			// Renames the items with a duplicate name.
			$document_a = $this->shared->rename_duplicate_names_in_document( $document_a );

			// Add all the files in the upload folder.
			foreach ( $document_a as $document ) {

				$file_name    = sanitize_file_name( $document['title'] . '.md' );

				global $wp_filesystem;
				require_once ABSPATH . '/wp-admin/includes/file.php';
				WP_Filesystem();

				// Create the single markdown file in the plugin upload folder.
				$content = $document['content'];

				/**
				 * If a file with the same name already exists, add a suffix "[counter]" to the file name and try
				 * again.
				 */
				$counter = 0;
				while ( $wp_filesystem->exists( $file_path . '/' . $file_name ) ) {
					++$counter;
					$file_name = sanitize_file_name( $document['title'] ) . $counter .  '.md';
				}

				$wp_filesystem->put_contents( $file_path . '/' . $file_name, $content );
				$written_file_names[] = $file_name;

			}

			// Create and open a new zip archive.
			$zip = new ZipArchive();
			if ( $zip->open( $archive_file_name, ZIPARCHIVE::CREATE ) !== true ) {
				die( 'cannot open ' . esc_html( $archive_file_name ) );
			}

			// Add each files of the $file_name array to the archive.
			foreach ( $written_file_names as $files ) {
				$zip->addFile( $file_path . $files, $files );
			}
			$zip->close();

			// Generate the header of a zip file.
			header( 'Content-type: application/zip' );
			header( 'Content-Disposition: attachment; filename=' . $archive_file_name );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );

			global $wp_filesystem;
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Not necessary for zip files.
			echo $wp_filesystem->get_contents( $archive_file_name );

			// Delete all the files used to create the archive.
			foreach ( $written_file_names as $written_file_name ) {
				wp_delete_file( $file_path . $written_file_name );
			}
		} else {
			return false;
		}

		die();
	}
}
