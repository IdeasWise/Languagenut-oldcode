<?php

class article_category extends generic_object {

	public $arrForm = array();

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function getList($all = false) {

		if (!$all) {
			$query = "SELECT ";
			$query.="count(`uid`) ";
			$query.="FROM ";
			$query.="`article_category` ";
			$this->setPagination($query);
		}
		$query = "SELECT ";
		$query.="* ";
		$query.="FROM ";
		$query.="`article_category` ";
		$query.="ORDER BY ";
		$query.="`name` ";
		if ($all == false) {
			$query.= "LIMIT " . $this->get_limit();
		}
		return database::arrQuery($query);
	}

	public function isValidCreate() {

		if ($this->isValidateFormData() === true) {
			$package_uid = $this->insert();
			return true;
		} else {
			return false;
		}
	}

	public function isValidUpdate() {

		if ($this->isValidateFormData() === true) {
			$this->save();
			return true;
		} else {
			return false;
		}
	}

	private function isValidateFormData() {
		if (isset($_POST['uid']) && is_numeric($_POST['uid']) && $_POST['uid'] > 0) {
			parent::__construct($_POST['uid'], __CLASS__);
			$this->load();
		} else {
			$this->set_created_date(date('Y-m-d H:i:s'));
		}
		$arrFields = array(
			'name' => array(
				'value' => (isset($_POST['name'])) ? trim($_POST['name']) : '',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 5,
				'maxChar' => 260,
				'errMinMax' => 'Name must be 5 to 255 characters in length.',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid Name.',
				'errIndex' => 'error.name'
			)
		);
		// $arrFields contains array for fields which needs to be validate and then we are passing class object($this)
		if ($this->isValidarrFields($arrFields, $this) === true) {
			$this->set_name($arrFields['name']['value']);
			return true;
		} else {
			return false;
		}
	}

	public function getCategoryListBox($name='article_category_uid', $selected_value=null) {
		$query = "SELECT ";
		$query.="`uid`, ";
		$query.="`name` ";
		$query.="FROM ";
		$query.="`article_category` ";
		$query.="ORDER BY `name`";
		$result = database::query($query);
		$arrTemplate = array();
		$arrTemplate[0] = 'Article Category';
		if (mysql_error() == '' && mysql_num_rows($result)) {
			while ($row = mysql_fetch_array($result)) {
				$arrTemplate[$row['uid']] = $row['name'];
			}
		}
		return format::to_select(
				array(
			"name" => $name,
			"id" => $name,
			"style" => "width:180px;",
			"options_only" => false
				), $arrTemplate, $selected_value
		);
	}

}

?>