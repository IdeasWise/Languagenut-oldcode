<?php

class articletemplate extends generic_object {

	public function __construct($uid = 0) {
		parent::__construct($uid, 'article_template');
	}

	public function getListByLocale($uid, $luid) {
		$response = false;
		$sql = "SELECT ";
		$sql.= " *";
		$sql.= " FROM ";
		$sql.= "`article_template_translation` ";
		$sql.= " WHERE ";
		$sql.= " article_template_uid='$uid' ";
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

	public function getListByname($OrderBy = 'title') {
		$response = false;
		$sql = "SELECT ";
		$sql.= "count(`uid`) ";
		$sql.= "FROM ";
		$sql.= "`article_template` ";
		$this->setPagination($sql);
		$sql = "SELECT ";
		$sql.= " * ";
		$sql.= "FROM ";
		$sql.= "`article_template` ";
		$sql.= "ORDER BY ";
		$sql.= "`" . $OrderBy . "` ASC";
		$sql.= " LIMIT " . $this->get_limit();
		$res = database::query($sql);
		if ($res && mysql_error() == '' && mysql_num_rows($res) > 0) {
			$response = array();
			while ($row = mysql_fetch_assoc($res)) {
				$response[$row['uid']] = array(
					'title' => stripslashes($row['title']),
					'token' => stripslashes($row['token']),
					'available' => stripslashes($row['available']),
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
		$title = (isset($arrData['title']) && strlen(trim($arrData['title'])) > 0) ? $arrData['title'] : '';
		$token = (isset($arrData['title']) && strlen(trim($arrData['title'])) > 0) ? $this->generateToken($title) : '';
		$available = (isset($arrData['available']) && strlen(trim($arrData['available'])) > 0) ? $arrData['available'] : '';
		if ($title != '' && $available != '' && $token != '') {
			$sql = "INSERT INTO `article_template` SET
					title='{$title}',
					token='{$token}',
					available='{$available}'";
			$articletemplate_uid = database::insert($sql);
			$arrLocales = language::getPrefixes();
			if (count($arrLocales) > 0) {
				foreach ($arrLocales as $luid => $arrData) {
					$sql = "INSERT INTO `article_template_translation` SET
					article_template_uid='{$articletemplate_uid}',
					locale='{$arrData['prefix']}',
					language_uid='{$luid}',
					name='{$_POST["name_" . $arrData['prefix']]}'
				";
					database::insert($sql);
				}
			}
			return true;
		} else {
			$arrData['name'] = $title;
			$arrData['available'] = $available;
			$arrData['message'] = 'Please complete all fields';
		}
		return $arrData;
	}

	public function isValidUpdate($arrData=array()) {
		$articletemplate_uid = $article_template_uid = (isset($arrData['article_template_uid']) && (int) $arrData['article_template_uid'] > 0) ? $arrData['article_template_uid'] : '';
		$title = (isset($arrData['title']) && strlen(trim($arrData['title'])) > 0) ? $arrData['title'] : '';
		$available = (isset($arrData['available']) && strlen(trim($arrData['available'])) > 0) ? $arrData['available'] : '';
		if ($article_template_uid != '' && $title != '' && $available != '') {
			$this->__construct($article_template_uid);
			$this->load();
			$this->arrFields['title']['Value'] = $title;
			$this->arrFields['available']['Value'] = $available;
			$this->save();
			$arrLocales = language::getPrefixes();
			if (count($arrLocales) > 0) {
				foreach ($arrLocales as $luid => $arrData) {
					$name = addslashes($_POST['name_' . $arrData['prefix']]);
					$sql = "UPDATE `article_template_translation` SET
					name='{$name}'			    
					WHERE
					article_template_uid='{$articletemplate_uid}'
					AND
					language_uid='{$luid}'
				";
					database::query($sql);
				}
			}
			return true;
		} else {
			$arrData['name'] = $title;
			$arrData['token'] = $token;
			$arrData['available'] = $available;
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

	public function getTranslationByUid($uid) {
		$response = false;
		$sql = "SELECT * FROM `article_template_translation`
			WHERE
			article_template_uid='{$uid}'";
		$res = database::query($sql);
		if ($res && mysql_error() == '' && mysql_num_rows($res) > 0) {
			$response = array();
			while ($row = mysql_fetch_assoc($res)) {
				$response[$row['uid']] = array(
					'locale' => stripslashes($row['locale']),
					'language_uid' => stripslashes($row['language_uid']),
					'name' => stripslashes($row['name']),
					'primary_image_path' => stripslashes($row['primary_image_path']),
					'primary_image_caption' => stripslashes($row['primary_image_caption']),
					'secondary_image_path' => stripslashes($row['secondary_image_path']),
					'secondary_image_caption' => stripslashes($row['secondary_image_caption']),
					'introduction' => stripslashes($row['introduction'])
				);
			}
		}
		return $response;
	}

	public function getTemplateSelectBox($name='template_uid', $selected_value=null) {
		$query = "SELECT ";
		$query.="`uid`, ";
		$query.="`title` ";
		$query.="FROM ";
		$query.="`article_template` ";
		$query.="ORDER BY `title`";
		$result = database::query($query);
		$arrTemplate = array();
		$arrTemplate[0] = 'Article List';
		if (mysql_error() == '' && mysql_num_rows($result)) {
			while ($row = mysql_fetch_array($result)) {
				$arrTemplate[$row['uid']] = $row['title'];
			}
		}
		return format::to_select(
				array(
			"name" => $name,
			"id" => $name,
			"style" => "width:180px;",
			"options_only" => false
				), $arrTemplate, $selected_value
		);
	}

}

?>