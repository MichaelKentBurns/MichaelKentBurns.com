/**
 * This file is used to create the "Submit Markdown" editor sidebar section.
 *
 * @package ultimate-markdown
 */

const {Button, TextareaControl, BaseControl} = wp.components;
const {dispatch, select} = wp.data;
const {PluginDocumentSettingPanel} = wp.editor;
const {Component} = wp.element;
const {__} = wp.i18n;
import {updateFields} from '../../utils';

export default class Sidebar extends Component {

    constructor(props) {

        super(...arguments);

        // The state is used only to rerender the component with setState.
        this.state = {
            textareaValue: '',
        };

    }

    render() {

        const meta = select('core/editor').getEditedPostAttribute('meta');
        const markdownText = meta['_import_markdown_pro_submit_text_textarea'];

        return (
            <PluginDocumentSettingPanel
                name="submit-text"
                title={__('Insert Markdown', 'ultimate-markdown')}
                className="daextulma-submit-text-panel"
            >
                <BaseControl
                    help={__(
                        'Inserts the Markdown above into the editor.',
                        'ultimate-markdown'
                    )}
                    __nextHasNoMarginBottom={ true }
                >
                <TextareaControl
                    label={__('Markdown', 'ultimate-markdown')}
                    value={markdownText}
                    onChange={(value) => {

                        dispatch('core/editor').editPost({
                            meta: {
                                '_import_markdown_pro_submit_text_textarea': value,
                            },
                        });

                        // Used to rerender the component.
                        this.setState({
                            textareaValue: value,
                        });
                    }}
                    __nextHasNoMarginBottom={ true }
                />

                <Button
                    variant="secondary"
                    className="editor-post-trash is-destructive"
                    onClick={() => {

                        wp.ajax.post('daextulma_submit_markdown', {
                            security: window.DAEXTULMA_PARAMETERS.nonce,
                            markdowntext: this.state.textareaValue,
                        }).done((data) => {

                            let pageHtml = '';
                            if ('marked' === window.DAEXTULMA_PARAMETERS.editorMarkdownParser) {
                                pageHtml = marked(data['content']);
                            } else {
                                pageHtml = data['html_content'];
                            }

                            pageHtml = DOMPurify.sanitize(pageHtml, { USE_PROFILES: { html: true } });

                            wp.data.dispatch('core/block-editor').resetBlocks([]);

                            const blocks = wp.blocks.rawHandler({ HTML: pageHtml });

                            updateFields(blocks, data);

                            // Set the textarea to an empty value.
                            dispatch('core/editor').editPost({
                                meta: {
                                    '_import_markdown_pro_submit_text_textarea': '',
                                },
                            });

                            this.setState({
                                textareaValue: '',
                            });

                            wp.data.dispatch('core/notices').createErrorNotice(
                                __('Markdown content successfully processed and blocks created.', 'ultimate-markdown'),
                                {
                                    type: 'snackbar',
                                    isDismissible: true,
                                }
                            );

                        }).fail((data) => {

                            const errorMessages = {
                                invalid_date_format: __('Invalid date format in Front Matter. Please enter a valid date, e.g. 2025-07-16, or a date and time, e.g. 2025-07-16 10:30:42.', 'ultimate-markdown'),
                                generic_error: __('Please review your document for any formatting issues in the Front Matter or Markdown content, then try again. The content could not be processed. For more details on supported fields and formatting, please refer to the plugin documentation.', 'ultimate-markdown'),
                            };

                            const errorMessage = errorMessages[data['error']] || errorMessages['generic_error'];

                            wp.data.dispatch('core/notices').createErrorNotice(
                                errorMessage,
                                {
                                    type: 'snackbar',
                                    isDismissible: true,
                                }
                            );
                        });

                    }}
                >{__('Insert', 'ultimate-markdown')}</Button>
                </BaseControl>
            </PluginDocumentSettingPanel>
        );
    }
}