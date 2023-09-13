<?php
/**
 * Plugin Name: Display Display Git Status
 * Version: 1.0.1
 * Plugin URI: https://wpgitupdater.dev/docs/latest/plugins#display-display-git-status
 * Author: WP Git Updater
 * Author URI: https://wpgitupdater.dev
 * Description: A simple WordPress plugin to display your current git branch and status in the admin area.
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * Text Domain: display-git-status
 * Domain Path: /languages
 *
 * @package display-display-git-status
 *
 * Display Display Git Status is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Display Git Status is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Display Git Status. If not, see https://wordpress.org/plugins/display-git-status/.
 */

// This plugin only operates in the admin area, there is no need to continue otherwise.
if ( ! is_admin() ) {
	return;
}

define( 'DISPLAY_GIT_STATUS_VERSION', '1.0.1' );
define(
	'DISPLAY_GIT_STATUS_WP_GIT_UPDATER_LOGO_SVG',
	'<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 544.1 85" xml:space="preserve" style="max-height: 100%;width: auto;">
		<g>
			<defs>
				<rect id="SVGID_1_" y="0" width="91.3" height="85"></rect>
			</defs>
			<clipPath id="SVGID_2_">
				<use xlink:href="#SVGID_1_" style="overflow: visible;"></use>
			</clipPath>
			<path d="M46.4,79.5c20.4,0,37-16.5,37.1-36.9c0-20.4-16.5-37-36.9-37.1 C26.1,5.5,9.5,22,9.5,42.5C9.5,62.9,26,79.5,46.4,79.5" style="clip-path: url(\'#SVGID_2_\'); fill: rgb(16, 116, 168);"></path>
			<path d="M46.5,22.2c-8.9,0-17.1,6-19.6,15 C24,48,30.4,59.2,41.2,62.1s21.9-3.5,24.8-14.3c1.2-4.3,0.8-8.6-0.6-12.5" style="clip-path: url(\'#SVGID_2_\'); fill: none; stroke: rgb(255, 255, 255); stroke-width: 2.52;"></path>
			<polygon points="51.3,22.2 44.2,15.6 44.2,28.9 	" style="clip-path: url(\'#SVGID_2_\'); fill: rgb(255, 255, 255);"></polygon>
			<path d="M68.9,35.5c3-3,3-7.8,0-10.7c-3-3-7.8-3-10.7,0c-3,3-3,7.8,0,10.7 C61.1,38.4,65.9,38.4,68.9,35.5" style="clip-path: url(\'#SVGID_2_\'); fill: rgb(240, 79, 50);"></path>
			<path d="M68.9,35.5c3-3,3-7.8,0-10.7 c-3-3-7.8-3-10.7,0c-3,3-3,7.8,0,10.7C61.1,38.4,65.9,38.4,68.9,35.5z" style="clip-path: url(\'#SVGID_2_\'); fill: none; stroke: rgb(255, 255, 255); stroke-width: 2.52;"></path>
		</g>
		<polygon points="43,30.4 51.2,22.2 43,14 " style="fill: rgb(255, 255, 255);"></polygon>
		<path d="M268.4,31.4v-9H238v9h10.1v32.2h10.1V31.4H268.4z M232,22.3h-10.1v41.3H232V22.3z M214.9,45.6v-5.7 h-16.2v8.5h6.1v0.6c0,1.6-0.4,3.2-1.6,4.4c-0.9,1-2.4,1.6-4.5,1.6c-1.9,0-3.2-0.8-4-1.7c-0.9-1.2-1.5-2.7-1.5-10.2s0.6-8.9,1.5-10.2 c0.8-1,2.1-1.8,4-1.8c3.5,0,5.1,1.5,5.9,4.6h10.2c-1-6.8-5.5-13.7-16.1-13.7c-5,0-8.5,1.6-11.5,4.6c-4.4,4.4-4.2,10.2-4.2,16.4 s-0.2,12,4.2,16.4c3,3,6.7,4.6,11.5,4.6c4.5,0,8.5-1,12-4.6C213.8,56.1,214.9,52.2,214.9,45.6" style="fill: rgb(240, 79, 50);"></path>
		<path d="M168.3,35.6c0,2.1-1.6,4.2-4.4,4.2h-5.7v-8.4h5.7C166.7,31.4,168.3,33.4,168.3,35.6 M178.4,35.6 c0-6.8-4.9-13.3-14.1-13.3H148v41.3h10.2V48.9h6.2C173.5,48.9,178.4,42.4,178.4,35.6 M143.8,22.3h-10.6l-5.4,21.8l-6.7-21.8H114 l-6.7,21.8l-5.4-21.8H91.3l11.4,41.3h8.2l6.7-20.4l6.7,20.4h8.3L143.8,22.3z" style="fill: rgb(16, 116, 168);"></path>
		<path d="M523.7,35c0,3.2-2.3,5.4-5.8,5.4h-7.5V29.6h7.5C521.4,29.6,523.7,31.8,523.7,35 M533.4,63.4 l-9.1-17.7c4-1.4,7.5-5,7.5-10.8c0-6.8-4.9-12.5-13.3-12.5h-16v41h8V47.1h5.8l8,16.3H533.4z M492.7,63.4v-7.1h-19v-10h16.2v-7.1 h-16.2v-9.6h19v-7.1h-27v41H492.7z M457.4,29.6v-7.1H428v7.1h10.7v33.8h8V29.6H457.4z M414.7,49.4h-10.2l5.2-14.9L414.7,49.4z M427.6,63.4l-15-41h-6.3l-14.9,41h8.3l2.5-7.2h14.6l2.4,7.2H427.6z M378.5,42.8c0,6.2-0.2,9.1-1.7,11c-1.4,1.7-3.2,2.5-6,2.5h-6 V29.6h6c2.8,0,4.6,0.9,6,2.5C378.3,34,378.5,36.5,378.5,42.8 M386.5,42.8c0-6.2,0.5-11.8-4.1-16.4c-2.7-2.7-6.6-3.9-10.8-3.9h-14.8 v41h14.8c4.3,0,8.1-1.2,10.8-3.9C386.9,54.9,386.5,48.9,386.5,42.8 M340.2,35.2c0,3.3-2.3,5.6-5.9,5.6h-7.5V29.6h7.5 C337.9,29.6,340.2,31.9,340.2,35.2 M348.2,35.2c0-7-5.1-12.8-13.5-12.8h-15.9v41h8V48h7.9C343.1,48,348.2,42.2,348.2,35.2 M308,49.4 V22.5H300v26.6c0,4.7-2.8,7.5-7.1,7.5c-4.3,0-7.1-2.8-7.1-7.5V22.5h-8v26.9c0,8.7,6.7,14.4,15.1,14.4C301.3,63.8,308,58.1,308,49.4" style="fill: rgb(178, 178, 178);"></path>
	</svg>'
);

register_activation_hook( __FILE__, 'dgs_install_hook' );
/**
 * Sets default option values if none are present.
 */
function dgs_install_hook() {
	if ( ! get_option( 'dgs_options' ) ) {
		update_option(
			'dgs_options',
			array(
				'git_directory' => rtrim( WP_CONTENT_DIR, '/' ),
				'version' => DISPLAY_GIT_STATUS_VERSION,
			)
		);
	}
}

add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'dgs_action_links' );
/**
 * Prepend the settings page link to the plugin links.
 *
 * @param array $links Existing links.
 * @return array Links including settings page
 */
function dgs_action_links( $links ) {
	return array_merge(
		array( '<a href="' . admin_url( 'tools.php?page=display-git-status' ) . '">' . __( 'Settings', 'display-git-status' ) . '</a>' ),
		$links
	);
}

/**
 * Determine if the `shell_exec` function is available.
 *
 * @return bool is the function available
 */
function dgs_is_shell_exec_available() {
	if ( in_array( strtolower( ini_get( 'safe_mode' ) ), array( 'on', '1' ), true ) || ( ! function_exists( 'exec' ) ) ) {
		return false;
	}
	return stripos( ini_get( 'disable_functions' ), 'shell_exec' ) === false;
}

/**
 * Returns the location of the git repository, defaulting to `wp-content` if not set.
 *
 * @return string The location of the git repository
 */
function dgs_get_repository_location() {
	$options = get_option( 'dgs_options' );
	if ( is_array( $options ) && isset( $options['git_directory'] ) ) {
		return $options['git_directory'];
	}
	return rtrim( WP_CONTENT_DIR, '/' );
}

/**
 * Returns the current branch name for a given location.
 *
 * @return string The current branch name
 */
function dgs_get_branch_name() {
	if ( ! dgs_is_shell_exec_available() ) {
		return '';
	}
	return trim( shell_exec( escapeshellcmd( 'cd ' . dgs_get_repository_location() . ' && git rev-parse --abbrev-ref HEAD' ) ) );
}

/**
 * Returns a boolean for git status, true being up to date, false otherwise
 *
 * @return bool true when no untracked changes, false otherwise.
 */
function dgs_is_up_to_date() {
	if ( ! dgs_is_shell_exec_available() ) {
		return true;
	}
	$status = trim( shell_exec( escapeshellcmd( 'cd ' . dgs_get_repository_location() . ' && git status --porcelain=v1' ) ) );
	if ( '' === $status ) {
		return true;
	}

	return false;
}

/**
 * Returns the content of the git status call
 *
 * @return string result of the git status call.
 */
function dgs_get_status() {
	if ( ! dgs_is_shell_exec_available() ) {
		return '';
	}
	return trim( shell_exec( escapeshellcmd( 'cd ' . dgs_get_repository_location() . ' && git status' ) ) );
}

/**
 * Returns last commit information
 *
 * @return string result of `git show --name-status`
 */
function dgs_get_last_commit() {
	if ( ! dgs_is_shell_exec_available() ) {
		return '';
	}
	return trim( shell_exec( escapeshellcmd( 'cd ' . dgs_get_repository_location() . ' && git show --name-status' ) ) );
}

add_action( 'admin_head', 'dgs_admin_css' );
/**
 * Add plugins admin css.
 */
function dgs_admin_css() {
	echo '<style>
	.display-git-status-menu img.ab-icon {
		height: 22px !important;
		width: 22px !important;
		padding-top: 5px !important;
	}
	.display-git-status-menu.display-git-status-untracked a {
		background-color: #f05133 !important;
	}
	.display-git-status-menu.display-git-status-untracked:hover a {
		background-color: #d4492f !important;
		color: #ffffff !important;
	}
</style>';
}

add_action( 'admin_bar_menu', 'dgs_add_branch_link', 100 );
/**
 * Adds a link with the current branch to the admin bar when repository located
 *
 * @param WP_Admin_Bar $admin_bar WordPress admin bar instance.
 */
function dgs_add_branch_link( WP_Admin_Bar $admin_bar ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$branch = esc_html(dgs_get_branch_name());
	if ( '' === $branch ) {
		return;
	}

	if ( dgs_is_up_to_date() ) {
		$img = plugins_url( 'img/git.svg', __FILE__ );
		$title = sprintf(
			/* translators: Asserting the current git branch */
			__( 'You are currently on the %s branch', 'display-git-status' ),
			$branch
		);
		$class_names = 'display-git-status-menu display-git-status-up-to-date';
	} else {
		$img = plugins_url( 'img/git-white.svg', __FILE__ );
		$title = sprintf(
			/* translators: Asserting the current git branch */
			__( 'You are currently on the %s branch, but there are uncommitted changes!', 'display-git-status' ),
			$branch
		);
		$class_names = 'display-git-status-menu display-git-status-untracked';
	}
	$admin_bar->add_menu(
		array(
			'id'    => 'display-git-status',
			'parent' => null,
			'group'  => null,
			'title' => '<img src="' . $img . '" alt="' . __( 'Git Icon', 'display-git-status' ) . '" class="ab-icon" />' . $branch,
			'href'  => admin_url( 'tools.php?page=display-git-status' ),
			'meta' => array(
				'title' => $title,
				'class' => $class_names,
			),
		)
	);
}

add_action( 'admin_menu', 'dgs_add_pages' );
/**
 * Adds the Display Git Status Tools menu page.
 */
function dgs_add_pages() {
	$page = add_management_page( __( 'Display Git Status', 'display-git-status' ), __( 'Display Git Status', 'display-git-status' ), 'manage_options', 'display-git-status', 'dgs_page' );
	add_action( 'load-' . $page, 'dgs_help' );
}

/**
 * Outputs the Display Git Status page content.
 */
function dgs_page() {
	if ( ! dgs_is_shell_exec_available() ) {
		add_settings_error( 'dgs_options', 'dgs_setting_git_directory', __( 'The function shell_exec is unavailable. The plugin cannot function correctly without it!', 'display-git-status' ), 'error' );
	}
	if ( dgs_get_branch_name() === '' ) {
		add_settings_error( 'dgs_options', 'dgs_setting_git_directory', __( 'The saved location is not a git repository! The git status menu item will be hidden from view.', 'display-git-status' ), 'error' );
	}
	?>
	<div class="wrap">
		<style type="text/css">
			.page-title img {
				width: 30px;
				height: 30px;
				margin-bottom: -6px;
			}
			.button[data-repository-directory] {
				margin-left: 6px;
			}
			#submit {
				margin-bottom: 20px;
			}
			.author-credit {
				display: flex;
				flex-wrap: wrap;
				align-items: center;
				margin-top: 20px;
			}
			.author-credit a {
				padding: 0;
				margin: 0;
				height: 30px;
			}
			.author-credit svg {
				display: inline;
				height: 100%;
				width: auto;
			}
			@media (max-width: 768px) {
				.author-credit > * {
					width: 100%;
					flex-shrink: unset;
					text-align: center;
					margin-bottom: 10px !important;
				}
			}
		</style>
		<h1 class="page-title">
			<img src="<?php echo esc_attr( plugins_url( 'img/git.svg', __FILE__ ) ); ?>" alt="<?php esc_attr_e( 'Git Icon', 'display-git-status' ); ?>" />
			<?php esc_attr_e( 'Display Git Status', 'display-git-status' ); ?>
		</h1>
		<form action="options.php" method="post">
			<?php
			settings_errors( 'dgs_options' );
			settings_fields( 'dgs_options' );
			do_settings_sections( 'dgs_settings' );
			?>
			<input id="submit" name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save Settings', 'display-git-status' ); ?>" />
			<?php
			do_settings_sections( 'dgs_status' );
			?>
		</form>
		<div class="author-credit">
			<?php
			$author_link = '<a href="https://wpgitupdater.dev" target="_blank" title="WP Git Updater">' . DISPLAY_GIT_STATUS_WP_GIT_UPDATER_LOGO_SVG . '</a>';
			echo sprintf(
				'<span>%s</span>%s<span>%s</span>',
				/* translators: Author credit */
				esc_attr( __( 'Git status is brought to you by ', 'display-git-status' ) ),
				// phpcs:ignore
				$author_link,
				/* translators: Author credit tagline */
				esc_attr( __( 'Automated Source Controlled WordPress Updates.', 'display-git-status' ) )
			);
			?>
		</div>
		<script type="text/javascript">
			(function() {
				var repoSetters = document.querySelectorAll('[data-repository-directory]');
				repoSetters.forEach(function(setter) {
					setter.addEventListener('click', function (event) {
						event.preventDefault();
						document.getElementById('dgs_setting_git_directory').value = this.dataset.repositoryDirectory;
					});
				});
			})();
		</script>
	</div>
	<?php
}

add_action( 'admin_init', 'dgs_register_settings' );
/**
 * Register our plugins settings, sections and fields.
 */
function dgs_register_settings() {
	register_setting(
		'dgs_options',
		'dgs_options',
		array(
			'type' => 'array',
			'sanitize_callback' => 'dgs_sanitize_options',
		)
	);
	add_settings_section( 'git_settings', __( 'Git Settings', 'display-git-status' ), 'dgs_git_settings_text', 'dgs_settings' );
	add_settings_field( 'dgs_setting_git_directory', __( 'Git Repository Location', 'display-git-status' ), 'dgs_setting_git_directory', 'dgs_settings', 'git_settings', array( 'label_for' => 'dgs_setting_git_directory' ) );

	add_settings_section( 'git_status', __( 'Git Status', 'display-git-status' ), 'dgs_dgs_text', 'dgs_status' );
	add_settings_field( 'dgs_setting_git_status', __( 'Repository Status', 'display-git-status' ), 'dgs_setting_git_status', 'dgs_status', 'git_status', array( 'label_for' => 'dgs_setting_git_status' ) );
	add_settings_field( 'dgs_setting_git_commit', __( 'Last Commit', 'display-git-status' ), 'dgs_setting_git_commit', 'dgs_status', 'git_status', array( 'label_for' => 'dgs_setting_git_commit' ) );
}

/**
 * Sanitize user supplied settings for our plugin, adding notices where appropriate.
 *
 * @param array $options plugin settings form options.
 * @return array Sanitized settings
 */
function dgs_sanitize_options( $options ) {
	$options['git_directory'] = esc_attr( rtrim( $options['git_directory'], '/' ) );
	$options['version'] = DISPLAY_GIT_STATUS_VERSION;
	add_settings_error( 'dgs_options', 'dgs_setting_git_directory', __( 'Settings Saved', 'display-git-status' ), 'success' );
	return $options;
}

/**
 * Introduction text for the git settings section.
 */
function dgs_git_settings_text() { }

/**
 * Output our git directory setting input.
 */
function dgs_setting_git_directory() {
	$options = get_option( 'dgs_options' );
	echo '<input id="dgs_setting_git_directory" class="regular-text code" name="dgs_options[git_directory]" type="text" value="' . esc_attr( $options['git_directory'] ) . '" />';
	echo '<a href="#" class="button" data-repository-directory="' . esc_attr( rtrim( WP_CONTENT_DIR, '/' ) ) . '">' . esc_attr( 'Set to wp-content', 'display-git-status' ) . '</a>';
	echo '<a href="#" class="button" data-repository-directory="' . esc_attr( rtrim( ABSPATH, '/' ) ) . '">' . esc_attr( 'Set to root', 'display-git-status' ) . '</a>';
	echo '<p class="description">' . esc_attr( 'Enter the full path to your sites git repository.', 'display-git-status' ) . '</p>';
}

/**
 * Introduction text for the git status section.
 */
function dgs_dgs_text() { }

/**
 * Output our git status setting input.
 */
function dgs_setting_git_status() {
	echo '<textarea id="dgs_setting_git_status" class="large-text code" rows="10" cols="50" disabled readonly>' . esc_attr( dgs_get_status() ) . '</textarea>';
}

/**
 * Output our git commit setting input.
 */
function dgs_setting_git_commit() {
	echo '<textarea id="dgs_setting_git_commit" class="large-text code" rows="10" cols="50" disabled readonly>' . esc_attr( dgs_get_last_commit() ) . '</textarea>';
}

/**
 * Add help content to the git status page
 */
function dgs_help() {
	$screen = get_current_screen();

	$screen->add_help_tab(
		array(
			'id'    => 'introduction',
			'title' => __( 'Introduction', 'display-git-status' ),
			'content'   => '<p>' . __( 'Display Git Status is a pretty simple plugin, all it needs is access to the shell_exec function and to be pointed at a git repository.', 'display-git-status' ) . '</p>' .
				'<p>' . __( 'The plugin will not perform any state altering operations, it will access the repository using read only methods to fetch information like branch name, last commit and status.', 'display-git-status' ) . '</p>',
		)
	);

	$screen->add_help_tab(
		array(
			'id'    => 'admin-bar',
			'title' => __( 'Admin Bar Item', 'display-git-status' ),
			'content'   => '<p>' . __( 'When directed to a git repository via the "Git Repository Location" setting the plugin will add a new admin bar item with the git icon.', 'display-git-status' ) . '</p>' .
				'<p>' . __( 'Next to the icon you can see the branch name currently checked out.', 'display-git-status' ) . '</p>' .
				'<p>' . __( 'Take note of the background color of this item. When the background is red it means your local repository is out of sync with git.', 'display-git-status' ) . '</p>',
		)
	);

	$screen->add_help_tab(
		array(
			'id'    => 'settings',
			'title' => __( 'Settings', 'display-git-status' ),
			'content'   => '<p>' . __( 'The plugin only has one setting, and that\'s the repository location.', 'display-git-status' ) . '</p>' .
				'<p>' . __( 'From this its able to perform all of its other functions.', 'display-git-status' ) . '</p>' .
				'<p>' . __( 'The repository location is most likely to be your wp-content folder, or the root folder of the install.', 'display-git-status' ) . '</p>' .
				'<p>' . sprintf(
						/* translators: Blog post link */
					__( 'Checkout our blog on %s for some of the common approaches.', 'display-git-status' ),
					'<a href="https://wpgitupdater.dev/blog/a-look-at-word-press-source-control-strategies" target="_blank">' . __( 'WordPress Source Control Strategies', 'display-git-status' ) . '</a>'
				) .
				'</p>' .
				'<p>' . __( 'However it can be anything suitable for your use case. It doesn\'t even have to be WordPress, you could use it to be aware of any git repository on the local filesystem (that the php process user as access to).', 'display-git-status' ) . '</p>',
		)
	);

	$screen->add_help_tab(
		array(
			'id'    => 'about',
			'title' => __( 'About', 'display-git-status' ),
			'content'   => '<div style="height: 40px;margin-top: 20px;">' . DISPLAY_GIT_STATUS_WP_GIT_UPDATER_LOGO_SVG . '</div>' .
				'<p>' . __( 'Display Git Status is a complimentary plugin for Git Source Controlled websites, provided by WP Git Updater.', 'display-git-status' ) . '</p>' .
				'<p>' . __( 'WP Git Updater provides an automated plugin and theme update service for git source controlled WordPress sites.', 'display-git-status' ) . '</p>' .
				'<p>' . __( 'Use of Git status does not require an active subscription to the WP Git Updater service. However we would super greatful if you tried out our 10 day free trial.', 'display-git-status' ) . '</p>' .
				'<p>' . sprintf(
					/* translators: About us link */
					__( 'Visit %s for more information.', 'display-git-status' ),
					'<a href="https://wpgitupdater.dev" target="_blank">' . __( 'WP Git Updater', 'display-git-status' ) . '</a>'
				) .
				'</p>',
		)
	);

	$screen->set_help_sidebar(
		'<p>' . __( 'For more information:', 'display-git-status' ) . '</p>' .
		'<p><a href="https://wpgitupdater.dev" target="_blank">' . __( 'WP Git Updater', 'display-git-status' ) . '</a></p>' .
		'<p><a href="https://wpgitupdater.dev/docs/latest/plugins#display-git-status" target="_blank">' . __( 'Documentation', 'display-git-status' ) . '</a></p>' .
		'<p><a href="https://wordpress.org/support/plugin/display-git-status/" target="_blank">' . __( 'Support', 'display-git-status' ) . '</a></p>'
	);
}
