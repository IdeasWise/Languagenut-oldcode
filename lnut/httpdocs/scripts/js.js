$(document).ready(function() {
	$(".side-bar").hide();
	if($(".error").length > 0) {
		$(".side-bar").fadeIn();
		$('#close').click(function() {
			$(".side-bar").fadeOut();
		});
	}
	$('#login-button').bind({
		mouseover: function () {
			$(this).css({'background-position':'-152px top'});
		},
		mouseout:	function () {
			$(this).css({'background-position':'left top'});
		}
	});
});