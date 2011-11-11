<?php

class article extends generic_object {

	public $arrForm = array( );
	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function getList( $all = false ) {
		
		if( !$all ) {
			$query ="SELECT ";
			$query.="count(`uid`) ";
			$query.="FROM ";
			$query.="`article` ";
			$this->setPagination( $query );
		}
		$query ="SELECT ";
		$query.="* ";
		$query.="FROM ";
		$query.="`article` ";
		$query.="ORDER BY ";
		$query.="`title` ";
		if($all	== false) {
			$query.= "LIMIT ".$this->get_limit();
		}
		return database::arrQuery($query);
	}

	public function getArticles($unit_uid = 0) {

		$response = array();

		$query = "SELECT ";
		$query.= "`uid`, ";
		$query.= "`title` ";
		$query.= "FROM ";
		$query.= "`article` ";
		$query.= "WHERE ";
		$query.= "`unit_uid`='".(int)$unit_uid."' ";
		$query.= "ORDER BY ";
		$query.= "`title` ASC";

		$result = database::query($query);

		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			while($row = mysql_fetch_assoc($result)) {
				$response[$row['uid']] = array(
					'uid'=>$row['uid'],
					'title'=>stripslashes($row['title'])
				);
			}
		}

		return $response;
	}

	public function isValidCreate () {
		
		if($this->isValidateFormData() === true) {
			$article_uid = $this->insert();
			$objArticleTranslation = new article_translations();
			$objArticleTranslation->SaveArticleTranslation($article_uid);
			$objArticlePage = new article_page();
			$objArticlePage->SaveArticlePage($article_uid);
			return true;
		} else {
			return false;
		}
		
	}

	public function isValidUpdate () {
		
		if($this->isValidateFormData() === true) {
			$this->save();
			$objArticleTranslation = new article_translations();
			$objArticleTranslation->SaveArticleTranslation($this->get_uid());
			return true;
		} else {
			return false;
		}
		
	}

	private function isValidateFormData() {

		if(isset($_POST['uid']) && is_numeric($_POST['uid']) && $_POST['uid'] > 0) {
			parent::__construct($_POST['uid'],__CLASS__);
			$this->load();
		} else {
			$this->set_created_date(date('Y-m-d H:i:s'));
		}
		$arrFields = array(
			'title'=>array(
				'value'			=> (isset($_POST['title']))?trim($_POST['title']):'',
				'checkEmpty'	=> false,
				'errEmpty'		=> '',
				'minChar'		=> 5,
				'maxChar'		=> 260,
				'errMinMax'		=> 'Title must be 5 to 255 characters in length.',
				'dataType'		=> 'text',
				'errdataType'	=> 'Please enter valid title.',
				'errIndex'		=> 'error.title'
			),
			'article_category_uid'=>array(
				'value'			=> (isset($_POST['article_category_uid']))?trim($_POST['article_category_uid']):0,
				'checkEmpty'	=> true,
				'errEmpty'		=> 'Please select category.',
				'minChar'		=> 0,
				'maxChar'		=> 0,
				'errMinMax'		=> '',
				'dataType'		=> 'int',
				'errdataType'	=> 'Please select valid category.',
				'errIndex'		=> 'error.article_category_uid'
			),
			'unit_uid'=>array(
				'value'			=> (isset($_POST['unit_uid']))?trim($_POST['unit_uid']):0,
				'checkEmpty'	=> true,
				'errEmpty'		=> 'Please select unit.',
				'minChar'		=> 0,
				'maxChar'		=> 0,
				'errMinMax'		=> '',
				'dataType'		=> 'int',
				'errdataType'	=> 'Please select valid unit.',
				'errIndex'		=> 'error.unit_uid'
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
			),
			'token'=>array(
				'value'			=> (isset($_POST['token']))?trim($_POST['token']):'',
				'checkEmpty'	=> false,
				'errEmpty'		=> '',
				'minChar'		=> 0,
				'maxChar'		=> 255,
				'errMinMax'		=> 'Token must be 0 to 255 characters in length.',
				'dataType'		=> 'text',
				'errdataType'	=> 'Please enter valid token.',
				'errIndex'		=> 'error.token'
			)
		);

		$arrMessages = array();

		$objArticleTranslation = new article_translations();
		$result = $objArticleTranslation->isValidInput();
		if(is_array($result) && count($result)) {
			if(is_array($result['arrName']) && count($result['arrName'])) {
				$arrMessages['error_names'] = "Name translation should not more than 255 characters in length in ".implode(',',$result['arrName'])." locale.";
			}
			if(is_array($result['arrWidth']) && count($result['arrWidth'])) {
				$arrMessages['error_widths'] = "Please enter valid width or it should not more than 5 digits in ".implode(',',$result['arrWidth'])." locale.";
			}
			if(is_array($result['arrHeight']) && count($result['arrHeight'])) {
				$arrMessages['error_heights'] = "Please enter valid height or it should not more than 5 digits in ".implode(',',$result['arrHeight'])." locale.";
			}
		}
		if(isset($_POST['template_uid'])) {
			foreach($_POST['template_uid'] as $template_uid) {
				if($template_uid == 0) {
					$arrMessages['error.template_uid'] = "Please select article template from available options.";
				}
			}
		}




		// $arrFields contains array for fields which needs to be validate and then we are passing class object($this)
		if($this->isValidarrFields($arrFields,$this,$arrMessages) === true) {
			$this->set_title($arrFields['title']['value']);
			//$this->set_template_uid($arrFields['template_uid']['value']);
			$this->set_article_category_uid($arrFields['article_category_uid']['value']);
			$this->set_unit_uid($arrFields['unit_uid']['value']);
			$this->set_width($arrFields['width']['value']);
			$this->set_height($arrFields['height']['value']);
			$this->set_token($arrFields['token']['value']);
			return true;
		} else {
			return false;
		}
	}

	public function APICreateArticle_old($objJson=null){
		if($objJson!=null) {
			$arrError = array();
			if(!isset($objJson->title)) {
				$arrError[] = 'title is missing';
			} else if(empty($objJson->title)) {
				$arrError[] = 'title is empty';
			}
			if(!isset($objJson->width)) {
				$arrError[] = 'width is missing';
			} else if(empty($objJson->width)) {
				$arrError[] = 'width is empty';
			} else if(!is_numeric($objJson->width)) {
				$arrError[] = 'invalid is width';
			}
			if(!isset($objJson->height)) {
				$arrError[] = 'height is missing';
			} else if(empty($objJson->height)) {
				$arrError[] = 'height is empty';
			} else if(!is_numeric($objJson->height)) {
				$arrError[] = 'invalid is height';
			}
			if(!isset($objJson->article_template_type_uid)) {
				$arrError[] = 'article_type is missing';
			} else if(empty($objJson->article_template_type_uid)) {
				$arrError[] = 'article_type is empty';
			} else if(!is_numeric($objJson->article_template_type_uid)) {
				$arrError[] = 'article_type height';
			}
			if(!isset($objJson->template_uid)) {
				$arrError[] = 'template_uid is missing';
			} else if(empty($objJson->template_uid)) {
				$arrError[] = 'template_uid is empty';
			} else if(!is_numeric($objJson->template_uid)) {
				$arrError[] = 'invalid is template_uid';
			}
			if(!isset($objJson->unit_uid)) {
				$arrError[] = 'unit_uid is missing';
			} else if(empty($objJson->unit_uid)) {
				$arrError[] = 'unit_uid is empty';
			} else if(!is_numeric($objJson->unit_uid)) {
				$arrError[] = 'invalid is unit_uid';
			}
			if(!isset($objJson->article_category_uid)) {
				$arrError[] = 'article_category_uid is missing';
			} else if(empty($objJson->article_category_uid)) {
				$arrError[] = 'article_category_uid is empty';
			} else if(!is_numeric($objJson->article_category_uid)) {
				$arrError[] = 'invalid is article_category_uid';
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
				$this->set_title($objJson->title);
				$this->set_width($objJson->width);
				$this->set_height($objJson->height);
				$this->set_article_template_type_uid($objJson->article_template_type_uid);
				$this->set_article_category_uid($objJson->article_category_uid);
				$this->set_unit_uid($objJson->unit_uid);
				$article_uid = $this->insert();

				$objArticlePage = new article_page();
				$objArticlePage->set_article_uid($article_uid);
				$objArticlePage->set_template_uid($objJson->template_uid);
				$objArticlePage->set_page_order(1);
				$objArticlePage->insert();

				$objArticleTranslations = new article_translations();
				$objArticleTranslations->APICreateArticleTranslations($objJson,$article_uid);
				return $article_uid;
			}
		} else {
			return false;
		}
	}

	public function APICreateArticle($objJson=null){
		if($objJson!=null) {
			$arrError = array();
			if(!isset($objJson->title)) {
				$arrError[] = 'title is missing';
			} else if(empty($objJson->title)) {
				$arrError[] = 'title is empty';
			}
			if(!isset($objJson->template_uid)) {
				$arrError[] = 'template_uid is missing';
			} else if(empty($objJson->template_uid)) {
				$arrError[] = 'template_uid is empty';
			} else if(!is_numeric($objJson->template_uid)) {
				$arrError[] = 'invalid is template_uid';
			}
			if(!isset($objJson->unit_uid)) {
				$arrError[] = 'unit_uid is missing';
			} else if(empty($objJson->unit_uid)) {
				$arrError[] = 'unit_uid is empty';
			} else if(!is_numeric($objJson->unit_uid)) {
				$arrError[] = 'invalid is unit_uid';
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
				$this->set_title($objJson->title);
				$this->set_article_category_uid($objJson->article_category_uid);
				$this->set_template_uid($objJson->template_uid);
				$this->set_unit_uid($objJson->unit_uid);
				$article_uid = $this->insert();

				$objTemplate = new template($objJson->template_uid);
				if($objTemplate->get_valid()) {
					$objTemplate->load();
					$objArticlePage = new article_page();
					$objArticlePage->set_article_uid($article_uid);
					$objArticlePage->set_template_uid($objJson->template_uid);
					$objArticlePage->set_width($objTemplate->get_width());
					$objArticlePage->set_height($objTemplate->get_height());
					$objArticlePage->set_page_order(1);
					$article_page_uid = $objArticlePage->insert();

					/*
					$ArticleContent = new article_content();
					$ArticleContent->CopyTemplateContentToArticleContent(
						$objJson->template_uid,
						$article_uid,
						$article_page_uid
					);
					*/


					$objArticlePageTranslation = new article_page_translation();
					$objArticlePageTranslation->APICopyArticlePageANDPageContentTranslations(
						$objJson->template_uid,
						$article_uid,
						$article_page_uid
					);

					$objArticleGroup = new article_group();
					$objArticleGroup->APICopyTemplateGroupToArticleGroup(
						$objJson->template_uid,
						$article_uid,
						$article_page_uid
					);

					$objArticleTranslations = new article_translations();
					$response = $objArticleTranslations->APICopyArticleTranslations($article_uid);
				}
				return $article_uid;
			}
		} else {
			return false;
		}
	}

	public function APIUpdateArticle($objJson=null){
		if($objJson!=null) {
			$arrError = array();

			if(!isset($objJson->article_uid)) {
				$arrError[] = 'article_uid is missing';
			} else if($objJson->article_uid==='') {
				$arrError[] = 'article_uid is empty';
			} else if(!is_numeric($objJson->article_uid)) {
				$arrError[] = 'invalid is article_uid';
			}

			if(!isset($objJson->title)) {
				$arrError[] = 'title is missing';
			} else if(empty($objJson->title)) {
				$arrError[] = 'title is empty';
			}

			if(isset($objJson->unit_uid)&& $objJson->unit_uid==='') {
				$arrError[] = 'unit_uid is empty';
			} else if(isset($objJson->unit_uid) && !is_numeric($objJson->unit_uid)) {
				$arrError[] = 'invalid is unit_uid';
			}

			if(isset($objJson->article_category_uid) && $objJson->article_category_uid==='') {
				$arrError[] = 'article_category_uid is empty';
			} else if(isset($objJson->article_category_uid) && !is_numeric($objJson->article_category_uid)) {
				$arrError[] = 'invalid is article_category_uid';
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
				parent::__construct($objJson->article_uid, __CLASS__);
				$this->load();
				$this->set_title($objJson->title);
				if(isset($objJson->article_category_uid)) {
					$this->set_article_category_uid($objJson->article_category_uid);
				}
				if(isset($objJson->unit_uid)) {
					$this->set_unit_uid($objJson->unit_uid);
				}
				$this->save();
				return $objJson->article_uid;
			}
		} else {
			return false;
		}
	}

	public function deleteArticle($article_uid=null) {
		if($article_uid!=null && is_numeric($article_uid)) {
			parent::__construct($article_uid, __CLASS__);
			if($this->get_valid()) {
				$this->load();
				$query ="DELETE ";
				$query.="FROM ";
				$query.="`article_content` ";
				$query.="WHERE ";
				$query.="`article_uid`='".$article_uid."' ";
				database::query($query);
				if(mysql_error()!='') {
					die(mysql_error());
				}

				$query ="DELETE ";
				$query.="FROM ";
				$query.="`article_content_translations` ";
				$query.="WHERE ";
				$query.="`article_uid`='".$article_uid."' ";
				database::query($query);
				if(mysql_error()!='') {
					die(mysql_error());
				}

				$query ="DELETE ";
				$query.="FROM ";
				$query.="`article_group` ";
				$query.="WHERE ";
				$query.="`article_uid`='".$article_uid."' ";
				database::query($query);
				if(mysql_error()!='') {
					die(mysql_error());
				}

				$query ="DELETE ";
				$query.="FROM ";
				$query.="`article_group_content` ";
				$query.="WHERE ";
				$query.="`article_uid`='".$article_uid."' ";
				database::query($query);
				if(mysql_error()!='') {
					die(mysql_error());
				}

				$query ="DELETE ";
				$query.="FROM ";
				$query.="`article_page` ";
				$query.="WHERE ";
				$query.="`article_uid`='".$article_uid."' ";
				database::query($query);
				if(mysql_error()!='') {
					die(mysql_error());
				}

				$query ="DELETE ";
				$query.="FROM ";
				$query.="`article_page_translation` ";
				$query.="WHERE ";
				$query.="`article_uid`='".$article_uid."' ";
				database::query($query);
				if(mysql_error()!='') {
					die(mysql_error());
				}

				$query ="DELETE ";
				$query.="FROM ";
				$query.="`article_translations` ";
				$query.="WHERE ";
				$query.="`article_uid`='".$article_uid."' ";
				database::query($query);
				if(mysql_error()!='') {
					die(mysql_error());
				}
				$this->delete();
				return array(
					'status'=>'success'
				);

			}
			echo array(
				'status'	=>'false',
				'message'	=>'article_uid is not valid.'
			);
		}
	}

}
?>