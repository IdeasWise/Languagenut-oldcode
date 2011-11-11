//---------------------------------------
// LanguageNut Login Widget jQuery Plugin
// Copyright (c) 2011 languagenut.com
//
// It is not permitted to copy or modify
// this plugin without prior written
// authorisation from languagenut.com.
//---------------------------------------

(function( $ ){
	$.fn.lnutLoginWidget = function( options ) {

		var settings = {
			locale	: 'en',
			usecss	: 'yes',
			option	: 'direct',
			value	: '',
			css		: 'http://www.languagenut.com/styles/lnut-login-widget.css'
		};

		if ( options ) {
			$.extend( {}, settings, options );
		}

		if(settings.usecss && 'yes'==settings.usecss) {
			$('head').append('<link rel="stylesheet" type="text/css" href="' + settings.css +'" />');
		}

		var $html = '<div id="language-nut-widget">';
			$html += '<form id="frm-login" method="post" action="http://www.languagenut.com/'+settings.locale+'/login/">';
			$html += '<input type="hidden" value="login" name="form" />';

			$html += '<p class="widget-text">';
				$html += '<label for="lnut_email">';
					$html += '<span>Username</span>';
					$html += '<input type="text" value="" id="lnut_email" name="email" />';
				$html += '</label>';
			$html += '</p>';

			$html += '<p class="widget-text">';
				$html += '<label for="lnut_password">';
					$html += '<span>Password</span>';
					$html += '<input type="password" value="" id="lnut_password" name="password" />';
				$html += '</label>';
			$html += '</p>';

			$html += '<p class="widget-submit">';
				$html += '<input type="submit" name="submit" value="Login" id="login-button" title="LOGIN" />';
				$html += '<span class="widget-error"></span>';
			$html += '</p>';

			$html += '</form>';
			$html += '<div class="widget-footer">';
			$html += '<a href="http://www.languagenut.com/'+settings.locale+'/trynow/" class="lnut-trynow">trynow</a>';
			$html += '<a href="http://www.languagenut.com/'+settings.locale+'/subscribe/school/" class="lnut-free-trial">free trial</a>';
			$html += '</div>';
			$html += '</div>';
		this.html($html);

		var $error		= $('span.widget-error');
		var $email		= $('#lnut_email');
		var $password	= $('#lnut_password');

		$('#frm-login').live('submit',function() { 
			if(!settings.option) {
				return true;
			}
			$error.html('');
			if($email.val() == '' || $password.val() == '') {
				$error.html('<br /><span style="color:#880000;font-family:Arial;font-size:0.8em;font-weight:bold;">Details Not Recognised!</span>');
				return false;
			}
			$.getJSON('http://www.languagenut.com/login_widget/?jsoncallback=?',{email:$email.val(),password:$password.val(),format:"json"},function(data) {
				if(data.status) {
					// if there is error in status do the following...
					if( data.status === 'error' && data.data) {
						$error.html('<p>'+data.data+'</p>');
					}

					// if status is success do following...
					if( data.status === 'success' && data.data) {
						console.log(data);
						console.log(data.data);
						$email.val('');
						$password.val('');
						$error.html('<p>Logged In</p>');

						if(settings.option) {
							if( settings.option === 'iframe' && settings.value ) {
								// if option is iframe and iframe id is given in value then just set src...
								$('#'+settings.value).attr('src',data.data);
							} else if( settings.option === 'noiframe' && settings.value ) {
								// if option is noiframe and div id is given in value then just add iframe...
								$('#'+settings.value).html('<iframe src="' + data.data + '"  class="lnutwidget-iframe"></iframe>');
							} else if(data.data) {
								// if none of option is provide form above then open new window.
								var win = window.open (data.data+'',"languagenutlogin"+(new Date).getTime(),'resizeable,scrollbars');
							}
						}
					}
				}
			});
			return false;
		});
	};
})( jQuery );

// USAGE
// -- put the results in <iframe id="content-goes-here"></iframe> with english interface
// $('#widget').lnutLoginWidget({option:'iframe',value:'content-goes-here',locale:'en'});
//
// -- put the results in <div id="content-goes-here"></div> with english interface
// $('#widget').lnutLoginWidget({option:'noiframe',value:'content-goes-here',locale:'en'});
//
// -- put the result in a new window with english interface
// $('#widget').lnutLoginWidget({option:'popup',locale:'en'});
//
// -- direct submission to the languagenut.com website in the fr locale's language (french)
// $('#widget').lnutLoginWidget({locale:'fr'});
//
// -- direct submission to the languagenut.com website in the english language - no options provided
// $('#widget').lnutLoginWidget();