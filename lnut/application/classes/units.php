<?php

class units extends generic_object {

	public $arrForm = array();

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function unitLit($orderBy = 'unit_number') {
		$query = "SELECT ";
		$query.="`uid`, ";
		$query.="`name`, ";
		$query.="`unit_number` ";
		$query.="FROM ";
		$query.="`units` ";
		$query.="ORDER BY " . $orderBy;
		$result = database::query($query);
		$arrRows = array();
		if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
			while ($row = mysql_fetch_assoc($result)) {
				$arrRows[] = $row;
			}
		}
		return $arrRows;
	}

	public function getList($data = array(), $all = false) {
		$where = ' WHERE `Y`.`uid` = `U`.`year_uid` ';
		foreach ($data as $idx => $val) {
			$where .= " AND " . $idx . "='" . $val . "'";
		}
		if (!$all) {
			$query = "SELECT ";
			$query.="COUNT(`U`.`uid`) ";
			$query.="FROM ";
			$query.="`units` AS `U`, ";
			$query.="`years` AS `Y` ";
			$query.= $where;
			$this->setPagination($query);
		}
		$query = "SELECT ";
		$query.="`U`.*, ";
		$query.="`Y`.`name` AS `years` ";
		$query.="FROM ";
		$query.="`units` AS `U`, ";
		$query.="`years` AS `Y` ";
		$query.=$where . " ";
		$query.="ORDER BY `U.`name` ";
		if (!all) {
			$query.=" LIMIT " . $this->get_limit();
		}
		return database::arrQuery($query);
	}

	public function yearTranslationsList($year_id=null) {
		$arrRows = array();
		if (is_numeric($year_id) && $year_id > 0) {
			$query = "SELECT ";
			$query.="`YT`.*, ";
			$query.="`L`.`name` AS `language` ";
			$query.="FROM ";
			$query.="`language` AS `L`, ";
			$query.="`years_translations` AS `YT` ";
			$query.="WHERE ";
			$query.="`L`.`uid` = `YT`.`language_id` ";
			$query.="AND ";
			$query.="`year_id` = '" . $year_id . "'";
			$result = database::query($query);
			if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
				while ($row = mysql_fetch_assoc($result)) {
					if ($row['active']) {
						$row['active_yes_no'] = 'Yes';
					} else {
						$row['active_yes_no'] = 'No';
					}
					$arrRows[] = $row;
				}
			}
		}
		return $arrRows;
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
		$name = (isset($_POST['name'])) ? $_POST['name'] : '';
		$year_uid = (isset($_POST['year_uid'])) ? $_POST['year_uid'] : '0';
		$active = (isset($_POST['active'])) ? $_POST['active'] : 0;
		$arrMessages = array();
		if (trim(strlen($name)) < 3 || trim(strlen($name)) > 255) {
			$arrMessages['error_name'] = "Unit name must be 3 to 255 characters in length.";
		} else if (!validation::isValid('text', $name)) {
			$arrMessages['error_name'] = "Please enter valid unit name.";
		}
		if (trim($year_uid) == '' || trim($year_uid) == "0") {
			$arrMessages['error_year_uid'] = "Please select year.";
		} else if (!validation::isValid('int', $year_uid)) {
			$arrMessages['error_year_uid'] = "Please select valid year.";
		}
		if (!validation::isValid('int', $active)) {
			$arrMessages['error_active'] = "Please choose valid section active option.";
		}
		if (count($arrMessages) == 0) {
			$this->set_name($name);
			$this->set_year_uid($year_uid);
			$this->set_active($active);
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

	public function unitTranslationsList($unit_id=null) {
		$arrRows = array();
		if (is_numeric($unit_id) && $unit_id > 0) {
			$query = "SELECT ";
			$query.="`UT`.*, ";
			$query.="`L`.`name` AS `language` ";
			$query.="FROM ";
			$query.="`language` AS `L`, ";
			$query.="`units_translations` AS `UT` ";
			$query.="WHERE ";
			$query.="`L`.`uid` = `UT`.`language_id` ";
			$query.="AND ";
			$query.="`unit_id` = '" . $unit_id . "'";
			$result = database::query($query);
			if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
				while ($row = mysql_fetch_assoc($result)) {
					if ($row['active']) {
						$row['active_yes_no'] = 'Yes';
					} else {
						$row['active_yes_no'] = 'No';
					}
					$arrRows[] = $row;
				}
			}
		}
		return $arrRows;
	}

	public function UnitSelectBox($inputName, $selctedValue = NULL) {
		$sql = "SELECT ";
		$sql.="`uid`, ";
		$sql.="`name` ";
		$sql.="FROM ";
		$sql.="`units` ";
		$sql.="ORDER BY `name`";
		$result = database::query($sql);
		$data = array();
		$data[0] = 'Unit Name';
		if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
			while ($row = mysql_fetch_assoc($result)) {
				$data[$row['uid']] = $row['name'];
			}
		}
		return format::to_select(array("name" => $inputName, "id" => $inputName, "options_only" => false), $data, $selctedValue);
	}

	public function does_file_exist($url='') {
		if($url=='') {
			return false;
		} else {
			$code = FALSE;
			$options['http'] = array(
				'method' => "HEAD",
				'ignore_errors' => 1,
				'max_redirects' => 0
			);
			$body = file_get_contents($url, NULL, stream_context_create($options));
			sscanf($http_response_header[0], 'HTTP/%*d.%*d %d', $code);
			if($code==200) {
				return true;
			} else {
				return false;
			}
		}
	}
	public function getUnitTransArray($language_id, $year_id, $locale) {
		
		$path = config::get('root');
		$song = 'http://content.languagenut.com/songs/[locale]/[locale]_u[unit_id]/[locale]_u[unit_id]_s[story_id]_karaoke.xml';
		$story = 'http://content.languagenut.com/stories/[locale]/[locale]_u[unit_id]/[locale]_u[unit_id]_s[story_id]_story.xml';

		//$story = '/swf/story/[locale]/[locale]_u[unit_id]/[locale]_u[unit_id]_s[story_id]_story.xml';
		//$karaoke = '/swf/karaoke/[locale]/[locale]_u[unit_id]/[locale]_u[unit_id]_s[story_id]_karaoke.xml';
		$units = array();
		$query = "SELECT ";
		$query.="`ut`.`unit_id`, ";
		$query.="`ut`.`name`, ";
		$query.="`colour`";
		$query.="FROM ";
		$query.="`units`, ";
		$query.="`units_translations` AS `ut` ";
		$query.="WHERE ";
		$query.="`ut`.`language_id`=$language_id ";
		$query.="AND `ut`.`unit_id`=`units`.`uid` ";
		if ($year_id != null) {
			$query.="AND `units`.`year_uid`=$year_id ";
		}
		$query.="ORDER BY ";
		$query.="`ut`.`unit_id` ASC";
		
		$result = database::query($query);
		if ($result && mysql_num_rows($result) > 0) {
			while ($row = mysql_fetch_assoc($result)) {
				$u = ((int) $row['unit_id'] < 10) ? '0' . $row['unit_id'] : $row['unit_id'];
				$s = 1;
				$units[$row['unit_id']] = array(
					'colour'=> stripslashes($row['colour']),
					'name'	=> stripslashes(str_replace('\\','',$row['name'])),
					'story' => (
					$this->does_file_exist(
							str_replace(
									array('[unit_id]', '[story_id]', '[locale]'), array($u, $s, ($locale == 'es' ? 'sp' : $locale)), $story
							)
					) ? true : false
					),
					'karaoke' => (
					$this->does_file_exist(
							str_replace(
									array('[unit_id]', '[story_id]', '[locale]'), array($u, $s, ($locale == 'es' ? 'sp' : $locale)), $song
							)
					) ? true : false
					)
				);
			}
		} else {
			$query = "SELECT ";
			$query.="`ut`.`unit_id`, ";
			$query.="`ut`.`name`, ";
			$query.="`colour`";
			$query.="FROM ";
			$query.="`units`, ";
			$query.="`units_translations` AS `ut` ";
			$query.="WHERE ";
			$query.="`ut`.`language_id`=14 ";
			$query.="AND `ut`.`unit_id`=`units`.`uid` ";
			if ($year_id != null) {
				$query.="AND `units`.`year_uid`=$year_id ";
			}
			$query.="ORDER BY ";
			$query.="`ut`.`unit_id` ASC";
			$result = database::query($query);
			if ($result && mysql_num_rows($result) > 0) {
				while ($row = mysql_fetch_assoc($result)) {
					$u = ((int) $row['unit_id'] < 10) ? '0' . $row['unit_id'] : $row['unit_id'];
					$s = 1;
					$units[$row['unit_id']] = array(
						'colour'=> stripslashes($row['colour']),
						'name' => stripslashes(str_replace('\\','',$row['name'])),
						'story' => (
						$this->does_file_exist(
								str_replace(
										array('[unit_id]', '[story_id]', '[locale]'), array($u, $s, ($locale == 'es' ? 'sp' : $locale)), $story
								)
						) ? true : false
						),
						'karaoke' => (
						$this->does_file_exist(
								str_replace(
										array('[unit_id]', '[story_id]', '[locale]'), array($u, $s, ($locale == 'es' ? 'sp' : $locale)), $song
								)
						) ? true : false
						)
					);
				}
			}
		}
		return $units;
	}

	public function getUnitTransArray_old($language_id, $year_id, $locale) {
		//$path = '/home/language/public_html';
		$path = config::get('root');
		//	$story	= '/swf/story/[locale]/[locale]_u[unit_id]/[locale]_u[unit_id]_story0[section_id]/[locale]_u[unit_id]_s[section_id]_story.xml';
		$story = '/swf/story/[locale]/[locale]_u[unit_id]/[locale]_u[unit_id]_s[story_id]_story.xml';
		$karaoke = '/swf/karaoke/[locale]/[locale]_u[unit_id]/[locale]_u[unit_id]_s[story_id]_karaoke.xml';
		$units = array();
		$query = "SELECT ";
		$query.="`ut`.`unit_id`, ";
		$query.="`ut`.`name`, ";
		$query.="`colour`";
		$query.="FROM ";
		$query.="`units`, ";
		$query.="`units_translations` AS `ut` ";
		$query.="WHERE ";
		$query.="`ut`.`language_id`=$language_id ";
		$query.="AND `ut`.`unit_id`=`units`.`uid` ";
		if ($year_id != null) {
			$query.="AND `units`.`year_uid`=$year_id ";
		}
		$query.="ORDER BY ";
		$query.="`ut`.`unit_id` ASC";
		
		$result = database::query($query);
		if ($result && mysql_num_rows($result) > 0) {
			while ($row = mysql_fetch_assoc($result)) {
				$u = ((int) $row['unit_id'] < 10) ? '0' . $row['unit_id'] : $row['unit_id'];
				$s = 1;
				$units[$row['unit_id']] = array(
					'colour'=> stripslashes($row['colour']),
					'name'	=> stripslashes(str_replace('\\','',$row['name'])),
					'story' => (
					file_exists(
							str_replace(
									array('[unit_id]', '[story_id]', '[locale]'), array($u, $s, ($locale == 'es' ? 'sp' : $locale)), $path . $story
							)
					) ? '1' : '0'
					),
					'karaoke' => (
					file_exists(
							str_replace(
									array('[unit_id]', '[story_id]', '[locale]'), array($u, $s, ($locale == 'es' ? 'sp' : $locale)), $path . $karaoke
							)
					) ? '1' : '0'
					)
				);
			}
		} else {
			$query = "SELECT ";
			$query.="`ut`.`unit_id`, ";
			$query.="`ut`.`name`, ";
			$query.="`colour`";
			$query.="FROM ";
			$query.="`units`, ";
			$query.="`units_translations` AS `ut` ";
			$query.="WHERE ";
			$query.="`ut`.`language_id`=14 ";
			$query.="AND `ut`.`unit_id`=`units`.`uid` ";
			if ($year_id != null) {
				$query.="AND `units`.`year_uid`=$year_id ";
			}
			$query.="ORDER BY ";
			$query.="`ut`.`unit_id` ASC";
			$result = database::query($query);
			if ($result && mysql_num_rows($result) > 0) {
				while ($row = mysql_fetch_assoc($result)) {
					$u = ((int) $row['unit_id'] < 10) ? '0' . $row['unit_id'] : $row['unit_id'];
					$s = 1;
					$units[$row['unit_id']] = array(
						'colour'=> stripslashes($row['colour']),
						'name' => stripslashes(str_replace('\\','',$row['name'])),
						'story' => (
						file_exists(
								str_replace(
										array('[unit_id]', '[story_id]', '[locale]'), array($u, $s, ($locale == 'es' ? 'sp' : $locale)), $path . $story
								)
						) ? '1' : '0'
						),
						'karaoke' => (
						file_exists(
								str_replace(
										array('[unit_id]', '[story_id]', '[locale]'), array($u, $s, ($locale == 'es' ? 'sp' : $locale)), $path . $karaoke
								)
						) ? '1' : '0'
						)
					);
				}
			}
		}
		return $units;
	}

	public function getUnitSelectBox($name='unit_uid', $selected_value=null) {
		$query = "SELECT ";
		$query.="`uid`, ";
		$query.="`name` ";
		$query.="FROM ";
		$query.="`units` ";
		$query.="WHERE ";
		$query.="`active`='1' ";
		$query.="ORDER BY `unit_number`";
		$result = database::query($query);
		$arrUnits = array();
		$arrUnits[0] = 'Unit List';
		if (mysql_error() == '' && mysql_num_rows($result)) {
			while ($row = mysql_fetch_array($result)) {
				$arrUnits[$row['uid']] = $row['name'];
			}
		}
		return format::to_select(
				array(
			"name" => $name,
			"id" => $name,
			"style" => "width:180px;",
			"options_only" => false
				), $arrUnits, $selected_value
		);
	}

	public static function getUnits() {
		$query = "SELECT ";
		$query.="`uid`, ";
		$query.="`name`, ";
		$query.="`unit_number` ";
		$query.="FROM ";
		$query.="`units` ";
		$query.="WHERE ";
		$query.="`active` = 1 ";
		$query.="ORDER BY unit_number";
		return database::arrQuery($query);
	}

	public function getFilteredUnits($year_uid=null,$arrFilter=array()) {
		$query = "SELECT ";
		$query.="`uid`,";
		$query.="`name`,";
		$query.="`unit_number` ";
		$query.="FROM ";
		$query.="`units` ";
		$query.="WHERE ";
		$query.="`active` = '1' ";
		if(is_numeric($year_uid)) {
			$query.="AND ";
			$query.="`year_uid` = '" . $year_uid . "' ";
		}
		if(count($arrFilter)) {
			$query.="AND ";
			$query.="`uid` IN (".implode(',',$arrFilter).") ";
		}
		$query.="ORDER BY `unit_number` ";
		return database::arrQuery($query);
	}

	public function unit_song_and_story_cron() {
		//$story = '/swf/story/[locale]/[locale]_u[unit_id]/[locale]_u[unit_id]_s[story_id]_story.xml';
		//$karaoke = '/swf/karaoke/[locale]/[locale]_u[unit_id]/[locale]_u[unit_id]_s[story_id]_karaoke.xml';

		$song = 'http://content.languagenut.com/songs/[locale]/[locale]_u[unit_id]/[locale]_u[unit_id]_s[story_id]_karaoke.xml';
		$story = 'http://content.languagenut.com/stories/[locale]/[locale]_u[unit_id]/[locale]_u[unit_id]_s[story_id]_story.xml';

		$query ="SELECT ";
		$query.="`U`.`uid`, ";
		$query.="`U`.`unit_id`, ";
		$query.="`L`.`prefix` ";
		$query.="FROM ";
		$query.="`units_translations` AS `U`, ";
		$query.="`language` AS `L` ";
		$query.="WHERE ";
		$query.="`L`.`uid`=`U`.`language_id` ";
		$query.="AND ";
		$query.="`L`.`prefix` IN ('ca','cc','ch','en','fr','ga','ge','ht','it','ja','ma','mx','sp','us') ";
		$query.="ORDER BY `U`.`language_id` ";

		$result = database::query($query);
		if(mysql_error()=='' && mysql_num_rows($result)) {
			while($arrRow=mysql_fetch_array($result)) {
				$u = ((int) $arrRow['unit_id'] < 10) ? '0' . $arrRow['unit_id'] : $arrRow['unit_id'];
				$s = 1;
				$locale = $arrRow['prefix'];
				$does_story_exist = ($this->does_file_exist(
								str_replace(
										array('[unit_id]', '[story_id]', '[locale]'), array($u, $s, ($locale == 'es' ? 'sp' : $locale)), $story
								)
						) ? 1 : 0);
				$does_song_exist = ($this->does_file_exist(
								str_replace(
										array('[unit_id]', '[story_id]', '[locale]'), array($u, $s, ($locale == 'es' ? 'sp' : $locale)), $song
								)
						) ? 1 : 0);
				$query ="UPDATE ";
				$query.="`units_translations` ";
				$query.="SET ";
				$query.="`song`='".$does_song_exist."', ";
				$query.="`story`='".$does_story_exist."'";
				$query.="WHERE ";
				$query.="`uid`='".$arrRow['uid']."'";
				database::query($query);
			}
		}
	}

}

?>