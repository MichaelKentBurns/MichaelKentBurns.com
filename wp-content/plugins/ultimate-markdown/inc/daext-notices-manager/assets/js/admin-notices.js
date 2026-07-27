/**
 * Plugin Notices Manager – Admin Notices JS
 * Version: 1.0.16
 *
 * Handles click events on notice buttons and sends the corresponding AJAX
 * requests to persist user actions (dismiss, remind later, never show).
 */
jQuery( document ).ready(
	function ($) {

		/**
		 * Send an AJAX request to handle a specific notice action for a given plugin.
		 *
		 * @param {string} action The action to perform (e.g., 'doc_dismiss', 'review_rated').
		 * @param {string} plugin The plugin identifier used server-side to track the notice.
		 * @return {void}
		 */
		function send(action, plugin){

			// Retrieve the nonce used to validate the notice-related AJAX requests.
			const nonce = $( '#daextpnm_notice_nonce' ).val();

			/**
			 * Send the AJAX request to WordPress using the global `ajaxurl` endpoint.
			 * The action name includes the plugin prefix so it matches the
			 * `wp_ajax_daext_notice_action_{$plugin_prefix}` hook registered in PHP.
			 */
			$.post(
				ajaxurl,
				{
					action:'daext_notice_action_' + ( typeof daextpnmNoticeData !== 'undefined' ? daextpnmNoticeData.pluginPrefix : '' ),
					notice_action:action,
					plugin:plugin,
					nonce:nonce
				}
			);

		}

		/**
		 * Handle click on the "documentation" notice dismiss button.
		 *
		 * Sends the appropriate AJAX action and removes the notice from the DOM.
		 */
		$( '.daextpnm-dismiss-doc' ).on(
			'click',
			function (e) {

				e.preventDefault();

				// Plugin slug stored in the data-plugin attribute of the clicked element.
				let plugin = $( this ).data( 'plugin' );

				// Notify the server that the documentation notice has been dismissed.
				send( 'doc_dismiss',plugin );

				// Remove the corresponding notice element from the page.
				$( this ).closest( '.daextpnm-notice' ).remove();

			}
		);

		/**
		 * Handle click on the "Remind me later" button of the review notice.
		 *
		 * Defers the review notice to a later time and removes it from the DOM.
		 */
		$( '.daextpnm-review-later' ).on(
			'click',
			function (e) {

				e.preventDefault();

				// Plugin slug stored in the data-plugin attribute of the clicked element.
				let plugin = $( this ).data( 'plugin' );

				// Notify the server that the user wants to be reminded later.
				send( 'review_later',plugin );

				// Remove the corresponding notice element from the page.
				$( this ).closest( '.daextpnm-notice' ).remove();

			}
		);

		/**
		 * Handle click on the "Never show again" button of the review notice.
		 *
		 * Permanently disables the review notice for the plugin and removes it from the DOM.
		 */
		$( '.daextpnm-review-never' ).on(
			'click',
			function (e) {

				e.preventDefault();

				// Plugin slug stored in the data-plugin attribute of the clicked element.
				let plugin = $( this ).data( 'plugin' );

				// Notify the server that the user never wants to see the review notice again.
				send( 'review_never',plugin );

				// Remove the corresponding notice element from the page.
				$( this ).closest( '.daextpnm-notice' ).remove();

			}
		);

	}
);