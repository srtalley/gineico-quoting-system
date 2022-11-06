jQuery(function($) {

    $('document').ready(function() {
        // setupAddParentProduct();
        removeDisabledYwraqButton();
    });


    /**
     * Observe and remove the disabled class from the YWRAQ button
     */
    function removeDisabledYwraqButton() {

        $('.add-request-quote-button.button').removeClass('disabled');
            // Create an observer instance
            var observer = new MutationObserver(function( mutations ) {
                mutations.forEach(function( mutation ) {		
                    if(mutation.attributeName == "class"){
                        if($(mutation.target).hasClass('disabled')) {
                            $(mutation.target).removeClass('disabled')
                        } 

                    }
                });    
                
            });
            // Configuration of the observer:
            var config = { 
                childList: true,
                attributes: true,
                subtree: true,
                characterData: true
            }; 
            var targetNode = $('.add-request-quote-button.button')[0];
            observer.observe(targetNode, config);  

    }

    /**
     * Set up the overlay for the request quote button
     * Disabled 10/13/2022
     */
    // function setupAddParentProduct() {
    //     $( document ).on('click', '.woocommerce-variation-add-to-cart-disabled + .yith-ywraq-add-to-quote .yith-ywraq-add-button', function (e) {
    //         // e.stopPropagation();
    //         e.preventDefault();
    //         var variations_form = $(e.target).parentsUntil('.variations_form').parent();
    //         var product_id = $(variations_form).data('product_id');
    //         var product_options = $(variations_form).serialize();
    //         gqs_add_parent_product_to_quote(product_id, product_options);

    //     });
    // }

    /**
     * Function to get a product price via Ajax
     * Disabled 10/13/2022
     */
    // function gqs_add_parent_product_to_quote(prod_id, product_options) {

    //     $t = $('.add-request-quote-button');

    //     return $.ajax({
    //         type: 'POST',
    //         dataType: 'json',
    //         url: gineico_gqs_frontend.ajaxurl,
    //         data: {
    //             'action': 'gqs_add_parent_product_to_quote',
    //             'nonce': gineico_gqs_frontend.ajaxnonce,
    //             'product_options': product_options
    //         },
    //         success: function(response) {
    //             console.log(response);

    //             // mirrors some code in YITH assets/js/frontend.js
    //             if (response.result == 'true' || response.result == 'exists') {
    //                 $('.yith_ywraq_add_item_response-' + prod_id).hide().addClass('hide').html('');
    //                 $('.yith_ywraq_add_item_product-response-' + prod_id).show().removeClass('hide').html(response.message);
    //                 $('.yith_ywraq_add_item_browse-list-' + prod_id).show().removeClass('hide');
    //                 $t.parent().hide().removeClass('show').addClass('addedd');
    //                 // $('.add-to-quote-' + prod_id).attr('data-variation', response.variations);
    //                 $(document).trigger('yith_wwraq_added_successfully', [response, product_options]);

    //             } else if (response.result == 'false') {
    //                 $('.yith_ywraq_add_item_response-' + prod_id).show().removeClass('hide').html(response.message);
          
    //                 $(document).trigger('yith_wwraq_error_while_adding');
    //             }
    //         },
    //         error: function(jqXHR, textStatus, errorThrown) {
    //             console.log(jqXHR + ' :: ' + textStatus + ' :: ' + errorThrown);
    //         }
    //     });
    // }
});