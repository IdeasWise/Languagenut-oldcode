<?php

class exercise_qae_topic_content_question_option extends generic_object {

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function deleteOption($uid=0) {
		$sql = "DELETE FROM `exercise_qae_topic_content_question_option_translation` ";
		$sql.= " WHERE ";
		$sql.= " exercise_qae_topic_content_question_option_uid='{$uid}'";
		$result1 = database::query($sql);
		$sql = "DELETE FROM `exercise_qae_topic_content_question_option` ";
		$sql.= " WHERE ";
		$sql.= " uid='{$uid}'";
		$result2 = database::query($sql);
		return ($result2 && $result1);
	}

	public function getListByLocale($uid, $luid) {
		$response = false;
		$sql = "SELECT ";
		$sql.= " *";
		$sql.= " FROM ";
		$sql.= "`exercise_qae_topic_content_question_option_translation` ";
		$sql.= " WHERE ";
		$sql.= " exercise_qae_topic_content_question_option_uid IN ";
		$sql.= " ( ";
		$sql.= " SELECT uid FROM `exercise_qae_topic_content_question_option` ";
		$sql.= " WHERE ";
		$sql.= " exercise_qae_topic_content_question_uid='{$uid}'";
		$sql.= " ) ";
		$sql.= " AND ";
		$sql.= " language_uid_support='$luid'";
		$response = database::arrQuery($sql);
		return (isset($response)) ? $response : FALSE;
	}

	public function getListByname($OrderBy = 'title') {
		$response = false;
		$sql = "SELECT ";
		$sql.= "count(`uid`) ";
		$sql.= "FROM ";
		$sql.= "`exercise_qae_topic_content_question_option` ";
		$this->setPagination($sql);
		$sql = "SELECT ";
		$sql.= " * ";
		$sql.= "FROM ";
		$sql.= "`exercise_qae_topic_content_question_option` ";
		$sql.= "ORDER BY ";
		$sql.= "`" . $OrderBy . "` ASC";
		$sql.= " LIMIT " . $this->get_limit();
		$response = database::arrQuery($sql);
		return $response;
	}

	public function getListByQuestion($questionUid=0) {
		$sql = "SELECT uid FROM `exercise_qae_topic_content_question_option`";
		$sql.=" WHERE";
		$sql.=" exercise_qae_topic_content_question_uid='{$questionUid}'";
		$result = database::arrQuery($sql);
		return $result;
	}

//	public function isValidCreate($arrData=array()) {
//		$title = (isset($arrData['title']) && strlen(trim($arrData['title'])) > 0) ? $arrData['title'] : '';
//
//		if ($title != '') {
//			$sql = "INSERT INTO `exercise_qae_topic_content_question_option` SET
//					`title`='{$title}'
//					";
//			$exercise_qae_topic_content_question_option_uid = database::insert($sql);
//
//			$this->__construct($exercise_qae_topic_content_question_option_uid);
//			$this->load();
//
//			$arrLocales = language::getPrefixes();
//			if (count($arrLocales) > 0) {
//				foreach ($arrLocales as $luid => $arrData) {
//					$primary_image_path = addslashes($_POST["primary_image_path_" . $arrData['prefix']]);
//					$primary_image_caption = addslashes($_POST["primary_image_caption_" . $arrData['prefix']]);
//					$secondary_image_path = addslashes($_POST["secondary_image_path_" . $arrData['prefix']]);
//					$secondary_image_caption = addslashes($_POST["secondary_image_caption_" . $arrData['prefix']]);
//					$title = addslashes($_POST["title_" . $arrData['prefix']]);
//					$introduction = addslashes($_POST["introduction_" . $arrData['prefix']]);
//					$sql = "INSERT INTO `exercise_qae_topic_content_question_option_translation` SET
//							exercise_qae_topic_content_question_option_uid='{$exercise_qae_topic_content_question_option_uid}',
//							locale='{$arrData['prefix']}',
//							language_uid_support='{$luid}',
//							primary_image_path='{$primary_image_path}',
//							primary_image_caption='{$primary_image_caption}',
//							secondary_image_path='{$secondary_image_path}',
//							secondary_image_caption='{$secondary_image_caption}',
//							title='{$title}',
//							introduction='{$introduction}'
//							";
//					database::insert($sql);
//				}
//			}
//
//			return true;
//		} else {
//			$arrData['title'] = $title;
//			$arrData['message'] = 'Please complete all fields';
//		}
//
//		return $arrData;
//	}
//
//	public function isValidUpdate($arrData=array()) {
//
//		$title = (isset($arrData['title']) && strlen(trim($arrData['title'])) > 0) ? $arrData['title'] : '';
//		$exercise_qae_topic_content_question_option_uid = (isset($arrData['exercise_qae_topic_content_question_option_uid']) && is_numeric($arrData['exercise_qae_topic_content_question_option_uid']) && $arrData['exercise_qae_topic_content_question_option_uid'] > 0) ? $arrData['exercise_qae_topic_content_question_option_uid'] : '';
//
//		if ($exercise_qae_topic_content_question_option_uid != '' && $title != '') {
//
//			$this->__construct($exercise_qae_topic_content_question_option_uid);
//			$this->load();
//
//			$this->set_title($title);
//			$this->save();
//
//			$arrLocales = language::getPrefixes();
//			if (count($arrLocales) > 0) {
//				foreach ($arrLocales as $luid => $arrData) {
//					$primary_image_path = addslashes($_POST["primary_image_path_" . $arrData['prefix']]);
//					$primary_image_caption = addslashes($_POST["primary_image_caption_" . $arrData['prefix']]);
//					$secondary_image_path = addslashes($_POST["secondary_image_path_" . $arrData['prefix']]);
//					$secondary_image_caption = addslashes($_POST["secondary_image_caption_" . $arrData['prefix']]);
//					$title = addslashes($_POST["title_" . $arrData['prefix']]);
//					$introduction = addslashes($_POST["introduction_" . $arrData['prefix']]);
//
//					$sql = "UPDATE 
//								`exercise_qae_topic_content_question_option_translation` 
//							SET
//								locale='{$arrData['prefix']}',
//								language_uid_support='{$luid}',
//								primary_image_path='{$primary_image_path}',
//								primary_image_caption='{$primary_image_caption}',
//								secondary_image_path='{$secondary_image_path}',
//								secondary_image_caption='{$secondary_image_caption}',
//								title='{$title}',
//								introduction='{$introduction}'
//							WHERE 
//								exercise_qae_topic_content_question_option_uid='{$exercise_qae_topic_content_question_option_uid}'
//							AND 
//								locale='{$arrData['prefix']}'
//							AND 
//								language_uid_support='{$luid}'";
//					database::query($sql);
//				}
//			}
//			return true;
//		} else {
//			$arrData['title'] = $title;
//
//			$arrData['message'] = 'Please complete all fields';
//		}
//
//		return $arrData;
//	}
}

?>