<?php

class language_translation extends generic_object {

	public $arrForm = array();

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function getTranslationForm($uid) {
		if (isset($_POST['save_changes'])) {
			foreach ($_POST['lang'] as $idx => $val) {
				$WHERE = "WHERE ";
				$WHERE.="`language_uid` = '" . mysql_real_escape_string($uid) . "' ";
				$WHERE.="AND ";
				$WHERE.="`language_translation_id` = '" . mysql_real_escape_string($idx) . "'";
				$sql = "SELECT ";
				$sql.="`uid` ";
				$sql.="FROM ";
				$sql.="`language_translation` ";
				$sql.=$WHERE;
				$sql.=" LIMIT 1";
				$result = database::query($sql);
				if (mysql_num_rows($result)) {
					$sql = " UPDATE ";
					$sql.="`language_translation` ";
					$sql.="SET ";
					$sql.="`name` = '" . mysql_real_escape_string($val) . "' ";
					$sql.=$WHERE;
					$sql.=" LIMIT 1";
					database::query($sql);
				} elseif (!empty($val)) {
					$sql = "INSERT ";
					$sql.="INTO ";
					$sql.="`language_translation` ";
					$sql.="(";
					$sql.="`name`, ";
					$sql.="`language_uid`, ";
					$sql.="`language_translation_id`";
					$sql.=") VALUES ( ";
					$sql.="'" . mysql_real_escape_string($val) . "', ";
					$sql.="'" . mysql_real_escape_string($uid) . "', ";
					$sql.="'" . mysql_real_escape_string($idx) . "'";
					$sql.=")";
					database::query($sql);
				}
			}
		}
		$query = "SELECT *, ";
		$query.="( ";
		$query.="SELECT ";
		$query.="`name` ";
		$query.="FROM ";
		$query.="`language_translation` ";
		$query.="WHERE ";
		$query.="`language_translation_id` = `language`.uid ";
		$query.="AND ";
		$query.="`language_uid` = '" . $uid . "'";
		$query.="LIMIT 1 ";
		$query.=") AS `Lvalue` ";
		$query.="FROM ";
		$query.="`language` ";
		$query.="ORDER BY `name`";
		$res = database::query($query);
		$form_rows = array();
		if ($res && mysql_error() == '' && mysql_num_rows($res) > 0) {
			$formBody = new xhtml('body.admin.language.translation');
			$formBody->load();
			while ($row = mysql_fetch_assoc($res)) {
				$panel = new xhtml('body.admin.translation.element');
				$panel->load();
				$data['lable'] = $row['name'];
				$data['input_name'] = 'lang[' . $row['uid'] . ']';
				if ($row['Lvalue'] != NULL) {
					$data['input_value'] = $row['Lvalue'];
				} else {
					$data['input_value'] = '';
				}
				$panel->assign($data);
				$form_rows[] = $panel->get_content();
			}
			$formBody->assign('form.elements', implode('', $form_rows));
			$formBody->assign('form.action', $_SERVER['REQUEST_URI']);
			return $formBody->get_content();
		}
		return ' ';
	}

}

?>