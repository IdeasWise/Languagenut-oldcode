<?php

class admin_messages extends generic_object {

	public $arrForm = array();

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function getList() {
		$query = "SELECT ";
		$query.="COUNT(`AM`.`uid`) ";
		$query.="FROM ";
		$query.="`admin_messages` AS `AM`, ";
		$query.="`admin_messages_module` AS `AM_MODULE` ";
		$query.="WHERE ";
		$query.="`AM`.`module_uid` = `AM_MODULE`.`uid`";
		$this->setPagination($query);
		$query = "SELECT ";
		$query.="`AM`.*,";
		$query.="`AM_MODULE`.`name` AS `module` ";
		$query.="FROM ";
		$query.="`admin_messages` AS `AM`, ";
		$query.="`admin_messages_module` AS `AM_MODULE` ";
		$query.="WHERE ";
		$query.="`AM`.`module_uid` = `AM_MODULE`.`uid` ";
		$query.="ORDER BY `AM`.`term_caption` ";
		$query.= "LIMIT " . $this->get_limit();
		return database::arrQuery($query);
	}

	public function term_tag_exist($term_tag = "") {
		$found = false;
		$term_tag = mysql_real_escape_string($term_tag);
		$sql = "SELECT ";
		$sql.= "`uid` ";
		$sql.= "FROM ";
		$sql.= "`admin_messages` ";
		$sql.= "WHERE ";
		$sql.= "`term_tag` = '$term_tag' ";
		if ($this->get_uid() != null) {
			$sql .= " AND `uid` != '" . $this->get_uid() . "'";
		}
		$sql .= " LIMIT 1";
		$result = database::query($sql);
		if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
			$found = true;
		}
		return $found;
	}

	public function doSave() {
		if ($this->isValidateFormData() == true) {
			if (isset($_POST['uid']) && is_numeric($_POST['uid']) && $_POST['uid'] > 0) {
				$this->save();
			} else {
				$insert = $this->insert();
				$this->arrForm['uid'] = $insert;
			}
			return true;
		} else {
			return false;
		}
	}

	public function isValidateFormData() {
		if (isset($_POST['uid']) && is_numeric($_POST['uid']) && $_POST['uid'] > 0) {
			parent::__construct($_POST['uid'], __CLASS__);
			$this->load();
		}
		$term_tag = (isset($_POST['term_tag'])) ? trim($_POST['term_tag']) : '';
		$module_uid = (isset($_POST['module_uid'])) ? trim($_POST['module_uid']) : '0';
		$term_caption = (isset($_POST['term_caption'])) ? trim($_POST['term_caption']) : '';
		$arrMessages = array();
		if (strlen($term_tag) < 3 || strlen($term_tag) > 255) {
			$arrMessages['error_term_tag'] = "Term tag must be 3 to 255 characters in length.";
		} else if (!validation::isValid('text', $term_tag)) {
			$arrMessages['error_term_tag'] = "Please enter valid term tag.";
		}
		if ($module_uid == '' || $module_uid == "0") {
			$arrMessages['error_module_uid'] = "Please choose module.";
		} else if (!validation::isValid('int', $module_uid)) {
			$arrMessages['error_module_uid'] = "Please choose valid module.";
		}
		if (strlen($term_caption) < 3 || strlen($term_caption) > 255) {
			$arrMessages['error_term_caption'] = "Please enter term caption.";
		} else if (!validation::isValid('text', $term_caption)) {
			$arrMessages['error_term_caption'] = "Please enter valid term caption.";
		}
		if (count($arrMessages) == 0) {
			$this->set_term_tag($term_tag);
			$this->set_module_uid($module_uid);
			$this->set_term_caption($term_caption);
		} else {
			$strMessage = '';
			foreach ($arrMessages as $index => $value) {
				$this->arrForm[$index] = 'label_error';
				$strMessage .= '<li>' . $value . '</li>';
			}
			$this->arrForm['message_error'] = '<p>Please correct the errors below:</p><ul>' . $strMessage . '</ul>';
		}
		foreach ($_POST as $index => $value) {
			$this->arrForm[$index] = $value;
		}
		if (count($arrMessages) == 0) {
			return true;
		} else {
			return false;
		}
	}

	public function getModuledropdown($uid = 0) {
		$arrRows = array();
		$query = "SELECT ";
		$query.="* ";
		$query.="FROM ";
		$query.="`admin_messages_module` ";
		$query.="ORDER BY `name`";
		$result = database::query($query);
		if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
			while ($row = mysql_fetch_array($result)) {
				$arrRows[$row['uid']] = $row['name'];
			}
		}
		return format::to_select(array("name" => "module_uid", "id" => "module_uid", "options_only" => false), $arrRows, $uid);
	}

	public function get_translation_addForm() {
		$body = make::tpl('body.admin.tabs');
		$arrTabs = array();
		$arrTabs_li = array();
		$arrTabs_divs = array();
		//	ADD TERM FORM SECTION STARTS HERE...
		$AddForm = make::tpl('body.admin.translation.term.add.edit');
		$message = '';
		if (isset($_POST['button_admin_translation_term'])) {
			if ($this->doSave() === false) {
				$message = $this->arrForm['message_error'];
			} else {
				$this->redirectToDynamic('/multilingual/edit/' . @$this->arrForm['uid']); // redirect to user list
			}
		}
		$array = array(
			'module_uid' => $this->getModuledropdown(),
			'message' => $message
		);
		$AddForm->assign($array);
		$arrTabs_li[] = '<li><a href="#tab-add"><span>Add New Term</span></a></li>';
		$arrTabs_divs[] = '<div id="tab-add">' . $AddForm->get_content() . '</div>';

		//	ADD TERM FORM SECTION ENDS HERE...
		$body->assign(
				array(
					'tabs.lis' => implode('', $arrTabs_li),
					'tabs.divs' => implode('', $arrTabs_divs),
					'page_title' => 'Admin Translation List'
				)
		);
		return $body->get_content();
	}

	public function get_translation_tab($object) {
		$uid = $object->get_uid();
		$body = make::tpl('body.admin.tabs');
		if (isset($_POST['button_admin_translation_tab'])) {
			$WHERE = " WHERE ";
			$WHERE.="`locale` = '" . mysql_real_escape_string($_POST['locale']) . "' ";
			$WHERE.="AND ";
			$WHERE.="`message_uid` = '" . mysql_real_escape_string($_POST['message_uid']) . "' ";
			$CheckQuery = "SELECT ";
			$CheckQuery.="`uid` ";
			$CheckQuery.="FROM ";
			$CheckQuery.="`admin_messages_translations` ";
			$CheckQuery.=$WHERE;
			$check = database::query($CheckQuery);
			if (mysql_num_rows($check) > 0) {
				$update = "UPDATE ";
				$update.="`admin_messages_translations` ";
				$update.="SET ";
				$update.="`text` = '" . mysql_real_escape_string(addslashes($_POST['text'])) . "' ";
				$update.=$WHERE;
				database::query($update);
			} else {
				$insert = "INSERT INTO ";
				$insert.="`admin_messages_translations` ";
				$insert.="SET ";
				$insert.="`message_uid` = '" . mysql_real_escape_string($_POST['message_uid']) . "', ";
				$insert.="`locale` = '" . mysql_real_escape_string($_POST['locale']) . "', ";
				$insert.="`text` = '" . mysql_real_escape_string(addslashes($_POST['text'])) . "' ";
				database::query($insert);
			}
		}
		$query = 'SELECT ';
		$query.='`uid`,';
		$query.='`prefix` ';
		$query.='FROM ';
		$query.='`language` ';
		$query.='ORDER BY `prefix`';
		$result = database::query($query);
		$arrTabs = array();
		$arrTabs_li = array();
		$arrTabs_divs = array();
		//	EDIT TERM FORM SECTION STARTS HERE...
		$EditForm = make::tpl('body.admin.translation.term.add.edit');
		$message = '';
		if (isset($_POST['button_admin_translation_term'])) {
			if ($this->doSave() === false) {
				$message = $this->arrForm['message_error'];
			} else {
				$this->redirectToDynamic('/multilingual/edit/' . @$this->arrForm['uid']); // redirect to user list
			}
		}
		$array = array(
			'uid' => $object->get_uid(),
			'term_tag' => $object->get_term_tag(),
			'term_caption' => stripslashes($object->get_term_caption()),
			'module_uid' => $this->getModuledropdown($object->get_module_uid()),
			'message' => $message
		);
		$EditForm->assign($array);
		$arrTabs_li[] = '<li><a href="#tab-edit"><span>Edit Term</span></a></li>';
		$arrTabs_divs[] = '<div id="tab-edit">' . $EditForm->get_content() . '</div>';

		//	EDIT TERM FORM SECTION ENDS HERE...
		if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
			while ($row = mysql_fetch_assoc($result)) {
				$query = "SELECT ";
				$query.="`AM`.`term_caption`, ";
				$query.="( ";
				$query.="SELECT ";
				$query.="`text` ";
				$query.="FROM ";
				$query.="`admin_messages_translations` ";
				$query.="WHERE ";
				$query.="`locale` = '" . $row['prefix'] . "' ";
				$query.="AND ";
				$query.="`message_uid` = '" . $uid . "' ";
				$query.=") AS `text` ";
				$query.="FROM ";
				$query.="`admin_messages` AS `AM` ";
				$query.="WHERE ";
				$query.="`uid` = '" . $uid . "' ";
				$data = array();
				$data = database::arrQuery($query);
				$data[0]['locale'] = $row['prefix'];
				$data[0]['message_uid'] = $uid;
				$xhtml = make::tpl('body.admin.translation.tab.edit.form');
				$row['form.action'] = $_SERVER['REQUEST_URI'] . '#main-3';
				$xhtml->assign($data[0]);
				$arrTabs_li[] = '<li><a href="#tab-' . $row['uid'] . '"><span>' . $row['prefix'] . '</span></a></li>';
				$arrTabs_divs[] = '<div id="tab-' . $row['uid'] . '">' . $xhtml->get_content() . '</div>';
			}
		}
		$body->assign(
				array(
					'tabs.lis' => implode('', $arrTabs_li),
					'tabs.divs' => implode('', $arrTabs_divs),
					'page_title' => '<a href="' . config::admin_uri('multilingual/list/') . '">Admin Translation List</a> &gt; Edit'
				)
		);
		return $body->get_content();
	}

}

?>