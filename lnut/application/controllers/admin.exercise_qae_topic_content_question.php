<?php

class admin_exercise_qae_topic_content_question extends Controller {

	private $token = 'list';
	private $arrTokens = array(
		'list',
		'edit',
		'add',
		'delete',
		'optiondelete'
	);
	private $parts = array();
	private $objexercise_qae_topic_content_question = null;

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

	protected function doOptiondelete() {
		$uid = $this->parts[3];
		$objExerciseQaeTopicContentQuestionOption = new exercise_qae_topic_content_question_option($uid);
		$result = $objExerciseQaeTopicContentQuestionOption->deleteOption($uid);
		output::redirect(config::url('admin/exercise_qae_topic_content_question/edit/' . $this->parts[4] . '/' . $this->parts[5] . '/'.$this->parts[6].'/'.$this->parts[7].'/'));
	}

	protected function doAdd() {
		$skeleton = new xhtml('skeleton.admin');
		$skeleton->load();

		$body = new xhtml('body.admin.exercise_qae_topic_content_question.add');
		$body->load();
		$body->assign("exercise_qae_topic_content_uid", $this->parts[3]);
		$body->assign("exercise_qae_topic_uid", $this->parts[5]);
		
		$activityUid=$this->parts[4];
		$objActivity=new activity($activityUid);
		$objActivity->load();		
		$body->assign('activity_name',$objActivity->get_name());
		$body->assign('activity_uid',$activityUid);
		
		// difficulty level dropdown
		$difficulty_uids = array("" => "Please Select");
		$difficultylevels = array();
		$difficultylevels = difficultylevel::getAllDifficulties();
		if (!empty($difficultylevels)) {
			foreach ($difficultylevels as $uid => $data) {
				$difficulty_uids[$data['uid']] = $data['name'];
			}
		}

		if (count($_POST) > 0) {
			$this->objexercise_qae_topic_content_question = new exercise_qae_topic_content_question();
			$_POST["exercise_qae_topic_content_uid"] = $this->parts[3];
			if (($response = $this->objexercise_qae_topic_content_question->isValidCreate($_POST)) === true) {
				output::redirect(config::url('admin/exercise_qae_topic_content_question/list/' . $this->parts[3] . '/'.$this->parts[4].'/'.$this->parts[5].'/'));
			} else {
				$body->assign($response);
			}
		}

		$tabs_li = array();
		$tabs_div = array();
		$arrLocales = language::getPrefixes();
		if (count($arrLocales) > 0) {
			foreach ($arrLocales as $uid => $arrData) {

				$tabs_li[] = '<li><a href="#tab-' . $uid . '"><span>' . $arrData['prefix'] . '</span></a></li>';

				$options = '<tr><td><input type="text" name="answer_text_' . $arrData['prefix'] . '[]" value="" /></td><td><input type="text" name="text_if_wrong_' . $arrData['prefix'] . '[]" value="" /></td><td><input type="text" name="audio_file_' . $arrData['prefix'] . '[]" value="" /></td><td><input type="radio" name="correct_answer_' . $arrData['prefix'] . '" id="correct_answer_' . $arrData['prefix'] . '" checked="checked" value="0" /></td><td><a href="javascript:;" onclick="remove_qae_question(this,\'' . $arrData['prefix'] . '\')">Remove</a></td></tr>';
				$lang_form = new xhtml('body.admin.exercise_qae_topic_content_question.tabs');
				$lang_form->load();
				$lang_form->assign("options", $options);
				$lang_form->assign("locale", $arrData['prefix']);

				$tabs_div[] = '<div id="tab-' . $uid . '">' . $lang_form->get_content() . '</div>';
			}
		}

		$body->assign(
				array(
					'tabs' => implode('', $tabs_div),
					'difficulty_uid' => format::to_select(array("name" => "difficulty_level_uid", "id" => "difficulty_level_uid", "options_only" => false), $difficulty_uids),
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

		$body = new xhtml('body.admin.exercise_qae_topic_content_question.edit');
		$body->load();

		$body->assign("exercise_qae_topic_content_uid", $this->parts[4]);
		$body->assign("exercise_qae_topic_uid", $this->parts[6]);
		
		$activityUid=$this->parts[5];
		$objActivity=new activity($activityUid);
		$objActivity->load();		
		$body->assign('activity_name',$objActivity->get_name());
		$body->assign('activity_uid',$activityUid);
		
		$exercise_qae_topic_content_question_uid = (isset($this->parts[3]) && (int) $this->parts[3] > 0) ? $this->parts[3] : '';
		$body->assign('uid', $exercise_qae_topic_content_question_uid);

		// difficulty level dropdown
		$difficulty_uids = array("" => "Please Select");
		$difficultylevels = array();
		$difficultylevels = difficultylevel::getAllDifficulties();
		if (!empty($difficultylevels)) {
			foreach ($difficultylevels as $uid => $data) {
				$difficulty_uids[$data['uid']] = $data['name'];
			}
		}

		$this->objexercise_qae_topic_content_question = null;

		if ($exercise_qae_topic_content_question_uid != '') {

			$this->objexercise_qae_topic_content_question = new exercise_qae_topic_content_question($exercise_qae_topic_content_question_uid);
			$this->objexercise_qae_topic_content_question->load();

			$arrqaetopic = $this->objexercise_qae_topic_content_question->getFields();


			if (count($arrqaetopic) > 0) {
				if (count($_POST) > 0) {
					$_POST["exercise_qae_topic_content_uid"] = $this->parts[4];
					$_POST['exercise_qae_topic_content_question_uid'] = $exercise_qae_topic_content_question_uid;
					if (($arrqaetopic1 = $this->objexercise_qae_topic_content_question->isValidUpdate($_POST)) === true) {
						output::redirect(config::url('admin/exercise_qae_topic_content_question/list/' . $this->parts[4] . '/'.$this->parts[5].'/'.$this->parts[6].'/'));
					} else {
						foreach ($arrqaetopic1 as $key => $value) {

							$arrqaetopic[$key] = $arrqaetopic1[$key];
						}

						$body->assign($arrqaetopic);
					}
				}
			}
		} else {
			output::redirect(config::url('admin/exercise_qae_topic_content_question/list/' . $this->parts[4] . '/'.$this->parts[5].'/'.$this->parts[6].'/'));
		}

		$body->assign($arrqaetopic);

		$tabs_li = array();
		$tabs_div = array();
		$arrLocales = language::getPrefixes();
		if (count($arrLocales) > 0) {
			foreach ($arrLocales as $uid => $arrData) {
				$objExerciseQaeTopicContentQuestionOption = new exercise_qae_topic_content_question_option();
				$optionValues = $objExerciseQaeTopicContentQuestionOption->getListByLocale($this->parts[3], $uid);


				$questionTranslation = $this->objexercise_qae_topic_content_question->getListByLocale($this->parts[3], $uid);

				$tabs_li[] = '<li><a href="#tab-' . $uid . '"><span>' . $arrData['prefix'] . '</span></a></li>';
				$options = "";
				if (count($optionValues) > 0) {
					$oCnt = 0;
					foreach ($optionValues as $optionRow) {
						$checked = ($arrqaetopic["correct_option_uid"] == $optionRow["exercise_qae_topic_content_question_option_uid"]) ? 'checked="checked"' : '';
						$options .= '<tr><td><input type="text" name="answer_text_' . $arrData['prefix'] . '[]" value="' . $optionRow["answer_text"] . '" /></td><td><input type="text" name="text_if_wrong_' . $arrData['prefix'] . '[]" value="' . $optionRow["text_if_wrong"] . '" /></td><td><input type="text" name="audio_file_' . $arrData['prefix'] . '[]" value="' . $optionRow["audio_file"] . '" /></td><td><input type="radio" name="correct_answer_' . $arrData['prefix'] . '" id="correct_answer_' . $arrData['prefix'] . '" ' . $checked . ' value="' . $oCnt . '" /></td><td><a href="{{ uri }}admin/exercise_qae_topic_content_question/optiondelete/' . $optionRow["exercise_qae_topic_content_question_option_uid"] . '/' . $this->parts[3] . '/' . $this->parts[4] . '/' . $this->parts[5] . '/' . $this->parts[6] . '/" >Remove</a></td></tr>';
						$oCnt++;
					}
				} else {
					$options = '<tr><td><input type="text" name="answer_text_' . $arrData['prefix'] . '[]" value="" /></td><td><input type="text" name="text_if_wrong_' . $arrData['prefix'] . '[]" value="" /></td><td><input type="text" name="audio_file_' . $arrData['prefix'] . '[]" value="" /></td><td><input type="radio" name="correct_answer_' . $arrData['prefix'] . '" id="correct_answer_' . $arrData['prefix'] . '" checked="checked" value="0" /></td><td><a href="javascript:;" onclick="remove_qae_question(this,\'' . $arrData['prefix'] . '\')">Remove</a></td></tr>';
				}
				$lang_form = new xhtml('body.admin.exercise_qae_topic_content_question.tabs');
				$lang_form->load();
				$lang_form->assign("options", $options);
				$lang_form->assign("locale", $arrData['prefix']);
				$lang_form->assign("question_text", $questionTranslation['question_text']);
				$lang_form->assign("correct_answer_text", $questionTranslation['correct_answer_text']);
				$lang_form->assign("audio_file", $questionTranslation['audio_file']);
				$lang_form->assign("available_select_1", ($questionTranslation['available'] == '1') ? 'selected="selected"' : '');
				$lang_form->assign("available_select_0", ($questionTranslation['available'] == '0') ? 'selected="selected"' : '');

				$tabs_div[] = '<div id="tab-' . $uid . '">' . $lang_form->get_content() . '</div>';
			}
		}

		$body->assign(
				array(
					'tabs' => implode('', $tabs_div),
					'difficulty_uid' => format::to_select(array("name" => "difficulty_level_uid", "id" => "difficulty_level_uid", "options_only" => false), $difficulty_uids, $this->objexercise_qae_topic_content_question->get_difficulty_level_uid()),
					'locales' => implode('', $tabs_li),
					'available_1' => ($this->objexercise_qae_topic_content_question->get_available() == '1') ? 'selected="selected"' : '',
					'available_0' => ($this->objexercise_qae_topic_content_question->get_available() == '0') ? 'selected="selected"' : '',
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

		$body = new xhtml('body.admin.exercise_qae_topic_content_question.list');
		$body->load();
		$body->assign("exercise_qae_topic_content_uid", $this->parts[3]);
		$body->assign("exercise_qae_topic_uid", $this->parts[5]);
		
		$activityUid=$this->parts[4];
		$objActivity=new activity($activityUid);
		$objActivity->load();		
		$body->assign('activity_name',$objActivity->get_name());
		$body->assign('activity_uid',$activityUid);

		$this->objexercise_qae_topic_content_question = new exercise_qae_topic_content_question ();
		$arrExerciseList = $this->objexercise_qae_topic_content_question->getListByContent($this->parts[3]);

		$i = 0;
		if ($arrExerciseList && count($arrExerciseList) > 0) {
			$rows = array();
			foreach ($arrExerciseList as $arrData) {
				$i++;
				$row = new xhtml('body.admin.exercise_qae_topic_content_question.list.row');
				$row->load();
				$row->assign(array(
					'uid' => $arrData['uid'],
					'available' => ($arrData['available'] == '1') ? "Yes" : "No",
					'title' => stripslashes($arrData['title']),
					'exercise_qae_topic_content_uid' => $this->parts[3],
					'activity_uid' => $this->parts[4],
					'exercise_qae_topic_uid' => $this->parts[5],
				));

				$rows[] = $row->get_content();
			}
			$body->assign('rows', implode('', $rows));
		}
		$page_display_name = $this->objexercise_qae_topic_content_question->get_page_name('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
		$page_navigation = $this->objexercise_qae_topic_content_question->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>') . $this->objexercise_qae_topic_content_question->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>', ' &raquo ') . $this->objexercise_qae_topic_content_question->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');
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

			$this->objexercise_qae_topic_content_question = new exercise_qae_topic_content_question($this->parts[3]);
			$this->objexercise_qae_topic_content_question->softDelete($this->parts[3]);
//			$objExerciseQaeTopicContentQuestionOption = new exercise_qae_topic_content_question_option();
			
			
//			$optionUids = $objExerciseQaeTopicContentQuestionOption->getListByQuestion($this->parts[3]);
//			foreach ($optionUids as $optionUid) {
//				$objExerciseQaeTopicContentQuestionOption->deleteOption($optionUid["uid"]);
//			}
//
//			$this->objexercise_qae_topic_content_question->deleteTranslation($this->parts[3]);
//			$this->objexercise_qae_topic_content_question->delete();

			$this->objexercise_qae_topic_content_question->redirectTo('admin/exercise_qae_topic_content_question/list/' . $this->parts[4] . '/'.$this->parts[5].'/'.$this->parts[6].'/');
		} else {
			output::redirect(config::url('admin/exercise_qae_topic_content_question/list/' . $this->parts[4] . '/'.$this->parts[5].'/'.$this->parts[6].'/'));
		}
	}

}

?>