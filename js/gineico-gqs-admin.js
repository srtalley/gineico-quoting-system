jQuery(function($) {

    $('document').ready(function() {

        // setupWCManualProductsUpdateFired();
        setupEditProducts();
        setupCustomMeta();

        // handle an update where the items are reloaded
        // via ajax and run it a few times in case there's 
        // a delay in them being added
        $(document).on('items_saved', function() {
            setupCustomMetaSupport();
        });
        
        $( '#woocommerce-order-items' ).on( 'click', '.cancel-action', function() {
            setupCustomMetaSupport();
        } );
        
        $( 'body' ).on( 'order-totals-recalculate-complete', function() {
            setupCustomMetaSupport();
        } );
        setupCustomShipping();
        addCustomVersionStringToPDFurl();

    });

    /**
     * Listen for the Add in the products add box being selected, and set 
     * up any custom JS again. These events are in 
     * woocommerce/assets/js/admin/backbone-modal.js
     */
    // function setupWCManualProductsUpdateFired() {
    //     $( document.body ).on( 'wc_backbone_modal_removed', function(e) {
    //         setupCustomMetaSupport();
    //     } );
    //     $( document.body ).on( 'wc_backbone_modal_response', function(e) {
    //         setupCustomMetaSupport();
    //     } );
    // }
    /**
     * Check if the block overlay is showing on the order area and then 
     * set up our custom fields again
     */
    function setupEditProducts() {

        // Create an observer instance to check the line items
        // for the block overlay
        var observer = new MutationObserver(function( mutations ) {
            mutations.forEach(function( mutation ) {	
                mutation.removedNodes.forEach(
                    function(node) {
                        if( $(node).hasClass("blockOverlay")){	
                            setupCustomMeta();
                        }
                    }
                );	
            });    
            
        });
        // Configuration of the observer:
        var config = { 
            childList: true,
            attributes: true,
            subtree: true,
            characterData: true
        }; 
        var targetNode = $('#woocommerce-order-items')[0];
        observer.observe(targetNode, config);  
    }
    /**
     * Timeouts to make sure that the custom meta is added
     */
    function setupCustomMetaSupport() {
        setTimeout(() => {
            setupCustomMeta();
        }, 500);
        setTimeout(() => {
            setupCustomMeta();
        }, 2000);
        setTimeout(() => {
            setupCustomMeta();
        }, 5000);
        setTimeout(() => {
            setupCustomMeta();
        }, 20000);
    }

    /**
     * Set up the custom Quote Type and Part Number fields.
     * Adds a unit price field for items.
     */
    function setupCustomMeta() {
        $('input[value="_gqs_quote_type"]').addClass('gqs-hide-label');
        $('input[value="_gqs_quote_type"]').parent().parent().addClass('gqs-quote-type');

        $('input[value="_gqs_quote_part_number"]').addClass('gqs-hide-label');
        $('input[value="_gqs_quote_part_number"]').parent().parent().addClass('gqs-quote-part-number');

        // add the quote desc edit
        var quote_desc_fields = $('.woocommerce_order_items_wrapper td.name .gqs-quote-description');

        $(quote_desc_fields).each(function(){
            if(!$(this).hasClass('added_js')) {
                $(this).addClass('added_js');
                var quote_desc_div = this;
                var quote_desc_edit_link = $(this).find('.gqs-quote-description-edit-link');
                var quote_desc_cancel_edit_link = $(this).find('.gqs-quote-description-cancel-edit-link');
                var quote_desc_is_custom = $(this).find('.gqs-quote-description-is-custom');
                $(quote_desc_edit_link).on('click', function(e){
                    e.preventDefault();
                    $(quote_desc_div).addClass('edit-description');
                    $(quote_desc_div).find('.gqs-quote-description-edit').show();
                    $(quote_desc_edit_link).addClass('hide-link');
                    $(quote_desc_cancel_edit_link).removeClass('hide-link');
                    $(quote_desc_is_custom).val('yes');
                });
                $(quote_desc_cancel_edit_link).on('click', function(e){
                    e.preventDefault();
                    $(quote_desc_div).removeClass('edit-description');
                    $(quote_desc_div).find('.gqs-quote-description-edit').hide();
                    $(quote_desc_edit_link).removeClass('hide-link');
                    $(quote_desc_cancel_edit_link).addClass('hide-link');
                    $(quote_desc_is_custom).val('no');

                    // replace the original description
                    var quote_description_text = $(quote_desc_div).find('.gqs-quote-description-text');
                    var quote_description_textarea = $(quote_desc_div).find('.gqs-quote-description-textarea');
                    $(quote_description_textarea).val($(quote_description_text).text());

                });
            }
        });
      
        // add the original price field column to the head
        // var item_header = $('.woocommerce_order_items_wrapper th.item_cost');
        // if(!$(item_header).parent().find('.gqs-original-price').length) {
            // $('<th class="gqs-original-price" style="text-align: right;">Original Cost</th>').insertBefore($(item_header));
        // }

        // add the unit price fields and original price fields
        var item_cost_fields = $('.woocommerce_order_items_wrapper td.item_cost');
        if(item_cost_fields.length) {
            $(item_cost_fields).each(function(index) {
                if(!$(this).find('div.edit').length) {
                    var item_cost_field = this;
                    // get the item ID
                    var order_item_id = $(this).parent().data('order_item_id');
                    // var line_total_value = $(item_cost_field).parent().find('input.line_total').val();
                    // var quantity = $(item_cost_field).parent().find('input.quantity').val(); 
                    var value = $(item_cost_field).parent().find('input[name="gqs_current_item_cost"]').val(); //$(this).data('sort-value');

                    // update the view price
                    $(item_cost_field).find('.woocommerce-Price-amount').html('<span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">$</span>' + value + '</bdi></span>');
                    // CODE TO SHOW PRICE
                    // $('<td class="gqs-original-price" style="text-align: right;"></td>').insertBefore(item_cost_field);

                    // gqs_get_product_price_from_order_item_id(order_item_id).done(function(data) {
                    //     $(item_cost_field).parent().find('.gqs-original-price').html(data.price);
                    // });

                    $(this).append('<div class="edit" style="display: none;"><input type="number" autocomplete="off" step=".01" name="gc_unit_price[' + order_item_id + ']" placeholder="0" value="' + value + '" data-qty="1" class="gc-unit-price"></div>');
                    var added_edit_field = $(this).find('.edit .gc-unit-price');

                    $(added_edit_field).on('change', function() {
                        // get the qty
                        var quantity = $(item_cost_field).parent().find('input.quantity').val(); 
                        
                        var new_price = $(this).val() * quantity;
                        new_price = new_price.toFixed(2);

                        $(item_cost_field).parent().find('input.line_total').attr('data-total', new_price).val(new_price);
                    });
                    
                    // update the line total and GST totla to a number field
                    $(item_cost_field).parent().find('input.line_total,input.line_tax').attr('type', 'number').attr('step', '.01');
                    $(item_cost_field).parent().find('input.line_total').on('change', function() {
                        
                        var quantity = $(item_cost_field).parent().find('input.quantity').val(); 
                        
                        var new_unit_price = $(this).val() / quantity;
                        new_unit_price = new_unit_price.toFixed(2);
                        $(item_cost_field).parent().find('input.gc-unit-price').val(new_unit_price);

                        $(this).attr('data-total', new_unit_price);

                    });
                }
                
            });
        }

        // fix the vouchers column
        var order_status = $('#order_status');
        if(order_status.length) {
            if(order_status.val() == 'wc-ywraq-new' || order_status.val() == 'wc-ywraq-pending' || order_status.val() == 'wc-ywraq-expired' || order_status.val() == 'wc-ywraq-accepted' || order_status.val() == 'wc-ywraq-rejected') {
                var wc_order_totals = $('.wc-order-totals');
                //get the site
                var gineico_site = $('input[name="gineico_site"]').val();
                if(wc_order_totals.length) {
                    // see if the vouchers column is filled
                    var wc_order_totals_lines = $(wc_order_totals).find('.label');
                    $(wc_order_totals_lines).each(function() {
                        if($(this).text() == 'Voucher(s):') {
                            // hide the vouchers line and fix the item subtotal
                            $(this).parent().hide();
                            var subtotal = $(this).parentsUntil('.wc-order-totals').parent().find('input[name="gqs_order_subtotal"]').val();
                            $(this).parentsUntil('.wc-order-totals').parent().find('tbody tr:first-child .total').html('<span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">$</span>' + subtotal + '</bdi></span>');
                        } else if($(this).text() == 'Order Total:' && gineico_site == 'GL') {
                            if($('input[name="gqs_order_true_total"]').length) {

                                gqs_order_true_total = $('input[name="gqs_order_true_total"]').val();
                                $(this).parent().find('.total').html('<span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">$</span>' + gqs_order_true_total + '</bdi></span>');
                            }
                        }
                    })
                }
            }
        }
        
        
    }
    /**
     * Function to get a product price via Ajax
     */
    function gqs_get_product_price_from_order_item_id(order_item_id) {

       return $.ajax({
            type: 'POST',
            dataType: 'json',
            url: gqs_admin_shop_order_init.ajaxurl,
            data: {
                'action': 'gqs_get_product_price_from_order_item_id',
                'nonce': gqs_admin_shop_order_init.ajaxnonce,
                'order_item_id': order_item_id
            },
            // success: function(data) {
            // },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR + ' :: ' + textStatus + ' :: ' + errorThrown);
            }
        });
    }

    /**
     * Add the custom shipping names
     */
    function setupCustomShipping() {
        // hide the other field
        // $('.gqs-other-shipping-method').css('display', 'none').removeClass('hide');
        $('.gqs-custom-label').css('display', 'none').removeClass('hide');
        $('.gqs-shipping-amount').css('display', 'none').removeClass('hide');

        // show the custom shipping name
        $('.gqs-edit-shipping-label').on('click', function(e) {
            e.preventDefault();
            var that = this;
            var current_row = $(that).parentsUntil('.gqs-label-col').parent();
            $(current_row).find('.gqs-regular-label').slideUp();
            $(current_row).find('.gqs-custom-label').slideDown();
            $(current_row).find('.gqs-use-custom-name-hidden').val('true');
        });
        // hide the custom shipping name
        $('.gqs-hide-shipping-label').on('click', function(e) {
            e.preventDefault();
            var that = this;
            var current_row = $(that).parentsUntil('.gqs-label-col').parent();
            $(current_row).find('.gqs-custom-label').slideUp();
            $(current_row).find('.gqs-use-custom-name-hidden').val('false');
            $(current_row).find('.gqs-regular-label').slideDown();
        });
        // reset the form
        $('#gqs_reset_form').on('click', function(e) {
            e.preventDefault();
            $('.gqs-hide-shipping-label').click();
            $('.gqs-shipping-checkbox').each(function() {
                $(this).prop( "checked", false );
            })
            $('.gqs-custom-shipping-name').each(function() {
                $(this).val($(this).attr('value'));
            });
            $('.gqs-shipping-amount').each(function() {
                $(this).val('0');
                $(this).hide();
            });
        });

        // show amounts with checkboxes 
        $('.gqs-shipping-checkbox').on('click', function(e) {
            if( $(this).is(':checked') ) {
                $(this).parentsUntil('.gqs-options-row').parent().find('.gqs-shipping-amount').show();
            } else {
                $(this).parentsUntil('.gqs-options-row').parent().find('.gqs-shipping-amount').hide();
            }
        });
        $('#gqs_add_shipping').on('click', function(e) {
            e.preventDefault();

            // get the checkboxes
            var shipping_options = new Array();
            $('.gqs-shipping-checkbox').each(function(){
                current_checkbox = this;
                if( $(current_checkbox).is(':checked') ) {
                    // get the id and then split to component parts
                    var checkbox_id = $(current_checkbox).attr('id');
                    var shipping_id = checkbox_id.split("[")[0];

                    // see if there's an amount set
                    var shipping_amount = $('#' + shipping_id + '\\[amount\\]').val();
                    if(shipping_amount < 0) {
                        // show error
                        $('#' + shipping_id + '\\[error\\]').show();
                    } else{
                        $('#' + shipping_id + '\\[error\\]').hide();
                        // see if custom name is being used
                        var use_custom_name = $('#' + shipping_id + '\\[use_custom_name\\]').val();
                        if(use_custom_name === 'true' || shipping_id == 'other') {
                            // get the custom name
                            var shipping_name = $('#' + shipping_id + '\\[custom_name\\]').val();
                        } else {
                            var shipping_name = $(current_checkbox).val();
                        }

                        // we can define the array options
                        var this_shipping_option = { 
                            'method' : shipping_name,
                            'amount' : shipping_amount
                        };
                        // add to the array
                        shipping_options.push(this_shipping_option);

                    }

                }

            });
            // see if no items have been checked and show error
            if (shipping_options.length === 0) {
                // show error
                $('#gqs-shipping-error').show();
                setTimeout(function() {
                    $('#gqs-shipping-error').hide();
                }, 10000);
                return false;
            }
            /**
             * Async function to click the add shipping button
             * and add options
             */
            async function loop_shipping_options () {
                for(var i=0;i<shipping_options.length;i++){
                    const result = await shipping_observer(shipping_options[i].method, shipping_options[i].amount);
                }
                return;
            };

            /**
             * Waits for the loop to finish and then clicks the
             * save button to save the shipping options
             */
            async function save_shipping_options(){
                
                $('html, body').animate({
                    scrollTop: ($('#order_shipping_line_items').first().offset().top - 150)
                }, 500);
                await loop_shipping_options();
                var result = $('.button.save-action').click();
                var gineico_site = $('input[name="gineico_site"]').val();
                if(gineico_site == 'GM') {
                    // recalculate taxes on GM
                    $('.button.calculate-action').click();
                }
                $('#gqs_reset_form').click();
                
            };
            save_shipping_options();
            
        });
    }

    /**
     * function that returns a promise, which clicks
     * the add shipping button and enters the info
     */
    function shipping_observer(method, amount, rejectTime = 50) {

        return new Promise((resolve,reject) => {

            // click the button
            $('.button.add-order-shipping').click(); 

            // let hasChanged = false;

            // Create an observer instance
            var observer = new MutationObserver(function( mutations ) {
                mutations.forEach(function( mutation ) {		
                    var newNodes = mutation.addedNodes; 
                    // If there are new nodes added
                    if( newNodes !== null ) { 
                        var $nodes = $( newNodes ); 
                        $nodes.each(function() {
                            var $node = $( this );
                            // check if new node added with class 'shipping'
                            if( $node.hasClass("shipping")){			
                                // get the id
                                order_item_id = $node.data('order_item_id');

                                $('input[name="shipping_method_title[' + order_item_id + ']"]').val(method);
                                $('input[name="shipping_cost[' + order_item_id + ']"]').val(amount);

                                // hasChanged = true;
                                observer.disconnect();

                                resolve(method);

                            }
                        });
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
            var targetNode = $('#order_shipping_line_items')[0];
            observer.observe(targetNode, config);  
        });
    }

    /**
     * Update the custom version string on the create PDF
     * link if it has been clicked.
     */
    function addCustomVersionStringToPDFurl() {
        $(document).on('click', '#ywraq_pdf_button', function() {

            var pdf_revision_number = $('#_gqs_ywraq_pdf_revision_number_html').val();
            var order_id = $('#post_ID').val();

            var currentUrl = $(this).data('pdf');
            var url_without_params = currentUrl.split('?')[0];
            var url_without_extension = url_without_params.split('.pdf')[0];
            var url_without_rev = url_without_extension.split('-REV')[0];

            if(pdf_revision_number >= 1) {
                var url_new_name = url_without_rev + '-REV' + pdf_revision_number + '.pdf';
            } else {
                var url_new_name = url_without_rev + '.pdf';
            }
            var url = new URL(url_new_name);

            url.searchParams.set("ver", makeid(6)); // setting your param
            $(this).data('pdf', url.href);

            // AJAX
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: gqs_admin_shop_order_init.ajaxurl,
                data: {
                    'action': 'gqs_save_pdf_name',
                    'nonce': gqs_admin_shop_order_init.ajaxnonce,
                    'pdf_name': pdf_revision_number,
                    'order_id': order_id,
                },
                // success: function(data) {
                //     console.log(data);
                // },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log(jqXHR + ' :: ' + textStatus + ' :: ' + errorThrown);
                }
            });
        });

    }
    /**
     * Random string generator
     */
    function makeid(length) {
        var result           = '';
        var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var charactersLength = characters.length;
        for ( var i = 0; i < length; i++ ) {
            result += characters.charAt(Math.floor(Math.random() * charactersLength));
        }
        return result;
    }


});