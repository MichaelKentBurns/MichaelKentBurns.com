<?php
/**
 * Class used to implement the back-end functionalities of the "Document" menu.
 *
 * @package ultimate-markdown
 */

/**
 * Class used to implement the back-end functionalities of the "Document" menu.
 */
class Daextulma_Document_Menu_Elements extends Daextulma_Menu_Elements {

	/**
	 * Daextulma_Document_Menu_Elements constructor.
	 *
	 * @param object $shared The shared class.
	 * @param string $page_query_param The page query parameter.
	 * @param string $config The config parameter.
	 */
	public function __construct( $shared, $page_query_param, $config ) {

		parent::__construct( $shared, $page_query_param, $config );

		$this->menu_slug          = 'document';
		$this->slug_plural        = 'documents';
		$this->label_singular     = 'Document';
		$this->label_plural       = 'Documents';
		$this->primary_key        = 'document_id';
		$this->db_table           = 'document';
		$this->list_table_columns = array(
			array(
				'db_field' => 'title',
				'label'    => 'Title',
			),
		);
		$this->searchable_fields  = array(
			'title',
			'content',
		);
	}

	/**
	 * Process the add/edit form submission of the menu. Specifically the following tasks are performed:
	 *
	 * 1. Sanitization
	 * 2. Validation
	 * 3. Database update
	 *
	 * @return void
	 */
	public function process_form() {

		if ( isset( $_POST['update_id'] ) ||
			isset( $_POST['form_submitted'] ) ) {

			// Nonce verification.
			check_admin_referer( 'daextulma_create_update_' . $this->menu_slug, 'daextulma_create_update_' . $this->menu_slug . '_nonce' );

		}

		// Preliminary operations ---------------------------------------------------------------------------------------------.
		global $wpdb;

		// Sanitization -------------------------------------------------------------------------------------------------------.

		$data = array();

		$data['update_id']      = isset( $_POST['update_id'] ) ? intval( $_POST['update_id'], 10 ) : null;
		$data['form_submitted'] = isset( $_POST['form_submitted'] ) ? intval( $_POST['form_submitted'], 10 ) : null;

		if ( ! is_null( $data['update_id'] ) || ! is_null( $data['form_submitted'] ) ) {

			// Main Form data.
			$data['title']   = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : null;

			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitization functions can't be applied to Markdown content because the Markdown syntax would be altered.
			$data['content'] = isset( $_POST['content'] ) ? wp_unslash( $_POST['content'] ) : null;

		}

		// Validation -------------------------------------------------------------------------------------------------.

		if ( ! is_null( $data['update_id'] ) || ! is_null( $data['form_submitted'] ) ) {

			// Validation on "title".
			if ( mb_strlen( trim( $data['title'] ) ) === 0 || mb_strlen( trim( $data['title'] ) ) > 100 ) {

				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid value in the "Title" field.', 'ultimate-markdown'),
					'error'
				);
				$invalid_data           = true;

			}
		}

		// Database record update -------------------------------------------------------------------------------------.
		if ( ! is_null( $data['update_id'] ) && ! isset( $invalid_data ) ) {

			// Update the database.

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$query_result = $wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->prefix}daextulma_document SET 
                title = %s,
                content = %s
                WHERE document_id = %d",
					$data['title'],
					$data['content'],
					$data['update_id']
				)
			);

			if ( false !== $query_result ) {
				$this->shared->save_dismissible_notice(
					__( 'The document has been successfully updated.', 'ultimate-markdown'),
					'updated'
				);
			}
		} elseif ( ! is_null( $data['form_submitted'] ) && ! isset( $invalid_data ) ) {

			// Insert into the database.

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$query_result = $wpdb->query(
				$wpdb->prepare(
					"INSERT INTO {$wpdb->prefix}daextulma_document SET 
                    title = %s,
                    content = %s",
					$data['title'],
					$data['content']
				)
			);

			if ( false !== $query_result ) {
				$this->shared->save_dismissible_notice(
					__( 'The document has been successfully added.', 'ultimate-markdown'),
					'updated'
				);
			}
		}
	}

	/**
	 * Defines the form fields present in the add/edit form and call the method to print them.
	 *
	 * @param array $item_obj The item object.
	 *
	 * @return void
	 */
	public function print_form_fields( $item_obj = null ) {

		// Add the form data in the $sections array.
		$sections = array(
			array(
				'label'          => 'Main',
				'section_id'     => 'main',
				'display_header' => false,
				'fields'         => array(
					array(
						'type'        => 'text',
						'name'        => 'title',
						'label'       => __( 'Title', 'ultimate-markdown'),
						'description' => __( 'The title of the document.', 'ultimate-markdown'),
						'placeholder' => '',
						'value'       => isset( $item_obj ) ? $item_obj['title'] : null,
						'maxlength'   => 100,
						'required'    => true,
					),
					array(
						'type'        => 'textarea',
						'name'        => 'content',
						'label'       => __( 'Content', 'ultimate-markdown'),
						'description' => __( 'The Markdown text of the document.', 'ultimate-markdown'),
						'placeholder' => '',
						'value'       => isset( $item_obj ) ? $item_obj['content'] : null,
						'maxlength'   => 1000000,
						'required'    => false,
					),
				),
			),
		);

		$this->print_form_fields_from_array( $sections );
	}

	/**
	 * Check if the item is deletable. If not, return the message to be displayed.
	 *
	 * @param int $item_id The ID of the item to be checked.
	 *
	 * @return array
	 */
	public function item_is_deletable( $item_id ) {

		return array(
			'is_deletable'               => true,
			'dismissible_notice_message' => null,
		);
	}
}
