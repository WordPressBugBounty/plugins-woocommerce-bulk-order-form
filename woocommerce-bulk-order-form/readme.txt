=== Quick/Bulk Order Form for WooCommerce ===
Contributors: pomegranate,jprummer,varunms,alexmigf,yordansoares
Author URI: https://wpovernight.com/
Plugin URL: https://wpovernight.com/downloads/woocommerce-bulk-order-form/
Tags: WooCommerce, bulk order, quick order, order form
Donate link: 
Requires at least: 4.0
Tested up to: 6.6
Requires PHP: 7.2
Stable tag: 3.6.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html 

Automatically add a bulk or quick order form to your WooCommerce site with a single shortcode.

== Description ==
This plugin helps you sell more by letting you add a Bulk/Quick Order Form for WooCommerce to your website in seconds!

All you have to do is add the `[wcbulkorder]` shortcode to a page and you already have a quick order form!
More information on setting up the plugin can be found in our documentation here: [Bulk Order Form shortcode](https://docs.wpovernight.com/bulk-order-form/bulk-order-form-shortcode/)

The shortcode is extremely customizable and includes the following awesome features:

* Let user search by product id, title, or sku
* Turn price fields on/off
* Set default number of rows
* Set title for product input field column
* Set title for quantity input field column
* Set title for price input field column
* Price totals are calculated in real time
* Disable jquery ui css or add your own
* Include specific products or variations by id
* Exclude specific products or variations by id
* Include only specific categories
* Display product images in autocomplete search
* Limit user to be able to search only by product id, title, sku, or allow them to search all.
* 4 additional search label output formats
* Add New Row button so the customer can add additional fields as needed
* Create as many forms as you want and customize them with shortcode attributes

== Changelog ==

= 3.6.5 (2024-06-26) =
* Tested up to WooCommerce 9.0 & WordPress 6.6

= 3.6.4 (2024-03-20) =
* Tested up to WooCommerce 8.7 & WordPress 6.5

= 3.6.3 (2024-02-22) =
* New: setting to display hidden products
* Fix: wrong text domain in the `class-dependencies.php`
* Translations: Updated translation template (POT)
* Tested up to WooCommerce 8.6

= 3.6.2 (2023-11-08) =
* New: code refactoring and optimization
* New: bump the minimum PHP version to 7.2
* Fix: `qty_max` value resetting to `empty`
* Tested up to WooCommerce 8.3 & WordPress 6.4

= 3.6.1 (2023-08-09) =
* Tested up to WooCommerce 8.0 & WordPress 6.3

= 3.6.0 (2023-05-29) =
* New: adds WooCommerce HPOS compatibility (beta)
* New: product classes on frontend search results: `on-backorder`, `in-stock`, `out-of-stock`
* Fix: Cross Site Scripting (XSS) vulnerability on label settings
* Fix: load styles/scripts only on bulk order settings page
* Tested up to WooCommerce 7.7

= 3.5.7 (2023-04-06) =
* Fix: bug in variation selection
* Tested up to WooCommerce 7.6 & WordPress 6.2

= 3.5.6 (2023-01-25) =
* Tweak: plugin header comment
* Tested up to WooCommerce 7.3

= 3.5.5 (2022-11-07) =
* Tweak: bumps the WooCommerce minimum version to 3.0
* Fix: product search args and sanitize function change for array
* Fix: show prices in form following WooCommerce price settings
* Tested up to WordPress 6.1 and WooCommerce 7.1

= 3.5.4 (2022-10-18) =
* Fix: additional security escaping on settings

= 3.5.3 (2022-10-13) =
* Fix: replaces strict array with `wc_bof_template_types()` function for better templates compatibility
* Fix: additional security escaping on settings
* Tested up to (and compatible with) WooCommerce 7.0

= 3.5.2 (2022-10-11) =
* Fix: additional security sanitizations

= 3.5.1 (2022-10-05) =
* Plugin name change to comply with trademark rules
* Fix: escapes HTML safely before echoing
* Fix: load assets when shortcode is used on shop page
* Tested up to (and compatible with) WooCommerce 6.9

= 3.5.0 (2022-05-13) =
* New: Settings UI redesign
* Tested up to (and compatible with) WordPress 6.0 and WooCommerce 6.5

= 3.4.0 (2021-12-21) =
* Fix: Variations were shown with main product image instead of variation image.
* Refactor: asset files reorganized
* Tested up to WooCommerce 6.0

= 3.3.11 (2021-09-16) =
* Fix: jQuery deprecation notices
* Translations: Update Spanish, Portuguese & Dutch, added Belgian & Formal Dutch.
* Tested up to (and compatible with) WordPress 5.8 and WooCommerce 5.7

= 3.3.10 (2021-05-25) =
* New: filter for search results (wc_bof_product_search_results)
* Compatibility: added support for WooCommerce Cart All In One 
* Fix: results limit was not taken into account when making query
* Fix: implemented internationalization good practices
* Fix: Include partial SKU matches in search results
* Translations: Added Portuguese, Spanish & French
* Tested up to (and compatible with) WordPress 5.7 and WooCommerce 5.4

= 3.3.9 (2021-02-25) =
* New: Filter for SKU search query
* Fix: PHP8 compatibility
* Fix: error on custom settings pages not using sections
* Tested up to WooCommerce 5.1

= 3.3.8 (2021-01-08) =
* Fix: variation form not returning results properly
* Tested up to WooCommerce 4.9

= 3.3.7 (2020-10-14) =
* Fix: removed deprecated jQuery code
* Fix: hide irrelevant settings when loading settings page
* Tested up to (and compatible with) WooCommerce 4.6

= 3.3.6 (2020-09-07) =
* Fix: Settings button inaccessible on some sites
* Tested up to (and compatible with) WordPress 5.5 and WooCommerce 4.5

= 3.3.5 (2020-06-02) =
* Fix: Default label for order form total
* Tested up to (and compatible with) WooCommerce 4.2

= 3.3.4 (2020-05-06) =
* New: filters to modify search results (`wc_bulk_order_form_suggestion` & `wc_bulk_order_form_suggestions`)
* New: action hooks before and after search process (`wc_bof_before_search` & `wc_bof_after_search`)
* Tested up to (and compatible with) WooCommerce 4.1

= 3.3.3 (2020-04-02) =
* Improved: Quantity input attribute handling
* Fix: Skip empty product quantities
* Fix: Adding variable products after bulk order form has been sent once already (Variation template)
* Fix: Clear stock notice when resetting variation (Variation template)

= 3.3.2 (2020-03-16) =
* Fix: Product was removed directly after selecting in Simple & Variation templates

= 3.3.1 (2020-03-12) =
* Fix: Allow clearing quantity input
* Improved: enforcing min/max/step input quantity rules

= 3.3.0 (2020-02-10) =
* Feature: handle min/max/step input quantities on adding products
* Feature: Show availability for variable products in Variation template
* Feature: Button to clear form selection
* Improved: Form column width distrubution

= 3.2.1 (2020-01-22) =
* Fix: Check stock status for products with stock management disabled

= 3.2.0 (2020-01-15) =
* Feature: option to exclude out of stock products from search results
* Feature: Added message when no products are found
* Fix: spinner size
* Fix: adhere to minimum & maximum quantity limitations when set
* Tested up to (and compatible with) WooCommerce 3.9

= 3.1.3 (2019-10-24) =
* Tested up to WooCommerce 3.8

= 3.1.2 (2019-09-24) =
* Feature: filter for conditionally loading assets
* Tested up to WooCommerce 3.7

= 3.1.1 (2019-04-17) =
* Tweak: pass product to wc_bulk_order_form_label filter
* Tested up to WooCommerce 3.6

= 3.1.0 (2019-03-18) =
* Fix: Stray div tag breaking footer
* Feature: Placeholder added to product search field
* Feature: Added translation field for "Total" string
* Feature: Minimum search lenght adjustable via a filter
* Several style & template tweaks
	* Limited price & qty column widths
	* Search field height in Flatsome theme
	* Better mobile compatibility

= 3.0.7 (2019-03-11) =
* Fix: Variation titles formatting
* Fix: Several strings made translatable
* Fix: Use product visibility methods for better integration
* Tweak: Leave cart notices for 10 seconds

= 3.0.6 (2019-02-27) =
* Tested up to WP5.1
* Feature: Added `processing` class to form during AJAX callback
* Feature: added filters for column content getters

= 3.0.5 (2018-12-03) =
* Fix: Errors during upgrade to WC3.5.2

= 3.0.4 (2018-10-31) =
* Fix: Deprecated function call in variation template
* Fix: Disabled unnecessary product thumbnail calls

= 3.0.3 (2018-10-17) =
* Tested up to WooCommerce 3.5
* Fix: settings display in Safari browser

= 3.0.2 (2018-09-11) =
* Fix: Variation template search

= 3.0.1 (2018-09-10) =
* Fix: fix [products] in attribute name rendering as WC shortcode
* Fix: Clear QTY on variation reset
* Fix: Gutenberg compatibility
* Fix: Settings Page Loading issue
* Fix: Prevent duplicate IDs in variation template
* Fix: Use add to cart label from settings & separate link to cart text
* Tweak: Abort previous request when search is updated
* Tweak: add filter for add to cart data `wc_bof_add_to_cart_data`
* Tweak: scroll to top on errors

= 3.0 (2018-07-17) =
* Complete interface & feature overhaul!
	* More flexible code base
	* Integrates better with third party WooCommerce plugins
	* Full WooCommerce 3.X support
	* Better PHP compatibility
* **Important:** We recommend making a backup before updating!

= 2.2.1 =
Prepping for rewrite launch

= 2.2 =
* New: Select between Cart/Checkout to link users after adding to cart. (Pro Version Only)
* New: Added customer success/failure messages (Pro Version Only)
* Tweak: Improved sku search
* Tweak: Replaced deprecated get_product function
* Tweak: Multiple JS fixes
* Tweak: Price fetching Improvements
* Tweak: Cut search speed by over 50%
* Tweak: Set bulk order form to 100% of container width

= 2.1.2 =
* Fix: Price columns display when price set to not display

= 2.1.1 =
* New: Foundation to set search time delay and character delay
* Tweak: Reformatted price display
* Fix: Price display not working for comma separated decimals

= 2.1 = 
* New: Variation label display now has its own option.
* New: Support Tab to more easily locate support
* Tweak: Use product thumbnail images for faster loading
* Tweak: Improved price fetching to include sale and dynamic pricing
* Tweak: Replaced inline styles with styles in wcbulkorder.css. Can be overridden from theme directory.
* Tweak: Changed price to autochange when new product is selected.
* Fix: Issue with price displaying as NaN
* Fix: Invalid argument error on activation
* Fix: Missing spinner on 'add row' in variation template

= 2.0.2 = 
* Fix: Products now display if not categorized
* Fix: Removed multiple products that were displayed if they had variations

= 2.0.1 = 
* New: Display attribute name and value or just attribute value in variations
* Fix: Attribute capitilization.
* Fix: Bug in standard template with variations.

= 2.0 = 
**This is a major update. Test thoroughly and review the full changes here.**

* New: Completely new template for better handling variations.
* New: Limit product search to a specific category via shortcode.
* New: Include only a specific set of products globally via the limit products extension or per shortcode.
* New: Exclude only a specific set of products globally via the limit products extension or per shortcode.

= 1.1.3 =
* Fix: problem with variation attributed displaying in cart

= 1.1.2 =
* Fix: html entity decode bug

= 1.1.1 =
* Tweak: Added filter to modify bulk order form messages
* Tweak: Added filter to modify label
* Tweak: Added translation elements for bulk order from messages
* Fix: Shortcode not working in sidebars

= 1.1.0 =
* Feature: Set max items in search
* Tweak: Improved css
* Fix: Extra characters outputted in debug mode
* Fix: Shortcode not working in sidebars

= 1.0.8 =
* Fix: Compatibility with pa_attribute format
* Tweak: Now works with carts like Menu Cart Pro

= 1.0.7 =
* Fix: Duplicate Spinner Displayed in Font Awesome
* Fix: Search By SKU Broken
* Tweak: Search for numbered titles enababled

= 1.0.6 =
* Fix: Removed extra fields outputted when price field turned off
* Tweak: Added spinner so user knows the form is working

= 1.0.5 =
* Tweak: Translation Ready
* Tweak: Outputs form inline instead of at top of page
* Fix: Issue with updater fixed
* Tweak: Added Spanish Translation

= 1.0.4 =
* Tweak: WC 2.0.xx compatibility
* Tweak: Price filter added
* Fix: No longer prints unfound strings

= 1.0.3 =
* Feature: added option to display images in autocomplete search.
* Tweak: css now only loaded on page where shortcode is present
* Tweak: variations now pull the attribute information

= 1.0.2 =
* Feature: added ability to remove jquery-ui styles or add your own.
* Tweak: css now loaded from within plugin instead of google libraries
* Tweak: scripts only loaded on pages that contain bulk order form
* Fix: issue for some users with search functionality
* Fix: minor errors in debug mode

== Screenshots ==
== Upgrade Notice ==
== Frequently Asked Questions == 

== Installation ==

= Minimum Requirements =

* WordPress 3.8 or greater
* PHP version 7.2 or greater
* MySQL version 5.0 or greater

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don't need to leave your web browser. To do an automatic install of Bulk Order Form for WooCommerce, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type "Bulk Order Form for WooCommerce"  and click Search Plugins. Once you've found our plugin you can view details about it such as the the point release, rating and description. Most importantly of course, you can install it by simply clicking "Install Now"

= Manual installation =

The manual installation method involves downloading our plugin and uploading it to your Web Server via your favourite FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

Installing alternatives:

via Admin Dashboard:

* Go to 'Plugins > Add New', search for "Bulk Order Form for WooCommerce", click "install"

OR via direct ZIP upload:

* Upload the ZIP package via 'Plugins > Add New > Upload' in your WP Admin

OR via FTP upload:

* Upload `woocommerce-bulk-order-form.zip` folder to the `/wp-content/plugins/` directory
 
Activate the plugin through the 'Plugins' menu in WordPress

= Using the shortcode =
Create a new page in WordPress via Pages > Add New. Add the shortcode `[wcbulkorder]` and any instructions you might want to add to the page.