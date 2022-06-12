<?php
/**
 * Request Quote PDF Wrapper fils
 *
 * @package YITH Woocommerce Request A Quote
 * @since   1.0.0
 * @version 3.0.0
 * @author  YITH
 *
 * @var int $order_id Order id.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

// NOTE: Block level tags are ignored inside tables and CSS does not apply:

// "Block-level tags (DIV, P etc) are ignored inside tables, including any CSS styles - inline CSS or stylesheet classes, id etc. To set text characteristics within a table/cell, either define the CSS for the table/cell, or use in-line tags e.g. <span style="...">"

// https://mpdf.github.io/tables/tables.html

?>
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
    <style type="text/css">

        @page { margin: 0.45in 0.45in 0.35in 0.35in; }

        body {
            color: #000;
            margin: 0;
            padding: 0;
        }

        .logo{
            width: 40%;
            float: left;
            max-width: 300px;
        }
        .right{
            float: right;
            width: 60%;
            text-align: right;
        }
        .clear{
            clear: both;
        }
        .admin_info{
            font-size: 12px;
        }
        .admin_info_part_left {
            width: 50%;
            float: left;
        }
        .admin_info_part_right {
            width: 50%;
            float: right;
        }
        table{
            border: 0;
        }
        table.quote-table{
            border: 0;
            font-size: 14px;
        }

        .small-title,
        .admin-info.small-title {
            text-align: right;
            font-weight: 600;
            color: #4e4e4e;
            padding-top: 5px;
            padding-right: 5px;
            font-size: 12px;
        }
        
        .small-info{
			background: #a8c6e4;
			background: linear-gradient(360deg, #a8c6e4 0%, #ffffff 1%, #ffffff 100%);
			padding-left:10px;
			margin-bottom: 20px;
		}
        .small-info p{
            padding: 0 0 5px 5px;
            margin: 0; 
            font-size: 12px;
        }

        .admin-info.small-info {
            font-size: 12px;
            text-decoration: none !important;
            color: #000 !important;
            line-height: 18px !important;
        }
        .quote-table td{
            border: 0;
            border-bottom: 1px solid #eee;
        }
        .quote-table .with-border td{
            border-bottom: 2px solid #eee;
        }
        .quote-table .with-border td{
            border-top: 2px solid #eee;
        }
        .quote-table .quote-total td{
            height: 100px;
            vertical-align: middle;
            font-size: 18px;
            border-bottom: 0;
        }
        .quote-table .type-col {
            width: 1in;
        }
        .quote-table .qty-col {
            width: 0.3in;
        }
        .quote-table .part-num-col {
            width: 1in;
        }
        .quote-table .image-col {
            width: 0.7in;
        }
        .quote-table .unit-col {
            width: 1in;
        }
        .quote-table .subtotal-col {
            width: 1.1in;
        }
        .quote-table small{
            font-size: 13px;
        }
        .quote-table .last-col{
            padding-right: .1in;
        }
        .quote-table .last-col.tot{
            font-weight: 600;
        }
        .quote-table .tr-wb{
            border-left: 1px solid #ccc ;
            border-right: 1px solid #ccc ;
        }
        .product-desc {
            line-height: 1.2em;
        }
        .pdf-button{
            color: #a8c6e4;
            text-decoration: none;
        }
        div.content{ padding-bottom: 0px; border-bottom: 1px; }
		.wc-item-meta {
			margin: 5px 0;
		}
        .shipping-col .shipped_via {
            display: none;
        }
        .footer {
			width: 100%;
			text-align: center;
			position: fixed;
			bottom: 0;
        }
		.gqs-last-page-footer {
			font-size: 13px;
            width: 100%;
            text-align: center;
			position: absolute;
			bottom: 0.25in;
            text-decoration: none !important;
            color: #000 !important;
		}
        .pagenum:before {
            content: counter(page);
        }
        .part-no-col {
            font-size: 12px;
        }
        .gineico-pdf-footer {
            margin-top: 25px !important;
            font-size: 13px !important;
        }
    </style>
	<?php

	do_action( 'yith_ywraq_quote_template_head' );
	?>
</head>

<body>
<?php
do_action( 'yith_ywraq_quote_template_footer', $order_id );
?>

<?php
do_action( 'yith_ywraq_quote_template_header', $order_id );
?>
<div class="content">
	<?php
	do_action( 'yith_ywraq_quote_template_content', $order_id );
    do_action( 'yith_ywraq_quote_template_after_content', $order_id );
	?>
</div>
<div class="gqs-last-page-footer">
<?php echo Gineicio\QuotingSystem\GQS_WooCommerce_Templates::get_pdf_footer(); ?>
</div>
</body>
</html>
