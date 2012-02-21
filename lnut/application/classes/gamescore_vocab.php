<?php

class gamescore_vocab extends generic_object {

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function add($gamescore_uid='', $language_uid='', $support_uid='', $arrVocab=array()) {
		foreach ($arrVocab as $vocab_uid => $vocab_array) {
			$this->set_vocab_uid($vocab_uid);
			$this->set_language_uid($language_uid);
			$this->set_gamescore_uid($gamescore_uid);
			$this->set_score_right($vocab_array['for']);
			$this->set_score_wrong($vocab_array['against']);
			$this->set_time($vocab_array['time']);
			$query = "SELECT ";
			$query.="`name` ";
			$query.="FROM ";
			$query.="`sections_vocabulary_translations` ";
			$query.="WHERE ";
			$query.="`term_uid`='" . $vocab_uid . "' ";
			$query.="AND ";
			$query.="`language_id`='" . $language_uid . "' ";
			$query.="LIMIT 1";
			$result = database::query($query);
			if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
				$row = mysql_fetch_assoc($result);
				$this->set_word_translated(stripslashes($row['name']));
			} else {
				//mail('andrew@languagenut.com', 'lnut: gamescore_vocab', mysql_error(), 'From: info@languagenut.com');
			}
			$query = "SELECT ";
			$query.="`name` ";
			$query.="FROM ";
			$query.="`sections_vocabulary_translations` ";
			$query.="WHERE ";
			$query.="`term_uid`='" . $vocab_uid . "' ";
			$query.="AND ";
			$query.="`language_id`='" . $support_uid . "' ";
			$query.="LIMIT 1";
			$result = database::query($query);
			if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
				$row = mysql_fetch_assoc($result);
				$this->set_word_english(stripslashes($row['name']));
			} else {
				//mail('andrew@languagenut.com', 'lnut: gamescore_vocab', mysql_error(), 'From: info@languagenut.com');
			}
			$this->insert();
		}
	}

	public function getByGame($game_uid=null) {
		$arrResult = array();
		$sql = "SELECT ";
		$sql.= "`uid`, ";
		$sql.= "`vocab_uid`, ";
		$sql.= "`gamescore_uid`, ";
		$sql.= "`word_english`, ";
		$sql.= "`word_translated`, ";
		$sql.= "`time`, ";
		$sql.= "`score_right`, ";
		$sql.= "`score_wrong` ";
		$sql.= "FROM ";
		$sql.= "`gamescore_vocab` ";
		$sql.= "WHERE ";
		$sql.= "`gamescore_uid`='" . mysql_real_escape_string($game_uid) . "' ";
		$sql.= "ORDER BY ";
		$sql.= "`vocab_uid` ASC";
		$res = database::query($sql);
		if ($res && mysql_error() == '' && mysql_num_rows($res)) {
			while ($row = mysql_fetch_assoc($res)) {
				$arrResult[$row['uid']] = array(
					'vocab_uid' => $row['vocab_uid'],
					'gamescore_uid' => $row['gamescore_uid'],
					'word_english' => stripslashes($row['word_english']),
					'word_translated' => stripslashes($row['word_translated']),
					'time' => $row['time'],
					'score_right' => $row['score_right'],
					'score_wrong' => $row['score_wrong']
				);
			}
		}
		return $arrResult;
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