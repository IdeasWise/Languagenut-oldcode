<?php

class admin_skills extends Controller {

    private $token = 'list';
    private $arrTokens = array(
	'list',
	'edit',
	'add',
	'delete'

    );
    private $parts = array();
    private $objActivity = null;

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

    protected function doAdd() {
	$skeleton = new xhtml('skeleton.admin');
	$skeleton->load();

	$body = new xhtml('body.admin.activity.add');
	$body->load();
	$body->assign('title', 'Activities > <a href="{{ uri }}admin/skills/list/">Skills</a> > Add');
	if (count($_POST) > 0) {
	    $this->objActivity = new activity();
	    if (($response = $this->objActivity->isValidCreate($_POST)) === true) {
		output::redirect(config::url('admin/skills/list/'));
	    } else {
		$body->assign($response);
	    }
	}


	$tabs_li = array();
	$tabs_div = array();

	$arrLocales = language::getPrefixes();
	if (count($arrLocales) > 0) {
	    foreach ($arrLocales as $uid => $arrData) {
		$lang_form = "";
		$nameVal = (isset($_POST['name_' . $arrData['prefix']])) ? $_POST['name_' . $arrData['prefix']] : "";

		$tabs_li[] = '<li><a href="#tab-' . $uid . '"><span>' . $arrData['prefix'] . '</span></a></li>';
		$lang_form.='<table>';
		$lang_form.='<tr>';
		$lang_form.='<td>';
		$lang_form.='name';
		$lang_form.='</td>';
		$lang_form.='<td>';
		$lang_form.='<input type="text" name="name_' . $arrData['prefix'] . '" value="' . $nameVal . '" >';
		$lang_form.='</td>';
		$lang_form.='</tr>';
		$lang_form.='</table>';
		$tabs_div[] = '<div id="tab-' . $uid . '">' . $lang_form . '</div>';
	    }
	}

	$body->assign(
		array(
		    'tabs' => implode('', $tabs_div),
		    'locales' => implode('', $tabs_li),
		)
	);
	$skeleton->assign(
		array(
		    'body' => $body
		)
	);
	output::as_html($skeleton, true);
    }

    protected function doEdit() {

	$skeleton = new xhtml('skeleton.admin');
	$skeleton->load();

	$body = new xhtml('body.admin.activity.edit');
	$body->load();
	$body->assign('title', 'Activities > <a href="{{ uri }}admin/skills/list/">Skills</a> > Update');
	$activity_skill_uid = (isset($this->parts[3]) && (int) $this->parts[3] > 0) ? $this->parts[3] : '';

	if ($activity_skill_uid != '') {

	    $this->objActivity = new activity($activity_skill_uid);
	    $this->objActivity->load();

	    $arrqaetopic = $this->objActivity->getFields();


	    if (count($arrqaetopic) > 0) {

		if (count($_POST) > 0) {
		    $_POST['activity_skill_uid'] = $activity_skill_uid;
		    if (($arrqaetopic = $this->objActivity->isValidUpdate($_POST)) === true) {
			output::redirect(config::url('admin/skills/list/'));
		    } else {
			$body->assign($arrqaetopic);
		    }
		}

		$body->assign('activity_uid', $activity_skill_uid);
		$body->assign('name', $arrqaetopic['name']);
		$body->assign('available_select_0', ($arrqaetopic['available']=="0")?'selected="selected"':'');
		$body->assign('available_select_1', ($arrqaetopic['available']=="1")?'selected="selected"':'');

	    }
	} else {
	    output::redirect(config::url('admin/skills/list/'));
	}

	$tabs_li = array();
	$tabs_div = array();

	$arrLocales = language::getPrefixes();
	
	if (count($arrLocales) > 0) {
	    foreach ($arrLocales as $uid => $arrData) {
		$activityData = $this->objActivity->getListByLocale($activity_skill_uid, $uid);
		$lang_form = "";

		$nameVal = (isset($_POST['name_' . $arrData['prefix']])) ? $_POST['name_' . $arrData['prefix']] : $activityData["name"];

		$tabs_li[] = '<li><a href="#tab-' . $uid . '"><span>' . $arrData['prefix'] . '</span></a></li>';
		$lang_form.='<table>';		
		$lang_form.='<tr>';
		$lang_form.='<td>';
		$lang_form.='name';
		$lang_form.='</td>';
		$lang_form.='<td>';
		$lang_form.='<input type="text" name="name_' . $arrData['prefix'] . '" value="' . $nameVal . '" >';
		$lang_form.='</td>';
		$lang_form.='</tr>';
//	
		$lang_form.='</table>';
		$tabs_div[] = '<div id="tab-' . $uid . '">' . $lang_form . '</div>';
	    }
	}

	$body->assign(
		array(		    
		    'tabs' => implode('', $tabs_div),
		    'locales' => implode('', $tabs_li),
		)
	);
	$skeleton->assign(
		array(
		    'body' => $body
		)
	);
	output::as_html($skeleton, true);
    }

    protected function doDelete() {

	if (isset($this->parts[3]) && (int) $this->parts[3] > 0) {
	    $objActivity = new activity($this->parts[3]);
	    
	    $sql = "DELETE FROM `activity_skill_translation`";
	    $sql.=" WHERE ";
	    $sql.=" activity_skill_uid='{$this->parts[3]}'";
	    $res = database::query($sql);

	    $objActivity->delete();

	    $objActivity->redirectTo('admin/skills/list/');
	} else {
	    output::redirect(config::url('admin/skills/list/'));
	}
    }

    protected function doList() {
	
	$skeleton = new xhtml('skeleton.admin');
	$skeleton->load();
	$hide = 'style="visibility:hidden;"';
	$body = new xhtml('body.admin.activity.list');
	$body->load();

	$this->objActivity = new activity();
	$arractivity = $this->objActivity->getListByname();
	$i = 0;
	if ($arractivity && count($arractivity) > 0) {
	    $rows = array();
	    foreach ($arractivity as $activity_skill_uid => $arrData) {
		$i++;

		$row = new xhtml('body.admin.activity.list.row');
		$row->load();
		$row->assign(array(
		    'activity_skill_uid' => $activity_skill_uid,
		    'name' => stripslashes($arrData['name']),
		    'available' => ($arrData['available']=='0')?'No':'Yes'
		));
		$rows[] = $row->get_content();
	    }
	    $body->assign('rows', implode('', $rows));
	}
	$page_display_name = $this->objActivity->get_page_name('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
	$page_navigation = $this->objActivity->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>') . $this->objActivity->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>', ' &raquo ') . $this->objActivity->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');
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