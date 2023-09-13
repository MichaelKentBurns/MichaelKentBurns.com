<?php
/**
 * Uninstall removes any options we have saved.
 *
 * @package display-git-status
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

delete_option( 'git_status_options' );
