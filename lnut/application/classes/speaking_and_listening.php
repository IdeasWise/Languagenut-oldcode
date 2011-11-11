<?php

class speaking_and_listening extends generic_object {

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function softDelete($uid) {
		$sql = "UPDATE `speaking_and_listening` SET";
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
		$sql.= "`speaking_and_listening_translation` ";
		$sql.= " WHERE ";
		$sql.= " speaking_and_listening_uid='$uid' ";
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
		$sql.= "`speaking_and_listening` ";
		$sql.= " WHERE deleted='0'";
		$this->setPagination($sql);
		$sql = "SELECT ";
		$sql.= " * ";
		$sql.= "FROM ";
		$sql.= "`speaking_and_listening` ";
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
		$sql.= "`speaking_and_listening` ";
		$sql.= " WHERE";
		$sql.= " activity_uid='{$activityUid}'";
		$sql.= " AND deleted='0'";
		$sql.= " LIMIT 1";
		$response = database::arrQuery($sql);
		return (isset($response[0]))?$response[0]:false;
	}

	public function isValidCreate($arrData=array()) {
		$title = (isset($arrData['title']) && strlen(trim($arrData['title'])) > 0) ? $arrData['title'] : '';
		$token = (isset($arrData['token']) && strlen(trim($arrData['token'])) > 0) ? $arrData['token'] : '';
		$activityUid = (isset($arrData['activity_uid']) && strlen(trim($arrData['activity_uid'])) > 0) ? $arrData['activity_uid'] : '0';
		if ($title != '') {
			$sql = "INSERT INTO `speaking_and_listening` SET
					`title`='{$title}',
					`token`='{$token}',
					`activity_uid`='{$activityUid}'
					";
			$speaking_and_listening_uid = database::insert($sql);
			$this->__construct($speaking_and_listening_uid);
			$this->load();
			$arrLocales = language::getPrefixes();
			if (count($arrLocales) > 0) {
				foreach ($arrLocales as $luid => $arrData) {
					$audio_file_path = addslashes($_POST["audio_file_path_" . $arrData['prefix']]);
					$translationTitle = addslashes($_POST["title_" . $arrData['prefix']]);
					
					$sql = "INSERT INTO `speaking_and_listening_translation` SET
							speaking_and_listening_uid='{$speaking_and_listening_uid}',
							language_uid='{$luid}',
							audio_file_path='{$audio_file_path}',
							title='{$translationTitle}'
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
		$speaking_and_listening_uid = (isset($arrData['speaking_and_listening_uid']) && is_numeric($arrData['speaking_and_listening_uid']) && $arrData['speaking_and_listening_uid'] > 0) ? $arrData['speaking_and_listening_uid'] : '';
		if ($speaking_and_listening_uid != '' && $title != '') {
			$this->__construct($speaking_and_listening_uid);
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
								`speaking_and_listening_translation` 
							SET								
								language_uid='{$luid}',
								audio_file_path='{$audio_file_path}'
								title='{$translationTitle}'
							WHERE 
								speaking_and_listening_uid='{$speaking_and_listening_uid}'
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

	public function insertOrUpdate($arrData=array()){
		if($arrData["speaking_and_listening_uid"]>0){
			$this->softDelete($arrData["speaking_and_listening_uid"]);
			return $this->isValidCreate($arrData);
		}
		else{
			return $this->isValidCreate($arrData);
		}
	}
}

?>