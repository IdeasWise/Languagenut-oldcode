/*
* jQuery showPage plugin
* 
* @version  0.1
* @homepage ??
* @author   Boz Kay (http://www.boz.co.uk)
*
* Copyright (c) 2009 Nowhere
*/

(function($){

	$.fn.showPage = function(options){
    
		var opts = $.extend({}, $.fn.showPage.defaults, options);
		
		if(opts.noClick == false) {
			this.click(function() {
				setup();
				showPageNow();
			}).css('cursor','pointer');
		}

		if(opts.showNow == true) {
			setup();
			showPageNow();
		}
		
		function setup() {
			if(opts.width > 0) {
				$(opts.show).width(opts.width);
			}
			if(opts.height > 0) {
				$(opts.show).height(opts.height);
			}
			if($(opts.show).filter('.close').length == 0) {
	
				// setup initial html elements
			    $(opts.show).prepend($('<a>').addClass('close').click(function(){
					$(opts.show).fadeOut(300);
					$('#popupMask').fadeOut(300);
				}));
		
			}
			$(opts.show).center().addClass('popup');
			if($('#popupMask').length == 0) {
				//$('body').append($('<div>').attr('id','popupMask').height($('body').innerHeight()).hide());
			}
		
		}
		
		function showPageNow() {
			$(opts.show).fadeIn(200);
			//$('#popupMask').show();
		}
		return this;
		
	}
	
	

	$.fn.showPage.defaults = {
		show: '#selector',
		width: 0,
		height: 0,
		showNow: false,
		noClick: false
	};        

})(jQuery);