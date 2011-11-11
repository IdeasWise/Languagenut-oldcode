<?php
class wordbank extends generic_object {
	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}
	public function exists($term_uid=null) {
		$sql = "SELECT * FROM `wordbank` WHERE `uid`='" . $term_uid . "' LIMIT 1";
		$res = database::query($sql);
		if ($res && mysql_error() == '' && mysql_num_rows($res) > 0) {
			return true;
		} else {
			return false;
		}
	}
	public static function getRandomWord($locale='') {
		$response = null;
		// get language from the locale
		$objLanguage = new language();
		$language_uid = $objLanguge->CheckLocale($locale);
		if ($language_uid !== false) {
			// get a list of the words in this language uid
			$terms = self::getByLanguageUid($language_uid);
			// shuffle and take the top one
			if (count($terms) > 0) {
				shuffle($terms);
				$response = $terms[0];
			} else if ($language_uid != 14) {
				$terms = self::getByLanguageUid(14);
				if (count($terms) > 0) {
					shuffle($terms);
					$response = $terms[0];
				} else {
					$response = 'rand'; // default?
				}
			}
		} else {
			$response = 'rand';
		}
		return $response;
	}
	public static function getByLanguageUid($language_uid = null) {
		$response = array();
		$query = "SELECT ";
		$query.= "`uid`, ";
		$query.= "`language_uid`, ";
		$query.= "`term` ";
		$query.= "FROM ";
		$query.= "`wordbank` ";
		$query.= "WHERE ";
		$query.= "`language_uid`='" . mysql_real_escape_string($language_uid) . "' ";
		$query.= "ORDER BY ";
		$query.= "`term` ASC";
		$result = database::query($query);
		if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
			while ($row = mysql_fetch_assoc($result)) {
				$response[$row['uid']] = array(
					'term' => stripslashes($row['term'])
				);
			}
		}
		return $response;
	}
	public function isValidCreate($arrData=array()) {
		$term = (isset($arrData['add_word']) && strlen(trim($arrData['add_word'])) > 0) ? trim($arrData['add_word']) : '';
		$language_uid = (isset($arrData['language_uid']) && (int) $arrData['language_uid'] > 0) ? (int) $arrData['language_uid'] : '';
		if ($term != '' && $language_uid != '') {
			$query = "INSERT INTO `wordbank` (";
			$query.= "`language_uid`, ";
			$query.= "`term` ";
			$query.= ") VALUES (";
			$query.= "'" . mysql_real_escape_string($language_uid) . "', ";
			$query.= "'" . mysql_real_escape_string($term) . "' ";
			$query.= ")";
			$result = database::query($query);
			if ($result && mysql_error() == '') {
				return true;
			} else {
				echo $query;
				echo mysql_error();
				return mysql_error();
			}
		} else {
			return false;
		}
	}
	public function isValidUpdate($arrData=array()) {
		foreach ($arrData as $key => $val) {
			$name = explode('_', $key);
			if (count($name) == 3 && $name[0] == 'word') {
				$word_uid = (int) $name[1];
				$language_uid = (int) $name[2];
				$query = "SELECT COUNT(`uid`) FROM `wordbank` WHERE `uid`='" . $word_uid . "' AND `language_uid`='" . $language_uid . "' LIMIT 1";
				$result = database::query($query);
				if ($result && mysql_error() == '') {
					$row = mysql_fetch_array($result);
					if ($row[0] > 0) {
						$query = "UPDATE `wordbank` SET `term`='" . mysql_real_escape_string($val) . "' WHERE `language_uid`='" . $language_uid . "' AND `uid`='" . $word_uid . "'";
						$result = database::query($query);
					} else {
						$query = "INSERT INTO `wordbank` (`term`,`language_uid`) VALUES ('" . mysql_real_escape_string($val) . "','" . $language_uid . "')";
						$result = database::query($query);
					}
				} else {
					return false;
				}
			} else {
				return false;
			}
		}
	}
}
?>