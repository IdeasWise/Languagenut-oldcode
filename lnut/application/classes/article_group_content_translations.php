<?php

class article_group_content_translations extends generic_object {

	public $arrForm = array( );
	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function getList($article_translation_uid=null,$all=false) {
		
		$where ="WHERE ";
		$where.="`TGCT`.`article_translation_uid` = '".$article_translation_uid."' ";
		$where.="AND ";
		$where.="`TCT`.`uid`=`TGCT`.`article_translation_uid` ";
		$where.="GROUP BY `TGCT`.`uid` ";
		if(!$all) {
			$query ="SELECT ";
			$query.="count(`TGCT`.`uid`) ";
			$query.="FROM ";
			$query.="`article_group_content_translations` AS `TGCT`, ";
			$query.="`article_content_translations` AS `TCT` ";
			$query.=$where;
			$this->setPagination( $query );
		}
		$query ="SELECT ";
		$query.="`TGCT`.*, ";
		$query.="`TCT`.`content` ";
		$query.="FROM ";
		$query.="`article_group_content_translations` AS `TGCT`, ";
		$query.="`article_content_translations` AS `TCT` ";
		$query.=$where;
		$query.="ORDER BY `TGCT`.`uid` DESC ";
		if(!$all) {
			$query.= "LIMIT ".$this->get_limit();
		}
		return database::arrQuery($query);
	}

	public function getContentGroupList($article_translation_uid=null,$article_content_translation_uid=null,$all=false) {
		
		$where ="WHERE ";
		$where.="`TGCT`.`article_translation_uid` = '".$article_translation_uid."' ";
		$where.="AND ";
		$where.="`TGCT`.`article_translation_content_uid` = '".$article_translation_uid."' ";
		$where.="AND ";
		$where.="`TCT`.`uid`=`TGCT`.`article_translation_uid` ";
		$where.="GROUP BY `TGCT`.`uid` ";
		if(!$all) {
			$query ="SELECT ";
			$query.="count(`TGCT`.`uid`) ";
			$query.="FROM ";
			$query.="`article_group_content_translations` AS `TGCT`, ";
			$query.="`article_content_translations` AS `TCT` ";
			$query.=$where;
			$this->setPagination( $query );
		}
		$query ="SELECT ";
		$query.="`TGCT`.*, ";
		$query.="`TCT`.`content` ";
		$query.="FROM ";
		$query.="`article_group_content_translations` AS `TGCT`, ";
		$query.="`article_content_translations` AS `TCT` ";
		$query.=$where;
		$query.="ORDER BY `TGCT`.`uid` DESC ";
		if(!$all) {
			$query.= "LIMIT ".$this->get_limit();
		}
		return database::arrQuery($query);
	}

	public function isValidUpdate () {
		
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
			'name'=>array(
				'value'			=> (isset($_POST['name']))?trim($_POST['name']):'',
				'checkEmpty'	=> false,
				'errEmpty'		=> '',
				'minChar'		=> 0,
				'maxChar'		=> 255,
				'errMinMax'		=> 'Name can not be more than 255 characters.',
				'dataType'		=> 'text',
				'errdataType'	=> 'Please enter valid name.',
				'errIndex'		=> 'error.content'
			)
		);
		// $arrFields contains array for fields which needs to be validate and then we are passing class object($this)
		if($this->isValidarrFields($arrFields,$this) === true) {
			$this->set_article_uid($_POST['article_uid']);
			$this->set_article_group_uid($_POST['article_group_uid']);
			$this->set_article_content_uid($_POST['article_content_uid']);
			$this->set_name($arrFields['name']['value']);
			return true;
		} else {
			return false;
		}

	}	
}
?>