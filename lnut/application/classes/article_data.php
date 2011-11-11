<?php

class article_data extends generic_object {

	public $arrForm = array( );
	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function getList($class_uid=null,$user_uid=null,$all = false ) {
		if($user_uid!=null && $class_uid!=null) {
			$where ="WHERE ";
			$where.="`A`.`uid`=`AD`.`article_uid` ";
			$where.="AND ";
			$where.="`student_user_uid`='".$user_uid."' ";
			$where.="AND ";
			$where.="`class_uid`='".$class_uid."' ";

			if(!$all) {
				$query ="SELECT ";
				$query.="count(`AD`.`uid`) ";
				$query.="FROM ";
				$query.="`article` AS `A`, ";
				$query.="`article_data` AS `AD` ";
				$query.=$where;
				$this->setPagination( $query );
			}
			$query ="SELECT ";
			$query.="`A`.`title`, ";
			$query.="`AD`.`uid`, ";
			$query.="`AD`.`article_uid`, ";
			$query.="`AD`.`student_user_uid`, ";
			$query.="`AD`.`class_uid`, ";
			$query.="`AD`.`language_uid`, ";
			$query.="`AD`.`value_submitted`, ";
			$query.="`AD`.`submitted_dts`, ";
			$query.="`AD`.`teacher_comment`, ";
			$query.="`AD`.`score` ";
			$query.="FROM ";
			$query.="`article` AS `A`, ";
			$query.="`article_data` AS `AD` ";
			$query.=$where;
			$query.="ORDER BY ";
			$query.="`submitted_dts` DESC ";
			if(!$all) {
				$query.= "LIMIT ".$this->get_limit();
			}
			return database::arrQuery($query);
		}
		
	}

	public function getArticleDetail($uid=null) {
		if($uid!=null) {
			$where ="WHERE ";
			$where.="`A`.`uid`=`AD`.`article_uid` ";
			$where.="AND ";
			$where.="`AD`.`uid`='".$uid."' ";

			$query ="SELECT ";
			$query.="`A`.`title`, ";
			$query.="`AD`.`uid`, ";
			$query.="`AD`.`article_uid`, ";
			$query.="`AD`.`student_user_uid`, ";
			$query.="`AD`.`class_uid`, ";
			$query.="`AD`.`language_uid`, ";
			$query.="`AD`.`value_submitted`, ";
			$query.="`AD`.`submitted_dts`, ";
			$query.="`AD`.`teacher_comment`, ";
			$query.="`AD`.`score` ";
			$query.="FROM ";
			$query.="`article` AS `A`, ";
			$query.="`article_data` AS `AD` ";
			$query.=$where;
			$arr= database::arrQuery($query);
			if(count($arr)==1) {
				return $arr[0];
			}
		}
	}

	public function SaveTeacherCahnges() {
		$uid	= (isset($_POST['uid']))?$_POST['uid']:0;
		$score	= (isset($_POST['score']))?$_POST['score']:0;
		$teacher_comment	= (isset($_POST['teacher_comment']))?$_POST['teacher_comment']:'';

		$arrMessages = array();

		if(strlen(trim($score))>11) {
			$arrMessages['error.score'] = "Mark should not greater than 11 digits in length.";
		} else if(!validation::isValid('int',$score) ) {
			$arrMessages['error.score'] = "Please enter valid Marks.";
		}
		if(trim($teacher_comment)=='') {
			$arrMessages['error.teacher_comment'] = "Please enter your comment.";
		}

		if($uid>0 && count($arrMessages) == 0) {
			parent::__construct($uid,__CLASS__);
			$this->load();
			$this->set_score($score);
			$this->set_teacher_comment($teacher_comment);
			$this->set_teacher_user_uid($_SESSION['user']['uid']);
			$this->set_review_dts(date('Y-m-d H:i:s'));
			$this->save();
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

	public function APISaveStudentArticleInput($objJson=null){
		if($objJson!=null) {
			$arrError = array();
			if(isset($objJson->unit_uid) && !is_numeric($objJson->unit_uid)) {
				$arrError[] = 'invalid unit_uid';
			}
			if(isset($objJson->article_uid) && !is_numeric($objJson->article_uid)) {
				$arrError[] = 'invalid article_uid';
			}
			if(isset($objJson->language_uid) && !is_numeric($objJson->language_uid)) {
				$arrError[] = 'invalid language_uid';
			}
			if(!isset($objJson->article_page_content_uid)) {
				$arrError[] = 'article_page_content_uid is missing';
			} else if (isset($objJson->article_page_content_uid) && !is_numeric($objJson->article_page_content_uid)) {
				$arrError[] = 'invalid article_page_content_uid';
			}
			if(!isset($objJson->value_submitted)) {
				$arrError[] = 'value_submitted is missing';
			} else if(empty($objJson->value_submitted)) {
				$arrError[] = 'value_submitted is missing';
			}
			if(!isset($_SESSION['user']['uid'])) {
				$arrError[] = 'only logged in user can submit inputs';
			}
			if(!isset($_SESSION['user']['class_uid'])) {
				$arrError[] = 'only student user can submit inputs';
			}

			if(count($arrError)) {
				$arrResponse = array();
				foreach($objJson as $index => $value ) {
					$arrResponse[$index] = $value;
				}
				$arrResponse['error'] = 1;
				$arrResponse['error_msg'] = implode(' | ',$arrError);
				return $arrResponse;
			} else {
				if(isset($objJson->unit_uid)) {
					$this->set_unit_uid($objJson->unit_uid);
				}
				if(isset($objJson->article_uid)) {
					$this->set_article_uid($objJson->article_uid);
				}
				if(isset($objJson->language_uid)) {
					$this->set_language_uid($objJson->language_uid);
				}
				if(isset($objJson->article_page_content_uid)) {
					$this->set_article_page_content_uid($objJson->article_page_content_uid);
				}
				if(isset($objJson->value_submitted)) {
					$this->set_value_submitted($objJson->value_submitted);
				}

				$this->set_student_user_uid($_SESSION['user']['uid']);
				if(isset($_SESSION['user']['class_uid'])) {
					$this->set_class_uid($_SESSION['user']['class_uid']);
				}
				$this->set_submitted_dts(date('Y-m-d H:i:s'));
				$response = $this->insert();
				return array('response'	=> array(
						'status'	=>'success',
						'message'	=>'Your input has beed saved successfully'
					)
				);
			}
		} else {
			return false;
		}
	}
}
?>