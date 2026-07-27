<?php
/**
 * Class used to implement the back-end functionalities of the "Tools" menu.
 *
 * @package ultimate-markdown
 */

/**
 * Class used to implement the back-end functionalities of the "Tools" menu.
 */
class Daextulma_Tools_Menu_Elements extends Daextulma_Menu_Elements {

	/**
	 * The class used to handle Front Matter.
	 *
	 * @var Daextulma_Front_Matter The class used to handle Front Matter.
	 */
	private $front_matter = null;

	/**
	 * Constructor.
	 *
	 * @param object $shared The shared class.
	 * @param string $page_query_param The page query parameter.
	 * @param string $config The config parameter.
	 */
	public function __construct( $shared, $page_query_param, $config ) {

		parent::__construct( $shared, $page_query_param, $config );

		$this->menu_slug      = 'tool';
		$this->slug_plural    = 'tools';
		$this->label_singular = __( 'Tool', 'ultimate-markdown');
		$this->label_plural   = __( 'Tools', 'ultimate-markdown');

		// Require and instantiate the class used to handle Front Matter.
		require_once $this->shared->get( 'dir' ) . 'admin/inc/class-daextulma-front-matter.php';
		$this->front_matter = new Daextulma_Front_Matter( $this->shared );

		// Require and instantiate the class used to handle the exports.
		require_once $this->shared->get( 'dir' ) . 'admin/inc/class-daextulma-export.php';
		Daextulma_Export::get_instance();
	}

	/**
	 * Process the add/edit form submission of the menu. Specifically the following tasks are performed:
	 *
	 *  1. Sanitization
	 *  2. Validation
	 *  3. Database update
	 *
	 * @return false|void
	 */
	public function process_form() {

		// Process the Markdown file upload (import) ------------------------------------------------------------------.
		if ( isset( $_FILES['file_to_upload'] ) ) {

			// Nonce verification.
			check_admin_referer( 'daextulma_tools_import', 'daextulma_tools_import_nonce' );


					// Document destination.
					$generated_a   = array();
					$list_of_posts = array();

					// Process all the uploaded files.
					$num_files = isset( $_FILES['file_to_upload']['name'] ) ? count( $_FILES['file_to_upload']['name'] ) : 0;
					for ( $i = 0; $i < $num_files; $i++ ) {

						//phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.InputNotValidated -- The sanitization is performed with sanitize_uploaded_file().
						$file_data = $this->shared->sanitize_uploaded_file(
							array(
								'name'     => $_FILES['file_to_upload']['name'][ $i ],
								'type'     => $_FILES['file_to_upload']['type'][ $i ],
								'tmp_name' => $_FILES['file_to_upload']['tmp_name'][ $i ],
								'error'    => $_FILES['file_to_upload']['error'][ $i ],
								'size'     => $_FILES['file_to_upload']['size'][ $i ],
							)
						);
						//phpcs:enable

						// Get the markdown content of the file.
						$markdown_content = $this->shared->get_markdown_file_content( $file_data );

						$title       = sanitize_text_field( $this->shared->get_title_from_file_name( $file_data['name'] ) );

						// Create a new record in the "document" database table created by the plugin -----------------.
						global $wpdb;

						// phpcs:disable WordPress.DB.DirectDatabaseQuery
						$query_result = $wpdb->query(
							$wpdb->prepare(
								"INSERT INTO {$wpdb->prefix}daextulma_document SET 
                            title = %s,
                            content = %s",
								$title,
								$markdown_content
							)
						);

						if ( false !== $query_result ) {
							$list_of_posts[] = array(
								'id'    => $wpdb->insert_id,
								'title' => $title,
							);
						}
					}

					$dm_notice = __( 'The following documents have been generated:', 'ultimate-markdown' ) . ' ';
					foreach ( $list_of_posts as $key => $post ) {
						$dm_notice .= $post['title'];
						if ( $key < count( $list_of_posts ) - 1 ) {
							$dm_notice .= ', ';
						} else {
							$dm_notice .= '.';
						}
					}

			$this->shared->save_dismissible_notice(
				$dm_notice,
				'updated'
			);

		}


	}

	/**
	 * Display the form.
	 *
	 * @return void
	 */
	public function display_custom_content() {

		?>

		<div class="daextulma-admin-body">

			<?php

			// Display the dismissible notices.
			$this->shared->display_dismissible_notices();

			?>

			<div class="daextulma-tools-menu">

				<div class="daextulma-main-form">

					<div class="daextulma-main-form__wrapper-half">

						<!-- Import form -->

						<div class="daextulma-main-form__daext-form-section">

							<div class="daextulma-main-form__section-header">
								<div class="daextulma-main-form__section-header-title">
									<?php $this->shared->echo_icon_svg( 'log-in-04' ); ?>
									<div class="daextulma-main-form__section-header-title-text"><?php esc_html_e( 'Import', 'ultimate-markdown' ); ?></div>
								</div>
							</div>

							<form enctype="multipart/form-data" id="import-upload-form" method="post" class="wp-upload-form daextulma-tools-menu__import-upload-form"
							      action="admin.php?page=daextulma-<?php echo esc_attr( $this->slug_plural ); ?>">

								<div class="daextulma-main-form__daext-form-section-body">


									<?php
									wp_nonce_field( 'daextulma_tools_import', 'daextulma_tools_import_nonce' );
									?>

									<div class="daextulma-main-form__daext-form-field">

										<?php
										esc_html_e(
											'Choose one or more Markdown files (.md .markdown .mdown .mkdn .mkd .mdwn .mdtxt .mdtext .text .txt) to upload, then click Upload files and import.',
											'ultimate-markdown'
										);
										?>

									</div>

									<div class="daextulma-main-form__daext-form-field">
										<div class="daextulma-input-wrapper">
											<label for="upload"
											       class="custom-file-upload"><?php esc_html_e( 'Choose file', 'ultimate-markdown' ); ?></label>
											<div class="custom-file-upload-text"
											     id="upload-text"><?php esc_html_e( 'No file chosen', 'ultimate-markdown' ); ?></div>
											<input type="file" id="upload" name="file_to_upload[]"
											       accept=".md,.markdown,.mdown,.mkdn,.mkd,.mdwn,.mdtxt,.mdtext,.text,.txt" multiple
											       class="custom-file-upload-input">
										</div>
									</div>

									<div>
										<input type="submit" name="submit" id="submit"
										       class="daextulma-btn daextulma-btn-primary"
										       value="<?php esc_attr_e( 'Upload files and import', 'ultimate-markdown' ); ?>">
									</div>
							</form>


						</div>

						</form>

					</div>

						<!-- the data sent through this form are handled by the export_xml_controller() method called with the WordPress init action -->

							<div class="daextulma-main-form__daext-form-section">

								<div class="daextulma-main-form__section-header">
									<div class="daextulma-main-form__section-header-title">
										<?php $this->shared->echo_icon_svg( 'log-out-04' ); ?>
										<div class="daextulma-main-form__section-header-title-text"><?php esc_html_e( 'Export', 'ultimate-markdown'); ?></div>
									</div>
								</div>

								<form method="POST"
								      action="admin.php?page=<?php echo esc_attr( $this->shared->get( 'slug' ) ); ?>-<?php echo esc_attr( $this->slug_plural ); ?>" class="daextulma-tools-menu__export-form">

								<div class="daextulma-main-form__daext-form-section-body">

									<!-- Export form -->

									<div class="daextulma-main-form__daext-form-field">

									<?php
										esc_html_e(
											'Click the Export button to generate a ZIP file that includes all your markdown documents.',
											'ultimate-markdown'
										);
										?>

									</div>

									<div class="daext-widget-submit">
										<input name="daextulma_export"
												class="daextulma-btn daextulma-btn-primary" type="submit"
												value="<?php esc_attr_e( 'Export', 'ultimate-markdown'); ?>"
											<?php
											if ( ! $this->shared->exportable_data_exists() ) {
												echo 'disabled="disabled"';
											}
											?>
										>
									</div>

									<?php wp_nonce_field( 'daextulma_tools_export', 'daextulma_tools_export_nonce' ); ?>

								</div>

								</form>

							</div>

						</form>

					</div>

				</div>

			</div>

		</div>

		<?php
	}
}
