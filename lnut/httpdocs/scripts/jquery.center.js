(function($){

	jQuery.fn.center = function (absolute) {
		return this.each(function () {
			var t = jQuery(this);
	
			t.css({
				position:	absolute ? 'absolute' : 'fixed', 
				left:		'50%', 
				top:		'50%'
			}).css({
				marginLeft:	'-' + (t.outerWidth() / 2) + 'px', 
				marginTop:	'-' + (t.outerHeight() / 2) + 'px'
			});
	
			if (absolute) {
				t.css({
					marginTop:	parseInt(t.css('marginTop'), 10) + jQuery(window).scrollTop(), 
					marginLeft:	parseInt(t.css('marginLeft'), 10) + jQuery(window).scrollLeft()
				});
			}
		});
	};

})(jQuery);