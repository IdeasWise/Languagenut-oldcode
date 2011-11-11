<?php

class admin_speaking_and_listening extends Controller {

	private $token = 'edit';
	private $arrTokens = array(
//		'list',
		'edit',
//		'add',
//		'delete',
	);
	private $parts = array();
	private $objspeaking_and_listening = null;

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

		$body = new xhtml('body.admin.speaking_and_listening.add-edit');
		$body->load();
		$body->assign('activity_uid', $this->parts[3]);

		$objActivity = new activity($this->parts[3]);
		$objActivity->load();
		$body->assign('activity_name', $objActivity->get_name());

		if (count($_POST) > 0) {
			$this->objspeaking_and_listening = new speaking_and_listening();
			$_POST["activity_uid"] = $this->parts[3];
			if (($response = $this->objspeaking_and_listening->isValidCreate($_POST)) === true) {
				output::redirect(config::url('admin/speaking_and_listening/list/' . $this->parts[3] . '/'));
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
				$tabs_li[] = '<li><a href="#tab-' . $uid . '"><span>' . $arrData['prefix'] . '</span></a></li>';
				$lang_form.='<table>';

				$lang_form.='<tr>';
				$lang_form.='<td>';
				$lang_form.='Audio File Path:';
				$lang_form.='</td>';
				$lang_form.='<td>';
				$lang_form.='<input type="text" name="audio_file_path_' . $arrData['prefix'] . '" value="" class="box">';
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

		$body = new xhtml('body.admin.speaking_and_listening.add-edit');
		$body->load();

		$activityUid = $this->parts[3];
		$body->assign('activity_uid', $activityUid);

		$objActivity = new activity($activityUid);
		$objActivity->load();
		$body->assign('activity_name', $objActivity->get_name());

		$objspeaking_and_listening = new speaking_and_listening();

		$speaking_and_listening_data = $objspeaking_and_listening->getListByActivity($activityUid);
		$speaking_and_listening_uid = ($speaking_and_listening_data) ? $speaking_and_listening_data["uid"] : 0;
		$body->assign('uid', $speaking_and_listening_uid);

		$this->objspeaking_and_listening = null;


		$this->objspeaking_and_listening = new speaking_and_listening($speaking_and_listening_uid);
		$this->objspeaking_and_listening->load();

		$arrqaetopic = $this->objspeaking_and_listening->getFields();
		$body->assign($arrqaetopic);

		if (count($arrqaetopic) > 0) {
			if (count($_POST) > 0) {
				$_POST['speaking_and_listening_uid'] = $speaking_and_listening_uid;
				$_POST['activity_uid'] = $activityUid;
				if (($arrqaetopic = $this->objspeaking_and_listening->insertOrUpdate($_POST)) === true) {
					output::redirect(config::url('admin/activity/list/'));
				} else {
					$body->assign($arrqaetopic);
				}
			}
		}


		$tabs_li = array();
		$tabs_div = array();
		$arrLocales = language::getPrefixes();
		if (count($arrLocales) > 0) {
			foreach ($arrLocales as $uid => $arrData) {
				$translationData = $this->objspeaking_and_listening->getListByLocale($speaking_and_listening_uid, $uid);
				$audio_file_path = (isset($translationData["audio_file_path"])) ? $translationData["audio_file_path"] : '';
				$title = (isset($translationData["title"])) ? $translationData["title"] : '';
				$lang_form = "";
				$tabs_li[] = '<li><a href="#tab-' . $uid . '"><span>' . $arrData['prefix'] . '</span></a></li>';
				$lang_form.='<table>';

				$lang_form.='<tr>';
				$lang_form.='<td>';
				$lang_form.='Title:';
				$lang_form.='</td>';
				$lang_form.='<td>';
				$lang_form.='<input type="text" name="title_' . $arrData['prefix'] . '" value="' . $title . '" class="box">';
				$lang_form.='</td>';
				$lang_form.='</tr>';

				$lang_form.='<tr>';
				$lang_form.='<td>';
				$lang_form.='Audio File Path:';
				$lang_form.='</td>';
				$lang_form.='<td>';
				$lang_form.='<input type="text" name="audio_file_path_' . $arrData['prefix'] . '" value="' . $audio_file_path . '" class="box">';
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

		$body->assign('activity_uid', $activityUid);

		$skeleton->assign(
				array(
					'body' => $body
				)
		);
		output::as_html($skeleton, true);
	}

	protected function doList() {
		$skeleton = new xhtml('skeleton.admin');
		$skeleton->load();

		$body = new xhtml('body.admin.speaking_and_listening.list');
		$body->load();

		$activityUid = $this->parts[3];

		$objActivity = new activity($activityUid);
		$objActivity->load();
		$body->assign('activity_name', $objActivity->get_name());

		$this->objspeaking_and_listening = new speaking_and_listening ();
		$arrExerciseList = $this->objspeaking_and_listening->getListByActivity($activityUid);

		$i = 0;
		if ($arrExerciseList && count($arrExerciseList) > 0) {
			$rows = array();
			foreach ($arrExerciseList as $arrData) {
				$i++;
				$row = new xhtml('body.admin.speaking_and_listening.list.row');
				$row->load();
				$row->assign(array(
					'uid' => $arrData['uid'],
					'title' => stripslashes($arrData['title']),
					'activity_uid' => $activityUid
				));

				$rows[] = $row->get_content();
			}
			$body->assign('rows', implode('', $rows));
		}
		$page_display_name = $this->objspeaking_and_listening->get_page_name('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
		$page_navigation = $this->objspeaking_and_listening->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>') . $this->objspeaking_and_listening->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>', ' &raquo ') . $this->objspeaking_and_listening->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');
		$body->assign('page.display.name', $page_display_name);
		$body->assign('page.navigation', $page_navigation);

		$body->assign('activity_uid', $activityUid);

		$skeleton->assign(
				array(
					'body' => $body
				)
		);

		output::as_html($skeleton, true);
	}

	protected function doDelete() {
		$activityUid = $this->parts[4];


		if (isset($this->parts[3]) && (int) $this->parts[3] > 0) {
			$this->objspeaking_and_listening = new speaking_and_listening($this->parts[3]);

			$this->objspeaking_and_listening->softDelete($this->parts[3]);
//			$sql = "DELETE FROM `speaking_and_listening_translation`";
//			$sql.=" WHERE ";
//			$sql.=" speaking_and_listening_uid='{$this->parts[3]}'";
//			$res = database::query($sql);
//
//			$this->objspeaking_and_listening->delete();

			output::redirect(config::url('admin/speaking_and_listening/list/' . $activityUid . '/'));
		} else {
			output::redirect(config::url('admin/speaking_and_listening/list/' . $activityUid . '/'));
		}
	}

}

?>