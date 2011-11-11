var url = window.location.protocol + "//" + window.location.host + "/";

$(document).ready(function () {

	jQuery.preloadImages = function()
	{
	  for(var i = 0; i<arguments.length; i++)
	  {
	    jQuery("<img>").attr("src", arguments[i]);
	  }
	}

	$('#header').pngFix();
	$('#subNow').pngFix();

	$('#about').pngFix();
	$('#songs').pngFix();
	$('#culture').pngFix();
	$('#teachers').pngFix();
	$('#children').pngFix();
	
	$.preloadImages(url+'images/loginH.png',url+'images/subscribeH.png',url+'images/subShow.jpg',url+'images/gameIcons/iconCrosses.jpg',url+'images/gameIcons/iconHangman.jpg',url+'images/gameIcons/iconListen.jpg',url+'images/gameIcons/iconMatch.jpg',url+'images/gameIcons/iconMemory.jpg',url+'images/gameIcons/iconMultiple.jpg',url+'images/gameIcons/iconPresentation.jpg');

	$('.login').html(' ');
	$('.subscribe').html(' ');
	
	$('#vScreen div.cycle').cycle({
		fx: 'fade', // choose your transition type, ex: fade, scrollUp, shuffle, etc...
		timeout: 3000
	});
	
	
	jQuery(document.body).imageZoom();
	
	$('.hangman').hover(function() {
		$('#gameIcons').css('background-image','url('+url+'images/gameIcons/iconHangman.png)')
	},function(){});
	$('.pairs').hover(function() {
		$('#gameIcons').css('background-image','url('+url+'images/gameIcons/iconMatch.png)')
	},function(){});
	$('.memory').hover(function() {
		$('#gameIcons').css('background-image','url('+url+'images/gameIcons/iconMemory.png)')
	},function(){});
	$('.multipleChoice').hover(function() {
		$('#gameIcons').css('background-image','url('+url+'images/gameIcons/iconMultipleChoice.png)')
	},function(){});
	$('.listenAndLearn').hover(function() {
		$('#gameIcons').css('background-image','url('+url+'images/gameIcons/iconListen.png)')
	},function(){});
	$('.presentation').hover(function() {
		$('#gameIcons').css('background-image','url('+url+'images/gameIcons/iconPresentation.png)')
	},function(){});
	$('.noughtsAndCrosses').hover(function() {
		$('#gameIcons').css('background-image','url('+url+'images/gameIcons/iconCrosses.png)')
	},function(){});

	$('#subnow-image img, #try-now-image img').bind({
		mouseover: function(){
			$(this).attr('src',$(this).attr('src').replace('.png','H.png'));
		},
		mouseout: function(){
			$(this).attr('src',$(this).attr('src').replace('H.png','.png'));
		}
	});
	
});