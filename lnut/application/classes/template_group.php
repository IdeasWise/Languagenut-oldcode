<?php

class template_group extends generic_object {

	public $arrForm = array( );
	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function getList($template_uid=null,$all=false) {
		
		$where ="WHERE ";
		$where.="`TG`.`template_uid` = `T`.`uid` ";
		$where.="AND ";
		$where.="`TG`.`template_uid` = '".$template_uid."' ";
		if(!$all) {
			$query ="SELECT ";
			$query.="count(`TG`.`uid`) ";
			$query.="FROM ";
			$query.="`template_group` AS `TG`, ";
			$query.="`template` AS `T` ";
			$query.=$where;
			$this->setPagination( $query );
		}
		$query ="SELECT ";
		$query.="`TG`.`uid`, ";
		$query.="`TG`.`template_uid`, ";
		$query.="`TG`.`created_date` ";
		$query.="FROM ";
		$query.="`template_group` AS `TG`, ";
		$query.="`template` AS `T` ";
		$query.=$where;
		$query.="ORDER BY `TG`.`uid` DESC ";
		if(!$all) {
			$query.= "LIMIT ".$this->get_limit();
		}
		return database::arrQuery($query);
	}

	public function APICreateGroup($objJson=null){
		if($objJson!=null) {
			$arrError = array();
			if(!isset($objJson->name)) {
				$arrError[] = 'name is missing';
			} else if(empty($objJson->name)) {
				$arrError[] = 'name is missing';
			}

			if(!isset($objJson->template_uid)) {
				$arrError[] = 'template_uid is missing';
			} else if($objJson->template_uid==='' && $objJson->template_uid=='0') {
				$arrError[] = 'template_uid is missing';
			} else if(!is_numeric($objJson->template_uid)) {
				$arrError[] = 'invalid template_uid';
			}

			if(!isset($objJson->content_uid_list) && !is_array($objJson->content_uid_list)) {
				$arrError[] = 'content_uid_list is missing';
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
				$this->set_template_uid($objJson->template_uid);
				$this->set_created_date(date('Y-m-d H:i:s'));
				$template_group_uid = $this->insert();
				if(is_array($objJson->content_uid_list) && count($objJson->content_uid_list)) {
					$objTemplateGroupContent = new template_group_content();
					foreach($objJson->content_uid_list as $template_content_uid) {
						$objTemplateGroupContent->set_template_uid($objJson->template_uid);
						$objTemplateGroupContent->set_template_group_uid($template_group_uid);
						$objTemplateGroupContent->set_template_content_uid($template_content_uid);
						$objTemplateGroupContent->insert();
					}
				}
				return array(
					'status'			=>'success',
					'template_group_uid'=>$template_group_uid
				);
			}
		} else {
			return false;
		}
	}


	public function APIUpdateGroup($objJson=null){
		if($objJson!=null) {
			$arrError = array();
			if(!isset($objJson->name)) {
				$arrError[] = 'name is missing';
			} else if(empty($objJson->name)) {
				$arrError[] = 'name is missing';
			}

			if(!isset($objJson->template_uid)) {
				$arrError[] = 'template_uid is missing';
			} else if($objJson->template_uid==='' && $objJson->template_uid=='0') {
				$arrError[] = 'template_uid is missing';
			} else if(!is_numeric($objJson->template_uid)) {
				$arrError[] = 'invalid template_uid';
			}

			if(!isset($objJson->template_group_uid)) {
				$arrError[] = 'template_group_uid is missing';
			} else if($objJson->template_group_uid==='' && $objJson->template_group_uid=='0') {
				$arrError[] = 'template_group_uid is missing';
			} else if(!is_numeric($objJson->template_group_uid)) {
				$arrError[] = 'invalid template_group_uid';
			}

			if(!isset($objJson->content_uid_list) && !is_array($objJson->content_uid_list)) {
				$arrError[] = 'content_uid_list is missing';
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
				parent::__construct($objJson->template_group_uid, __CLASS__);
				if($this->get_valid()) {
					$this->load();
					$template_group_uid = $objJson->template_group_uid;
					$this->set_name($objJson->name);
					$this->set_template_uid($objJson->template_uid);
					$this->set_created_date(date('Y-m-d H:i:s'));
					$this->save();
					if(is_array($objJson->content_uid_list) && count($objJson->content_uid_list)) {

						$query ="DELETE FROM `template_group_content` WHERE `template_group_uid` = '".$template_group_uid."'";
						database::query($query);

						$objTemplateGroupContent = new template_group_content();
						foreach($objJson->content_uid_list as $template_content_uid) {
							$objTemplateGroupContent->set_template_uid($objJson->template_uid);
							$objTemplateGroupContent->set_template_group_uid($template_group_uid);
							$objTemplateGroupContent->set_template_content_uid($template_content_uid);
							$objTemplateGroupContent->insert();
						}
					}
					return array(
						'status'			=>'success',
						'template_group_uid'=>$template_group_uid
					);
				} else {
					return array(
						'status'	=>'fail',
						'message'	=>'template_group_uid is not valid.'
					);
				}
			}
		} else {
			return false;
		}
	}

	public function APIDelete($template_group_uid=null) {
		if($template_group_uid!=null) {
			parent::__construct($template_group_uid, __CLASS__);
			$this->load();
			$query ="DELETE FROM `template_group_content` WHERE `template_group_uid` = '".$template_group_uid."'";
						database::query($query);
			$this->delete();
		}
	}
}
?>