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
            add_filter( 'woocommerce_get_sku', array($this, 'gqs_change_woocommerce_sku_display'), 10, 2 );
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

        $main_brand_prefix = '';

        // Retrieve associated brand terms
        $brands = get_the_terms( $product->get_id(), 'brands' );

        if(is_array($brands) && !empty($brands) && isset($brands[0])) {
            // get the first brand
            $main_brand = $brands[0];
            $main_brand_prefix = get_field('brand_prefix', 'brands' . '_' . $main_brand->term_id);
        }

        if($main_brand_prefix != '') {
            // remove any GL or GM at the beginning
            $current_gineico_site = GQS_Site_Utils::get_gineico_site_abbreviation();
            $sku = str_replace($current_gineico_site . '-', $main_brand_prefix . '-', $sku);
        }
        return $sku;

    }

} // end class

$gqs_woocommerce_product = new GQS_WooCommerce_Product();