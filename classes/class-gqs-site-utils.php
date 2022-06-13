<?php 

namespace Gineicio\QuotingSystem;

class GQS_Site_Utils {

  
    // public function __construct() {


    // }
    /**
     * Return the abbreviation for this site
     */
    public static function get_gineico_site_abbreviation() {
      if(site_url() == "https://www.gineicolighting.com.au" || site_url() == "https://gineicolighting.client.dustysun.com") {
        return 'GL';
      }  if(site_url() == "https://www.gineicomarine.com.au" || site_url() == "https://gineicomarine.dev.dustysun.com") {
        return 'GM';
      } else {
        return false;
      }
    }
    /**
     * Return the name for this site
     */
    public static function get_gineico_site_name() {
      $current_site = self::get_gineico_site_abbreviation();
      if($current_site == 'GL') {
        return 'Gineico Lighting';
      } else if($current_site == 'GM') {
          return 'Gineico Marine';
      } else {
        return false;
      }
    }
    /**
     * Return the primary link color for this site
     */
    public static function get_gineico_primary_link_color() {
      $current_site = self::get_gineico_site_abbreviation();
      if($current_site == 'GL') {
        return '#e2ae68';
      } else if($current_site == 'GM') {
          return '#81d8d0';
      } else {
        return '#000';
      }
    }


    
   
} // end class

$gqs_site_utils = new GQS_Site_Utils();