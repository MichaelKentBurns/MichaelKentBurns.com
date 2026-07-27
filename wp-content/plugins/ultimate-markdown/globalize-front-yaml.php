<?php
/**
 * Globalize Front YAML parser.
 *
 * @package ultimate-markdown
 */

// Prevent direct access to this file.
if ( ! defined( 'WPINC' ) ) {
	die();
}

require __DIR__ . '/vendor/autoload.php';

global $daextulma_front_yaml_parser;
$daextulma_front_yaml_parser = new Mni\FrontYAML\Parser();
