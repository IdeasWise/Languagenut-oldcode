<?php

class lingualympics_cms extends generic_object {

	public $arrForm = array();

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function get_content_by_locale($locale=null,$force_en_version=false) {
		$arrResult = false;
		if($locale!=null) {
			$query ="SELECT ";
			$query.="* ";
			$query.="FROM ";
			$query.="`lingualympics_cms` ";
			$query.="WHERE ";
			$query.="`locale` ='".mysql_real_escape_string($locale)."' ";
			$query.="LIMIT 0,1";
			$result = database::query($query);
			if(mysql_error()== '' && mysql_num_rows($result)) {
				$arrResult = mysql_fetch_assoc($result);
			} else if($force_en_version===true) {
				$query ="SELECT ";
				$query.="* ";
				$query.="FROM ";
				$query.="`lingualympics_cms` ";
				$query.="WHERE ";
				$query.="`locale` ='en' ";
				$query.="LIMIT 0,1";
				$result = database::query($query);
				if(mysql_error()== '' && mysql_num_rows($result)) {
					$arrResult = mysql_fetch_assoc($result);
				}
			}
		}
		return $arrResult;
	}

	public function doSave() {
		if($this->isValidateFormData() == true) {
			if (isset($_POST['uid']) && is_numeric($_POST['uid']) && $_POST['uid'] > 0) {
				$this->save();
			} else {
				$insert = $this->insert();
				return $insert;
			}
			return true;
		} else {
			return false;
		}
	}

	private function isValidateFormData() {
		if (isset($_POST['uid']) && is_numeric($_POST['uid']) && $_POST['uid'] > 0) {
			parent::__construct($_POST['uid'], __CLASS__);
			$this->load();
		}
		$arrFields = array(
			'locale' => array(
				'value' => (isset($_POST['locale'])) ? trim($_POST['locale']) : '',
				'checkEmpty' => true,
				'errEmpty' => 'Please select locale!',
				'minChar' => 0,
				'maxChar' => 0,
				'errMinMax' => '',
				'dataType' => 'text',
				'errdataType' => 'Please select valid locale.',
				'errIndex' => 'error.locale'
			),
			'page_title' => array(
				'value' => (isset($_POST['page_title'])) ? trim($_POST['page_title']) : '',
				'checkEmpty' => true,
				'errEmpty' => 'Please enter title.',
				'minChar' => 2,
				'maxChar' => 260,
				'errMinMax' => 'Title must be 2 to 260 characters in length.',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid title.',
				'errIndex' => 'error.page_title'
			),
			'label_school' => array(
				'value' => (isset($_POST['label_school'])) ? trim($_POST['label_school']) : '',
				'checkEmpty' => true,
				'errEmpty' => 'Please enter school label.',
				'minChar' => 2,
				'maxChar' => 100,
				'errMinMax' => 'School lable must be 2 to 100 characters in length.',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid school label.',
				'errIndex' => 'error.label_school'
			),
			'label_student' => array(
				'value' => (isset($_POST['label_student'])) ? trim($_POST['label_student']) : '',
				'checkEmpty' => true,
				'errEmpty' => 'Please enter student label.',
				'minChar' => 2,
				'maxChar' => 150,
				'errMinMax' => 'Student label must be 2 to 150 characters in length.',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid student label.',
				'errIndex' => 'error.label_student'
			),
			'label_country' => array(
				'value' => (isset($_POST['label_country'])) ? trim($_POST['label_country']) : '',
				'checkEmpty' => true,
				'errEmpty' => 'Please enter country label.',
				'minChar' => 2,
				'maxChar' => 100,
				'errMinMax' => 'Country lable must be 2 to 100 characters in length.',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid country label.',
				'errIndex' => 'error.label_country'
			),
			'label_rank' => array(
				'value' => (isset($_POST['label_rank'])) ? trim($_POST['label_rank']) : '',
				'checkEmpty' => true,
				'errEmpty' => 'Please enter rank label.',
				'minChar' => 2,
				'maxChar' => 100,
				'errMinMax' => 'Rank lable must be 2 to 100 characters in length.',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid rank label.',
				'errIndex' => 'error.label_rank'
			),
			'label_name' => array(
				'value' => (isset($_POST['label_name'])) ? trim($_POST['label_name']) : '',
				'checkEmpty' => true,
				'errEmpty' => 'Please enter name label.',
				'minChar' => 2,
				'maxChar' => 100,
				'errMinMax' => 'Name lable must be 2 to 100 characters in length.',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid name label.',
				'errIndex' => 'error.label_name'
			),
			'label_score' => array(
				'value' => (isset($_POST['label_score'])) ? trim($_POST['label_score']) : '',
				'checkEmpty' => true,
				'errEmpty' => 'Please enter score label.',
				'minChar' => 2,
				'maxChar' => 100,
				'errMinMax' => 'Score lable must be 2 to 100 characters in length.',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid score label.',
				'errIndex' => 'error.label_score'
			),
			'content' => array(
				'value' => (isset($_POST['content'])) ? trim($_POST['content']) : '',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 0,
				'maxChar' => 0,
				'errMinMax' => '',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid content.',
				'errIndex' => 'error.content'
			),
			'meta_title' => array(
				'value' => (isset($_POST['meta_title'])) ? trim($_POST['meta_title']) : '',
				'checkEmpty' => true,
				'errEmpty' => 'Please enter meta title.',
				'minChar' => 2,
				'maxChar' => 260,
				'errMinMax' => 'Meta title must be 2 to 260 characters in length.',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid meta title.',
				'errIndex' => 'error.meta_title'
			),
			'meta_keywords' => array(
				'value' => (isset($_POST['meta_keywords'])) ? trim($_POST['meta_keywords']) : '',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 0,
				'maxChar' => 0,
				'errMinMax' => '',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid meta keyword.',
				'errIndex' => 'error.meta_keywords'
			),
			'meta_description' => array(
				'value' => (isset($_POST['meta_description'])) ? trim($_POST['meta_description']) : '',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 0,
				'maxChar' => 0,
				'errMinMax' => '',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid meta description.',
				'errIndex' => 'error.meta_description'
			)
			
		);
		// $arrFields contains array for fields which needs to be validate and then we are passing class object($this)
		if ($this->isValidarrFields($arrFields, $this) === true) {
			$this->set_locale($arrFields['locale']['value']);
			$this->set_page_title($arrFields['page_title']['value']);
			$this->set_label_school($arrFields['label_school']['value']);
			$this->set_label_student($arrFields['label_student']['value']);
			$this->set_label_country($arrFields['label_country']['value']);
			$this->set_label_rank($arrFields['label_rank']['value']);
			$this->set_label_name($arrFields['label_name']['value']);
			$this->set_label_score($arrFields['label_score']['value']);
			$this->set_content(str_replace(array('{{', '}}'),array('&#123;&#123;', '&#125;&#125;'),$arrFields['content']['value']));
			$this->set_meta_title($arrFields['meta_title']['value']);
			$this->set_meta_keywords($arrFields['meta_keywords']['value']);
			$this->set_meta_description($arrFields['meta_description']['value']);
			return true;
		} else {
			return false;
		}
	}
}

?>