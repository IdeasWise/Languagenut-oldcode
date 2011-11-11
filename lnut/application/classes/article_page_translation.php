<?php

class article_page_translation extends generic_object {

	public $arrForm = array();

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function APICopyArticlePageANDPageContentTranslations_old($article_uid=null) {
		if($article_uid!=null) {
			$query ="SELECT ";
			$query.="`uid`, ";
			$query.="`template_uid` ";
			$query.="FROM ";
			$query.="`article_page` ";
			$query.="WHERE ";
			$query.="`article_uid` = '".$article_uid."'";
			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				while($rowPage=mysql_fetch_array($result)) {
					$query ="SELECT ";
					$query.="* ";
					$query.="FROM ";
					$query.="`template_translation` ";
					$query.="WHERE ";
					$query.="`template_uid` = '".$rowPage['template_uid']."' ";
					$resTemplates = database::query($query);
					if(mysql_error()=='' && mysql_num_rows($resTemplates)) {
						while($rowTemplate = mysql_fetch_array($resTemplates)) {
							$this->set_article_uid($article_uid);
							$this->set_article_page_uid($rowPage['uid']);
							$this->set_language_uid($rowTemplate['language_uid']);
							$this->set_width($rowTemplate['width']);
							$this->set_height($rowTemplate['height']);
							$article_page_translation_uid = $this->insert();
							$objArticleContentTranslations = new article_content_translations();
							$objArticleContentTranslations->APICopyArticlePageContentTranslation(
								$article_uid,
								$rowPage['uid'], // article_page_uid
								$article_page_translation_uid,
								$rowTemplate['uid'] // template_translation_uid
							);
						}
					}
				}
			}
		}
	}

	public function APICopyArticlePageANDPageContentTranslations($template_uid=null, $article_uid=null, $article_page_uid=null) {
		if($template_uid!=null && $article_uid!=null && $article_page_uid!=null) {
			$without_template_content_uid = true;
			$query = "SELECT ";
			$query.="* ";
			$query.="FROM ";
			$query.="`template_content` ";
			$query.="WHERE ";
			$query.="`template_uid` = '".$template_uid."'";
			$resTemplateContent = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($resTemplateContent)) {
				$objArticleContent = new article_content();
				while($rowTemplateContent=mysql_fetch_array($resTemplateContent)) {
					$article_content_uid=$objArticleContent->AddArticleContent(
						$rowTemplateContent,
						$article_uid,
						$article_page_uid
					);
					$query ="SELECT ";
					$query.="* ";
					$query.="FROM ";
					$query.="`template_translation` ";
					$query.="WHERE ";
					$query.="`template_uid` = '".$template_uid."' ";
					$resTemplates = database::query($query);
					if(mysql_error()=='' && mysql_num_rows($resTemplates)) {
						while($rowTemplate = mysql_fetch_array($resTemplates)) {
							$article_page_translation_uid = $this->CheckEntryExistAndAdd(
								$rowTemplate,
								$article_uid,
								$article_page_uid
							);
							$objArticleContentTranslations = new article_content_translations();
							$objArticleContentTranslations->APICopyArticlePageContentTranslation(
								$article_uid,
								$article_page_uid, // article_page_uid
								$article_page_translation_uid,
								$rowTemplate['uid'], // template_translation_uid
								$article_content_uid,
								$rowTemplateContent['uid'] // template_content_uid
							);

							if($without_template_content_uid) {
									$objArticleContentTranslations->APICopyArticlePageContentTranslation(
										$article_uid,
										$article_page_uid, // article_page_uid
										$article_page_translation_uid,
										$rowTemplate['uid'], // template_translation_uid
										$article_content_uid
									);
							}
						}
					}
					$without_template_content_uid=false;
				}
			}
		}
	}

private function CheckEntryExistAndAdd($arrData=array(),$article_page_uid=null,$article_uid=null) {
	if(count($arrData) && $article_page_uid!=null && $article_uid!=null) {
		$query ="SELECT ";
		$query.="`uid` ";
		$query.="FROM ";
		$query.="`article_page_translation` ";
		$query.="WHERE ";
		$query.="`article_uid`='".$article_uid."' ";
		$query.="AND ";
		$query.="`article_page_uid`='".$article_page_uid."' ";
		$query.="AND ";
		$query.="`language_uid`='".$arrData['language_uid']."' ";
		$query.="LIMIT 0,1";
		$result = database::query($query);
		if(mysql_error()=='' && mysql_num_rows($result)) {
			$row = mysql_fetch_array($result);
			return $row['uid'];
		} else {
			$this->set_article_uid($article_uid);
			$this->set_article_page_uid($article_page_uid);
			$this->set_language_uid($arrData['language_uid']);
			$this->set_width($arrData['width']);
			$this->set_height($arrData['height']);
			$article_page_translation_uid = $this->insert();
			return $article_page_translation_uid;
		}
	}
}

public function APICopyArticlePageTranslations($article_uid=null) {
		if($article_uid!=null) {
			$query ="SELECT ";
			$query.="`uid`, ";
			$query.="`template_uid` ";
			$query.="FROM ";
			$query.="`article_page` ";
			$query.="WHERE ";
			$query.="`article_uid` = '".$article_uid."'";
			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				while($rowPage=mysql_fetch_array($result)) {
					$query ="SELECT ";
					$query.="* ";
					$query.="FROM ";
					$query.="`template_translation` ";
					$query.="WHERE ";
					$query.="`template_uid` = '".$rowPage['template_uid']."' ";
					$resTemplates = database::query($query);
					if(mysql_error()=='' && mysql_num_rows($resTemplates)) {
						while($rowTemplate = mysql_fetch_array($resTemplates)) {


							$query ="SELECT ";
							$query.="`uid` ";
							$query.="FROM ";
							$query.="`article_page_translation` ";
							$query.="WHERE ";
							$query.="`article_uid` = '".$article_uid."' ";
							$query.="AND ";
							$query.="`article_page_uid`='".$rowPage['uid']."' ";
							$query.="AND ";
							$query.="`language_uid`='".$rowTemplate['language_uid']."' ";
							$query.="LIMIT 0,1";
							$checkRequest = database::query($query);
							$update = false;
							if(mysql_error()=='' && mysql_num_rows($checkRequest) == 1) {
								$rowPageTranslation = mysql_fetch_array($checkRequest);
								$update = true;
								parent::__construct($rowPageTranslation['uid'],__CLASS__);
								$this->load();
							}
							$this->set_article_uid($article_uid);
							$this->set_article_page_uid($rowPage['uid']);
							$this->set_language_uid($rowTemplate['language_uid']);
							$this->set_width($rowTemplate['width']);
							$this->set_height($rowTemplate['height']);
							if($update==true) {
								$this->save();
							} else {
								$this->insert();
							}


						}
					}
				}
			}
		}
	}

	public function APICopyPageTranslation($article_page_uid=null) {
		if($article_page_uid!=null) {
			$arrResponse = array();
			$query	="SELECT * FROM `article_page` WHERE `uid`='".$article_page_uid."' LIMIT 0,1";
			$result	=database::query($query);
			$arrArticlePage = array();
			if($result && mysql_error()=="" && mysql_num_rows($result)) {
				$arrArticlePage = mysql_fetch_array($result);
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
						$query.="`article_page_translation` ";
						$query.="WHERE ";
						$query.="`article_page_uid`='".$article_page_uid."' ";
						$query.="AND ";
						$query.="`language_uid`='".$row['uid']."' ";
						$query.="LIMIT 0,1";
						$result2 = database::query($query);
						$update = false;
						$article_page_translation_uid = null;
						if(mysql_error() == '' && mysql_num_rows($result2)) {
							$arrTranslation = mysql_fetch_array($result2);
							parent::__construct($arrTranslation['uid'],__CLASS__);
							$this->load();
							$update = true;
							$article_page_translation_uid = $arrTranslation['uid'];
						}
						$this->set_article_uid($arrArticlePage['article_uid']);
						$this->set_width($arrArticlePage['width']);
						$this->set_height($arrArticlePage['height']);
						$this->set_article_page_uid($article_page_uid);
						$this->set_language_uid($row['uid']);
						if($update == false) {
							$article_page_translation_uid = $this->insert();
						} else {
							$this->save();
						}
						$arrResponse[] = array(
							'language_uid'				=> $row['uid'],
							'article_page_translation_uid'	=> $article_page_translation_uid
						);
					}
				}
			}
		}
		return $arrResponse;
	}

}
// ALTER TABLE `article_content` ADD `template_content_uid` INT( 11 ) NOT NULL AFTER `article_uid` 
// ALTER TABLE `article_content_translations` ADD `template_content_translation_uid` INT( 11 ) NOT NULL AFTER `article_uid` 
?>