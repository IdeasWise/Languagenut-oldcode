<?php

class exercise_qae_topic extends generic_object {

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function softDelete($uid) {
		$sql = "UPDATE `exercise_qae_topic` SET";
		$sql.=" `deleted`='1'";
		$sql.=" WHERE";
		$sql.=" uid='{$uid}'";
		database::query($sql);
	}

	public function getListByLocale($uid, $luid) {
		$response = false;
		$sql = "SELECT ";
		$sql.= " *";
		$sql.= " FROM ";
		$sql.= "`exercise_qae_topic_translation` ";
		$sql.= " WHERE ";
		$sql.= " exercise_qae_topic_uid='$uid' ";
		$sql.= " AND ";
		$sql.= " language_uid_support='$luid'";
		$sql.= " LIMIT 1";
		$response = database::arrQuery($sql);
		return (isset($response[0])) ? $response[0] : FALSE;
	}

	public function getListByname($OrderBy = 'title') {
		$response = false;
		$sql = "SELECT ";
		$sql.= "count(`uid`) ";
		$sql.= "FROM ";
		$sql.= "`exercise_qae_topic` ";
		$sql.= " WHERE deleted='0'";
		$this->setPagination($sql);
		$sql = "SELECT ";
		$sql.= " * ";
		$sql.= "FROM ";
		$sql.= "`exercise_qae_topic` ";
		$sql.= " WHERE deleted='0'";
		$sql.= "ORDER BY ";
		$sql.= "`" . $OrderBy . "` ASC";
		$sql.= " LIMIT " . $this->get_limit();
		$response = database::arrQuery($sql);
		return $response;
	}

	public function getListByActivity($activityUid=0, $OrderBy = 'title') {
		$response = false;
		$sql = "SELECT ";
		$sql.= " count(`uid`) ";
		$sql.= " FROM ";
		$sql.= " `exercise_qae_topic` ";
		$sql.= " WHERE";
		$sql.= " activity_exercise_uid='{$activityUid}'";
		$sql.= " AND deleted='0'";

		$this->setPagination($sql);
		$sql = "SELECT ";
		$sql.= " * ";
		$sql.= "FROM ";
		$sql.= "`exercise_qae_topic` ";
		$sql.= " WHERE";
		$sql.= " activity_exercise_uid='{$activityUid}'";
		$sql.= " AND deleted='0'";
		$sql.= "ORDER BY ";
		$sql.= "`" . $OrderBy . "` ASC";
		$sql.= " LIMIT " . $this->get_limit();
		$response = database::arrQuery($sql);
		return $response;
	}

	public function isValidCreate($arrData=array()) {
		$title = (isset($arrData['title']) && strlen(trim($arrData['title'])) > 0) ? $arrData['title'] : '';
		$activityExerciseUid = (isset($arrData['activity_uid']) && strlen(trim($arrData['activity_uid'])) > 0) ? $arrData['activity_uid'] : '0';
		if ($title != '') {
			$sql = "INSERT INTO `exercise_qae_topic` SET
					`title`='{$title}',
					`activity_exercise_uid`='{$activityExerciseUid}'
					";
			$exercise_qae_topic_uid = database::insert($sql);
			$this->__construct($exercise_qae_topic_uid);
			$this->load();
			$arrLocales = language::getPrefixes();
			if (count($arrLocales) > 0) {
				foreach ($arrLocales as $luid => $arrData) {
					$primary_image_path = addslashes($_POST["primary_image_path_" . $arrData['prefix']]);
					$primary_image_caption = addslashes($_POST["primary_image_caption_" . $arrData['prefix']]);
					$secondary_image_path = addslashes($_POST["secondary_image_path_" . $arrData['prefix']]);
					$secondary_image_caption = addslashes($_POST["secondary_image_caption_" . $arrData['prefix']]);
					$title = addslashes($_POST["title_" . $arrData['prefix']]);
					$introduction = addslashes($_POST["introduction_" . $arrData['prefix']]);
					$sql = "INSERT INTO `exercise_qae_topic_translation` SET
							exercise_qae_topic_uid='{$exercise_qae_topic_uid}',
							locale='{$arrData['prefix']}',
							language_uid_support='{$luid}',
							primary_image_path='{$primary_image_path}',
							primary_image_caption='{$primary_image_caption}',
							secondary_image_path='{$secondary_image_path}',
							secondary_image_caption='{$secondary_image_caption}',
							title='{$title}',
							introduction='{$introduction}'
							";
					database::insert($sql);
				}
			}
			return true;
		} else {
			$arrData['title'] = $title;
			$arrData['message'] = 'Please complete all fields';
		}
		return $arrData;
	}

	public function isValidUpdate($arrData=array()) {

		$title = (isset($arrData['title']) && strlen(trim($arrData['title'])) > 0) ? $arrData['title'] : '';
		$exercise_qae_topic_uid = (isset($arrData['exercise_qae_topic_uid']) && is_numeric($arrData['exercise_qae_topic_uid']) && $arrData['exercise_qae_topic_uid'] > 0) ? $arrData['exercise_qae_topic_uid'] : '';
		if ($exercise_qae_topic_uid != '' && $title != '') {
			$this->__construct($exercise_qae_topic_uid);
			$this->load();
			$this->set_title($title);
			$this->save();
			$arrLocales = language::getPrefixes();
			if (count($arrLocales) > 0) {
				foreach ($arrLocales as $luid => $arrData) {
					$primary_image_path = addslashes($_POST["primary_image_path_" . $arrData['prefix']]);
					$primary_image_caption = addslashes($_POST["primary_image_caption_" . $arrData['prefix']]);
					$secondary_image_path = addslashes($_POST["secondary_image_path_" . $arrData['prefix']]);
					$secondary_image_caption = addslashes($_POST["secondary_image_caption_" . $arrData['prefix']]);
					$title = addslashes($_POST["title_" . $arrData['prefix']]);
					$introduction = addslashes($_POST["introduction_" . $arrData['prefix']]);
					$sql = "UPDATE 
								`exercise_qae_topic_translation` 
							SET
								locale='{$arrData['prefix']}',
								language_uid_support='{$luid}',
								primary_image_path='{$primary_image_path}',
								primary_image_caption='{$primary_image_caption}',
								secondary_image_path='{$secondary_image_path}',
								secondary_image_caption='{$secondary_image_caption}',
								title='{$title}',
								introduction='{$introduction}'
							WHERE 
								exercise_qae_topic_uid='{$exercise_qae_topic_uid}'
							AND 
								locale='{$arrData['prefix']}'
							AND 
								language_uid_support='{$luid}'";
					database::query($sql);
				}
			}
			return true;
		} else {
			$arrData['title'] = $title;
			$arrData['message'] = 'Please complete all fields';
		}
		return $arrData;
	}

}

?>