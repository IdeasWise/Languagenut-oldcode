<?php

class game extends generic_object {

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function getGamesByGameNumber() {
		$query = "SELECT ";
		$query.="`uid`, ";
		$query.="`name`, ";
		$query.="`game_number` ";
		$query.="FROM ";
		$query.="`game` ";
		$query.="ORDER BY `game_number`";
		return database::arrQuery($query);
	}
	public function getListByName($OrderBy = 'name', $playable = false) {
		$response = false;
		$sql = "SELECT ";
		$sql.= "`uid`, ";
		$sql.= "`name`, ";
		$sql.= "`class_name`, ";
		$sql.= "`active_trynow`, ";
		$sql.= "`active_subscription` ";
		$sql.= "FROM ";
		$sql.= "`game` ";
		if ($playable) {
			$sql.=" where `playable`='1' ";
		}
		$sql.= "ORDER BY ";
		$sql.= "`" . $OrderBy . "` ASC";
		$keyMap=array(
					'name' => 'name',
					'trynow' => 'active_trynow',
					'subscription' => 'active_subscription',
					'class_name' => 'class_name'
				);
		$response = database::arrQueryByUid($sql, $keyMap);
//		$res = database::query($sql);
//		if ($res && mysql_error() == '' && mysql_num_rows($res) > 0) {
//			$response = array();
//			while ($row = mysql_fetch_assoc($res)) {
//				$response[$row['uid']] = array(
//					'name' => stripslashes($row['name']),
//					'trynow' => $row['active_trynow'],
//					'subscription' => $row['active_subscription'],
//					'class_name' => $row['class_name']
//				);
//			}
//		}
		return $response;
	}

	public function getListBySupportName($support_language_uid='') {
		$response = false;
		$sql = "SELECT ";
		$sql.= "`game`.`uid`, ";
		$sql.= "`game`.`tagname`, ";
		$sql.= "`game_translation`.`name` ";
		$sql.= "FROM ";
		$sql.= "`game`, ";
		$sql.= "`game_translation` ";
		if ($support_language_uid != '') {
			$sql.= "WHERE ";
			$sql.= "`game_translation`.`language_uid`='" . $support_language_uid . "' ";
		}
		$sql.= "ORDER BY ";
		$sql.= "`game_translation`.`game_uid` = `game`.`uid` ";
		
		$keyMap=array();
		$response = database::arrQueryByUid($sql, $keyMap, 1);
		
//		if ($res && mysql_error() == '' && mysql_num_rows($res) > 0) {
//			$response = array();
//			while ($row = mysql_fetch_assoc($res)) {
//				$response[$row['uid']] = array(
//					'tagname' => stripslashes($row['tagname']),
//					'name' => stripslashes($row['name'])
//				);
//			}
//		}
		return $response;
	}

	public function isValidCreate($arrData=array()) {
		$name = (isset($arrData['name']) && strlen(trim($arrData['name'])) > 0) ? $arrData['name'] : '';
		$active_trynow = (isset($arrData['active_trynow']) && (int) $arrData['active_trynow'] > -1) ? $arrData['active_trynow'] : '';
		$active_subscription = (isset($arrData['active_subscription']) && (int) $arrData['active_subscription'] > -1) ? $arrData['active_subscription'] : '';
		if ($name != '' && $active_trynow != '' && $active_subscription != '') {
			$this->arrFields['name']['Value'] = $name;
			$this->arrFields['active_trynow']['Value'] = $active_trynow;
			$this->arrFields['active_subscription']['Value'] = $active_subscription;
			$this->insert();
			return true;
		} else {
			$arrData['name'] = $name;
			$arrData['active_trynow'] = $active_trynow;
			$arrData['active_subscription'] = $active_subscription;
			$arrData['message'] = 'Please complete all fields';
		}
		return $arrData;
	}

	public function isValidUpdate($arrData=array()) {
		$game_uid = (isset($arrData['game_uid']) && (int) $arrData['game_uid'] > 0) ? $arrData['game_uid'] : '';
		$name = (isset($arrData['name']) && strlen(trim($arrData['name'])) > 0) ? $arrData['name'] : '';
		$active_trynow = (isset($arrData['active_trynow']) && (int) $arrData['active_trynow'] > -1) ? $arrData['active_trynow'] : '';
		$active_subscription = (isset($arrData['active_subscription']) && (int) $arrData['active_subscription'] > -1) ? $arrData['active_subscription'] : '';
		if ($game_uid != '' && $name != '' && $active_trynow != '' && $active_subscription != '') {
			$this->__construct($game_uid);
			$this->load();
			$this->arrFields['name']['Value'] = $name;
			$this->arrFields['active_trynow']['Value'] = $active_trynow;
			$this->arrFields['active_subscription']['Value'] = $active_subscription;
			$this->save();
			return true;
		} else {
			$arrData['name'] = $name;
			$arrData['active_trynow'] = $active_trynow;
			$arrData['active_subscription'] = $active_subscription;
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

	public function getGameScoreHeader() {
		$games = array();
		$games = $this->getListByName('game_number', true);
		$html = '';
		if (!empty($games)) {
			foreach ($games as $uid => $data) {
				$html .= '<th title="Game: ' . $data['name'] . '" class="' . $data['class_name'] . '">&nbsp;</th>';
			}
		}
		return $html;
	}

	public function getGameKeys() {
		$query ="SELECT ";
		$query.="`uid`, ";
		$query.="`tagname` ";
		$query.="FROM ";
		$query.="`game`";
		
		$result = database::query($query);
		$arrGameKeys = array();
		if(mysql_error() == '' && mysql_num_rows($result) && $result) {
			while($row=mysql_fetch_array($result)) {
				$arrGameKeys[$row['uid']] = $row['tagname'];
			}
		}
		return $arrGameKeys;
	}

	public function getFilteredGames($arrFilter=array()) {
		$query = "SELECT ";
		$query.="`uid`, ";
		$query.="`name` ";
		$query.="FROM ";
		$query.="`game` ";
		if(count($arrFilter)) {
			$query.="WHERE ";
			$query.="`uid` IN (".implode(',',$arrFilter).") ";
		}
		$query.="ORDER BY `game_number`";
		return database::arrQuery($query);
	}
}

?>