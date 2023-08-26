<?php 
defined( 'ABSPATH' ) || exit;
define( 'BREEZE_ADVANCED_CACHE', true );
if ( is_admin() ) { return; }
if ( ! @file_exists( '/home/971999.cloudwaysapps.com/hynxgrbyjv/public_html/wp-content/plugins/breeze/breeze.php' ) ) { return; }
$config['config_path'] = '/home/971999.cloudwaysapps.com/hynxgrbyjv/public_html/wp-content/breeze-config/breeze-config.php';
if ( empty( $config ) || ! isset( $config['config_path'] ) || ! @file_exists( $config['config_path'] ) ) { return; }
$breeze_temp_config = include $config['config_path'];
if ( isset( $config['blog_id'] ) ) { $breeze_temp_config['blog_id'] = $config['blog_id']; }
$GLOBALS['breeze_config'] = $breeze_temp_config; unset( $breeze_temp_config );
if ( empty( $GLOBALS['breeze_config'] ) || empty( $GLOBALS['breeze_config']['cache_options']['breeze-active'] ) ) { return; }
if ( @file_exists( '/home/971999.cloudwaysapps.com/hynxgrbyjv/public_html/wp-content/plugins/breeze/inc/cache/execute-cache.php' ) ) {
	include_once '/home/971999.cloudwaysapps.com/hynxgrbyjv/public_html/wp-content/plugins/breeze/inc/cache/execute-cache.php';
}
