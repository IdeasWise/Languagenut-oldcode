<?php

class user extends generic_object {

	private $arrForm = array();

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
		$this->pocessArrGet();
	}

	private function pocessArrGet() {
		foreach($_GET as $index => $value ) {
			if(strpos($_GET[$index],"/p-") !== false) {
				$_GET[$index] = substr($_GET[$index],0,strpos($_GET[$index],"/p-"));
			}
		}
	}


	public function	get_next_auto_increment_value() {
		$uid	= 0;
		$sql	= "SHOW TABLE STATUS LIKE 'user'";
		$result	= database::query($sql);

		if($result && mysql_error()==''	&& mysql_num_rows($result) > 0) {
			$row	= mysql_fetch_assoc($result);
			$uid	= $row['Auto_increment'];
		}
		return $uid;
	}


	public static function getDistinctLocales() {
		$response	=	false;
		$query = "SELECT `prefix` FROM `language` ORDER BY `prefix`";
		$result = database::query($query);
		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			$response = array ();
			while($row=mysql_fetch_assoc($result)) {
				$response[] = $row['prefix'];
			}
		}
		return $response;
	}

	// listing of the users
	public function	get_users($pageId = '',$all = false) {

		$parts = config::get('paths');

		if(isset($parts[2]) && $parts[2] == 'list') {

			return $this->getAllUsersList( $all, $parts );

		} else if (isset($parts[2]) && $parts[2] == 'school') {

			return $this->getSchoolList( $all, $parts );

		}  else if (isset($parts[2]) && $parts[2] == 'unallocated-schools') {

			return $this->getSchoolList( $all, $parts, true );

		} else if (isset($parts[2]) && $parts[2] == 'schooladmin') {

			return $this->getSchoolAdminList( $all, $parts );

		} else if (isset($parts[2]) && $parts[2] == 'schoolteacher') {

			return $this->getSchoolTeacherList( $all, $parts );

		} else if (isset($parts[2]) && $parts[2] == 'student') {

			return $this->getSchoolStudentList( $all, $parts );

		} else if (isset($parts[2]) && $parts[2] == 'homeuser') {

			return $this->getHomeuserList( $all, $parts );

		} else {

			$where		= '';
			$userTypes	= array (
				'school',
				'schooladmin',
				'schoolteacher',
				'student',
				'homeuser',
				'affiliate',
				'reseller',
				'translator'
			);

			$where = " WHERE 1 = 1 ";
			// this	needs clean	up without @ and should	be checked first
			if(	in_array( strtolower( @$parts[2] ),	$userTypes ) ) {
				$where .= " AND ";
				$where .= "FIND_IN_SET('".strtolower( $parts[2] )."',`user_type`)";
			}
			// parts[3]	should be checked before being used
			if(	isset($parts[3]) &&	!is_numeric($parts[3]) && language::CheckLocale($parts[3]) != false) {
				$where .= " AND ";
				$where .= "`locale` = '".$parts[3]."'";
			}

			if($where != '') {
				$where .= " and deleted != '1' ";
			} else {
				$where = " where deleted != '1' ";
			}

			if($all	== false) {
				$sql = 'SELECT ';
				$sql.= 'COUNT(`uid`) ';
				$sql.= 'FROM ';
				$sql.= '`user` '.$where;
				$this->setPagination($sql);

				$sql = "SELECT ";
				$sql.= "* ";
				$sql.= "FROM ";
				$sql.= "`user` AS `U` ".$where." ";
				$sql.= $this->getOrderBy();
				$sql.= "LIMIT ".$this->get_limit();
				$result	= database::query($sql);
			} else {
				$result	= database::query("SELECT * FROM `user` ".$where." ORDER BY `registered_dts` DESC");
			}
			$this->data = array();
			if($result && mysql_error()==''	&& mysql_num_rows($result) > 0) {
				while($row=mysql_fetch_assoc($result)) {
					$this->data[] = $row;
				}
			}
			return $this->data;
		}
	}

	private function getAllUsersList( $all = false, $parts = array() ) {

		if(isset($_GET['find']) && trim($_GET['find']) != '') {
			return $this->getAllUsersSearchList( $all, $parts );
		}

		$where = $this->QueryWhere($parts);
		if(!$all) {
			$query = 'SELECT ';
			$query.= 'COUNT(`U`.`uid`) ';
			$query.= 'FROM ';
			$query.= '`user` as `U` '.$where;
			$this->setPagination( $query );
		}
		$query = "SELECT ";
		$query.= "* ";
		$query.= "FROM ";
		$query.= "`user` as `U` ".$where." ";
		$query.= $this->getOrderBy();
		if(!$all) {
			$query.= "LIMIT ".$this->get_limit();
		}
		return database::arrQuery($query);
	}

	private function getAllUsersSearchList( $all = false, $parts = array() ) {
		$where = $this->QueryWhere($parts, array(), true);
		if(!$all) {
			$query = 'SELECT ';
			$query.= 'COUNT(`U`.`uid`) ';
			$query.= 'FROM ';
			$query.= '`user` as `U` '.$where;
			//echo $query;
			$this->setPagination( $query );
		}
		$query = "SELECT ";
		$query.= "* ";
		$query.= "FROM ";
		$query.= "`user` as `U` ".$where." ";
		$query.= $this->getOrderBy();
		if(!$all) {
			$query.= "LIMIT ".$this->get_limit();
		}
		return database::arrQuery($query);
	}

	private function getSchoolList( $all = false, $parts = array(), $unAllocatedSchools=false ) {
		/* if admin user performs search then query will look following fields form profile. */
		$Fields		= array();
		$Fields[]	= '`SC`.`name`';
		$Fields[]	= '`SC`.`school`';
		$Fields[]	= '`SC`.`contact`';
		$Fields[]	= '`SC`.`phone_number`';
		$Fields[]	= '`SC`.`affiliate`';

		$where = $this->QueryWhere($parts, $Fields);
		$where .= " AND FIND_IN_SET('school',`U`.`user_type`)";
		$where .= " AND `U`.`uid` = `SC`.`user_uid`";
		if($unAllocatedSchools) {
			$where .= " AND `SC`.`tracking_code`='' ";
		}

		if(isset($_REQUEST['from']) && isset($_REQUEST['to']) && !empty($_REQUEST['from']) && !empty($_REQUEST['to'])) {
			if(strpos($_REQUEST['to'],"/p-") !== false) {
				$_REQUEST['to'] = substr($_REQUEST['to'],0,strpos($_REQUEST['to'],"/p-"));
			}
			$where .= " AND `U`.`registered_dts` BETWEEN '".date('Y-m-d 00:00:00',strtotime($_REQUEST['from']))."' AND '".date('Y-m-d 23:59:59',strtotime($_REQUEST['to']))."'";
		}

		if(!$all) {
			$query = 'SELECT ';
			$query.= 'COUNT(`U`.`uid`) ';
			$query.= 'FROM ';
			$query.= '`user` AS `U` ';
			$query.= ', `users_schools` AS `SC` ';
			$query.= $where;
			$this->setPagination( $query );
		}
		$query = "SELECT ";
		$query.= "`U`.* ";
		$query.= ",`SC`.`school` ";
		$query.= "FROM ";
		$query.= "`user` AS `U` ";
		$query.= ', `users_schools` AS `SC` ';
		$query.= $where;
		$query.= $this->getOrderBy();

		if(!$all) {
			$query.= "LIMIT ".$this->get_limit();
		}
		return database::arrQuery($query);
	}

	private function getSchoolAdminList( $all = false, $parts = array() ) {
		/* if admin user performs search then query will look following fields form profile. */
		$Fields		= array();
		$Fields[]	= '`SA`.`vfirstname`';
		$Fields[]	= '`SA`.`vlastname`';
		$Fields[]	= '`SA`.`vemail`';
		$Fields[]	= '`SA`.`vfax`';
		$Fields[]	= '`SA`.`vphone`';

		$where = $this->QueryWhere($parts, $Fields);
		$where .= " AND ";
		$where .= "FIND_IN_SET('schooladmin',`U`.`user_type`)";
		$where .= " AND ";
		$where .= "`U`.`uid` = `SA`.`iuser_uid`";

		if(!$all) {
			$query = 'SELECT ';
			$query.= 'COUNT(`U`.`uid`) ';
			$query.= 'FROM ';
			$query.= '`user` AS `U` ';
			$query.= ', `profile_schooladmin` AS `SA` ';
			$query.= $where;
			$this->setPagination( $query );
		}

		$query = "SELECT ";
		$query.= "`U`.* ";
		$query.= "FROM ";
		$query.= "`user` AS `U` ";
		$query.= ', `profile_schooladmin` AS `SA` ';
		$query.= $where;
		$query.= $this->getOrderBy();
		if(!$all) {
			$query.= "LIMIT ".$this->get_limit();
		}
		return database::arrQuery($query);
	}


	private function getSchoolTeacherList( $all = false, $parts = array() ) {
		/* if admin user performs search then query will look following fields form profile. */
		$Fields		= array();
		$Fields[]	= '`ST`.`vfirstname`';
		$Fields[]	= '`ST`.`vlastname`';
		$Fields[]	= '`ST`.`vemail`';
		$Fields[]	= '`ST`.`vphone`';

		$where = $this->QueryWhere($parts, $Fields);
		$where .= " AND ";
		$where .= " FIND_IN_SET('schoolteacher',`U`.`user_type`)";
		$where .= " AND ";
		$where .= " `U`.`uid` = `ST`.`iuser_uid`";

		if(!$all) {
			$query = 'SELECT ';
			$query.= 'COUNT(`U`.`uid`) ';
			$query.= 'FROM ';
			$query.= '`user` AS `U` ';
			$query.= ', `profile_schoolteacher` AS `ST` ';
			$query.= $where;
			$this->setPagination( $query );
		}

		$query = "SELECT ";
		$query.= "`U`.* ";
		$query.= "FROM ";
		$query.= "`user` AS `U` ";
		$query.= ', `profile_schoolteacher` AS `ST` ';
		$query.= $where;
		$query.= $this->getOrderBy();
		if(!$all) {
			$query.= "LIMIT ".$this->get_limit();
		}
		return database::arrQuery($query);

	}

	private function getSchoolStudentList( $all = false, $parts = array() ) {
		/* if admin user performs search then query will look following fields form profile. */
		$Fields		= array();
		$Fields[]	= '`ST`.`vfirstname`';
		$Fields[]	= '`ST`.`vlastname`';

		$where = $this->QueryWhere($parts, $Fields);
		$where .= " AND ";
		$where .= " FIND_IN_SET('student',`U`.`user_type`)";
		$where .= " AND ";
		$where .= " `U`.`uid` = `ST`.`iuser_uid`";

		if(!$all) {
			$query = 'SELECT ';
			$query.= 'COUNT(`U`.`uid`) ';
			$query.= 'FROM ';
			$query.= '`user` AS `U` ';
			$query.= ', `profile_student` AS `ST` ';
			$query.= $where;
			$this->setPagination( $query );
		}

		$query = "SELECT ";
		$query.= "`U`.* ";
		if(count($Fields)) {
			$query.= ", ";
			$query.= implode(", ",$Fields);
		}
		$query.= "FROM ";
		$query.= "`user` AS `U` ";
		$query.= ', `profile_student` AS `ST` ';
		$query.= $where;
		$query.= $this->getOrderBy();
		if(!$all) {
			$query.= "LIMIT ".$this->get_limit();
		}
		return database::arrQuery($query);
	}

	private function getHomeuserList( $all = false, $parts = array() ) {

		/* if admin user performs search then query will look following fields form profile. */
		$Fields		= array();
		$Fields[]	= '`H`.`vfirstname`';
		$Fields[]	= '`H`.`vlastname`';
		$Fields[]	= '`H`.`vphone`';

		$where = $this->QueryWhere($parts, $Fields);
		$where .= " AND FIND_IN_SET('homeuser',`U`.`user_type`)";
		$where .= " AND `U`.`uid` = `H`.`iuser_uid`";

		if(!$all) {
			$query = 'SELECT ';
			$query.= 'COUNT(`U`.`uid`) ';
			$query.= 'FROM ';
			$query.= '`user` AS `U` ';
			$query.= ', `profile_homeuser` AS `H` ';
			$query.= $where;
			$this->setPagination( $query );
		}

		$query = "SELECT ";
		$query.= "`U`.* ";
		$query.= "FROM ";
		$query.= "`user` AS `U` ";
		$query.= ', `profile_homeuser` AS `H` ';
		$query.= $where;
		$query.= $this->getOrderBy();
		if(!$all) {
			$query.= "LIMIT ".$this->get_limit();
		}
		return database::arrQuery($query);
	}

	private function getOrderBy() {
		$orderBy = " ORDER BY ";
		if(isset($_GET['order']) && trim($_GET['order']) != '' && isset($_GET['column']) && trim($_GET['column']) != '') {
			$orderBy .= $_GET['column'] . " ";
			if($_GET['order'] == 'desc') {
				$orderBy .= " DESC ";
			}
		} else {
			$orderBy .= "`U`.`registered_dts` DESC ";
		}
		return $orderBy;
	}

	private function QueryWhere($parts, $Fields = array(), $is_user_list_search = false) {
		$where = " WHERE `U`.`deleted` != '1' ";
		// parts[3]	should be checked before being used
		if(	isset($parts[3]) && language::CheckLocale($parts[3], false) != false) {
			$where .= "	and	`U`.`locale` = '".$parts[3]."'";
		}

		$where .= $this->getSchoolSessionWhere( $is_user_list_search );
		$where .= $this->SearchQueryWhere($Fields, $is_user_list_search);
		return $where;
	}

	private function SearchQueryWhere( $Fields = array(), $is_user_list_search = false ) {
		$where = '';
		if(isset($_GET['find']) && trim($_GET['find']) != '' && $is_user_list_search == false) {
			if(strpos($_GET['find'],"/p-") !== false) {
				$_GET['find'] = substr($_GET['find'],0,strpos($_GET['find'],"/p-"));
			}
			$_GET['find'] = mysql_real_escape_string($_GET['find']);
			$where .= " AND ";
			$where .= " ( ";
				$where .= "`U`.`email` LIKE '%".$_GET['find']."%'";
				$where .= " OR `U`.`username_open` LIKE '%".$_GET['find']."%'";
				foreach($Fields as $Field) {
					$where .= " OR ".$Field." LIKE '%".$_GET['find']."%'";
				}
			$where .= " ) ";
		}
		return $where;
	}

	private function getSchoolSessionWhere($is_user_list_search = false) {
		$where = '';
		$find  = '';
		if(isset($_GET['find']) && trim($_GET['find']) != '' && $is_user_list_search ) {
			if(strpos($_GET['find'],"/p-") !== false) {
				$_GET['find'] = substr($_GET['find'],0,strpos($_GET['find'],"/p-"));
			}
			$find = mysql_real_escape_string($_GET['find']);
			$where .= " AND ( ";
			$where .= "`U`.`email` LIKE '%".$_GET['find']."%'";
			$where .= " OR `U`.`username_open` LIKE '%".$_GET['find']."%' ) ";
		}

		if($_SESSION['user']['admin'] != 1  && isset($_SESSION['user']['school_uid'])){

			if($_SESSION['user']['userRights'] == 'school') {
				$where.= " AND (";
					$where.= "`U`.`uid` IN (";
						$where.= "SELECT ";
						$where.= "`iuser_uid` ";
						$where.= "FROM ";
						$where.= "`profile_schooladmin` ";
						$where.= "WHERE ";
						$where.= "`school_id` = '".$_SESSION['user']['school_uid']."'";
						if(trim($find) != '') {
							$where.= " AND ";
							$where.= "( ";
								$where.= "`vfirstname` LIKE '%".$find."%' ";
								$where.= " OR `vlastname` LIKE '%".$find."%' ";
								$where.= " OR `vemail` LIKE '%".$find."%' ";
								$where.= " OR `vfax` LIKE '%".$find."%' ";
								$where.= " OR `vphone` LIKE '%".$find."%' ";
							$where.= ") ";
						}
					$where.= ") OR `U`.`uid` IN (";
						$where.= "SELECT ";
						$where.= "`iuser_uid` ";
						$where.= "FROM ";
						$where.= "`profile_schoolteacher` ";
						$where.= "WHERE ";
						$where.= "`school_id` = '".$_SESSION['user']['school_uid']."'";
						if(trim($find) != '') {
							$where.= " AND ";
							$where.= "( ";
									$where.= "`vfirstname` LIKE '%".$find."%' ";
									$where.= " OR `vlastname` LIKE '%".$find."%' ";
									$where.= " OR `vemail` LIKE '%".$find."%' ";
									$where.= " OR `vphone` LIKE '%".$find."%' ";
							$where.= ") ";
						}
					$where.= ")	OR `U`.`uid` IN (";
						$where.= "SELECT ";
						$where.= "`iuser_uid` ";
						$where.= "FROM ";
						$where.= "`profile_student` ";
						$where.= "WHERE ";
						$where.= "`school_id` = '".$_SESSION['user']['school_uid']."'";
						if(trim($find) != '') {
							$where.= " AND ";
							$where.= "( ";
									$where.= "`vfirstname` LIKE '%".$find."%' ";
									$where.= " OR `vlastname` LIKE '%".$find."%' ";
							$where.= ") ";
						}
					$where.= ")";
				$where.= ")";
			}

			if($_SESSION['user']['userRights'] == 'schooladmin' && isset($_SESSION['user']['school_uid'])) {
				$where.= " AND (";
					$where.= "`U`.`uid` IN (";
						$where.= "SELECT ";
						$where.= "`iuser_uid` ";
						$where.= "FROM ";
						$where.= "`profile_schoolteacher` ";
						$where.= "WHERE ";
						$where.= "`school_id` = '".$_SESSION['user']['school_uid']."'";
						if(trim($find) != '') {
							$where.= " AND ";
							$where.= "( ";
									$where.= "`vfirstname` LIKE '%".$find."%' ";
									$where.= " OR `vlastname` LIKE '%".$find."%' ";
									$where.= " OR `vemail` LIKE '%".$find."%' ";
									$where.= " OR `vphone` LIKE '%".$find."%' ";
							$where.= ") ";
						}
					$where.= ") OR `U`.`uid` IN (";
						$where.= "SELECT ";
						$where.= "`iuser_uid` ";
						$where.= "FROM ";
						$where.= "`profile_student` ";
						$where.= "WHERE ";
						$where.= "`school_id` = '".$_SESSION['user']['school_uid']."'";
						if(trim($find) != '') {
							$where.= " AND ";
							$where.= "( ";
									$where.= "`vfirstname` LIKE '%".$find."%' ";
									$where.= " OR `vlastname` LIKE '%".$find."%' ";
							$where.= ") ";
						}
					$where.= ")";
				$where.= ")";
			}

			if($_SESSION['user']['userRights'] == 'schoolteacher' && isset($_SESSION['user']['school_uid'])){
				$where .= " AND (";
					$where.= "`U`.`uid` IN (";
						$where.= "SELECT ";
						$where.= "`iuser_uid` ";
						$where.= "FROM ";
						$where.= "`profile_student` ";
						$where.= "WHERE ";
						$where.= "`school_id` = '".$_SESSION['user']['school_uid']."'";
						if(trim($find) != '') {
							$where.= " AND ";
							$where.= "( ";
									$where.= "`vfirstname` LIKE '%".$find."%' ";
									$where.= " OR `vlastname` LIKE '%".$find."%' ";
							$where.= ") ";
						}
					$where.= ")";
				$where.= ")";
			}
		} else if(trim($find) != '') {
			$where.= " AND (";
					$where.= "`U`.`uid` IN (";
						$where.= "SELECT ";
						$where.= "`user_uid` ";
						$where.= "FROM ";
						$where.= "`users_schools` ";
						$where.= "WHERE ";

						$where.= "( ";
								$where.= "`name` LIKE '%".$find."%' ";
								$where.= " OR `school` LIKE '%".$find."%' ";
								$where.= " OR `contact` LIKE '%".$find."%' ";
								$where.= " OR `phone_number` LIKE '%".$find."%' ";
						$where.= ") ";

					$where.= ") OR `U`.`uid` IN (";
						$where.= "SELECT ";
						$where.= "`iuser_uid` ";
						$where.= "FROM ";
						$where.= "`profile_schooladmin` ";
						$where.= "WHERE ";
						$where.= "( ";
								$where.= "`vfirstname` LIKE '%".$find."%' ";
								$where.= " OR `vlastname` LIKE '%".$find."%' ";
								$where.= " OR `vemail` LIKE '%".$find."%' ";
								$where.= " OR `vfax` LIKE '%".$find."%' ";
								$where.= " OR `vphone` LIKE '%".$find."%' ";
						$where.= ") ";

					$where.= ") OR `U`.`uid` IN (";
						$where.= "SELECT ";
						$where.= "`iuser_uid` ";
						$where.= "FROM ";
						$where.= "`profile_schoolteacher` ";
						$where.= "WHERE ";
						$where.= "( ";
								$where.= "`vfirstname` LIKE '%".$find."%' ";
								$where.= " OR `vlastname` LIKE '%".$find."%' ";
								$where.= " OR `vemail` LIKE '%".$find."%' ";
								$where.= " OR `vphone` LIKE '%".$find."%' ";
						$where.= ") ";

					$where.= ") OR `U`.`uid` IN (";
						$where.= "SELECT ";
						$where.= "`iuser_uid` ";
						$where.= "FROM ";
						$where.= "`profile_homeuser` ";
						$where.= "WHERE ";
						$where.= "( ";
								$where.= "`vfirstname` LIKE '%".$find."%' ";
								$where.= " OR `vlastname` LIKE '%".$find."%' ";
								$where.= " OR `vemail` LIKE '%".$find."%' ";
								$where.= " OR `vphone` LIKE '%".$find."%' ";
						$where.= ") ";

					$where.= ") OR `U`.`uid` IN (";
						$where.= "SELECT ";
						$where.= "`iuser_uid` ";
						$where.= "FROM ";
						$where.= "`profile_student` ";
						$where.= "WHERE ";
						$where.= "( ";
								$where.= "`vfirstname` LIKE '%".$find."%' ";
								$where.= " OR `vlastname` LIKE '%".$find."%' ";
						$where.= ") ";
					$where.= ")";
				$where.= ")";
		}
		return $where;
		//$query = "SELECT COUNT(`U`.`uid`) FROM `user` as `U` WHERE `U`.`deleted` != '1' AND (`U`.`uid` IN (SELECT `iuser_uid` FROM `profile_schooladmin` WHERE `school_id` = '826') OR `U`.`uid` IN (SELECT `iuser_uid` FROM `profile_schoolteacher` WHERE `school_id` = '826') OR `U`.`uid` IN (SELECT `iuser_uid` FROM `profile_student` WHERE `school_id` = '826')) ";

	}

	public function	isCreateSuccessful() {

		$insert_id	= 0;
		$response	= $this->isValidData();
		if(!empty ($response) && $response[0] == false){
			$insert_id		= $this->insertFromAdmin();
			$response[1][]	= "User Added Successfully";
		}

		$message_type	= ($response[0]	== true)?"error":"success";
		return array($message_type,$response[1],$insert_id);
	}

	public function	insertFromAdmin() {

		$insert_id	= null;
		$email		= mysql_real_escape_string($this->arrFields['email']['Value']);
		$password	= mysql_real_escape_string($this->arrFields['password']['Value']);
		$referral	= mysql_real_escape_string($_POST['referral']);

		$sql = "INSERT INTO `user` (";
			$sql.= "`registered_dts`, ";
			$sql.= "`email`, ";
			$sql.= "`password`, ";
			$sql.= "`access_allowed`, ";
			$sql.= "`deleted`, ";
			$sql.= "`registration_ip`, ";
			$sql.= "`registration_key`, ";
			$sql.= "`allow_access_without_sub`, ";
			$sql.= "`optin`, ";
			$sql.= "`referral`, ";
			$sql.= "`verified_dts`, ";
			$sql.= "`locale`";
		$sql.= ") VALUES (";
			$sql.= "NOW(), ";
			$sql.= "'{$email}', ";
			$sql.= "'{$password}', ";
			$sql.= "'{$this->arrFields['access_allowed']['Value']}', ";
			$sql.= "'{$this->arrFields['deleted']['Value']}', ";
			$sql.= "'".$_SERVER['REMOTE_ADDR']."', ";
			$sql.= "'".session_id()."',	";
			$sql.= "'".$_POST['allow_access_without_sub']."', ";
			$sql.= "'".$_POST['optin']."', ";
			$sql.= "'".addslashes($referral)."', ";
			$sql.= "NOW(), ";
			$sql.= "'".$_POST['locale']."'";
		$sql.= ")";

		$result = database::query($sql);

		if($result && mysql_error()=='') {
			$insert_id = mysql_insert_id();
		}

		$this->SetRegistrationKey( $insert_id, $_SERVER['REMOTE_ADDR'] );
		return $insert_id;
	}

	public function	isUpdateSuccessFul() {

		$response	= $this->isValidData(true);
		$insert_id	= 0;

		if(!empty ($response) && $response[0] == false)	{
			$this->save();
			$insert_id		= $this->get_uid();
			//$this->insertChangeInTransaction($insert_id);
			$response[1][]	= "User Updated Successfully";
		}

		$message_type =	($response[0] == true)?"error":"success";
		return array($message_type,$response[1],$insert_id);
	}

	public function	insertChangeInTransaction($row_uid = 0)	{
		if(!empty($this->arrForm)	&& is_numeric($row_uid)	&& $row_uid	> 0) {

			$objUT = new user_transaction();

			foreach($this->arrForm as $key => $val) {
				$objUT->set_row_uid($row_uid);
				$objUT->set_field_name($key);
				$objUT->set_field_value_was($val);
				$objUT->set_field_updated_dts(date("Y-m-d H:i:s"));
				$objUT->set_changed_by_user_uid($_SESSION['user']['uid']);
				$objUT->set_changed_by_user_type(1);
				$objUT->set_session_uid(session_id());
				$objUT->set_changed_by_ip_address($_SERVER['REMOTE_ADDR']);
				$objUT->insert_transaction();
			}
			$objUT->commit_transaction();
			unset($this->arrForm);
		}
	}

	protected function isValidData($update = false)	{
		$user_uid			= (isset($_POST['user_uid'])	  ?	format::to_integer($_POST['user_uid']) : '0');
		$email				= (isset($_POST['email'])		  ?	format::to_string($_POST['email']) : '');
		$password			= (isset($_POST['password'])	  ?	format::to_string($_POST['password']) :	'');
		$conf_password		= (isset($_POST['conf_password']) ?	format::to_string($_POST['conf_password']) : '');
		$allow_access		= (isset($_POST['allow_access'])  ?	format::to_integer($_POST['allow_access']) : '');
		$is_admin			= (isset($_POST['is_admin'])	  ?	format::to_integer($_POST['is_admin']) : '');
		$is_deleted			= (isset($_POST['deleted'])		  ?	format::to_integer($_POST['deleted']) :	'');

		$error				= false;
		$message			= array();

		if(is_numeric($user_uid) &&	$user_uid >	0) {
			parent::__construct($user_uid,__CLASS__);
			$this->load();
		}

		if(strlen($email) <= 0 || strlen($email) > 255)	{

			$error = true;
			$message['email_error']	= "Please Provide Valid	Email";

		#} else if($this->email_exist($email)) {
#
#			$error = true;
#			$message['email_error'] = "Email Already Exist";
#
		} else if($this->email_exist($email)) {

			$error = true;
			$message['email_error'] = "Email Already Exist As Username!";

		} /*
		else if(!validation::isValid('email',$email) ) {
			$message['email_error'] = "Please enter valid email.";
		} */


		if($update == false) {
			if(strlen($password) <=	3 ) {
				$error =   true;
				$message['pass_error'] = "Please Provide Password";
			} else if($password	!= $conf_password) {
				$error =   true;
				$message['cpass_error']	= "Password	and	confirm	Password do	not	match";
			}
		} else if($password	!= $conf_password) {
			$error = true;
			$message['cpass_error']	= "Password	and	confirm	Password do	not	match";
		}

		if(isset($_POST['user_type']) && $_POST['user_type'] ==	'')	{
			$error =   true;
			$message['user_type_error']	= "Please select user type.";
		}

		if(isset($_POST['locale']) && $_POST['locale'] == '') {
			$error =   true;
			$message['locale_error'] = "Please select locale.";
		}

		if(!$error) {

			// get the old values to store in the transaction
			if($update)	{
				if($this->get_email() != $email) {
					$this->arrForm['email'] =	$this->get_email();;
				}
				if($this->get_access_allowed() != (int)$allow_access) {
					$this->arrForm['access_allowed'] = $this->get_access_allowed();
				}
				if($this->get_is_admin() !=	(int)$is_admin)	{
					$this->arrForm['is_admin'] = $this->get_is_admin();
				}
				if($this->get_deleted()	!= (int)$is_deleted) {
					$this->arrForm['deleted']	= $this->get_deleted();
				}
			}

			$this->set_email($email);
			if($password !=	"")	{
				$this->set_password(md5($password));
			}
			$this->set_access_allowed($allow_access);
			$this->set_is_admin($is_admin);
			$this->set_deleted($is_deleted);
			$this->set_allow_access_without_sub($_POST['allow_access_without_sub']);
			$this->set_optin($_POST['optin']);
			$this->set_referral($_POST['referral']);
			$this->set_locale($_POST['locale']);

			if(isset($_POST['user_type'])) {
				$this->set_user_type($_POST['user_type']);
			}
		}
		return array($error,$message);
	}

	public function	isAdmin() {
		return ($this->get_is_admin() == 1)	? true : false;
	}

	public function	userRedirectUrl() {
		if(isset($_SESSION['user'])	&& isset($_SESSION['user']['admin']) &&	$_SESSION['user']['admin'] == 1) {
			return "admin";
		} else if(isset($_SESSION['user']) && isset($_SESSION['user']['user_type'])	&& is_array($_SESSION['user']['user_type'])	) {
			if(	in_array('student',$_SESSION['user']['user_type']) || in_array('homeuser',$_SESSION['user']['user_type']) )	{
				return "flash";
			} else if($_SESSION['user']['ByOpenUserName'] == 1){
				if($this->get_package_count()==2) {
					return 'selection';
				} else {
					return 'flash';
				}
			} else {
				return "account";
			}
		}
	}

	public function	isUser() {
		return $this->get_active();
	}

	public static function isLoggedIn() {
		$response = false;
		if(isset($_SESSION['user']) && isset($_SESSION['user']['uid']) && $_SESSION['user']['uid'] > 0) {
			$response =	true;
		}
		return $response;
	}

	public function	email_exist($email = "") {

		$found	= false;
		$email	= mysql_real_escape_string($email);

		$sql = "SELECT ";
		$sql.= "`uid` ";
		$sql.= "FROM ";
		$sql.= "`user` ";
		$sql.= "WHERE ";
		$sql.= "`email` = '$email' ";
		$sql.= "AND ";
		$sql.= "`deleted` = '0' ";

		if($this->get_uid() != null) {
			$sql .= " AND `uid` != '{$this->get_uid()}'";
		}
		$sql .= " LIMIT 1";

		$result = database::query($sql);

		if($result && mysql_error()==''	&& mysql_num_rows($result) > 0) {
			$found = true;
		}
		return $found;
	}

	public function username_exist($username_open = "") {

		$found = false;
		$username_open = mysql_real_escape_string($username_open);

		$sql = "SELECT ";
		$sql.= "`uid` ";
		$sql.= "FROM ";
		$sql.= "`user` ";
		$sql.= "WHERE ";
		$sql.= "`username_open` = '$username_open' ";
		$sql.= "AND ";
		$sql.= "`deleted` = '0' ";
		if($this->get_uid() != null) {
			$sql .= " AND `uid` != '{$this->get_uid()}'";
		}
		$sql .=" LIMIT 1";

		$result = database::query($sql);

		if($result && mysql_error()==''	&& mysql_num_rows($result) > 0)	{
			$found = true;
		}
		return $found;
	}

	public function SubscribeSave() {

		/**
		 * NOTE: change	$this->arrFields['key']['Value'] = 'something';
		 *		to $this->set_$key('something');
		 */

		$response	=false;
		$message	= '';
		$errors		= array ();

		$form1 = isset($_SESSION['form1']) ? $_SESSION['form1']	: array	();
		$form2 = isset($_SESSION['form2']) ? $_SESSION['form2']	: array	();
		$form3 = isset($_SESSION['form3']) ? $_SESSION['form3']	: array	();

		if(count($form1) > 0 &&	count($form2) >	0 && count($form3) > 0) {
			/**
			 * Add user	to database	and	add	to subscriptions if	necessary too
			 */
			$time				= time();
			$ip_address			= addslashes(substr($_SERVER['REMOTE_ADDR'],0,32));
			$registration_key	= md5($form2['school_name']['value'].$ip_address);

			$paths = config::get('paths');

			/* Set values to user table	fields */
			$this->arrFields['registered_dts']['Value']		= date('Y-m-d H:i:s',$time);
			$this->arrFields['registration_ip']['Value']	= $ip_address;
			$this->arrFields['registration_key']['Value']	= $registration_key;
			$this->arrFields['affiliate']['Value']			= mysql_real_escape_string(@$_SESSION['aff']);
			$this->arrFields['email']['Value']				= mysql_real_escape_string($form1['email']['value']);

			if(isset($form3['username_open']['value']) && $form3['username_open']['value'] != '') {
				$this->arrFields['username_open']['Value'] = mysql_real_escape_string($form3['username_open']['value']);
			}

			$this->arrFields['password']['Value']			= md5($form1['password']['value']);
			$this->arrFields['password_open']['Value']		= mysql_real_escape_string($form3['password_open']['value']);

			/*
			if(isset($form3['password_open']['value']) && $form3['password_open']['value'] != '')
			$this->arrFields['password']['Value'] =	md5($form1['password']['value']);
			$this->arrFields['password_open']['Value'] = mysql_real_escape_string($form1['password']['value']);
			 *
			 */

			$this->arrFields['access_allowed']['Value']				= 1;
			$this->arrFields['allow_access_without_sub']['Value']	= 1;
			$this->arrFields['active']['Value']						= 1;
			$this->arrFields['locale']['Value']						= mysql_real_escape_string(config::get('locale'));
			$this->arrFields['user_type']['Value']					= 'school';
			$this->arrFields['reseller_code_uid']['Value']			= mysql_real_escape_string($form1['reseller_code_uid']['value']);

			/* Set values to user table	fields END */
			// insert record to	table.
			$response =	$this->insert();

			// the following function will set registration key and that we'll use to cancel account
			$this->SetRegistrationKey( $response, $ip_address );
		}

		return $response;

	}

	public function	SubscribeSaveHomeUser() {
		/**
		 * Fix calls to	$this->arrFields - to use $this->set_fieldname($value);	instead
		 */
		$response	= false;
		$message	= '';
		$errors		= array();

		$form1 = isset($_SESSION['form1']) ? $_SESSION['form1']	: array	();
		$form2 = isset($_SESSION['form2']) ? $_SESSION['form2']	: array	();
		$form3 = isset($_SESSION['form3']) ? $_SESSION['form3']	: array	();

		if(count($form1) > 0) {
			/**
			 * Add user	to database	and	add	to subscriptions if	necessary too
			 */
			$time				= time();
			$ip_address			= addslashes(substr($_SERVER['REMOTE_ADDR'],0,32));
			$registration_key	= md5($form1['email']['value'].$ip_address);

			/* Set values to user table	fields */
			$this->arrFields['registered_dts']['Value']		= date('Y-m-d H:i:s',$time);
			$this->arrFields['registration_ip']['Value']	= $ip_address;
			$this->arrFields['registration_key']['Value']	= $registration_key;
			$this->arrFields['affiliate']['Value']			= mysql_real_escape_string(@$_SESSION['aff']);
			$this->arrFields['email']['Value']				= mysql_real_escape_string($form1['email']['value']);

			if(isset($form3['username_open']['value']) && $form3['username_open']['value'] != '')
				$this->arrFields['username_open']['Value']	= mysql_real_escape_string($form3['username_open']['value']);

			$this->arrFields['password']['Value']			= md5($form1['password']['value']);
			$this->arrFields['password_open']['Value']		= mysql_real_escape_string($form1['password']['value']);

			/*
			if(isset($form3['password_open']['value']) && $form3['password_open']['value'] != '')
			$this->arrFields['password']['Value'] =	md5($form1['password']['value']);
			$this->arrFields['password_open']['Value'] = mysql_real_escape_string($form1['password']['value']);
			 *
			 */

			$this->arrFields['allow_access_without_sub']['Value']	= 1;
			$this->arrFields['active']['Value']						= 1;
			$this->arrFields['locale']['Value']						= mysql_real_escape_string(config::get('locale'));
			$paths = config::get('paths');
			$this->arrFields['user_type']['Value']					= 'homeuser';

			/* Set values to user table	fields END */
			// insert record to	table.
			$response = $this->insert();

			// the following function will set registration key and that we'll use to cancel account
			$this->SetRegistrationKey( $response, $ip_address );
			return $response;
		}
	}

	public function CreateStudentUser( $username, $password, $locale ) {
		$response = false;
		$time				= time();
		$ip_address			= addslashes(substr($_SERVER['REMOTE_ADDR'],0,32));
		$this->arrFields['registered_dts']['Value']		= date('Y-m-d H:i:s',$time);
		$this->arrFields['registration_ip']['Value']	= $ip_address;
		//$this->arrFields['email']['Value']			= mysql_real_escape_string($username);
		$this->arrFields['password']['Value']			= md5($password);
		$this->arrFields['access_allowed']['Value']		= 1;
		$this->arrFields['allow_access_without_sub']['Value'] =1;
		$this->arrFields['active']['Value']				= 1;
		$this->arrFields['locale']['Value']				= mysql_real_escape_string($locale);
		$this->arrFields['user_type']['Value']			= 'student';
		$response										= $this->insert();
		$this->SetRegistrationKey( $response, $ip_address );
		$this->SetUserName( $response, $username );
		return $response;

	}

	public function SetUserName( $uid, $username ) {
		$email = $username.$uid;
		$sql = "UPDATE `user` SET ";
		$sql .="`email` = '".strtolower(mysql_real_escape_string($email))."' ";
		$sql .="WHERE `uid` = '".$uid."'";
		database::query($sql);
	}

	public function SetRegistrationKey( $uid, $ip_address ) {
		$md5 = md5( $uid .'-'. $ip_address );
		$sql = "UPDATE `user` SET ";
		$sql .="`registration_key` = '".$md5."' ";
		$sql .="WHERE `uid` = '".$uid."'";
		database::query($sql);
	}


	public static function getUserByEmailAddress($email	= "") {

		$user_uid	= false;
		$email		= addslashes(mysql_real_escape_string($email));

		$sql = "SELECT ";
		$sql.= "`uid` ";
		$sql.= "FROM ";
		$sql.= "`user` ";
		$sql.= "WHERE ";
		$sql.= "`email`	= '".$email."' ";
		$sql.= "AND ";
		$sql.= "`deleted` = '0' ";
		$sql.= "LIMIT 1";

		$result		= database::query($sql);

		if($result && mysql_error()==''	&& mysql_num_rows($result) > 0)	{
			$row		= mysql_fetch_assoc($result);
			$user_uid	= $row['uid'];
		}
		return $user_uid;
	}

	public static function getUserByOpenUserName($username_open	= "") {

		$user_uid		= false;
		$username_open	= addslashes(mysql_real_escape_string($username_open));
		$sql = "SELECT ";
		$sql.= "`uid` ";
		$sql.= "FROM ";
		$sql.= "`user` ";
		$sql.= "WHERE ";
		$sql.= "`username_open` = '$username_open' ";
		$sql.= "AND ";
		$sql.= "`deleted` = '0' ";
		$sql.= "LIMIT 1";

		$result = database::query($sql);
		if($result && mysql_error()==''	&& mysql_num_rows($result) > 0) {
			$row		= mysql_fetch_assoc($result);
			$user_uid	= $row['uid'];
		}
		return $user_uid;
	}

	// operations to perform
	public function	isValidLogin() {
		$response =	array (
			'fields'=>array	(
				'email'		=> array (
					'default'	=> 'Email Address',
					'message'	=> '',
					'highlight'	=> false,
					'error'		=> false,
					'value'		=> ''
				),
				'password'		=> array (
					'default'	=> '',
					'message'	=> '',
					'error'		=> false,
					'highlight'	=> false
				)
			),
			'message' => ''
		);

		$user_uid	= 0;
		$error		= false;

		// validation start here
		if(validation::isPresent('email',$_POST)) {
			if(validation::isValid('text',$_POST['email'])) {
				if((($user_uid = self::getUserByEmailAddress($_POST['email'])) === false) && (($user_uid = self::getUserByOpenUserName($_POST['email'])) === false)	 ) {
					$response['fields']['email']['message']		= "The details you have entered do not match our records.";
					$response['fields']['email']['error']		= true;
					$response['fields']['email']['highlight']	= true;
					$response['message']						= "The details you have entered do not match our records.";
				} else {
					$response['fields']['email']['value']		= $_POST['email'];
				}
			} else {
				$response['fields']['email']['message']			= 'Please enter a valid email address.';
				$response['fields']['email']['error']			= true;
				$response['fields']['email']['highlight']		= true;
				$response['message']							= "Please enter a valid email address.";
			}
		} else {
			$response['fields']['email']['message']				= 'Email address is requried.';
			$response['fields']['email']['error']				= true;
			$response['fields']['email']['highlight']			= true;
			$response['message']								= "Email address is requried.";
		}

		if(validation::isPresent('password',$_POST)) {
			if(!validation::isValid('text',$_POST['password'])) {
				$response['fields']['password']['message']	= 'Please enter a valid password.';
				$response['fields']['password']['error']	= true;
				$response['fields']['password']['highlight']= true;
				$response['message']						= "Please enter a valid password.";
			} else {
			}
		} else {
			$response['fields']['password']['message']		= 'Password is required.';
			$response['fields']['password']['error']		= true;
			$response['fields']['password']['highlight']	= true;
			$response['message']							= "Password is required.";
		}

		if(is_numeric($user_uid) &&	$user_uid > 0) {

			parent::__construct($user_uid);
			$this->load();
			$oldPassword		= $this->get_password();
			$oldOpenPassword	= $this->get_password_open();

			if($this->get_access_allowed() == 0) {
				$response['message'] = 'The details you have entered do not match our records.';
			} else if(self::getUserByOpenUserName($_POST['email']) === $user_uid) {
				if($_POST['password'] != $oldOpenPassword) {
					if($this->get_user_type() === 'student') {
					}
					$response['message'] = 'The details you have entered do not match our records.';
				}
			} else if(self::getUserByEmailAddress($_POST['email']) === $user_uid) {


				if($this->get_user_type() === 'student') {
					if(!$this->checkStudentsMultiplePasswords($user_uid)) {
						//$response['message'] = 'The details you have entered do not match our records.';
						if(md5($_POST['password']) != $oldPassword) {
							$response['message'] = 'The details you have entered do not match our records.';
						}
					}
				} else {
					if(md5($_POST['password']) != $oldPassword)	{
						$response['message'] = 'The details you have entered do not match our records.';
					}
				}


			}
			if($this->get_deleted()	== 1) {
				$response['message']	= "The details you have entered do not match our records.";
			}
			$arrUserType = explode(',',$this->get_user_type());
			$arrPaidUserTypes = array (
									'school',
									'schooladmin',
									'schoolteacher',
									'student',
									'homeuser'
								);
			if($this->get_is_admin() ==	0 && count(array_intersect($arrPaidUserTypes, $arrUserType))){
				if($this->has_active_subscription()	== false) {
					$response['message'] = "Your subscription period has expired please renew now.";
				}
			}
		} else {
			//
		}

		if(count($response['fields']) > 0) {
			foreach($response['fields'] as $key => $data) {
				if($data['error'] == true) {
					$error = true;
					break;
				}
			}
		}

		if($response['message'] != "") {
			$error = true;
		}

		if(!$error)	{
			return true;
		} else {
			return $response;
		}
	}

	private function checkStudentsMultiplePasswords($iuser_uid=null) {
		if($iuser_uid!=null && is_numeric($iuser_uid)){
			$query ="SELECT ";
			$query.="`uid` ";
			$query.="FROM ";
			$query.="`profile_student` ";
			$query.="WHERE ";
			$query.="`iuser_uid`='".$iuser_uid."' ";
			$query.="LIMIT 0,1";
			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				$arrRow = mysql_fetch_array($result);
				$query ="SELECT ";
				$query.="`uid`, ";
				$query.="`class_uid` ";
				$query.="FROM ";
				$query.="`classes_student` ";
				$query.="WHERE ";
				$query.="`student_uid`='".$arrRow['uid']."' ";
				$query.="AND ";
				$query.="`student_password`='".mysql_real_escape_string($_POST['password'])."' ";
				$query.="LIMIT 0,1";
				$result = database::query($query);
				if(mysql_error()=='' && mysql_num_rows($result)) {
					$row = mysql_fetch_array($result);
					$_SESSION['user']['class_uid'] = $row['class_uid'];
					return true;
				}
			}
		}
		return false;
	}

	public function	has_active_subscription() {

		$arrUserType = explode(',',$this->get_user_type());
		$arrPaidUserTypes = array (
			'reseller',
			'affiliate',
			'translator'
		);
		if(count(array_intersect($arrPaidUserTypes, $arrUserType))) {
			return true;
		}
		
		
		$return		= false;
		$user_uid	= $this->getSchoolId();

		if($user_uid == 0) {
			return false;
		}

		$arrSubscription = false;
		$arrSubscription =subscriptions::getUserSubscriptionDetails($user_uid);
		if($arrSubscription!=false) {
			$now = time();
			if(strtotime($arrSubscription['due_date']) > $now) {
				$return	=  true;
			}

			if(strtotime($arrSubscription['expires_dts']) > $now){ // fix all verified dates
				$return	= true;
			}
		}
		return $return;
	}

	public function	isValidRegister() {
		$response =	array (
			'fields'=>array	(
				'email'				=> array (
					'default'	=> 'Email Address',
					'message'	=> '',
					'highlight'	=> false,
					'error'		=> false,
					'value'		=> ''
				),
				'password'			=> array (
					'default'	=> '',
					'message'	=> '',
					'error'		=> false,
					'highlight'	=> false
				),
				'confirm_password'	=> array (
					'default'	=> '',
					'message'	=> '',
					'error'		=> false,
					'highlight'	=> false
				)
			),
			'message' => ''
		);

		$user_uid	= 0;
		$error		= false;

		// validation start	here
		if(validation::isPresent('email',$_POST)) {
			if(validation::isValid('text',$_POST['email'])) {
				if(($user_uid  = self::getUserByEmailAddress($_POST['email'])) !== false) {
					$response['fields']['email']['message']		= "Email Address is	not	available";
					$response['fields']['email']['error']		= true;
					$response['fields']['email']['highlight']	= true;
				} else {
					$response['fields']['email']['value']		= $_POST['email'];
					$this->set_email($_POST['email']);
				}
			} else {
				$response['fields']['email']['message']			= 'Please Enter	a valid	email address';
				$response['fields']['email']['error']			= true;
				$response['fields']['email']['highlight']		= true;
			}
		} else {
			$response['fields']['email']['message']				= 'Email address is	requried';
			$response['fields']['email']['error']				= true;
			$response['fields']['email']['highlight']			= true;
		}

		if(validation::isPresent('password',$_POST)) {
			if(!validation::isValid('text',$_POST['password']))	{
				$response['fields']['password']['message']	= 'Please Enter	a valid	password';
				$response['fields']['password']['error']	= true;
				$response['fields']['password']['highlight']= true;
			} else {
				$this->set_password($_POST['password']);
			}
		} else {
			$response['fields']['password']['message']		= 'Password	is requried';
			$response['fields']['password']['error']		= true;
			$response['fields']['password']['highlight']	= true;
		}

		if(validation::isPresent('confirm_password',$_POST)) {
			if(!validation::isValid('text',$_POST['confirm_password']))	{
				$response['fields']['confirm_password']['message']	= 'Please Enter	a valid	Confirm	password';
				$response['fields']['confirm_password']['error']	= true;
				$response['fields']['confirm_password']['highlight']= true;
			} else if($_POST['confirm_password'] !=	$_POST['password'])	{
				$response['fields']['confirm_password']['message']	= 'Password	and	Confirm	password do	not	match';
				$response['fields']['confirm_password']['error']	= true;
				$response['fields']['confirm_password']['highlight']= true;
			}
		} else {
			$response['fields']['confirm_password']['message']		= 'Confirm Password	is requried';
			$response['fields']['confirm_password']['error']		= true;
			$response['fields']['confirm_password']['highlight']	= true;
		}
		if(count($response['fields']) >	0) {
			foreach($response['fields']	as $key	=> $data) {
				if($data['error'] == true) {
					$error = true;
					break;
				}
			}
		}

		if(!$error)	{
			return true;
		} else {
			return $response;
		}
	}

	/**
	 * DEPRECATED?
	 */
	public function	isValidLink($link =	"") {
		$valid					= false;
		$user_registration_uid	= 0;

		if($link !=	"")	{
			if(($user_registration_uid = user_registration::ket_exists($link)) !== false) {
				$user_registration	= new user_registration($user_registration_uid);
				if($user_registration->get_valid())	{
					$user_registration->load();
					parent::__construct($user_registration->get_user_uid(),__CLASS__);
					$this->load();
					$user_registration->delete();
					$valid = true;
				}
			}
		}
		return $valid;
	}

	public function	login($returnUrl = false) {

		$_SESSION['user']['uid']			= $this->get_uid();
		$_SESSION['user']['email']			= $this->get_email();
		$_SESSION['user']['type']			= $this->get_is_admin();
		$_SESSION['user']['admin']			= $this->get_is_admin();
		$_SESSION['user']['user_type']		= explode(',',@$this->get_user_type());
		$_SESSION['user']['prefix']			= $this->get_locale();
		$_SESSION['user']['reseller_uid']	= $this->getResellerUid($this->get_locale());
		$_SESSION['user']['logged_in']		= true;
		$_SESSION['user']['school_uid']		= $this->getSchoolIdForAccount();
		$_SESSION['user']['ByOpenUserName']	= 0;
		$_SESSION['user']['defaultPage']	= 'admin/users/school/';
		$_SESSION['user']['package_token']	= 'standard';
		$user_uid	= $this->getSchoolId();
		$arrSubscription = false;
		$arrSubscription =subscriptions::getUserSubscriptionDetails($user_uid);
		if($arrSubscription!=false) {
			$_SESSION['user']['package_token']	= $arrSubscription['package_token'];
		}
		if(isset($_SESSION['user']['user_type']) && is_array($_SESSION['user']['user_type']) && $this->get_is_admin() != 1)	{
			$_SESSION['user']['defaultPage']	= 'account/users/student/';
			$_SESSION['user']['defaultMenu']	= 'menu.schoolteacher.user';
			$_SESSION['user']['userRights']		= 'student';

			if(in_array('school',$_SESSION['user']['user_type']) ) {
				$_SESSION['user']['defaultPage']	= 'account/classes/list/';
				$_SESSION['user']['defaultMenu']	= 'menu.school.user';
				$_SESSION['user']['userRights']		= 'school';

			} else if( in_array('schooladmin',$_SESSION['user']['user_type']) ) {
				$_SESSION['user']['defaultPage']	= 'account/users/schoolteacher/';
				$_SESSION['user']['defaultMenu']	= 'menu.schooladmin.user';
				$_SESSION['user']['userRights']		= 'schooladmin';
			} else if( in_array('schoolteacher',$_SESSION['user']['user_type']) ){
				$_SESSION['user']['defaultPage']	= 'account/classes/list/';
				$_SESSION['user']['defaultMenu']	= 'menu.schoolteacher.user';
				$_SESSION['user']['userRights']		= 'schoolteacher';
			} else if( in_array('translator',$_SESSION['user']['user_type']) ){
				$_SESSION['user']['defaultPage'] 	= 'account/translations/';
				$_SESSION['user']['defaultMenu'] 	= 'menu.translator.user';
				$_SESSION['user']['userRights']		= 'translator';
				$_SESSION['user']['localeRights']	= profile_translator::GetLocaleRights($this->get_uid());
			} else if( in_array('reseller',$_SESSION['user']['user_type']) ){
				$_SESSION['user']['defaultPage'] 	= 'account/users/school';
				$_SESSION['user']['defaultMenu'] 	= 'menu.reseller.account';
				$_SESSION['user']['userRights']		= 'reseller';
				$_SESSION['user']['localeRights']	= profile_reseller::GetLocaleRights($this->get_uid());
				$_SESSION['user']['tracking_code']	= profile_reseller::getTrackingCode($this->get_uid());
			}
		}

		if(isset($_POST['email']) && self::getUserByOpenUserName($_POST['email']) === $this->get_uid()) {
			if($_POST['password'] == $this->get_password_open()) {
				$_SESSION['user']['ByOpenUserName'] = 1;
			}
		}

		$objShibboleth = new shibboleth();
		$objShibboleth->updateUserWithShibbolethId();
		
		logger::run(1);
		if($returnUrl == true) {
			return $this->userRedirectUrl();
		} else {
			$this->redirectTo($this->userRedirectUrl());
			echo 'yes'; exit;
		}
	}

	public function getResellerUid($locale=null) {
		if($locale!=null) {
			$query ="SELECT ";
			$query.="`iuser_uid` ";
			$query.="FROM ";
			$query.="`profile_reseller` ";
			$query.="WHERE ";
			$query.="`locale_rights`='".$locale."' ";
			$query.="LIMIT 0,1";
			$result = database::query($query);
			if($result && mysql_num_rows($result) && mysql_error()=='') {
				$arrRow = mysql_fetch_array($result);
				return $arrRow['iuser_uid'];
			}
			return null;
		}
		return null;
	}

	public function	getSchoolId() {
		$userType = array();
		$userType =	explode(',',$this->get_user_type());
		if(is_array($userType) && count($userType)){

			if(in_array( 'school', $userType )) {
				return $this->get_uid();
			} elseif( in_array(	'schooladmin', $userType ) ) {

				$sql = "SELECT ";
				$sql.= "`S`.`user_uid` ";
				$sql.= "FROM ";
				$sql.= "`profile_schooladmin` AS `T`, ";
				$sql.= "`users_schools` AS `S` ";
				$sql.= "WHERE ";
				$sql.= "`T`.`school_id` = `S`.`uid` ";
				$sql.= "AND `T`.`iuser_uid` = '".$this->get_uid()."' ";
				$sql.= "LIMIT 0,1";
				$result = database::query($sql);

				if(mysql_num_rows($result)){
					$row = mysql_fetch_array($result);
					return $row['user_uid'];
				}

			} elseif( in_array(	'schoolteacher', $userType ) ) {

				$sql = "SELECT ";
				$sql.= "`S`.`user_uid` ";
				$sql.= "FROM ";
				$sql.= "`profile_schoolteacher` AS `T`, ";
				$sql.= "`users_schools` AS `S` ";
				$sql.= "WHERE ";
				$sql.= "`T`.`school_id` = `S`.`uid` ";
				$sql.= "AND `T`.`iuser_uid` = '".$this->get_uid()."' ";
				$sql.= "LIMIT 0,1";

				$result = database::query($sql);

				if(mysql_num_rows($result)){
					$row = mysql_fetch_array($result);
					return $row['user_uid'];
				}

			} elseif( in_array('student', $userType ) ) {

				$sql = "SELECT ";
				$sql.= "`S`.`user_uid` ";
				$sql.= "FROM ";
				$sql.= "`profile_student` AS `PT`, ";
				$sql.= "`users_schools` AS `S` ";
				$sql.= "WHERE ";
				$sql.= "`PT`.`school_id` = `S`.`uid` ";
				$sql.= "AND `PT`.`iuser_uid` = '".$this->get_uid()."' ";
				$sql.= "LIMIT 0,1";

				$result = database::query($sql);

				if(mysql_num_rows($result)){
					$row = mysql_fetch_array($result);
					return $row['user_uid'];
				}

			} else if( in_array( 'homeuser', $userType ) ) {
				return $this->get_uid();
			}
		}

		return 0;
	}

	public function	getSchoolIdForAccount() {
		$userType = array();
		$userType = explode(',',$this->get_user_type());

		if(	is_array( $userType) && count($userType)){
			if(in_array( 'school',$userType )) {
				$sql = "SELECT ";
				$sql.= "`uid` ";
				$sql.= "FROM ";
				$sql.= "`users_schools` ";
				$sql.= "WHERE ";
				$sql.= "`user_uid` = '".$this->get_uid()."' ";
				$sql.= "LIMIT 0,1";

				$result = database::query($sql);

				if(mysql_num_rows($result)){
					$row = mysql_fetch_array($result);
					return $row['uid'];
				}

			} else if( in_array( 'schooladmin', $userType )) {

				$sql = "SELECT ";
				$sql.= "`school_id` ";
				$sql.= "FROM ";
				$sql.= "`profile_schooladmin` ";
				$sql.= "WHERE ";
				$sql.= "`iuser_uid` = '".$this->get_uid()."' ";
				$sql.= "LIMIT 0,1";

				$result = database::query($sql);
				if(mysql_num_rows($result)){
					$row = mysql_fetch_array($result);
					return $row['school_id'];
				}
			} else if( in_array( 'schoolteacher', $userType ) ) {

				$sql = "SELECT ";
				$sql.= "`school_id` ";
				$sql.= "FROM ";
				$sql.= "`profile_schoolteacher` ";
				$sql.= "WHERE ";
				$sql.= "`iuser_uid` = '".$this->get_uid()."' ";
				$sql.= "LIMIT 0,1";

				$result	= database::query($sql);

				if(mysql_num_rows($result)){
					$row = mysql_fetch_array($result);
					return $row['school_id'];
				}

			}
			else if( in_array( 'student', $userType ) ) {

				$sql = "SELECT ";
				$sql.= "`school_id` ";
				$sql.= "FROM ";
				$sql.= "`profile_student` ";
				$sql.= "WHERE ";
				$sql.= "`iuser_uid`	= '".$this->get_uid()."' ";
				$sql.= "LIMIT 0,1";

				$result = database::query($sql);

				if(mysql_num_rows($result)){
					$row = mysql_fetch_array($result);
					return $row['school_id'];
				}

			}
		}
		return 0;
	}

	public function	register($sendEmail	= true)	{
		$success = false;
		$this->set_registered_dts(date("Y-m-d H:i:s"));

		if($sendEmail) {
			$this->set_access_allowed(0);
		} else {
			$this->set_access_allowed(1);
		}

		$this->set_is_admin(0);
		$this->set_deleted(0);

		if(($insert_id = $this->insert()) !== false) {

			parent::__construct($insert_id,	__CLASS__);
			$this->load();
			$success = true;

			if($sendEmail) {
				$this->sendRegistrationEmail();
			}
			$this->login();
		}
		return $success;
	}

	public function	verify($sendEmail =	true) {
		if($this->get_access_allowed() != 1) {
			$this->arrForm['access_allowed']	= $this->get_access_allowed();
			$this->arrForm['verified_dts']	= $this->get_verified_dts();
			$this->set_access_allowed(1);
			$this->set_verified_dts(date("Y-m-d	H:i:s"));
			$this->save();
			$this->insertChangeInTransaction($this->get_uid());
			if($sendEmail) {
				$this->sendEmailWelcome();
			}
		}
	}

	public function	logout($redirect = false) {
		//session_destroy();
		foreach($_SESSION as $index => $val) {
			unset($_SESSION[$index]);
		}
		//session_regenerate_id();
		if(!isset($_SESSION['shibboleth_logout'])) {
			$_SESSION['shibboleth_logout']=true;
		}
		if($redirect) {
			output::redirect(config::url());
		}
	}

	public function	passwordStrength() {

	}

	public function	enable($sendEmail =	true) {
		if($this->get_allow_access() !=	1) {
			$this->arrForm['access_allowed'] = $this->get_access_allowed();
			$this->insertChangeInTransaction($this->get_uid());
		}
		$this->arrFields['access_allowed']['Value']	= 1;
		$this->save();
		if($sendEmail) {
			$this->sendEmailEnabled();
		}
	}

	public function	disable($sendEmail = true) {
		if($this->get_allow_access() !=	0) {
			$this->arrForm['access_allowed'] = $this->get_access_allowed();
			$this->insertChangeInTransaction($this->get_uid());
		}
		$this->arrFields['access_allowed']['Value']	= 0;
		$this->save();
		if($sendEmail) {
			$this->sendEmailDisabled();
		}
	}

	public function	changePassword($password = "",$sendEmail=false)	{
		if($this->get_password() !=	md5($password))	{
			$this->arrForm['password'] = $this->get_password();
			$this->insertChangeInTransaction($this->get_uid());
		}
		$this->arrFields['password']['Value'] =	md5($password);
		$this->save();
		if($sendEmail) {
			$this->sendEmailPasswordChanged();
		}
	}

	public function	getUsernamesMatching($pattern =	'',$excludeUsers=array()) {

		$arrResponse = array ();

		$sql = "SELECT ";
		$sql.= "`open_username`	";
		$sql.= "FROM ";
		$sql.= "`user` ";
		$sql.= "WHERE ";
		$sql.= "`open_username`	LIKE '".$pattern."'	";
		if(count($excludeUsers)	> 0) {
			$sql.= "AND	`uid` NOT IN (".implode(',',$excludeUsers).") ";
		}
		$sql.= "ORDER BY ";
		$sql.= "`open_username`	ASC";

		$res = database::query($sql);

		if($res	&& mysql_error()=='' &&	mysql_num_rows($res) > 0) {
			while($row = mysql_fetch_assoc($res)) {
				$arrResponse[] = stripslashes($row['open_username']);
			}
		}

		return $arrResponse;

	}

	public function	updateUsername($arrUsernames=array()){
		$firstname		= $this->get_vfirstname();
		$lastname		= $this->get_vlastname();
		$open_username	= substr($firstname,0,1).substr($lastname,0,1);
		$number			= rand(0,10).rand(0,10).rand(0,10).rand(0,10);

		if(count($arrUsernames)	< 1) {
			$arrUsernames	= $this->getUsernamesMatching($open_username.'%', array($this->get_uid()));
		}

		if(count($arrUsernames)	> 0) {
			foreach($arrUsernames as $username)	{
				if($username ==	$open_username . $number) {
					$this->updateUsername($arrUsernames);
				}
			}
		}
		$open_username.=$number;

		// check if	open_username already exists
		if(!$this->username_exists($open_username))	{
			$this->set_open_username($username);
		} else {
			// shouldn't occur indefinitely	- but perhaps up to	10 times this could	repeat,	depending on the number	of people in the DB?
			$this->updateUsername();
		}
	}

	public function	changeEmail() {

	}

	public function	sendEmailWelcome() {

	}

	public function	sendEmailDisabled()	{

	}

	public function	sendEmailEnabled() {

	}

	public function	sendEmailPasswordChanged() {

	}

	public function	sendEmailChangedEmail()	{

	}

	public function	sendRegistrationEmail()	{
		$md5Id		= $this->getMD5registerLink();
		$insert		= $this->setUserRegistrationKey($md5Id);

		if($insert)	{
			$link	= config::url("login/verify/$md5Id/");
			$mail	= new email_phpmailer();
			//$mail->AddReplyTo("name@yourdomain.com","First Last");
			//$mail->SetFrom('name@yourdomain.com',	'First Last');
			//$mail->AddReplyTo("name@yourdomain.com","First Last");
			$body				= $link;
			$address			= $this->get_email();
			$mail->Subject		= "Create Account Verification Email";
			$mail->AltBody		= "To view the message,	please use an HTML compatible email	viewer."; // optional, comment out and test

			$mail->AddAddress($address,	"");
			$mail->MsgHTML($body);
			$mail->Send();
		}
	}

	public function	getMD5registerLink() {
		$email	= $this->get_email();
		$uid	= $this->get_uid();
		$md5Id	= md5($email."_".$uid);

		return $md5Id;
	}

	public function	setUserRegistrationKey($md5Id =	"")	{
		$insert		= false;
		$insert_id	= null;

		if($md5Id != "") {

			$user_registration = new user_registration();
			$user_registration->set_user_uid($this->get_uid());
			$user_registration->set_key($md5Id);
			$user_registration->set_created_dts(date("Y-m-d	H:i:s"));

			if(($insert_id = $user_registration->insert()) !== false) {
				$insert	= true;
			}
		}
		return $insert;
	}


	public function getUserListByType($type=null, $where = array()) {
		if(!is_null($type)) {
			$and = '';
			foreach( $where as $idx => $val	){
				$and .= " AND `" . $idx . "` = '" . mysql_real_escape_string($val) . "'";
			}

			$sql = "SELECT ";
			$sql.= "* ";
			$sql.= "FROM ";
			$sql.= "`user` ";
			$sql.= "WHERE FIND_IN_SET('".strtolower( mysql_real_escape_string($type) )."',`user_type`) ";
			$sql.= $and;
			$sql.= " ORDER BY ";
			$sql.= "`registered_dts` DESC";

			$result	= database::query($sql);

			if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
				$body = make::tpl('body.admin.users.type.list');

				while($data	= mysql_fetch_assoc($result)) {
					$data['edit'] = 'profile/'.$type.'/';
					$panel = make::tpl('body.admin.users.list.row');
					$panel->assign($data);
					$page_rows[] = $panel->get_content();
				}

				$body->assign('users.rows',	implode('',$page_rows));

				return $body->get_content();
			}
			return 'Users not found.';
		}

	}

	public function getUserListForSchoolByType($type=null, $school_id=null, $ref_table=null, $where = array()) {
		if(is_null($type) || is_null($school_id) && is_null($ref_table)) {
			return 'Users not found.';
		}
		$and = "AND ";
		$and.= "`uid` IN ( ";
		$and.= "SELECT ";
		$and.= "`iuser_uid` ";
		$and.= "FROM ";
		$and.= mysql_real_escape_string($ref_table);
		$and.= " WHERE ";
		$and.= "`school_id` = '".mysql_real_escape_string($school_id)." '";
		$and.= ")";
		foreach( $where	as $idx => $val ){
			$and .= " AND `" . $idx . "` = '" . mysql_real_escape_string($val) . "'";
		}

		$sql = "SELECT ";
		$sql.= "* ";
		$sql.= "FROM ";
		$sql.= "`user` ";
		$sql.= "WHERE FIND_IN_SET('".strtolower( mysql_real_escape_string($type) )."',`user_type`) ";
		$sql.= $and;
		$sql.= " ORDER BY ";
		$sql.= "`registered_dts` DESC";

		$result	= database::query($sql);

		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			$body =	new	xhtml('body.admin.users.type.list');
			$body->load();

			while($data	= mysql_fetch_assoc($result)) {
				$data['edit'] =	'profile/'.$type.'/';
				$panel = new xhtml('body.admin.users.list.row');
				$panel->load();
				$panel->assign($data);
				$page_rows[] = $panel->get_content();
			}

			$body->assign('users.rows',	implode('',$page_rows));

			return $body->get_content();
		}
		return 'Users not found.';
	}

	public function getUserPackage() {
		$arrSupportLanguages = array();
		if(isset($_SESSION['user']['uid']) && isset($_SESSION['user']['school_uid'])) {
			if(isset($_SESSION['user']['userRights']) && $_SESSION['user']['userRights']=='student') {
				$query ="SELECT ";
				$query.="`support_language_uid` ";
				$query.="FROM ";
				$query.="`student_packages` ";
				$query.="WHERE ";
				$query.="`student_user_uid`='".$_SESSION['user']['uid']."' ";
				$query.="AND ";
				$query.="`school_uid`='".$_SESSION['user']['school_uid']."' AND ";
				$query.="`removed_by_uid`='0' ";
				$query.="AND ";
				$query.="`assigned_by_uid`!='0' ";
				$query.="GROUP BY ";
				$query.="`support_language_uid` ";
				$result = database::query($query);
				if(mysql_error()=='' && mysql_num_rows($result)) {
					while($arrRow = mysql_fetch_array($result)) {
						$arrSupportLanguages[] = $arrRow['support_language_uid'];
					}
				}

				if(isset($_SESSION['user']['class_uid'])) {
					$query ="SELECT ";
					$query.="`support_language_uid` ";
					$query.="FROM ";
					$query.="`class_packages` ";
					$query.="WHERE ";
					$query.="`class_uid`='".$_SESSION['user']['class_uid']."' ";
					$query.="AND ";
					$query.="`school_uid`='".$_SESSION['user']['school_uid']."' AND ";
					$query.="`removed_by_uid`='0' ";
					$query.="AND ";
					$query.="`assigned_by_uid`!='0' ";
					$query.="GROUP BY ";
					$query.="`support_language_uid` ";
					$result = database::query($query);
					if(mysql_error()=='' && mysql_num_rows($result)) {
						while($arrRow = mysql_fetch_array($result)) {
							$arrSupportLanguages[] = $arrRow['support_language_uid'];
						}
					}
				}
			} else {
					$query ="SELECT ";
					$query.="`support_language_uid` ";
					$query.="FROM ";
					$query.="`school_packages` ";
					$query.="WHERE ";
					$query.="`school_uid`='".$_SESSION['user']['school_uid']."' ";
					$query.="AND ";
					$query.="`canclled_by_uid`='0' ";
					$query.="AND ";
					$query.="`approved_by_uid`!='0' ";
					$query.="GROUP BY ";
					$query.="`support_language_uid` ";
					$result = database::query($query);
					if(mysql_error()=='' && mysql_num_rows($result)) {
						while($arrRow = mysql_fetch_array($result)) {
							$arrSupportLanguages[] = $arrRow['support_language_uid'];
						}
					}
			}
		}
		if(count($arrSupportLanguages)) {
			return array_unique($arrSupportLanguages);
		}
		return array(14);
	}

	private function get_package_count() {
		$user_uid = $this->getSchoolId();
		$query = "SELECT ";
		$query.= "DISTINCT `package_token` ";
		$query.= "FROM ";
		$query.= "`subscriptions` ";
		$query.= "WHERE ";
		$query.= "`user_uid` = '".$user_uid."' ";
		$query.= "AND ";
		$query.= "`expires_dts`>'".date('Y-m-d H:i:s')."' ";
		$result = database::query($query);
		if(mysql_error()==='') {
			return mysql_num_rows($result);
		} else {
			return 0;
		}
	}

	public function get_user_packages($user_uid=null) {
		$arrPackages = array();
		$query ="SELECT ";
		$query.="`package_token` ";
		$query.="FROM ";
		$query.="`subscriptions` ";
		$query.="WHERE ";
		$query.="`user_uid`='".$user_uid."' ";
		$query.="AND ";
		$query.="`expires_dts`>'".date('Y-m-d H:i:s')."' ";
		$result = database::query($query);
		if(mysql_error()==='' && mysql_num_rows($result)) {
			while($row=mysql_fetch_assoc($result)) {
				$arrPackages[] = $row['package_token'];
			}
		}
		return $arrPackages;
	}

	public function get_user_package_text($user_uid=null) {
		$package_text = '-';
		$arrPackages = $this->get_user_packages($user_uid);
		if(is_array($arrPackages) && count($arrPackages)) {
			if(count($arrPackages)==1) {
				switch ($arrPackages[0]) {
					case 'standard':
						$package_text = 'mfl';
					break;
					default:
						$package_text = $arrPackages[0];
					break;
				}
			} else if (count($arrPackages)==2) {
				$package_text = 'both';
			}
		}
		return $package_text;
	}
}

?>