<?php
/**
 * Uninstall plugin.
 *
 * @package ultimate-markdown
 */

// Exit if this file is called outside WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die();
}

require_once plugin_dir_path( __FILE__ ) . 'shared/class-daextulma-shared.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/class-daextulma-admin.php';

// Delete options and tables.
Daextulma_Admin::un_delete();
