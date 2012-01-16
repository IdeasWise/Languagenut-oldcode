<?php

class landing_cms_menu_item extends generic_object {

	public $arrForm = array();

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}
	
	public function get_menu_item_by_header_uid_and_cms_uid($landing_cms_menu_header_uid=null,$landing_cms_uid=null) {
		$arrMenuHeaderItem = array();
		if($landing_cms_menu_header_uid!=null && $landing_cms_uid!=null) {
			$query ="SELECT ";
			$query.="`uid`, ";
			$query.="`menu_name`, ";
			$query.="`menu_url` ";
			$query.="FROM ";
			$query.="`landing_cms_menu_item` ";
			$query.="WHERE ";
			$query.="`landing_cms_menu_header_uid`='".$landing_cms_menu_header_uid."' ";
			$query.="AND ";
			$query.="`landing_cms_uid`='".$landing_cms_uid."' ";
			$query.="ORDER BY `uid`";
			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				while($arrRow = mysql_fetch_array($result)) {
					$arrMenuHeaderItem[] =$arrRow;
				}
			}
		}
		return $arrMenuHeaderItem;
	}

	public function save_menu_header_item($landing_cms_menu_header_uid=null,$landing_cms_uid=null,$index=null) {
		if($landing_cms_menu_header_uid!=null && is_numeric($landing_cms_menu_header_uid) && $landing_cms_uid!=null && is_numeric($landing_cms_uid) && isset($_POST['menu_name'][$index])) {
			foreach($_POST['menu_name'][$index] as $menu_index => $menu_name) {
				$menu_url = (isset($_POST['menu_url'][$index][$menu_index])?$_POST['menu_url'][$index][$menu_index]:'');
				if(!empty($menu_name) && !empty($menu_url)) {
					$this->set_landing_cms_uid($landing_cms_uid);
					$this->set_landing_cms_menu_header_uid($landing_cms_menu_header_uid);
					$this->set_menu_name(mysql_real_escape_string($menu_name));
					$this->set_menu_url(mysql_real_escape_string($menu_url));
					$this->insert();
				}
			}
		}
		return true;
	}

	public function delete_all_current_menu_item_by_cms_uid($landing_cms_uid=null) {
		if($landing_cms_uid!=null && is_numeric($landing_cms_uid)) {
			$query ="DELETE ";
			$query.="FROM ";
			$query.="`landing_cms_menu_item` ";
			$query.="WHERE ";
			$query.="`landing_cms_uid`='".$landing_cms_uid."' ";
			$result = database::query($query);
		}
	}
}

?>