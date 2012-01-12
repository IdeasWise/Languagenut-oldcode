<?php

/*
 * Shibboleth.php
 */

class Shibboleth {
	public $shibboleth_uid	= null;
	public $user_type		= null;
	public $institution_uid	= null;
	public function __construct() {

	}

	public function Init() {
		// following is test server vars
		//$_SERVER['persistent-id'] ='https://idp3.lgfl.org.uk/idp/shibboleth!https://www.languagenut.com/shibboleth!420E3C9D31A813C7378D9EA75601C9C3@uso.im';
		//$_SERVER['affiliation'] = 'student@westminster.gov.uk';
		
		// end of test server vars
		//if(!isset($_SESSION['user']) && isset($_SERVER['persistent-id']) && !isset($_SESSION['shibboleth_logout'])) {
		if(!isset($_SESSION['user']) && isset($_SERVER['persistent-id']) && isset($_SERVER['affiliation'])) {

			// check user exist with this shibboleth `persistent-id` var
			$arrExplode = explode('!',$_SERVER['persistent-id']);
			if(is_array($arrExplode) && count($arrExplode)==3) {
				$arrExplodeLast = explode('@',$arrExplode[2]);
				if(is_array($arrExplodeLast) && count($arrExplodeLast)==2) {
					$this->shibboleth_uid = $arrExplodeLast[0];
					if($user_uid=$this->does_shibboleth_user_exist()) {
						if($this->is_valid_user_uid($user_uid)) {
							$this->try_login($user_uid);
						}
					} else { 
						// check shibboleth user_type
						// get shibboleth user_type and institution_uid in class variables
						$arrAffiliation = explode('@',$_SERVER['affiliation']);
						if(is_array($arrAffiliation) && count($arrAffiliation) == 2) {
							$this->user_type = $arrAffiliation[0];
							$this->institution_uid = '@'.$arrAffiliation[1];
							if($school_uid=$this->does_shibboleth_school_exist()) {
								if($this->user_type == 'staff') {
									$user_uid = $this->CreateSchoolTeacher($school_uid);
									if($this->is_valid_user_uid($user_uid)) {
										$this->try_login($user_uid);
									}
								} else if($this->user_type == 'student' || $this->user_type == 'affiliate') {
									$user_uid = $this->CreateSchoolStudent($school_uid);
									if($this->is_valid_user_uid($user_uid)) {
										$this->try_login($user_uid);
									}
								}
							}
						}
					}
				}
			}
		}
	}

	private function is_valid_user_uid($user_uid=null) {
		if(is_numeric($user_uid) && $user_uid > 0) {
			return true;
		} else {
			return false;
		}
	}

	private function try_login($user_uid=null) {
		$objUser = new user($user_uid);
		$objUser->load();
		$error = false;
		if($objUser->get_access_allowed() == 0) {
			$error = true;
		}
		if($objUser->get_deleted()	== 1) {
			$error = true;
		}
		if($objUser->get_is_admin() ==	0){
			if($objUser->has_active_subscription()	== false) {
			$error = true;
			}
		}
		if($error===false) {
			$objUser->login();
		} else {
			
		}
	}

	private function does_shibboleth_school_exist() {
		if($this->institution_uid!=null) {
			$query ="SELECT ";
			$query.="`uid` ";
			$query.="FROM ";
			$query.="`users_schools` ";
			$query.="WHERE ";
			$query.="`institution_uid`='".mysql_real_escape_string($this->institution_uid)."' ";
			$query.="LIMIT 0,1";
			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				$row = mysql_fetch_assoc($result);
				return $row['uid'];
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	private function does_shibboleth_user_exist() {
		if($this->shibboleth_uid!=null) {
			$query ="SELECT ";
			$query.="`uid` ";
			$query.="FROM ";
			$query.="`user` ";
			$query.="WHERE ";
			$query.="`shibboleth_uid` = '".mysql_real_escape_string($this->shibboleth_uid)."' ";
			$query.="LIMIT 0,1";
			$result=database::query($query);
			if(mysql_error() == '' && mysql_num_rows($result)) {
				$row = mysql_fetch_array($result);
				return $row['uid'];
			}
			return false;
		}
		return false;
	}

	public function updateUserWithShibbolethId() {
		if(isset($_SESSION['user']['uid']) && isset($_SERVER['persistent-id'])) {
			// check user exist with this shibboleth `persistent-id` var
			$arrExplode = explode('!',$_SERVER['persistent-id']);
			if(is_array($arrExplode) && count($arrExplode)==3) {
				$arrExplodeLast = explode('@',$arrExplode[2]);
				if(is_array($arrExplodeLast) && count($arrExplodeLast)==2) {
					$this->shibboleth_uid = $arrExplodeLast[0];
					if($this->does_shibboleth_user_exist()===false) {
						$query ="UPDATE ";
						$query.="`user` ";
						$query.="SET ";
						$query.="`shibboleth_uid` = '".$this->shibboleth_uid."' ";
						$query.="WHERE ";
						$query.="`uid`='".$_SESSION['user']['uid']."' ";
						$query.="LIMIT 0,1";
						$result = database::query($query);
					}
				}
			}
		}
	}



	public function CreateSchoolTeacher($school_uid=null) {
		if($school_uid!=null) {
			$user_uid = $this->CreateSchoolUser(
				'schoolteacher',
				$school_uid
			);
			if($user_uid!=false) {
				// INSERT ADMIN IN USERS TEACHER PROFILE TABLE...
				$query = "INSERT INTO `profile_schoolteacher` SET ";
				$query .= "`iuser_uid` = '".$user_uid."', ";
				$query .= "`vfirstname` = 'default', ";
				$query .= "`vemail` = 'shibb.".$this->shibboleth_uid."@languagenut.com', ";
				$query .= "`school_id` = '".$school_uid."' ";
				$result = database::query( $query ) or die($query.'<br>'.mysql_error());
				return $user_uid;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function CreateSchoolStudent($school_uid=null) {
		if($school_uid!=null) {
			$user_uid = $this->CreateSchoolUser(
				'student',
				$school_uid
			);
			if($user_uid!=false) {
				// INSERT ADMIN IN USERS STUDENT PROFILE TABLE...
				$query = "INSERT INTO `profile_student` SET ";
				$query .= "`iuser_uid` = '".$user_uid."', ";
				$query .= "`vfirstname` = 'default', ";
				$query .= "`school_id` = '".$school_uid."' ";
				$result = database::query( $query ) or die($query.'<br>'.mysql_error());
				$student_uid = mysql_insert_id();
				$this->assignClass($school_uid,$student_uid);
				return $user_uid;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function assignClass ($school_uid=null,$student_uid=null) {
		if($school_uid!=null && $student_uid!=null) {
			if($class_uid=$this->does_default_class_exist($school_uid)) {
				$query ="INSERT INTO ";
				$query.="`classes_student` (";
					$query.="`class_uid`,";
					$query.="`student_uid`,";
					$query.="`student_password` ";
				$query.=") VALUES ( ";
					$query.="'".$class_uid."',";
					$query.="'".$student_uid."',";
					$query.="'default.".$student_uid."'";
				$query.=") ";
				$result = database::query($query) or die($query.'<br>'.mysql_error());
			} else {
				if($class_uid = $this->create_default_class($school_uid)) {
					$query ="INSERT INTO ";
					$query.="`classes_student` (";
						$query.="`class_uid`,";
						$query.="`student_uid`,";
						$query.="`student_password` ";
					$query.=") VALUES ( ";
						$query.="'".$class_uid."',";
						$query.="'".$student_uid."',";
						$query.="'default.".$student_uid."'";
					$query.=") ";
					$result = database::query($query) or die($query.'<br>'.mysql_error());
				}
			}
		}
	}

	public function does_default_class_exist($school_uid=null) {
		if($school_uid!=null) {
			$query = "SELECT ";
			$query.="`uid` ";
			$query.="FROM ";
			$query.="`classes` ";
			$query.="WHERE ";
			$query.="`is_default_class`='1' ";
			$query.="AND ";
			$query.="`school_id`='".$school_uid."' ";
			$query.="LIMIT 0,1";
			$result = database::query($query);
			if(mysql_error()== '' && mysql_num_rows($result)) {
				$row = mysql_fetch_assoc($result);
				return $row['uid'];
			}
		} else {
			return false;
		}
	}

	public function create_default_class($school_uid=null) {
		if($school_uid!=null) {
			if($teacher_user_uid=$this->create_default_teacher($school_uid)) {
				$query ="INSERT INTO ";
				$query.="`classes` (";
					$query.="`name`,";
					$query.="`school_id`,";
					$query.="`class_user_uid`, ";
					$query.="`is_default_class` ";
				$query.=") VALUES (";
					$query.="'default Class',";
					$query.="'".$school_uid."',";
					$query.="'".$teacher_user_uid."', ";
					$query.="'1' ";
				$query.=") ";
				$result = database::query($query) or die($query.'<br>'.mysql_error());
				$class_uid = mysql_insert_id();
				return $class_uid;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function create_default_teacher($school_uid=null) {
		if($school_uid!=null) {
			$user_uid = $this->CreateSchoolUser(
				'schoolteacher',
				$school_uid,
				'en',
				'defaultteacher.'.$school_uid.'@languagenut.com'
			);
			if($user_uid!=false) {
				// INSERT ADMIN IN USERS TEACHER PROFILE TABLE...
				$query = "INSERT INTO `profile_schoolteacher` SET ";
				$query .= "`iuser_uid` = '".$user_uid."', ";
				$query .= "`vfirstname` = 'default', ";
				$query .= "`vemail` = 'defaultteacher.".$school_uid."@languagenut.com', ";
				$query .= "`school_id` = '".$school_uid."' ";
				$result = database::query( $query ) or die($query.'<br>'.mysql_error());
				return $user_uid;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function CreateSchoolUser($user_type='schoolteacher',$school_uid=null,$school_locale='en',$default_email='') {
		$shibboleth_uid = $this->shibboleth_uid;
		if($default_email=='') {
			$default_email = 'shibb.'.$this->shibboleth_uid.'@languagenut.com';
		} else {
			$shibboleth_uid = '';
		}
		$query = "INSERT INTO `user` SET ";
		$query .= "`registered_dts` = '".date('Y-m-d H:i:s')."', ";
		$query .= "`registration_ip` = '".$_SERVER['REMOTE_ADDR']."', ";
		$query .= "`email` = '".$default_email."', ";
		$query .= "`password` = '".md5('default'.$school_uid)."', ";
		$query .= "`shibboleth_uid` = '".mysql_real_escape_string($shibboleth_uid)."', ";
		$query .= "`provider_uid` = '@atomwide', ";
		$query .= "`institution_uid` = '".mysql_real_escape_string($this->institution_uid)."', ";
		$query .= "`active` = '1', ";
		$query .= "`access_allowed` = '1', ";
		$query .= "`allow_access_without_sub` = '1', ";
		$query .= "`locale` = '".$school_locale."', ";
		$query .= "`user_type` = '".$user_type."' ";
		//$query;
		$uid = false;
		$res = database::query( $query );
		$uid = mysql_insert_id();

		if( is_numeric($uid) && $uid > 0 ) {
			$registration_key = md5( $uid .'-'. $_SERVER['REMOTE_ADDR'] );
			$sql = "UPDATE `user` SET ";
			$sql .="`registration_key` = '".$registration_key."' ";
			$sql .="WHERE `uid` = '".$uid."'";
			database::query($sql);
			return $uid;
		}
	}

}

?>