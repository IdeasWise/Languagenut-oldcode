<?php

class page_messages extends generic_object {

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public static function getAllByTagName() {
		$data = array();
		$sql = "SELECT ";
		$sql.= "`uid`, ";
		$sql.= "`tag`, ";
		$sql.= "`description` ";
		$sql.= "FROM ";
		$sql.= "`page_messages` ";
		$sql.= "ORDER BY ";
		$sql.= "`tag` ASC";
		$result = database::query($sql);
		if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
			while ($row = mysql_fetch_assoc($result)) {
				$data[$row['uid']] = array(
					'tag' => $row['tag'],
					'description' => stripslashes($row['description'])
				);
			}
		}
		return $data;
	}

	public static function getById($tag_uid=null) {
		$data = array();
		$sql = "SELECT ";
		$sql.= "`uid`, ";
		$sql.= "`tag_name`, ";
		$sql.= "`description` ";
		$sql.= "FROM ";
		$sql.= "`flash_translations_tags` ";
		$sql.= "WHERE ";
		$sql.= "`uid`='" . mysql_real_escape_string($tag_uid) . "' ";
		$sql.= "LIMIT 1";
		$data= database::arrQuery($sql, 1);
		
		return (isset($data[0]))?$data[0]:array();
	}

	public function updatePageMessageTranslations() {
		if (isset($_POST['form_submit_button'])) {
			foreach ($_POST['tags'] as $message_uid => $text) {
				if (trim($text) == '') {
					continue;
				}
				$query = "SELECT ";
				$query .= "`uid` ";
				$query .= "FROM ";
				$query .= "`page_messages_translations` ";
				$query .= "WHERE ";
				$query .= "`message_uid` = '" . mysql_real_escape_string($message_uid) . "' ";
				$query .= "AND ";
				$query .= "`locale` = '" . mysql_real_escape_string($_POST['locale']) . "' ";

				$result = database::query($query);
				if (mysql_error() == '' && mysql_num_rows($result)) {
					$query = "UPDATE ";
					$query .= "`page_messages_translations` ";
					$query .= "SET ";
					$query .= "`text` = '" . mysql_real_escape_string(addslashes($text)) . "' ";
					$query .= "WHERE ";
					$query .= "`message_uid` = '" . mysql_real_escape_string($message_uid) . "' ";
					$query .= "AND ";
					$query .= "`locale` = '" . mysql_real_escape_string($_POST['locale']) . "' ";
					database::query($query);
				} else {
					$query = "INSERT INTO ";
					$query .= "`page_messages_translations` ";
					$query .= "( ";
					$query .= "`message_uid`, ";
					$query .= "`locale`, ";
					$query .= "`text` ";
					$query .= " ) ";
					$query .= "VALUES ";
					$query .= "( ";
					$query .= "'" . mysql_real_escape_string($message_uid) . "', ";
					$query .= "'" . mysql_real_escape_string($_POST['locale']) . "', ";
					$query .= "'" . mysql_real_escape_string(addslashes($text)) . "' ";
					$query .= " ) ";
					database::query($query);
				}
			}
			//output::redirect(config::url('account/message_translations/'));
			output::redirect(config::admin_uri('message_translations/'));
		}
	}

}

?>