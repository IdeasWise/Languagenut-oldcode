function attachClickHandlers () {
	$('.invoice-sent, .invoice-unsent, .invoice-paid, .invoice-unpaid').unbind('click');
	$('.invoice-sent').click(function(){
		var school_id = $(this).attr('href').split('_')[1];
		var url = 'http://www.languagenut.com/api.php';
		var obj = {'school_id':school_id,'action':'unsent'};
		$.post(url,obj,sentHandler,'xml');
		return false;
	});
	$('.invoice-unsent').click(function(){
		var school_id = $(this).attr('href').split('_')[1];
		var url = 'http://www.languagenut.com/api.php';
		var obj = {'school_id':school_id,'action':'sent'};
		$.post(url,obj,unsentHandler,'xml');
		return false;
	});
	$('.invoice-paid').click(function(){
		var school_id = $(this).attr('href').split('_')[1];
		var url = 'http://www.languagenut.com/api.php';
		var obj = {'school_id':school_id,'action':'unpaid'};
		$.post(url,obj,paidHandler,'xml');
		return false;
	});
	$('.invoice-unpaid').click(function(){
		var school_id = $(this).attr('href').split('_')[1];
		var url = 'http://www.languagenut.com/api.php';
		var obj = {'school_id':school_id,'action':'paid'};
		$.post(url,obj,unpaidHandler,'xml');
		return false;
	});
}

function sentHandler (xml) {
	if($("action",xml).length == 1) {
		// Get Common Response
		var action	= $("action",xml).text();

		$("a[href='#invoice_"+action+"']").each(function(){
			if($(this).attr('class')=='invoice-sent') {
				$(this).attr('title','Click to mark as not sent');
				$(this).find('img').each(function(){
					$(this).attr('src','http://www.languagenut.com/images/notsent.png');
				});
				$(this).removeClass('invoice-sent').addClass('invoice-unsent');
			}
		});
		attachClickHandlers();
	} else {
		alert('Invalid Response');
	}
}
function unsentHandler (xml) {
	if($("action",xml).length == 1) {
		// Get Common Response
		var action	= $("action",xml).text();

		$("a[href='#invoice_"+action+"']").each(function(){
			if($(this).attr('class')=='invoice-unsent') {
				$(this).attr('title','Click to mark as sent');
				$(this).find('img').each(function(){
					$(this).attr('src','http://www.languagenut.com/images/sent.png');
				});
				$(this).removeClass('invoice-unsent').addClass('invoice-sent');
			}
		});
		attachClickHandlers();
	} else {
		alert('Invalid Response');
	}
}
function paidHandler (xml) {
	if($("action",xml).length == 1) {
		// Get Common Response
		var action	= $("action",xml).text();

		$("a[href='#invoice_"+action+"']").each(function(){
			if($(this).attr('class')=='invoice-paid') {
				$(this).attr('title','Click to mark as not paid');
				$(this).find('img').each(function(){
					$(this).attr('src','http://www.languagenut.com/images/notsent.png');
				});
				$(this).removeClass('invoice-paid').addClass('invoice-unpaid');
			}
		});
		attachClickHandlers();
	} else {
		alert('Invalid Response');
	}
}
function unpaidHandler (xml) {
	if($("action",xml).length == 1) {
		// Get Common Response
		var action	= $("action",xml).text();

		$("a[href='#invoice_"+action+"']").each(function(){
			if($(this).attr('class')=='invoice-unpaid') {
				$(this).attr('title','Click to mark as paid');
				$(this).find('img').each(function(){
					$(this).attr('src','http://www.languagenut.com/images/sent.png');
				});
				$(this).removeClass('invoice-unpaid').addClass('invoice-paid');
			}
		});
		attachClickHandlers();
	} else {
		alert('Invalid Response');
	}
}

$(document).ready(function() {
	attachClickHandlers();
});