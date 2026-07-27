/**
 * This file handle the following meta boxes displayed in the classic editor:
 *
 * - Import Document: Enables users to import a Markdown file directly from their computer, which is then processed and
 * its content applied to the current post.
 * - Load Document: Allows users to load and process a previously imported Markdown document from the plugin's library,
 * applying its content and configuration to the current post.
 * - Submit Text: Provides a textarea for users to input or paste Markdown content directly, which is then processed and
 * applied to the current post.
 *
 * @package ultimate-markdown
 */

(function($) {
	'use strict';

	$(document).ready(function() {

		// Import Document meta box functionality. --------------------------------------------------------------------.
		$('#daextulma-import-trigger').on('click', function(e) {
			e.preventDefault();
			$('#daextulma-import-file').trigger('click');
		});

		$('#daextulma-import-file').on('change', function(e) {
			const files = e.target.files;

			if (files.length === 0) {
				return;
			}

			const file = files[0];
			const $statusDiv = $('.daextulma-import-status');

			// Show loading message.
			$statusDiv
				.removeClass('notice-success notice-error')
				.addClass('notice notice-info')
				.html('<p>' + wp.i18n.__('Processing file...', 'ultimate-markdown') + '</p>')
				.show();

			// Prepare form data.
			const data = new FormData();
			data.append('action', 'daextulma_import_document');
			data.append('security', window.DAEXTULMA_PARAMETERS.nonce);
			data.append('uploaded_file', file);

			// Send AJAX request.
			fetch(window.DAEXTULMA_PARAMETERS.ajaxUrl, {
				method: 'POST',
				body: data,
			})
				.then(function(response) {
					return response.json();
				})
				.then(function(response) {

					// Error messages mapping.
					const errorMessages = {
						invalid_date_format: wp.i18n.__('Invalid date format in Front Matter. Please enter a valid date, e.g. 2025-07-16, or a date and time, e.g. 2025-07-16 10:30:42.', 'ultimate-markdown'),
						generic_error: wp.i18n.__('Please review your document for any formatting issues in the Front Matter or Markdown content, then try again. The content could not be processed. For more details on supported fields and formatting, please refer to the plugin documentation.', 'ultimate-markdown'),
					};

					const errorMessage = errorMessages[response.data.error] || errorMessages.generic_error;

					// Handle error.
					if (response.data.error) {
						$statusDiv
							.removeClass('notice-info')
							.addClass('notice-error')
							.html('<p>' + errorMessage + '</p>');

						// Reset file input.
						$('#daextulma-import-file').val('');
						return;
					}

					// Convert the Markdown text to HTML.
					let pageHtml = '';
					if('marked' === window.DAEXTULMA_PARAMETERS.editorMarkdownParser) {
						pageHtml = marked(response.data['content']);
					}else{
						pageHtml = response.data['html_content'];
					}

					// Update post fields.
					updateClassicEditorFields(response.data, pageHtml);

					// Show success message.
					$statusDiv
						.removeClass('notice-info')
						.addClass('notice-success')
						.html('<p>' + wp.i18n.__('Markdown content successfully processed and imported.', 'ultimate-markdown') + '</p>');

					// Reset file input.
					$('#daextulma-import-file').val('');

				})
				.catch(function(error) {
					$statusDiv
						.removeClass('notice-info')
						.addClass('notice-error')
						.html('<p>' + wp.i18n.__('An error occurred while processing the file.', 'ultimate-markdown') + '</p>');

					// Reset file input.
					$('#daextulma-import-file').val('');
				});
		});

		// Load Document meta box functionality. ----------------------------------------------------------------------.
		$('#daextulma-load-document-trigger').on('click', function(e) {
			e.preventDefault();

			const documentId = $('#daextulma-document-selector').val();
			const $statusDiv = $('.daextulma-load-status');

			// Do not proceed if the selected option is "Select a document...".
			if (parseInt(documentId, 10) === 0) {
				$statusDiv
					.removeClass('notice-success notice-info')
					.addClass('notice notice-error')
					.html('<p>' + wp.i18n.__('Please select a document before submitting.', 'ultimate-markdown') + '</p>')
					.show();
				return;
			}

			// Show loading message.
			$statusDiv
				.removeClass('notice-success notice-error')
				.addClass('notice notice-info')
				.html('<p>' + wp.i18n.__('Loading document...', 'ultimate-markdown') + '</p>')
				.show();

			// Prepare form data.
			const data = new FormData();
			data.append('action', 'daextulma_load_document');
			data.append('security', window.DAEXTULMA_PARAMETERS.nonce);
			data.append('document_id', documentId);

			// Send AJAX request.
			fetch(window.DAEXTULMA_PARAMETERS.ajaxUrl, {
				method: 'POST',
				body: data,
			}).then(function(response) {
				return response.json();
			})
				.then(function(response) {

					// Error messages mapping.
					const errorMessages = {
						invalid_date_format: wp.i18n.__('Invalid date format in Front Matter. Please enter a valid date, e.g. 2025-07-16, or a date and time, e.g. 2025-07-16 10:30:42.', 'ultimate-markdown'),
						generic_error: wp.i18n.__('Please review your document for any formatting issues in the Front Matter or Markdown content, then try again. The content could not be processed. For more details on supported fields and formatting, please refer to the plugin documentation.', 'ultimate-markdown'),
					};

					const errorMessage = errorMessages[response.data.error] || errorMessages.generic_error;

					// Handle error.
					if (response.data.error) {
						$statusDiv
							.removeClass('notice-info')
							.addClass('notice-error')
							.html('<p>' + errorMessage + '</p>');
						return;
					}

					// Convert the Markdown text to HTML.
					let pageHtml = '';
					if('marked' === window.DAEXTULMA_PARAMETERS.editorMarkdownParser) {
						pageHtml = marked(response.data['content']);
					}else{
						pageHtml = response.data['html_content'];
					}

					// Update post fields.
					updateClassicEditorFields(response.data, pageHtml);

					// Reset the document selector to "Select a document…" (0).
					$('#daextulma-document-selector').val('0');

					// Show success message.
					$statusDiv
						.removeClass('notice-info')
						.addClass('notice-success')
						.html('<p>' + wp.i18n.__('Markdown content successfully processed and loaded.', 'ultimate-markdown') + '</p>');

				})
				.catch(function(error) {
					$statusDiv
						.removeClass('notice-info')
						.addClass('notice-error')
						.html('<p>' + wp.i18n.__('An error occurred while loading the document.', 'ultimate-markdown') + '</p>');
				});
		});

		// Submit Text meta box functionality. ------------------------------------------------------------------------.
		$('#daextulma-submit-text-trigger').on('click', function(e) {
			e.preventDefault();

			const $statusDiv = $('.daextulma-submit-text-status');

			// Show loading message.
			$statusDiv
				.removeClass('notice-success notice-error')
				.addClass('notice notice-info')
				.html('<p>' + wp.i18n.__('Processing text...', 'ultimate-markdown') + '</p>')
				.show();

			// Get the markdown content from the textarea.
			let markdownContent = $('#daextulma-submit-text-textarea').val();

			// Prepare form data.
			const data = new FormData();
			data.append('action', 'daextulma_submit_markdown');
			data.append('security', window.DAEXTULMA_PARAMETERS.nonce);
			data.append('markdowntext', markdownContent);

			// Send AJAX request.
			fetch(window.DAEXTULMA_PARAMETERS.ajaxUrl, {
				method: 'POST',
				body: data,
			})
				.then(function(response) {
					return response.json();
				})
				.then(function(response) {

					// Error messages mapping.
					const errorMessages = {
						invalid_date_format: wp.i18n.__('Invalid date format in Front Matter. Please enter a valid date, e.g. 2025-07-16, or a date and time, e.g. 2025-07-16 10:30:42.', 'ultimate-markdown'),
						generic_error: wp.i18n.__('Please review your document for any formatting issues in the Front Matter or Markdown content, then try again. The content could not be processed. For more details on supported fields and formatting, please refer to the plugin documentation.', 'ultimate-markdown'),
					};

					const errorMessage = errorMessages[response.data.error] || errorMessages.generic_error;

					// Handle error.
					if (response.data.error) {
						$statusDiv
							.removeClass('notice-info')
							.addClass('notice-error')
							.html('<p>' + errorMessage + '</p>');
						return;
					}

					// Convert the Markdown text to HTML.
					let pageHtml = '';
					if('marked' === window.DAEXTULMA_PARAMETERS.editorMarkdownParser) {
						pageHtml = marked(response.data['content']);
					}else{
						pageHtml = response.data['html_content'];
					}

					// Update post fields using the utility function.
					updateClassicEditorFields(response.data, pageHtml);

					// Clear the textarea.
					$('#daextulma-submit-text-textarea').val('');

					// Show success message.
					$statusDiv
						.removeClass('notice-info')
						.addClass('notice-success')
						.html('<p>' + wp.i18n.__('Markdown content successfully converted to HTML.', 'ultimate-markdown') + '</p>');

				})
				.catch(function(error) {
					$statusDiv
						.removeClass('notice-info')
						.addClass('notice-error')
						.html('<p>' + wp.i18n.__('An error occurred while processing the text.', 'ultimate-markdown') + '</p>');
				});
		});

	});

})(jQuery);
