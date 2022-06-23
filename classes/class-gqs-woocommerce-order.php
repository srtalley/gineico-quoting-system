<?php 

namespace Gineicio\QuotingSystem;

class GQS_WooCommerce_Order {
  
    public function __construct() {
        
        if(site_url() != "https://www.gineicomarine.com.au" && site_url() != "https://gineicomarine.dev.dustysun.com") {
            // Add a meta key to the request a quote items
            add_action( 'ywraq_from_cart_to_order_item', array($this, 'gqs_ywraq_from_cart_to_order_item'), 10, 4 );

            // Change the meta key display label
            add_filter( 'woocommerce_order_item_display_meta_key', array($this, 'gqs_filter_wc_order_item_display_meta_key'), 20, 3 );

            // Add the missing columns
            add_action( 'woocommerce_after_order_item_object_save', array($this, 'gqs_woocommerce_after_order_item_object_save'), 10, 2 );

            // add freight options
            add_action( 'add_meta_boxes', array($this, 'gqs_shop_order_add_meta_boxes'), 40 );
        }
            // Change certain text strings
            add_filter( 'gettext', array($this,'change_text_strings'), 20, 3 );
            
            add_action( 'current_screen', array($this,'gqs_woocommerce_order_admin'), 10, 1 );

            // Save the PDF name on a new quote
            add_action( 'woocommerce_new_order', array($this, 'gqs_add_pdf_name_new_order'), 10, 1 );
            // update the PDF name on save
            // add_action( 'save_post', array($this, 'gqs_update_pdf_name'), 9999 );

            // Ajax to update the PDF name
            add_action( 'wp_ajax_gqs_save_pdf_name', array($this, 'gqs_save_pdf_name') );



            add_filter( 'yith_ywraq_metabox_fields', array($this, 'gqs_yith_ywraq_metabox_fields'), 10, 3 );

            // do not show discounts in quotes
            add_filter( 'option_ywraq_show_old_price', array($this, 'filter_ywraq_show_old_price'), 10, 1 );

            // show the quote description in the order area
            add_action( 'woocommerce_before_order_itemmeta', array($this, 'gqs_show_quote_description'), 10, 3 );

            // add the original price to the admin order 
            add_action( 'woocommerce_admin_order_item_headers', array($this, 'gqs_show_original_price_header'), 10, 1 );
            add_action( 'woocommerce_admin_order_item_values', array($this, 'gqs_show_original_price_value'), 10, 3 );

            // add a field for the admin orders to show the price without a voucher column and add the GST field to the order area 
            add_action( 'woocommerce_admin_order_totals_after_shipping', array($this, 'gqs_show_subtotal_without_vouchers'), 10, 1 );
            
            // allow resending the quote from the order actions box in the order area
            add_action( 'woocommerce_order_actions', array($this, 'add_action_to_order_actions_box') );
            add_action( 'woocommerce_order_action_wc_resend_quote_email_action', array($this, 'wc_resend_quote_email_handler') );
    }


/**
     * Change various text strings
     */
    public function change_text_strings($translated_text, $text, $domain) {

        if($domain == 'yith-woocommerce-request-a-quote') {
            switch ( $translated_text ) {
               
                case 'PDF preview':
                    $translated_text = __( 'PDF Quote', $domain);
                    break;
                case 'See a PDF preview':
                    $translated_text = __( 'Create PDF Quote', $domain);
                    break;
                case 'Click to see a PDF preview of this quote.':
                    $translated_text = __( 'Click to create a PDF of this quote.', $domain);
                    break;
            }
        } 
        // if($domain == 'woocommerce') {
        //     switch ( $translated_text ) {
        //         case 'Items Subtotal:':
        //             $translated_text = __( 'Total Ex. GST:', $domain);
        //             break;
        //     }
        // }

        return $translated_text;
    }
    


    /** 
     * Add custom keys to each submitted ywraq quote order
     */
    public function gqs_ywraq_from_cart_to_order_item( $values, $cart_item_key, $item_id, $order ) {
        $item = $order->get_item($item_id);
        // The WC_Product object
        $product = $item->get_product(); 
        $sku = $product->get_sku();
        if($sku == '') {
            $sku = ' ';
        }

        $key = '_gqs_quote_type'; 
        $value = ' '; 
        wc_update_order_item_meta($item_id, $key, $value);

        $key = '_gqs_quote_part_number'; 
        $value = $sku; 
        wc_update_order_item_meta($item_id, $key, $value);
    }

    /**
     * Change the display of the meta key labels
     */
    public function gqs_filter_wc_order_item_display_meta_key( $display_key, $meta, $item ) {
        // Change displayed label for specific order item meta key
        if( is_admin() && $item->get_type() === 'line_item' && $meta->key === '_gqs_quote_type' ) {
            $display_key = __("Quote Type", "woocommerce" );
        }
        if( is_admin() && $item->get_type() === 'line_item' && $meta->key === '_gqs_quote_part_number' ) {
            $display_key = __("Part Number", "woocommerce" );
        }
        return $display_key;
    }
 
    /**
     * Checks and adds meta key items on order or item save
     */
    public function gqs_woocommerce_after_order_item_object_save($item, $data_store) {

        if(is_a($item, 'WC_Order_Item_Product')) {
            $item_id = $item->get_id();
            // The WC_Product object
            $product = $item->get_product(); 
            $sku = $product->get_sku();
            if($sku == '') {
                $sku = ' ';
            }
    
            $this->update_wc_order_item_meta_key($item_id, '_gqs_quote_type');
            $this->update_wc_order_item_meta_key($item_id, '_gqs_quote_part_number', $sku);
        }

    }

    /**
     * Custom function to check for and add order item meta if 
     * it does not exist.
     */
    private function update_wc_order_item_meta_key($item_id, $meta_key, $value = ' ') {
        $meta_key_search = wc_get_order_item_meta( $item_id, $meta_key, true );
        if($meta_key_search == '') {
            wc_update_order_item_meta($item_id, $meta_key, $value);
        }
    }

    /**
     * Javascript to prevent editing the custom meta field
     * key names or labels
     */
    public function gqs_woocommerce_order_admin($current_screen) {
        if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
            if($current_screen->id == 'shop_order') {
                // prevent loading user custom order
                add_filter( 'get_user_option_meta-box-order_shop_order', '__return_empty_string' );

                // add the CSS & JS
                add_action('admin_head', array($this, 'gqs_shop_order_quote_css_js'), 1);

                // move the metaboxes
                add_action( 'add_meta_boxes', array($this, 'gqs_change_order_metaboxes'), 99 );

                // enqueue the JS file
                add_action( 'admin_enqueue_scripts', array($this, 'gqs_shop_order_quote_external_js'), 1, 1 );

            }
        } // end if 
    }

    /**
     * CSS & JS for the shop order
     */
    public function gqs_shop_order_quote_css_js() {

        $primary_link_color = GQS_Site_Utils::get_gineico_primary_link_color();
        $site = GQS_Site_Utils::get_gineico_site_abbreviation();
        $additional_css = '';
        if($site == 'GL') {
            $additional_css = '#woocommerce-order-items .add-items .button.add-coupon {display: none;}';
        }
        ?>

        <style>
            #order_line_items td.name {
                display: flex;
                flex-direction: column;
            }
            #order_line_items td.name > a,
            #order_line_items td.name > div {
                order: 100;
            }
            #woocommerce-order-items .woocommerce_order_items_wrapper table.woocommerce_order_items tbody th textarea, #woocommerce-order-items .woocommerce_order_items_wrapper table.woocommerce_order_items td textarea {
                font-size: 16px;
            }
            #woocommerce-order-items .woocommerce_order_items_wrapper table.woocommerce_order_items table.display_meta, #woocommerce-order-items .woocommerce_order_items_wrapper table.woocommerce_order_items table.meta,
            #woocommerce-order-items .woocommerce_order_items_wrapper table.woocommerce_order_items td.name .wc-order-item-sku, #woocommerce-order-items .woocommerce_order_items_wrapper table.woocommerce_order_items td.name .wc-order-item-variation,
            .gqs-quote-description,
            #woocommerce-order-items .woocommerce_order_items_wrapper table.woocommerce_order_items td p {
                display: block;
                margin-top: 0.5em;
                font-size: 16px !important;
                line-height: 1.6em;
                color: #2e2e2e;
                padding-top: 8px;
            }
            #woocommerce-order-items .woocommerce_order_items_wrapper table.woocommerce_order_items table.display_meta tr td p {
                padding-top: 0;
            }
            .gqs-quote-description-label {
                font-weight: 600;
            }
            .gqs-quote-description.edit-description .gqs-quote-description-label,
            .gqs-quote-description.edit-description .gqs-quote-description-text {
                color: #8d8d8d;
            }
            .gqs-quote-description-edit-links {
                padding-left: 10px;
            }
            #woocommerce-order-items .woocommerce_order_items_wrapper table.woocommerce_order_items .line_cost .wc-order-item-discount,
            .gqs-quote-description-edit-links .hide-link {
                display: none;
            }
            .gqs-quote-description-text p {
                margin: 0;
                font-size: 16px;
            }
            .gqs-quote-description-textarea {
                min-height: 200px;
            }
            #order_line_items td.name .wc-order-item-name {
                order: 2 !important;
                color: #000000;
                font-size: 18px;
                font-weight: 600;
                line-height: 1.6em;
                text-decoration-color: <?php echo $primary_link_color; ?>;
                text-decoration-thickness: .125em;
                text-underline-offset: 4.5px;
            }
            #order_line_items td.name .wc-order-item-name:after {
                content:'';
                border-bottom: 2px solid black;
            }
            #woocommerce-order-items .wc-order-data-row {
                padding-right: 90px;
            }
            .gqs-quote-description {
                order: 2 !important;
            }
            .gqs-quote-description .edit,
            .gqs-quote-description-edit {
                display: none;
            }
            .gqs-hide-label {
                display: none;
            }
            .gqs-quote-description-edit-label,
            .gqs-quote-type > td:first-child::before,
            .gqs-quote-part-number > td:first-child::before {
                content: '';
                font-weight: 600;
                font-size: 16px;
                color: #000;
                margin-bottom: 5px;
                display: block
            }
            .gqs-quote-description-edit-label {
                display: inline-block;
            }
            .gqs-quote-type > td:last-child button,
            .gqs-quote-part-number > td:last-child button{
                display: none;
            }
            .gqs-quote-type > td:first-child::before {
                content: 'Quote Type:';
            }
            .gqs-quote-part-number > td:first-child::before {
                content: 'Part Number:';
            }
            #gqs-order-custom div {
                margin-bottom: 10px;
            }
            .gqs-options-row.header {
                margin-bottom: 40px !important;
                padding: 0 40px;
                position: relative;
            }
            .gqs-options-row.header:after {
                content: '';
                display: block;
                border-bottom: 1px solid #c5c5c5;
                position: absolute;
                bottom: -15px;
                left:  0;
                width: 100%;
            }
            .gqs-options-row.header p {
                font-size: 18px;
                margin-bottom: 0;
                margin-top: 0;
                width: 100%;
            }
            .gqs-options-row.header h4 {
                margin: 0;
            }
            .gqs-other-shipping-method.hide {
                /* display: none; */
            }
            .gqs-options-row {
                display: flex;
                flex-direction: row;
                flex-wrap: wrap;
            }
            .gqs-options-row-left {
                flex: 1 1 50%;
                display: flex;
                flex-direction: row;
                align-items: flex-start;
            }
            .gqs-options-row-right {
                flex: 1 1 20%;
                display: flex;
                flex-direction: row;
                align-items: flex-start;
            }
            .gqs-options-row input[type="text"] {
                width: 90%;
            }
            #gqs_shipping_error {
                color: #e60000;
                font-weight: bold;
                font-size: 15px;
            }
            .gqs-edit-shipping-label,
            .gqs-hide-shipping-label {
                display: block;
                font-size: 12px;
                text-decoration: none;
            }
            .gqs-checkbox-col {
                flex: 0 1 40px;
            }
            .gqs-label-col {
                flex: 1 1 50%;
            }
            .gqs-label-col label {
                display: block;
                font-size: 16px;
            }
            .gqs-custom-label.hide {
                display: none;
            }
            .gqs-shipping-amount.hide {
                display: none;
            }
            .gqs-amount-error {
                display: none;
                color: #e60000;
                font-weight: bold;
                font-size: 15px;
                padding-left: 20px;
            }
            #gqs-shipping-error {
                display: none;
                color: #e60000;
                font-weight: bold;
                font-size: 18px;
            }
            .button.add-order-shipping {
                display: none !important;
            }
            .wc-backbone-modal .wc-backbone-modal-content {
                min-width: 800px;
            }
            .wc-backbone-modal .wc-backbone-modal-content .widefat>tbody>tr>td:first-child {
                width: 90%;
            }
            /* Hide Total/GST discount */
            .line_cost label,
            .line_cost .line_subtotal,
            .line_tax label,
            .line_tax .line_subtotal_tax  {
                display: none !important;
            }
            #woocommerce-order-items .woocommerce_order_items_wrapper table.woocommerce_order_items .line_cost .split-input div.input input,
            #woocommerce-order-items .woocommerce_order_items_wrapper table.woocommerce_order_items .line_tax .split-input div.input input {
                border: 1px solid #8c8f94;
                font-size: 14px;
                padding: 4px;
                color: #555;
                vertical-align: middle;
                width: 100px;
            }

            #woocommerce-order-items .woocommerce_order_items_wrapper table.woocommerce_order_items .line_cost .split-input,
            #woocommerce-order-items .woocommerce_order_items_wrapper table.woocommerce_order_items .line_tax .split-input {
                border: none;
                box-shadow: none;
            }
            #woocommerce-order-items .woocommerce_order_items_wrapper table.woocommerce_order_items .line_cost .split-input div.input:first-child,
            #woocommerce-order-items .woocommerce_order_items_wrapper table.woocommerce_order_items .line_tax .split-input div.input:first-child {
                border-bottom: none;
            }
            #woocommerce-order-items .woocommerce_order_items_wrapper table.woocommerce_order_items .item_cost input {
                width: 100px;
            }

            #woocommerce-order-items .woocommerce_order_items_wrapper table.woocommerce_order_items .item_cost input::-webkit-outer-spin-button,
            #woocommerce-order-items .woocommerce_order_items_wrapper table.woocommerce_order_items .item_cost input::-webkit-inner-spin-button,
            #woocommerce-order-items .woocommerce_order_items_wrapper table.woocommerce_order_items .line_cost .split-input div.input input::-webkit-outer-spin-button,
            #woocommerce-order-items .woocommerce_order_items_wrapper table.woocommerce_order_items .line_cost .split-input div.input input::-webkit-inner-spin-button,
            #woocommerce-order-items .woocommerce_order_items_wrapper table.woocommerce_order_items .line_tax .split-input div.input input::-webkit-outer-spin-button,
            #woocommerce-order-items .woocommerce_order_items_wrapper table.woocommerce_order_items .line_tax .split-input div.input input::-webkit-inner-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }
            <?php echo $additional_css; ?>
        </style>

        <?php
    }

    /**
     * Enqueue a separate JS file for the shop order
     */
    public function gqs_shop_order_quote_external_js($hook) {

        $plugin_data = get_plugin_data( GINEICO_QUOTING_SYSTEM__FILE__ );

        wp_enqueue_script( 'gineico-qs-admin', plugins_url('/js/gineico-gqs-admin.js', GINEICO_QUOTING_SYSTEM__FILE__), array('jquery'), $plugin_data['Version'], true );
        wp_localize_script( 'gineico-qs-admin', 'gqs_admin_shop_order_init', array(
            'ajaxurl'   => admin_url( 'admin-ajax.php' ),
            'ajaxnonce' => wp_create_nonce( 'gqs_mods_init_nonce' )
        ) );
    }
    /**
     * Add a PDF name on new order
     */
    public function gqs_add_pdf_name_new_order($order_id) {
        $value =  array(
            'html' => 0
        );
        update_post_meta( $order_id, '_gqs_ywraq_pdf_revision_number', $value );
    }
    /**
     * Save the PDF name that is entered
     */
    public function gqs_save_pdf_name() {
        $nonce_check = check_ajax_referer( 'gqs_mods_init_nonce', 'nonce' );
        $value =  array(
            'html' => sanitize_text_field($_POST['pdf_name'])
        );
        $order_id = sanitize_text_field($_POST['order_id']);
        update_post_meta( $order_id, '_gqs_ywraq_pdf_revision_number', $value );
    }

    public function gqs_shop_order_add_meta_boxes() {
        add_meta_box( 
            'gqs-order-custom', 
            __( 'Add Shipping Methods' ), 
            array($this, 'gqs_shop_order_custom_metabox_callback'), 
            'shop_order', 
            'normal', 
            'high'
        );
    }
    public function gqs_shop_order_custom_metabox_callback() {

        $freight_name = 'Freight - Delivery From Gineico QLD Warehouse To Client To Be Confirmed';

        $local_freight_name = 'Local Freight - Delivery From Gineico QLD Warehouse';
        
        $international_freight_name = 'International Freight - From Manufacturer Warehouse';
        ?>
        <!-- <p><strong>Shipping</strong></p> -->
        <form id="gqs-shipping-options">

            <div class="gqs-options-row header">
                <p>Check the shipping options below you wish to add to the quote, and the amount field will appear. When done, click the Add Shipping button to add the chosen methods to the quote.</p>
                <hr>
            </div>
            <div class="gqs-options-row header">
                <div class="gqs-options-row-left">
                    <h4>Shipping Method</h4>
                </div>
                <div class="gqs-options-row-right">
                    <h4 style="padding-left: 60px;">Amount</h4>
                </div>
            </div>
            <div class="gqs-options-row">
                <div class="gqs-options-row-left">
                    <!-- <label for="gqs_shipping_option">Shipping Method</label> -->
                    <div class="gqs-checkbox-col">
                        <input type="checkbox" class="gqs-shipping-checkbox" name="freight[name]" id="freight[name]" value="<?php echo $freight_name; ?>">
                    </div>
                    <div class="gqs-label-col">
                        <div class="gqs-regular-label">
                            <label for="freight[name]"><?php echo $freight_name; ?></label> <a href="#" class="gqs-edit-shipping-label">(Edit Name)</a>
                        </div>
                        <div class="gqs-custom-label hide">
                            <input type="text" class="gqs-custom-shipping-name" id="freight[custom_name]" value="<?php echo $freight_name; ?>">
                            <a href="#" class="gqs-hide-shipping-label">(Discard)</a>
                            <input type="hidden" class="gqs-use-custom-name-hidden" id="freight[use_custom_name]" value="false">
                        </div>
                    </div>

                </div>
                <div class="gqs-options-row-right">
                    <label for="freight[amount]"></label>
                    <input type="number" class="gqs-shipping-amount hide" id="freight[amount]" name="freight[amount]"  step="0.01" value="0" min="0" style="max-width: 100px; text-align: right;">
                    <span id="freight[error]" class="gqs-amount-error">Please enter an amount.</span>
                </div>
            </div>
            <div class="gqs-options-row">
                <div class="gqs-options-row-left">
                    <div class="gqs-checkbox-col">
                        <input type="checkbox" class="gqs-shipping-checkbox" name="local_freight[name]" id="local_freight[name]" value="<?php echo $local_freight_name; ?>">
                    </div>
                    <div class="gqs-label-col">
                        <div class="gqs-regular-label">
                            <label for="local_freight[name]"><?php echo $local_freight_name; ?></label> <a href="#" class="gqs-edit-shipping-label">(Edit Name)</a>
                        </div>
                        <div class="gqs-custom-label hide">
                            <input type="text" class="gqs-custom-shipping-name"  id="local_freight[custom_name]" value="<?php echo $local_freight_name; ?>">
                            <a href="#" class="gqs-hide-shipping-label">(Discard)</a>
                            <input type="hidden" class="gqs-use-custom-name-hidden" id="local_freight[use_custom_name]" value="false">
                        </div>

                    </div>
                </div>
                <div class="gqs-options-row-right">
                    <label for="local_freight[amount]"></label>
                    <input type="number" class="gqs-shipping-amount hide" id="local_freight[amount]" name="local_freight[amount]"  step="0.01" value="0" min="0" style="max-width: 100px; text-align: right;">
                    <span id="local_freight[error]" class="gqs-amount-error">Please enter an amount.</span>

                </div>
            </div>
            <div class="gqs-options-row">
                <div class="gqs-options-row-left">
                    <div class="gqs-checkbox-col">
                        <input type="checkbox" class="gqs-shipping-checkbox" name="international_freight[name]" id="international_freight[name]" value="<?php echo $international_freight_name; ?>">
                    </div>
                    <div class="gqs-label-col">
                        <div class="gqs-regular-label">
                            <label for="international_freight[name]"><?php echo $international_freight_name; ?></label> <a href="#" class="gqs-edit-shipping-label">(Edit Name)</a>
                        </div>
                        <div class="gqs-custom-label hide">
                            <input type="text" class="gqs-custom-shipping-name"  id="international_freight[custom_name]" value="<?php echo $international_freight_name; ?>">
                            <a href="#" class="gqs-hide-shipping-label">(Discard)</a>
                            <input type="hidden" class="gqs-use-custom-name-hidden" id="international_freight[use_custom_name]" value="false">
                        </div>
                    </div>
                </div>
                <div class="gqs-options-row-right">
                    <label for="international_freight[amount]"></label>
                    <input type="number" class="gqs-shipping-amount hide" id="international_freight[amount]" name="international_freight[amount]"  step="0.01" value="0" min="0" style="max-width: 100px; text-align: right;">
                    <span id="international_freight[error]" class="gqs-amount-error">Please enter an amount.</span>

                </div>
            </div>
            <div class="gqs-options-row">
                <div class="gqs-options-row-left">
                    <div class="gqs-checkbox-col">
                        <input type="checkbox" class="gqs-shipping-checkbox" name="other[name]" id="other[name]">
                    </div>
                    <div class="gqs-label-col">
                        <input type="text" class="gqs-custom-shipping-name" id="other[custom_name]" placeholder="Other - Enter Custom Shipping Name">
                        <input type="hidden" class="gqs-use-custom-name-hidden" id="other[use_custom_name]" value="false">
                    </div>
                    </div>
                <div class="gqs-options-row-right">
                    <label for="other[amount]"></label>
                    <input type="number" class="gqs-shipping-amount hide" id="other[amount]" name="other[amount]"  step="0.01" value="0" min="0" style="max-width: 100px; text-align: right;">
                    <span id="other[error]" class="gqs-amount-error">Please enter an amount.</span>
                </div>
            </div>
            <!-- <div class="gqs-other-shipping-method hide gqs-options-row">
                <div class="gqs-options-row-left">
                    <label for="gqs_other_shipping_name">Other Shipping Method</label>
                </div>
                <div class="gqs-options-row-right">
                    <input type="text" id="gqs_other_shipping_name" name="gqs_other_shipping_name" style="width: 100%; max-width: 516px;">
                </div>
            </div> -->
           
            <div class="gqs-options-row">
                <div class="gqs-options-row-left">
                <button class="button button-primary" id="gqs_reset_form">Reset Form</button>

                </div>
                <div class="gqs-options-row-right">
                    <div id="gqs_shipping_error"></div>
                    <button class="button button-primary" id="gqs_add_shipping">Add Shipping</button>
                </div>
            </div>
            <div class="gqs-options-row">
                <div id="gqs-shipping-error">
                    Please choose some shipping options before clicking "Add Shipping."
                </div>
            </div>
        </form>
        <?php
    }

    /**
     * Move the position of the order metaboxes
     */
    public function gqs_change_order_metaboxes() {
        global $wp_meta_boxes;
        // Set up the 'normal' location with 'high' priority.
        if ( empty( $wp_meta_boxes['shop_order']['normal'] ) ) {
            $wp_meta_boxes['shop_order']['normal'] = [];
        }
        if ( empty( $wp_meta_boxes['shop_order']['normal']['high'] ) ) {
            $wp_meta_boxes['shop_order']['normal']['high'] = [];
        }

        // move to the end
        $yith_ywraq_metabox_order = $wp_meta_boxes['shop_order']['normal']['high']['yith-ywraq-metabox-order'];
        unset($wp_meta_boxes['shop_order']['normal']['high']['yith-ywraq-metabox-order']);
        $wp_meta_boxes['shop_order']['normal']['high']['yith-ywraq-metabox-order'] = $yith_ywraq_metabox_order;
    }

    /**
     * Add the PDF revision field
     */
    public function gqs_yith_ywraq_metabox_fields( $array_fields, $fields, $group_2 ) {
        $array_fields['gqs_ywraq_pdf_revision_number'] = array(
            'type'   => 'inline-fields',
            'label'  => esc_html__( 'PDF Revision Number', 'yith-woocommerce-request-a-quote' ),
            'fields' => array(
                'html' => array(
                    'type' => 'number',
                    'custom_attributes' => 'placeholder="0"',
                    'std'               => '',
                    'class'             => 'number-short',
    
                ),
            ),
        );
        return $array_fields;
    }

    /**
     * Increase the revision number on save
     * Not currently being used
     */
    function gqs_update_pdf_name( $post_id ){

        // Only for shop order 
        if ( 'shop_order' != $_POST[ 'post_type' ] )
            return $post_id;

        // Checking that is not an autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return $post_id;

        // Check the user’s permissions (for 'shop_manager' and 'administrator' user roles)
        if ( ! current_user_can( 'edit_shop_order', $post_id ) && ! current_user_can( 'edit_shop_orders', $post_id ) )
            return $post_id;

        // Updating custom field data
        if( isset( $_POST['yit_metaboxes'] ) ) {
            if( isset( $_POST['yit_metaboxes']['_gqs_ywraq_pdf_revision_number'])) {
                $current_number = sanitize_text_field($_POST['yit_metaboxes']['_gqs_ywraq_pdf_revision_number']['html']);

                if($current_number >= 1) {
                    $new_number = (int) $current_number;
                    $new_number++;

                    // The new value
                    $value = array(
                        'html' => $new_number
                    );

                    // Replacing and updating the value
                    update_post_meta( $post_id, '_gqs_ywraq_pdf_revision_number', $value );
                }
                // this was for text revision names
                // if (preg_match('/^[1-9][0-9]*$/', substr($current_name, -3))) { 
                //     $current_number = (int) substr($current_name, -3);
                //     $new_number = substr($current_name, 0, -3) . $current_number++;
                // } else if (preg_match('/^[1-9][0-9]*$/', substr($current_name, -2))) { 
                //     $current_number = (int) substr($current_name, -2);
                //     $new_number = substr($current_name, 0, -2) . $current_number++;
                // } else if (preg_match('/^[1-9][0-9]*$/', substr($current_name, -1))) { 
                //     $current_number = (int) substr($current_name, -1);
                //     if($current_number == 0) {
                //         $new_number = substr($current_name, 0, -1) . '1';  
                //     } else {
                //         $new_number = substr($current_name, 0, -1) . $current_number++;
                //     }
                // } else {
                //     return false;
                // }
            }
        }
    }
    

    /**
     * Do not show discounts in subtotals in PDFs
     */
    public function filter_ywraq_show_old_price($value) {
        return 'no';
    }
    /**
     * Show the quote description in the order area
     */
    public function gqs_show_quote_description($item_id, $item, $product) {
        if(is_object($product)) {

            $is_variation = false;
            $product->get_sku();
            // first see if this line item already has a custom description
            $quote_description_custom_meta = wc_get_order_item_meta($item_id, '_gqs_quote_description_custom', true);
            $quote_description = get_post_meta($product->get_id(), 'quote_description', true);

            if($product->get_type() == 'variation') {
                $is_variation = true;
                $quote_description = get_post_meta($product->get_id(), 'quote_description', true);
                if($quote_description == '') {
                    // try to get the parent desc
                    $parent_id = $product->get_parent_id();
                    $quote_description = get_post_meta($parent_id, 'quote_description', true);
                }
            } 
            // if($quote_description != '') {
                // echo '<div class="gqs-quote-description"><strong>Quote Description: </strong>' . $quote_description . '<div class="edit"><table class="gqs-quote-description-edit"><tr><td><textarea name="gqs-quote-description[' . $item_id . ']" disabled>' . $quote_description . '</textarea></td><td><a href="#" class=name="gqs-quote-description-edit-link" data-item_id="' . $item_id . '">Edit</a><a href="#" class=name="gqs-quote-description-cancel-edit-link" data-item_id="' . $item_id . '">Cancel</a> <label><input type="checkbox" name="gqs-quote-description-update-product[' . $item_id . ']">Update Description for All</label></td></tr></table></div></div>';
                // echo '<div class="gqs-quote-description"><strong>Quote Description: </strong>' . $quote_description . '<span class="edit gqs-quote-description-edit-links"><a href="#" class="gqs-quote-description-edit-link" data-item_id="' . $item_id . '">Edit</a><a href="#" class="gqs-quote-description-cancel-edit-link hide-link" data-item_id="' . $item_id . '">Cancel</a></span><div class="gqs-quote-description-edit"><label class="gqs-quote-description-edit-label" for="gqs-quote-description[' . $item_id . ']">Enter New Quote Description:</label><textarea name="gqs-quote-description[' . $item_id . ']">' . $quote_description . '</textarea> <label><input type="checkbox" name="gqs-quote-description-update-product[' . $item_id . ']">&nbsp;Update Description for All</label></div></div>';

                // echo '<div class="gqs-quote-description"><span class="gqs-quote-description-label">Quote Description: </span><span class="gqs-quote-description-text">' . $quote_description . '</span><span class="edit gqs-quote-description-edit-links"><a href="#" class="gqs-quote-description-edit-link" data-item_id="' . $item_id . '">Edit</a></span><div class="gqs-quote-description-edit"><label class="gqs-quote-description-edit-label" for="gqs-quote-description[' . $item_id . ']">Enter New Quote Description:</label><span class="edit gqs-quote-description-edit-links"><a href="#" class="gqs-quote-description-cancel-edit-link hide-link" data-item_id="' . $item_id . '">Cancel</a></span><textarea class="gqs-quote-description-textarea" name="gqs-quote-description[' . $item_id . ']">' . $quote_description . '</textarea> <label><input type="checkbox" name="gqs-quote-description-update-product[' . $item_id . ']">&nbsp;Update Description for All</label></div></div>';
                ?>
                <div class="gqs-quote-description">
                    <?php if($quote_description_custom_meta != ''): ?>
                        <span class="gqs-quote-description-label">Custom Quote Description: </span>
                        <span class="edit gqs-quote-description-edit-links"><a href="#" class="gqs-quote-description-edit-link" data-item_id="<?php echo $item_id ; ?>">Edit</a></span>
                        <span class="gqs-quote-description-text"><?php echo wpautop($quote_description_custom_meta) ; ?></span>

                        <? // set it for the text area 
                        $quote_description = $quote_description_custom_meta;
                        ?>
                    <?php else: ?>
                        <span class="gqs-quote-description-label">Quote Description: </span>
                        <span class="edit gqs-quote-description-edit-links"><a href="#" class="gqs-quote-description-edit-link" data-item_id="<?php echo $item_id ; ?>">Edit</a></span>
                        <span class="gqs-quote-description-text"><?php echo wpautop($quote_description ); ?></span>
                    <?php endif; ?>

                    <div class="gqs-quote-description-edit">
                        <label class="gqs-quote-description-edit-label" for="gqs-quote-description[<?php echo $item_id ; ?>]">Enter New Quote Description:</label><span class="edit gqs-quote-description-edit-links"><a href="#" class="gqs-quote-description-cancel-edit-link hide-link" data-item_id="<?php echo $item_id ; ?>">Cancel</a></span>
                        <textarea class="gqs-quote-description-textarea" name="gqs-quote-description[<?php echo $item_id ; ?>]"><?php echo $quote_description ; ?></textarea>
                        <input type="hidden" class="gqs-quote-description-is-custom" name="gqs-quote-description-is-custom[<?php echo $item_id ; ?>]" value="no">
                        <?php if($is_variation): ?>
                            <div><label><input type="checkbox" name="gqs-quote-description-update-product-variation[<?php echo $item_id ; ?>]">&nbsp;Update description for this variation</label></div>

                            <div><label><input type="checkbox" name="gqs-quote-description-update-product-parent[<?php echo $item_id ; ?>]">&nbsp;Update description for parent product</label></div>
                        <?php else: ?>
                            <div><label><input type="checkbox" name="gqs-quote-description-update-product-parent[<?php echo $item_id ; ?>]">&nbsp;Update description for product</label></div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php
            // }
        }
    }

    /**
     * Show the Original Price in the order area
     */
    public function gqs_show_original_price_header($order) {
        echo '<th class="gqs-original-price" style="text-align: right;">Original Cost</th>';
    }
    
    public function gqs_show_original_price_value($product, $item, $item_id) {
        $current_cost = 'null';
        $original_price_html = '';
        if(is_object( $product )) {
            $current_cost = $item->get_total() / $item->get_quantity();
            $current_cost = number_format($current_cost, 2, '.', '');
            $original_price_html = wc_price($product->get_price());
        }
        echo '<td class="gqs-original-price" style="text-align: right;">' . $original_price_html . '<input type="hidden" name="gqs_current_item_cost" value="' . $current_cost . '"></td>';

    }
    /**
     * Adds hidden fields used to hide the vouchers column, set the site,
     * the correct subtotal and the correct total.
     */
    public function gqs_show_subtotal_without_vouchers($order_id) {
        $site = GQS_Site_Utils::get_gineico_site_abbreviation();

        $order = wc_get_order($order_id);
        $order_subtotal = $order->get_subtotal() - $order->get_discount_total();
        $order_subtotal = number_format((float)$order_subtotal, 2, '.', '');
        $order_total = $order->get_total();
        $total_ex_gst = $order->get_subtotal() - $order->get_discount_total() + $order->get_shipping_total();

        echo '<input type="hidden" name="gineico_site" value="' . $site . '">';
        echo '<input type="hidden" name="gqs_order_subtotal" value="' . $order_subtotal . '">';
        ?>
        <tr>
            <td class="label">Total Ex. GST:</td>
            <td width="1%"></td>
            <td class="total"><?php echo wc_price($total_ex_gst); ?></td>
        </tr>

        <?php 
        if($site == 'GL') {
            $order_gst = round((floatval($order->get_total()) * .1), 2);
            $order_total = number_format((float)$order_total + $order_gst, 2, '.', '');
            echo '<input type="hidden" name="gqs_order_true_total" value="' . $order_total . '">';

            ?>

            <tr>
				<td class="label">GST:</td>
				<td width="1%"></td>
				<td class="total"><?php echo wc_price($order_gst); ?></td>
			</tr>
            <?php
        }
    }

     /**
     * Add the resend quote action to order actions select box on edit order page
     * Only added for Pending Quote orders
     *
     * @param array $actions order actions array to display
     * @return array - updated actions
     */
    public function add_action_to_order_actions_box($actions) {
        global $theorder;
        $order_data = $theorder->get_data();
        if($order_data['status'] != 'ywraq-pending') {
            return $actions;
        }
        $actions['wc_resend_quote_email_action'] = __('Resend Quote Email', 'gineicolighting');
        return $actions;
    }

    /**
     * Add an order note when quote resend
     *
     * @param \WC_Order $order
     */
    public function wc_resend_quote_email_handler($order) {
        $message = sprintf(__('Quote details email resent by %s.', 'gineicolighting'), wp_get_current_user()->display_name);
        $order->add_order_note($message);

        $mailer = WC()->mailer();
        $mails = $mailer->get_emails();
        if(!empty($mails)) {
            foreach($mails as $mail) {
                if($mail->id == 'ywraq_send_quote') {
                    $mail->trigger($order->get_id());
                }
            }
        }
    }

} // end class

$gqs_woocommerce_order = new GQS_WooCommerce_Order();