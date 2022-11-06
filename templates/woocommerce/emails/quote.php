<?php
/**
 * HTML Template Email Send Quote
 *
 * @since   1.0.0
 * @author  YITH
 * @version 4.0.0
 * @package YITH Woocommerce Request A Quote
 *
 * @var $order WC_Order
 * @var $raq_data array
 * @var $email_heading array
 * @var $email string
 * @var $email_description string
 * @var $email_title string
 */

do_action( 'woocommerce_email_header', $email_heading, $email );


$order_id = $order->get_id();

$order_date = wc_format_datetime( $order->get_date_created() );
$exdata     = $order->get_meta( '_ywcm_request_expire' );
$exdata     = new WC_DateTime( $exdata, new DateTimeZone( 'UTC' ) );
$exdata     = wc_format_datetime( $exdata );
$after_list = $order->get_meta( '_ywraq_request_response_after' );

$show_accept_link = get_option( 'ywraq_show_accept_link' ) !== 'no';
$show_reject_link = get_option( 'ywraq_show_reject_link' ) !== 'no';
?>
<style>
	.customer-info div{
		line-height: 2em;
	}
	h2{
		margin-bottom: 10px;
	}
	h2.quote-title{
		margin-bottom: 25px;
	}
	.thumb-wrapper{
		display: table-cell;
		margin-right:15px;
		padding-right:15px;
	}
	.product-name-wrapper{
		display: table-cell;
		vertical-align: middle;
	}
	.product-name-wrapper small {
		font-size: 13px !important;
		line-height: 20px !important;
	}
	.product-name-wrapper small p {
		font-size: 13 !important;
		margin: 0;
	}
	.product-name-wrapper .wc-item-meta {
		margin-top: 10px !important;
		display: block;
	}
	.product-name-wrapper .wc-item-meta br:first-child {
		display: none;
	}
	.product-name-wrapper small li {
		margin: 0 !important;
	}
	.product-name-wrapper small strong {
		text-transform: uppercase;
	}
	.product-name-wrapper small li:last-child {
		margin: 0 !important;
	}
	.wc-item-meta p {
		display: inline-block;
	}
	.tax_label {
		display:block;
	}
	.table-wrapper {
		margin: 30px 0;
	}
	.date-request {
		float: left;
		width: 50%;
	}

	.date-expiration {
		float: right;
		text-align: right;
		width: 50%;
	}

	.date-wrapper {
		padding: 20px 0;
		border-top: 1px solid #eee;
		margin-top: 20px;
	}
</style>

<!-- BEGIN GQS CUSTOM -->
<!-- <h2 class="quote-title"><?php //printf( ( '%1$s n. %2$s' ), apply_filters( 'wpml_translate_single_string', esc_html( $email_title ), 'admin_texts_woocommerce_ywraq_send_quote_settings', '[woocommerce_ywraq_send_quote_settings]email-title', $raq_data['lang'] ),  esc_html( $raq_data['order-number'] ) ); // phpcs:ignore ?></h2> -->
<!-- END GQS CUSTOM -->
<p><?php echo apply_filters( 'wpml_translate_single_string', wp_kses_post( nl2br( $email_description ) ), 'admin_texts_woocommerce_ywraq_send_quote_settings', '[woocommerce_ywraq_send_quote_settings]email-description', $raq_data['lang'] ); // phpcs:ignore ?></p>

<!-- BEGIN GQS CUSTOM -->
<p><strong><?php esc_html_e( 'Request date', 'yith-woocommerce-request-a-quote' ); ?></strong>: <?php echo esc_html( $order_date ); ?></p>
<!-- END GQS CUSTOM -->

<?php if ( get_option( 'ywraq_hide_table_is_pdf_attachment' ) === 'no' || get_option( 'ywraq_hide_table_is_pdf_attachment', '' ) === '' ) : ?>

	<?php if ( ! empty( $raq_data['admin_message'] ) ) : ?>
		<p><?php echo wp_kses_post( $raq_data['admin_message'] ); ?></p>
	<?php endif ?>

<div class="table-wrapper">
	<?php
	wc_get_template(
		'emails/quote-table.php',
		array(
			'order' => $order,
		),
		'',
		YITH_YWRAQ_TEMPLATE_PATH . '/'
	);
	?>
</div>


<?php endif ?>

<p>
	<?php if ( $show_accept_link ) : ?>
		<a href="<?php echo esc_url( ywraq_get_accepted_quote_page( $order ) ); ?>"><?php esc_html( ywraq_get_label( 'accept', true ) ); ?></a>
		<?php
	endif;
	echo ( $show_accept_link && $show_reject_link ) ? ' | ' : '';
	if ( $show_reject_link ) :
		?>
		<a href="<?php echo esc_url( ywraq_get_rejected_quote_page( $order ) ); ?>"><?php esc_html( ywraq_get_label( 'reject', true ) ); ?></a>
	<?php endif; ?>
</p>

<?php if ( ! empty( $after_list ) ) : ?>
	<p><?php echo wp_kses_post( apply_filters( 'ywraq_quote_after_list', nl2br( $after_list ), $order_id ) ); ?></p>
<?php endif; ?>

<?php
wc_get_template(
	'emails/customer-details.php',
	array( 'order' => $order ),
	'',
	YITH_YWRAQ_TEMPLATE_PATH . '/'
);
?>

<!-- BEGIN GQS CUSTOM -->
<!-- <div class="date-wrapper">
	<div class="date-request">
		<strong><?php //esc_html_e( 'Request date', 'yith-woocommerce-request-a-quote' ); ?></strong>: <?php //echo esc_html( $order_date ); ?>
	</div>-->

	<?php if ( ! empty( $exdata ) ) : ?>
		<!-- <div class="date-expiration">
			<strong><?php //esc_html_e( 'Expiration date', 'yith-woocommerce-request-a-quote' ); ?></strong>: <?php echo esc_html( $exdata ); ?>
		</div>-->
	<?php endif ?>
<!-- </div> -->
<!-- END GQS CUSTOM -->

<?php do_action( 'woocommerce_email_footer', $email ); ?>
