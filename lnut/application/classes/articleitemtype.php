<?php

class articleitemtype extends generic_object {

	public function __construct($uid = 0) {
		parent::__construct($uid, 'article_item_type');
	}

	public function getListByLocale($uid, $luid) {
		$response = false;
		$sql = "SELECT ";
		$sql.= " *";
		$sql.= " FROM ";
		$sql.= "`exercise_type_translation` ";
		$sql.= " WHERE ";
		$sql.= " exercise_type_uid='$uid' ";
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
		$sql.= "`article_item_type` ";
		$this->setPagination($sql);
		$sql = "SELECT ";
		$sql.= " * ";
		$sql.= "FROM ";
		$sql.= "`article_item_type` ";
		$sql.= "ORDER BY ";
		$sql.= "`" . $OrderBy . "` ASC";
		$sql.= " LIMIT " . $this->get_limit();
		$res = database::query($sql);
		if ($res && mysql_error() == '' && mysql_num_rows($res) > 0) {
			$response = array();
			while ($row = mysql_fetch_assoc($res)) {
				$response[$row['uid']] = array(
					'name' => stripslashes($row['name']),
					'token' => stripslashes($row['token'])
				);
			}
		}
		return $response;
	}

	public function generateToken($title) {
		$token = format::to_friendly_url($title);
		$i = 0;
		while (1) {
			$sql = "SELECT count(uid) as total FROM `article_template` WHERE token='{$token}'";
			$result = database::query($sql);
			$data = mysql_fetch_assoc($result);
			if ($data["total"] == "0") {
				return $token;
				break;
			}
			$token = format::to_friendly_url($title . $i);
			$i++;
		}
	}

	public function isValidCreate($arrData=array()) {
		$name = (isset($arrData['name']) && strlen(trim($arrData['name'])) > 0) ? $arrData['name'] : '';
		$token = $this->generateToken($name);
		if ($name != '') {
			$sql = "INSERT INTO `article_item_type` SET
				name='{$name}',
				token='{$token}'
				";
			$articleitemtype_uid = database::insert($sql);

			return true;
		} else {
			$arrData['name'] = $name;

			$arrData['message'] = 'Please complete all fields';
		}
		return $arrData;
	}

	public function isValidUpdate($arrData=array()) {
		$articleitemtype_uid = $exercise_type_uid = (isset($arrData['exercise_type_uid']) && (int) $arrData['exercise_type_uid'] > 0) ? $arrData['exercise_type_uid'] : '';
		$name = (isset($arrData['name']) && strlen(trim($arrData['name'])) > 0) ? $arrData['name'] : '';

		if ($exercise_type_uid != '' && $name != '') {
			$this->__construct($exercise_type_uid);
			$this->load();
			$this->arrFields['name']['Value'] = $name;
			$this->save();
			return true;
		} else {
			$arrData['name'] = $name;
			$arrData['message'] = 'Please complete all fields';
		}
		return $arrData;
	}

	public function getFields() {
		$response = array();
		foreach ($this->arrFields as $key => $val) {
			$response[$key] = $this->arrFields[$key]['Value'];
		}
		return $response;
	}

}

?>