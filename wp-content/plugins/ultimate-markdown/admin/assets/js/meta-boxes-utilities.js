/**
 * Utility functions for the meta boxes displayed in the classic editor.
 *
 * @package ultimate-markdown
 */

(function($) {
	'use strict';

	/**
	 * Update classic editor fields with imported data.
	 *
	 * This function updates various post configuration fields in the classic editor,
	 * mirroring the functionality of updateFields() in the block editor.
	 *
	 * @param {Object} data - The imported data containing post configuration fields.
	 * @param {string} pageHtml - The HTML content to be inserted into the editor.
	 */
	window.updateClassicEditorFields = function(data, pageHtml) {

		/**
		 * Update the classic editor post title and trigger the same events that WordPress binds to the title field.
		 *
		 * This ensures that:
		 *
		 *  - The "Add title" placeholder label is hidden.
		 *  - The permalink slug and UI are updated.
		 *  - Any other core or plugin listeners react as if the user typed.
		 */
		if ( data.title !== null && data.title !== undefined ) {

			const title = $( '#title' );

			// Set the new title value.
			title.val( data.title );

			// Fire the primary modern event used by most handlers.
			title.trigger( 'input' );

			// Fire "change" to catch handlers that listen for value changes but not for `input`.
			title.trigger( 'change' );

			// Fire "keyup" for older or legacy code that still relies on it (some plugins and older core logic).
			title.trigger( 'keyup' );

		}

		// Update content in TinyMCE or textarea.
		if (pageHtml) {
			if (typeof tinyMCE !== 'undefined' && tinyMCE.get('content') && tinyMCE.get('content').isHidden() === false) {
				// Visual editor is active.
				tinyMCE.get('content').setContent(pageHtml);
			} else {
				// Text editor is active.
				$('#content').val(pageHtml);
			}
		}

		// Update excerpt.
		if (data.excerpt !== null && data.excerpt !== undefined) {
			$('#excerpt').val(data.excerpt);
		}

		// Update post date.
		if (data.date !== null && data.date !== undefined) {
			const dateObj = new Date(data.date);
			const month = String(dateObj.getMonth() + 1).padStart(2, '0');
			const day = String(dateObj.getDate()).padStart(2, '0');
			const year = dateObj.getFullYear();
			const hours = String(dateObj.getHours()).padStart(2, '0');
			const minutes = String(dateObj.getMinutes()).padStart(2, '0');

			$('#mm').val(month);
			$('#jj').val(day);
			$('#aa').val(year);
			$('#hh').val(hours);
			$('#mn').val(minutes);

			// Update the timestamp display
			if (typeof wp !== 'undefined') {
				// Call WordPress's timestamp update function
				$('#edit_timestamp').click();
				$('.save-timestamp').click();
			}
		}

		// Update categories. Replace all categories with the new ones.
		if (data.categories !== null && data.categories !== undefined) {
			if (Array.isArray(data.categories)) {
				// First, uncheck all existing categories.
				$('#categorychecklist input[type="checkbox"]').prop('checked', false);
				// Then check only the new categories.
				data.categories.forEach(function(categoryId) {
					$('#categorychecklist input[value="' + categoryId + '"]').prop('checked', true);
				});
			}
		}

		// Update tags from tag IDs.
		if (data.tags !== null && data.tags !== undefined) {
			if (Array.isArray( data.tags ) && data.tags.length > 0) {

				// Ensure all tag IDs are strings before building the REST API query.
				const tagIds = data.tags.map( String );

				/**
				 * Retrieve the full tag objects (including tag names) from the WordPress REST API.
				 * The Classic Editor tag system works with tag *names*, not IDs, so we must resolve the IDs to names
				 * before adding them to the UI.
				 */
				wp.apiFetch( {
					path: '/wp/v2/tags?include=' + tagIds.join( ',' ),
					method: 'GET'
				} ).then( function (tagObjects) {

					/**
					 * Reference to the Classic Editor tag input field used to add new tags. This field normally
					 * receives user input when typing a tag manually.
					 * @type {*|jQuery|HTMLElement}
					 */
					const input = $( '#new-tag-post_tag' );

					tagObjects.forEach( tag => {

						/**
						 * Insert the tag name into the input field. This mimics the first step of the manual "add tag"
						 * workflow.
						 */
						input.val( tag.name );

						/**
						 * Simulate pressing the Enter key inside the tag input field. This triggers the internal
						 * WordPress logic in tags-box.js:
						 *
						 * keypress (Enter):
						 * - tagBox.parseTags()
						 * - tagBox.quickClicks()
						 * - update textarea (.the-tags)
						 * - rebuild the visual tag list (.tagchecklist)
						 * - attach remove button event handlers
						 *
						 * Using this mechanism ensures the UI is rebuilt exactly the same way as when a user manually
						 * adds tags in the Classic Editor.
						 */
						input.trigger( {
							type: 'keypress',
							which: 13,
							keyCode: 13
						} );

					} );

				} );

			}
		}

		// Update author.
		if (data.author !== null && data.author !== undefined) {
			$('#post_author_override').val(data.author);
		}

		/**
		 * Update the post status in the Classic Editor.
		 *
		 * WordPress internally supports several statuses (publish, draft, pending,
		 * future, private, trash, auto-draft, inherit, etc.). However, the Classic
		 * Editor "Publish" meta box only exposes a limited subset that can be safely
		 * modified through the UI:
		 *
		 * - publish  → Published
		 * - draft    → Draft
		 * - pending  → Pending Review
		 * - private  → Private
		 *
		 * Other statuses such as "future" (scheduled), "trash", "auto-draft", or "inherit" are controlled by other
		 * mechanisms (e.g. publish date, trash actions, or internal WordPress logic) and should not be set directly
		 * through this UI logic.
		 *
		 * More in details, setting the value of the 'future' status (scheduled) using the status key is not supported
		 * in the Classic Editor because it relies on the publish date to determine the scheduled status, and setting
		 * the status to 'future' without a future publish date would lead to an empty value set to the status dropdown
		 * element, because the "Scheduled" item only appears if a post is already scheduled.
		 *
		 * For this reason we explicitly restrict the allowed statuses.
		 *
		 * @type {string[]}
		 */
		const allowedStatuses = ['publish', 'draft', 'pending', 'private'];

		if (data.status !== null && data.status !== undefined && allowedStatuses.includes(data.status)) {

			const status = data.status;

			/**
			 * The hidden field #post_status stores the actual status that will be sent to WordPress when the post is
			 * saved. Updating this field ensures the correct value is persisted.
			 */
			$('#post_status').val(status);

			if (status === 'private') {

				/**
				 * In the Classic Editor the "private" status is not controlled by the Status dropdown. Instead it is
				 * managed through the "Visibility" controls of the Publish meta box. Therefore we must update the
				 * corresponding radio button.
				 */
				$('#visibility-radio-private').prop('checked', true);

				/**
				 * Update the visibility label shown in the Publish meta box so the UI immediately reflects the new
				 * visibility state.
				 */
				$('#post-visibility-display').text('Private');

			} else {

				/**
				 * All other supported statuses (publish, draft, pending) are controlled by the Status dropdown menu.
				 */
				$('#post-status-select').val(status);

				/**
				 * Update the visible status label in the Publish meta box so the UI reflects the newly selected status.
				 */
				$('#post-status-display').text(
					$('#post-status-select option:selected').text()
				);

			}

		}

		// Update featured image (thumbnail), alt, and title.
		if (data.thumbnail !== null && data.thumbnail !== undefined) {

			const thumbnailId = parseInt(data.thumbnail, 10);

			if (!isNaN(thumbnailId) && thumbnailId > 0) {

				// 1. Set hidden field (required for saving)
				$('#_thumbnail_id').val(thumbnailId).trigger('change');

				// 2. Get attachment model
				var attachment = wp.media.attachment(thumbnailId);
				attachment.fetch();

				// 3. Use WordPress core featured image frame
				if (typeof wp !== 'undefined' && wp.media && wp.media.featuredImage) {

					// This is the KEY line:
					wp.media.featuredImage.set(thumbnailId);

				}

				// 4. Mark post dirty
				$(window).trigger('beforeunload');
			}
		}

	};

})(jQuery);