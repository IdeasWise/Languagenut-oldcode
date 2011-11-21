<?php

class remote_register extends Controller {

	public $xml = null;
	public function __construct ( ) {
		parent::__construct();	
		// can use a lot of memory, but not this much hopefully
		@ini_set('memory_limit', '256M');

	}

	public function ProcessRequest( $objRemote ) {
		$verifyReseller = false;
		$Xml = '';

/*		$url =  'http://127.0.0.1/languagenut/version.xml';
		if ( function_exists('curl_init') && 2==3 ) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; Konqueror/4.0; Microsoft Windows) KHTML/4.0.80 (like Gecko)");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_TIMEOUT, 60);
			$TinyContent = curl_exec($ch);		
			curl_close($ch);
		} else {
			$TinyContent =	@file_get_contents($url);
		}
		$_POST['xml'] = file_get_contents('http://127.0.0.1/languagenut/version.xml');
*/		
		
		if(!isset($_POST['xml'])) {
			header ("Content-Type:text/xml");
			echo '<?xml version="1.0" encoding="utf-8"?><error>Invalid XML Request...</error>';
			die();
		}

		$Xml = simplexml_load_string($_POST['xml']);
		/*if( trim($Xml->user_limit_message) != '' ) {
			echo '<pre>';
			print_r(trim($Xml->user_limit_message));
			echo '</pre>';
		}
		echo '<pre>';
		print_r($Xml);
		exit;*/

		if(isset($Xml->reseller_key)) {
			// VERIFY RESELLER IF MD5 IS OKAY IT RETURNS TRUE
			$verifyReseller = $objRemote->verifyReseller($Xml->reseller_key->Attributes()->key);

			if(empty($verifyReseller['error'])) {
				$this->ResponseXml('reseller_key', $Xml->reseller_key->Attributes());
			} else {
				$this->ResponseXml('reseller_key', $Xml->reseller_key->Attributes(), array('status'=>'failed','reason'=>$verifyReseller['error']));
			}

			if(empty($verifyReseller['error'])) {
				// CHECK IF XML HAS USER LIMIT REACHED/END MESSAGE IF YES THEN UPDATE RESELLER TABLE WITH THAT
				if( isset($Xml->user_limit_message) && trim($Xml->user_limit_message) != '' ) {
					$this->SetUserLimitReachedMessage($Xml->user_limit_message, $verifyReseller['data']);
				}
				// CHECK IF XML HAS NEW SCHOOL REGISTRATION REQUESTS ?
				if(isset($Xml->existing_schools)) { 
					$this->xml .= '<existing_schools>';
					foreach($Xml->existing_schools->school as $School) {
						$arrSchool = array();
						$arrSchool = $this->VerifySchool($School->Attributes()->key);
						if(isset($arrSchool['school_uid']) && $arrSchool['school_uid'] > 0 ) {
							if(isset($School->Attributes()->user_limit)) {
								$this->setSchoolUserLimit($School->Attributes(), $arrSchool['school_uid']);
							}
							$this->ResponseXml('school', $School->Attributes(), array('status'=>'found'));
							// CREATE SCHOOL ADMIN....
							if(isset($School->admins->admin)) {
								foreach($School->admins->admin as $Admin ) {
									$this->xml .= '<admins>';
									// CHECK IF USER IS EXIST OR NOT IF FALSE MEANS USER DOES NOT EXIST.
									if( $this->ValidateUser($Admin->Attributes(), 'asmin') === true ) {
										$arrSchoolAdmin = array();
										$arrSchoolAdmin = $this->RegisterAdmins( $Admin->Attributes(), $verifyReseller['data'], $arrSchool['school_uid'] );
									}
									$this->xml .= '</admins>';
								}
							}

							// CREATE SCHOOL TEACHER....
							if(isset($School->teachers->teacher)) {
								foreach($School->teachers->teacher as $Teacher ) {
									// CHECK IF USER IS EXIST OR NOT IF FALSE MEANS USER DOES NOT EXIST.
									$this->xml .= '<teachers>';
									if( $this->ValidateUser($Teacher->Attributes(), 'teacher') === true ) {
										$arrTeacher = array();
										$arrTeacher = $this->RegisterTeacher( $Teacher->Attributes(), $verifyReseller['data'], $arrSchool['school_uid'] );
									}
									$this->xml .= '</teachers>';
								}
							}
							// CREATE SCHOOL STUDENTS....
							foreach( $School->students->student as $Student ) {
								// CHECK IF USER IS EXIST OR NOT IF FALSE MEANS USER DOES NOT EXIST.
								$this->xml .= '<students>';
								if( $this->ValidateUser($Student->Attributes(), 'student',$arrSchool['school_uid']) === true ) {
									$arrStudent = array();
									$arrStudent = $this->RegisterStudent( $Student->Attributes(), $verifyReseller['data'], $arrSchool['school_uid'] );
								}
								$this->xml .= '</students>';
							}
						} else {
							$this->ResponseXml('school', $School->Attributes(), array('status'=>'failed','reason'=>'not_found'));
							$this->FailAllSubUsers($School);
						}
					}
					$this->xml .= '</existing_schools>';
				}


				// CHECK IF XML HAS NEW SCHOOL REGISTRATION REQUESTS ?
				if(isset($Xml->new_schools)) {
					$this->xml .= '<new_schools>';
					foreach($Xml->new_schools->school as $School) {
						$arrSchool = array();
						// CHECK IF USER IS EXIST OR NOT IF FALSE MEANS USER DOES NOT EXIST.
						if( $this->ValidateUser($School->Attributes(), 'school') === true ) {
							$arrSchool = $this->RegisterSchoolUser( $School->Attributes(), $verifyReseller['data'] );
							if($arrSchool['school_uid'] > 0 ) {
								// CREATE SCHOOL ADMIN....
								if(isset($School->admins->admin)) {
									foreach( $School->admins->admin as $Admin ) {
										$this->xml .= '<admins>';
										// CHECK IF USER IS EXIST OR NOT IF FALSE MEANS USER DOES NOT EXIST.
										if( $this->ValidateUser($Admin->Attributes(), 'asmin') === true ) {
											$arrSchoolAdmin = array();
											$arrSchoolAdmin = $this->RegisterAdmins( $Admin->Attributes(), $verifyReseller['data'], $arrSchool['school_uid'] );
										}
										$this->xml .= '</admins>';
									}
								}
								// CREATE SCHOOL TEACHER....
								if(isset($School->teachers->teacher)) {
									foreach($School->teachers->teacher as $Teacher ) {
										// CHECK IF USER IS EXIST OR NOT IF FALSE MEANS USER DOES NOT EXIST.
										$this->xml .= '<teachers>';
										if( $this->ValidateUser($Teacher->Attributes(), 'teacher') === true ) {
											$arrTeacher = array();
											$arrTeacher = $this->RegisterTeacher( $Teacher->Attributes(), $verifyReseller['data'], $arrSchool['school_uid'] );
										}
										$this->xml .= '</teachers>';
									}
								}

								// CREATE SCHOOL STUDENTS....
								if(isset($School->students->student)) {
									foreach( $School->students->student as $Student ) {
										// CHECK IF USER IS EXIST OR NOT IF FALSE MEANS USER DOES NOT EXIST.
										$this->xml .= '<students>';
										if( $this->ValidateUser($Student->Attributes(), 'student', $arrSchool['school_uid']) === true ) {
											$arrStudent = array();
											$arrStudent = $this->RegisterStudent( $Student->Attributes(), $verifyReseller['data'], $arrSchool['school_uid'] );
										}
										$this->xml .= '</students>';
									}
								}
							}
						} else {
							$this->FailAllSubUsers($School);
						}
					}
					$this->xml .= '</new_schools>';
				}
			}
			header ("Content-Type:text/xml");
			echo '<?xml version="1.0" encoding="utf-8"?><actions>'.$this->xml.'</actions>';
		} else {
			header ("Content-Type:text/xml");
			echo '<?xml version="1.0" encoding="utf-8"?><error>Invalid XML Request(reseller_key not found)...</error>';
		}
	}

	public function CheckUserExist( $email ) {
		$objUser = new user();
		return $objUser->email_exist($this->sql_quote($email));
	}

	public function RegisterSchoolUser( $school, $resller = array() ) {
		$arrUser	= array();
		$locale		= $resller['locale'];
		$arrUser	= $this->CreateUser($school, $locale, 'school');
		$res		= false;

		// INSERT SCHOOL IN USERS SCHOOLS TABLE...
		$school_uid = 0;
		$query = "INSERT INTO `users_schools` SET ";
		$query .= "`user_uid` = '".$arrUser['uid']."', ";
		$query .= "`name` = '".$this->sql_quote($school->contact_name)."', ";
		$query .= "`school` = '".$this->sql_quote($school->name)."', ";
		$query .= "`contact` = '".$this->sql_quote($school->contact_name)."', ";
		$query .= "`phone_number` = '".$this->sql_quote($school->phone)."' ";
		if(isset($school->user_limit)) {
			$query .= ", `user_limit` = '".$this->sql_quote($school->user_limit)."' ";
			$arrUser['user_limit'] = $school->user_limit;
		}
		$res		= database::query( $query ) or die($query.'<br>'.mysql_error());
		$school_uid	= mysql_insert_id();
		//$this->addEmailList($school->contact_name, $school->email);


		if(is_numeric($school_uid) && $school_uid > 0) {
			// INSERT AN ENTRY TO RESELLER SALE TABLE 
			$query = "INSERT INTO `reseller_sale` SET ";
			$query .= "`reseller_user_uid` = '".$resller['uid']."', ";
			$query .= "`sold_user_uid` = '".$school_uid."' ";
			database::query( $query ) or die($query.'<br>'.mysql_error());
			$arrUser['school_uid'] = $school_uid;
			// CHREATE SCHOOL INVOICE
			$this->CreateInvoice( $arrUser['uid'] );
		}
		return $arrUser;
	}

	public function RegisterAdmins( $admin, $resller, $school_uid ) {
		$arrUser	= array();
		$locale		= $resller['locale'];
		$arrUser	= $this->CreateUser($admin, $locale, 'schooladmin');
		$res		= false;

		// INSERT ADMIN IN USERS ADMIN PROFILE TABLE...
		$admin_uid = 0;
		$query = "INSERT INTO `profile_schooladmin` SET ";
		$query .= "`iuser_uid` = '".$arrUser['uid']."', ";
		$query .= "`vfirstname` = '".$this->sql_quote($admin->name)."', ";
		$query .= "`vemail` = '".$this->sql_quote($admin->email)."', ";
		$query .= "`school_id` = '".$this->sql_quote($school_uid)."' ";
		$res		= database::query( $query ) or die($query.'<br>'.mysql_error());
		$admin_uid	= mysql_insert_id();

		if(is_numeric($admin_uid) && $admin_uid > 0) {
			$arrUser['admin_uid'] = $admin_uid;
		}
		return $arrUser;
	}


	public function RegisterTeacher( $teacher, $resller, $school_uid ) {
		$arrUser	= array();
		$locale		= $resller['locale'];
		$arrUser	= $this->CreateUser($teacher, $locale, 'schoolteacher');
		$res		= false;

		// INSERT TEACHER IN USERS TEACHER PROFILE TABLE...
		$teacher_uid = 0;
		$query = "INSERT INTO `profile_schoolteacher` SET ";
		$query .= "`iuser_uid` = '".$arrUser['uid']."', ";
		$query .= "`vfirstname` = '".$this->sql_quote($teacher->name)."', ";
		$query .= "`vemail` = '".$this->sql_quote($teacher->email)."', ";
		$query .= "`school_id` = '".$this->sql_quote($school_uid)."' ";
		$res			= database::query( $query ) or die($query.'<br>'.mysql_error());
		$teacher_uid	= mysql_insert_id();

		if(is_numeric($teacher_uid) && $teacher_uid > 0) {
			$arrUser['teacher_uid'] = $teacher_uid;
		}
		return $arrUser;
	}

	public function RegisterStudent( $student, $resller, $school_uid ) {
		$arrUser	= array();
		$locale		= $resller['locale'];
		$arrUser	= $this->CreateUser($student, $locale, 'student');
		$res		= false;

		// INSERT STUDENT IN USERS STUDENT PROFILE TABLE...
		$student_uid = 0;
		$query = "INSERT INTO `profile_student` SET ";
		$query .= "`iuser_uid` = '".$arrUser['uid']."', ";
		$query .= "`vfirstname` = '".$this->sql_quote($student->name)."', ";
		$query .= "`wordbank_word` = '".$this->sql_quote($arrUser['password'])."', ";
		$query .= "`school_id` = '".$this->sql_quote($school_uid)."' ";
		$res			= database::query( $query ) or die($query.'<br>'.mysql_error());
		$student_uid	= mysql_insert_id();
		
		if(is_numeric($student_uid) && $student_uid > 0) {
			$arrUser['student_uid'] = $student_uid;
		}
		return $arrUser;
	}

	public function CreateUser( $userArray, $locale, $userType ) {

		$arrResponse			= array();
		$uid				= 0;
		$res				= false;
		$registration_key	= '';
		$Type				= '';

		$password = $this->generatePassword();
		// INSERT SCHOOL RECORD IN MAIN USER TABLE 
		$query = "INSERT INTO `user` SET ";
		$query .= "`registered_dts` = '".date('Y-m-d H:i:s')."', ";
		$query .= "`registration_ip` = '".$_SERVER['REMOTE_ADDR']."', ";
		$query .= "`email` = '".$this->sql_quote($userArray->email)."', ";
		$query .= "`password` = '".md5($password)."', ";
		$query .= "`active` = '1', ";
		$query .= "`access_allowed` = '1', ";
		$query .= "`allow_access_without_sub` = '1', ";
		$query .= "`locale` = '".$this->sql_quote($locale)."', ";
		$query .= "`user_type` = '".$this->sql_quote($userType)."' ";
		//$query;
		$res = database::query( $query );
		$uid = mysql_insert_id();

		if( is_numeric($uid) && $uid > 0 ) {
			$registration_key = md5( $uid .'-'. $_SERVER['REMOTE_ADDR'] );
			$sql = "UPDATE `user` SET ";
			$sql .="`registration_key` = '".$registration_key."' ";
			$sql .="WHERE `uid` = '".$uid."'";
			database::query($sql);

			$arrResponse['uid']				= $uid;
			$arrResponse['password']		= $password;
			$arrResponse['registration_key']= $registration_key;
			if($userType == 'school' ){
				$Type = 'school';
			}
			if($userType == 'schooladmin' ){
				$Type = 'admin';
			}
			if($userType == 'schoolteacher' ){
				$Type = 'teacher';
			}
			if($userType == 'student' ){
				$Type = 'student';
			}
			$this->ResponseXml($Type, $userArray, array('status'=>'success', 'key'=>$registration_key));
		}
		return $arrResponse;
	}

	public function CreateInvoice( $user_uid ) {

		$now				= date('Y-m-d H:i:s');
		list($date, $time)	= explode(' ', $now);
		list($y, $m, $d)	= explode('-', $date);
		list($h, $i, $s)	= explode(':', $time);

		$start		= $now;
		$expires	= date('Y-m-d H:i:s', mktime($h, $i, $s, $m, ($d + 14), ($y + 1)));
		$due_date	= date('Y-m-d H:i:s', mktime($h, $i, $s, $m, ($d + 14), ($y)));

		$arrPrice		= array();
		$objCurrency	= new currencies();
		$arrPrice		= $objCurrency->getPriceAndCurrency('school');

		// CRFATE INVOICE FOR THE SCHOOL
		$query = "INSERT INTO `subscriptions` SET ";
		$query .= "`user_uid` = '".$user_uid."', ";
		$query .= "`due_date` = '".$due_date."', ";
		$query .= "`expires_dts` = '".$expires."', ";
		$query .= "`start_dts` = '".$start."', ";
		$query .= "`invoice_for` = 'school', ";
		$query .= "`amount` = '".$arrPrice['price']."', ";
		$query .= "`vat` = '".$arrPrice['vat']."', ";
		$query .= "`invoice_number` = '".(1600+$user_uid)."' ";
		database::query( $query ) or die($query.'<br>'.mysql_error());
	}

	public function VerifySchool( $key ) {
		$row = array('school_uid'=>'0');
		$query ="SELECT ";
		$query.="`RS`.`sold_user_uid` AS `school_uid`, ";
		$query.="`SCH`.`user_limit` ";
		$query.="FROM ";
		$query.="`reseller_sale` AS `RS`, ";
		$query.="`users_schools` AS `SCH`, ";
		$query.="`user` AS `U` ";
		$query.="WHERE ";
		$query.="`RS`.`sold_user_uid` = `SCH`.`uid` ";
		$query.="AND ";
		$query.="`SCH`.`user_uid` = `U`.`uid` ";
		$query.="AND ";
		$query.="`U`.`registration_key` = '".$this->sql_quote($key)."' ";

		$result = database::query( $query );
		if($result && mysql_error()=='' && mysql_num_rows($result) == 1) {
			$row = mysql_fetch_array( $result );
		}
		return $row;
	}

	public function setSchoolUserLimit( $school, $school_uid ) {
		if(isset($school->user_limit) && isset($school_uid)) {
			$query = "UPDATE `users_schools` SET ";
			$query .= "`user_limit` = '".$this->sql_quote($school->user_limit)."' ";
			$query .= "WHERE ";
			$query .= "`uid` = '".$school_uid."' ";
			database::query( $query );
		}
	}

	public function generatePassword( ) {
		
		$alfa = "1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$token = "";
		for($i = 0; $i < 6; $i ++) {
			$token .= $alfa[@rand(0, 61)];
		}
		return $token;
	}

	public function sql_quote( $value ) {
		if(get_magic_quotes_gpc() ) {
			$value = stripslashes( $value );
		}
		//check if this function exists
		if( function_exists( "mysql_real_escape_string" ) ) {
			$value = mysql_real_escape_string( $value );
		} else { //for PHP version < 4.3.0 use addslashes
			$value = addslashes( $value );
		}
		return $value;
	}

	public function ValidateUser( $Arr, $userType = 'school', $school_id = 0 ){
		$error = array();
		$suberr = array();

		// ERROR VALIDATION FOR SCHOOL
		if($userType == 'school') {
			if(empty($Arr->email)) {
				$suberr[] = 'email_missing';
			} else if( filter_var($Arr->email,FILTER_VALIDATE_EMAIL) === false ){
				$suberr[] = 'email_invalid';
			} else if( $this->CheckUserExist($Arr->email) === true ) {
				$error['status'] = 'failed';
				$error['reason'] = 'record_exist!';
				$this->ResponseXml('school', $Arr, $error);
				return false;
			}
			if(empty($Arr->name)) {
				$suberr[] = 'name_missing';
			}
			if(empty($Arr->contact_name)) {
				$suberr[] = 'contact_name_missing';
			}
			if(count($suberr) > 0) {
				$error['status'] = 'failed';
				$error['reason'] = implode(' | ',$suberr);
				$this->ResponseXml('school', $Arr, $error);
				return false;
			}
		}
		// ERROR VALIDATION FOR ADMINS/TEACHERS/STUDENTS
		if($userType != 'school') {
			if(empty($Arr->email)) {
				$suberr[] = 'email_missing';
			} else if( filter_var($Arr->email,FILTER_VALIDATE_EMAIL) === false ){
				$suberr[] = 'email_invalid';
			} else if( $this->CheckUserExist($Arr->email) === true ) {
				$error['status'] = 'failed';
				$error['reason'] = 'record_exist!';
				$this->ResponseXml('admin', $Arr, $error);
				return false;
			}
			if(empty($Arr->name)) {
				$suberr[] = 'name_missing';
			}
			if(count($suberr) > 0) {
				$error['status'] = 'failed';
				$error['reason'] = implode(' | ',$suberr);
				$this->ResponseXml($userType, $Arr, $error);
				return false;
			}
		}

		if($userType == 'student' && count($suberr) == 0) {
			$message = '';
			$message = $this->CheckSchoolStudentLimitRestriction($school_id);
			if( trim($message) != '' ) {
				$error['status'] = 'failed';
				$error['reason'] = strip_tags($message);
				$this->ResponseXml($userType, $Arr, $error);
				return false;
			}
		}
		return true;
	}

	public function FailAllSubUsers( $School ) {
		$error = array();
		$error['status'] = 'failed';
		$error['reason'] = 'parent_school_error';

		if(isset($School->admins->admin)) {
			foreach($School->admins->admin as $Admin ) {
				$this->xml .= '<admins>';
				$this->ResponseXml('admin', $Admin->Attributes(), $error);	
				$this->xml .= '</admins>';
			}
		}

		if(isset($School->teachers->teacher)) {
			foreach($School->teachers->teacher as $Teacher ) {
				$this->xml .= '<teachers>';
				$this->ResponseXml('teacher', $Teacher->Attributes(), $error);	
				$this->xml .= '</teachers>';
			}
		}

		if(isset($School->students->student)) {
			foreach($School->students->student as $Student ) {
				$this->xml .= '<students>';
				$this->ResponseXml('student', $Student->Attributes(), $error);
				$this->xml .= '</students>';
			}
		}
	}

	public function ResponseXml( $tag, $Attributes, $AdditionalAttr = array()) {
		$this->xml .= '<'.$tag;
		foreach( $Attributes as $idx => $val ) {
			$this->xml .= ' '.$idx.'="'.$val.'"';
		}

		foreach( $AdditionalAttr as $idx => $val ) {
			$this->xml .= ' '.$idx.'="'.$val.'"';
		}
		$this->xml .=	'></'.$tag.'>';
	}

	public function SetUserLimitReachedMessage( $message, $reseller ) {
		$query = "UPDATE ";
		$query .= "`profile_reseller` ";
		$query .= "SET ";
		$query .= "`user_limit_reached` = '".trim($this->sql_quote($message))."' ";
		$query .= "WHERE ";
		$query .= "`iuser_uid` = '".$reseller['uid']."' ";
		database::query( $query );
	}

	/**
	* CHECK `user_limi` FIELD IN SCHOOL PROFILE DO IT HAVE USER LIMIT IF YES THEN CHECK THAT SCHOOL IS UNDER ANY RESELLER.
	* IF YSE THEN CHECK IS THAT RESSLER HAVE SET USER LIMIT REACHED MESSAGE 
	* IF YSE THEN BRING THAT MESSAGE FROM RESELLER PROFILE AND DISPLAY IT ON THE PAGE 
	* ELSE DISPLAY DEFAULT USER LIMIT REACHERD MESSAGE ON SCREEN.
	*/
	public function CheckSchoolStudentLimitRestriction( $school_id ) {
		$message = '';
		if( isset($school_id) && is_numeric($school_id) && $school_id > 0 ) {
			$objSchool = new users_schools( $school_id );
			$objSchool->load();
			if($objSchool->get_user_limit() > 0 ) {
				$student_count = $this->getSchoolStudentCount( $school_id );
				if(($student_count+1) > $objSchool->get_user_limit() ) {
					$message = $this->getUserLimitReachedMessageFromReseller( $school_id );
					if(trim($message) == '') {
						$message = 'Your subscription permits only '.$objSchool->get_user_limit().' accounts to be created. Please contact us about increasing this limit.';
					}
				}
			}
		}
		return strip_tags($message);
	}

	public function getSchoolStudentCount( $school_id ) {
		$student_count = 0;
		if( isset($school_id) && is_numeric($school_id) && $school_id > 0 ) {
			$query = "SELECT ";
			$query .="count(`uid`) as `tot` ";
			$query .= "FROM ";
			$query .= "`profile_student` ";
			$query .= "WHERE ";
			$query .= "`school_id` = '".$school_id."' ";
			
			$result = database::query( $query );
			if( $result && mysql_error() == '' ) {
				$row = mysql_fetch_array( $result );
				$student_count =  $row['tot'];
			}
		}
		return $student_count;
	}

	public function getUserLimitReachedMessageFromReseller( $school_id ) {
		$message = '';
		if( isset($school_id) && is_numeric($school_id) && $school_id > 0 ) {
			$query = "SELECT ";
			$query .="`user_limit_reached` ";
			$query .= "FROM ";
			$query .= "`profile_reseller` AS `PR`, ";
			$query .= "`reseller_sale` AS `RS` ";
			$query .= "WHERE ";
			$query .= "`PR`.`iuser_uid` = `reseller_user_uid` ";
			$query .= "AND ";
			$query .= "`sold_user_uid` = '".$school_id."'";
			$query .= "AND ";
			$query .= "`user_limit_reached` != '' ";

			$result = database::query( $query );
			if( $result && mysql_error() == '' && mysql_num_rows( $result ) ) {
				$row = mysql_fetch_array( $result );
				$message =  $row['user_limit_reached'];
			}
		}
		return $message;
	}

	protected function addEmailList($name, $email) {
		//Your API Key. Go to http://www.campaignmonitor.com/api/required/ to see where to find this and other required keys
		$api_key = '595c7c768c20a86383d81a7066f962d9';
		$client_id = null;
		$campaign_id = null;
		$list_id = '84b28341cf0209713c2dc232651c6bfd';
		$cm = new component_campaignmonitor($api_key, $client_id, $campaign_id, $list_id);

		//Optional statement to include debugging information in the result
		//$cm->debug_level = 1;
		//This is the actual call to the method, passing email address, name.
		$result = $cm->subscriberAdd($email, $name);
	}
}
?>