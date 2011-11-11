<?php

class flash_tips_translation extends generic_object {

	public $arrForm = array();

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function getTranslations($flash_tips_uid=null,$language_uid=14) {
		$arrRow = array();
		if($flash_tips_uid!=null) {
			$query ="SELECT ";
			$query.="* ";
			$query.="FROM ";
			$query.="`flash_tips_translation` ";
			$query.="WHERE ";
			$query.="`flash_tips_uid`='".$flash_tips_uid."' ";
			$query.="AND ";
			$query.="`language_uid`='".$language_uid."'";
			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				$arrRow = mysql_fetch_array($result);
			}
		}
		return $arrRow;
	}

	public function saveTranslation() {
		if(isset($_POST['uid']) && $_POST['uid']>0) {
			parent::__construct($_POST['uid'], __CLASS__);
			$this->load();
		}
		if(isset($_POST['language_uid'])) {
			$this->set_language_uid($_POST['language_uid']);
		}
		if(isset($_POST['flash_tips_uid'])) {
			$this->set_flash_tips_uid($_POST['flash_tips_uid']);
		}
		if(isset($_POST['title'])) {
			$this->set_title($_POST['title']);
		}
		if(isset($_POST['content'])) {
			$this->set_content($_POST['content']);
		}
		if(isset($_POST['uid']) && $_POST['uid']>0) {
			$this->save();
		} else {
			$this->insert();
		}
	}

	public function getAPIFlashTipsTranslations($language_uid=14) {
		$query = "SELECT ";
		$query.="`uid`, ";
		$query.="`tag` ";
		$query.="FROM ";
		$query.="`flash_tips` ";
		$result=database::query($query);
		$arrTips = array();
		$arrTips['language_uid']=$language_uid;
		while($arrRow=mysql_fetch_array($result)) {
			$arrTips[$arrRow['tag']] = $this->getApiTranslation($arrRow['uid'],$language_uid);
		}
		return $arrTips;
	}

	public function getApiTranslation($flash_tips_uid=null,$language_uid=14) {
		$arrRow = array(
			'title'			=>'',
			'translation'	=>''
		);
		if($flash_tips_uid!=null) {
			$query ="SELECT ";
			$query.="`title`, ";
			$query.="`content` ";
			$query.="FROM ";
			$query.="`flash_tips_translation` ";
			$query.="WHERE ";
			$query.="`flash_tips_uid`='".$flash_tips_uid."' ";
			$query.="AND ";
			$query.="`language_uid`='".mysql_real_escape_string($language_uid)."'";
			
			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				$arrResult = mysql_fetch_array($result);
				$arrRow['title']=$arrResult['title'];
				$arrRow['translation']=$arrResult['content'];
			}
		}
		return $arrRow;
	}

}

?>