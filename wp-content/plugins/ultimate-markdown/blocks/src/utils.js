/**
 * This file is used to store utility functions used in the block editor.
 *
 * @package ultimate-markdown
 */

/**
 * Add the tag and refresh the panel.
 *
 * See: https://stackoverflow.com/questions/69983604/changing-a-posts-tag-category-from-a-wordpress-gutenberg-sidebar-plugin/70029499#70029499
 *
 * @param tag
 * @constructor
 */
export function AddTag(tag) {

    const {dispatch, select} = wp.data;

    // Get Current Selected Tags.
    let tags = select('core/editor').getEditedPostAttribute('tags');

    // Get State of Tag Panel.
    let is_tag_panel_open = select('core/edit-post').isEditorPanelOpened('taxonomy-panel-tags');

    // Verify new tag isn't already selected.
    if (!tags.includes(tag)) {

        // Add new tag to existing list.
        tags.push(tag);

        // Update Post with new tags.
        dispatch('core/editor').editPost({'tags': tags});

        // Verify if the tag panel is open.
        if (is_tag_panel_open) {

            // Close and re-open the tag panel to reload data / refresh the UI.
            dispatch('core/edit-post').toggleEditorPanelOpened('taxonomy-panel-tags');
            dispatch('core/edit-post').toggleEditorPanelOpened('taxonomy-panel-tags');
        }
    }

}

/**
 * Updates the editor fields based on the provided data.
 *
 * @param blocks The blocks that should be added in the block editor
 * @param data The data used to update various fields of the editor
 */
export function updateFields(blocks, data) {

    const {dispatch, select} = wp.data;

    // Insert the blocks in the editor.
    wp.data.dispatch('core/block-editor').insertBlocks(blocks);

    // Clear selection so the Block tab doesn't stay active.
    wp.data.dispatch('core/block-editor').clearSelectedBlock();

    // Force the sidebar back to "Post" section.
    wp.data.dispatch('core/edit-post').openGeneralSidebar('edit-post/document');

    // Update the post title.
    if (data['title'] !== null) {
        wp.data.dispatch('core/editor').editPost({title: data['title']});
    }

    // Update the excerpt.
    if (data['excerpt'] !== null) {
        wp.data.dispatch('core/editor').editPost({excerpt: data['excerpt']});
    }

    // Update the categories.
    if (data['categories'] !== null) {
        // Replace all categories with the new ones.
        dispatch('core/editor').editPost({ categories: data['categories'] });
    }

    // Update the tags.
    if (data['tags'] !== null) {
        // Replace all tags with the new ones.
        dispatch('core/editor').editPost({ tags: data['tags'] });
    }

    // Update the author.
    if (data['author'] !== null) {
        wp.data.dispatch('core/editor').editPost({author: data['author']});
    }

    // Update the date.
    if (data['date'] !== null) {
        wp.data.dispatch('core/editor').editPost({date: data['date']});
    }

    /**
     *
     * Update the post status.
     *
     * WordPress internally supports several statuses (publish, draft, pending,
     * future, private, trash, auto-draft, inherit, etc.). However, only a limited subset of these statuses can be
     * safely using the provided front matter value.
     *
     *  - publish  → Published
     *  - draft    → Draft
     *  - pending  → Pending Review
     *  - private  → Private
     *  - future   → Scheduled
     *
     * Other statuses such as "trash", "auto-draft", or "inherit" are controlled by other mechanisms (e.g. trash
     * actions, internal WordPress logic) and should not be set.
     *
     * Note that the 'future' status (scheduled) doesn't actually need a publishing date, because if the date is not
     * set in the future, WordPress will automatically change the status to 'publish' when the post is saved. If instead
     * a future date is set, WordPress will keep the 'future' status and publish the post at the scheduled date.
     *
     * @type {string[]}
     */
    const allowedStatuses = ['publish', 'draft', 'pending', 'private', 'future'];
    if (data['status'] !== null && data.status !== undefined && allowedStatuses.includes(data.status)) {
        wp.data.dispatch('core/editor').editPost({status: data['status']});
    }

}

/**
 * Download a file from the provided string.
 *
 * @param content
 * @param fileNameExtension
 */
export const downloadFileFromString = (content, fileNameExtension, postId) => {

    const blob = content;

    // Create a temporary URL to the blob.
    const url = window.URL.createObjectURL(new Blob([blob]));

    // Create a link element.
    const link = document.createElement('a');
    link.href = url;
    const fileName = 'post-' + postId + '.' + fileNameExtension;
    link.setAttribute('download', fileName); // Specify the filename

    // Append the link to the body.
    document.body.appendChild(link);

    // Trigger the click event on the link.
    link.click();

    // Cleanup.
    link.parentNode.removeChild(link);

}