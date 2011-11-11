<?php

class template extends generic_object {

	public $arrForm = array( );
	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function getList( $all = false ) {
		
		if(!$all) {
			$query ="SELECT ";
			$query.="count(`uid`) ";
			$query.="FROM ";
			$query.="`template` ";
			$this->setPagination( $query );
		}
		$query ="SELECT ";
		$query.="* ";
		$query.="FROM ";
		$query.="`template` ";
		$query.="ORDER BY ";
		$query.="`name` ";
		if(!$all) {
			$query.= "LIMIT ".$this->get_limit();
		}
		return database::arrQuery($query);
	}

	public function isValidCreate () {
		
		if($this->isValidateFormData() === true) {
			$template_uid = $this->insert();
			$objTemplateTranslation = new template_translation();
			$objTemplateTranslation->SaveTemplateTranslation($template_uid);
			return true;
		} else {
			return false;
		}
		
	}

	public function isValidUpdate () {
		
		if($this->isValidateFormData() === true) {
			$this->save();
			$objTemplateTranslation = new template_translation();
			$objTemplateTranslation->SaveTemplateTranslation($this->get_uid());
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
		$name			= (isset($_POST['name']) ) ? trim($_POST['name']):'';
		$width			= (isset($_POST['width']) && $_POST['width']!='') ? trim($_POST['width']):0;
		$height			= (isset($_POST['height']) && $_POST['height']!='') ? trim($_POST['height']):0;
		$arrMessages	= array();

		if( trim(strlen($name)) < 5 || trim(strlen($name)) > 255 ) {
			$arrMessages['error_name'] = "Name must be 5 to 260 characters in length.";
		} else if(!validation::isValid('text',$name) ) {
			$arrMessages['error_name'] = "Please enter valid name.";
		}
		if(strlen($width)>5) {
			$arrMessages['error_width'] = "Width should not more than 5 digits.";
		} else if (!validation::isValid('int',$width)) {
			$arrMessages['error_width'] = "Please enter valid width.";
		}
		if(strlen($height)>5) {
			$arrMessages['error_height'] = "Height should not more than 5 digits.";
		} else if (!validation::isValid('int',$height)) {
			$arrMessages['error_height'] = "Please enter valid height.";
		}

		$objTemplateTranslation = new template_translation();
		$result = $objTemplateTranslation->isValidInput();
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


		if(count($arrMessages) == 0) {
			$this->set_name($name);
			$this->set_width($width);
			$this->set_height($height);
		} else {

			$strMessage = '';
			foreach( $arrMessages as $index => $value ){
				$this->arrForm[$index] = 'label_error';
				$strMessage .= '<li>'.$value.'</li>';
			}
			$this->arrForm['message'] = '<p>Please correct the errors below:</p><ul>'.$strMessage.'</ul>';

		}

		foreach( $_POST as $index => $value ) {
			if(!is_array($value)) {
				$this->arrForm[$index] = $value;
			}
		}

		if(count($arrMessages) == 0) {
			return true;
		} else {
			return false;
		}

	}

	public function getTemplateSelectBox($name='template_uid',$id='template_uid',$selected_value=null) {
		$query ="SELECT ";
		$query.="`uid`, ";
		$query.="`name` ";
		$query.="FROM ";
		$query.="`template` ";
		$query.="ORDER BY `name`";
		$result = database::query($query);
		$arrTemplate = array();
		$arrTemplate[0] = 'Article Templates';
		if(mysql_error() == '' && mysql_num_rows($result)) {
			while($row=mysql_fetch_array($result)) {
				$arrTemplate[$row['uid']] = $row['name'];
			}
		}
		return format::to_select(
			array(
				"name"			=> $name,
				"id"			=> $id,
				"style"			=> "width:180px;",
				"options_only"	=> false
			),
			$arrTemplate,
			$selected_value
		);
	}

	public function APICreateTemplate($objJson=null){
		if($objJson!=null) {
			$arrError = array();
			if(!isset($objJson->name)) {
				$arrError[] = 'name missing';
			} else if(empty($objJson->name)) {
				$arrError[] = 'name missing';
			}
			if(!isset($objJson->width)) {
				$arrError[] = 'width missing';
			} else if(empty($objJson->width)) {
				$arrError[] = 'width is empty';
			} else if(!is_numeric($objJson->width)) {
				$arrError[] = 'invalid width';
			}
			if(!isset($objJson->height)) {
				$arrError[] = 'height missing';
			} else if(empty($objJson->height)) {
				$arrError[] = 'height is empty';
			} else if(!is_numeric($objJson->height)) {
				$arrError[] = 'invalid height';
			}
			if(isset($objJson->locked) && trim($objJson->locked) == '') {
				$arrError[] = 'locked is empty';
			} else if(isset($objJson->locked) && !is_numeric($objJson->locked)) {
				$arrError[] = 'invalid locked';
			}
			if(isset($objJson->is_suitable_to_article) && trim($objJson->is_suitable_to_article) == '') {
				$arrError[] = 'is_suitable_to_article is empty';
			} else if(isset($objJson->is_suitable_to_article) && !is_numeric($objJson->is_suitable_to_article)) {
				$arrError[] = 'invalid is_suitable_to_article';
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
				$this->set_width($objJson->width);
				$this->set_height($objJson->height);
				if(isset($objJson->locked)) {
					$this->set_locked($objJson->locked);
				} else {
					$this->set_locked(0);
				}
				if(isset($objJson->is_suitable_to_article)) {
					$this->set_is_suitable_to_article($objJson->is_suitable_to_article);
				}
				$template_uid = $this->insert();
				$objTemplateTranslation = new template_translation();
				$objTemplateTranslation->APICreateTemplateTranslations($objJson,$template_uid);
				return $template_uid;
			}
		} else {
			return false;
		}
	}

	public function APIUpdateTemplate($objJson=null){
		if($objJson!=null) {
			$arrError = array();
			if(!isset($objJson->template_uid)) {
				$arrError[] = 'template_uid missing';
			} else if(empty($objJson->template_uid)) {
				$arrError[] = 'template_uid is empty';
			} else if(!is_numeric($objJson->template_uid)) {
				$arrError[] = 'invalid template_uid';
			}
			if(!isset($objJson->name)) {
				$arrError[] = 'name missing';
			} else if(empty($objJson->name)) {
				$arrError[] = 'name missing';
			}
			if(!isset($objJson->width)) {
				$arrError[] = 'width missing';
			} else if(empty($objJson->width)) {
				$arrError[] = 'width is empty';
			} else if(!is_numeric($objJson->width)) {
				$arrError[] = 'invalid width';
			}
			if(!isset($objJson->height)) {
				$arrError[] = 'height missing';
			} else if(empty($objJson->height)) {
				$arrError[] = 'height is empty';
			} else if(!is_numeric($objJson->height)) {
				$arrError[] = 'invalid height';
			}
			if(isset($objJson->locked) && trim($objJson->locked) == '') {
				$arrError[] = 'locked is empty';
			} else if(isset($objJson->locked) && !is_numeric($objJson->locked)) {
				$arrError[] = 'invalid locked';
			}
			if(isset($objJson->is_suitable_to_article) && trim($objJson->is_suitable_to_article) == '') {
				$arrError[] = 'is_suitable_to_article is empty';
			} else if(isset($objJson->is_suitable_to_article) && !is_numeric($objJson->is_suitable_to_article)) {
				$arrError[] = 'invalid is_suitable_to_article';
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
				parent::__construct($objJson->template_uid,__CLASS__);
				$this->load();
				$this->set_name($objJson->name);
				$this->set_width($objJson->width);
				$this->set_height($objJson->height);
				if(isset($objJson->locked)) {
					$this->set_locked($objJson->locked);
				}
				if(isset($objJson->is_suitable_to_article)) {
					$this->set_is_suitable_to_article($objJson->is_suitable_to_article);
				}
				$this->save();
				$objTemplateTranslation = new template_translation();
				$objTemplateTranslation->APICreateTemplateTranslations($objJson,$template_uid);
				return $objJson->template_uid;
			}
		} else {
			return false;
		}
	}

	public function deleteTemplate($template_uid=null) {
		if($template_uid!=null && is_numeric($template_uid)) {
			parent::__construct($template_uid, __CLASS__);
			if($this->get_valid()) {

				if($this->isArticleExistForThisTemplate($template_uid)===false) {
					$query ="DELETE ";
					$query.="FROM ";
					$query.="`template_content` ";
					$query.="WHERE ";
					$query.="`template_uid`='".$template_uid."' ";
					database::query($query);
					if(mysql_error()!='') {
						die(mysql_error());
					}

					$query ="DELETE ";
					$query.="FROM ";
					$query.="`template_content_translations` ";
					$query.="WHERE ";
					$query.="template_uid='".$template_uid."' ";
					database::query($query);
					if(mysql_error()!='') {
						die(mysql_error());
					}

					$query ="DELETE ";
					$query.="FROM ";
					$query.="`template_group` ";
					$query.="WHERE ";
					$query.="`template_uid`='".$template_uid."' ";
					database::query($query);
					if(mysql_error()!='') {
						die(mysql_error());
					}

					$query ="DELETE ";
					$query.="FROM ";
					$query.="`template_group_content` ";
					$query.="WHERE ";
					$query.="`template_uid`='".$template_uid."' ";
					database::query($query);
					if(mysql_error()!='') {
						die(mysql_error());
					}

					$query ="DELETE ";
					$query.="FROM ";
					$query.="`template_translation` ";
					$query.="WHERE ";
					$query.="`template_uid`='".$template_uid."' ";
					database::query($query);
					if(mysql_error()!='') {
						die(mysql_error());
					}
					$this->delete();
					return array(
						'status'=>'success'
					);

				} else {
					return array(
						'status'	=>'false',
						'message'	=>'This template_uid is associated with articles.'
					);
				}
			}
			return array(
				'status'	=>'false',
				'message'	=>'template_uid is not valid.'
			);
		}
	}

	private function isArticleExistForThisTemplate($template_uid=null) {
		if($template_uid!=null && is_numeric($template_uid)) {
			$query = "SELECT ";
			$query.="`uid` ";
			$query.="FROM ";
			$query.="`article` ";
			$query.="WHERE ";
			$query.="`template_uid`='".$template_uid."' ";
			$query.="LIMIT 0,1";
			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				return true;
			}
		}
		return false;
	}
}
?>