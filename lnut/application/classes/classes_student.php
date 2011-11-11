<?php

class classes_student extends generic_object {

	public $arrForm = array();

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function doSave ($class_uid=null, $student_uid=null) {
		$objWordBank = new wordbank();
		$word		= '';
		$word_term	= '';
		$word		= $objWordBank->getRandomWord('en');
		if(is_array($word)) {
			$word_term = $word['term'];
		}
		if($word_term && $word_term !== null) {
			$word_term.= rand(0,10).rand(0,10).rand(0,10);
		}
		$this->set_student_password($word_term);
		$this->set_class_uid($class_uid);
		$this->set_student_uid($student_uid);

		$response = $this->insert();
	}

	public function CheckAndSave ($class_uid=null, $student_uid=null) {
		$Fields	= array('uid');
		$Where	= array('class_uid'=>$class_uid, 'student_uid'=>$student_uid);
		$row	= array();
		$row	= $this->search( $Fields , $Where );

		if(count($row) == 0) {
			$objWordBank = new wordbank();
			$word		= '';
			$word_term	= '';
			$word		= $objWordBank->getRandomWord('en');
			if(is_array($word)) {
				$word_term = $word['term'];
			}
			if($word_term && $word_term !== null) {
				$word_term.= rand(0,10).rand(0,10).rand(0,10);
			}
			$this->set_student_password($word_term);
			$this->set_class_uid($class_uid);
			$this->set_student_uid($student_uid);
			$response = $this->insert();
		}
	}

	public function getStudentCount( $class_uid=null ){
		if($class_uid) {
			$query = "SELECT ";
			$query.= "count(`uid`) as `tot` ";
			$query.= "FROM ";
			$query.= "`classes_student` ";
			$query.= "WHERE ";
			$query.= "`class_uid` = '".$class_uid."' ";
			$result= database::query($query);
			if( mysql_error() == '' && $result ) {
				$row = mysql_fetch_array($result);
				return $row['tot'];
			}
			return 0;
		}
		return 0;
	}

	public function setStudentPasswordForThisClass($class_uid=null,$student_uid=null,$locale=null) {

		if($class_uid!=null && $student_uid!=null && $locale!=null) {
			$query="SELECT ";
			$query.="`uid` ";
			$query.="FROM ";
			$query.="`classes_student` ";
			$query.="WHERE ";
			$query.="`class_uid`='".$class_uid."' ";
			$query.="AND ";
			$query.="`student_uid`='".$student_uid."' ";
			$query.="LIMIT 0,1";
			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				$arrRow = mysql_fetch_array($result);
				parent::__construct($arrRow['uid'], __CLASS__);
				$this->load();

				$objWordBank = new wordbank();
				$word		= '';
				$word_term	= '';
				$word		= $objWordBank->getRandomWord($locale);
				if(is_array($word)) {
					$word_term = $word['term'];
				}
				if($word_term && $word_term !== null) {
					$word_term.= rand(0,10).rand(0,10).rand(0,10);
				}
				$this->set_student_password($word_term);
				$this->save();
			}
		}
	}

	public function isValidClassStudent($class_uid=null,$student_uid=null) {
		if($class_uid!=null && $student_uid!=null) {
			$query ="SELECT ";
			$query.="`uid` ";
			$query.="FROM ";
			$query.="`classes_student` ";
			$query.="WHERE ";
			$query.="`class_uid`='".$class_uid."' ";
			$query.="AND ";
			$query.="`student_uid`='".$student_uid."' ";
			$result = database::query($query);
			if($result && mysql_num_rows($result) && mysql_error()== ''){
				return true;
			} else {
				return false;
			}
		}
		return false;
	}
}
?>