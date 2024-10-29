=== Affilizz ===
Contributors: affilizz, romaincarlier
Tags: gutenberg, affilizz, editor, block, affiliation, affiliate, ads
Requires at least: 5.9
Tested up to: 6.6.2
Stable tag: 1.14.5
Requires PHP: 8.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Affilizz allows you to manage all your affiliated content in one place regardless of your activity.

== Description ==

Affilizz is an official plugin for the affilizz.com affiliation platform that enables its users to connect to the API and insert seamlessly affiliate links, price tables and calls to action both on the classic and Gutenberg editors.

The plugin was released outside of the plugin directory at its launch and was added to the directory at the 1.13.0 version.

## Features

* Affiliate link configuration pop-in
* Step-by-step configuration wizard
* Configuration pane and debug information page
* Inline rendering of an affiliate link or price table in the classic editor
* Gutenberg editor block to insert affiliate links and price tables

In addition, the plugin aims to allow users to customize the way they interact with the platform and insert links in the table.

It adds a database table holding the rendered versions of the embeds, to bypass connectivity issues.

## How to contribute

As of now, this plugin is not open to external pull requests. To allow users to better understand how our code is built and proposed here, the plugin's "assets" folders contains a "src" folder containing unobfuscated, readable JS and SCSS code. Our team uses [Mix](https://www.laravel-mix.com) to bundle javascript files and uses these external libraries / modules :

* [Tom Select 2.3.1](https://tom-select.js.org/)

If you want to apply changes to the current codebase, please configure your preprocessor or bundler to compile :

* assets/src/scss/admin.scss to assets/dist/css/admin.css
* assets/src/scss/public.scss to assets/dist/css/public.css
* assets/src/js/admin.js to assets/dist/js/admin.js
* assets/src/js/wizard.js to assets/dist/js/wizard.js
* assets/src/js/editor.js to assets/dist/js/editor.js

== Screenshots ==

1. Configure your affiliation content when adding it to your posts.
2. Setup your connection to Affilizz with our easy step-by-step wizard.
3. Configure options to tailor your experience to your needs.
4. Affiliation content can be added as a dynamic block to the classic editor to see results live.
5. Completely integrated for Classic or Block (Gutenberg) editor

== Changelog ==

= 1.14.4 =
* Minor bug fixes and improvements.
* Fixes :
  * Corrects a CSS rule that broke Gutenberg content editor.

= 1.14.4 =
* Features :
  * Added textual information to the publications and publication contents retrieval endpoints.
  * Adds support for the Carousel publication content type.
  * Adds Affilizz Magic Match's webcomponent support.
  * Adds the floating button in the classic editor.
  * Changes the looks and features of the settings pages to simplify UX.

* Fixes :
  * Fixes a performance issue.
  * Fixes a loading issue on the file assets/css/admin.css (which did not exist).
  * Fixes array format in the publications retrieval endpoints.
  * Partially fixes code to WordPress alignment standards.
  * Minor updates to analytics::all preparing for future updates.
  * Adds a verification for the existence of the cache directory.
  * Creates the cache folder while registering assets.
  * Checks and creates cache folder when updating the cache UUID.

* Technical :
  * Bumps the supported version of WordPress to 6.6.2.
  * Bumps the required version of PHP to 8.1.
  * Updates the AFFILIZZ_EDIT_PUBLICATION_URL constant's placeholders.
  * Adds class attributes to allowed tags and attributes in the plugin.
  * Adds translations for the button opening process.
  * Limits searches in the publications table to local- and affilizz- ids columns.
  * Prepares the works to move to the react-based version of the plugin.

= 1.14.3 =

* Fixes an issue when changing rendering mode.
* Loads the modal on new posts instead of only published / saved posts.
* Updates the regular expression used to detect Affilizz publications (now supports both SSR and webcomponent renders).
* Fixes an issue of the custom proxy UUID disappearing.
* Changes local caching logic to only store in database cached version of SSR renders (not webcomponents).
* Fixes various issues with the diverted asset file path (local cached version of the Affilizz assets).
* Rewrites the selective enqueueing logic.
* Adds filters for multiple plugin variables :
  * affilizz_publication_transient_key
* Adds a "live search" feature to find publications and publication contents in larger collections.
* Bumps Tom Select and added the dropdown_select plugin.
* Adds a custom header to Ajax requests to Affilizz to send the current WordPress version.

= 1.14.2 =

* Hotfixes a side-effect from the publication of the new version provoking an error upon uninstallation.

= 1.14.1 =

* Hotfixes SVN malfunction / misuse.

= 1.14.0 =

* Adds a CRON check to verify the existence of a media / channel on Affilizz.
* Updated for WordPress 6.4(.1) :
  * The label options are no longer autoloaded.
  * The front-end script is deferred on all installations.
* Adds a better management of the cache for publications, reducing overall loading times.
* Adds a transient to publications, reducing overall loading times while allowing for "hard-"refresh in the back-office.
* Bumped Tom Select to version 2.3.1.

= 1.13.1 =

* Fixes the format in readme.txt.
* Adds screenshots and assets to the plugin page.
* Fixes an overlooked content verification in the 'the_content' that generated a warning on 404 pages.
* Improves code documentation for the wrap_affilizz_webcomponents function.
* Fixes issues related to the launch on WordPress plugins :
  * Additional table being created.
  * Errors in saving large data in the database.
  * Errors in nonces while saving settings pages.
  * Errors in nonces in the Wizard upon installation.
  * Errors in update queries due to double escaping of data.
* Fixes bugs reported by early adopters :
  * Escaping of the publication contents options.
  * Recent publications where no longer displayed at the top of the list.
  * Allow strong and small tags in the wordpress kses allowed tags.
  * Fixes loading of the scripts on the front-end.

= 1.13.0 =

* Adds filters for multiple plugin variables :
  * affilizz_ssr_endpoint_root_url
  * affilizz_rendering_mode
  * affilizz_publication_default_width
  * affilizz_cdn_rendering_script_location
  * affilizz_cdn_cache_time
  * affilizz_cdn_cache_root_path
  * affilizz_cdn_cache_root_url
  * affilizz_publications_database_table
  * affilizz_selective_enqueuing_regex
  * affilizz_publication_content_render
  * affilizz_has_affilizz_content
* Solves an issue with the publication content search.
* Re-adds support for PHP7.3.
* Adds drag and drop features for publication contents.
* Switches to [SEMVER](https://semver.org/) for plugin versioning.
* Escaping of echoed value and sanitization of stored values has been rethought.
* The plugin now contains source files to allow for review and extension / fork by users.
* Asset files have been moved to a "dist" subfolder.
* Removes obsolete composer dependencies in the plugin.
* Removes PHPCS annotations.
* Fixes some PHP comments, for developers.
* Adds the <meta name="affilizz-media"> wp_head hook.
* Adds nonces to the wizard and settings pages.
* Explicitely lists the allowed tags in the wp_kses calls.

= 1.12.0 =

* Moves from the Guzzle Client to the native WordPress HTTP API.
* Resolves a visual issue in the admin tabs.
* Fixes a wrong version definition in init.php.
* Resolves a z-index problem when two blocks are consecutive (notified by a user).
* Updates translation files.
* Updates the local caching mechanism to use real files and avoid rewriting after report of bugs from a user.
* Adds option to delete the affilizz table.
* Moves the creation of the table to the insertion of blocks, thus being less invasive for testers.
* Allows to load the rendering script selectively.
* Allows to not load the rendering script at all.
* Allows to paste affilizz-rendering tags in the classic TinyMCE Editor.

= 1.11.0 =

* Corrects situations made buggy with the wordpress-standardization of the code.
* Adds a local caching mechanism for webcomponent scripts.
* Adds support for card format (from Affilizz).
* Updates translation files.
* Adds a verification of the need to regenerate tinyMCE previews of the affilizz blocks.

= 1.10.0 =

* Aligns the plugin with the WordPress standards.

= 1.9.0 =

* Fixes an error when inserting publications without publication contents.

= 1.8.0 =

* Fixes an error when using ampersands in the publication name / publication content name.
* Changes the location of the front-end rendering scripts.

= 1.7.0 =

* Fixes an issue when inserting multiple consecutive blocks (beta).

= 1.6.0 =

* Allows to include multiple consecutive blocks in both editors.
* Introduces the error, warning, info and success messages in the native popin.
* Adds the "no caret defined" error in the classic editor.
* Resolves the wizard display when the plugin has already been configured.

= 1.5.0 =

* Added an error summary template file and loading of the matching template when errors pop.
* Added the webcomponent or server-side rendering switch option.

= 1.4.0 =

* Correction of a JSON encoding error.
* Added the help URL and call in the wizard.

= 1.3.0 =

* Translations corrections and updates.
* The pop-in is now agnostic to the editor type and triggered through a standalone script instead of the React popin component.
* Updates on the database schema and JSON escaping method to insert in the database.
* Deletin of the default width in the server-side-rendered block calls.

= 1.2.0 =

* Fixes the application icon and an error in the escaping of the rendered data in the database.

= 1.1.0 =

* Hotfix to correct a database table creation issue.

= 1.0.0 =

* Initial release.

== Screenshots ==
1. Insert affiliate content popin in the classic- and Gutenberg editors.
2. Setup wizard to help you set up your API Key and key data to interact with Affilizz.
3. General configuration pane in a dedicated "Affilizz" page.
4. Live rendering of the webcomponent view of your Call to action button, link, or price table.
5. Works with both the classic and Gutenberg editors, more to come.