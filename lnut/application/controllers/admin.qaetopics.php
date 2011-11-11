<?php

class admin_qaetopics extends Controller {

    private $token = 'list';
    private $arrTokens = array(
	'list',
	'edit',
	'add',
	'delete',
	'ajaximagedelete'
    );
    private $parts = array();
    private $objQaetopics = null;

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

	$body = new xhtml('body.admin.qaetopics.add');
	$body->load();
	$body->assign('title', 'QAE Topic: Add');
	if (count($_POST) > 0) {
	    $this->objQaetopics = new qaetopics();
	    if (($response = $this->objQaetopics->isValidCreate($_POST)) === true) {
		output::redirect(config::url('admin/qaetopics/list/'));
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
		$titleVal = (isset($_POST['title_' . $arrData['prefix']])) ? $_POST['title_' . $arrData['prefix']] : "";
		$primary_image_captionVal = (isset($_POST['primary_image_caption_' . $arrData['prefix']])) ? $_POST['primary_image_caption_' . $arrData['prefix']] : "";
		$secondary_image_captionVal = (isset($_POST['secondary_image_caption_' . $arrData['prefix']])) ? $_POST['secondary_image_caption_' . $arrData['prefix']] : "";
		$introductionVal = (isset($_POST['introduction_' . $arrData['prefix']])) ? $_POST['introduction_' . $arrData['prefix']] : "";

		$tabs_li[] = '<li><a href="#tab-' . $uid . '"><span>' . $arrData['prefix'] . '</span></a></li>';
		$lang_form.='<table>';
		$lang_form.='<tr>';
		$lang_form.='<td>';
		$lang_form.='Title';
		$lang_form.='</td>';
		$lang_form.='<td>';
		$lang_form.='<input type="text" name="title_' . $arrData['prefix'] . '" value="' . $titleVal . '" >';
		$lang_form.='</td>';
		$lang_form.='</tr>';
		$lang_form.='<tr>';
		$lang_form.='<td>';
		$lang_form.='Primary Image Path';
		$lang_form.='</td>';
		$lang_form.='<td>';
		$lang_form.='<input type="file" name="primary_image_path_' . $arrData['prefix'] . '" >';
		$lang_form.='</td>';
		$lang_form.='</tr>';
		$lang_form.='<tr>';
		$lang_form.='<td>';
		$lang_form.='Primary Image Caption';
		$lang_form.='</td>';
		$lang_form.='<td>';
		$lang_form.='<input type="text" name="primary_image_caption_' . $arrData['prefix'] . '" value="' . $primary_image_captionVal . '" >';
		$lang_form.='</td>';
		$lang_form.='</tr>';
		$lang_form.='<tr>';
		$lang_form.='<td>';
		$lang_form.='Secondary Image Path';
		$lang_form.='</td>';
		$lang_form.='<td>';
		$lang_form.='<input type="file" name="secondary_image_path_' . $arrData['prefix'] . '" >';
		$lang_form.='</td>';
		$lang_form.='</tr>';
		$lang_form.='<tr>';
		$lang_form.='<td>';
		$lang_form.='Secondary Image Caption';
		$lang_form.='</td>';
		$lang_form.='<td>';
		$lang_form.='<input type="text" name="secondary_image_caption_' . $arrData['prefix'] . '" value="' . $secondary_image_captionVal . '" >';
		$lang_form.='</td>';
		$lang_form.='</tr>';
		$lang_form.='<tr>';
		$lang_form.='<td>';
		$lang_form.='Introduction';
		$lang_form.='</td>';
		$lang_form.='<td>';
		$lang_form.='<textarea type="text" name="introduction_' . $arrData['prefix'] . '" >' . $introductionVal . '</textarea>';
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

	$body = new xhtml('body.admin.qaetopics.edit');
	$body->load();
	$body->assign('title', 'QAE Topic: Update');
	$qaetopic_uid = (isset($this->parts[3]) && (int) $this->parts[3] > 0) ? $this->parts[3] : '';

	if ($qaetopic_uid != '') {

	    $this->objQaetopics = new qaetopics($qaetopic_uid);
	    $this->objQaetopics->load();

	    $arrqaetopic = $this->objQaetopics->getFields();


	    if (count($arrqaetopic) > 0) {

		if (count($_POST) > 0) {
		    $_POST['qaetopic_uid'] = $qaetopic_uid;
		    if (($arrqaetopic = $this->objQaetopics->isValidUpdate($_POST)) === true) {
			output::redirect(config::url('admin/qaetopics/list/'));
		    } else {
			$body->assign($arrqaetopic);
		    }
		}

		$body->assign('topic_uid', $qaetopic_uid);
		$body->assign('qaetitle', $arrqaetopic['title']);
	    }
	} else {
	    output::redirect(config::url('admin/qaetopics/list/'));
	}

	$tabs_li = array();
	$tabs_div = array();

	$arrLocales = language::getPrefixes();
	$baseImageUrl=config::base().'images/qae/';
	if (count($arrLocales) > 0) {
	    foreach ($arrLocales as $uid => $arrData) {
		$topicData = $this->objQaetopics->getListByLocale($qaetopic_uid, $uid);
		$lang_form = "";

		$titleVal = (isset($_POST['title_' . $arrData['prefix']])) ? $_POST['title_' . $arrData['prefix']] : $topicData["title"];
		$primary_image_captionVal = (isset($_POST['primary_image_caption_' . $arrData['prefix']])) ? $_POST['primary_image_caption_' . $arrData['prefix']] : $topicData["primary_image_caption"];
		$secondary_image_captionVal = (isset($_POST['secondary_image_caption_' . $arrData['prefix']])) ? $_POST['secondary_image_caption_' . $arrData['prefix']] : $topicData["secondary_image_caption"];
		$introductionVal = (isset($_POST['introduction_' . $arrData['prefix']])) ? $_POST['introduction_' . $arrData['prefix']] : $topicData["introduction"];

		$tabs_li[] = '<li><a href="#tab-' . $uid . '"><span>' . $arrData['prefix'] . '</span></a></li>';
		$lang_form.='<table>';
		$lang_form.='<tr>';
		$lang_form.='<td>';
		$lang_form.='Title';
		$lang_form.='</td>';
		$lang_form.='<td>';
		$lang_form.='<input type="text" name="title_' . $arrData['prefix'] . '" value="' . $titleVal . '" >';
		$lang_form.='</td>';
		$lang_form.='</tr>';
		$lang_form.='<tr>';
		$lang_form.='<td>';
		$lang_form.='Primary Image Path';
		$lang_form.='</td>';
		$lang_form.='<td>';
		$lang_form.='<input type="file" name="primary_image_path_' . $arrData['prefix'] . '" >';
		if(!empty($topicData["primary_image_path"])){
		    $lang_form.='&nbsp;<a id="view_image_1'.$arrData['prefix'].'" href="'.$baseImageUrl.$arrData['prefix']."/".$topicData["primary_image_path"].'" class="view_image">View Image</a>';
		    $lang_form.='&nbsp;<a id="delete_image_1'.$arrData['prefix'].'" href="javascript:;" onclick="imageDelete(\''.$arrData['prefix'].'\',\''.$topicData["uid"].'\',\'1\')" >Delete Image</a>';
		}
		$lang_form.='<input type="hidden" name="old_primary_image_path_' . $arrData['prefix'] . '" value="' . $topicData["primary_image_path"] . '" >';
		$lang_form.='</td>';
		$lang_form.='</tr>';
		$lang_form.='<tr>';
		$lang_form.='<td>';
		$lang_form.='Primary Image Caption';
		$lang_form.='</td>';
		$lang_form.='<td>';
		$lang_form.='<input type="text" name="primary_image_caption_' . $arrData['prefix'] . '"  value="' . $primary_image_captionVal . '" >';
		$lang_form.='</td>';
		$lang_form.='</tr>';
		$lang_form.='<tr>';
		$lang_form.='<td>';
		$lang_form.='Secondary Image Path';
		$lang_form.='</td>';
		$lang_form.='<td>';
		$lang_form.='<input type="file" name="secondary_image_path_' . $arrData['prefix'] . '" >';
		if(!empty($topicData["secondary_image_path"])){
		    $lang_form.='&nbsp;<a id="view_image_2'.$arrData['prefix'].'" href="'.$baseImageUrl.$arrData['prefix']."/".$topicData["secondary_image_path"].'" class="view_image">view image</a>';
		    $lang_form.='&nbsp;<a id="delete_image_2'.$arrData['prefix'].'" href="javascript:;" onclick="imageDelete(\''.$arrData['prefix'].'\',\''.$topicData["uid"].'\',\'2\')" >Delete Image</a>';
		}
		$lang_form.='<input type="hidden" name="old_secondary_image_path_' . $arrData['prefix'] . '" value="' . $topicData["secondary_image_path"] . '" >';
		$lang_form.='</td>';
		$lang_form.='</tr>';
		$lang_form.='<tr>';
		$lang_form.='<td>';
		$lang_form.='Secondary Image Caption';
		$lang_form.='</td>';
		$lang_form.='<td>';
		$lang_form.='<input type="text" name="secondary_image_caption_' . $arrData['prefix'] . '" value="' . $secondary_image_captionVal . '" >';
		$lang_form.='</td>';
		$lang_form.='</tr>';
		$lang_form.='<tr>';
		$lang_form.='<td>';
		$lang_form.='Introduction';
		$lang_form.='</td>';
		$lang_form.='<td>';
		$lang_form.='<textarea type="text" name="introduction_' . $arrData['prefix'] . '" >' . $introductionVal . '</textarea>';
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

    protected function doDelete() {

	if (isset($this->parts[3]) && (int) $this->parts[3] > 0) {
	    $objQaetopics = new qaetopics($this->parts[3]);
	    // delete translation image
	    $sql = "SELECT ";
	    $sql.=" locale,";
	    $sql.=" primary_image_path,";
	    $sql.=" secondary_image_path ";
	    $sql.=" FROM `qae_topic_translation`";
	    $sql.=" WHERE ";
	    $sql.=" qaetopic_uid='{$this->parts[3]}'";
	    $res = database::query($sql);
	    while ($translation = mysql_fetch_assoc($res)) {
		$imgPath = config::get('site') . '/images/qae/' . $translation['locale'] . "/";
		@unlink($imgPath . $translation['primary_image_path']);
		@unlink($imgPath . 'thumb_' . $translation['primary_image_path']);
		@unlink($imgPath . $translation['secondary_image_path']);
		@unlink($imgPath . 'thumb_' . $translation['secondary_image_path']);
	    }
	    // delete translation image
	    $sql = "DELETE FROM `qae_topic_translation`";
	    $sql.=" WHERE ";
	    $sql.=" qaetopic_uid='{$this->parts[3]}'";
	    $res = database::query($sql);

	    $objQaetopics->delete();

	    $objQaetopics->redirectTo('admin/qaetopics/list/');
	} else {
	    output::redirect(config::url('admin/qaetopics/list/'));
	}
    }

    protected function doList() {
	$skeleton = new xhtml('skeleton.admin');
	$skeleton->load();
	$hide = 'style="visibility:hidden;"';
	$body = new xhtml('body.admin.qaetopics.list');
	$body->load();

	$this->objQaetopics = new qaetopics();
	$arrqaetopics = $this->objQaetopics->getListByTitle();
	$i = 0;
	if ($arrqaetopics && count($arrqaetopics) > 0) {
	    $rows = array();
	    foreach ($arrqaetopics as $qaetopic_uid => $arrData) {
		$i++;

		$row = new xhtml('body.admin.qaetopics.list.row');
		$row->load();
		$row->assign(array(
		    'qaetopic_uid' => $qaetopic_uid,
		    'title' => stripslashes($arrData['title'])
		));
		$rows[] = $row->get_content();
	    }
	    $body->assign('rows', implode('', $rows));
	}
	$page_display_title = $this->objQaetopics->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
	$page_navigation = $this->objQaetopics->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>') . $this->objQaetopics->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>', ' &raquo ') . $this->objQaetopics->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');
	$body->assign('page.display.title', $page_display_title);
	$body->assign('page.navigation', $page_navigation);
	$skeleton->assign(
		array(
		    'body' => $body
		)
	);

	output::as_html($skeleton, true);
    }

    protected function doAjaximagedelete(){
	$locale=$this->parts[3];
	$uid=$this->parts[4];
	$imageId=$this->parts[5];

	$sql="SELECT primary_image_path,secondary_image_path";
	$sql.=" FROM ";
	$sql.=" `qae_topic_translation` ";
	$sql.=" WHERE ";
	$sql.=" uid='{$uid}' ";
	$res=database::query($sql);
	$topicImages=mysql_fetch_assoc($res);
	$filename=($imageId=="1")?$topicImages["primary_image_path"]:$topicImages["secondary_image_path"];

	$delete=0;
	$imgPath = config::get('site') . '/images/qae/' . $locale . "/";


	if(file_exists($imgPath . $filename)){
	    unlink($imgPath . $filename);
	    $delete++;
	}
	if(file_exists($imgPath . 'thumb_' . $filename)){
	    unlink($imgPath . 'thumb_' . $filename);
	    $delete++;
	}
	if($delete>0){
	    $sql="UPDATE `qae_topic_translation` SET";
	    $sql.=($imageId=="1")?" primary_image_path='' ":" secondary_image_path='' ";
	    $sql.=" WHERE";
	    $sql.=" uid='{$uid}' ";
	    database::query($sql);
	}
	echo $delete;
    }
}

?>