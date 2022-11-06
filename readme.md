Gineico Quoting System
==========

Adds changes to the WooCommerce admin order area and contains modifications for the YITH Request a Quote plugin. 

## v1.5.4 - 2022-11-05
* Adds the ability to replace an item that a customer added without choosing all of the options via a lightbox in the admin area.

## v1.5.3 - 2022-10-14
* Updates to allow adding a product to the quote without choosing all of the options.
* Updated request-quote-table.php template.

## v1.5.2 - 2022-09-25
* Added a separate function to return the brand prefix for a product so that it can be reused.

## v1.5.1 - 2022-09-12
* Bug fixes to the get_sku filter for product variations.

## v1.5 - 2022-09-11
* Updated the PDF to also show product attributes even if a Quote Description is defined.
* Added a "Notes" section for each product on the backend.
* Added line break formatting to the Quote Description when displayed on the PDF.
* Updated the SKU field to use a "Brand Prefix" if defined for each brand category.

## v1.4 - 2022-06-28
* Added the email templates for the new quote that goes to admins. Removed the totals column.

## v1.3 - 2022-06-24
* Now filtering the calculate_taxes function to disable taxes for quotes.

## v1.2 - 2022-06-23
* Filtered the WooCommerce tax calculations for quotes but leaves it in place for regular orders.

## v1.1 - 2022-06-22
* Hides the shipping options and quote description/part number from GM.
* Handles the two different tax calculations between the sites.

## v1.0 - 2022-06-12
* Initial version - contains items from main themes.

# Database Updates
UPDATE wptk_woocommerce_order_itemmeta SET meta_key = replace(meta_key, '_gl_quote_part_number', '_gqs_quote_part_number') WHERE meta_key = '_gl_quote_part_number'


UPDATE wptk_woocommerce_order_itemmeta SET meta_key = replace(meta_key, '_gl_quote_type', '_gqs_quote_type') WHERE meta_key = '_gl_quote_type'

