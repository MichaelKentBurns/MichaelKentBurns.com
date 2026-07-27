<?php
/**
 * Globalize Markdown parsers.
 *
 * @package ultimate-markdown
 */

// Prevent direct access to this file.
if ( ! defined( 'WPINC' ) ) {
    die();
}

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\GithubFlavoredMarkdownConverter;

// Check PHP version and mbstring extension.
if ( version_compare( PHP_VERSION, '7.4', '>=' ) && extension_loaded( 'mbstring' ) ) {
	// Autoload dependencies.
	require __DIR__ . '/vendor/autoload.php';

	// Instantiate the Markdown parsers and store them in global variables.
	global $commonmark_converter, $github_flavored_converter;

	$commonmark_converter      = new CommonMarkConverter();
	$github_flavored_converter = new GithubFlavoredMarkdownConverter();
}