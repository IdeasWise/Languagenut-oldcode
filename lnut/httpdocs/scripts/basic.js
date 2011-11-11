var url = window.location.protocol + "//" + window.location.host + "/";

$(document).ready(function () {
	$('#basicheader a').bind({
		mouseover:	function () {
			var img = $('#basicheader img').attr('src');
			$('#basicheader img').attr('src',img.replace('back','backH'));
		},
		mouseout:	function () {
			var img = $('#basicheader img').attr('src');
			$('#basicheader img').attr('src',img.replace('backH','back'));
		}
	});

	$('.selector-usertype a').bind({
		click:	function () {

			$('.selectors').addClass('animating');

			var data = $('.selectors').data('state');
			var $this = $(this);

			if(!$this.hasClass('active')) {

				$('.selector-usertype a').removeClass('active');
				$this.addClass('active');

				var currentType = data.currentType;
				var newType = data.currentType;

				if($this.hasClass('schools')) {
					newType = 'school';
				} else if($this.hasClass('students')) {
					newType = 'students';
				} else if($this.hasClass('homeusers')) {
					newType = 'homeusers';
				}

				var currentLanguage = data.currentLanguage;
				var newLanguage = data.currentLanguage;

				$('.selectors').data('state',{currentType:newType,currentLanguage:newLanguage});

				$('.stats-view .'+currentType+'-'+currentLanguage).animate({opacity:0},500,function(){
					$(this).css({display:'none'});
					$('.stats-view .'+newType+'-'+newLanguage).css({display:'block'}).animate({opacity:1},500,function(){
						$('.selectors').removeClass('animating');
					});
				});

			} else {
				$('.selectors').removeClass('animating');
			}

			return false;
		}
	});

	$('.selector-languages a').bind({
		click: function () {

			$('.selectors').addClass('animating');

			var data = $('.selectors').data('state');
			var $this = $(this);

			if(!$this.hasClass('active')) {

				$('.selector-languages a').removeClass('active');
				$this.addClass('active');

				var currentLanguage = data.currentLanguage;
				var newLanguage = $this.attr('class').replace('active','').replace(' ','');

				var currentType = data.currentType;
				var newType = data.currentType;

				$('.selectors').data('state',{currentType:newType,currentLanguage:newLanguage});

				$('.stats-view .'+currentType+'-'+currentLanguage).animate({opacity:0},500,function(){
					$(this).css({display:'none'});
					$('.stats-view .'+newType+'-'+newLanguage).css({display:'block'}).animate({opacity:1},500,function(){
						$('.selectors').removeClass('animating');
					});
				});

			} else {
				$('.selectors').removeClass('animating');
			}

			return false;
		}
	});

	$('.selectors').data('state',{currentLanguage:'fr',currentType:'school'});
	$('.locale-school-scores, .all-student-scores, .all-homeuser-scores').not('.school-fr').css({display:'none',opacity:0});
});