<?php

class reseller_users extends generic_object {

	private $arrForm = array();

	public function __construct($uid = 0) {
		//parent::__construct($uid, __CLASS__);
		$this->pocessArrGet();
	}

	private function pocessArrGet() {
		foreach ($_GET as $index => $value) {
			if (strpos($_GET[$index], "/p-") !== false) {
				$_GET[$index] = substr($_GET[$index], 0, strpos($_GET[$index], "/p-"));
			}
		}
	}

	public static function getDistinctLocales() {
		$response = false;
		/*
		$arrUserTypes = array(
			'school',
			'schooladmin',
			'schoolteacher',
			'student',
			'homeuser'
		);
		$where = '';
		$parts = config::get('paths');
		if (isset($parts[2]) && in_array(strtolower($parts[2]), $arrUserTypes)) {
			$where = " AND FIND_IN_SET('" . strtolower($parts[2]) . "',`user_type`)";
		}*/
		$query = "SELECT DISTINCT ";
		$query.= "`prefix` ";
		$query.= "FROM ";
		$query.= "`language` ";
		$query.= "WHERE ";
		$query.= "`prefix` in (" . $_SESSION['user']['localeRights'] . ") ";
		$result = database::query($query);
		if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
			$response = array();
			while ($row = mysql_fetch_assoc($result)) {
				$response[] = $row['prefix'];
			}
		}
		return $response;
	}

	// listing of the users
	public function get_users($all = false) {
		$parts = config::get('paths');
		if (isset($parts[2]) && $parts[2] == 'list') {
			//return $this->getAllUsersList($all, $parts);
			return $this->getSchoolList($all, $parts);
		} else if (isset($parts[2]) && $parts[2] == 'school') {
			return $this->getSchoolList($all, $parts);
		} else if (isset($parts[2]) && $parts[2] == 'schooladmin') {
			return $this->getSchoolAdminList($all, $parts);
		} else if (isset($parts[2]) && $parts[2] == 'schoolteacher') {
			return $this->getSchoolTeacherList($all, $parts);
		} else if (isset($parts[2]) && $parts[2] == 'student') {
			return $this->getSchoolStudentList($all, $parts);
		} else if (isset($parts[2]) && $parts[2] == 'homeuser') {
			return $this->getHomeuserList($all, $parts);
		}
	}

	private function getAllUsersList($all = false, $parts = array()) {
		if (isset($_GET['find']) && trim($_GET['find']) != '') {
			return $this->getAllUsersSearchList($all, $parts);
		}
		$where = $this->QueryWhere($parts);
		if ($all == false) {
			$query = 'SELECT ';
			$query.= 'COUNT(`U`.`uid`) ';
			$query.= 'FROM ';
			$query.= '`user` as `U` ' . $where;
			$this->setPagination($query);
		}
		$query = "SELECT ";
		$query.= "`U`.`uid`, ";
		$query.= "`U`.`email`, ";
		$query.= "`U`.`user_type`, ";
		$query.= "`U`.`registered_dts`, ";
		$query.= "`U`.`active`, ";
		$query.= "`U`.`access_allowed` ";
		$query.= "FROM ";
		$query.= "`user` as `U` " . $where . " ";
		$query.= $this->getOrderBy();
		if ($all == false) {
			$query.= "LIMIT " . $this->get_limit();
		}
		return database::arrQuery($query);
	}

	private function getAllUsersSearchList($all = false, $parts = array()) {
		$where = $this->QueryWhere($parts, array(), true);
		if ($all == false) {
			$query = 'SELECT ';
			$query.= 'COUNT(`U`.`uid`) ';
			$query.= 'FROM ';
			$query.= '`user` as `U` ' . $where;
			$this->setPagination($query);
		}
		$query = "SELECT ";
		$query.= "`U`.`uid`, ";
		$query.= "`U`.`email`, ";
		$query.= "`U`.`user_type`, ";
		$query.= "`U`.`registered_dts`, ";
		$query.= "`U`.`active`, ";
		$query.= "`U`.`access_allowed` ";
		$query.= "FROM ";
		$query.= "`user` as `U` " . $where . " ";
		$query.= $this->getOrderBy();
		if ($all == false) {
			$query.= "LIMIT " . $this->get_limit();
		}
		return database::arrQuery($query);
	}

	private function getSchoolList($all = false, $parts = array()) {
		/* if admin user performs search then query will look following fields form profile. */
		$Fields = array();
		$Fields[] = '`SC`.`name`';
		$Fields[] = '`SC`.`school`';
		$Fields[] = '`SC`.`contact`';
		$Fields[] = '`SC`.`phone_number`';
		$where = $this->QueryWhere($parts, $Fields);
		$where .= " AND FIND_IN_SET('school',`U`.`user_type`)";
		$where .= " AND `U`.`uid` = `SC`.`user_uid`";
		$where .= " AND `SC`.`tracking_code`='".$_SESSION['user']['tracking_code']."'";
		if ($all == false) {
			$query = 'SELECT ';
			$query.= 'COUNT(`U`.`uid`) ';
			$query.= 'FROM ';
			$query.= '`user` AS `U` ';
			$query.= ', `users_schools` AS `SC` ';
			$query.= $where;
			$this->setPagination($query);
		}
		$query = "SELECT ";
		$query.= "`U`.`uid`, ";
		$query.= "`U`.`email`, ";
		$query.= "`U`.`user_type`, ";
		$query.= "`U`.`registered_dts`, ";
		$query.= "`U`.`active`, ";
		$query.= "`U`.`access_allowed`, ";
		$query.= "`U`.`username_open`, ";
		$query.= "`U`.`access_allowed`, ";
		$query.= "`SC`.`school`, ";
		$query.= "`SC`.`uid` AS `school_uid`, ";
		$query.= "( SELECT count(`logging_access`.`uid`) FROM `logging_access` WHERE `SC`.`uid` = `logging_access`.`school_uid` AND `is_login_entry` = '1' ) AS `AllTime`";
		$query.= "FROM ";
		$query.= "`user` AS `U` ";
		$query.= ', `users_schools` AS `SC` ';
		$query.= $where;
		$query.= $this->getOrderBy();
		if ($all == false) {
			$query.= "LIMIT " . $this->get_limit();
		}
		$result = database::arrQuery($query);
		return $result;
	}

	private function getSchoolAdminList($all = false, $parts = array()) {
		/* if admin user performs search then query will look following fields form profile. */
		$Fields = array();
		$Fields[] = '`SA`.`vfirstname`';
		$Fields[] = '`SA`.`vlastname`';
		$Fields[] = '`SA`.`vemail`';
		$Fields[] = '`SA`.`vfax`';
		$Fields[] = '`SA`.`vphone`';
		$Fields[] = '`SC`.`school`';
		$where = $this->QueryWhere($parts, $Fields);
		$where .= " AND FIND_IN_SET('schooladmin',`U`.`user_type`)";
		$where .= " AND `U`.`uid` = `SA`.`iuser_uid`";
		$where .= " AND `SC`.`uid` = `SA`.`school_id` ";
		$where .= " AND `SC`.`tracking_code`='".$_SESSION['user']['tracking_code']."'";
		if ($all == false) {
			$query = 'SELECT ';
			$query.= 'COUNT(`U`.`uid`) ';
			$query.= 'FROM ';
			$query.= '`user` AS `U` ';
			$query.= ', `profile_schooladmin` AS `SA` ';
			$query.= ', `users_schools` AS `SC` ';
			$query.= $where;
			$this->setPagination($query);
		}
		$query = "SELECT ";
		$query.= "`U`.`uid`, ";
		$query.= "`U`.`email`, ";
		$query.= "`U`.`user_type`, ";
		$query.= "`U`.`registered_dts`, ";
		$query.= "`U`.`active`, ";
		$query.= "`U`.`access_allowed`, ";
		$query.= "`SC`.`school` ";
		$query.= "FROM ";
		$query.= "`user` AS `U` ";
		$query.= ', `profile_schooladmin` AS `SA` ';
		$query.= ', `users_schools` AS `SC` ';
		$query.= $where;
		$query.= $this->getOrderBy();
		if ($all == false) {
			$query.= "LIMIT " . $this->get_limit();
		}
		return database::arrQuery($query);
	}

	private function getSchoolTeacherList($all = false, $parts = array()) {
		/* if admin user performs search then query will look following fields form profile. */
		$Fields = array();
		$Fields[] = '`ST`.`vfirstname`';
		$Fields[] = '`ST`.`vlastname`';
		$Fields[] = '`ST`.`vemail`';
		$Fields[] = '`ST`.`vphone`';
		$Fields[] = '`SC`.`school`';
		$where = $this->QueryWhere($parts, $Fields);
		$where .= " AND FIND_IN_SET('schoolteacher',`U`.`user_type`)";
		$where .= " AND `U`.`uid` = `ST`.`iuser_uid`";
		$where .= " AND `SC`.`uid` = `ST`.`school_id` ";
		$where .= " AND `SC`.`tracking_code`='".$_SESSION['user']['tracking_code']."'";
		if ($all == false) {
			$query = 'SELECT ';
			$query.= 'COUNT(`U`.`uid`) ';
			$query.= 'FROM ';
			$query.= '`user` AS `U` ';
			$query.= ', `profile_schoolteacher` AS `ST` ';
			$query.= ', `users_schools` AS `SC` ';
			$query.= $where;
			$this->setPagination($query);
		}
		$query = "SELECT ";
		$query.= "`U`.`uid`, ";
		$query.= "`U`.`email`, ";
		$query.= "`U`.`user_type`, ";
		$query.= "`U`.`registered_dts`, ";
		$query.= "`U`.`active`, ";
		$query.= "`U`.`access_allowed`, ";
		$query.= "`SC`.`school` ";
		$query.= "FROM ";
		$query.= "`user` AS `U` ";
		$query.= ', `profile_schoolteacher` AS `ST` ';
		$query.= ', `users_schools` AS `SC` ';
		$query.= $where;
		$query.= $this->getOrderBy();
		if ($all == false) {
			$query.= "LIMIT " . $this->get_limit();
		}
		return database::arrQuery($query);
	}

	private function getSchoolStudentList($all = false, $parts = array()) {
		/* if admin user performs search then query will look following fields form profile. */
		$Fields = array();
		$Fields[] = '`ST`.`vfirstname`';
		$Fields[] = '`ST`.`vlastname`';
		$Fields[] = '`SC`.`school`';
		$where = $this->QueryWhere($parts, $Fields);
		$where .= " AND FIND_IN_SET('student',`U`.`user_type`)";
		$where .= " AND `U`.`uid` = `ST`.`iuser_uid`";
		$where .= " AND `SC`.`uid` = `ST`.`school_id` ";
		$where .= " AND `SC`.`tracking_code`='".$_SESSION['user']['tracking_code']."'";
		if ($all == false) {
			$query = 'SELECT ';
			$query.= 'COUNT(`U`.`uid`) ';
			$query.= 'FROM ';
			$query.= '`user` AS `U` ';
			$query.= ', `profile_student` AS `ST` ';
			$query.= ', `users_schools` AS `SC` ';
			$query.= $where;
			$this->setPagination($query);
		}
		$query = "SELECT ";
		$query.= "`U`.`uid`, ";
		$query.= "`U`.`email`, ";
		$query.= "`U`.`user_type`, ";
		$query.= "`U`.`registered_dts`, ";
		$query.= "`U`.`active`, ";
		$query.= "`U`.`access_allowed`, ";
		$query.= "`SC`.`school` ";
		$query.= "FROM ";
		$query.= "`user` AS `U` ";
		$query.= ', `profile_student` AS `ST` ';
		$query.= ', `users_schools` AS `SC` ';
		$query.= $where;
		$query.= $this->getOrderBy();
		if ($all == false) {
			$query.= "LIMIT " . $this->get_limit();
		}
		return database::arrQuery($query);
	}

	private function getHomeuserList($all = false, $parts = array()) {
		/* if admin user performs search then query will look following fields form profile. */
		$Fields = array();
		$Fields[] = '`H`.`vfirstname`';
		$Fields[] = '`H`.`vlastname`';
		$Fields[] = '`H`.`vphone`';
		$where = $this->QueryWhere($parts, $Fields);
		$where .= " AND FIND_IN_SET('homeuser',`U`.`user_type`)";
		$where .= " AND `U`.`uid` = `H`.`iuser_uid`";
		if ($all == false) {
			$query = 'SELECT ';
			$query.= 'COUNT(`U`.`uid`) ';
			$query.= 'FROM ';
			$query.= '`user` AS `U` ';
			$query.= ', `profile_homeuser` AS `H` ';
			$query.= $where;
			$this->setPagination($query);
		}
		$query = "SELECT ";
		$query.= "`U`.`uid`, ";
		$query.= "`U`.`email`, ";
		$query.= "`U`.`user_type`, ";
		$query.= "`U`.`registered_dts`, ";
		$query.= "`U`.`active`, ";
		$query.= "`U`.`access_allowed` ";
		$query.= "FROM ";
		$query.= "`user` AS `U` ";
		$query.= ', `profile_homeuser` AS `H` ';
		$query.= $where;
		$query.= $this->getOrderBy();
		if ($all == false) {
			$query.= "LIMIT " . $this->get_limit();
		}
		return database::arrQuery($query);
	}

	private function QueryWhere($parts, $Fields = array(), $is_user_list_search = false) {
		$where = " WHERE `U`.`deleted` != '1' ";

		// no home users
		$where .= " AND `U`.`user_type` != 'homeuser' ";

		$where .= " AND `U`.`locale` in (" . $_SESSION['user']['localeRights'] . ") ";
		if (isset($parts[3]) && language::CheckLocale($parts[3], false) != false) {
			$where .= " AND `U`.`locale` = '" . $parts[3] . "'";
		}
		$where .= $this->getSchoolSessionWhere($is_user_list_search);
		$where .= $this->SearchQueryWhere($Fields, $is_user_list_search);
		return $where;
	}

	private function SearchQueryWhere($Fields = array(), $is_user_list_search = false) {
		$where = '';
		if (isset($_GET['find']) && trim($_GET['find']) != '' && $is_user_list_search == false) {
			if (strpos($_GET['find'], "/p-") !== false) {
				$_GET['find'] = substr($_GET['find'], 0, strpos($_GET['find'], "/p-"));
			}
			$_GET['find'] = mysql_real_escape_string($_GET['find']);
			$where .= " AND ";
			$where .= " ( ";
			$where .= "`U`.`email` LIKE '%" . $_GET['find'] . "%'";
			$where .= " OR `U`.`username_open` LIKE '%" . $_GET['find'] . "%'";
			foreach ($Fields as $Field) {
				$where .= " OR " . $Field . " LIKE '%" . $_GET['find'] . "%'";
			}
			$where .= " ) ";
		}
		return $where;
	}

	private function getSchoolSessionWhere($is_user_list_search = false) {
		$where = '';
		$find = '';
		if (isset($_GET['find']) && trim($_GET['find']) != '' && $is_user_list_search) {
			if (strpos($_GET['find'], "/p-") !== false) {
				$_GET['find'] = substr($_GET['find'], 0, strpos($_GET['find'], "/p-"));
			}
			$find = mysql_real_escape_string($_GET['find']);
			$where .= " AND ( ";
			$where .= "`U`.`email` LIKE '%" . $_GET['find'] . "%'";
			$where .= " OR `U`.`username_open` LIKE '%" . $_GET['find'] . "%' ) ";
		}
		if (trim($find) != '') {
			$where.= " AND (";
			$where.= "`U`.`uid` IN (";
			$where.= "SELECT ";
			$where.= "`user_uid` ";
			$where.= "FROM ";
			$where.= "`users_schools` ";
			$where.= "WHERE ";
			$where.= "( ";
			$where.= "`name` LIKE '%" . $find . "%' ";
			$where.= " OR `school` LIKE '%" . $find . "%' ";
			$where.= " OR `contact` LIKE '%" . $find . "%' ";
			$where.= " OR `phone_number` LIKE '%" . $find . "%' ";
			$where.= ") ";
			$where.= ") OR `U`.`uid` IN (";
			$where.= "SELECT ";
			$where.= "`iuser_uid` ";
			$where.= "FROM ";
			$where.= "`profile_schooladmin` ";
			$where.= "WHERE ";
			$where.= "( ";
			$where.= "`vfirstname` LIKE '%" . $find . "%' ";
			$where.= " OR `vlastname` LIKE '%" . $find . "%' ";
			$where.= " OR `vemail` LIKE '%" . $find . "%' ";
			$where.= " OR `vfax` LIKE '%" . $find . "%' ";
			$where.= " OR `vphone` LIKE '%" . $find . "%' ";
			$where.= ") ";
			$where.= ") OR `U`.`uid` IN (";
			$where.= "SELECT ";
			$where.= "`iuser_uid` ";
			$where.= "FROM ";
			$where.= "`profile_schoolteacher` ";
			$where.= "WHERE ";
			$where.= "( ";
			$where.= "`vfirstname` LIKE '%" . $find . "%' ";
			$where.= " OR `vlastname` LIKE '%" . $find . "%' ";
			$where.= " OR `vemail` LIKE '%" . $find . "%' ";
			$where.= " OR `vphone` LIKE '%" . $find . "%' ";
			$where.= ") ";
			$where.= ") OR `U`.`uid` IN (";
			$where.= "SELECT ";
			$where.= "`iuser_uid` ";
			$where.= "FROM ";
			$where.= "`profile_homeuser` ";
			$where.= "WHERE ";
			$where.= "( ";
			$where.= "`vfirstname` LIKE '%" . $find . "%' ";
			$where.= " OR `vlastname` LIKE '%" . $find . "%' ";
			$where.= " OR `vemail` LIKE '%" . $find . "%' ";
			$where.= " OR `vphone` LIKE '%" . $find . "%' ";
			$where.= ") ";
			$where.= ") OR `U`.`uid` IN (";
			$where.= "SELECT ";
			$where.= "`iuser_uid` ";
			$where.= "FROM ";
			$where.= "`profile_student` ";
			$where.= "WHERE ";
			$where.= "( ";
			$where.= "`vfirstname` LIKE '%" . $find . "%' ";
			$where.= " OR `vlastname` LIKE '%" . $find . "%' ";
			$where.= ") ";
			$where.= ")";
			$where.= ")";
		}
		return $where;
	}

	private function getOrderBy() {
		$orderBy = " ORDER BY ";
		if (isset($_GET['order']) && trim($_GET['order']) != '' && isset($_GET['column']) && trim($_GET['column']) != '') {
			$orderBy .= $_GET['column'] . " ";
			if ($_GET['order'] == 'desc') {
				$orderBy .= " DESC ";
			}
		} else {
			$orderBy .= "`U`.`registered_dts` DESC ";
		}
		return $orderBy;
	}

}

?>