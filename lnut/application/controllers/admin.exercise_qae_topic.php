<?php

class admin_exercise_qae_topic extends Controller {

	private $token = 'list';
	private $arrTokens = array(
		'list',
		'edit',
		'add',
		'delete',
	);
	private $parts = array();
	private $objexercise_qae_topic = null;

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

		$body = new xhtml('body.admin.exercise_qae_topic.add');
		$body->load();
		$body->assign('activity_uid', $this->parts[3]);
		
		$objActivity=new activity($this->parts[3]);
		$objActivity->load();		
		$body->assign('activity_name',$objActivity->get_name());
		
		if (count($_POST) > 0) {
			$this->objexercise_qae_topic = new exercise_qae_topic();
			$_POST["activity_uid"] = $this->parts[3];
			if (($response = $this->objexercise_qae_topic->isValidCreate($_POST)) === true) {
				output::redirect(config::url('admin/exercise_qae_topic/list/' . $this->parts[3] . '/'));
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
				$lang_form.='Primary Image Path:';
				$lang_form.='</td>';
				$lang_form.='<td>';
				$lang_form.='<input type="text" name="primary_image_path_' . $arrData['prefix'] . '" value="" class="box">';
				$lang_form.='</td>';
				$lang_form.='</tr>';

				$lang_form.='<tr>';
				$lang_form.='<td>';
				$lang_form.='Primary Image Caption:';
				$lang_form.='</td>';
				$lang_form.='<td>';
				$lang_form.='<input type="text" name="primary_image_caption_' . $arrData['prefix'] . '" value="" class="box">';
				$lang_form.='</td>';
				$lang_form.='</tr>';

				$lang_form.='<tr>';
				$lang_form.='<td>';
				$lang_form.='Secondary Image Path:';
				$lang_form.='</td>';
				$lang_form.='<td>';
				$lang_form.='<input type="text" name="secondary_image_path_' . $arrData['prefix'] . '" value="" class="box">';
				$lang_form.='</td>';
				$lang_form.='</tr>';

				$lang_form.='<tr>';
				$lang_form.='<td>';
				$lang_form.='Secondary Image Caption:';
				$lang_form.='</td>';
				$lang_form.='<td>';
				$lang_form.='<input type="text" name="secondary_image_caption_' . $arrData['prefix'] . '" value="" class="box">';
				$lang_form.='</td>';
				$lang_form.='</tr>';

				$lang_form.='<tr>';
				$lang_form.='<td>';
				$lang_form.='Title:';
				$lang_form.='</td>';
				$lang_form.='<td>';
				$lang_form.='<input type="text" name="title_' . $arrData['prefix'] . '" value="" class="box">';
				$lang_form.='</td>';
				$lang_form.='</tr>';

				$lang_form.='<tr>';
				$lang_form.='<td>';
				$lang_form.='Introduction:';
				$lang_form.='</td>';
				$lang_form.='<td>';
				$lang_form.='<textarea name="introduction_' . $arrData['prefix'] . '" class="box"></textarea>';
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

		$body = new xhtml('body.admin.exercise_qae_topic.edit');
		$body->load();

		$activityUid = $this->parts[4];
		$body->assign('activity_uid', $activityUid);
		
		$objActivity=new activity($activityUid);
		$objActivity->load();		
		$body->assign('activity_name',$objActivity->get_name());

		$exercise_qae_topic_uid = (isset($this->parts[3]) && (int) $this->parts[3] > 0) ? $this->parts[3] : '';
		$body->assign('uid', $exercise_qae_topic_uid);

		$this->objexercise_qae_topic = null;

		if ($exercise_qae_topic_uid != '') {

			$this->objexercise_qae_topic = new exercise_qae_topic($exercise_qae_topic_uid);
			$this->objexercise_qae_topic->load();

			$arrqaetopic = $this->objexercise_qae_topic->getFields();
			$body->assign($arrqaetopic);

			if (count($arrqaetopic) > 0) {
				if (count($_POST) > 0) {
					$_POST['exercise_qae_topic_uid'] = $exercise_qae_topic_uid;
					if (($arrqaetopic = $this->objexercise_qae_topic->isValidUpdate($_POST)) === true) {
						output::redirect(config::url('admin/exercise_qae_topic/list/' . $activityUid . '/'));
					} else {
						$body->assign($arrqaetopic);
					}
				}
			}
		} else {
			output::redirect(config::url('admin/exercise_qae_topic/list/' . $activityUid . '/'));
		}

		$tabs_li = array();
		$tabs_div = array();
		$arrLocales = language::getPrefixes();
		if (count($arrLocales) > 0) {
			foreach ($arrLocales as $uid => $arrData) {
				$activityData = $this->objexercise_qae_topic->getListByLocale($exercise_qae_topic_uid, $uid);

				$lang_form = "";
				$tabs_li[] = '<li><a href="#tab-' . $uid . '"><span>' . $arrData['prefix'] . '</span></a></li>';
				$lang_form.='<table>';
				$lang_form.='<tr>';
				$lang_form.='<td>';
				$lang_form.='Primary Image Path:';
				$lang_form.='</td>';
				$lang_form.='<td>';
				$lang_form.='<input type="text" name="primary_image_path_' . $arrData['prefix'] . '" value="' . $activityData["primary_image_path"] . '" class="box">';
				$lang_form.='</td>';
				$lang_form.='</tr>';

				$lang_form.='<tr>';
				$lang_form.='<td>';
				$lang_form.='Primary Image Caption:';
				$lang_form.='</td>';
				$lang_form.='<td>';
				$lang_form.='<input type="text" name="primary_image_caption_' . $arrData['prefix'] . '" value="' . $activityData["primary_image_caption"] . '" class="box">';
				$lang_form.='</td>';
				$lang_form.='</tr>';

				$lang_form.='<tr>';
				$lang_form.='<td>';
				$lang_form.='Secondary Image Path:';
				$lang_form.='</td>';
				$lang_form.='<td>';
				$lang_form.='<input type="text" name="secondary_image_path_' . $arrData['prefix'] . '" value="' . $activityData["secondary_image_path"] . '" class="box">';
				$lang_form.='</td>';
				$lang_form.='</tr>';

				$lang_form.='<tr>';
				$lang_form.='<td>';
				$lang_form.='Secondary Image Caption:';
				$lang_form.='</td>';
				$lang_form.='<td>';
				$lang_form.='<input type="text" name="secondary_image_caption_' . $arrData['prefix'] . '" value="' . $activityData["secondary_image_caption"] . '" class="box">';
				$lang_form.='</td>';
				$lang_form.='</tr>';

				$lang_form.='<tr>';
				$lang_form.='<td>';
				$lang_form.='Title:';
				$lang_form.='</td>';
				$lang_form.='<td>';
				$lang_form.='<input type="text" name="title_' . $arrData['prefix'] . '" value="' . $activityData["title"] . '" class="box">';
				$lang_form.='</td>';
				$lang_form.='</tr>';

				$lang_form.='<tr>';
				$lang_form.='<td>';
				$lang_form.='Introduction:';
				$lang_form.='</td>';
				$lang_form.='<td>';
				$lang_form.='<textarea name="introduction_' . $arrData['prefix'] . '" class="box">' . $activityData["introduction"] . '</textarea>';
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
		
		$body = new xhtml('body.admin.exercise_qae_topic.list');
		$body->load();

		$activityUid = $this->parts[3];

		$objActivity=new activity($activityUid);
		$objActivity->load();		
		$body->assign('activity_name',$objActivity->get_name());
		
		$this->objexercise_qae_topic = new exercise_qae_topic ();
		$arrExerciseList = $this->objexercise_qae_topic->getListByActivity($activityUid);

		$i = 0;
		if ($arrExerciseList && count($arrExerciseList) > 0) {
			$rows = array();
			foreach ($arrExerciseList as $arrData) {
				$i++;
				$row = new xhtml('body.admin.exercise_qae_topic.list.row');
				$row->load();
				$row->assign(array(
					'uid' => $arrData['uid'],
					'title' => stripslashes($arrData['title']),
					'activity_uid'=> $activityUid
				));

				$rows[] = $row->get_content();
			}
			$body->assign('rows', implode('', $rows));
		}
		$page_display_name = $this->objexercise_qae_topic->get_page_name('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
		$page_navigation = $this->objexercise_qae_topic->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>') . $this->objexercise_qae_topic->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>', ' &raquo ') . $this->objexercise_qae_topic->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');
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
			$this->objexercise_qae_topic = new exercise_qae_topic($this->parts[3]);

			$this->objexercise_qae_topic->softDelete($this->parts[3]);
//			$sql = "DELETE FROM `exercise_qae_topic_translation`";
//			$sql.=" WHERE ";
//			$sql.=" exercise_qae_topic_uid='{$this->parts[3]}'";
//			$res = database::query($sql);
//
//			$this->objexercise_qae_topic->delete();

			output::redirect(config::url('admin/exercise_qae_topic/list/'.$activityUid.'/'));
		} else {
			output::redirect(config::url('admin/exercise_qae_topic/list/'.$activityUid.'/'));
		}
	}

}

?>