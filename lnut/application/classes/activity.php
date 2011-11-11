<?php

class activity extends generic_object {

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function redirectToUrl($uid=0) {
		$sql = "SELECT token FROM";
		$sql.=" `exercise_type`";
		$sql.=" WHERE";
		$sql.=" uid IN (";
		$sql.=" SELECT exercise_type_uid FROM";
		$sql.=" `activity`";
		$sql.=" WHERE ";
		$sql.=" uid='{$uid}'";
		$sql.=" )";
		$sql.=" LIMIT 1";
		$exerciseType = database::arrQuery($sql);

		if (isset($exerciseType[0]) && !empty($exerciseType[0])) {
			switch ($exerciseType[0]['token']) {
				case 'content-creation':
					$sql = "SELECT token FROM";
					$sql.="`activity_skill`";
					$sql.=" WHERE";
					$sql.=" uid IN (";
					$sql.=" SELECT skill_level_uid FROM";
					$sql.=" `activity`";
					$sql.=" WHERE ";
					$sql.=" uid='{$uid}'";
					$sql.=" )";
					$skill = database::arrQuery($sql);

					if ($skill[0]["token"] == 'speaking_and_listening') {
						return config::url('admin/speaking_and_listening/edit/' . $uid . "/");
					}
					if ($skill[0]["token"] == 'writing') {
						return config::url('admin/activity/article/' . $uid . "/");
					}

					break;
				case 'multi-choice':
					return config::url('admin/exercise_qae_topic/list/' . $uid . "/");
					break;
				default :
					return config::url('admin/activity/list/');
					break;
			}
		}
		return config::url('admin/activity/list/');
	}

	public function getListByLocale($uid, $luid) {
		$response = false;
		$sql = "SELECT ";
		$sql.= " *";
		$sql.= " FROM ";
		$sql.= "`activity_translation` ";
		$sql.= " WHERE ";
		$sql.= " activity_uid='$uid' ";
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
					'available' => stripslashes($row['available'])
				);
			}
		}
		return $response;
	}

	public function softDelete($uid) {
		$sql = "UPDATE";
		$sql.=" `activity`";
		$sql.=" SET";
		$sql.=" deleted='1'";
		$sql.=" WHERE";
		$sql.=" uid='{$uid}'";
		if (database::query($sql))
			return true;
		return false;
	}

	public function getActivityByDifficulty($activity_main_uid=0, $difficulty_uid) {
		$sql = "SELECT * FROM `activity`
				WHERE
				activity_main_uid='{$activity_main_uid}'
				AND
				difficulty_uid='{$difficulty_uid}'
			";
		$data=database::arrQuery($sql);
		return (count($data)>0)?$data[0]:array();
	}

	public function getListByname($OrderBy = 'name') {
		$response = false;
		$sql = "SELECT ";
		$sql.= "count(`uid`) ";
		$sql.= "FROM ";
		$sql.= "`activity` ";
		$sql.= " WHERE ";
		$sql.= " deleted='0'";
		$this->setPagination($sql);
		$sql = "SELECT ";
		$sql.= " * ";
		$sql.= "FROM ";
		$sql.= "`activity` ";
		$sql.= " WHERE ";
		$sql.= " deleted='0'";
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

	public function getGroupedListByname($OrderBy = 'name') {
		$response = false;
		$sql = "SELECT ";
		$sql.= "count(`uid`) ";
		$sql.= "FROM ";
		$sql.= "`activity_main` ";
		$sql.= " WHERE ";
		$sql.= " deleted='0'";
		$this->setPagination($sql);
		$sql = "SELECT ";
		$sql.= " * ";
		$sql.= "FROM ";
		$sql.= "`activity_main` ";
		$sql.= " WHERE ";
		$sql.= " deleted='0'";
		$sql.= "ORDER BY ";
		$sql.= "`" . $OrderBy . "` ASC";
		$sql.= " LIMIT " . $this->get_limit();
		$res = database::query($sql);
		if ($res && mysql_error() == '' && mysql_num_rows($res) > 0) {
			$response = array();
			while ($row = mysql_fetch_assoc($res)) {
				$response[$row['uid']] = array(
					'name' => stripslashes($row['name']),
				);
			}
		}
		return $response;
	}

	public function isValidCreate($arrData=array()) {

		$difficultylevels = difficultylevel::getAllDifficulties();

		$name = (isset($arrData['name']) && strlen(trim($arrData['name'])) > 0) ? $arrData['name'] : '';
		$exercise_type_uid = (isset($arrData['exercise_type_uid']) && is_numeric($arrData['exercise_type_uid']) && $arrData['exercise_type_uid'] > 0) ? $arrData['exercise_type_uid'] : '';
		$material_type_uid = (isset($arrData['material_type_uid']) && is_numeric($arrData['material_type_uid']) && $arrData['material_type_uid'] > 0) ? $arrData['material_type_uid'] : '';
		$skill_level_uid = (isset($arrData['skill_level_uid']) && strlen(trim($arrData['skill_level_uid'])) > 0) ? $arrData['skill_level_uid'] : '';
		$unit_uid = (isset($arrData['unit_uid']) && strlen(trim($arrData['unit_uid'])) > 0) ? $arrData['unit_uid'] : '';
		if ($name != '' && !empty($difficultylevels) && $exercise_type_uid != '' && $material_type_uid != '' && $skill_level_uid != '' && $unit_uid!='') {
			$sql = "INSERT INTO `activity_main` SET
						name='{$name}',
						created_date='" . date("Y-m-d") . "'
					";
			$activity_main_uid = database::insert($sql);

			foreach ($difficultylevels as $uid => $data) {

				 $sql = "INSERT INTO `activity` SET
					`name`='{$name}',
					`activity_main_uid`='{$activity_main_uid}',
					`difficulty_uid`='{$data['uid']}',
					`exercise_type_uid`='{$exercise_type_uid}',
					`material_type_uid`='{$material_type_uid}',
					`skill_level_uid`='{$skill_level_uid}',
					`unit_uid`='{$unit_uid}',					
					`created_dts`='" . date("Y-m-d") . "'";
				$activity_uid = database::insert($sql);
				$this->__construct($activity_uid);
				$this->load();
				$arrLocales = language::getPrefixes();
				if (count($arrLocales) > 0) {
					foreach ($arrLocales as $luid => $arrData) {
						$sql = "INSERT INTO `activity_translation` SET
							activity_uid='{$activity_uid}',
							locale='{$arrData['prefix']}',
							language_uid='{$luid}',
							name='{$_POST["name_" . $arrData['prefix']]}',
							available='{$_POST["available_" . $arrData['prefix']]}'";
						database::insert($sql);
					}
				}
			}

			return $activity_main_uid;
		} else {
			$arrData['name'] = $name;
//			$arrData['available'] = $available;
			$arrData['message'] = 'Please complete all required fields like Name/Exercise/Material/Skill/Unit etc';
		}
		return $arrData;
	}

	public function isValidUpdate($arrData=array()) {
		$name = (isset($arrData['name']) && strlen(trim($arrData['name'])) > 0) ? $arrData['name'] : '';
		$activity_uid = (isset($arrData['activity_uid']) && is_numeric($arrData['activity_uid']) && $arrData['activity_uid'] > 0) ? $arrData['activity_uid'] : '';
		$exercise_type_uid = (isset($arrData['exercise_type_uid']) && is_numeric($arrData['exercise_type_uid']) && $arrData['exercise_type_uid'] > 0) ? $arrData['exercise_type_uid'] : '';
		$material_type_uid = (isset($arrData['material_type_uid']) && is_numeric($arrData['material_type_uid']) && $arrData['material_type_uid'] > 0) ? $arrData['material_type_uid'] : '';
		$skill_level_uid = (isset($arrData['skill_level_uid']) && strlen(trim($arrData['skill_level_uid'])) > 0) ? $arrData['skill_level_uid'] : '';
		$unit_uid = (isset($arrData['unit_uid']) && strlen(trim($arrData['unit_uid'])) > 0) ? $arrData['unit_uid'] : '';
		$available = (isset($arrData['available']) && strlen(trim($arrData['available'])) > 0) ? $arrData['available'] : '';
		if ($activity_uid != '' && $name != ''  && $exercise_type_uid != '' && $material_type_uid != '' && $skill_level_uid != '' && $available != '' && $unit_uid != '') {
			$this->__construct($activity_uid);
			$this->load();
			$this->set_name($name);
			$this->set_exercise_type_uid($exercise_type_uid);
			$this->set_material_type_uid($material_type_uid);
			$this->set_skill_level_uid($skill_level_uid);
			$this->set_unit_uid($unit_uid);
			$this->set_available($available);
			$this->save();
			$arrLocales = language::getPrefixes();
			if (count($arrLocales) > 0) {
				foreach ($arrLocales as $luid => $arrData) {
					$sql = "UPDATE 
								`activity_translation` 
							SET
								name='{$_POST["name_" . $arrData['prefix']]}',
								available='{$_POST["available_" . $arrData['prefix']]}'
							WHERE 
								activity_uid='{$activity_uid}'
							AND 
								locale='{$arrData['prefix']}'
							AND 
								language_uid='{$luid}'";
					database::query($sql);
				}
			}
			return true;
		} else {
			$arrData['name'] = $name;
			$arrData['available'] = $available;
			$arrData['message'] = 'Please complete all required fields like Name/Exercise/Material/Skill/Unit etc';
		}
		return $arrData;
	}

}

?>