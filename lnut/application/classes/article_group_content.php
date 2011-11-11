<?php

class article_group_content extends generic_object {

	public $arrForm = array( );
	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function getList($article_uid=null,$article_group_uid=null,$all=false) {
		
		$where ="WHERE ";
		$where.="`TG`.`uid` = `TGC`.`article_group_uid` ";
		$where.="AND ";
		$where.="`T`.`uid` = `TGC`.`article_uid` ";
		$where.="AND ";
		$where.="`TC`.`uid` = `TGC`.`article_content_uid` ";
		$where.="AND ";
		$where.="`TGC`.`article_uid` = '".$article_uid."' ";
		$where.="AND ";
		$where.="`TGC`.`article_group_uid` = '".$article_group_uid."' ";
		$where.="GROUP BY `TGC`.`uid` ";
		if(!$all) {
			$query ="SELECT ";
			$query.="count(`TGC`.`uid`) ";
			$query.="FROM ";
			$query.="`article_group_content` AS `TGC`, ";
			$query.="`article_group` AS `TG`, ";
			$query.="`article` AS `T`, ";
			$query.="`article_content` AS `TC` ";
			$query.=$where;
			$this->setPagination( $query );
		}
		$query ="SELECT ";
		$query.="`TGC`.*, ";
		$query.="`TC`.`content` ";
		$query.="FROM ";
		$query.="`article_group_content` AS `TGC`, ";
		$query.="`article_group` AS `TG`, ";
		$query.="`article` AS `T`, ";
		$query.="`article_content` AS `TC` ";
		$query.=$where;
		$query.="ORDER BY `TGC`.`uid` DESC ";
		if(!$all) {
			$query.= "LIMIT ".$this->get_limit();
		}
		return database::arrQuery($query);
	}

	public function getContentGroupList($article_uid=null,$article_content_uid=null,$all=false) {
		
		$where ="WHERE ";
		$where.="`TG`.`uid` = `TGC`.`article_group_uid` ";
		$where.="AND ";
		$where.="`T`.`uid` = `TGC`.`article_uid` ";
		$where.="AND ";
		$where.="`TC`.`uid` = `TGC`.`article_content_uid` ";
		$where.="AND ";
		$where.="`TGC`.`article_uid` = '".$article_uid."' ";
		$where.="AND ";
		$where.="`TGC`.`article_content_uid` = '".$article_content_uid."' ";
		$where.="GROUP BY `TGC`.`uid` ";
		if(!$all) {
			$query ="SELECT ";
			$query.="count(`TGC`.`uid`) ";
			$query.="FROM ";
			$query.="`article_group_content` AS `TGC`, ";
			$query.="`article_group` AS `TG`, ";
			$query.="`article` AS `T`, ";
			$query.="`article_content` AS `TC` ";
			$query.=$where;
			$this->setPagination( $query );
		}
		$query ="SELECT ";
		$query.="`TGC`.*, ";
		$query.="`TC`.`content` ";
		$query.="FROM ";
		$query.="`article_group_content` AS `TGC`, ";
		$query.="`article_group` AS `TG`, ";
		$query.="`article` AS `T`, ";
		$query.="`article_content` AS `TC` ";
		$query.=$where;
		$query.="ORDER BY `TGC`.`uid` DESC ";
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

	public function APICopyTemplateGroupContentToArticleGroupContent($template_group_uid=null,$article_group_uid=null,$article_uid=null,$article_page_uid=null) {
		if($template_group_uid!=null && $article_uid!=null && $article_group_uid!=null && $article_page_uid!=null) {
			$query ="SELECT ";
			$query.="`TGC`.`template_uid`, ";
			$query.="`AC`.`uid` ";
			$query.="FROM ";
			$query.="`template_group_content` AS `TGC`, ";
			$query.="`article_content` AS `AC`";
			$query.="WHERE ";
			$query.="`TGC`.`template_group_uid`='".$template_group_uid."'";
			$query.="AND ";
			$query.="`TGC`.`template_content_uid`=`AC`.`template_content_uid` ";
			$query.="AND ";
			$query.="`AC`.`article_uid` ='".$article_uid."' ";
			$query.="AND ";
			$query.="`AC`.`article_page_uid` ='".$article_page_uid."' ";

			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				while($row = mysql_fetch_array($result)) {
					$this->set_template_uid($row['template_uid']);
					$this->set_article_uid($article_uid);
					$this->set_article_group_uid($article_group_uid);
					$this->set_article_page_uid($article_page_uid);
					$this->set_article_content_uid($row['uid']);
					$this->insert();
				}
			}
		}
	}
}
?>