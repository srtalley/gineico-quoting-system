jQuery('body').animate({ scrollTop: jQuery("#order_line_items")[0].scrollHeight}, 1000);


jQuery('html, body').animate({
    scrollTop: jQuery("#order_line_items > tr:last-of-type").offset().top + 100
}, 2000);
jQuery('html, body').animate({
    scrollTop: jQuery("#order_fee_line_items").offset().top - 100
}, 2000);
