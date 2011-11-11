<?php

class exercise_writing extends generic_object {

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function softDelete($uid) {
		$sql = "UPDATE `exercise_writing` SET";
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
		$sql.= "`exercise_writing_translation` ";
		$sql.= " WHERE ";
		$sql.= " exercise_writing_uid='$uid' ";
		$sql.= " AND ";
		$sql.= " language_uid='$luid'";
		$sql.= " LIMIT 1";
		$response = database::arrQuery($sql);
		return (isset($response[0])) ? $response[0] : FALSE;
	}

	public function getListByname($OrderBy = 'title') {
		$response = false;
		$sql = "SELECT ";
		$sql.= "count(`uid`) ";
		$sql.= "FROM ";
		$sql.= "`exercise_writing` ";
		$sql.= " WHERE deleted='0'";
		$this->setPagination($sql);
		$sql = "SELECT ";
		$sql.= " * ";
		$sql.= "FROM ";
		$sql.= "`exercise_writing` ";
		$sql.= " WHERE deleted='0'";
		$sql.= "ORDER BY ";
		$sql.= "`" . $OrderBy . "` ASC";
		$sql.= " LIMIT " . $this->get_limit();
		$response = database::arrQuery($sql);
		return $response;
	}

	public function getListByActivity($activityUid=0) {
		$response = false;

		$sql = "SELECT ";
		$sql.= " * ";
		$sql.= "FROM ";
		$sql.= "`exercise_writing` ";
		$sql.= " WHERE";
		$sql.= " activity_uid='{$activityUid}'";
		$sql.= " AND deleted='0'";
		$sql.= " LIMIT 1";
		$response = database::arrQuery($sql);
		return (isset($response[0])) ? $response[0] : false;
	}

	public function isValidCreate($arrData=array()) {
		$article_uid = (isset($arrData['article_uid']) && $arrData['article_uid'] > 0) ? $arrData['article_uid'] : '';
		$activityUid = (isset($arrData['activity_uid']) && strlen(trim($arrData['activity_uid'])) > 0) ? $arrData['activity_uid'] : '0';
		if ($article_uid != '') {
			$sql = "INSERT INTO `exercise_writing` SET
					`article_uid`='{$article_uid}',					
					`activity_uid`='{$activityUid}'
					";
			$exercise_writing_uid = database::insert($sql);
			$this->__construct($exercise_writing_uid);
			$this->load();
			$arrLocales = language::getPrefixes();
			if (count($arrLocales) > 0) {
				foreach ($arrLocales as $luid => $arrData) {
					$articleList = article_translations::getTranslationByLocale($article_uid, $luid);
					$article_translation_uid = (isset($articleList['uid'])) ? $articleList['uid'] : 0;

					$activityList = activity_translation::getTranslationByLocale($article_uid, $luid);
					$activity_translation_uid = (isset($activityList['uid'])) ? $activityList['uid'] : 0;

					$sql = "INSERT INTO `exercise_writing_translation` SET
							`article_uid`='{$article_uid}',
							`activity_translation_uid`='{$activity_translation_uid}',
							`article_translation_uid`='{$article_translation_uid}',
							`language_uid`='{$luid}',
							`exercise_writing_uid`='{$exercise_writing_uid}',
							`activity_uid`='{$activityUid}'
							";
					database::insert($sql);
				}
			}
			return true;
		} else {
			$arrData['title'] = $title;
			$arrData['token'] = $token;
			$arrData['message'] = 'Please complete all fields';
		}
		return $arrData;
	}

	public function isValidUpdate($arrData=array()) {

		$title = (isset($arrData['title']) && strlen(trim($arrData['title'])) > 0) ? $arrData['title'] : '';
		$token = (isset($arrData['token']) && strlen(trim($arrData['token'])) > 0) ? $arrData['token'] : '';
		$exercise_writing_uid = (isset($arrData['exercise_writing_uid']) && is_numeric($arrData['exercise_writing_uid']) && $arrData['exercise_writing_uid'] > 0) ? $arrData['exercise_writing_uid'] : '';
		if ($exercise_writing_uid != '' && $title != '') {
			$this->__construct($exercise_writing_uid);
			$this->load();
			$this->set_title($title);
			$this->set_token($token);
			$this->save();
			$arrLocales = language::getPrefixes();
			if (count($arrLocales) > 0) {
				foreach ($arrLocales as $luid => $arrData) {
					$audio_file_path = addslashes($_POST["audio_file_path_" . $arrData['prefix']]);
					$translationTitle = addslashes($_POST["title_" . $arrData['prefix']]);

					$sql = "UPDATE
								`exercise_writing_translation`
							SET								
								language_uid='{$luid}',
								audio_file_path='{$audio_file_path}'
								title='{$translationTitle}'
							WHERE 
								exercise_writing_uid='{$exercise_writing_uid}'
							AND 
								language_uid='{$luid}'";
					database::query($sql);
				}
			}
			return true;
		} else {
			$arrData['title'] = $title;
			$arrData['token'] = $token;
			$arrData['message'] = 'Please complete all fields';
		}
		return $arrData;
	}

	public function insertOrUpdate($arrData=array()) {
		if ($arrData["exercise_writing_uid"] > 0) {
			$this->softDelete($arrData["exercise_writing_uid"]);
			return $this->isValidCreate($arrData);
		} else {
			return $this->isValidCreate($arrData);
		}
	}

}
?>