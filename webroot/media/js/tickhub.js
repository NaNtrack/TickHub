function showModalWindow (endpoint, params, method, onCompleteFuncion, onCleanupFunction) {
	onCompleteFuncion = onCompleteFuncion == null ? function () {} : onCompleteFuncion;
	onCleanupFunction = onCleanupFunction == null ? function () {} : onCleanupFunction;
	jQuery.fancybox.showActivity();
	jQuery.ajax({
		type     : method,
		cache    : false,
		url      : endpoint,
		data	 : params,
		dataType : 'html',
		success  : function(response){
			var data = {
				'content'            : response,
				'titleShow'          : false,
				'hideOnOverlayClick' : false,
				'enableEscapeButton' : false,
				'padding'            : 0,
				'margin'             : 0,
				'overlayOpacity'     : 0.2,
				'onComplete'	     : onCompleteFuncion,
				'onCleanup'	     : onCleanupFunction
			};
			jQuery.fancybox(data);
		}
	});
	return false;
}

function displayLoginBox(reload) {
	showModalWindow ('/login/', '', 'POST', function(){
		jQuery("#login_form").bind("submit", function() {
			if (jQuery("#user_email").val().length < 1 || jQuery("#user_password").val().length < 1) {
				return false;
			}
			jQuery.ajax({
				type     : jQuery('#login_form').attr('method'),
				cache    : false,
				url      : jQuery('#login_form').attr('action'),
				data	 : jQuery('#login_form').serialize(),
				dataType : 'json',
				success  : function(response){
					jQuery.fancybox.hideActivity();
					if ( response.result == 'OK' ) {
						jQuery.fancybox.close();
						if (reload){
							window.location.reload();
						} else {
							window.location = '/dashboard/';
						}
					} else {
						jQuery('#login_submit').removeAttr("disabled");
						jQuery('#user_password').val('');
						alert(response.reason);
					}
				},
				beforeSend : function () {
					jQuery.fancybox.showActivity();
					jQuery('#login_submit').attr('disabled', 'disabled');
				}, 
				error: function (jqXHR, textStatus, errorThrown) {
					jQuery.fancybox.hideActivity();
					jQuery('#login_submit').removeAttr("disabled");
					var error = 'An error ocurred';
					if ( textStatus != null ) {
						error += ' ('+textStatus+')'
					}
					if ( errorThrown != null) {
						error += ': ' + errorThrown;
					}
					alert(error);
				}
			});
			return false;
		});
	});
	return false;
}

function displayTickspotConnection () {
	showModalWindow ('/tickspot/connect/', '', 'POST', function(){
		jQuery("#tickspot_form").bind("submit", function() {
			if (jQuery("#tickspot_email").val().length < 1 || 
			    jQuery("#tickspot_password").val().length < 1 ||
			    jQuery('#tickspot_company').val().length < 1 ) {
				return false;
			}
			jQuery.ajax({
				type	: jQuery(this).attr('method'),
				cache	: false,
				url	: jQuery(this).attr('action'),
				data	: jQuery(this).serializeArray(),
				dataType: 'json',
				success : function(response) {
					jQuery.fancybox.hideActivity();
					if (response.result == 'OK') {
						jQuery.fancybox.close();
						window.location.reload();
					} else {
						jQuery('#tickspot_submit').removeAttr('disabled').val('Connect!');
						jQuery("#tickspot_password").val('')
						jQuery.fancybox.hideActivity();
						alert(response.reason);
					}
				},
				beforeSend : function () {
					jQuery.fancybox.showActivity();
					jQuery('#tickspot_submit').attr('disabled', 'disabled').val('Connecting...');
				}, 
				error: function (jqXHR, textStatus, errorThrown) {
					jQuery('#tickspot_submit').removeAttr('disabled').val('Connect!');
					jQuery.fancybox.hideActivity();
					var error = 'An error ocurred';
					if ( textStatus != null ) {
						error += ' ('+textStatus+')'
					}
					if ( errorThrown != null) {
						error += ': ' + errorThrown;
					}
					alert(error);
				}
			});
			return false;
		});
	});
	return false;
}