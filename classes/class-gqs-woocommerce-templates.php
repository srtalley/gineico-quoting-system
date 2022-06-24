<?php 

namespace Gineicio\QuotingSystem;

class GQS_WooCommerce_Templates {

  
    public function __construct() {
        add_filter( 'woocommerce_locate_template', array( $this, 'woocommerce_locate_template' ), 10, 3 );
    }
    /**
     * Add WooCommerce template location
     */
    public function woocommerce_locate_template( $template, $template_name, $template_path ) {

        global $woocommerce;

        $_template = $template;

        if ( !$template_path ) {
            $template_path = $woocommerce->template_url;
        }
        // Template Path
        $plugin_path = untrailingslashit( plugin_dir_path( GINEICO_QUOTING_SYSTEM__FILE__ ) ) . '/templates/woocommerce/';

        // Look within passed path within the theme - this is priority
        $template = locate_template(
            array(
                $template_path . $template_name,
                $template_name
            )
        );

        // Modification: Get the template from this plugin, if it exists
        if ( !$template && file_exists( $plugin_path . $template_name ) ) {
            $template = $plugin_path . $template_name;
        }

        // Use default template
        if ( !$template ) {
            $template = $_template;
        }

        // Return what we found
        return $template;
    }
    /**
     * Print the info for the PDF header block depending on the site
     */
    public static function get_pdf_header_info() {

        $html = '';

        $current_site = GQS_Site_Utils::get_gineico_site_abbreviation();

        if($current_site == 'GL') {
            $html = '<p>
                        <strong>Gineico Lighting</strong><br>
                        <a class="link-nounderline" href="mailto:showroom@gineico.com" target="_blank" style="text-decoration: none; color: #000;">showroom@gineico.com</a><br>
                        <a class="link-nounderline" href="https://www.gineicolighting.com.au" target="_blank" style="text-decoration: none; color: #000;">www.gineicolighting.com.au</a><br>
                        <a class="link-nounderline" href="tel:+61-417-950-455" target="_blank" style="text-decoration: none; color: #000; ">+61 417 950 455</a><br>
                    </p>';

        } else if($current_site == 'GM') {
                    $html = '<p>
                        <strong>Gineico Marine</strong><br>
                        <a class="link-nounderline" href="mailto:sales@gineico.com" target="_blank" style="text-decoration: none; color: #000;">sales@gineico.com</a><br>
                        <a class="link-nounderline" href="https://www.gineicomarine.com.au" target="_blank" style="text-decoration: none; color: #000;">www.gineicomarine.com.au</a><br>
                        <a class="link-nounderline" href="tel:+61-7-5556-0244" target="_blank" style="text-decoration: none; color: #000; ">+61 7 5556 0244</a><br>
                    </p>';
        }

        return $html;
    }
    /**
     * Print the info for the PDF footer depending on the site
     */
    public static function get_pdf_footer() {

        $html = '';

        $current_site = GQS_Site_Utils::get_gineico_site_abbreviation();

        $primary_link_color = GQS_Site_Utils::get_gineico_primary_link_color();

        if($current_site == 'GL') {
            $html = '<p>Thank you for the opportunity to quote on your lighting selections. Contact Us <a href="tel:+61-417-950-455" style="text-decoration: none; color: ' . $primary_link_color . '; font-weight: bold;">+61 417 950 455</a> © Gineico Lighting | <a href="https://www.gineicolighting.com.au" target="_blank" style="text-decoration: none; color: ' . $primary_link_color . '; font-weight: bold;">www.gineicolighting.com.au</a></p>';

        } else if($current_site == 'GM') {
            $html = '<p>Thank you for the opportunity to quote on your marine selections. Contact Us <a href="tel:+61-7-5556-0244" style="text-decoration: none; color: ' . $primary_link_color . '; font-weight: bold;">+61 7 5556 0244</a> © Gineico Marine | <a href="https://www.gineicomarine.com.au" target="_blank" style="text-decoration: none; color: ' . $primary_link_color . '; font-weight: bold;">www.gineicomarine.com.au</a></p>';

        }

        return $html;
    }
   
} // end class

$gqs_woocommerce_templates = new GQS_WooCommerce_Templates();