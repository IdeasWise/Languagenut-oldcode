<?php

class article_page extends generic_object {

	public $arrForm = array();

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function getList($article_uid=null,$all=false) {
		
		$where ="WHERE ";
		$where.="`AP`.`article_uid` = `A`.`uid` ";
		$where.="AND ";
		$where.="`AP`.`article_uid` = '".$article_uid."' ";
		if(!$all) {
			$query ="SELECT ";
			$query.="count(`AP`.`uid`) ";
			$query.="FROM ";
			$query.="`article_page` AS `AP`, ";
			$query.="`article` AS `A` ";
			$query.=$where;
			$this->setPagination( $query );
		}
		$query ="SELECT ";
		$query.="`AP`.`uid`, ";
		$query.="`AP`.`article_uid`, ";
		$query.="`AP`.`page_order` ";
		$query.="FROM ";
		$query.="`article_page` AS `AP`, ";
		$query.="`article` AS `A` ";
		$query.=$where;
		$query.="ORDER BY `AP`.`page_order`";
		if(!$all) {
			$query.= "LIMIT ".$this->get_limit();
		}
		return database::arrQuery($query);
	}

	public function SaveArticlePage($article_uid=null) { 
		if($article_uid!=null && isset($_POST['template_uid']) && is_array($_POST['template_uid'])) { 
			$loop = count($_POST['template_uid']);
			for($i=0; $i<$loop; $i++) {
				$this->set_article_uid($article_uid);
				$this->set_template_uid($_POST['template_uid'][$i]);
				$this->set_page_order($i+1);
				$this->insert();
			}
		}
	}
	public function getPageOrder($article_uid=null) {
		if($article_uid!=null) {
			$query="SELECT ";
			$query.="MAX(`page_order`) AS `page_order` ";
			$query.="FROM ";
			$query.="`article_page` ";
			$query.="WHERE ";
			$query.="`article_uid`='".$article_uid."'";
			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				$row = mysql_fetch_array($result);
				return $row['page_order']+1;
			} else {
				return 1;
			}
		}
	}
	

	public function APICreateArticlePage($template_uid=null,$article_uid=null) {
		$arrResult = array();
		if($template_uid!=null && $article_uid!=null) {
			$objArtcile		= new article($article_uid);
			if($objArtcile->get_valid()) { 
				$objTemplate	= new template($template_uid);
				if($objTemplate->get_valid()) {
					$objTemplate->load();
					$this->set_article_uid($article_uid);
					$this->set_template_uid($template_uid);
					$this->set_width($objTemplate->get_width());
					$this->set_height($objTemplate->get_height());
					$this->set_page_order($this->getPageOrder($article_uid));
					$article_page_uid = $this->insert();

					$ArticleContent = new article_content();
					$ArticleContent->CopyTemplateContentToArticleContent(
						$template_uid,
						$article_uid,
						$article_page_uid
					);
					$objArticlePageTranslation = new article_page_translation();
					$objArticlePageTranslation->APICopyArticlePageANDPageContentTranslations($article_uid);

					$objArticleTranslations = new article_translations();
					$response = $objArticleTranslations->APICopyArticleTranslations($article_uid);
					$arrResult = array(
						'status'	=>'success',
						'article_page_uid'	=>$article_page_uid
					);
				} else {
					$arrResult = array(
						'status'	=>'fail',
						'reason'	=>'template is not exist!'
					);
				}
			} else {
				$arrResult = array(
					'status'	=>'fail',
					'reason'	=>'article is not exist!'
				);
			}
		} else {
			$arrResult = array(
				'status'	=>'fail',
				'reason'	=>'missing article_uid or template_uid'
			);
		}
		return $arrResult;
	}

	public function APIUpdateArticlePage($objJson=null) {
		if($objJson!=null) {
			$arrError = array();
			if(!isset($objJson->article_page_uid)) {
				$arrError[] = 'article_page_uid missing';
			} else if(empty($objJson->article_page_uid)) {
				$arrError[] = 'article_page_uid is empty';
			} else if(!is_numeric($objJson->article_page_uid)) {
				$arrError[] = 'invalid article_page_uid';
			} else {
				parent::__construct($objJson->article_page_uid, __CLASS__);
				if($this->get_valid()) {
					$this->load();
				} else {
					$arrError[] = 'article_page_uid is not exist';
				}
			}
			if(isset($objJson->width) && $objJson->width==='') {
				$arrError[] = 'width is empty';
			} else if(isset($objJson->width) && !is_numeric($objJson->width)) {
				$arrError[] = 'invalid width';
			} else if(isset($objJson->width)) {
				$this->set_width($objJson->width);
			}
			if(isset($objJson->height) && $objJson->height==='') {
				$arrError[] = 'height is empty';
			} else if(isset($objJson->height) && !is_numeric($objJson->height)) {
				$arrError[] = 'invalid height';
			} else if(isset($objJson->height)) {
				$this->set_height($objJson->height);
			}
			if(count($arrError)) {
				$arrResponse = array();
				foreach($objJson as $index => $value ) {
					$arrResponse[$index] = $value;
				}
				$arrResponse['status'] = 'fail';
				$arrResponse['error_msg'] = implode(' | ',$arrError);
				return $arrResponse;
			} else {
				$this->save();
				return array(
					'status'=>'success',
					'article_page_uid'=>$objJson->article_page_uid
				);
			}
		}
	}

	public function APIdeleteArticlePage($article_page_uid=null,$article_uid=null) {
		parent::__construct($article_page_uid, __CLASS__);
		if($this->get_valid()) {
			$this->load();
			if($this->get_article_uid()==$article_uid) {

				$query ="DELETE ";
				$query.="FROM ";
				$query.="`article_content` ";
				$query.="WHERE ";
				$query.="`article_uid`='".$article_uid."' ";
				$query.="AND ";
				$query.="`article_page_uid`='".$article_page_uid."'";
				database::query($query);

				$query ="DELETE ";
				$query.="FROM ";
				$query.="`article_content_translations` ";
				$query.="WHERE ";
				$query.="`article_uid`='".$article_uid."' ";
				$query.="AND ";
				$query.="`article_page_uid`='".$article_page_uid."'";
				database::query($query);
				$this->delete();
			} else {
				return array(
					'status'	=>'fail',
					'message'	=>'article_uid is not valid'
				);
			}
		} else {
			return array(
				'status'	=>'fail',
				'message'	=>'article_page_uid is not valid'
			);
		}
	}
}
?>