<?php

class exercise_qae_topic_content_question extends generic_object {

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function softDelete($uid) {
		$sql = "UPDATE `exercise_qae_topic_content_question` SET";
		$sql.=" `deleted`='1'";
		$sql.=" WHERE";
		$sql.=" uid='{$uid}'";
		database::query($sql);
	}

	public function deleteTranslation($uid) {
		$sql = "DELETE FROM `exercise_qae_topic_content_question_translation`";
		$sql.=" WHERE exercise_qae_topic_content_question_uid='{$uid}'";
		database::query($sql);
	}

	public function getListByLocale($uid, $luid) {
		$response = false;
		$sql = "SELECT ";
		$sql.= " *";
		$sql.= " FROM ";
		$sql.= "`exercise_qae_topic_content_question_translation` ";
		$sql.= " WHERE ";
		$sql.= " exercise_qae_topic_content_question_uid='$uid' ";
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
		$sql.= "`exercise_qae_topic_content_question` ";
		$sql.= " WHERE";
		$sql.= " deleted='0'";
		$this->setPagination($sql);
		$sql = "SELECT ";
		$sql.= " uid,available,title ";
		$sql.= "FROM ";
		$sql.= "`exercise_qae_topic_content_question` ";
		$sql.= " WHERE ";
		$sql.= " deleted='0'";
		$sql.= "ORDER BY ";
		$sql.= "`" . $OrderBy . "` ASC";
		$sql.= " LIMIT " . $this->get_limit();
		$response = database::arrQuery($sql);
		return $response;
	}
	
	public function getListByContent($contentUid=0,$OrderBy = 'title') {
		$response = false;
		$sql = "SELECT ";
		$sql.= "count(`uid`) ";
		$sql.= "FROM ";
		$sql.= "`exercise_qae_topic_content_question` ";
		$sql.= " WHERE";
		$sql.= " deleted='0'";
		$sql.= " AND ";
		$sql.= " exercise_qae_topic_content_uid='{$contentUid}'";
		$this->setPagination($sql);
		$sql = "SELECT ";
		$sql.= " uid,available,title ";
		$sql.= "FROM ";
		$sql.= "`exercise_qae_topic_content_question` ";
		$sql.= " WHERE ";
		$sql.= " deleted='0'";
		$sql.= " AND ";
		$sql.= " exercise_qae_topic_content_uid='{$contentUid}'";
		$sql.= "ORDER BY ";
		$sql.= "`" . $OrderBy . "` ASC";
		$sql.= " LIMIT " . $this->get_limit();
		$response = database::arrQuery($sql);
		return $response;
	}

	public function isValidCreate($arrData=array()) {
		$arrDataReturn = array();
		$title = (isset($arrData['title']) && strlen(trim($arrData['title'])) > 0) ? $arrData['title'] : '';
		$focus = (isset($arrData['focus']) && strlen(trim($arrData['focus'])) > 0) ? $arrData['focus'] : '';
		$difficulty_level_uid = (isset($arrData['difficulty_level_uid']) && strlen(trim($arrData['difficulty_level_uid'])) > 0) ? $arrData['difficulty_level_uid'] : '';
		$available = (isset($arrData['available']) && strlen(trim($arrData['available'])) > 0) ? $arrData['available'] : '';
		$exercise_qae_topic_content_uid = (isset($arrData['exercise_qae_topic_content_uid']) && strlen(trim($arrData['exercise_qae_topic_content_uid'])) > 0) ? $arrData['exercise_qae_topic_content_uid'] : '';
		if ($title != '' && $focus != '' && $difficulty_level_uid != '' && $available != '' && $exercise_qae_topic_content_uid != '') {
			$sql = "INSERT INTO `exercise_qae_topic_content_question` SET
					`title`='{$title}',
					`focus`='{$focus}',
					`difficulty_level_uid`='{$difficulty_level_uid}',
					`available`='{$available}',
					`exercise_qae_topic_content_uid`='{$exercise_qae_topic_content_uid}'
					";
			$exercise_qae_topic_content_question_uid = database::insert($sql);
			$this->__construct($exercise_qae_topic_content_question_uid);
			$this->load();
			$arrLocales = language::getPrefixes();
			$arrFirstLang = array();
			$arrLang = array();
			
			foreach ($arrLocales as $luid => $arrData) {				
				$arrFirstLang[]=count($_POST['answer_text_' . $arrData['prefix']]);
				$arrLang[]=$arrData['prefix'];
			}
			asort($arrFirstLang,SORT_NUMERIC);
			$key=array_search(end($arrFirstLang), $arrFirstLang);
			$firstLang=$arrLang[$key];
			
			$exercise_qae_topic_content_question_option_uid = array();
			$correct_option_id = 0;
			$cnt = 0;
			
			foreach ($_POST['answer_text_' . $firstLang] as $post) {
				$sql = "INSERT INTO `exercise_qae_topic_content_question_option` SET
						exercise_qae_topic_content_question_uid='{$exercise_qae_topic_content_question_uid}'";
				$exercise_qae_topic_content_question_option_uid[] = database::insert($sql);
				if ($cnt == $_POST["correct_answer_" . $firstLang]) {
					$correct_option_id = $exercise_qae_topic_content_question_option_uid[$cnt];
				}
				$cnt++;
			}
			$this->set_correct_option_uid($correct_option_id);
			$this->save();
			if (count($arrLocales) > 0) {
				foreach ($arrLocales as $luid => $arrData) {
					$question_text = (isset($_POST["question_text_" . $arrData['prefix']])) ? addslashes($_POST["question_text_" . $arrData['prefix']]) : '';
					$correct_answer_text = (isset($_POST["correct_answer_text_" . $arrData['prefix']])) ? addslashes($_POST["correct_answer_text_" . $arrData['prefix']]) : '';
					$audio_file = (isset($_POST["question_audio_file_" . $arrData['prefix']])) ? addslashes($_POST["question_audio_file_" . $arrData['prefix']]) : '';
					$available = (isset($_POST["available_" . $arrData['prefix']])) ? addslashes($_POST["available_" . $arrData['prefix']]) : '';
					$sql = "INSERT INTO `exercise_qae_topic_content_question_translation` SET
							exercise_qae_topic_content_question_uid='{$exercise_qae_topic_content_question_uid}',
							locale='{$arrData['prefix']}',
							language_uid_support='{$luid}',
							question_text='{$question_text}',
							correct_answer_text='{$correct_answer_text}',
							audio_file='{$audio_file}',
							available='{$available}'
							";
					$question_id = database::insert($sql);
					$cnt = 0;
					foreach ($exercise_qae_topic_content_question_option_uid as $option_uid) {
						$answer_text = (isset($_POST['answer_text_' . $arrData['prefix']][$cnt])) ? $_POST['answer_text_' . $arrData['prefix']][$cnt] : '';
						$text_if_wrong = (isset($_POST['text_if_wrong_' . $arrData['prefix']][$cnt])) ? $_POST['text_if_wrong_' . $arrData['prefix']][$cnt] : '';
						$audio_file = (isset($_POST['audio_file_' . $arrData['prefix']][$cnt])) ? $_POST['audio_file_' . $arrData['prefix']][$cnt] : '';
						$sql = "INSERT INTO `exercise_qae_topic_content_question_option_translation` SET
								exercise_qae_topic_content_question_option_uid='{$option_uid}',
								locale='{$arrData['prefix']}',
								language_uid_support='{$luid}',
								answer_text='{$answer_text}',
								text_if_wrong='{$text_if_wrong}',
								audio_file='{$audio_file}'";
						database::insert($sql);
						$cnt++;
					}
				}
			}
			return true;
		} else {
			$arrDataReturn['title'] = $title;
			$arrDataReturn['focus'] = $focus;
			$arrDataReturn['difficulty_level_uid'] = $difficulty_level_uid;
			$arrDataReturn['available'] = $available;
			$arrDataReturn['exercise_qae_topic_content_uid'] = $exercise_qae_topic_content_uid;
			$arrDataReturn['message'] = 'Please complete all fields';
		}
		return $arrDataReturn;
	}

	public function isValidUpdate($arrData=array()) {
		$arrDataReturn = array();
		$title = (isset($arrData['title']) && strlen(trim($arrData['title'])) > 0) ? $arrData['title'] : '';
		$focus = (isset($arrData['focus']) && strlen(trim($arrData['focus'])) > 0) ? $arrData['focus'] : '';
		$difficulty_level_uid = (isset($arrData['difficulty_level_uid']) && strlen(trim($arrData['difficulty_level_uid'])) > 0) ? $arrData['difficulty_level_uid'] : '';
		$available = (isset($arrData['available']) && strlen(trim($arrData['available'])) > 0) ? $arrData['available'] : '';
		$exercise_qae_topic_content_uid = (isset($arrData['exercise_qae_topic_content_uid']) && strlen(trim($arrData['exercise_qae_topic_content_uid'])) > 0) ? $arrData['exercise_qae_topic_content_uid'] : '';
		if ($title != '' && $focus != '' && $difficulty_level_uid != '' && $available != '' && $exercise_qae_topic_content_uid != '') {
			$sql = "UPDATE `exercise_qae_topic_content_question` SET
					`title`='{$title}',
					`focus`='{$focus}',
					`difficulty_level_uid`='{$difficulty_level_uid}',
					`available`='{$available}',
					`exercise_qae_topic_content_uid`='{$exercise_qae_topic_content_uid}'
					WHERE
					`uid`='{$arrData["exercise_qae_topic_content_question_uid"]}'
					";
			database::query($sql);
			$exercise_qae_topic_content_question_uid = $arrData["exercise_qae_topic_content_question_uid"];
			$this->__construct($exercise_qae_topic_content_question_uid);
			$this->load();
			$arrLocales = language::getPrefixes();
						$arrFirstLang = array();
			$arrLang = array();

			foreach ($arrLocales as $luid => $arrData) {
				$arrFirstLang[]=count($_POST['answer_text_' . $arrData['prefix']]);
				$arrLang[]=$arrData['prefix'];
			}
			asort($arrFirstLang,SORT_NUMERIC);
			$key=array_search(end($arrFirstLang), $arrFirstLang);
			$firstLang=$arrLang[$key];
			
			$exercise_qae_topic_content_question_option_uid = array();
			$correct_option_id = 0;
			$cnt = 0;
			$sql = "SELECT uid FROM `exercise_qae_topic_content_question_option`";
			$sql.=" WHERE ";
			$sql.=" exercise_qae_topic_content_question_uid='{$exercise_qae_topic_content_question_uid}'";
			$totalOption = database::arrQuery($sql);
			
			foreach ($_POST['answer_text_' . $firstLang] as $post) {
				if (isset($totalOption[$cnt]["uid"])) {
					$exercise_qae_topic_content_question_option_uid[] = $totalOption[$cnt]["uid"];
					$cnt++;
					continue;
				}
				$sql = "INSERT INTO `exercise_qae_topic_content_question_option` SET
						exercise_qae_topic_content_question_uid='{$exercise_qae_topic_content_question_uid}'";
				$exercise_qae_topic_content_question_option_uid[] = database::insert($sql);
				if ($cnt == $_POST["correct_answer_" . $firstLang]) {
					$correct_option_id = $exercise_qae_topic_content_question_option_uid[$cnt];
				}
				$cnt++;
			}
			if ($correct_option_id != 0) {
				$this->set_correct_option_uid($correct_option_id);
				$this->save();
			}
			if (count($arrLocales) > 0) {
				foreach ($arrLocales as $luid => $arrData) {
					$question_text = (isset($_POST["question_text_" . $arrData['prefix']])) ? addslashes($_POST["question_text_" . $arrData['prefix']]) : '';
					$correct_answer_text = (isset($_POST["correct_answer_text_" . $arrData['prefix']])) ? addslashes($_POST["correct_answer_text_" . $arrData['prefix']]) : '';
					$audio_file = (isset($_POST["question_audio_file_" . $arrData['prefix']])) ? addslashes($_POST["question_audio_file_" . $arrData['prefix']]) : '';
					$available = (isset($_POST["available_" . $arrData['prefix']])) ? addslashes($_POST["available_" . $arrData['prefix']]) : '';
					$sql = "UPDATE `exercise_qae_topic_content_question_translation` SET
							question_text='{$question_text}',
							correct_answer_text='{$correct_answer_text}',
							audio_file='{$audio_file}',
							available='{$available}'
							WHERE
							exercise_qae_topic_content_question_uid='{$exercise_qae_topic_content_question_uid}'
							AND
							language_uid_support='{$luid}'
							";
					database::query($sql);
					$cnt = 0;
					foreach ($exercise_qae_topic_content_question_option_uid as $option_uid) {
						$answer_text = (isset($_POST['answer_text_' . $arrData['prefix']][$cnt])) ? $_POST['answer_text_' . $arrData['prefix']][$cnt] : '';
						$text_if_wrong = (isset($_POST['text_if_wrong_' . $arrData['prefix']][$cnt])) ? $_POST['text_if_wrong_' . $arrData['prefix']][$cnt] : '';
						$audio_file = (isset($_POST['audio_file_' . $arrData['prefix']][$cnt])) ? $_POST['audio_file_' . $arrData['prefix']][$cnt] : '';
						$sql = "SELECT uid FROM `exercise_qae_topic_content_question_option_translation`";
						$sql.=" WHERE ";
						$sql.=" exercise_qae_topic_content_question_option_uid='{$option_uid}'";
						$sql.=" AND";
						$sql.=" language_uid_support='{$luid}'";
						$sql.=" LIMIT 1";
						$optionTranslationUid = database::arrQuery($sql);
						if (isset($optionTranslationUid[0]["uid"])) {
							$sql = "UPDATE `exercise_qae_topic_content_question_option_translation` SET
									answer_text='{$answer_text}',
									text_if_wrong='{$text_if_wrong}',
									audio_file='{$audio_file}'
									WHERE 
									exercise_qae_topic_content_question_option_uid='{$option_uid}'
									AND
									language_uid_support='{$luid}'
									";
							database::insert($sql);
						} else {
							$sql = "INSERT INTO `exercise_qae_topic_content_question_option_translation` SET
									exercise_qae_topic_content_question_option_uid='{$option_uid}',
									locale='{$arrData['prefix']}',
									language_uid_support='{$luid}',
									answer_text='{$answer_text}',
									text_if_wrong='{$text_if_wrong}',
									audio_file='{$audio_file}'";
							database::insert($sql);
						}
						$cnt++;
					}
				}
			}
			return true;
		} else {
			$arrDataReturn['title'] = $title;
			$arrDataReturn['focus'] = $focus;
			$arrDataReturn['difficulty_level_uid'] = $difficulty_level_uid;
			$arrDataReturn['available'] = $available;
			$arrDataReturn['exercise_qae_topic_content_uid'] = $exercise_qae_topic_content_uid;
			$arrDataReturn['message'] = 'Please complete all fields';
		}
		return $arrDataReturn;
	}

}

?>