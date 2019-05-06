function do_nothig()
{}

jQuery( document ).ready(function() {
    jQuery('#hotpay_kodsms').val(jQuery('#sms_code').val());
	if (jQuery('.prize_generator').val())
		jQuery('.hid').remove();
});

jQuery( document ).on( 'click', '.prize_generator', function(event) {
	
	jQuery('#gif_loading').prepend('<img src="https://losujboxa.pl/wp-content/uploads/2018/09/kisspng-question-mark-free-content-clip-art-question-mark-vector-5aab6afc7fbf08.9156542615211834845233.png" class="pulse"></img>');
    event.preventDefault();
    
    var hotpay_kodsms = jQuery("#hotpay_kodsms").val();
    var adress = jQuery("#action_adress").val();
    jQuery.ajax({ 
         data: {action: 'get_prize', hotpay_kodsms:hotpay_kodsms},
         type: 'post',
         url: adress,
         success: function(data) {
              jQuery('#msg_alert').prepend(data);
              var button = jQuery('.prize_generator');
              button.prop('disabled', true);
			  jQuery('#gif_loading').remove();

        },
		error: function (xhr, ajaxOptions, thrownError) {
				console.log(xhr.status);
				console.log(thrownError);
				console.log(xhr.responseText);
			}
    });
});

jQuery( document ).on( 'click', '.get_prizes', function(event) {
    event.preventDefault();
    var adress = jQuery("#action_adress").val();
    jQuery.ajax({ 
         data: {action: 'get_prize'},
         type: 'post',
         url: adress,
         success: function(data) {
              jQuery('#prizes_box').prepend(data);
              var button = jQuery('.get_prizes');
              button.prop('disabled', true);
        }
    });
});

jQuery( document ).on( 'click', '.points_box', function(event) {
	jQuery('#gif_loading').prepend('<img src="https://losujboxa.pl/wp-content/uploads/2018/09/kisspng-question-mark-free-content-clip-art-question-mark-vector-5aab6afc7fbf08.9156542615211834845233.png" class="pulse"></img>');
    event.preventDefault();
	var adress = jQuery("#action_adress").val();
    jQuery.ajax({ 
         data: {action: 'points_box'},
         type: 'post',
         url: adress,
         success: function(data) {
              jQuery('.prize').prepend(data);
              var button = jQuery('.points_box');
              button.prop('disabled', true);
			  jQuery('#gif_loading').remove();

        },
		error: function (xhr, ajaxOptions, thrownError) {
				console.log(xhr.status);
				console.log(thrownError);
				console.log(xhr.responseText);
			}
    });
});

jQuery( document ).on( 'click', '.daily_box', function(event) {
	
	jQuery('#prize_loading').prepend('<img src="https://losujboxa.pl/wp-content/uploads/2018/09/kisspng-question-mark-free-content-clip-art-question-mark-vector-5aab6afc7fbf08.9156542615211834845233.png" class="pulse"></img>');
    event.preventDefault();
    
   if (jQuery(this).hasClass('prize-bronze'))
	   var category = 'bronze';
	
	else if (jQuery(this).hasClass('prize-silver'))
	   var category = 'silver';
	
	else if (jQuery(this).hasClass('prize-gold'))
	   var category = 'gold';
	
	var adress = jQuery("#prize_adress").val();
	
    jQuery.ajax({ 
         data: {action: 'get_prize', category:category},
         type: 'post',
         url: adress,
         success: function(data) {
              jQuery('.prize_for_unbox').prepend(data);
			  jQuery('#prize_loading').remove();

        },
		error: function (xhr, ajaxOptions, thrownError) {
				console.log(xhr.status);
				console.log(thrownError);
				console.log(xhr.responseText);
			}
    });
});