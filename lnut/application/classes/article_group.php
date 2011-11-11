<?php

class article_group extends generic_object {

	public $arrForm = array( );
	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function getList($article_uid=null,$all=false) {
		
		$where ="WHERE ";
		$where.="`TG`.`article_uid` = `T`.`uid` ";
		$where.="AND ";
		$where.="`TG`.`article_uid` = '".$article_uid."' ";
		if(!$all) {
			$query ="SELECT ";
			$query.="count(`TG`.`uid`) ";
			$query.="FROM ";
			$query.="`article_group` AS `TG`, ";
			$query.="`article` AS `T` ";
			$query.=$where;
			$this->setPagination( $query );
		}
		$query ="SELECT ";
		$query.="`TG`.`uid`, ";
		$query.="`TG`.`article_uid`, ";
		$query.="`TG`.`created_date` ";
		$query.="FROM ";
		$query.="`article_group` AS `TG`, ";
		$query.="`article` AS `T` ";
		$query.=$where;
		$query.="ORDER BY `TG`.`uid` DESC ";
		if(!$all) {
			$query.= "LIMIT ".$this->get_limit();
		}
		return database::arrQuery($query);
	}

	public function APICopyTemplateGroupToArticleGroup($template_uid=null,$article_uid=null,$article_page_uid=null) {
		if($template_uid!=null && $article_uid!=null && $article_page_uid!=null) {
			$query ="SELECT ";
			$query.="* ";
			$query.="FROM ";
			$query.="`template_group` ";
			$query.="WHERE ";
			$query.="`template_uid`='".$template_uid."' ";
			$result=database::query($query);
			if(mysql_error()=='' && $result && mysql_num_rows($result)) {
				$objArticleGroupContent = new article_group_content();
				while($row=mysql_fetch_array($result)) {
					$this->set_article_uid($article_uid);
					$this->set_name($row['name']);
					$this->set_created_date(date('Y-m-d H:i:s'));
					$article_group_uid = $this->insert();
					$objArticleGroupContent->APICopyTemplateGroupContentToArticleGroupContent(
						$row['uid'],
						$article_group_uid,
						$article_uid,
						$article_page_uid
					);
				}
			}
		}
	}

	public function APICreateGroup($objJson=null){
		if($objJson!=null) {
			$arrError = array();
			if(!isset($objJson->name)) {
				$arrError[] = 'name is missing';
			} else if(empty($objJson->name)) {
				$arrError[] = 'name is missing';
			}

			if(!isset($objJson->article_uid)) {
				$arrError[] = 'article_uid is missing';
			} else if($objJson->article_uid==='' && $objJson->article_uid=='0') {
				$arrError[] = 'article_uid is missing';
			} else if(!is_numeric($objJson->article_uid)) {
				$arrError[] = 'invalid article_uid';
			}

			if(!isset($objJson->content_uid_list) && !is_array($objJson->content_uid_list)) {
				$arrError[] = 'content_uid_list is missing';
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
				$this->set_name($objJson->name);
				$this->set_article_uid($objJson->article_uid);
				$this->set_created_date(date('Y-m-d H:i:s'));
				$article_group_uid = $this->insert();
				if(is_array($objJson->content_uid_list) && count($objJson->content_uid_list)) {
					$objArticleGroupContent = new article_group_content();
					foreach($objJson->content_uid_list as $article_content_uid) {
						$objArticleGroupContent->set_article_uid($objJson->article_uid);
						$objArticleGroupContent->set_article_page_uid(article_content::getArticlePageUid($article_content_uid));
						$objArticleGroupContent->set_article_group_uid($article_group_uid);
						$objArticleGroupContent->set_article_content_uid($article_content_uid);
						$objArticleGroupContent->insert();
					}
				}
				return array(
					'status'			=>'success',
					'article_group_uid'=>$article_group_uid
				);
			}
		} else {
			return false;
		}
	}


	public function APIUpdateGroup($objJson=null){
		if($objJson!=null) {
			$arrError = array();
			if(!isset($objJson->name)) {
				$arrError[] = 'name is missing';
			} else if(empty($objJson->name)) {
				$arrError[] = 'name is missing';
			}

			if(!isset($objJson->article_uid)) {
				$arrError[] = 'article_uid is missing';
			} else if($objJson->article_uid==='' && $objJson->article_uid=='0') {
				$arrError[] = 'article_uid is missing';
			} else if(!is_numeric($objJson->article_uid)) {
				$arrError[] = 'invalid article_uid';
			}

			if(!isset($objJson->article_group_uid)) {
				$arrError[] = 'article_group_uid is missing';
			} else if($objJson->article_group_uid==='' && $objJson->article_group_uid=='0') {
				$arrError[] = 'article_group_uid is missing';
			} else if(!is_numeric($objJson->article_group_uid)) {
				$arrError[] = 'invalid article_group_uid';
			}

			if(!isset($objJson->content_uid_list) && !is_array($objJson->content_uid_list)) {
				$arrError[] = 'content_uid_list is missing';
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
				parent::__construct($objJson->article_group_uid, __CLASS__);
				if($this->get_valid()) {
					$this->load();
					$article_group_uid = $objJson->article_group_uid;
					$this->set_name($objJson->name);
					$this->set_article_uid($objJson->article_uid);
					$this->set_created_date(date('Y-m-d H:i:s'));
					$this->save();
					if(is_array($objJson->content_uid_list) && count($objJson->content_uid_list)) {

						$query ="DELETE FROM `article_group_content` WHERE `article_group_uid` = '".$article_group_uid."'";
						database::query($query);

						$objArticleGroupContent = new article_group_content();
						foreach($objJson->content_uid_list as $article_content_uid) {
							$objArticleGroupContent->set_article_uid($objJson->article_uid);
							$objArticleGroupContent->set_article_group_uid($article_group_uid);
							$objArticleGroupContent->set_article_page_uid(article_content::getArticlePageUid($article_content_uid));
							$objArticleGroupContent->set_article_content_uid($article_content_uid);
							$objArticleGroupContent->insert();
						}
					}
					return array(
						'status'			=>'success',
						'article_group_uid'=>$article_group_uid
					);
				} else {
					return array(
						'status'	=>'fail',
						'message'	=>'article_content_uid is not valid.'
					);
				}
			}
		} else {
			return false;
		}
	}

	public function APIDelete($article_content_uid=null) {
		if($article_content_uid!=null) {
			parent::__construct($article_content_uid, __CLASS__);
			$this->load();
			$query ="DELETE FROM `article_group_content` WHERE `article_content_uid` = '".$article_content_uid."'";
			database::query($query);
			$this->delete();
		}
	}
}
?>