/**
 * Functions
 */
	var url = window.location.protocol + "//" + window.location.host + "/";

/**
 * jQuery Utility Functions
 */
	function formStyle (mode) {
		if(mode) {
			$('.field').hide();
			$('#contact_'+mode+'_fields').show();
		}
	}
	function formValidate (obj) {
		formProcessing();
		$.post($('#formsend').attr('action').replace('callback','callback-mini'), obj, function(data) {
			var response = formResponse(data);
			if(response.complete == true) {
				formComplete(response.code);
			} else {
				formResponseError(response.text);
				processing = false;
			}
		});
	}
	function formProcessing () {
		$('#note').html('<span id="note-text">Please wait</span>');
		$('#note-text').fadeOut(5000);
	}
	function formResponseError (text) {
		$('#note').html('<span id="note-text"><strong>Error</strong><br /><br />'+text+'</span>');
		$('#note-text').fadeOut(5000);
		processing = false;
		$('#submit').attr('disabled','');
		$('#submit').click(function(){
			formSubmit();
			return false;
		});
	}
	function formComplete (code) {
		var codeRef = code;
		$('#formsend').fadeOut('fast',function(){
			$(this).remove();
			$('.body').prepend(
				'<div id="formsent" style="display:none;">'+
				'<h1>An advisor will contact you within 1-3 business days.</h1>'+
				'<p>The advisor must quote the following reference number: <strong>'+codeRef+'</strong></p>'+
				'<p>You are under no obligation to accept any quote provided.</p>'+
				'<h2>For your information:</h2>'+
				'<div class="info">'+
				'<p><strong>Service provided by <a href="http://www.lea-financial-services.co.uk/" target="_new">LEA Financial Services</a></strong></p>'+
				'<p><strong>Others Services you may be interested in include:</strong></p>'+
				'<p>Personal Protection, Business Finance and Residential Mortgages</p>'+
				'</div>'
			);
			$('#formsent').fadeIn('slow');
		});
	}
	function formResponse (data) {
		var response = {
			complete:	false,
			text:		'',
			code:		''
		};
		if($("code",data).length == 1 && $("complete",data).length == 1 && $("text",data).length == 1) {
			response.complete	= ($('complete',data).text()=='yes' ? true : false);
			response.code		= $('code',data).text();
			response.text		= $('text',data).text();
		} else {
			response.text = 'Parse Error';
		}
		return response;
	}

	var processing  = false;

	function formSubmit () {

		// disable submit button
		$('#submit').attr('disabled','disabled');
		$('#submit').unbind('click');

		if(processing) {
			formProcessing();
		} else {
			processing = true;

			var name			= $('#contact_name').val();
			var method			= ($('#contact_phone').is(':checked') ? $('#contact_phone').val() : ($('#contact_email').is(':checked') ? $('#contact_email').val() : ''));
			var phone			= $('#phone_number').val();
			var email			= $('#email_address').val();
			var start_hour		= $('#start_hour option:selected').val();
			var start_minute	= $('#start_minute option:selected').val();
			var end_hour		= $('#end_hour option:selected').val();
			var end_minute		= $('#end_minute option:selected').val();

			var obj = {
				"name":					name,
				"method":				method,
				"phone":				phone,
				"email":				email,
				"start_hour":			start_hour,
				"start_minute":			start_minute,
				"end_hour":				end_hour,
				"end_minute":			end_minute
			};
			formValidate(obj);
			return false;
		}
	}

$(document).ready(function(){

	$('.hide-me').hide();

	$('#contact_phone, #contact_email').click(function(){
		var type = $(this).attr('id').split('_');
		formStyle(type[1]);
	});
	$('#formsend').submit(function(){
		formSubmit();
		return false;
	});
	$('#submit').click(function(){
		$('#formsend').submit();
		return false;
	});
});