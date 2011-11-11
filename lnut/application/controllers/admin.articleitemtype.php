<?php

class admin_articleitemtype extends Controller {

    private $token = 'list';
    private $arrTokens = array(
	'list',
	'edit',
	'add',
	'delete'

    );
    private $parts = array();
    private $objarticleitemtype = null;

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

	$body = new xhtml('body.admin.articleitemtype.add');
	$body->load();
	$body->assign('title', 'Activities > <a href="{{ uri }}admin/articleitemtype/list/">Article Item Type</a> > Add');
	if (count($_POST) > 0) {
	    $this->objarticleitemtype = new articleitemtype();
	    if (($response = $this->objarticleitemtype->isValidCreate($_POST)) === true) {
		output::redirect(config::url('admin/articleitemtype/list/'));
	    } else {
		$body->assign($response);
	    }
	}
	
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

	$body = new xhtml('body.admin.articleitemtype.edit');
	$body->load();
	$body->assign('title', 'Activities > <a href="{{ uri }}admin/articleitemtype/list/">Article Item Type</a> > Update');
	$exercise_type_uid = (isset($this->parts[3]) && (int) $this->parts[3] > 0) ? $this->parts[3] : '';

	if ($exercise_type_uid != '') {

	    $this->objarticleitemtype = new articleitemtype($exercise_type_uid);
	    $this->objarticleitemtype->load();

	    $arrqaetopic = $this->objarticleitemtype->getFields();


	    if (count($arrqaetopic) > 0) {

		if (count($_POST) > 0) {
		    $_POST['exercise_type_uid'] = $exercise_type_uid;
		    if (($arrqaetopic = $this->objarticleitemtype->isValidUpdate($_POST)) === true) {
			output::redirect(config::url('admin/articleitemtype/list/'));
		    } else {
			$body->assign($arrqaetopic);
		    }
		}

		$body->assign('articleitemtype_uid', $exercise_type_uid);
		$body->assign('name', $arrqaetopic['name']);
		
	    }
	} else {
	    output::redirect(config::url('admin/articleitemtype/list/'));
	}
	
	$skeleton->assign(
		array(
		    'body' => $body
		)
	);
	output::as_html($skeleton, true);
    }

    protected function doDelete() {

	if (isset($this->parts[3]) && (int) $this->parts[3] > 0) {
	    $objarticleitemtype = new articleitemtype($this->parts[3]);
	    $objarticleitemtype->delete();

	    $objarticleitemtype->redirectTo('admin/articleitemtype/list/');
	} else {
	    output::redirect(config::url('admin/articleitemtype/list/'));
	}
    }

    protected function doList() {
	$skeleton = new xhtml('skeleton.admin');
	$skeleton->load();
	$hide = 'style="visibility:hidden;"';
	$body = new xhtml('body.admin.articleitemtype.list');
	$body->load();

	$this->objarticleitemtype = new articleitemtype();
	$arrarticleitemtype = $this->objarticleitemtype->getListByname();
	$i = 0;
	if ($arrarticleitemtype && count($arrarticleitemtype) > 0) {
	    $rows = array();
	    foreach ($arrarticleitemtype as $exercise_type_uid => $arrData) {
		$i++;

		$row = new xhtml('body.admin.articleitemtype.list.row');
		$row->load();
		$row->assign(array(
		    'articleitemtype_skill_uid' => $exercise_type_uid,
		    'name' => stripslashes($arrData['name']),
		    
		));
		$rows[] = $row->get_content();
	    }
	    $body->assign('rows', implode('', $rows));
	}
	$page_display_name = $this->objarticleitemtype->get_page_name('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
	$page_navigation = $this->objarticleitemtype->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>') . $this->objarticleitemtype->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>', ' &raquo ') . $this->objarticleitemtype->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');
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