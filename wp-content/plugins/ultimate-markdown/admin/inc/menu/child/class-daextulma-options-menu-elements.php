<?php
/**
 * This class adds the options with the related callbacks and validations.
 *
 * @package ultimate-markdown
 */

/**
 * This class adds the options with the related callbacks and validations.
 */
class Daextulma_Options_Menu_Elements extends Daextulma_Menu_Elements {

	/**
	 * Constructor.
	 *
	 * @param object $shared The shared class.
	 * @param string $page_query_param The page query parameter.
	 * @param string $config The config parameter.
	 */
	public function __construct( $shared, $page_query_param, $config ) {

		parent::__construct( $shared, $page_query_param, $config );

		$this->menu_slug      = 'options';
		$this->slug_plural    = 'options';
		$this->label_singular = __( 'Options', 'ultimate-markdown');
		$this->label_plural   = __( 'Options', 'ultimate-markdown');
	}

	/**
	 * Display the body content.
	 *
	 * @return void
	 */
	public function display_custom_content() {

		?>

		<div class="wrap">

			<div id="react-root"></div>

		</div>

		<?php

		// Display the Pro features section.
		$this->display_pro_features();

	}
}
