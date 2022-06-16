<?php
/**
 * HTML Template Quote table
 *
 * @package YITH Woocommerce Request A Quote
 * @since   1.0.0
 * @version 2.2.7
 * @author  YITH
 *
 * @var WC_Order $order
 */

$border   = true;
$order_id = $order->get_id();

if ( function_exists( 'icl_get_languages' ) ) {
	global $sitepress;
	$lang = $order->get_meta( 'wpml_language' );
	YITH_Request_Quote_Premium()->change_pdf_language( $lang );
}
add_filter( 'woocommerce_is_attribute_in_product_name', '__return_false' );

$primary_link_color = Gineicio\QuotingSystem\GQS_Site_Utils::get_gineico_primary_link_color();

?>

<?php
$after_list = $order->get_meta( '_ywcm_request_response' );
if ( '' !== $after_list ) :
	?>
	<div class="after-list">
		<p><?php echo wp_kses_post( apply_filters( 'ywraq_quote_before_list', nl2br( $after_list ), $order_id ) ); ?></p>
	</div>
<?php endif; ?>

<?php do_action( 'yith_ywraq_email_before_raq_table', $order ); ?>

<?php
$columns = get_option( 'ywraq_pdf_columns', 'all' );
/* be sure it is an array */
if ( ! is_array( $columns ) ) {
	$columns = array( $columns );
}
$colspan = 0;

?>
<div class="table-wrapper">
    <div class="mark"></div>
    <h5 style="margin: 0 0 10px 0; font-style: italic; font-weight: normal;">Product images are indicative only</h5>
    <table class="quote-table" cellspacing="0" cellpadding="6" style="width: 100%;" border="0">
        <thead>
        <tr>
            <?php if( get_option('ywraq_show_preview') == 'yes'): ?>
                <th scope="col" style="text-align:left; border-bottom: 1px solid #777; border-left: 1px solid #777;border-top: 1px solid #777;" class="image-col"><?php _e( 'Image', 'yith-woocommerce-request-a-quote' ); ?></th>
            <?php endif ?>
            <th scope="col"  style="text-align:left; border-bottom: 1px solid #777; border-left: 1px solid #777;border-top: 1px solid #777;" class="type-col"><?php _e( 'Type', 'yith-woocommerce-request-a-quote' ); ?></th>
            <th scope="col"  style="text-align:left; border-bottom: 1px solid #777; border-left: 1px solid #777;border-top: 1px solid #777;" class="qty-col"><?php _e( 'QTY', 'yith-woocommerce-request-a-quote' ); ?></th>
            <th scope="col"  style="text-align:left; border-bottom: 1px solid #777; border-left: 1px solid #777;border-top: 1px solid #777;" class="part-num-col"><?php _e( 'Part No', 'yith-woocommerce-request-a-quote' ); ?></th>
            <th scope="col"  style="text-align:left; border-bottom: 1px solid #777; border-left: 1px solid #777;border-top: 1px solid #777;" class="product-col"><?php _e( 'Product', 'yith-woocommerce-request-a-quote' ); ?></th>
            <th scope="col"  style="text-align:left; border-bottom: 1px solid #777; border-left: 1px solid #777;border-top: 1px solid #777;" class="unit-col"><?php _e( 'Unit Price', 'yith-woocommerce-request-a-quote' ); ?></th>
            <th scope="col"  style="text-align:left; border-bottom: 1px solid #777; border-left: 1px solid #777;border-top: 1px solid #777; border-right: 1px solid #777;" class="subtotal-col"><?php _e( 'Subtotal', 'yith-woocommerce-request-a-quote' ); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php
        $items = $order->get_items();
        $currency = method_exists( $order,  'get_currency') ? $order->get_currency() :  $order->get_order_currency();
        $colspan = 5;
        if( ! empty( $items ) ):

            foreach( $items as $item_id => $item ):
              
                if( isset( $item['variation_id']) && $item['variation_id'] ){
                    $_product = wc_get_product( $item['variation_id'] );
                }else{
                    $_product = wc_get_product( $item['product_id'] );
                }

                $title = $_product->get_title();

                if( $_product->get_sku() != '' && get_option('ywraq_show_sku') == 'yes' ){
                    $title .= ' '.apply_filters( 'ywraq_sku_label', __( ' SKU:', 'yith-woocommerce-request-a-quote' ) ) . $_product->get_sku();
                }

                $subtotal = wc_price( $item['line_total'] , array( 'currency' => $currency) );
                $unit_price = wc_price( $item['line_total']/$item['qty'], array( 'currency' => $currency ) );

                if ( get_option( 'ywraq_show_old_price' ) == 'yes' ) {
                    $subtotal = ( $item['line_subtotal'] != $item['line_total'] ) ? '<small><del>' . wc_price( $item['line_subtotal'], array( 'currency' => $currency) ) . '</del></small> ' . wc_price( $item['line_total'], array( 'currency' => $currency) ) : wc_price( $item['line_subtotal'] , array( 'currency' => $currency));
                    $unit_price = ( $item['line_subtotal'] != $item['line_total'] ) ? '<small><del>' . wc_price( $item['line_subtotal']/$item['qty'], array( 'currency' => $currency) ) . '</del></small> ' . wc_price( $item['line_total']/$item['qty'] ) : wc_price( $item['line_subtotal']/$item['qty'] , array( 'currency' => $currency));
                }

                //$meta = yith_ywraq_get_product_meta_from_order_item( $item['item_meta'], false );
	            $im = false;
	            if ( version_compare( WC()->version, '2.7.0', '<' ) ) {
	                $im = new WC_Order_Item_Meta( $item );
	            }

                ?>
                <tr>
                    <?php if( get_option('ywraq_show_preview') == 'yes'): ?>
                        <td scope="col" style="text-align:center; border-left: 1px solid #777; border-color: #777;">
                            <?php
                            $image_id = $_product->get_image_id();
                            if ( $image_id ) {
	                            $thumbnail_id  = $image_id;
	                            $thumbnail_url = apply_filters( 'ywraq_pdf_product_thumbnail', get_attached_file( $thumbnail_id ), $thumbnail_id );
                            } else {
	                            $thumbnail_url = function_exists( 'wc_placeholder_img_src' ) ? wc_placeholder_img_src() : '';
                            }
                            $thumbnail = sprintf( '<img src="%s" style="max-width:100px; max-height:87px;"/>', $thumbnail_url );

                            $colspan = 6;
                            if ( ! $_product->is_visible() ) {
	                            echo $thumbnail;
                            } else {
	                            printf( '<a href="%s">%s</a>', $_product->get_permalink(), $thumbnail );
                            }
                            ?>
                        </td>
                    <?php endif ?>
                    <!-- BEGIN GQS CUSTOM -->
                    <td scope="col" style="text-align:center; border-left: 1px solid #777; border-color: #777;" class="type-col"><?php echo wc_get_order_item_meta( $item->get_id(), '_gqs_quote_type', true );?></td>

                    <td scope="col" style="text-align:center; border-left: 1px solid #777; border-color: #777;" class="qty-col"><?php echo $item['qty'] ?></td>

                    <td scope="col" style="text-align:center; border-left: 1px solid #777; border-color: #777;" class="part-no-col"><?php echo wc_get_order_item_meta( $item->get_id(), '_gqs_quote_part_number', true );?></td>
                    <!-- EHD GQS CUSTOM -->

                    <td scope="col" style="text-align:left; border-left: 1px solid #777; border-color: #777;" class="product-desc">
                    <?php //echo $title
                    //BEGIN GQS CUSTOM
                    //app.launchURL("http://www.mycompany.com/pdfDocument.pdf", true);
                    echo '<a style="text-decoration: none; color: ' . $primary_link_color . '; font-weight: bold;" target="_blank" href="' . esc_url( $_product->get_permalink() ) . '">' . esc_html( $title ) . '</a>';
                    // see if the item order meta quote description is set
                    $quote_description = wc_get_order_item_meta($item_id, '_gqs_quote_description_custom', true);
                    if($quote_description == '') {
                        // see if the quote description is set 
                        $quote_description = get_post_meta($_product->get_id(), 'quote_description', true);

                        if($_product->get_type() == 'variation') {
                            if($quote_description == '') {
                                // try to get the parent desc
                                $parent_id = $_product->get_parent_id();
                                $quote_description = get_post_meta($parent_id, 'quote_description', true);
                            }
                        } 
                    }
                   
                    if($quote_description != ''):
                        ?>
                        <small>
                            <div class="quote-description">
                                <?php echo $quote_description; ?>
                            </div>
                        </small>
                        <?php 
                    // END GQS CUSTOM 

                    else:

                    ?>
                       <small><?php

                            //BEGIN GQS CUSTOM	
							echo '<div class="product-description">';
							$product_id  = '';
							$variation_description = '';
							if($_product->is_type('variable') || $_product->is_type('variation')) {
								$product_id = $_product->get_parent_id();
								$variation_description_raw = strip_tags($_product->get_variation_description());
								if($variation_description_raw != '' && $variation_description_raw != null) {
									$variation_description = '<ul class="wc-item-meta"><li><p><strong class="wc-item-meta-label" style="vertical-align: top;">Description:&nbsp;</strong>' . $variation_description_raw . '</p></li></ul>';
								}

							} else if($_product->is_type('simple')) {
								$product_id = $_product->get_id();
							}
							if($product_id != '') {
								$product = wc_get_product($product_id);
								$product_short_description = $product->get_short_description();

									echo strip_tags( substr($product->get_short_description(), 0 , 110)) . '&hellip; <a style="text-decoration: none; color: ' . $primary_link_color . ';" target="_blank" href="' . esc_url( $_product->get_permalink() ) . '">Read More</a>';
								
							}
							echo '</div>';
                            echo $variation_description;
                            //END GQS CUSTOM

						   if ( $im ) {
		                       $im->display();
	                       } else {
                                // wc_display_item_meta( $item );
                                // Customized wc_display_item_meta funciton
                                foreach ( $item->get_all_formatted_meta_data() as $meta_id => $meta ) {
                                    $value     = strip_tags(trim( $meta->display_value ));
                                    $strings[] = '<span class="wc-item-meta-label" style="display: inline-block"><strong>' . wp_kses_post( $meta->display_key ) . ':</strong></span>&nbsp;<span class="wc-item-meta-value" style="display: inline-block">' . $value . '</span>';
                                }
                                if ( $strings ) {
                                    $html = '<span style="display: block; height: 5px;">&nbsp;</span><ul class="wc-item-meta"><li>' . implode( '</li><li>', $strings ) . '</li></ul>';
                                }
                        
                                echo $html;
	                       }
	                       ?></small>
                           
                           <?php 
                            endif; // if quote_description
                            ?>
                           
                           </td>
                    <td scope="col" style="text-align:center; border-left: 1px solid #777; border-color: #777;"><?php echo $unit_price ?></td>
                    <td scope="col" class="last-col" style="text-align:right; border-left: 1px solid #777; border-right: 1px solid #777; border-color: #777;"><?php echo apply_filters('ywraq_quote_subtotal_item', ywraq_formatted_line_total( $order, $item ), $item['line_total'], $_product); ?></td>
                </tr>

            <?php
            endforeach; ?> 

            <?php
            $bottom_table_array = array();
            foreach ( $order->get_order_item_totals() as $key => $total ) {
                ob_start();
                if($key == 'shipping') {
                    $selected_shipping_methods = array();
                    foreach( $order->get_shipping_methods() as $shipping_method ) {
                        if($shipping_method->get_total() == 0) {
                            $amount = '&#8211;';
                        } else {
                            $amount = wc_price( $shipping_method->get_total(), get_woocommerce_currency_symbol() );
                        }
                        $selected_shipping_methods[] = array(
                            'name' => $shipping_method->get_name(),
                            'amount' => $amount
                        );
                    }
                    $shipping_method_count = count($selected_shipping_methods);

                    $i = 1;
                    foreach($selected_shipping_methods as $this_shipping_method) {
                        // this matches a single shipping method or the last of multiple methods
                        if($i == $shipping_method_count) {                 
                            $shipping_additional_classes = 'border-bottom: 1px solid #777;';
                        } else if($i < $shipping_method_count)
                        if($shipping_method_count >= 1) {
                            $shipping_additional_classes = 'border-bottom: none;';
                        }
                        ?>
                        <tr>
                        <?php
                        if($i == 1) {
                            ?>
                            <th scope="col" colspan="3"></th>
                            <th scope="col" style="text-align:left; border-left: 1px solid #777; border-top: 1px solid #777; <?php echo $shipping_additional_classes; ?>"><strong>Freight</strong></th>
                        <?php
                        } else if($i > 1) {

                        ?>
                            <th scope="col" colspan="3"></th>
                            <th scope="col" style="text-align:left; border-left: 1px solid #777; border-top: 1px solid #777; <?php echo $shipping_additional_classes; ?>"></th>
                        <?php
                        } // end if
                        ?>
                            <td scope="col" colspan="2" style="text-align:right; border-left: 1px solid #777; border-top: 1px solid #777;border-color: #777; <?php echo $shipping_additional_classes; ?>"><?php echo $this_shipping_method['name']; ?></td>
                            <td scope="col" style="text-align:right; border-left: 1px solid #777; border-top: 1px solid #777; border-right: 1px solid #777;  <?php echo $shipping_additional_classes; ?>" class="shipping-col"><?php echo  $this_shipping_method['amount']; ?></td>
                            </tr>
                        <?php
                            $i++;
                        } // end foreach
                        ?>

                    <?php 
                    
                } else {

                    ?>
                    <tr>
                        <th scope="col" colspan="3"></th>
                        <?php 
                            $total_label_additional_classes = 'border-bottom: 1px solid #777;';
                            if($total['label'] == 'Subtotal:') {
                            // there may be "discounts so include those
                            $total['value'] = wc_price($order->get_subtotal() - $order->get_discount_total());
                            if(count($order->get_shipping_methods()) >= 1) {
                                $total_label_additional_classes = 'border-bottom: none;';
                            }
                        }
                        ?>
                        <th scope="col" colspan="3" style="text-align:right;"><?php echo $total['label']; ?></th>

                        <td scope="col" class="last-col" style="text-align:right; border-left: 1px solid #777; border-right: 1px solid #777; border-color: #777; <?php echo $total_label_additional_classes; ?>"><?php echo $total['value']; ?></td>
                    </tr>
                    <?php 
                }
              
                $bottom_table_array[$key] = ob_get_clean();
            } 
            
            echo $bottom_table_array['cart_subtotal'];
            if(isset($bottom_table_array['shipping'])) {
                echo $bottom_table_array['shipping'];
            }
            echo $bottom_table_array['order_total'];

            $order_gst = round((floatval($order->get_total()) * .1), 2);
            $order_total_with_gst = floatval($order_gst) + floatval($order->get_total());
            ?>
            <tr>
                <th scope="col" colspan="4"></th>
                <th scope="col" colspan="2" style="text-align:right;">GST</th>
                <td scope="col" class="last-col" style="text-align:right; border-left: 1px solid #777; border-right: 1px solid #777; border-color: #777;"><?php echo wc_price( $order_gst, get_woocommerce_currency_symbol()); ?></td>
            </tr>

            <tr>
                <th scope="col" colspan="4"></th>
                <th scope="col" colspan="2" style="text-align:right;">TOTAL</th>
                <td scope="col" class="last-col" style="text-align:right; border-left: 1px solid #777; border-right: 1px solid #777; border-color: #777;"><?php echo wc_price( $order_total_with_gst, get_woocommerce_currency_symbol()); ?></td>
            </tr>
        <?php endif; ?>


		</tbody>
	</table>
</div>
<?php if ( get_option( 'ywraq_pdf_link' ) === 'yes' ) : ?>
	<div>
		<table class="ywraq-buttons">
			<tr>
				<?php if ( get_option( 'ywraq_show_accept_link' ) !== 'no' ) : ?>
					<td><a href="<?php echo esc_url( ywraq_get_accepted_quote_page( $order ) ); ?>"
							class="pdf-button"><?php ywraq_get_label( 'accept', true ); ?></a></td>
					<?php
				endif;
				echo ( get_option( 'ywraq_show_accept_link' ) !== 'no' && get_option( 'ywraq_show_reject_link' ) !== 'no' ) ? '<td><span style="color: #666666">|</span></td>' : '';
				if ( get_option( 'ywraq_show_reject_link' ) !== 'no' ) :
					?>
					<td><a href="<?php echo esc_url( ywraq_get_rejected_quote_page( $order ) ); ?>"
							class="pdf-button"><?php ywraq_get_label( 'reject', true ); ?></a></td>
				<?php endif ?>
			</tr>
		</table>
	</div>
<?php endif ?>

<?php do_action( 'yith_ywraq_email_after_raq_table', $order ); ?>

<?php $after_list = apply_filters( 'ywraq_quote_after_list', $order->get_meta( '_ywraq_request_response_after' ), $order_id ); ?>

<?php if ( '' !== $after_list ) : ?>
	<div class="after-list">
		<p><?php echo wp_kses_post( nl2br( $after_list ) ); ?></p>
	</div>
<?php endif; ?>
