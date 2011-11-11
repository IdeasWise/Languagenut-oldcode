<?php

class game_translation extends generic_object {

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function getByLanguageUid($language_uid=null) {
		$arrResponse = array();
		if ($language_uid) {
			$sql = "SELECT ";
			$sql.= "`uid`, ";
			$sql.= "`game_uid`, ";
			$sql.= "`language_uid`, ";
			$sql.= "`name` ";
			$sql.= "FROM ";
			$sql.= "`game_translation` ";
			$sql.= "WHERE ";
			$sql.= "`language_uid`='" . mysql_real_escape_string($language_uid) . "' ";
			$sql.= "ORDER BY ";
			$sql.= "`game_uid` ASC";
//			$res = database::query($sql);
			$keyMap = array(
						'game_uid' => 'game_uid',
						'language_uid' => 'language_uid',
						'name' => 'name'
					);
			$arrResponse = database::arrQueryByUid($sql, $keyMap,1);
//			
//			if ($res && mysql_error() == '' && mysql_num_rows($res) > 0) {
//				while ($row = mysql_fetch_assoc($res)) {
//					$arrResponse[$row['uid']] = array(
//						'game_uid' => $row['game_uid'],
//						'language_uid' => $row['language_uid'],
//						'name' => stripslashes($row['name'])
//					);
//				}
//			}
		}
		return $arrResponse;
	}

	public function updateGameTranslation() {
		if (count($_POST) > 0) {
			foreach ($_POST as $key => $val) {
				$name = explode('_', $key);
				if (count($name) == 3 && $name[0] == 'game') {
					$game_uid = (int) $name[1];
					$language_uid = (int) $name[2];
					$query = "SELECT ";
					$query.="COUNT(`uid`) ";
					$query.="FROM ";
					$query.="`game_translation` ";
					$query.="WHERE ";
					$query.="`game_uid`='" . mysql_real_escape_string($game_uid) . "' ";
					$query.="AND ";
					$query.="`language_uid`='" . mysql_real_escape_string($language_uid) . "' ";
					$query.="LIMIT 1";
					$result = database::query($query);
					if ($result && mysql_error() == '') {
						$row = mysql_fetch_array($result);
						if ($row[0] > 0) {
							$query = "UPDATE ";
							$query.="`game_translation` ";
							$query.="SET ";
							$query.="`name`='" . mysql_real_escape_string($val) . "' ";
							$query.="WHERE ";
							$query.="`language_uid`='" . mysql_real_escape_string($language_uid) . "' ";
							$query.="AND ";
							$query.="`game_uid`='" . mysql_real_escape_string($game_uid) . "' ";
							$query.="LIMIT 1";
							$result = database::query($query,1);
							echo mysql_error();
						} else {
							$query = "INSERT INTO ";
							$query.="`game_translation` (";
							$query.="`game_uid`,";
							$query.="`language_uid`,";
							$query.="`name`";
							$query.=") VALUES (";
							$query.="'" . mysql_real_escape_string($game_uid) . "',";
							$query.="'" . mysql_real_escape_string($language_uid) . "',";
							$query.="'" . mysql_real_escape_string($val) . "'";
							$query.=")";
							$result = database::query($query,1);
							echo mysql_error();
						}
					}
				}
			}
		}
	}

	public function duplicateToLanguage($locale, $languageUid) {
		$arrValues = array();

		$arrValues[] = array(
			"field" => "language_uid",
			"value" => $languageUid
		);
		$enUid = language::getUidFromPrefix("en");
		$where = ($enUid) ? "AND language_uid='" . $enUid . "'" : "";
		$groupBy = " GROUP BY game_uid";
		$this->copyToTranslation(__CLASS__, __CLASS__, $where, $arrValues, $groupBy);
	}

}

?>