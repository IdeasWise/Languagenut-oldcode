<?php

class template_translation extends generic_object {

	public $arrForm = array( );
	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function isValidInput() {
		$arrName	= array();
		$arrWidth	= array();
		$arrHeight	= array();

		if(isset($_POST['tname']) && is_array($_POST['tname'])) {
			foreach($_POST['tname'] as $index => $value) {
				if(trim($value)!='' && strlen(trim($value))>255) {
					$arrName[] = '<i><b>'.$_POST['locale'][$index].'</b></i>';
				}

				$width	= (isset($_POST['twidth'][$index]) && trim($_POST['twidth'][$index]) !='') ? $_POST['twidth'][$index]:0;

				$height	= (isset($_POST['theight'][$index]) && trim($_POST['theight'][$index])!='') ? $_POST['theight'][$index]:0;

				if(trim($width)!='' && (strlen(trim($width))>5 || !is_numeric($width))) {
					$arrWidth[] = '<i><b>'.$_POST['locale'][$index].'</b></i>';
				}
				if(trim($height)!='' && (strlen(trim($height))>5 || !is_numeric($height))) {
					$arrHeight[] = '<i><b>'.$_POST['locale'][$index].'</b></i>';
				}
			}

		}
		return array(
			'arrName'	=>$arrName,
			'arrWidth'	=>$arrWidth,
			'arrHeight'	=>$arrHeight
		);
	}

	public function SaveTemplateTranslation($template_uid=null) {
		if($template_uid!=null && isset($_POST['tname']) && is_array($_POST['tname'])) {
			foreach($_POST['tname'] as $index => $value) {

				$width	= (isset($_POST['twidth'][$index]) && is_numeric($_POST['twidth'][$index])) ? $_POST['twidth'][$index]:0;
			
				$height	= (isset($_POST['theight'][$index]) && is_numeric($_POST['theight'][$index])) ? $_POST['theight'][$index]:0;


				$query ="SELECT ";
				$query.="`uid` ";
				$query.="FROM ";
				$query.="`template_translation` ";
				$query.="WHERE ";
				$query.="`template_uid` = '".mysql_real_escape_string($template_uid)."' ";
				$query.="AND ";
				$query.="`language_uid`='".mysql_real_escape_string($index)."' ";
				$query.="LIMIT 1";
				$result = database::query($query);
				if(mysql_error() == '' && mysql_num_rows($result)) {
					$row = mysql_fetch_array($result);
					parent::__construct($row['uid'],__CLASS__);
					$this->load();
					$this->set_name($value);
					$this->set_width($width);
					$this->set_height($height);
					$this->save();
				} else {
					$this->set_name($value);
					$this->set_width($width);
					$this->set_height($height);
					$this->set_template_uid($template_uid);
					$this->set_language_uid($index);
					$this->insert();

				}
			}
		}
	}

	public function DeleteTemplateTranslation($template_uid=null) {
		if(is_numeric($template_uid) && $template_uid > 0 ) {
			$query ="DELETE ";
			$query.="FROM ";
			$query.="`template_translation` ";
			$query.="WHERE ";
			$query.="`template_uid` = '".mysql_real_escape_string($template_uid)."' ";
			database::query($query);
		}
	}

	public function APICreateTemplateTranslations($objJson=null,$template_uid=null) {
		if($objJson!=null && $template_uid!=null) {
			$query="SELECT ";
			$query.="`uid`, ";
			$query.="`prefix` ";
			$query.="FROM ";
			$query.="`language`";
			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				while($row=mysql_fetch_array($result)) {
					$this->set_name($objJson->name);
					$this->set_width($objJson->width);
					$this->set_height($objJson->height);
					$this->set_template_uid($template_uid);
					$this->set_language_uid($row['uid']);
					$this->insert();
				}
			}
		}
	}


	public function APICopyTemplateTranslations($template_uid=null) {
		if($template_uid!=null) {
			$arrResponse = array();
			$query	="SELECT * FROM `template` WHERE `uid`='".$template_uid."' LIMIT 0,1";
			$result	=database::query($query);
			$arrTemplate = array();
			if($result && mysql_error()=="" && mysql_num_rows($result)) {
				$arrTemplate = mysql_fetch_array($result);
				$query="SELECT ";
				$query.="`uid`, ";
				$query.="`prefix` ";
				$query.="FROM ";
				$query.="`language`";
				$result = database::query($query);
				if(mysql_error()=='' && mysql_num_rows($result)) {
					while($row=mysql_fetch_array($result)) {
						$query ="SELECT ";
						$query.="`uid` ";
						$query.="FROM ";
						$query.="`template_translation` ";
						$query.="WHERE ";
						$query.="`template_uid` = '".mysql_real_escape_string($template_uid)."' ";
						$query.="AND ";
						$query.="`language_uid`='".mysql_real_escape_string($row['uid'])."' ";
						$query.="LIMIT 1";
						$result2 = database::query($query);
						$update = false;
						$template_translation_uid = null;
						if(mysql_error() == '' && mysql_num_rows($result2)) {
							$rowTemplate = mysql_fetch_array($result2);
							parent::__construct($rowTemplate['uid'],__CLASS__);
							$this->load();
							$update = true;
							$template_translation_uid = $rowTemplate['uid'];
						}
						$this->set_name($arrTemplate['name']);
						$this->set_width($arrTemplate['width']);
						$this->set_height($arrTemplate['height']);
						$this->set_template_uid($template_uid);
						$this->set_language_uid($row['uid']);
						$this->set_locked($arrTemplate['locked']);
						if($update == false) {
							$template_translation_uid = $this->insert();
						} else {
							$this->save();
						}
						$arrResponse[] = array(
							'language_uid'				=> $row['uid'],
							'template_translation_uid'	=> $template_translation_uid
						);
					}
				}
			}
		}
		return $arrResponse;
	}

	public function APIUpdateTemplateTranslation($objJson=null){
		if($objJson!=null) {
			$arrError = array();
			if(!isset($objJson->template_translation_uid)) {
				$arrError[] = 'template_translation_uid missing';
			} else if(empty($objJson->template_translation_uid)) {
				$arrError[] = 'template_translation_uid is empty';
			} else if(!is_numeric($objJson->template_translation_uid)) {
				$arrError[] = 'invalid template_translation_uid';
			}
			if(!isset($objJson->name)) {
				$arrError[] = 'name missing';
			} else if(empty($objJson->name)) {
				$arrError[] = 'name missing';
			}
			if(!isset($objJson->width)) {
				$arrError[] = 'width missing';
			} else if(empty($objJson->width)) {
				$arrError[] = 'width is empty';
			} else if(!is_numeric($objJson->width)) {
				$arrError[] = 'invalid width';
			}
			if(!isset($objJson->height)) {
				$arrError[] = 'height missing';
			} else if(empty($objJson->height)) {
				$arrError[] = 'height is empty';
			} else if(!is_numeric($objJson->height)) {
				$arrError[] = 'invalid height';
			}
			if(isset($objJson->locked) && trim($objJson->locked) == '') {
				$arrError[] = 'locked is empty';
			} else if(isset($objJson->locked) && !is_numeric($objJson->locked)) {
				$arrError[] = 'invalid locked';
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
				parent::__construct($objJson->template_translation_uid,__CLASS__);
				$this->load();
				$this->set_name($objJson->name);
				$this->set_width($objJson->width);
				$this->set_height($objJson->height);
				if(isset($objJson->locked)) {
					$this->set_locked($objJson->locked);
				}
				$this->save();
				//$objTemplateTranslation = new template_translation();
				//$objTemplateTranslation->APICreateTemplateTranslations($objJson,$template_uid);
				return $objJson->template_translation_uid;
			}
		} else {
			return false;
		}
	}

	public function duplicateToLanguage($locale, $languageUid) {
		$arrValues = array();

		$arrValues[] = array(
			"field" => "language_uid",
			"value" => $languageUid
		);
		$enUid = language::getUidFromPrefix("en");
		$where = ($enUid) ? " AND language_uid='" . $enUid . "'" : "";
		$groupBy = " GROUP BY template_uid";
		$this->copyToTranslation(__CLASS__, __CLASS__, $where, $arrValues, $groupBy);
	}
}
?>