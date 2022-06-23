Gineico Quoting System
==========

Adds changes to the WooCommerce admin order area and contains modifications for the YITH Request a Quote plugin. 

## v1.1 - 2022-06-22
* Hides the shipping options and quote description/part number from GM.
* Handles the two different tax calculations between the sites.

## v1.0 - 2022-06-12
* Initial version - contains items from main themes.

# Database Updates
UPDATE wptk_woocommerce_order_itemmeta SET meta_key = replace(meta_key, '_gl_quote_part_number', '_gqs_quote_part_number') WHERE meta_key = '_gl_quote_part_number'


UPDATE wptk_woocommerce_order_itemmeta SET meta_key = replace(meta_key, '_gl_quote_type', '_gqs_quote_type') WHERE meta_key = '_gl_quote_type'

