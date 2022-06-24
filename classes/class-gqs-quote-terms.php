<?php 

namespace Gineicio\QuotingSystem;

class GQS_Quote_Terms{

  
    public function __construct() {

        // Add the terms at the bottom of the PDF quote
        add_action( 'yith_ywraq_quote_template_after_content', array($this, 'get_site_quote_terms'));
        // Check for YITH emails and add the terms to the bottom
        add_action ( 'woocommerce_email_footer', array($this, 'woocommerce_email_footer_with_terms'), 10, 1);
        // Set an auto generated quote back to the "New" status
        // add_action( 'ywraq_after_create_order', array( $this, 'gm_reset_auto_generated_quote_order_status' ), 20, 2 );
    }

    /** 
     * Set the quotes back to new status after auto generating email
     */
    // public function gm_reset_auto_generated_quote_order_status( $raq, $order ) {
    //     if ( current_action() === 'ywraq_after_create_order' ) {
    //         $order = wc_get_order( $raq );
    //         if(!empty($order) && $order != null) {
    //             $order->update_status( 'ywraq-new' );

    //         }
    //     }
    // }
    public function get_site_quote_terms() {
        $current_site = GQS_Site_Utils::get_gineico_site_abbreviation();

        if($current_site == 'GL') {
            echo $this->gl_quote_terms(true);
        } else if($current_site == 'GM') {
            return $this->gm_quote_terms(true);
        } else {
            return false;
        }
    }
    /**
     * Check for YITH Request a Quote emails and call the function to add the 
     * terms for the footer
     */
    public function woocommerce_email_footer_with_terms($email) {
        switch ($email->id) {
            case 'ywraq_email':
            case 'ywraq_email_customer':
            case 'wraq_quote_status':
            case 'ywraq_send_quote':
            case 'ywraq_send_email_request_quote_customer':
                $this->get_site_quote_terms();
                
            break;
        }
    } // end gm_woocommerce_email_footer

    /**
     * Quote terms for GL
     */
    public function gl_quote_terms($is_pdf = false) {
        if($is_pdf) {
            $additional_styles = 'margin-top: 40px;';
        } else {
            $additional_styles = '';
        }
        ?>
		<div class="gineico-pdf-footer" style="<?php echo $additional_styles; ?> font-style: italic; font-size: 12px;">
		<p style="font-style: italic; font-size: 12px;">
			PLEASE TAKE NOTE OF ALL THE CONDITIONS OF THIS QUOTE AS STATED BELOW, BEFORE PLACING AN ORDER</p>
			<ol>
				<li style="font-style: italic; margin-bottom: 5px; font-size: 12px;">Until fully Paid, goods remain the sole property of Gineico Queensland Pty Ltd.</li>
				<li style="font-style: italic; margin-bottom: 5px; font-size: 12px;">Unless otherwise specified, indicated costs are unit costs.</li>
				<li style="font-style: italic; margin-bottom: 5px; font-size: 12px;">Prices are quoted not including G.S.T</li>
				<li style="font-style: italic; margin-bottom: 5px; font-size: 12px;">Prices are quoted for goods ex our store. Delivery charges will apply if goods are required to be on-forwarded.</li>
				<li style="font-style: italic; margin-bottom: 5px; font-size: 12px;">Unless otherwise stated, standard manufacturing lead time is aproximately 4-5 weeks from confirmation of order (not including holiday closures). The goods are then ready for collection from the manufacturer in Italy.</li>
				<li style="font-style: italic; margin-bottom: 5px; font-size: 12px;">Standard prices are based on sea freight from Italy to Australia. Transit time for sea freight is aproximately 6-7 weeks from date goods are ready / collected from the manufacturers warehouse in Italy (see above).</li>
				<li style="font-style: italic; margin-bottom: 5px; font-size: 12px;">Express air freight option is available at additional cost. This reduces transit time to aproximately 7 working days from date goods are ready / collected from the manufacturers warehouse in Italy (see above).</li>
				<li style="font-style: italic; margin-bottom: 5px; font-size: 12px;">Terms of sale: 50% deposit with written order. Balance in full prior to consignment.</li>
				<li style="font-style: italic; margin-bottom: 5px; font-size: 12px;">Balance of payment and collection of goods, to take place within 7 calendar days from date when goods become available from our warehouse.</li>
				<li style="font-style: italic; margin-bottom: 5px; font-size: 12px;">Failure to pay and collect goods by the stated time may incur storage costs or the forfeit of the deposit and goods.</li>
				<li style="font-style: italic; margin-bottom: 5px; font-size: 12px;">Payments made by cheque, credit card or telegraphic transfer will be subject to clearance of funds in our account, prior to goods being released.</li>
				<li style="font-style: italic; margin-bottom: 5px; font-size: 12px;">This offer is valid for 30 calendar days from date of issue.</li>
				<li style="font-style: italic; margin-bottom: 5px; font-size: 12px;">Quantities indicated above are to be checked by purchaser prior to ordering. Reduction of the indicated quantities will be cause for revision of quoted prices.</li>
				<li style="font-style: italic; margin-bottom: 5px; font-size: 12px;">Restocking fee of 50% applies to all items returned. Items can only be returned with prior written consent by gineico QLD Pty Ltd. Goods to be returned in "as new condition" at client's expense.</li>
				<li style="font-style: italic; margin-bottom: 5px; font-size: 12px;">Custom or non standard / stock hardware cannot be returned.</li>
				<li style="font-style: italic; margin-bottom: 5px; font-size: 12px;">Clients should take care to download the product specific data sheets, or to request technical information in writing, to ensure all items ordered are in every way compatible for each specific intended application.</li>
				<li style="font-style: italic; margin-bottom: 5px; font-size: 12px;">Clients should take care to check all goods when they are delivered. Any claims for damaged goods or missing items must be lodged in writing within 7 days of arrival on site.</li>
			</ol>
		</div>
		<?php

    }

    /**
     * Quote terms for GM
     */
    public function gm_quote_terms($is_pdf = false) {
        if($is_pdf) {
            $additional_styles = 'margin-top: 40px;';
        } else {
            $additional_styles = '';
        }
        ?>
		<div class="gineico-pdf-footer" style="<?php echo $additional_styles; ?> font-style: italic; font-size: 12px;">
            <p style="margin-top: 0; font-style: italic; font-size: 12px;">
                PLEASE TAKE NOTE OF ALL THE CONDITIONS OF THIS QUOTE AS STATED BELOW, BEFORE PLACING AN ORDER
                <ol>
                    <li style="font-style: italic; margin-bottom: 5px; font-size: 12px; color: #232323 !important">Until fully Paid, goods remain the sole property of Gineico Queensland Pty Ltd.</li>

                    <li style="font-style: italic; margin-bottom: 5px; font-size: 12px; color: #232323 !important">Unless otherwise specified, indicated costs are unit costs.</li>

                    <li style="font-style: italic; margin-bottom: 5px; font-size: 12px; color: #232323 !important">Prices are quoted in Australian Dollars not including G.S.T</li>

                    <li style="font-style: italic; margin-bottom: 5px; font-size: 12px; color: #232323 !important">Prices are quoted for goods ex our store. Delivery charges will apply if goods are required to be on-forwarded.</li>

                    <li style="font-style: italic; margin-bottom: 5px; font-size: 12px; color: #232323 !important">Unless otherwise stated, lead time is approximately 8-10 weeks from confirmation of order (not including holiday closures). Faster air freight delivery can be requested.</li>

                    <li style="font-style: italic; margin-bottom: 5px; font-size: 12px; color: #232323 !important">Terms of sale: 50% deposit with written order. Balance in full prior to consignment.</li>

                    <li style="font-style: italic; margin-bottom: 5px; font-size: 12px; color: #232323 !important">Balance of payment and collection of goods, to take place within 7 calendar days from date when goods become available. </li>

                    <li style="font-style: italic; margin-bottom: 5px; font-size: 12px; color: #232323 !important">Failure to pay and collect goods by the stated time may incur storage costs or the forfeit of the deposit and goods.</li>

                    <li style="font-style: italic; margin-bottom: 5px; font-size: 12px; color: #232323 !important">Payments made by cheque, credit card or telegraphic transfer, will be subject to clearance of funds (in our account), prior to goods being released. </li>

                    <li style="font-style: italic; margin-bottom: 5px; font-size: 12px; color: #232323 !important">This offer is valid for 30 calendar days from this date.</li>

                    <li style="font-style: italic; margin-bottom: 5px; font-size: 12px; color: #232323 !important">Reduction of the indicated quantity will be cause for revision of quoted prices.</li>

                    <li style="font-style: italic; margin-bottom: 5px; font-size: 12px; color: #232323 !important">Restocking fee of 50% applies to all items returned and can only be accepted with a prior written consent by gineico QLD p/l. Goods returned at client's expense.</li>

                    <li style="font-style: italic; margin-bottom: 5px; font-size: 12px; color: #232323 !important">Custom or non standard stock hardware cannot be returned.</li>

                </ol>
            </p>
        </div>
		<?php
	}
} // end class

$gqs_quote_terms = new GQS_Quote_Terms();