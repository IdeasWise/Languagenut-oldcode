<?php

class article_content extends generic_object {

	public $arrForm = array( );
	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function getList($article_uid=null,$all=false) {
		
		$where ="WHERE ";
		$where.="`AC`.`article_uid` = `A`.`uid` ";
		$where.="AND ";
		$where.="`AC`.`article_uid` = '".$article_uid."' ";
		$where.=" GROUP BY `AC`.`uid` ";
		if(!$all) {
			$query ="SELECT ";
			$query.="count(`AC`.`uid`) ";
			$query.="FROM ";
			$query.="`article_content` AS `AC`, ";
			$query.="`article` AS `A` ";
			$query.=$where;
			$this->setPagination( $query );
		}
		$query ="SELECT ";
		$query.="`AC`.`uid`, ";
		$query.="`AC`.`article_uid`, ";
		$query.="`AC`.`article_page_uid`, ";
		$query.="`AC`.`content` ";
		$query.="FROM ";
		$query.="`article_content` AS `AC`, ";
		$query.="`article` AS `A` ";
		$query.=$where;
		$query.="ORDER BY `AC`.`uid` DESC ";
		if(!$all) {
			$query.= "LIMIT ".$this->get_limit();
		}
		return database::arrQuery($query);
	}

	public function isValidCreate () {
		
		if($this->isValidateFormData() === true) {
			$article_content_uid = $this->insert();
			if(isset($_POST['copy_to_translation']) && $_POST['copy_to_translation'] == 1) {
				$objArticleContentTranslations = new article_content_translations();
				$objArticleContentTranslations->CopyContentToContentTranslation($_POST['article_uid'],$article_content_uid);
			}
			//$objTemplateTranslation->SaveTemplateTranslation($article_uid);
			return true;
		} else {
			return false;
		}
		
	}

	public function isValidUpdate () {
		
		if($this->isValidateFormData() === true) {
			$this->save();
			//$objTemplateTranslation = new template_translation();
			//$objTemplateTranslation->SaveTemplateTranslation($this->get_uid());
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
			'item_type_uid'=>array(
				'value'			=> (isset($_POST['item_type_uid']))?trim($_POST['item_type_uid']):'',
				'checkEmpty'	=> false,
				'errEmpty'		=> '',
				'minChar'		=> 0,
				'maxChar'		=> 3,
				'errMinMax'		=> 'Content type must be 0 to 3 characters in length.',
				'dataType'		=> 'int',
				'errdataType'	=> 'Please enter valid content type.',
				'errIndex'		=> 'error.item_type_uid'
			),
			'content'=>array(
				'value'			=> (isset($_POST['content']))?trim($_POST['content']):'',
				'checkEmpty'	=> true,
				'errEmpty'		=> 'Please enter content.',
				'minChar'		=> 0,
				'maxChar'		=> 0,
				'errMinMax'		=> '',
				'dataType'		=> 'text',
				'errdataType'	=> 'Please enter valid content.',
				'errIndex'		=> 'error.content'
			),
			'rotation'=>array(
				'value'			=> (isset($_POST['rotation']))?trim($_POST['rotation']):'',
				'checkEmpty'	=> true,
				'errEmpty'		=> 'Please enter rotation.',
				'minChar'		=> 0,
				'maxChar'		=> 5,
				'errMinMax'		=> 'Rotation must be 0 to 5 digits.',
				'dataType'		=> 'int',
				'errdataType'	=> 'Please enter valid rotation.',
				'errIndex'		=> 'error.rotation'
			),
			'width'=>array(
				'value'			=> (isset($_POST['width']))?trim($_POST['width']):'',
				'checkEmpty'	=> true,
				'errEmpty'		=> 'Please enter width.',
				'minChar'		=> 0,
				'maxChar'		=> 5,
				'errMinMax'		=> 'Width must be 0 to 5 digits.',
				'dataType'		=> 'int',
				'errdataType'	=> 'Please enter valid width.',
				'errIndex'		=> 'error.width'
			),
			'height'=>array(
				'value'			=> (isset($_POST['height']))?trim($_POST['height']):'',
				'checkEmpty'	=> true,
				'errEmpty'		=> 'Please enter height.',
				'minChar'		=> 0,
				'maxChar'		=> 5,
				'errMinMax'		=> 'Height must be 0 to 5 digits.',
				'dataType'		=> 'int',
				'errdataType'	=> 'Please enter valid Height.',
				'errIndex'		=> 'error.height'
			),
			'fontfamily'=>array(
				'value'			=> (isset($_POST['fontfamily']))?trim($_POST['fontfamily']):'',
				'checkEmpty'	=> false,
				'errEmpty'		=> '',
				'minChar'		=> 5,
				'maxChar'		=> 32,
				'errMinMax'		=> 'Font family must be 5 to 32 characters in length.',
				'dataType'		=> 'text',
				'errdataType'	=> 'Please enter valid font family.',
				'errIndex'		=> 'error.fontfamily'
			),
			'fontsize'=>array(
				'value'			=> (isset($_POST['fontsize']))?trim($_POST['fontsize']):'',
				'checkEmpty'	=> true,
				'errEmpty'		=> 'Please enter font size.',
				'minChar'		=> 0,
				'maxChar'		=> 2,
				'errMinMax'		=> 'Font size must be 0 to 2 digits.',
				'dataType'		=> 'int',
				'errdataType'	=> 'Please enter valid font size.',
				'errIndex'		=> 'error.fontsize'
			),
			'textalignment'=>array(
				'value'			=> (isset($_POST['textalignment']))?trim($_POST['textalignment']):'',
				'checkEmpty'	=> false,
				'errEmpty'		=> '',
				'minChar'		=> 4,
				'maxChar'		=> 8,
				'errMinMax'		=> 'Text alignment must be 4 to 8 characters in length.',
				'dataType'		=> 'text',
				'errdataType'	=> 'Please enter valid text alignment.',
				'errIndex'		=> 'error.textalignment'
			),
			'textcolour'=>array(
				'value'			=> (isset($_POST['textcolour']))?trim($_POST['textcolour']):'',
				'checkEmpty'	=> false,
				'errEmpty'		=> '',
				'minChar'		=> 4,
				'maxChar'		=> 32,
				'errMinMax'		=> 'Text colour must be 5 to 32 characters in length.',
				'dataType'		=> 'text',
				'errdataType'	=> 'Please enter valid text colour.',
				'errIndex'		=> 'error.textcolour'
			),
			'positionx'=>array(
				'value'			=> (isset($_POST['positionx']))?trim($_POST['positionx']):'',
				'checkEmpty'	=> false,
				'errEmpty'		=> '',
				'minChar'		=> 0,
				'maxChar'		=> 9,
				'errMinMax'		=> 'Position X must be 0 to 9 digits.',
				'dataType'		=> 'int',
				'errdataType'	=> 'Please enter valid position X.',
				'errIndex'		=> 'error.positionx'
			),
			'positiony'=>array(
				'value'			=> (isset($_POST['positiony']))?trim($_POST['positiony']):'',
				'checkEmpty'	=> false,
				'errEmpty'		=> '',
				'minChar'		=> 0,
				'maxChar'		=> 9,
				'errMinMax'		=> 'Position Y must be 0 to 9 digits.',
				'dataType'		=> 'int',
				'errdataType'	=> 'Please enter valid position Y.',
				'errIndex'		=> 'error.positiony'
			),
			'stackingposition'=>array(
				'value'			=> (isset($_POST['stackingposition']))?trim($_POST['stackingposition']):'',
				'checkEmpty'	=> false,
				'errEmpty'		=> '',
				'minChar'		=> 0,
				'maxChar'		=> 9,
				'errMinMax'		=> 'Stacking Position X must be 0 to 9 digits.',
				'dataType'		=> 'int',
				'errdataType'	=> 'Please enter valid stacking position.',
				'errIndex'		=> 'error.stackingposition'
			)
		);
		// $arrFields contains array for fields which needs to be validate and then we are passing class object($this)
		if($this->isValidarrFields($arrFields,$this) === true) {
			$this->set_article_uid($_POST['article_uid']);
			$this->set_article_page_uid($_POST['article_page_uid']);
			$this->set_item_type_uid($arrFields['item_type_uid']['value']);
			$this->set_content($arrFields['content']['value']);
			$this->set_rotation($arrFields['rotation']['value']);
			$this->set_width($arrFields['width']['value']);
			$this->set_height($arrFields['height']['value']);
			$this->set_fontfamily($arrFields['fontfamily']['value']);
			$this->set_fontsize($arrFields['fontsize']['value']);
			$this->set_textalignment($arrFields['textalignment']['value']);
			$this->set_textcolour($arrFields['textcolour']['value']);
			$this->set_positionx($arrFields['positionx']['value']);
			$this->set_positiony($arrFields['positiony']['value']);
			$this->set_stackingposition($arrFields['stackingposition']['value']);
			if(isset($_POST['accept_content'])) {
				$this->set_accept_content($_POST['accept_content']);
			}
			return true;
		} else {
			return false;
		}

	}

	public function CopyTemplateContentToArticleContent($template_uid=null,$article_uid=null,$page_uid=null) {
		if($template_uid!=null && $article_uid!=null && $page_uid!=null) {
			$query = "SELECT ";
			$query.="* ";
			$query.="FROM ";
			$query.="`template_content` ";
			$query.="WHERE ";
			$query.="`template_uid` = '".$template_uid."'";
			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				while($row=mysql_fetch_array($result)) {
					$this->set_article_uid($article_uid);
					$this->set_article_page_uid($page_uid);
					$this->set_item_type_uid($row['item_type_uid']);
					$this->set_content($row['content']);
					$this->set_rotation($row['rotation']);
					$this->set_width($row['width']);
					$this->set_height($row['height']);
					$this->set_fontfamily($row['fontfamily']);
					$this->set_fontsize($row['fontsize']);
					$this->set_textalignment($row['textalignment']);
					$this->set_textcolour($row['textcolour']);
					$this->set_positionx($row['positionx']);
					$this->set_positiony($row['positiony']);
					$this->set_stackingposition($row['stackingposition']);
					$article_content_uid = $this->insert();
				}
			}
		}
	}

	public function APIupdateArticleContent($objJson=null){
		if($objJson!=null) {
			$arrError = array();

			if(!isset($objJson->article_content_uid)) {
				$arrError[] = 'article_content_uid is missing';
			} else if(empty($objJson->article_content_uid)) {
				$arrError[] = 'article_content_uid is empty';
			} else if(!is_numeric($objJson->article_content_uid)) {
				$arrError[] = 'invalid article_content_uid';
			}

			if(!isset($objJson->item_type_uid)) {
				$arrError[] = 'item_type_uid is missing';
			} else if($objJson->item_type_uid==='') {
				$arrError[] = 'item_type_uid is empty';
			} else if(!is_numeric($objJson->item_type_uid)) {
				$arrError[] = 'invalid item_type_uid';
			}

			if(!isset($objJson->rotation)) {
				$arrError[] = 'rotation is missing';
			} else if($objJson->rotation==='') {
				$arrError[] = 'rotation is empty';
			} else if(!is_numeric($objJson->rotation)) {
				$arrError[] = 'invalid rotation';
			}

			if(!isset($objJson->width)) {
				$arrError[] = 'width is missing';
			} else if($objJson->width==='') {
				$arrError[] = 'width is empty';
			} else if(!is_numeric($objJson->width)) {
				$arrError[] = 'invalid width';
			}

			if(!isset($objJson->height)) {
				$arrError[] = 'height is missing';
			} else if($objJson->height==='') {
				$arrError[] = 'height is empty';
			} else if(!is_numeric($objJson->height)) {
				$arrError[] = 'invalid height';
			}

			if(!isset($objJson->positionx)) {
				$arrError[] = 'positionx is missing';
			} else if($objJson->positionx==='') {
				$arrError[] = 'positionx is empty';
			} else if(!is_numeric($objJson->positionx)) {
				$arrError[] = 'invalid positionx';
			}

			if(!isset($objJson->positiony)) {
				$arrError[] = 'positiony is missing';
			} else if($objJson->positiony==='') {
				$arrError[] = 'positiony is empty';
			} else if(!is_numeric($objJson->positiony)) {
				$arrError[] = 'invalid positiony';
			}

			if(!isset($objJson->stackingposition)) {
				$arrError[] = 'stackingposition is missing';
			} else if($objJson->stackingposition==='') {
				$arrError[] = 'stackingposition is empty';
			} else if(!is_numeric($objJson->stackingposition)) {
				$arrError[] = 'invalid stackingposition';
			}

			if(!isset($objJson->accept_content)) {
				$arrError[] = 'accept_content is missing';
			} else if($objJson->accept_content==='') {
				$arrError[] = 'accept_content is empty';
			} else if(!is_numeric($objJson->accept_content)) {
				$arrError[] = 'invalid accept_content';
			}

			if(!isset($objJson->fontsize)) {
				$arrError[] = 'fontsize is missing';
			} else if($objJson->fontsize==='') {
				$arrError[] = 'fontsize is empty';
			} else if(!is_numeric($objJson->fontsize)) {
				$arrError[] = 'invalid fontsize';
			}

			if(!isset($objJson->content)) {
				$arrError[] = 'content is missing';
			}

			if(!isset($objJson->fontfamily)) {
				$arrError[] = 'fontfamily is missing';
			} else if(empty($objJson->fontfamily)) {
				$arrError[] = 'fontfamily is empty';
			}

			if(!isset($objJson->textalignment)) {
				$arrError[] = 'textalignment is missing';
			} else if(empty($objJson->textalignment)) {
				$arrError[] = 'textalignment is empty';
			} else if(strlen($objJson->textalignment) > 8) {
				$arrError[] = 'textalignment can not be more than 8 character';
			}

			if(!isset($objJson->textcolour)) {
				$arrError[] = 'textcolour is missing';
			} else if(empty($objJson->textcolour)) {
				$arrError[] = 'textcolour is empty';
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
				parent::__construct($objJson->article_content_uid, __CLASS__);
				if($this->get_valid()) {
					$this->load();
					$this->set_item_type_uid($objJson->item_type_uid);
					$this->set_content($objJson->content);
					$this->set_rotation($objJson->rotation);
					$this->set_width($objJson->width);
					$this->set_height($objJson->height);
					$this->set_fontfamily($objJson->fontfamily);
					$this->set_fontsize($objJson->fontsize);
					$this->set_textalignment($objJson->textalignment);
					$this->set_textcolour($objJson->textcolour);
					$this->set_positionx($objJson->positionx);
					$this->set_positiony($objJson->positiony);
					$this->set_stackingposition($objJson->stackingposition);
					$this->set_accept_content($objJson->accept_content);
					$this->save();
					return array(
						'status'				=>'success',
						'article_content_uid'	=>$objJson->article_content_uid
					);
				}
			}
		} else {
			return false;
		}
	}


	public function APICreateArticleContent($objJson=null){
		if($objJson!=null) {
			$arrError = array();

			if(!isset($objJson->item_type_uid)) {
				$arrError[] = 'item_type_uid is missing';
			} else if($objJson->item_type_uid==='') {
				$arrError[] = 'item_type_uid is empty';
			} else if(!is_numeric($objJson->item_type_uid)) {
				$arrError[] = 'invalid item_type_uid';
			}

			if(!isset($objJson->article_uid)) {
				$arrError[] = 'article_uid is missing';
			} else if($objJson->article_uid==='') {
				$arrError[] = 'article_uid is empty';
			} else if(!is_numeric($objJson->article_uid)) {
				$arrError[] = 'invalid article_uid';
			}

			if(!isset($objJson->article_page_uid)) {
				$arrError[] = 'article_page_uid is missing';
			} else if($objJson->article_page_uid==='') {
				$arrError[] = 'article_page_uid is empty';
			} else if(!is_numeric($objJson->article_page_uid)) {
				$arrError[] = 'invalid article_page_uid';
			}

			if(!isset($objJson->rotation)) {
				$arrError[] = 'rotation is missing';
			} else if($objJson->rotation==='') {
				$arrError[] = 'rotation is empty';
			} else if(!is_numeric($objJson->rotation)) {
				$arrError[] = 'invalid rotation';
			}

			if(!isset($objJson->width)) {
				$arrError[] = 'width is missing';
			} else if($objJson->width==='') {
				$arrError[] = 'width is empty';
			} else if(!is_numeric($objJson->width)) {
				$arrError[] = 'invalid width';
			}

			if(!isset($objJson->height)) {
				$arrError[] = 'height is missing';
			} else if($objJson->height==='') {
				$arrError[] = 'height is empty';
			} else if(!is_numeric($objJson->height)) {
				$arrError[] = 'invalid height';
			}

			if(!isset($objJson->positionx)) {
				$arrError[] = 'positionx is missing';
			} else if($objJson->positionx==='') {
				$arrError[] = 'positionx is empty';
			} else if(!is_numeric($objJson->positionx)) {
				$arrError[] = 'invalid positionx';
			}

			if(!isset($objJson->positiony)) {
				$arrError[] = 'positiony is missing';
			} else if($objJson->positiony==='') {
				$arrError[] = 'positiony is empty';
			} else if(!is_numeric($objJson->positiony)) {
				$arrError[] = 'invalid positiony';
			}

			if(!isset($objJson->stackingposition)) {
				$arrError[] = 'stackingposition is missing';
			} else if($objJson->stackingposition==='') {
				$arrError[] = 'stackingposition is empty';
			} else if(!is_numeric($objJson->stackingposition)) {
				$arrError[] = 'invalid stackingposition';
			}

			if(!isset($objJson->accept_content)) {
				$arrError[] = 'accept_content is missing';
			} else if($objJson->accept_content==='') {
				$arrError[] = 'accept_content is empty';
			} else if(!is_numeric($objJson->accept_content)) {
				$arrError[] = 'invalid accept_content';
			}

			if(!isset($objJson->fontsize)) {
				$arrError[] = 'fontsize is missing';
			} else if($objJson->fontsize==='') {
				$arrError[] = 'fontsize is empty';
			} else if(!is_numeric($objJson->fontsize)) {
				$arrError[] = 'invalid fontsize';
			}

			if(!isset($objJson->content)) {
				$arrError[] = 'content is missing';
			}

			if(!isset($objJson->fontfamily)) {
				$arrError[] = 'fontfamily is missing';
			} else if(empty($objJson->fontfamily)) {
				$arrError[] = 'fontfamily is empty';
			}

			if(!isset($objJson->textalignment)) {
				$arrError[] = 'textalignment is missing';
			} else if(empty($objJson->textalignment)) {
				$arrError[] = 'textalignment is empty';
			} else if(strlen($objJson->textalignment) > 8) {
				$arrError[] = 'textalignment can not be more than 8 character';
			}

			if(!isset($objJson->textcolour)) {
				$arrError[] = 'textcolour is missing';
			} else if(empty($objJson->textcolour)) {
				$arrError[] = 'textcolour is empty';
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
				$this->set_article_uid($objJson->article_uid);
				$this->set_article_page_uid($objJson->article_page_uid);
				$this->set_item_type_uid($objJson->item_type_uid);
				$this->set_content($objJson->content);
				$this->set_rotation($objJson->rotation);
				$this->set_width($objJson->width);
				$this->set_height($objJson->height);
				$this->set_fontfamily($objJson->fontfamily);
				$this->set_fontsize($objJson->fontsize);
				$this->set_textalignment($objJson->textalignment);
				$this->set_textcolour($objJson->textcolour);
				$this->set_positionx($objJson->positionx);
				$this->set_positiony($objJson->positiony);
				$this->set_stackingposition($objJson->stackingposition);
				$this->set_accept_content($objJson->accept_content);
				$article_content_uid = $this->insert();
				return array(
					'status'				=>'success',
					'article_content_uid'	=>$article_content_uid
				);
			}
		} else {
			return false;
		}
	}

	public function AddArticleContent($arrData=array(),$article_uid=null,$article_page_uid=null) {
		$article_content_uid = false;
		if(count($arrData) && $article_uid!=null && $article_page_uid!=null) {
			$this->set_article_uid($article_uid);
			$this->set_article_page_uid($article_page_uid);
			$this->set_template_content_uid($arrData['uid']);
			$this->set_item_type_uid($arrData['item_type_uid']);
			$this->set_content($arrData['content']);
			$this->set_rotation($arrData['rotation']);
			$this->set_width($arrData['width']);
			$this->set_height($arrData['height']);
			$this->set_fontfamily($arrData['fontfamily']);
			$this->set_fontsize($arrData['fontsize']);
			$this->set_textalignment($arrData['textalignment']);
			$this->set_textcolour($arrData['textcolour']);
			$this->set_positionx($arrData['positionx']);
			$this->set_positiony($arrData['positiony']);
			$this->set_stackingposition($arrData['stackingposition']);
			$article_content_uid = $this->insert();
		}
		return $article_content_uid;
	}

	public function getArticlePageUid($article_content_uid=null) {
		if($article_content_uid==null || !is_numeric($article_content_uid)) {
			return 0;
		} else {
			$query ="SELECT ";
			$query.="`article_page_uid` ";
			$query.="FROM ";
			$query.="`article_content` ";
			$query.="WHERE ";
			$query.="`uid`='".$article_content_uid."' ";
			$query.="LIMIT 0,1 ";
			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				$row = mysql_fetch_array($result);
				return $row['article_page_uid'];
			} else {
				return 0;
			}
		}

	}

	public function APIdeleteArticleContent($article_content_uid=null) {
		parent::__construct($article_content_uid, __CLASS__);
		if($this->get_valid()) {
			$this->load();
			$query ="DELETE ";
			$query.="FROM ";
			$query.="`article_content_translations` ";
			$query.="WHERE ";
			$query.="`article_content_uid`='".$article_content_uid."'";
			database::query($query);
			$this->delete();
			return array('status'=>'success');
		} else {
			return array(
				'status'	=>'fail',
				'message'	=>'article_content_uid is not valid'
			);
		}
	}
}
?>