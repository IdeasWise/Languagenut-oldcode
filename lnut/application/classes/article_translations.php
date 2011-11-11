<?php

class article_translations extends generic_object {

	public $arrForm = array();

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}


	public function doSave() {

		if($this->isValidateFormData() === true) {
			$this->save();
			return true;
		} else {
			return false;
		}
	}

	private function isValidateFormData() {
		if(isset($_POST['uid']) && is_numeric($_POST['uid']) && $_POST['uid'] > 0) {
			parent::__construct($_POST['uid'],__CLASS__);
			$this->load();
		}
		$arrFields = array(
			'title'=>array(
				'value'			=> (isset($_POST['title']))?trim($_POST['title']):'',
				'checkEmpty'	=> false,
				'errEmpty'		=> '',
				'minChar'		=> 5,
				'maxChar'		=> 260,
				'errMinMax'		=> 'Title must be 5 to 260 characters in length.',
				'dataType'		=> 'text',
				'errdataType'	=> 'Please enter valid title.',
				'errIndex'		=> 'error.title'
			),
			'width'=>array(
				'value'			=> (isset($_POST['width']))?trim($_POST['width']):0,
				'checkEmpty'	=> false,
				'errEmpty'		=> '',
				'minChar'		=> 0,
				'maxChar'		=> 5,
				'errMinMax'		=> 'Width should not more than 5 digits.',
				'dataType'		=> 'int',
				'errdataType'	=> 'Please enter valid width.',
				'errIndex'		=> 'error.width'
			),
			'height'=>array(
				'value'			=> (isset($_POST['height']))?trim($_POST['height']):0,
				'checkEmpty'	=> false,
				'errEmpty'		=> '',
				'minChar'		=> 0,
				'maxChar'		=> 5,
				'errMinMax'		=> 'Height should not more than 5 digits.',
				'dataType'		=> 'int',
				'errdataType'	=> 'Please enter valid Height.',
				'errIndex'		=> 'error.height'
			)
		);
		// $arrFields contains array for fields which needs to be validate and then we are passing class object($this)
		if($this->isValidarrFields($arrFields,$this) === true) {
			$this->set_title($arrFields['title']['value']);
			$this->set_width($arrFields['width']['value']);
			$this->set_height($arrFields['height']['value']);
			return true;
		} else {
			return false;
		}
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

	public function SaveArticleTranslation($article_uid=null) {
		if($article_uid!=null && isset($_POST['tname']) && is_array($_POST['tname'])) {
			foreach($_POST['tname'] as $index => $value) {

				$width	= (isset($_POST['twidth'][$index]) && is_numeric($_POST['twidth'][$index])) ? $_POST['twidth'][$index]:0;
			
				$height	= (isset($_POST['theight'][$index]) && is_numeric($_POST['theight'][$index])) ? $_POST['theight'][$index]:0;

				$query ="SELECT ";
				$query.="`uid` ";
				$query.="FROM ";
				$query.="`article_translations` ";
				$query.="WHERE ";
				$query.="`article_uid` = '".mysql_real_escape_string($article_uid)."' ";
				$query.="AND ";
				$query.="`language_uid`='".mysql_real_escape_string($index)."' ";
				$query.="LIMIT 1";
				$result = database::query($query);
				if(mysql_error() == '' && mysql_num_rows($result)) {
					$row = mysql_fetch_array($result);
					parent::__construct($row['uid'],__CLASS__);
					$this->load();
					$this->set_title($value);
					$this->set_width($width);
					$this->set_height($height);
					$this->save();
				} else {
					$this->set_title($value);
					$this->set_width($width);
					$this->set_height($height);
					$this->set_article_uid($article_uid);
					$this->set_language_uid($index);
					$this->insert();

				}
			}
		}
	}

	public function DeleteArticleTranslation($article_uid=null) {
		if(is_numeric($article_uid) && $article_uid > 0 ) {
			$query ="DELETE ";
			$query.="FROM ";
			$query.="`article_translations` ";
			$query.="WHERE ";
			$query.="`article_uid` = '".mysql_real_escape_string($article_uid)."' ";
			database::query($query);
		}
	}


	public function APICreateArticleTranslations($objJson=null,$article_uid=null) {
		if($objJson!=null && $article_uid!=null) {
			$query="SELECT ";
			$query.="`uid`, ";
			$query.="`prefix` ";
			$query.="FROM ";
			$query.="`language`";
			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				while($row=mysql_fetch_array($result)) {
					$this->set_title($objJson->title);
					$this->set_width($objJson->width);
					$this->set_height($objJson->height);
					$this->set_article_uid($article_uid);
					$this->set_language_uid($row['uid']);
					$this->insert();
				}
			}
		}
	}


	public function APICopyArticleTranslations($article_uid=null) {
		if($article_uid!=null) {
			$arrResponse = array();
			$query	="SELECT * FROM `article` WHERE `uid`='".$article_uid."' LIMIT 0,1";
			$result	=database::query($query);
			$arrArticle = array();
			if($result && mysql_error()=="" && mysql_num_rows($result)) {
				$arrArticle = mysql_fetch_array($result);
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
						$query.="`article_translations` ";
						$query.="WHERE ";
						$query.="`article_uid` = '".mysql_real_escape_string($article_uid)."' ";
						$query.="AND ";
						$query.="`language_uid`='".mysql_real_escape_string($row['uid'])."' ";
						$query.="LIMIT 1";
						$resArticle = database::query($query);
						$update = false;
						$article_translation_uid = null;
						if(mysql_error() == '' && mysql_num_rows($resArticle)) {
							$rowArticle = mysql_fetch_array($result);
							parent::__construct($rowArticle['uid'],__CLASS__);
							$this->load();
							$update = true;
							$article_translation_uid = $rowArticle['uid'];
						}
						$this->set_title($arrArticle['title']);
						$this->set_article_uid($article_uid);
						$this->set_language_uid($row['uid']);
						$this->set_locked($arrArticle['locked']);
						$this->set_published($arrArticle['published']);
						if($update) {
							$this->save();
						} else {
							$article_translation_uid = $this->insert();
						}
						$arrResponse[] = array(
							'language_uid'				=> $row['uid'],
							'article_translation_uid'	=> $article_translation_uid
						);
					}
				}
			}
		}
		return $arrResponse;
	}
}
?>