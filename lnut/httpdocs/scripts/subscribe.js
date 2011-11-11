$(document).ready(function() {

	$('.nutsWrap').pngFix();
	//$('.confirm').pngFix();
	$('label').pngFix();
	$('#header').pngFix();
	$('div.errors').pngFix();

	if($('div.errors').length > 0) {

		$('#notErrors').hide();
		
		$('.errorClose').click(function() {
			$('div.errors').hide();
			$('#notErrors').fadeIn(300);
		});
		
		/*$().showPage({
			show: 'div.errors',
			width: 550,
			noClick: true,
			showNow: true
		});

		$('.errorClose').click(function() {
			$('div.errors').fadeOut(300);
			$('#popupMask').fadeOut(300);
		});*/
	}
	/*$('#basicheader a').bind({
		mouseover:	function () {
			var img = $('#basicheader img').attr('src');
			$('#basicheader img').attr('src',img.replace('back','backH'));
		},
		mouseout:	function () {
			var img = $('#basicheader img').attr('src');
			$('#basicheader img').attr('src',img.replace('backH','back'));
		}
	});
	*/
	$('.confirm-button').bind({
		mouseover:	function () {
			$(this).css({'background-position':'-131px top'});
		},
		mouseout:	function () {
			$(this).css({'background-position':'left top'});
		}
	});
	/*$('#subscribe-school, #subscribe-homeuser').bind({
		mouseover:	function () {
			var img = $(this).find('img').attr('src');
			$(this).find('img').attr('src',img.replace('.png','H.png'));
		},
		mouseout:	function () {
			var img = $(this).find('img').attr('src');
			$(this).find('img').attr('src',img.replace('H.png','.png'));
		}
	});
	*/
});