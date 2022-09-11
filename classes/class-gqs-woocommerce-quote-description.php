<?php 

namespace Gineicio\QuotingSystem;

class GQS_WooCommerce_Quote_Description {

  
    public function __construct() {
        // Add and save Quote Description field for simple products
        add_action( 'woocommerce_product_options_advanced', array($this, 'gqs_add_quote_description_to_advanced_tab') ); 
        add_action( 'woocommerce_process_product_meta', array($this, 'gqs_save_quote_description_main_product') );

        // Add and save Quote Description field for variable products
        add_action( 'woocommerce_product_after_variable_attributes', array($this, 'gqs_add_quote_description_to_variations'), 20, 3 );
        add_action( 'woocommerce_save_product_variation', array($this, 'gqs_save_quote_description_variations'), 10, 2 );

        // Save the custom Quote Description when editing a manual order
        add_action( 'woocommerce_before_save_order_items', array($this, 'gqs_save_custom_order_items'), 10, 2 );
        add_filter( 'woocommerce_hidden_order_itemmeta', array($this, 'gqs_hide_quote_description_custom_meta'), 10, 1 );
        
    }
    
    /**
     * Add the Quote Description field for simple or parent products under the advanced tab
     */
    public function gqs_add_quote_description_to_advanced_tab($product) {

        $product = wc_get_product(get_the_ID());

        woocommerce_wp_textarea_input(
            array(
                'id'            => "quote_description_adv",
                'name'          => "quote_description_adv",
                'value'         => get_post_meta( get_the_ID(), 'quote_description', true ),
                'label'         => __( 'Quote Description', 'woocommerce' ),
                'wrapper_class' => 'form-row form-row-full',
                'class'         => 'long quote-description',
            )
        );
        echo '<style>.quote-description {min-height: 200px;}</style>';
        echo '<p class="form-field quote_description_field_label form-row form-row-full">';
        if($product->is_type( 'variable' )) {
            echo '<label></label>This field will show on the PDF, but if you write a different description in the options for a variation, then this field will be ignored.</p>';
        } else {
            echo '<label></label>This field will show on the PDF.</p>';
        }

    }

    /**
     * Save the Quote Description field for simple products
     */
    public function gqs_save_quote_description_main_product($post_id) {
        $quote_description = $_POST['quote_description_adv'];
        if ( isset( $quote_description ) ) update_post_meta( $post_id, 'quote_description', sanitize_textarea_field( $quote_description ) );       
    }
    /**
     * Add the Quote Description field for Variations under Product Data
     */
    public function gqs_add_quote_description_to_variations( $loop, $variation_data, $variation ) {
        woocommerce_wp_textarea_input(
            array(
                'id'            => "quote_description{$loop}",
                'name'          => "quote_description[{$loop}]",
                'value'         => get_post_meta( $variation->ID, 'quote_description', true ),
                'label'         => __( 'Quote Description', 'woocommerce' ),
                'desc_tip'      => true,
                'description'   => __( 'Enter a description that will show on the quote for this variation.', 'woocommerce' ),
                'wrapper_class' => 'form-row form-row-full',
                'class'         => 'long quote-description',
            )
        );
    }
    
    /** 
     * Save the variation Quote Description field
     */   
    public function gqs_save_quote_description_variations( $variation_id, $i ) {
        $quote_description = $_POST['quote_description'][$i];
        if ( isset( $quote_description ) ) update_post_meta( $variation_id, 'quote_description', sanitize_textarea_field( $quote_description ) );
    }
    
    /**
     * Handle the custom quote description field when saving line items
     * in a manual order
     */
    public function gqs_save_custom_order_items($order_id, $items) {
        $gqs_quote_description = isset($items['gqs-quote-description']) ? $items['gqs-quote-description'] : '';
        $gqs_quote_description_is_custom = isset($items['gqs-quote-description-is-custom']) ? $items['gqs-quote-description-is-custom'] : '';
        $gqs_quote_description_update_product_variation = isset($items['gqs-quote-description-update-product-variation']) ? $items['gqs-quote-description-update-product-variation'] : '';
        $gqs_quote_description_update_product_parent = isset($items['gqs-quote-description-update-product-parent']) ? $items['gqs-quote-description-update-product-parent'] : '';

        if(is_array($gqs_quote_description_is_custom)) {
            foreach ($gqs_quote_description_is_custom as $item_id => $custom) {
                if($custom == 'yes') {
                    $custom_quote_description = $gqs_quote_description[$item_id];
                    wc_update_order_item_meta($item_id, '_gqs_quote_description_custom', sanitize_textarea_field($custom_quote_description), false);
                    if(is_array($gqs_quote_description_update_product_variation) && isset($gqs_quote_description_update_product_variation[$item_id])) {
                        // get the product ID
                        $product_id = wc_get_order_item_meta($item_id, '_product_id');
                        $variation_id = wc_get_order_item_meta($item_id, '_variation_id');
                        update_post_meta( $variation_id, 'quote_description', sanitize_textarea_field( $custom_quote_description ) );

                    }
                    if(is_array($gqs_quote_description_update_product_parent) && isset($gqs_quote_description_update_product_parent[$item_id])) {
                        // get the product ID
                        $product_id = wc_get_order_item_meta($item_id, '_product_id');
                        update_post_meta( $product_id, 'quote_description', sanitize_textarea_field( $custom_quote_description ) );

                    }
                }
            }
        }     

    }
    /**
     * Hide the custom meta field if added
     */
    function gqs_hide_quote_description_custom_meta($arr) {
        $arr[] = '_gqs_quote_description_custom';
        return $arr;
    }
   
} // end class

$gqs_woocommerce_quote_description = new GQS_WooCommerce_Quote_Description();