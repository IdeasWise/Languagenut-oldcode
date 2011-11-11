<?php

class activity_skill extends generic_object {

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function getListByLocale($uid, $luid) {
		$response = false;
		$sql = "SELECT ";
		$sql.= " *";
		$sql.= " FROM ";
		$sql.= "`activity_skill_translation` ";
		$sql.= " WHERE ";
		$sql.= " activity_skill_uid='$uid' ";
		$sql.= " AND ";
		$sql.= " language_uid='$luid'";
		$sql.= " LIMIT 1";
		$res = database::query($sql);
		if ($res && mysql_error() == '' && mysql_num_rows($res) > 0) {
			$response = array();
			while ($row = mysql_fetch_assoc($res)) {
				$response = array(
					'uid' => stripslashes($row['uid']),
					'name' => stripslashes($row['name']),
				);
			}
		}
		return $response;
	}

	public function getListByname($OrderBy = 'name') {
		$response = false;
		$sql = "SELECT ";
		$sql.= "count(`uid`) ";
		$sql.= "FROM ";
		$sql.= "`activity_skill` ";
		$this->setPagination($sql);
		$sql = "SELECT ";
		$sql.= " * ";
		$sql.= "FROM ";
		$sql.= "`activity_skill` ";
		$sql.= "ORDER BY ";
		$sql.= "`" . $OrderBy . "` ASC";
		$sql.= " LIMIT " . $this->get_limit();
		$res = database::query($sql);
		if ($res && mysql_error() == '' && mysql_num_rows($res) > 0) {
			$response = array();
			while ($row = mysql_fetch_assoc($res)) {
				$response[$row['uid']] = array(
					'name' => stripslashes($row['name']),
					'available' => stripslashes($row['available']),
				);
			}
		}
		return $response;
	}

	public function isValidCreate($arrData=array()) {
		$name = (isset($arrData['name']) && strlen(trim($arrData['name'])) > 0) ? $arrData['name'] : '';
		$token = (isset($arrData['token']) && strlen(trim($arrData['token'])) > 0) ? $arrData['token'] : '';
		$available = (isset($arrData['available']) && strlen(trim($arrData['available'])) > 0) ? $arrData['available'] : '';
		if ($token == "") {
			$token = format::to_friendly_url($name);
		}
		if ($name != '' && $available != '') {
			$sql = "INSERT INTO `activity_skill` SET
					name='{$name}',
					token='{$token}',
					available='{$available}'";
			$activity_skill_uid = database::insert($sql);
			$arrLocales = language::getPrefixes();
			if (count($arrLocales) > 0) {
				foreach ($arrLocales as $luid => $arrData) {
					$sql = "INSERT INTO `activity_skill_translation` SET
							activity_skill_uid='{$activity_skill_uid}',
							locale='{$arrData['prefix']}',
							language_uid='{$luid}',
							name='{$_POST["name_" . $arrData['prefix']]}'";
					database::insert($sql);
				}
			}
			return true;
		} else {
			$arrData['name'] = $name;
			$arrData['available'] = $available;
			$arrData['message'] = 'Please complete all fields';
		}
		return $arrData;
	}

	public function isValidUpdate($arrData=array()) {
		$activity_skill_uid = (isset($arrData['activity_skill_uid']) && (int) $arrData['activity_skill_uid'] > 0) ? $arrData['activity_skill_uid'] : '';
		$name = (isset($arrData['name']) && strlen(trim($arrData['name'])) > 0) ? $arrData['name'] : '';
		$token = (isset($arrData['token']) && strlen(trim($arrData['token'])) > 0) ? $arrData['token'] : '';
		$available = (isset($arrData['available']) && strlen(trim($arrData['available'])) > 0) ? $arrData['available'] : '';
		if ($token == "") {
			$token = format::to_friendly_url($name);
		}
		if ($activity_skill_uid != '' && $name != '' && $available != '') {
			$this->__construct($activity_skill_uid);
			$this->load();
			$this->arrFields['name']['Value'] = $name;
			$this->arrFields['token']['Value'] = $token;
			$this->arrFields['available']['Value'] = $available;
			$this->save();
			$arrLocales = language::getPrefixes();
			if (count($arrLocales) > 0) {
				foreach ($arrLocales as $luid => $arrData) {
					$name = addslashes($_POST['name_' . $arrData['prefix']]);
					$sql = "UPDATE `activity_skill_translation` SET
							name='{$name}'
							WHERE
							activity_skill_uid='{$activity_skill_uid}'
							AND
							language_uid='{$luid}'";
					database::query($sql);
				}
			}
			return true;
		} else {
			$arrData['name'] = $name;
			$arrData['message'] = 'Please complete all fields';
		}
		return $arrData;
	}

	public function getTranslationByUid($uid) {
		$response = false;
		$sql = "SELECT * FROM `activity_skill_translation`
				WHERE
				activity_skill_uid='{$uid}'";
		$res = database::query($sql);
		if ($res && mysql_error() == '' && mysql_num_rows($res) > 0) {
			$response = array();
			while ($row = mysql_fetch_assoc($res)) {
				$response[$row['uid']] = array(
					'locale' => stripslashes($row['locale']),
					'language_uid' => stripslashes($row['language_uid']),
					'name' => stripslashes($row['name']),
					'primary_image_path' => stripslashes($row['primary_image_path']),
					'primary_image_caption' => stripslashes($row['primary_image_caption']),
					'secondary_image_path' => stripslashes($row['secondary_image_path']),
					'secondary_image_caption' => stripslashes($row['secondary_image_caption']),
					'introduction' => stripslashes($row['introduction'])
				);
			}
		}
		return $response;
	}

	public static function getAllActivitySkills() {
		$sql = "SELECT ";
		$sql.= " * ";
		$sql.= "FROM ";
		$sql.= "`activity_skill` ";
		$sql.= "ORDER BY ";
		$sql.= "`name` ASC";
		return database::arrQuery($sql);
	}

}

?>