<?php

class landing_cms_menu_header extends generic_object {

	public $arrForm = array();

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}
	
	public function get_menu_header_by_cms_uid($landing_cms_uid=null) {
		$arrMenuHeader = array();
		if($landing_cms_uid!=null) {
			$query ="SELECT ";
			$query.="`uid`, ";
			$query.="`header_text` ";
			$query.="FROM ";
			$query.="`landing_cms_menu_header` ";
			$query.="WHERE ";
			$query.="`landing_cms_uid`='".$landing_cms_uid."' ";
			$query.="ORDER BY `uid`";
			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				while($arrRow = mysql_fetch_array($result)) {
					$arrMenuHeader[] =$arrRow;
				}
			}
		}
		return $arrMenuHeader;
	}

	public function save_menu_header($landing_cms_uid=null) {
		if($landing_cms_uid!=null && is_numeric($landing_cms_uid) && isset($_POST['header_text'])) {
			$this->delete_all_current_menu_header_by_cms_uid($landing_cms_uid);
			$obj_landing_cms_menu_item = new landing_cms_menu_item();
			$obj_landing_cms_menu_item->delete_all_current_menu_item_by_cms_uid($landing_cms_uid);
			foreach($_POST['header_text'] as $index => $value) {
				if(trim($value)!='') {
					$this->set_landing_cms_uid($landing_cms_uid);
					$this->set_header_text(mysql_real_escape_string($value));
					$landing_cms_menu_header_uid = $this->insert();
					$obj_landing_cms_menu_item->save_menu_header_item(
						$landing_cms_menu_header_uid,
						$landing_cms_uid,
						$index
					);
				}
			}
		}
		return true;
	}

	public function delete_all_current_menu_header_by_cms_uid($landing_cms_uid=null) {
		if($landing_cms_uid!=null && is_numeric($landing_cms_uid)) {
			$query ="DELETE ";
			$query.="FROM ";
			$query.="`landing_cms_menu_header` ";
			$query.="WHERE ";
			$query.="`landing_cms_uid`='".$landing_cms_uid."' ";
			$result = database::query($query);
		}
	}
}

?>