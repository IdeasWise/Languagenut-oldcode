<?php

class template_content_translations extends generic_object {

	public $arrForm = array( );
	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function getList($template_translation_uid=null,$all=false) {
		
		$where ="WHERE ";
		$where.="`TCT`.`item_type_uid` = `TIT`.`uid` ";
		$where.="AND ";
		$where.="`TCT`.`template_translation_uid` = '".$template_translation_uid."' ";
		$where.=" GROUP BY `TCT`.`uid` ";
		if(!$all) {
			$query ="SELECT ";
			$query.="count(`TCT`.`uid`) ";
			$query.="FROM ";
			$query.="`template_content_translations` AS `TCT`, ";
			$query.="`template` AS `T`, ";
			$query.="`template_item_type` AS `TIT` ";
			$query.=$where;
			$this->setPagination( $query );
		}
		$query ="SELECT ";
		$query.="`TCT`.`uid`, ";
		$query.="`TCT`.`template_translation_uid`, ";
		$query.="`TIT`.`name`, ";
		$query.="`TCT`.`content` ";
		$query.="FROM ";
		$query.="`template_content_translations` AS `TCT`, ";
		$query.="`template` AS `T`, ";
		$query.="`template_item_type` AS `TIT` ";
		$query.=$where;
		$query.="ORDER BY `TCT`.`uid` DESC ";
		if(!$all) {
			$query.= "LIMIT ".$this->get_limit();
		}
		return database::arrQuery($query);
	}

	public function CopyContentToContentTranslation($template_uid=null,$template_content_uid=null) {
		if($template_uid!=null && $template_content_uid!=null) {
			$query ="SELECT ";
			$query.="`uid` ";
			$query.="FROM ";
			$query.="`template_translation` ";
			$query.="WHERE ";
			$query.="`template_uid` = '".$template_uid."'";
			$result = database::query($query);
			if(mysql_error() == '' && mysql_num_rows($result)) {
				while($row = mysql_fetch_array($result)) {
					$this->set_template_uid(mysql_real_escape_string($template_uid));
					$this->set_item_type_uid(mysql_real_escape_string($_POST['item_type_uid']));
					$this->set_content(mysql_real_escape_string($_POST['content']));
					$this->set_rotation(mysql_real_escape_string($_POST['rotation']));
					$this->set_width(mysql_real_escape_string($_POST['width']));
					$this->set_height(mysql_real_escape_string($_POST['height']));
					$this->set_fontfamily(mysql_real_escape_string($_POST['fontfamily']));
					$this->set_fontsize(mysql_real_escape_string($_POST['fontsize']));
					$this->set_textalignment(mysql_real_escape_string($_POST['textalignment']));
					$this->set_textcolour(mysql_real_escape_string($_POST['textcolour']));
					$this->set_positionx(mysql_real_escape_string($_POST['positionx']));
					$this->set_positiony(mysql_real_escape_string($_POST['positiony']));
					$this->set_stackingposition(mysql_real_escape_string($_POST['stackingposition']));

					$this->set_template_content_uid($template_content_uid);
					$this->set_template_translation_uid($row['uid']);
					$this->insert();
				}
			}

		}
	}


	public function isValidCreate () {
		
		if($this->isValidateFormData() === true) {
			$this->insert();
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
			$this->set_template_translation_uid($_POST['template_translation_uid']);
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

	public function APIAddTemplateContentTranslation($objJson=null){
		if($objJson!=null) {
			$arrError = array();

			if(!isset($objJson->template_uid)) {
				$arrError[] = 'template_uid is missing';
			} else if(empty($objJson->template_uid)) {
				$arrError[] = 'template_uid is empty';
			} else if(!is_numeric($objJson->template_uid)) {
				$arrError[] = 'invalid template_uid';
			}

			if(!isset($objJson->template_translation_uid)) {
				$arrError[] = 'template_translation_uid is missing';
			} else if(empty($objJson->template_uid)) {
				$arrError[] = 'template_translation_uid is empty';
			} else if(!is_numeric($objJson->template_translation_uid)) {
				$arrError[] = 'invalid template_translation_uid';
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
			} else if($objJson->positionx=='') {
				$arrError[] = 'positionx is empty';
			} else if(!is_numeric($objJson->positionx)) {
				$arrError[] = 'invalid positionx';
			}

			if(!isset($objJson->positiony)) {
				$arrError[] = 'positiony is missing';
			} else if($objJson->positiony=='') {
				$arrError[] = 'positiony is empty';
			} else if(!is_numeric($objJson->positiony)) {
				$arrError[] = 'invalid positiony';
			}

			if(!isset($objJson->stackingposition)) {
				$arrError[] = 'stackingposition is missing';
			} else if($objJson->stackingposition=='') {
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

			if(isset($objJson->template_content_uid) && trim($objJson->template_content_uid) == '') {
				$arrError[] = 'template_content_uid is empty';
			} else if(isset($objJson->template_content_uid) && !is_numeric($objJson->template_content_uid)) {
				$arrError[] = 'invalid template_content_uid';
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
				$this->set_template_translation_uid($objJson->template_translation_uid);
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
				if(isset($objJson->template_content_uid)) {
					$this->set_template_content_uid($objJson->template_content_uid);
				}
				$template_content_translation_uid = $this->insert();
				return array(
					'status'=>'success',
					'template_content_translation_uid'=>$template_content_translation_uid
				);
			}
		} else {
			return array(
					'status'=>'fail'
				);
		}
	}


	public function APIEditTemplateContentTranslation($objJson=null){
		if($objJson!=null) {
			$arrError = array();

			if(!isset($objJson->template_content_translation_uid)) {
				$arrError[] = 'template_content_translation_uid is missing';
			} else if(empty($objJson->template_content_translation_uid)) {
				$arrError[] = 'template_content_translation_uid is empty';
			} else if(!is_numeric($objJson->template_content_translation_uid)) {
				$arrError[] = 'invalid template_content_translation_uid';
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
			} else if(empty($objJson->content)) {
				$arrError[] = 'content is empty';
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
				parent::__construct($objJson->template_content_translation_uid, __CLASS__);
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
						'template_content_translation_uid'=>$this->get_uid()
					);
				}
			}
		} else {
			return array(
				'status'=>'fail',
				'template_content_translation_uid'=>$objJson->template_content_translation_uid
			);
		}
	}

	public function APICopyTemplateContent($template_content_uid=null) {
		$arrTemplateContentTranslations = array();
		if($template_content_uid!=null) {
			$objTemplateContent = new template_content($template_content_uid);
			if($objTemplateContent->get_valid()) {
				$objTemplateContent->load();
				$query ="SELECT ";
				$query.="`uid` ";
				$query.="FROM ";
				$query.="`template_translation` ";
				$query.="WHERE ";
				$query.="`template_uid` = '".$objTemplateContent->get_template_uid()."' ";
				$query.="AND ";
				$query.="`locked`='0'";
				$result = database::query($query);
				if(mysql_error()=='' && mysql_num_rows($result)) {
					while($row=mysql_fetch_array($result)) {
						$template_content_translation_uid = $this->checkTranslationIsExist(
							$template_content_uid,
							$row['uid']

						);
						if($template_content_translation_uid!=false) {
							parent::__construct($template_content_translation_uid,__CLASS__);
							$this->load();
						}

						$this->set_template_uid($objTemplateContent->get_template_uid());
						$this->set_item_type_uid($objTemplateContent->get_item_type_uid());
						$this->set_content($objTemplateContent->get_content());
						$this->set_rotation($objTemplateContent->get_rotation());
						$this->set_width($objTemplateContent->get_width());
						$this->set_height($objTemplateContent->get_height());
						$this->set_fontfamily($objTemplateContent->get_fontfamily());
						$this->set_fontsize($objTemplateContent->get_fontsize());
						$this->set_textalignment($objTemplateContent->get_textalignment());
						$this->set_textcolour($objTemplateContent->get_textcolour());
						$this->set_positionx($objTemplateContent->get_positionx());
						$this->set_positiony($objTemplateContent->get_positiony());
						$this->set_stackingposition($objTemplateContent->get_stackingposition());
						$this->set_template_content_uid($template_content_uid);
						$this->set_template_translation_uid($row['uid']);

						if($template_content_translation_uid!=false) {
							$this->save();
						} else {
							$template_content_translation_uid = $this->insert();
						}
						$arrTemplateContentTranslations[] = array(
							'template_content_translation_uid'	=>$template_content_translation_uid
						);
					}
				}
				$arrResponse = array(
					'status'						=>'Sucess',
					'templateContentTranslations'	=>$arrTemplateContentTranslations
				);
				return $arrResponse;
			}
				return array(
					'status'	=>'fail',
					'reason'	=>'template_content_uid does not exist!'
				);
			
		}
		return array(
			'status'	=>'fail',
			'reason'	=>'unknon reason!'
		);
	}

	private function checkTranslationIsExist($template_content_uid=null,$template_translation_uid=null) {
		if($template_content_uid!=null && $template_translation_uid!=null) {
			$query ="SELECT ";
			$query.="`uid` ";
			$query.="FROM ";
			$query.="`template_content_translations` ";
			$query.="WHERE ";
			$query.="`template_content_uid` = '".$template_content_uid."' ";
			$query.="AND ";
			$query.="`template_translation_uid`='".$template_translation_uid."' ";
			$query.="LIMIT 0,1 ";
			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				$row = mysql_fetch_array($result);
				return $row['uid'];
			}
		}
		return false;
	}

}
?>