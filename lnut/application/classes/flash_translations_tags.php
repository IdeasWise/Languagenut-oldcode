<?php

class flash_translations_tags extends generic_object {

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public static function getAllByTagName() {
		$data = array();
		$sql = "SELECT ";
		$sql.= "`uid`, ";
		$sql.= "`tag_name`, ";
		$sql.= "`description` ";
		$sql.= "FROM ";
		$sql.= "`flash_translations_tags` ";
		$sql.= "ORDER BY ";
		$sql.= "`tag_name` ASC";
		
		$keyMap=array();
		$result = database::arrQueryByUid($sql, $keyMap, 0);
		
		if (count($result) > 0) {
			foreach ($result as $row){
				$data[$row['uid']] = array(
					'tag_name' => $row['tag_name'],
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
		
		$data = database::arrQuery($sql, 1);
		
		return (isset($data[0]))?$data[0]:array();
	}

	public function updateFlashTranslationsTags() {
		if (count($_POST) > 0) {
			foreach ($_POST as $key => $val) {
				$name = explode('_', $key);
				if (count($name) == 2) {
					if ($key == 'add_tag') {
						if (strlen(trim($val)) > 0) {
							$query = "SELECT COUNT(`uid`) as tot FROM `flash_translations_tags` WHERE `tag_name`='" . mysql_real_escape_string($val) . "' LIMIT 1";
							$result = database::arrQuery($query,1);
							
							if (count($result)>0) {
								$row = $result[0];
								if ($row['tot'] > 0) {
									echo $row['tot'];
								} else {
									$query = "INSERT INTO `flash_translations_tags` (`tag_name`, `description`) VALUES ('" . mysql_real_escape_string($_POST['add_tag']) . "', '" . mysql_real_escape_string($_POST['tag_description']) . "')";
									$result = database::query($query,1);
									echo mysql_error().$query;
									exit();
									//output::redirect(config::url('admin/flash_translations_tags/'));
								}
							} else {
								echo mysql_error().$query;
							}
						} else {
							// create error message
						}
					} else {
						$tag_uid = (int) $name[1];
						$query = "SELECT COUNT(`uid`) as tot FROM `flash_translations_tags` WHERE `uid`='" . $tag_uid . "' LIMIT 1";
						$result = database::arrQuery($query,1);
						
						if (count($result)>0) {
							$row = $result[0];
							
							if ($row['tot'] > 0) {
								switch ($name[0]) {
									case 'tagdelete':
										$query = "DELETE FROM `flash_translations_tags` WHERE `uid`='" . $tag_uid . "'";
										$result = database::query($query,1);
										$query = "DELETE FROM `flash_translations_locales` WHERE `tag_uid`='" . $tag_uid . "'";
										$result = database::query($query,1);
										break;
									case 'tagname':
										if (strlen($val) > 0) {
											$query = "UPDATE `flash_translations_tags` SET `tag_name`='" . mysql_real_escape_string($val) . "' WHERE `uid`='" . $tag_uid . "' LIMIT 1";
											$result = database::query($query,1);
										}
										break;
									case 'tagdesc':
										$query = "UPDATE `flash_translations_tags` SET `description`='" . mysql_real_escape_string($val) . "' WHERE `uid`='" . $tag_uid . "' LIMIT 1";
										$result = database::query($query,1);
										break;
								}
							}
						}
					}
				}
			}
			output::redirect(config::url('admin/flash_translations_tags/'));
		}
	}

}

?>