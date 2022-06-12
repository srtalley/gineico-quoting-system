<?php
/**
 * Request Quote PDF Header
 *
 * @package YITH Woocommerce Request A Quote
 * @since   1.0.0
 * @version 3.0.0
 * @author  YITH
 *
 * @var $order    WC_Order
 * @var $raq_data array
 */

if ( function_exists( 'icl_get_languages' ) ) {
	global $sitepress;
	$lang = $order->get_meta( 'wpml_language' );
	if ( function_exists( 'wc_switch_to_site_locale' ) ) {
		wc_switch_to_site_locale();
	}
	YITH_Request_Quote_Premium()->change_pdf_language( $lang );
}

$order_id = $order->get_id();
$logo_url = get_option( 'ywraq_pdf_logo' );

$logo_attachment_id = apply_filters( 'yith_pdf_logo_id', get_option( 'ywraq_pdf_logo-yith-attachment-id' ) );


if ( ! $logo_attachment_id && $logo_url ) {
	$logo_attachment_id = attachment_url_to_postid( $logo_url );
}

$logo = $logo_attachment_id ? get_attached_file( $logo_attachment_id ) : $logo_url;


$image_type        = wp_check_filetype( $logo );
$mime_type         = array( 'image/jpeg', 'image/png' );
$logo              = apply_filters( 'ywraq_pdf_logo', ( isset( $image_type['type'] ) && in_array( $image_type['type'], $mime_type, true ) ) ? $logo : '' );
$user_name         = $order->get_meta( 'ywraq_customer_name' );
$user_email        = $order->get_meta( 'ywraq_customer_email' ); //phpcs:ignore
$formatted_address = $order->get_formatted_billing_address();

$billing_phone   = $order->get_meta( 'ywraq_billing_phone' );
$billing_name    = $order->get_meta( '_billing_first_name' );
$billing_surname = $order->get_meta( '_billing_last_name' );
$billing_phone   = $order->get_meta( 'ywraq_billing_phone' );
$billing_phone   = empty( $billing_phone ) ? $order->get_meta( '_billing_phone' ) : $billing_phone;
$billing_vat     = $order->get_meta( 'ywraq_billing_vat' );


$exdata = $order->get_meta( '_ywcm_request_expire' );

/* GQS CUSTOM */
$title_order_date   = date_i18n('d/m/Y', strtotime(yit_get_prop($order, 'date_created', true)));
$additional_email_fields = get_post_meta( $order_id, 'ywraq_other_email_fields', true );
$g_ywraq_pdf_revision_number = get_post_meta( $order_id, '_gqs_ywraq_pdf_revision_number', true );
$pdf_revision_name_extension = '';
if(is_array($gqs_ywraq_pdf_revision_number) && isset($gqs_ywraq_pdf_revision_number['html'])) {
	$pdf_revision = $gqs_ywraq_pdf_revision_number['html'];
	if($pdf_revision != 0) {
		$pdf_revision_name_extension = ' - REV ' . $pdf_revision;
	}
} 
/* END GQS CUSTOM */

$expiration_data = '';

if ( function_exists( 'wc_format_datetime' ) ) {
	$order_date = wc_format_datetime( $order->get_date_created() );
	if ( ! empty( $exdata ) ) {
		try {
			$exdata = new WC_DateTime( $exdata );
		} catch ( Exception $e ) {
			$exdata = '';
		}
		$expiration_data = wc_format_datetime( $exdata );
	}
} else {
	$date_format     = isset( $raq_data['lang'] ) ? ywraq_get_date_format( $raq_data['lang'] ) : wc_date_format();
	$order_date      = date_i18n( $date_format, strtotime( $order->get_date_created() ) );
	$expiration_data = empty( $exdata ) ? '' : date_i18n( $date_format, strtotime( $exdata ) );
}

?>
<div class="logo">
	<img src="<?php echo apply_filters( 'ywraq_pdf_log_src', $logo ); //phpcs:ignore ?>" style="max-width: 300px;max-height: 80px;">
</div>
<div class="admin_info right">
	<div class="admin_info_part_left">
		<table style="overflow: hidden;" autosize="1">
			<tr>
				<td valign="top" class="admin-info small-title"><?php echo __( 'From', 'yith-woocommerce-request-a-quote' ) ?></td>
				<td valign="top" class="admin-info small-info">
					<p><?php //echo apply_filters( 'ywraq_pdf_info', nl2br( get_option( 'ywraq_pdf_info' ) ), $order ) ?>
					<strong>Gineico Lighting</strong><br>
					<a class="link-nounderline" href="mailto:showroom@gineico.com" target="_blank" style="text-decoration: none; color: #000;">showroom@gineico.com</a><br>
					<a class="link-nounderline" href="https://www.gineicolighting.com.au" target="_blank" style="text-decoration: none; color: #000;">www.gineicolighting.com.au</a><br>
					<a class="link-nounderline" href="tel:+61-417-950-455" target="_blank" style="text-decoration: none; color: #000; ">+61 417 950 455</a><br>
				</p>
				</td>
			</tr>
		</table>
	</div>
	<div class="admin_info_part_right">
		<table style="overflow: hidden;" autosize="1">
			<tr>
				<td valign="top" class="admin-info small-title"><?php echo __( 'Customer', 'yith-woocommerce-request-a-quote' ) ?></td>
				<td valign="top" class="admin-info small-info">
	                <p>
						<?php if(isset($additional_email_fields['Company Name']) && !empty($additional_email_fields['Company Name'])) {
	                        echo esc_attr($additional_email_fields['Company Name']) . '<br>';
						} ?>
						
						<?php if ( empty( $billing_name ) && empty( $billing_surname ) ): ?>
	                        <strong><?php echo $user_name ?></strong>
	                        <br>
						<?php endif; ?>

						<?php
						echo $formatted_address .'<br>';
						echo $user_email . '<br>';

						if ( $billing_phone != '' ) {
							echo $billing_phone . '<br>';
						}

						if ( $billing_vat != '' ) {
							echo $billing_vat . '<br>';
						} ?>
						
						<?php if(isset($additional_email_fields['Phone Number']) && !empty($additional_email_fields['Phone Number'])) {
	                        echo esc_attr($additional_email_fields['Phone Number']) . '<br>';
						} ?>
	                </p>
				</td>
			</tr>
			<?php if ( $expiration_data != '' ): ?>
				<tr>
					<td valign="top" class="small-title"><?php echo __( 'Expiration date', 'yith-woocommerce-request-a-quote' ) ?></td>
					<td valign="top" class="small-info">
						<p><strong><?php echo $expiration_data ?></strong></p>
					</td>
				</tr>
			<?php endif ?>
		</table>
	</div>
</div>
<div class="clear"></div>
<div class="quote-title" style="margin: 30px 0 0;">
	<h4 style="margin: 0 0 5px 0;"><?php printf( __( 'Quote #%s', 'yith-woocommerce-request-a-quote' ), apply_filters( 'ywraq_quote_number', $order_id ) ) ?> - <?php echo $title_order_date . $pdf_revision_name_extension; ?></h4>
	<?php
	if((isset($additional_email_fields['Project Name']) && !empty($additional_email_fields['Project Name'])) or (isset($additional_email_fields['Project Address']) && !empty($additional_email_fields['Project Address']))) {
        echo '<h5 style="margin: 0;">';
        if(!empty($additional_email_fields['Project Name'])) {
	        echo 'Project Name: <span style="font-weight: normal">' . esc_attr($additional_email_fields['Project Name']) . '</span>&nbsp;&nbsp;&nbsp;&nbsp;';
        }
        if(!empty($additional_email_fields['Project Address'])) {
	        echo 'Project Address: <span style="font-weight: normal">' . esc_attr($additional_email_fields['Project Address']) . '</span>';
        }
        echo '</h5>';
	} ?>
	<?php if(isset($additional_email_fields['Required Delivery Date']) && !empty($additional_email_fields['Required Delivery Date'])) {
        echo '<h5 style="margin: 0;">Required Delivery Date: ';
        echo '<span style="font-weight: normal">' . esc_attr($additional_email_fields['Required Delivery Date']) . '</span> ';
        echo '</h5>';
	} ?>
</div>