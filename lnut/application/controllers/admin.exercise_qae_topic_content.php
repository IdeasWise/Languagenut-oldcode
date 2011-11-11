<?php

class admin_exercise_qae_topic_content extends Controller {

	private $token = 'list';
	private $arrTokens = array(
		'list',
		'edit',
		'add',
		'delete',
	);
	private $parts = array();
	private $objexercise_qae_topic_content = null;

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

		$body = new xhtml('body.admin.exercise_qae_topic_content.add');
		$body->load();
		$body->assign('exercise_qae_topic_uid', $this->parts[3]);
		
		$activityUid=$this->parts[4];
		$objActivity=new activity($activityUid);
		$objActivity->load();		
		$body->assign('activity_name',$objActivity->get_name());
		$body->assign('activity_uid',$activityUid);
		

		if (count($_POST) > 0) {
			$this->objexercise_qae_topic_content = new exercise_qae_topic_content();
			if (($response = $this->objexercise_qae_topic_content->isValidCreate($_POST)) === true) {
				output::redirect(config::url('admin/exercise_qae_topic_content/list/' . $this->parts[3] . '/'.$activityUid.'/'));
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
				$lang_form.='Content:';
				$lang_form.='</td>';
				$lang_form.='<td>';
				$lang_form.='<textarea name="content_' . $arrData['prefix'] . '" class="box"></textarea>';
				$lang_form.='</td>';
				$lang_form.='</tr>';

				$lang_form.='<tr>';
				$lang_form.='<td>';
				$lang_form.='Available:';
				$lang_form.='</td>';
				$lang_form.='<td>';
				$lang_form.='<select name="available_' . $arrData['prefix'] . '" >
								<option value="1" >Yes</option>
								<option value="0" >No</option>
							</select>';
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

		$body = new xhtml('body.admin.exercise_qae_topic_content.edit');
		$body->load();
		$body->assign('exercise_qae_topic_uid', $this->parts[4]);
		
		$activityUid=$this->parts[5];
		$objActivity=new activity($activityUid);
		$objActivity->load();		
		$body->assign('activity_name',$objActivity->get_name());
		$body->assign('activity_uid',$activityUid);
		
		
		$exercise_qae_topic_content_uid = (isset($this->parts[3]) && (int) $this->parts[3] > 0) ? $this->parts[3] : '';

		$body->assign('uid', $exercise_qae_topic_content_uid);

		$this->objexercise_qae_topic_content = null;

		if ($exercise_qae_topic_content_uid != '') {

			$this->objexercise_qae_topic_content = new exercise_qae_topic_content($exercise_qae_topic_content_uid);
			$this->objexercise_qae_topic_content->load();

			$arrqaetopic = $this->objexercise_qae_topic_content->getFields();

			$selected1 = ($arrqaetopic["available"] == '1') ? 'selected="selected"' : '';
			$selected0 = ($arrqaetopic["available"] == '0') ? 'selected="selected"' : '';
			$body->assign('available_select_1', $selected1);
			$body->assign('available_select_0', $selected0);
			$body->assign($arrqaetopic);

			if (count($arrqaetopic) > 0) {
				if (count($_POST) > 0) {
					$_POST['exercise_qae_topic_content_uid'] = $exercise_qae_topic_content_uid;
					if (($arrqaetopic = $this->objexercise_qae_topic_content->isValidUpdate($_POST)) === true) {
						output::redirect(config::url('admin/exercise_qae_topic_content/list/'.$_POST['exercise_qae_topic_uid'].'/'.$activityUid.'/'));
					} else {
						$body->assign($arrqaetopic);
					}
				}
			}
		} else {
			output::redirect(config::url('admin/exercise_qae_topic_content/list/'.$activityUid.'/'));
		}

		$tabs_li = array();
		$tabs_div = array();
		$arrLocales = language::getPrefixes();
		if (count($arrLocales) > 0) {
			foreach ($arrLocales as $uid => $arrData) {
				$activityData = $this->objexercise_qae_topic_content->getListByLocale($exercise_qae_topic_content_uid, $uid);

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
				$lang_form.='Content:';
				$lang_form.='</td>';
				$lang_form.='<td>';
				$lang_form.='<textarea name="content_' . $arrData['prefix'] . '" class="box">' . $activityData["content"] . '</textarea>';
				$lang_form.='</td>';
				$lang_form.='</tr>';

				$lang_form.='<tr>';
				$lang_form.='<td>';
				$lang_form.='Available:';
				$lang_form.='</td>';
				$lang_form.='<td>';
				$selected1 = ($activityData["available"] == '1') ? 'selected="selected"' : '';
				$selected0 = ($activityData["available"] == '0') ? 'selected="selected"' : '';
				$lang_form.='<select name="available_' . $arrData['prefix'] . '" >
								<option value="1" ' . $selected1 . ' >Yes</option>
								<option value="0" ' . $selected0 . '>No</option>
							</select>';
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

	protected function doList() {
		$skeleton = new xhtml('skeleton.admin');
		$skeleton->load();

		$body = new xhtml('body.admin.exercise_qae_topic_content.list');
		$body->load();
		$body->assign('exercise_qae_topic_uid', $this->parts[3]);
		
		$activityUid=$this->parts[4];
		$objActivity=new activity($activityUid);
		$objActivity->load();		
		$body->assign('activity_name',$objActivity->get_name());
		$body->assign('activity_uid',$activityUid);
		
		
		$this->objexercise_qae_topic_content = new exercise_qae_topic_content($this->parts[3]);
		$arrExerciseList = $this->objexercise_qae_topic_content->getListByname($this->parts[3]);

		$i = 0;
		if ($arrExerciseList && count($arrExerciseList) > 0) {
			$rows = array();
			foreach ($arrExerciseList as $arrData) {
				$i++;
				$row = new xhtml('body.admin.exercise_qae_topic_content.list.row');
				$row->load();
				$row->assign(array(
					'uid' => $arrData['uid'],
					'exercise_qae_topic_uid' => $this->parts[3],
					'activity_uid' => $this->parts[4],
					'available' => ($arrData['available'] == '0') ? 'No' : 'Yes',
					'title' => stripslashes($arrData['title'])
				));

				$rows[] = $row->get_content();
			}
			$body->assign('rows', implode('', $rows));
		}
		$page_display_name = $this->objexercise_qae_topic_content->get_page_name('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
		$page_navigation = $this->objexercise_qae_topic_content->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>') . $this->objexercise_qae_topic_content->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>', ' &raquo ') . $this->objexercise_qae_topic_content->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');
		$body->assign('page.display.name', $page_display_name);
		$body->assign('page.navigation', $page_navigation);
		$skeleton->assign(
				array(
					'body' => $body
				)
		);

		output::as_html($skeleton, true);
	}

	protected function doDelete() {
		if (isset($this->parts[3]) && (int) $this->parts[3] > 0) {
			$this->objexercise_qae_topic_content = new exercise_qae_topic_content($this->parts[3]);
			
			$this->objexercise_qae_topic_content->softDelete($this->parts[3]);
//			$sql = "DELETE FROM `exercise_qae_topic_content_translation`";
//			$sql.=" WHERE ";
//			$sql.=" exercise_qae_topic_content_uid='{$this->parts[3]}'";
//			$res = database::query($sql);
//
//			$this->objexercise_qae_topic_content->delete();

			$this->objexercise_qae_topic_content->redirectTo('admin/exercise_qae_topic_content/list/'.$this->parts[4].'/'.$this->parts[5].'/');
		} else {
			output::redirect(config::url('admin/exercise_qae_topic_content/list/'.$this->parts[4].'/'.$this->parts[5].'/'));
		}
	}

}

?>