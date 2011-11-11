/*-------------------------------------------------------------------------------
	A Better jQuery Tooltip
	Version 1.0
	By Jon Cazier
	jon@3nhanced.com
	01.22.08
-------------------------------------------------------------------------------*/

$.fn.betterTooltip = function(options){
	
	/* Setup the options for the tooltip that can be 
	   accessed from outside the plugin              */
	var defaults = {
		speed: 200,
		delay: 300
	};
	
	var options = $.extend(defaults, options);
	
	/* Create a function that builds the tooltip 
	   markup. Then, prepend the tooltip to the body */
	getTip = function() {
		var tTip = 
			'<div class="tip">'+
			'<div class="clearfix"><div class="tipTL"></div><div class="tipTM"></div><div class="tipTR"></div></div>' +
			'<div class="clearfix"><div class="tipML"></div><div class="tipMid"></div><div class="tipMR"></div></div>' +
			'<div class="clearfix"><div class="tipBL"></div><div class="tipBM"></div><div class="tipBR"></div></div>' +
			'</div>';
		return tTip;
	};
	$("body").prepend(getTip());
	
	/* Give each item with the class associated with 
	   the plugin the ability to call the tooltip    */
	$(this).each(function(){
		
		var $this = $(this);
		var tip = $('.tip');
		var tipInner = $('.tip .tipMid');
		
		var tTitle = (this.title);
		//var bgImage = $(this).css('background-image').replace(/\"/g,"").replace(/url\(|\)$/ig, "").replace(/thumb/,'popup');
		var bgImage = $(this).find('img').attr('src').replace(/thumb/,'popup');
		this.title = "";
		
		var offset = $(this).offset();
		var tLeft = offset.left;
		var tTop = offset.top;
		var tWidth = $this.width();
		var tHeight = $this.height();
		var tPosition = parseInt($(this).attr('rel'));
		
		/* Mouse over and out functions*/
		$this.hover(
			function() {
				//tipInner.html(tTitle);
				tipInner.html('<img src="'+bgImage+'" width="400px" />');
				setTip(tTop, tLeft, tPosition);
				setTimer();
			}, 
			function() {
				stopTimer();
				tip.hide();
			}
		);
		
		/* Delay the fade-in animation of the tooltip */
		setTimer = function() {
			$this.showTipTimer = setInterval("showTip()", defaults.delay);
		};
		
		stopTimer = function() {
			clearInterval($this.showTipTimer);
		};
		
		/* Position the tooltip relative to the class 
		   associated with the tooltip                */
		// container is 800 and tip is 430px
		setTip = function(top, left, position) {
			var topOffset = tip.height();
			if(position==1) {
				var xTip = (left-25)+"px";
			} else if(position==2) {
				var xTip = (left-60)+"px";
			} else if(position==3) {
				var xTip = (left-140)+"px";
			} else if(position==4) {
				var xTip = (left-220)+"px";
			} else if(position==5) {
				var xTip = (left-255)+"px";
			}
			var yTip = (top-topOffset-60)+"px";
			tip.css({'top' : yTip, 'left' : xTip});
		};
		
		/* This function stops the timer and creates the
		   fade-in animation                          */
		showTip = function(){
			stopTimer();
			tip.animate({"top": "+=20px", "opacity": "toggle"}, defaults.speed);
		}
	});
};