<?php

class template_content extends generic_object {

	public $arrForm = array( );
	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function getList($template_uid=null,$all=false) {
		
		$where ="WHERE ";
		$where.="`TC`.`template_uid` = `T`.`uid` ";
		$where.="AND ";
		$where.="`TC`.`item_type_uid` = `TIT`.`uid` ";
		$where.="AND ";
		$where.="`TC`.`template_uid` = '".$template_uid."' ";
		$where.=" GROUP BY `TC`.`uid` ";
		if(!$all) {
			$query ="SELECT ";
			$query.="count(`TC`.`uid`) ";
			$query.="FROM ";
			$query.="`template_content` AS `TC`, ";
			$query.="`template` AS `T`, ";
			$query.="`template_item_type` AS `TIT` ";
			$query.=$where;
			$this->setPagination( $query );
		}
		$query ="SELECT ";
		$query.="`TC`.`uid`, ";
		$query.="`TC`.`template_uid`, ";
		$query.="`TIT`.`name`, ";
		$query.="`TC`.`content` ";
		$query.="FROM ";
		$query.="`template_content` AS `TC`, ";
		$query.="`template` AS `T`, ";
		$query.="`template_item_type` AS `TIT` ";
		$query.=$where;
		$query.="ORDER BY `TC`.`uid` DESC ";
		if(!$all) {
			$query.= "LIMIT ".$this->get_limit();
		}
		return database::arrQuery($query);
	}

	public function isValidCreate () {
		
		if($this->isValidateFormData() === true) {
			$template_content_uid = $this->insert();
			if(isset($_POST['copy_to_translation']) && $_POST['copy_to_translation'] == 1) {
				$objTemplateContentTranslations = new template_content_translations();
				$objTemplateContentTranslations->CopyContentToContentTranslation($_POST['template_uid'],$template_content_uid);
			}
			//$objTemplateTranslation->SaveTemplateTranslation($template_uid);
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
				'value'			=> (isset($_POST['item_type_uid']))?trim($_POST['item_type_uid']):1,
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
			$this->set_template_uid($_POST['template_uid']);
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
			return true;
		} else {
			return false;
		}

	}


	public function APIAddTemplateContent($objJson=null){
		if($objJson!=null) {
			$arrError = array();

			if(!isset($objJson->template_uid)) {
				$arrError[] = 'template_uid is missing';
			} else if(empty($objJson->template_uid)) {
				$arrError[] = 'template_uid is empty';
			} else if(!is_numeric($objJson->template_uid)) {
				$arrError[] = 'invalid template_uid';
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
				$this->set_template_uid($objJson->template_uid);
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
				$template_content_uid = $this->insert();
				if(isset($objJson->copy_translation) && $objJson->copy_translation==1) {
					$this->APICopyTemplateContentTranslations($objJson->template_uid,$template_content_uid,$objJson);
				}
				return array(
					'status'=>'success',
					'template_content_uid'=>$template_content_uid
				);
			}
		} else {
			return false;
		}
	}

	public function APICopyTemplateContentTranslations($template_uid=null,$template_content_uid=null,$objJson=null) {
		if($template_uid!=null && $template_content_uid!=null && $objJson!=null ) {
			$query = "SELECT ";
			$query.="`uid` ";
			$query.="FROM ";
			$query.="`template_translation` ";
			$query.="WHERE ";
			$query.="`template_uid` = '".$template_uid."' ";
			$query.="AND ";
			$query.="`locked`='0'";
			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				$objTemplateContentTranslation = new template_content_translations();
				while($row=mysql_fetch_array($result)) {
					$objTemplateContentTranslation->set_template_translation_uid($row['uid']);
					$objTemplateContentTranslation->set_template_content_uid($template_content_uid);
					$objTemplateContentTranslation->set_template_uid($objJson->template_uid);
					$objTemplateContentTranslation->set_item_type_uid($objJson->item_type_uid);
					$objTemplateContentTranslation->set_content($objJson->content);
					$objTemplateContentTranslation->set_rotation($objJson->rotation);
					$objTemplateContentTranslation->set_width($objJson->width);
					$objTemplateContentTranslation->set_height($objJson->height);
					$objTemplateContentTranslation->set_fontfamily($objJson->fontfamily);
					$objTemplateContentTranslation->set_fontsize($objJson->fontsize);
					$objTemplateContentTranslation->set_textalignment($objJson->textalignment);
					$objTemplateContentTranslation->set_textcolour($objJson->textcolour);
					$objTemplateContentTranslation->set_positionx($objJson->positionx);
					$objTemplateContentTranslation->set_positiony($objJson->positiony);
					$objTemplateContentTranslation->set_stackingposition($objJson->stackingposition);
					$template_content_translation_uid = $objTemplateContentTranslation->insert();
				}
			}
		}
	}


	public function APIUpdateTemplateContent($objJson=null){

		if($objJson!=null) {
			$arrError = array();
			if(!isset($objJson->template_content_uid)) {
				$arrError[] = 'template_content_uid is missing';
			} else if(empty($objJson->template_content_uid)) {
				$arrError[] = 'template_content_uid is empty';
			} else if(!is_numeric($objJson->template_content_uid)) {
				$arrError[] = 'invalid template_content_uid';
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
				parent::__construct($objJson->template_content_uid, __CLASS__);
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
					$this->save();
					return array(
						'status'=>'success',
						'template_content_uid'=>$this->get_uid()
					);
				}
			}
		} else {
			return array(
				'status'=>'fail',
				'template_content_uid'=>$objJson->template_content_uid
			);
		}
	}

	public function APIdeleteTemplateContent($template_content_uid=null) {
		parent::__construct($template_content_uid, __CLASS__);
		if($this->get_valid()) {
			$this->load();
			$query ="DELETE ";
			$query.="FROM ";
			$query.="`template_content_translations` ";
			$query.="WHERE ";
			$query.="`template_content_uid`='".$template_content_uid."'";
			database::query($query);
			$this->delete();
			return array('status'=>'success');
		} else {
			return array(
				'status'	=>'fail',
				'message'	=>'template_content_uid is not valid'
			);
		}
	}

}
?>