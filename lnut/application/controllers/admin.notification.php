<?php

class admin_notification extends Controller {

    private $token = 'list';
    private $arrTokens = array(
	'list',
	

    );
    private $parts = array();
    private $objnotification = null;

    public function __construct() {

	parent::__construct();

	$this->parts = config::get('paths');

	if (isset($this->parts[2]) && in_array($this->parts[2], $this->arrTokens)) {
	    $this->token = $this->parts[2];
	}

	if (in_array($this->token, $this->arrTokens)) {
	    $method = 'do' . ucfirst($this->token);
	    $this->$method();
	}
    }

    protected function doList() {
	$skeleton = new xhtml('skeleton.admin');
	$skeleton->load();
	$hide = 'style="visibility:hidden;"';
	$body = new xhtml('body.admin.notification.list');
	$body->load();

	$this->objnotification = new notification();
	$arrnotification = $this->objnotification->getList('notification_created');
	$i = 0;
	if ($arrnotification && count($arrnotification) > 0) {
	    $rows = array();
	    foreach ($arrnotification as $notification_event_uid => $arrData) {
		$i++;
		$row = new xhtml('body.admin.notification.list.row');
		$row->load();		
		list($y,$m,$d)=explode("-", $arrData['notification_created']);		
		$row->assign(array(
		    'notification_skill_uid' => $notification_event_uid,		    
		    'from_username' => stripslashes($arrData['from_username']),
		    'to_username' => stripslashes($arrData['to_username']),
		    'from_usertype' => stripslashes($arrData['from_usertype']),
		    'to_usertype' => stripslashes($arrData['to_usertype']),
		    'message' => stripslashes($arrData['message']),
		    'notification_created' => date("d/m/Y", mktime(0,0,0,$m,$d,$y))
		    
		));
		$rows[] = $row->get_content();
	    }
	    $body->assign('rows', implode('', $rows));
	}
	$page_display_name = $this->objnotification->get_page_name('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
	$page_navigation = $this->objnotification->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>') . $this->objnotification->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>', ' &raquo ') . $this->objnotification->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');
	$body->assign('page.display.name', $page_display_name);
	$body->assign('page.navigation', $page_navigation);
	$skeleton->assign(
		array(
		    'body' => $body
		)
	);

	output::as_html($skeleton, true);
    }

}

?>