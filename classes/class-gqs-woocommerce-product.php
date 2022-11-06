<?php 

namespace Gineicio\QuotingSystem;

class GQS_WooCommerce_Product {

  
    public function __construct() {
                
        if(site_url() != "https://www.gineicomarine.com.au" && site_url() != "https://gineicomarine.dev.dustysun.com") {
           
            // add product notes metabox
            add_action( 'add_meta_boxes', array($this, 'gqs_product_admin_add_meta_boxes'), 40 );

            // move the product notes metabox
            add_action( 'current_screen', array($this,'gqs_woocommerce_product_admin'), 10, 1 );

            // save the product notes
            add_action( 'save_post_product', array($this, 'gqs_save_product_admin_fields'), 10, 3 );

            // change the display of the sku by adding the brand on the product page
            add_filter( 'woocommerce_product_get_sku', array($this, 'gqs_change_woocommerce_sku_display'), 10, 2 );
            add_filter( 'woocommerce_product_variation_get_sku', array($this, 'gqs_change_woocommerce_sku_display'), 10, 2 );

            // add CSS to modify the behavior of the request a quote button when the options
            // are not all selected
            // add_action( 'woocommerce_before_single_variation', array($this, 'gqs_add_variations_button_to_single_product'), 10 );

            // ajax to handle adding products without all options selected
            // add_action( 'wp_ajax_nopriv_gqs_add_parent_product_to_quote', array($this, 'gqs_add_parent_product_to_quote') );
            // add_action( 'wp_ajax_gqs_add_parent_product_to_quote', array($this, 'gqs_add_parent_product_to_quote') );

            // change the choose an option text on variable products
            add_filter( 'woocommerce_dropdown_variation_attribute_options_args', array($this, 'gqs_change_variation_dropdown'), 10 , 1 );

            // filter the options being added so that variations can be added 
            // without all options being chosen
            add_filter( 'ywraq_add_item', array($this, 'filter_product_raq'), 10, 2 );

            // change the id for the key for each quote item to allow adding the 
            // parent product multiple times with different incomplete options
            add_filter( 'ywraq_quote_item_id', array($this, 'filter_quote_item_id'), 10, 3 );

            // filter the check to see if an item exists in the list so we can check for 
            // our custom parent items that have been added
            add_filter( 'ywraq_exists_in_list', array($this, 'filter_exists_in_list'), 10, 5 );
        }

    }

    /**
     * Add the metabox in the product screen
     */
    public function gqs_product_admin_add_meta_boxes() {
        add_meta_box( 
            'gqs_product_notes', 
            __( 'Notes' ), 
            array($this, 'gqs_product_admin_metabox_callback'), 
            'product', 
            'normal', 
            'high'
        );
    }
    public function gqs_product_admin_metabox_callback( $post ) {

        ?>

            <div class="gqs-options-row header">
                <p>These notes are not shown anywhere on the product and are only visible in the backend.</p>
            </div>
            <div class="gqs-options-row">
                <div class="gqs-options-row-left">
                    
                <!-- <label for="gqs_woocommerce_product_notes"><?php //_e( 'Product Notes', 'gineico' ); ?></label> -->
                    <?php 

                    $gqs_woocommerce_product_notes_value = get_post_meta( $post->ID, '_gqs_woocommerce_product_notes', true );

                    $settings = array('wpautop' => true, 'media_buttons' => true, 'quicktags' => true, 'textarea_rows' => '10', 'textarea_name' => '_gqs_woocommerce_product_notes' );

                    wp_editor(html_entity_decode($gqs_woocommerce_product_notes_value, ENT_QUOTES, 'UTF-8'), 'gqs_woocommerce_product_notes', $settings);

                    ?>

                </div>
                
            </div>
            
        <?php
    }

    /**
     * Add filters on the product screen
     */
    public function gqs_woocommerce_product_admin($current_screen) {
        if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
            if($current_screen->id == 'product') {
              
                // move the metaboxes
                add_action( 'add_meta_boxes', array($this, 'gqs_product_change_order_metaboxes'), 100 );

            }
        } // end if 
    }

    /**
     * Move the position of the product metaboxes
     */
    public function gqs_product_change_order_metaboxes() {

        global $wp_meta_boxes;

        // new variable to store the sorted array
        $new_product_normal_high_order = array();

        $gqs_product_notes_key = $wp_meta_boxes['product']['normal']['high']['gqs_product_notes'];

        foreach( $wp_meta_boxes['product']['normal']['high'] as $key => $field ) {
            if($key == 'gqs_product_notes') {
                continue;
            }
            if($key == 'woocommerce-product-data') {
                // insert the product notes box before this one
                $new_product_normal_high_order['gqs_product_notes'] = $gqs_product_notes_key;
            }
            $new_product_normal_high_order[$key] = $field;

        }
     
        // set the revised order back to the proper key
        $wp_meta_boxes['product']['normal']['high'] = $new_product_normal_high_order;
      
    }
    
    /**
     * Save any edited custom fields
     */
    public function gqs_save_product_admin_fields( $post_id, $post, $update ) {

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
            return;

        if ( $parent_id = wp_is_post_revision( $post_id ) ) {
            $post_id = $parent_id;
        }

        $fields = [
            '_gqs_woocommerce_product_notes',
        ];

        foreach ( $fields as $field ) {
            if ( array_key_exists( $field, $_POST ) ) {
                update_post_meta( $post_id, $field, sanitize_text_field( htmlentities($_POST[$field]) ) );
            }
        }
        
    }  
   
    /**
     * Filter the product SKU display and add the brand prefix if it exists
     */
    public function gqs_change_woocommerce_sku_display( $sku, $product ) {

        if($sku == '') 
            return;

        $main_brand_prefix = self::get_brand_prefix($product);

        if($main_brand_prefix != '') {
            // see if it already starts with the prefix and if so, continue
            if(substr($sku, 0, strlen($main_brand_prefix . '-')) !== $main_brand_prefix . '-') {
                 // remove any GL or GM at the beginning
                $current_gineico_site = GQS_Site_Utils::get_gineico_site_abbreviation();
                $sku = str_replace($current_gineico_site . '-', '', $sku);
                $sku = $main_brand_prefix . '-' . $sku;
            }
        }
        return $sku;

    }

    /**
     * Gets the brand prefix of the first/main brand selected in the brands
     * taxonomy when given a product.
     */
    public static function get_brand_prefix($product) {

        $product_id = $product->get_id();

        // if this is a varation get the parent product id
        if($product->get_type() == 'variation') {
            $product_id = $product->get_parent_id();
        } 
        
        $main_brand_prefix = '';

        // Retrieve associated brand terms
        $brands = get_the_terms( $product_id, 'brands' );

        if(is_array($brands) && !empty($brands) && isset($brands[0])) {
            // get the first brand
            $main_brand = $brands[0];
            $main_brand_prefix = get_field('brand_prefix', 'brands' . '_' . $main_brand->term_id);
        }

        return $main_brand_prefix;

    }
 
     /**
     * Add CSS to modify the behavior of the request a quote button when the options
     * are not all selected
     * Disabled 10/13/2022
     */
    // public function gqs_add_variations_button_to_single_product() {

        // echo '<style type="text/css">';
        
        // echo ".woocommerce-variation-add-to-cart-disabled + .yith-ywraq-add-to-quote .yith-ywraq-add-button .add-request-quote-button.disabled:after {
        //         content: '';
        //         position: absolute;
        //         top: 0;
        //         left: 0;
        //         width: 100%;
        //         height: 100%;
        //         background: #020202;
        //         display: flex;
        //         flex-direction: row;
        //         align-items: center;
        //         justify-content: center;
        //         opacity: 0;
        //     }";
        // echo ".woocommerce-variation-add-to-cart-disabled + .yith-ywraq-add-to-quote  .yith-ywraq-add-button:hover {
        //         cursor: pointer;
        //     }";
        // echo ".woocommerce-variation-add-to-cart-disabled + .yith-ywraq-add-to-quote .yith-ywraq-add-button:hover .add-request-quote-button.disabled {
        //         opacity: 1 !important;
        //     }";
        // echo ".woocommerce-variation-add-to-cart-disabled + .yith-ywraq-add-to-quote .yith-ywraq-add-button:hover .add-request-quote-button.disabled:after {
        //         opacity: 1;
        //     }";
        // echo '</style>';

    // }


    /**
     * Ajax function to add variable products to the quote even
     * though not all options are selected
     * Disabled 10/13/2022
     */
    // public function gqs_add_parent_product_to_quote() {

    //     // get the current quote
    //     $current_user_quote = YITH_Request_Quote();
     
    //     parse_str($_POST['product_options'], $product_options);

    //     $gqs_product_attributes = array();
    //     foreach($product_options as $key => $value) {
    //         if( substr( $key, 0, 9 ) === 'attribute' ) {
    //             $label = str_replace( 'attribute_', '', $key );
    //             $label = str_replace( '-', ' ', $label );
    //             $label = ucwords($label);
    //             $gqs_product_attributes[$key]['name'] = $label;
    //             $gqs_product_attributes[$key]['value'] = $value;
    //         }
    //     }
    //     $product_raq = array(
    //         'product_id' => sanitize_text_field($product_options['product_id']),
    //         'quantity'   => sanitize_text_field($product_options['quantity']),
    //         'gqs_product_attributes' => $gqs_product_attributes
    //     );

    //     // create a key name 
    //     $key_name = $product_raq['product_id'];
    //     foreach( $product_raq['gqs_product_attributes'] as $attribute ) {
    //         $key_name .= ',' . $attribute['value'];
    //     }
    //     $key_name = md5( $key_name );

    //     // see if it exists and if not add it
    //     if( $this->gqs_product_exists_in_quote($key_name) ) {
    //         $status = 'exists';
    //         $message = $current_user_quote->errors;
    //     } else {
    //         $current_user_quote->raq_content[ apply_filters( 'ywraq_quote_item_id', $key_name, $product_raq, $product_raq['product_id'] ) ] = $product_raq;
    //         $current_user_quote->set_session( $current_user_quote->raq_content );
    //         $current_user_quote->maybe_set_raq_cookies();
    //         $status = 'true';
    //         $message = 'Product added.';
    //     }

    //     $return_info = array(
    //         'result' => $status,
    //         'message' => $message
    //     );

    //     wp_send_json($return_info);
    
    // }
    /**
     * Check if a given key exists within the current quote
     * Disabled 10/13/2022
     */
    // public function gqs_product_exists_in_quote( $key_name ) {

    //     // get the current quote
    //     $current_user_quote = YITH_Request_Quote();

    //     if ( array_key_exists( $key_name, $current_user_quote->raq_content ) ) {
    //         $current_user_quote->errors[] = __( 'Product already in the list.', 'yith-woocommerce-request-a-quote' );
    //         return true;
    //     }

    //     return false;
        
    // }



    /**
     * Filter the attributes stored for variable products without all
     * variation items selected
     */
    public function filter_product_raq($raq, $product_raq) {

        // get the product 
        $product = wc_get_product( $product_raq['product_id'] );
        if($product->get_type() == 'variable' && $product_raq['variation_id'] == '') {
            $gqs_product_attributes = array();
            foreach($product_raq as $key => $value) {
                if( substr( $key, 0, 9 ) === 'attribute' ) {
                    $label = str_replace( 'attribute_', '', $key );
                    $label = str_replace( '-', ' ', $label );
                    $label = str_replace( 'pa_', '', $label );
                    $label = ucwords($label);
                    $gqs_product_attributes[$key]['name'] = $label;
                    $gqs_product_attributes[$key]['value'] = ucwords($value);
                }
            }

            $raq['gqs_product_attributes'] = $gqs_product_attributes;
        }
        return $raq;
    }
    /** 
     * Change the product key in the quote for products without all 
     * variation items selected
     */
    public function filter_quote_item_id($key_name, $product_raq, $product_id) {

        $product = wc_get_product( $product_raq['product_id'] );
        if($product->get_type() == 'variable' && $product_raq['variation_id'] == '') {
            // create a key name 
            $key_name = $this->build_custom_ywraq_key_name($product_raq['product_id'], $product_raq);
        }
        return $key_name;
    }

    /**
     * Filter the key name for parent products that were added without full
     * variations being selected
     */
    public function filter_exists_in_list( $return, $product_id, $variation_id, $postadata, $raq_content ) {
        
        $product = wc_get_product( $product_id );
        if($product->get_type() == 'variable' && $product_raq['variation_id'] == '') {
            $key_name = $this->build_custom_ywraq_key_name($product_id, $_POST);
            $current_user_quote = YITH_Request_Quote();
            if ( array_key_exists( $key_name, $current_user_quote->raq_content ) ) {
                $current_user_quote->errors[] = __( 'Product already in the list.', 'yith-woocommerce-request-a-quote' );
                $return         = true;
            }
        }
       
        return $return;
    }

    /**
     * Construct a custom key name based on the product ID and the chosen
     * variation options
     */
    private function build_custom_ywraq_key_name( $product_id, $product_data ) {
        $key_name = $product_id;
        foreach( $product_data as $key => $attribute ) {
            if( substr( $key, 0, 9 ) === 'attribute' ) {
                $key_name .= ',' . $key . '_' . $attribute;
            }
        }
        $key_name = md5( $key_name );
        return $key_name;
    }

    /**
     * Change the choose an option text for variable products
     */
    public function gqs_change_variation_dropdown( $args ) {
        $args['show_option_none'] = __( 'Choose an option if known', 'woocommerce' );
        return $args;
    }

} // end class

$gqs_woocommerce_product = new GQS_WooCommerce_Product();