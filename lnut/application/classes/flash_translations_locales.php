<?php

class flash_translations_locales extends generic_object {

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function exists($uid=null) {
		// use return $this->is_valid(); instead
		$query = "SELECT ";
		$query.="`uid` ";
		$query.="FROM ";
		$query.="`flash_translations_locales` ";
		$query.="WHERE ";
		$query.="`uid`='" . $uid . "' LIMIT 1";
		$res = database::arrQuery($query, 1);
		if (count($res)>0) {
			return true;
		} else {
			return false;
		}
	}

	public function getListByLanguageUid($language_uid=null) {
		$data = array();
		$sql = "SELECT ";
		$sql.= "`flash_translations_locales`.`uid`, ";
		$sql.= "`flash_translations_locales`.`tag_uid`, ";
		$sql.= "`flash_translations_locales`.`translation_text`, ";
		$sql.= "`flash_translations_tags`.`tag_name` ";
		$sql.= "FROM `flash_translations_locales`, `flash_translations_tags` ";
		$sql.= "WHERE ";
		$sql.= "`flash_translations_locales`.`tag_uid`=`flash_translations_tags`.`uid` ";
		$sql.= "AND `flash_translations_locales`.`support_language_uid`='" . mysql_real_escape_string($language_uid) . "' ";
		$sql.= "ORDER BY `flash_translations_tags`.`tag_name` ASC";
		$result = database::arrQueryByUid($sql, array(), 1);
		
		
		if (count($result) > 0) {
			foreach($result  as $row){
				$data[$row['uid']] = array(
					'tag_uid' => $row['tag_uid'],
					'translation_text' => ((strlen($row['translation_text']) > 0) ? $row['translation_text'] : flash_translations_locales::getDefaultTranslation($row['tag_uid'], 14)),
					'tag_name' => $row['tag_name']
				);
			}
		}
		return $data;
	}

	public static function getTagsByLanguageUid($language_uid=null) {
		$data = array();
		$sql = "SELECT ";
		$sql.="`uid`, ";
		$sql.="`tag_uid`, ";
		$sql.="`translation_text` ";
		$sql.="FROM ";
		$sql.="`flash_translations_locales` ";
		$sql.="WHERE ";
		$sql.="`support_language_uid`='" . $language_uid . "'";
		
		$keyMap=array();
		$result = database::arrQueryByUid($sql, $keyMap, 1);
		
		if (count($result) > 0) {
			foreach($result as $row){
				$data[$row['uid']] = array(
					'tag_uid' => $row['tag_uid'],
					'translation_text' => stripslashes($row['translation_text'])
				);
			}
		}
		return $data;
	}

	public function getDefaultTranslation($tag_uid=null, $default_language_uid=null) {
		$response = '';
		$sql = "SELECT ";
		$sql.= "`translation_text` ";
		$sql.= "FROM ";
		$sql.= "`flash_translations_locales` ";
		$sql.= "WHERE ";
		$sql.= "`support_language_uid`='" . $default_language_uid . "' ";
		$sql.= "AND `tag_uid`='" . $tag_uid . "' ";
		$sql.= "LIMIT 1";
		$res = database::arrQuery($sql, 1);
		
		if (count($res) > 0) {
			$row = $res[0];
			$response = stripslashes($row['translation_text']);
		}
		return $response;
	}

	public function updateFlashTranslations() {
		if (count($_POST) > 0) {
			foreach ($_POST as $key => $val) {


				$name = explode('_', $key);
				if (count($name) == 3 && $name[0] == 'tag') {
					$tag_uid = (int) $name[1];
					$language_uid = (int) $name[2];
					$query = "SELECT ";
					$query.="`uid` ";
					$query.="FROM ";
					$query.="`flash_translations_locales` ";
					$query.="WHERE ";
					$query.="`tag_uid`='" . mysql_real_escape_string($tag_uid) . "' ";
					$query.="AND ";
					$query.="`support_language_uid`='" . mysql_real_escape_string($language_uid) . "' ";
					$query.="LIMIT 1";
					$result = database::query($query);
					if (mysql_error()=='' && mysql_num_rows($result)) {
						$query = "UPDATE ";
						$query.="`flash_translations_locales` ";
						$query.="SET ";
						$query.="`translation_text`='" . mysql_real_escape_string($val) . "' ";
						$query.="WHERE ";
						$query.="`support_language_uid`='" . mysql_real_escape_string($language_uid) . "' ";
						$query.="AND ";
						$query.="`tag_uid`='" . mysql_real_escape_string($tag_uid) . "'";
						$result = database::query($query);
						if (mysql_error() != '') {
							echo mysql_error();
						}
					} else {
						$query = "INSERT INTO ";
						$query.="`flash_translations_locales` (";
						$query.="`tag_uid`,";
						$query.="`translation_text`,";
						$query.="`support_language_uid`";
						$query.=") VALUES (";
						$query.="'" . mysql_real_escape_string($tag_uid) . "',";
						$query.="'" . mysql_real_escape_string($val) . "',";
						$query.="'" . mysql_real_escape_string($language_uid) . "'";
						$query.=")";
						$result = database::query($query);
						if (mysql_error() != '') {
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
			"field" => "support_language_uid",
			"value" => $languageUid
		);
		$enUid = language::getUidFromPrefix("en");
		$where = ($enUid) ? " AND support_language_uid='" . $enUid . "'" : "";
		$groupBy = " GROUP BY tag_uid";
		$this->copyToTranslation(__CLASS__, __CLASS__, $where, $arrValues, $groupBy);
	}

}

?>