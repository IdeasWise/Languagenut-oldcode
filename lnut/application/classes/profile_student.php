<?php

class profile_student extends generic_object {

	public $arrForm = array();
	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__, true);
	}

	public function doSave() {

		if( $this->isValidateFormData() == true ) {

			if(isset($_POST['uid']) && is_numeric($_POST['uid']) && $_POST['uid'] > 0) {
				$this->save();
			} else {
				$this->set_itime(time());
				$insert = $this->insert();
				$this->arrForm['uid'] = $insert;
				$query ="UPDATE ";
				$query.="`user` ";
				$query.="SET ";
				$query.="`user_type` = CONCAT(`user_type` , ',student') ";
				$query.="WHERE ";
				$query.="`uid` = '".mysql_real_escape_string($_POST['iuser_uid'])."' ";
				$query.="LIMIT 1 ";

				database::query( $query );
			}
			return true;
		}
		return false;
	}

	public function isValidateFormData()
	{

		if(isset($_POST['uid']) && is_numeric($_POST['uid']) && $_POST['uid'] > 0) {
			parent::__construct($_POST['uid'],__CLASS__);
			$this->load();
		}

		$iuser_uid	= (isset($_POST['iuser_uid']) && is_numeric($_POST['iuser_uid'])) ? $_POST['iuser_uid']:'0';
		$vfirstname	= (isset($_POST['vfirstname']) && strlen(trim($_POST['vfirstname'])) > 0) ? $_POST['vfirstname'] : '';
		$vlastname	= (isset($_POST['vlastname']) && strlen(trim($_POST['vlastname'])) > 0) ? $_POST['vlastname'] : '';
		$school_id	= (isset($_POST['school_id']) && is_numeric($_POST['school_id'])) ? $_POST['school_id']:'';

		$arrMessages = array();

		if( trim(strlen($vfirstname)) < 5 || trim(strlen($vfirstname)) > 250 ) {
			$arrMessages['error_vfirstname'] = "First name must be 5 to 250 characters in length.";
		} else if(!validation::isValid('text',$vfirstname) ) {
			$arrMessages['error_vfirstname'] = "Please enter valid first name.";
		}

		if( trim(strlen($vlastname)) < 3 || trim(strlen($vlastname)) > 250 ) {
			$arrMessages['error_vlastname'] = "Last name must be 3 to 250 characters in length.";
		} else if(!validation::isValid('text',$vlastname) ) {
			$arrMessages['error_vlastname'] = "Please enter valid last name.";
		}

		if( trim($school_id)== '' ) {
			$arrMessages['error_school_id'] = "Please choose school name.";
		} else if(!validation::isValid('int',$school_id) ) {
			$arrMessages['error_school_id'] = "Please choose valid school name.";
		}

		if(count($arrMessages) == 0) {

			$this->set_iuser_uid($iuser_uid);
			$this->set_vfirstname($vfirstname);
			$this->set_vlastname($vlastname);
			$this->set_school_id($school_id);

		} else {

			$strMessage = '';
			foreach( $arrMessages as $index => $value ){
				$this->arrForm[$index] = 'label_error';
				$strMessage .= '<li>'.$value.'</li>';
			}
			$this->arrForm['message_error'] = '<p>Please correct the errors below:</p><ul>'.$strMessage.'</ul>';

		}

		foreach( $_POST as $index => $value ) {
			$this->arrForm[$index] = $value;
		}

		if(count($arrMessages) == 0) {
			return true;
		} else {
			return false;
		}

	}

	public function generateLogin() {

		$objUser = new user($this->iuser_uid);
		$objWordBank = new wordbank();

		$word = $objWordBank->getRandomWord($objUser->get_locale());

		if($word && $word !== null) {

			$word.= rand(0,10).rand(0,10).rand(0,10);
			// save to the user object's password via md5
			$objUser->changePassword($word);
			$objUser->save();

			// store in an open field for the student's profile
			$this->set_wordbankword($word);
			$this->save();

			// format username based on the firstname/lastname etc
			$objUser->updateUsername();
			$objUser->save();

			return true;
		} else {
			return false;
		}
	}

	public function CheckAllStudents( $class, $EmptycheckOnly = false ) {

		$FinalCheck = true;
		$arrReturn = array();
		if(isset($_POST['last']) && count($_POST['last'])) {
			$numStudent= count($_POST['last']);
			for( $i = 0; $i < $numStudent; $i++ ) {

				if(empty($_POST['last'][$i]) || empty($_POST['first'][$i]) ) {
					return $this->CheckAllStudentsEmpty( $class );
				}
				elseif($EmptycheckOnly == false) {

					/**
					*   CHECKING FOR SIMILAR RECORDS
					*/
					
					$sql = "SELECT ";
					$sql .= "`uid` ";
					$sql .= "FROM `profile_student` ";
					$sql .= "WHERE ";
					$sql .= "`school_id` = '".mysql_real_escape_string($_POST['school_id'])."' ";
					$sql .= "and ( LOWER(CONCAT(`vlastname`,`vfirstname`)) = ";
					$sql .= "'".mysql_real_escape_string(strtolower($_POST['last'][$i].$_POST['first'][$i]))."'";
					$sql .= " OR LOWER(CONCAT(`vfirstname`,`vlastname`)) = ";
					$sql .= "'".mysql_real_escape_string(strtolower($_POST['last'][$i].$_POST['first'][$i]))."')"; 
					$result = database::query($sql);
					if(mysql_num_rows($result)) {
						return $this->CheckAllStudentsDuplicate( $class );
					}
				}
			}
		}
		return array($FinalCheck, $arrReturn);
	}



	protected function CheckAllStudentsEmpty( $class ) {

		$FinalCheck = true;
		$arrReturn = array();

		if(isset($_POST['last']) && count($_POST['last'])) {
			$numStudent= count($_POST['last']);
			for( $i = 0; $i < $numStudent; $i++ ) {

				$lastname = array(
						'value'		=> '',
						'error'		=> '',
						'option'	=> '',
						'index'		=> ''
						);
				$firstname = array(
						'value'		=> '',
						'error'		=> '',
						'option'	=> '',
						'index'		=> ''
						);

				$lastname['value'] = $_POST['last'][$i];
				$firstname['value'] = $_POST['first'][$i];
				
				if(isset($_POST['last'][$i]) || isset($_POST['first'][$i]) ) {
					
					$FinalCheck = false; // if names empty means not final check we have to check form once more after they fill it			
					if(empty($_POST['last'][$i])) {
						$lastname['error'] = 'input-error';
					}
					if(empty($_POST['first'][$i])) {
						$firstname['error'] = 'input-error';
					}
				}
				if(isset($_POST['last'][$i]) || isset($_POST['first'][$i]) ) {
					
					$FinalCheck = false; // if names empty means not final check we have to check form once more after they fill it			
					if(!validation::isValid('text',$_POST['last'][$i])) {
						$lastname['error'] = 'input-error';
					}
					if(!validation::isValid('text',$_POST['first'][$i])) {
						$firstname['error'] = 'input-error';
					}
				}

				$arrReturn[] = $class->CreateStudentsRow( $lastname, $firstname);
			}

		}

		return array($FinalCheck, $arrReturn);
	}


	protected function CheckAllStudentsDuplicate( $class ) {
		
		$FinalCheck = true;
		$arrReturn = array();

		if(isset($_POST['last']) && count($_POST['last'])) {
			$numStudent= count($_POST['last']);
			for( $i = 0; $i < $numStudent; $i++ ) {

				$lastname = array(
						'value'		=> '',
						'error'		=> '',
						'option'	=> '',
						'index'		=> ''
						);
				$firstname = array(
						'value'		=> '',
						'error'		=> '',
						'option'	=> '',
						'index'		=> ''
						);

				$lastname['value'] = $_POST['last'][$i];
				$firstname['value'] = $_POST['first'][$i];

				/**
				.*   CHECKING FOR SIMILAR RECORDS
				 */

				$sql = "SELECT ";
				$sql .= "`uid`, ";
				$sql .= "`vlastname`, ";
				$sql .= "`vfirstname` ";
				$sql .= "FROM `profile_student` ";
				$sql .= "WHERE ";
				$sql .= "`school_id` = '".mysql_real_escape_string($_POST['school_id'])."' ";
				$sql .= "and ( LOWER(CONCAT(`vlastname`,`vfirstname`)) = ";
				$sql .= "'".mysql_real_escape_string(strtolower($_POST['last'][$i].$_POST['first'][$i]))."'";
				$sql .= " OR LOWER(CONCAT(`vfirstname`,`vlastname`)) = ";
				$sql .= "'".mysql_real_escape_string(strtolower($_POST['last'][$i].$_POST['first'][$i]))."')";
				$result = database::query($sql);
				if(mysql_num_rows($result)) {

					$lastname['option'] = array();
					$lastname['error']  = 'input-error';
					$firstname['error'] = 'input-error';
					
					$lastname['index']  = $i;
					$firstname['index'] = $i;
					
					while ( $row = mysql_fetch_array ( $result ) ) {
						$lastname['option'][] = $row;
					}
				}
				$arrReturn[] = $class->CreateStudentsRow( $lastname, $firstname);
			}
		}

		return array($FinalCheck, $arrReturn);
	}

	public function SaveNow() {
		$objWordBank		= new wordbank();
		$objClassesStudent	= new classes_student();
		$objUser			= new user();
		$locale				= 'en';
		
		$sql = "SELECT ";
		$sql .="`locale` ";
		$sql .="FROM `user` as `U`, ";
		$sql .="`users_schools` as `S` ";
		$sql .="WHERE ";
		$sql .="`user_uid` = `U`.`uid` ";
		$sql .=" AND `S`.`uid` = '".mysql_real_escape_string($_POST['school_id'])."' ";
		$result = database::query($sql);
		if(mysql_num_rows($result)) {
			$row = mysql_fetch_array( $result );
			$locale = $row['locale'];
		}
		if(isset($_POST['last']) && count($_POST['last'])) {
			$numStudent= count($_POST['last']);
			for( $i = 0; $i < $numStudent; $i++ ) {
				
					
				if( isset($_POST['radio'][$i]) && $_POST['radio'][$i] != 0){
					$objClassesStudent->CheckAndSave( $_POST['class_uid'] , $_POST['radio'][$i]);
				} else {
					$word = $objWordBank->getRandomWord($locale);
					if($word && $word !== null) {
						$word = $word['term'].rand(0,10).rand(0,10).rand(0,10);
					}
					if(empty($word)) {
						$word = rand(5, 15);
					}

					$username = $_POST['last'][$i][0].$_POST['first'][$i][0];
					$user_uid = null;
					$user_uid = $objUser->CreateStudentUser($username, $word , $locale);
					$student_uid = null;
					$student_uid = $this->createNewStudent( $user_uid, $word, $i );
					$objClassesStudent->doSave( $_POST['class_uid'] , $student_uid);
				}
			}

		}
		$this->redirectToDynamic('/classes/game-scores/class-'.$_POST['class_uid'].'/');
		exit;
		
	}

	protected function createNewStudent( $user_uid, $wordbank, $index ) {
		$this->set_vfirstname($_POST['first'][$index]);
		$this->set_vlastname($_POST['last'][$index]);
		$this->set_school_id($_POST['school_id']);
		$this->set_iuser_uid($user_uid);
		$this->set_itime(time());
		$this->set_wordbank_word($wordbank);
		return $this->insert();
	}

}
?>