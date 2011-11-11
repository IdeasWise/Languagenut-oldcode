<?php

/**
 * api.templates.php
 */

class API_Templates extends Controller {

	public function __construct () {
		parent::__construct();
		$arrPaths = config::get('paths');
		$method = 'getTemplate';
		if(isset($arrPaths[2]) && isset($arrPaths[3]) && !empty($arrPaths[2]) && !empty($arrPaths[3])) {
			$newMethod = $arrPaths[2].ucfirst($arrPaths[3]);
			if(method_exists($this,$newMethod)) {
				$method = $newMethod;
			}
		}
		$this->$method();
	}
	// -> /api/templates/get/template/
	protected function getTemplate($template_uid=null) {
		$query ="SELECT ";
		$query.="`uid`, ";
		$query.="`name`, ";
		$query.="`width`, ";
		$query.="`height`, ";
		$query.="`locked` ";
		$query.="FROM ";
		$query.="`template` ";
		if(isset($_REQUEST['template_uid']) && is_numeric($_REQUEST['template_uid'])) {
			$query.="WHERE ";
			$query.="`uid`='".mysql_real_escape_string($_REQUEST['template_uid'])."' ";
		}
		if($template_uid!=null && is_numeric($template_uid)) {
			$query.="WHERE ";
			$query.="`uid`='".mysql_real_escape_string($template_uid)."' ";
		}
		$query.="ORDER BY `uid`";
		$result = database::query($query);
		$arrTemplates = array();
		if(mysql_error()=='' && mysql_num_rows($result)) {
			while($row=mysql_fetch_array($result)) {
				$arrTemplates[] = array(
					'uid'					=> $row['uid'],
					'name'					=> str_replace('\\','',$row['name']),
					'width'					=> $row['width'],
					'height'				=> $row['height'],
					'locked'				=> $row['locked'],
					'content'				=> $this->getTemplateContent($row['uid']),
					'translations'			=> $this->getTemplateTranslationArray($row['uid']),
					'groups'				=> $this->getTemplateGroups($row['uid'])

				);
			}
		}
		$arrJson = array(
			'template'	=> $arrTemplates
		);
		echo json_encode($arrJson);
	}

	protected function getTemplateTranslationArray($template_uid=null) {
		$arrTranslations = array();
		if($template_uid!=null) {
			$query ="SELECT ";
			$query.="`uid`, ";
			$query.="`language_uid`, ";
			$query.="`name`, ";
			$query.="`width`, ";
			$query.="`height`, ";
			$query.="`locked` ";
			$query.="FROM ";
			$query.="`template_translation` ";
			$query.="WHERE ";
			$query.="`template_uid`='".$template_uid."' ";
			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				while($row=mysql_fetch_array($result)) {
					$arrTranslations[]=array(
						'uid'			=>$row['uid'],
						'name'			=>$row['name'],
						'width'			=>$row['width'],
						'height'		=>$row['height'],
						'locked'		=>$row['locked'],
						'language_uid'	=>$row['language_uid'],
						'content_translation'=>$this->getTemplateContentTranslation($template_uid,$row['uid'])
					);
				}
			}
		}
		return $arrTranslations;
	}

	protected function getTemplateGroups($template_uid=null) {
		$arrTemplateGroups = array();
		if($template_uid!=null) {
			$query ="SELECT ";
			$query.="* ";
			$query.="FROM ";
			$query.="`template_group` ";
			$query.="WHERE ";
			$query.="`template_uid`='".$template_uid."' ";
			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				while($row=mysql_fetch_array($result)) {
					$arrTemplateGroups[]=array(
						'template_group_uid'=>$row['uid'],
						'name'				=>$row['name'],
						'template_uid'		=>$row['template_uid'],
						'content_item_list'	=> $this->getTemplateGroupContent($row['uid'])
					);
				}
			}
		}
		return $arrTemplateGroups;
	}

	private function getTemplateGroupContent($template_group_uid=null) {
		$arrGroupContent = array();
		$query ="SELECT ";
		$query.="`template_content_uid` ";
		$query.="FROM ";
		$query.="`template_group_content` ";
		$query.="WHERE ";
		$query.="`template_group_uid`='".$template_group_uid."' ";
		$query.="ORDER BY ";
		$query.="`template_content_uid`";
		$result = database::query($query);
		if(mysql_error()=='' && mysql_num_rows($result)) {
			while($row=mysql_fetch_array($result)) {
				$arrGroupContent[] = $row['template_content_uid'];
			}
		}
		return $arrGroupContent;
	}

	// -> /api/templates/get/templateContent/?template_uid=VALUE
	protected function getTemplateContent($template_uid=null) {
		if(isset($_REQUEST['template_uid']) && is_numeric($_REQUEST['template_uid']) && $_REQUEST['template_uid'] > 0){
			$template_uid=mysql_real_escape_string($_REQUEST['template_uid']);
		}
		if($template_uid!=null) {
			$arrTemplateContent = array();
			$query ="SELECT ";
			$query.="* ";
			$query.="FROM ";
			$query.="`template_content` ";
			$query.="WHERE ";
			$query.="`template_uid`='".$template_uid."'";
			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				while($row=mysql_fetch_array($result)) {
					$arrTemplateContent[]=array(
						'uid'				=>$row['uid'],
						'template_uid'		=>$row['template_uid'],
						'item_type_uid'		=>$row['item_type_uid'],
						'content'			=>$row['content'],
						'rotation'			=>$row['rotation'],
						'width'				=>$row['width'],
						'height'			=>$row['height'],
						'fontfamily'		=>$row['fontfamily'],
						'fontsize'			=>$row['fontsize'],
						'textalignment'		=>$row['textalignment'],
						'textcolour'		=>$row['textcolour'],
						'positionx'			=>$row['positionx'],
						'positiony'			=>$row['positiony'],
						'stackingposition'	=>$row['stackingposition']
					);
				}
			}
		}
		return $arrTemplateContent;
	}

	
	protected function getTemplateContentTranslation($template_uid=null,$template_translation_uid=null) {
		if($template_uid!=null && $template_translation_uid!=null) {
			$arrTemplateContentTranslation = array();
			$query ="SELECT ";
			$query.="* ";
			$query.="FROM ";
			$query.="`template_content_translations` ";
			$query.="WHERE ";
			$query.="`template_uid`='".mysql_real_escape_string($template_uid)."' ";
			$query.="AND ";
			$query.="`template_translation_uid`='".mysql_real_escape_string($template_translation_uid)."'";

			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				while($row=mysql_fetch_array($result)) {
					$arrTemplateContentTranslation[]=array(
						'uid'				=>$row['uid'],
						'template_content_uid'=>$row['template_content_uid'],
						'template_uid'		=>$row['template_uid'],
						'item_type_uid'		=>$row['item_type_uid'],
						'content'			=>$row['content'],
						'rotation'			=>$row['rotation'],
						'width'				=>$row['width'],
						'height'			=>$row['height'],
						'fontfamily'		=>$row['fontfamily'],
						'fontsize'			=>$row['fontsize'],
						'textalignment'		=>$row['textalignment'],
						'textcolour'		=>$row['textcolour'],
						'positionx'			=>$row['positionx'],
						'positiony'			=>$row['positiony'],
						'stackingposition'	=>$row['stackingposition']
					);
				}
			}
		}
		return $arrTemplateContentTranslation;
	}

	// -> /api/templates/get/templateTranslationAndContentTranslation/?template_uid=VALUE&language_uid=VALUE
	protected function getTemplateTranslationAndContentTranslation() {
		$arrTranslations = array();
		if(isset($_REQUEST['template_uid']) && is_numeric($_REQUEST['template_uid'])) {
			$query ="SELECT ";
			$query.="`uid`, ";
			$query.="`language_uid`, ";
			$query.="`name`, ";
			$query.="`width`, ";
			$query.="`height`, ";
			$query.="`locked` ";
			$query.="FROM ";
			$query.="`template_translation` ";
			$query.="WHERE ";
			$query.="`template_uid`='".mysql_real_escape_string($_REQUEST['template_uid'])."' ";
			if(isset($_REQUEST['language_uid']) && is_numeric($_REQUEST['language_uid'])) {
				$query.="AND ";
				$query.="`language_uid` = '".mysql_real_escape_string($_REQUEST['language_uid'])."'";
			}
			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				while($row=mysql_fetch_array($result)) {
					$arrTranslations[]=array(
						'uid'			=>$row['uid'],
						'name'			=>$row['name'],
						'width'			=>$row['width'],
						'height'		=>$row['height'],
						'locked'		=>$row['locked'],
						'language_uid'	=>$row['language_uid'],
						'content_translation'=>$this->getTemplateContentTranslation($_REQUEST['template_uid'],$row['uid'])
					);
				}
			}
		}
		echo json_encode($arrTranslations);
	}



	// -> /api/templates/get/templateTranslation/
	protected function getTemplateTranslation() {
		$arrTranslations = array();
		if(isset($_REQUEST['template_uid']) && is_numeric($_REQUEST['template_uid']) && isset($_REQUEST['language_uid']) && is_numeric($_REQUEST['language_uid'])) {
			$query ="SELECT ";
			$query.="`uid`, ";
			$query.="`language_uid`, ";
			$query.="`name`, ";
			$query.="`width`, ";
			$query.="`height` ";
			$query.="FROM ";
			$query.="`template_translation` ";
			$query.="WHERE ";
			$query.="`template_uid`='".$_REQUEST['template_uid']."' ";
			$query.="AND ";
			$query.="`language_uid`='".$_REQUEST['language_uid']."' ";
			$arrTranslations = database::arrQuery($query);
		}
		$arrJson = array(
			'template'	=> $arrTranslations
		);
		echo json_encode($arrJson);
	}

	// -> /api/templates/create/template/
	protected function createTemplate() {
		$arrJson = array(
			'name'		=>'template name',
			'width'		=>500,
			'height'	=>500,
			'locked'	=>1
		);
		//echo json_encode($arrJson);
		if(isset($_REQUEST['data']) && !empty($_REQUEST['data'])) {
			$objJson=json_decode($_REQUEST['data']);
			$objTemplate = new template();
			$response = $objTemplate->APICreateTemplate($objJson);
			if(is_array($response)) {
				echo json_encode($response);
			} else if(is_numeric($response) && $response > 0) {
				echo json_encode(array('template_uid'=>$response));
				//$this->getTemplate($response);
			}
		}
	}

// -> /api/templates/update/template/
	protected function updateTemplate() {
		$arrJson = array(
			'name'			=>'kamal joshi',
			'width'			=>500,
			'height'		=>500,
			'locked'		=>1,
			'template_uid'	=>4
		);
		//echo json_encode($arrJson); exit;
		if(isset($_REQUEST['data']) && !empty($_REQUEST['data'])) {
			$objJson=json_decode($_REQUEST['data']);
			$objTemplate = new template();
			$response = $objTemplate->APIUpdateTemplate($objJson);
			if(is_array($response)) {
				echo json_encode($response);
			} else if(is_numeric($response) && $response > 0) {
				echo json_encode(array('template_uid'=>$response));
				//$this->getTemplate($response);
			}
		}
	}

	// -> /api/templates/copy/translations/?template_uid=[VALUE]
	protected function copyTranslations() {
		if(isset($_REQUEST['template_uid']) && is_numeric($_REQUEST['template_uid']) && $_REQUEST['template_uid'] > 0) {
			$objTemplateTranslation = new template_translation();
			$response = $objTemplateTranslation->APICopyTemplateTranslations($_REQUEST['template_uid']);
			echo json_encode($response);
		}
	}

	// -> /api/templates/update/templateTranslations/?data=[VALUE]
	protected function updateTemplateTranslations() {
		$arrJson = array(
			'name'						=>'kamal joshi',
			'width'						=>500,
			'height'					=>500,
			'locked'					=>1,
			'template_translation_uid'	=>81
		);
		if(isset($_REQUEST['data']) && !empty($_REQUEST['data'])) {
			$objJson=json_decode($_REQUEST['data']);
			$objTemplateTranslation = new template_translation();
			$response = $objTemplateTranslation->APIUpdateTemplateTranslation($objJson);
			if(is_array($response)) {
				echo json_encode($response);
			} else if(is_numeric($response) && $response > 0) {
				echo json_encode(array('template_translation_uid'=>$response));
				//$this->getTemplate($response);
			}
		}
		if(isset($_REQUEST['template_translation_uid']) && is_numeric($template_translation_uid['template_translation_uid']) && $_REQUEST['template_uid'] > 0) {
			$objTemplateTranslation = new template_translation();
			$response = $objTemplateTranslation->APICopyTemplateTranslations($_REQUEST['template_uid']);
			echo json_encode($response);
		}
	}

	// -> /api/templates/create/templateContent/?data={json}
	protected function createTemplateContent() {
		$arrJson = array(
			'item_type_uid'		=>1,
			'content'			=>'mystream',
			'rotation'			=>1,
			'width'				=>500,
			'height'			=>500,
			'fontfamily'		=>'arial',
			'fontsize'			=>10,
			'textalignment'		=>'left',
			'textcolour'		=>'000',
			'positionx'			=>1,
			'positiony'			=>1,
			'stackingposition'	=>1,
			'copy_translation'	=>1, // copy_trnaslation 1/0, 1 means copy translations to template_content_translation, this is an optional argument.
			'template_uid'		=>4
		);
		//echo json_encode($arrJson); exit;
		if(isset($_REQUEST['data']) && !empty($_REQUEST['data'])) {
			$objJson=json_decode($_REQUEST['data']);
			$objTemplateContent = new template_content();
			$response = $objTemplateContent->APIAddTemplateContent($objJson);
			if(is_array($response)) {
				echo json_encode($response);
			}
		}
	}
	// -> /api/templates/copy/templateContent/?template_content_uid=XXX
	private function copyTemplateContent() {
		if(isset($_REQUEST['template_content_uid']) && is_numeric($_REQUEST['template_content_uid'])) {
			$objTemplateContentTranslation = new template_content_translations();
			$response = $objTemplateContentTranslation->APICopyTemplateContent($_REQUEST['template_content_uid']);
			echo json_encode($response);
		}
	}

	// -> /api/templates/update/templateContent/?data={json}
	protected function updateTemplateContent() {
		$arrJson = array(
			'item_type_uid'			=>1,
			'content'				=>'mystream',
			'rotation'				=>1,
			'width'					=>500,
			'height'				=>500,
			'fontfamily'			=>'arial',
			'fontsize'				=>10,
			'textalignment'			=>'left',
			'textcolour'			=>'000',
			'positionx'				=>1,
			'positiony'				=>1,
			'stackingposition'		=>1,
			'template_content_uid'	=>6
		);
		// echo json_encode($arrJson); exit;
		if(isset($_REQUEST['data']) && !empty($_REQUEST['data'])) {
			$objJson=json_decode($_REQUEST['data']);
			$objTemplateContent = new template_content();
			$response = $objTemplateContent->APIUpdateTemplateContent($objJson);
			if(is_array($response)) {
				echo json_encode($response);
			}
		}
	}


	// -> /api/templates/create/templateContentTranslation/?data={json}
	protected function createTemplateContentTranslation() {
		$arrJson = array(
			'item_type_uid'				=>1,
			'content'					=>'mystream',
			'rotation'					=>1,
			'width'						=>500,
			'height'					=>500,
			'fontfamily'				=>'arial',
			'fontsize'					=>10,
			'textalignment'				=>'left',
			'textcolour'				=>'000',
			'positionx'					=>1,
			'positiony'					=>1,
			'stackingposition'			=>1,
			'template_uid'				=>6,
			'template_translation_uid'	=>2,
			'template_content_uid'		=>1 // optional
		);
		//echo json_encode($arrJson); exit;
		if(isset($_REQUEST['data']) && !empty($_REQUEST['data'])) {
			$objJson=json_decode($_REQUEST['data']);
			$objTemplateContentTranslation = new template_content_translations();
			$response = $objTemplateContentTranslation->APIAddTemplateContentTranslation($objJson);
			if(is_array($response)) {
				echo json_encode($response);
			}
		}
	}


	// -> /api/templates/update/templateContentTranslation/?data={json}
	protected function updateTemplateContentTranslation() {
		$arrJson = array(
			'item_type_uid'						=>1,
			'content'							=>'Joshi mystream',
			'rotation'							=>1,
			'width'								=>500,
			'height'							=>500,
			'fontfamily'						=>'arial',
			'fontsize'							=>10,
			'textalignment'						=>'left',
			'textcolour'						=>'000',
			'positionx'							=>1,
			'positiony'							=>1,
			'stackingposition'					=>1,
			'template_content_translation_uid'	=>20
		);
		//echo json_encode($arrJson); exit;
		if(isset($_REQUEST['data']) && !empty($_REQUEST['data'])) {
			$objJson=json_decode($_REQUEST['data']);
			$objTemplateContentTranslation = new template_content_translations();
			$response = $objTemplateContentTranslation->APIEditTemplateContentTranslation($objJson);
			if(is_array($response)) {
				echo json_encode($response);
			}
		}
	}

	// -> /api/templates/create/group/
	protected function createGroup() {
		$arrJson = array(
			'name'				=>'group1',
			'template_uid'		=>9,
			'content_uid_list'	=>array(10,12,15)
		);
		if(isset($_REQUEST['data']) && !empty($_REQUEST['data'])) {
			$objJson=json_decode($_REQUEST['data']);
			$objTemplateGroup = new template_group();
			$response = $objTemplateGroup->APICreateGroup($objJson);
			if(is_array($response)) {
				echo json_encode($response);
			}
		}
	}

	// -> /api/templates/update/group/
	protected function updateGroup() {
		$arrJson = array(
			'template_group_uid'=>2,
			'name'				=>'group2',
			'template_uid'		=>9,
			'content_uid_list'	=>array(10,12,15)
		);
//		echo json_encode($arrJson);

		if(isset($_REQUEST['data']) && !empty($_REQUEST['data'])) {
			$objJson=json_decode($_REQUEST['data']);
			$objTemplateGroup = new template_group();
			$response = $objTemplateGroup->APIUpdateGroup($objJson);
			if(is_array($response)) {
				echo json_encode($response);
			}
		}
	}

	// -> /api/templates/delete/group/?template_group_uid=XXX
	protected function deleteGroup() {
		if(isset($_REQUEST['template_group_uid']) && is_numeric($_REQUEST['template_group_uid'])) {
			$objTemplateGroup = new template_group($_REQUEST['template_group_uid']);
			if($objTemplateGroup->get_valid()) {
				$objTemplateGroup->APIDelete($_REQUEST['template_group_uid']);
				echo json_encode(array(
					'status'=>'sucess'
				));
			} else {
				echo json_encode(array(
					'status'	=>'fail',
					'message'	=>'template_group_uid is not valid.'
				));
			}
		}
	}
	// -> /api/templates/delete/template/?template_uid=XXX
	public function deleteTemplate() {
		if(isset($_GET['template_uid']) && is_numeric($_GET['template_uid'])) {
			$objTemplate = new template();
			$response = $objTemplate->deleteTemplate($_GET['template_uid']);
			echo json_encode($response);
		} else {
			echo json_encode(array(
				'status'	=>'false',
				'message'	=>'template_uid is not valid.'
			));
		}
	}
	// -> /api/templates/delete/TemplateContent/?template_content_uid=XXX
	public function deleteTemplateContent() {
		if(isset($_REQUEST['template_content_uid']) && is_numeric($_REQUEST['template_content_uid'])) {
			$objTemplateContent = new template_content();
			$response = $objTemplateContent->APIdeleteTemplateContent($_REQUEST['template_content_uid']);
		}
		echo json_encode($response);
	}
}
?>