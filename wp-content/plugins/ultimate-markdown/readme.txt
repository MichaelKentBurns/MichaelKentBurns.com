=== Ultimate Markdown - Markdown Editor, Importer, & Exporter ===
Contributors: DAEXT
Tags: markdown, markdown editor, import markdown, export markdown, front matter
Donate link: https://daext.com
Requires at least: 5.0
Tested up to: 7.0
Requires PHP: 5.3
Stable tag: 1.26
License: GPLv3

Generate articles from a Markdown file, bulk import and export Markdown documents, create Markdown documents from an editor, and more.

== Description ==
Ultimate Markdown helps you speed up your writing workflow. Create, import, and export Markdown with tools designed for faster publishing and automation.

The plugin offers multiple ways to work with Markdown. After installing it, you can:

* Create posts from Markdown files or internal Markdown documents.
* Convert Markdown text to blocks directly in the post editor.
* Create and manage Markdown documents in a dedicated plugin menu.
* Import Markdown files into WordPress.
* Export Markdown documents from the internal library as ZIP archives.

We distribute the [Pro version](https://daext.com/ultimate-markdown/) of this plugin, which includes additional import and export options, automatic image uploads during import, REST API support for automation, additional Markdown parsers (7 options), extended Front Matter support, document categories, and more. See the [feature comparison](https://daext.com/ultimate-markdown/#features) for a detailed overview.

### Getting Started

To quickly understand how the plugin works, start with the guide below:

* [Quick Start Guide](https://daext.com/kb/ultimate-markdown/quick-start-guide/)

### Using the Editor Panels

Ultimate Markdown adds dedicated panels to the post editor:

- [Import Markdown](https://daext.com/kb/ultimate-markdown/import-markdown-files-in-the-post-editor/) – Upload a Markdown file and convert it into post content
- [Load Markdown](https://daext.com/kb/ultimate-markdown/load-documents-in-the-post-editor/) – Generate post content from documents stored in the plugin's internal library
- [Insert Markdown](https://daext.com/kb/ultimate-markdown/insert-markdown-in-the-post-editor/) – Paste or type Markdown text and convert it instantly

The plugin integrates with both the Block Editor and the Classic Editor. The editor panels are available in the post editor sidebar when using the Block Editor and as meta boxes when using the Classic Editor.

### Managing Markdown Documents

Manage your Markdown content directly within the plugin using a dedicated menu.

- [Documents Overview](https://daext.com/kb/ultimate-markdown/introduction-to-managing-documents/) – Learn how to create, edit, and organize your Markdown documents

### Front Matter

The plugin supports Front Matter key-value pairs provided in the YAML language. With this feature, you can configure post settings by defining key-value pairs at the beginning of the Markdown document.

See the [Using Front Matter](https://daext.com/kb/ultimate-markdown/using-front-matter-in-the-plugin/) guide for available keys and practical examples.

### Bulk Import and Export

Use the dedicated menu to import one or more Markdown files and store them as documents in the internal library.

You can also export the Markdown documents created within the plugin as downloadable files.

### Learn More

Want to explore the plugin in more detail? See the full documentation:

* [Plugin Documentation](https://daext.com/kb/ultimate-markdown/)

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/ultimate-markdown/` directory, or install the plugin through the WordPress Plugins screen directly.
2. Activate the plugin through the Plugins screen in WordPress.

### 1. Start Using the Editor Panels

Go to the post editor and use the editor panels to create content from Markdown files, internal documents, or Markdown text.

### 2. Manage your Markdown Documents

Go to **Markdown → Documents** to create and manage your Markdown documents.
For bulk import and export of Markdown files to and from the internal library, use **Markdown → Tools**.

### 3. Configure the Plugin Options

Go to **Markdown → Options** to configure the plugin settings. For example, you can choose the Markdown parser used in the editor panels and document preview, along with other advanced options.

For detailed guidance and advanced configuration, visit the [official Knowledge Base](https://daext.com/kb/ultimate-markdown/).

== Changelog ==

= 1.26 =

*April 15, 2026*

* Updated links to the Quick Start Guide and plugin documentation.

= 1.25 =

*March 18, 2026*

* The Pro version upgrade banner previously displayed at the bottom of all plugin menu pages is now shown only in the Options menu.
* Added a notices manager class used to display documentation resources and a link to rate the plugin. A related action hook has also been introduced to allow notices to be rendered in a specific area of the plugin UI.

= 1.24 =

*March 8, 2026*

* The post editor tools added by the plugin are now also available as meta boxes in the Classic Editor.
* Improved the UI of the Block Editor tools.
* Updating the post status via front matter is now restricted to valid statuses.
* The plugin now checks if the user has the "edit_posts" capability before showing the block editor tools or meta boxes.

= 1.23 =

*December 21, 2025*

* Improved Front Matter stripping logic to ensure it is removed only when located at the beginning of the document.

= 1.22 =

*July 28, 2025*

* Improved normalization of Front Matter date field during Markdown imports in the block editor.
* Bug fix: Categories and tags in the block editor are now correctly updated based on the imported Front Matter data.

= 1.21 =

*July 11, 2025*

* Added the League CommonMark and League CommonMark GitHub Markdown parsers.
* Added Editor and Live Preview Markdown parser options.
* The Live Preview feature in the Documents menu editor can now use PHP-based parsers.
* Added new settings to control the behavior of the Markdown preview when using PHP parsers.
* The block editor now shows notification messages indicating whether Front Matter and Markdown content was parsed successfully or if errors occurred.
* The Markdown preview in the Documents menu now displays a header indicating the active Markdown parser and, if applicable, a refresh button.
* Bug fix: Markdown code was altered in specific scenarios due to improper use of sanitize_textarea_field().

= 1.20 =

*April 21, 2025*

* Fixed PHP notice caused by early use of translation functions.

= 1.19 =

*November 29, 2024*

* Resolved CSS style issue.
* The load_plugin_textdomain() function now runs with the correct hook.

= 1.18 =

*June 26, 2024*

* wp_unslash() was added to the Documents menu before input sanitization.

= 1.17 =

*June 26, 2024*

* An alert message now prevents uploading more than the maximum number of files allowed with the max_file_uploads directive.

= 1.16 =

*June 25, 2024*

* Vendor packages updated.

= 1.15 =

*June 25, 2024*

* Major back-end UI update.

= 1.14 =

*April 7, 2024*

* Fixed a bug (started with WordPress version 6.5) that prevented the creation of the plugin database tables and the initialization of the plugin database options during the plugin activation.

= 1.13 =

*March 26, 2024*

* The phpcs "WordPress" ruleset has been applied to the plugin code.
* Nonce verification has been added to the Documents menu for create/update/delete/duplicate operations.
* Nonce verification has been added to the Import and Export menus.
* The Select2 library has been removed.
* Removed React warning in the block editor.

= 1.12 =

*November 8, 2023*

* Removed deprecation warnings.

= 1.11 =

*February 22, 2023*

* Fixed a bug causing incorrect parsing of Markdown tables in the context of the block editor.

= 1.10 =

*September 22, 2022*

* Fixed a bug causing JavaScript errors in the site-editor.php page.
* The "Content" textarea of the "Documents" menu now supports up to 1,000,000 characters.
* Front Matter is now supported and can be used to configure post settings.

= 1.09 =

*June 13, 2022*

* Fixed a bug causing JavaScript errors in the widgets.php page.
* The "Pro Version" menu has been added.
* A link to the Pro version has been added in the "Plugins" menu.

= 1.08 =

*November 24, 2021*

* Fixed a bug causing JavaScript errors in the post editor of custom post types that don't declare support for custom fields.
* Aria attributes added in the "Documents" menu.

= 1.07 =

*November 4, 2021*

* Improved the message displayed in the "Import" menu after a successful submission.
* Improved input sanitization.
* The file extensions supported in the upload process of the "Import" menu have been limited.

= 1.06 =

*November 3, 2021*

* Initial release.

== Screenshots ==
1. Import Markdown section in the post sidebar.
2. Load Markdown section in the post sidebar.
3. Insert Markdown section in the post sidebar.
4. A single document.
5. List of documents.
6. Tools menu.
7. Options menu.