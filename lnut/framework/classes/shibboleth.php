<?php

/*
 * Shibboleth.php
 */

class Shibboleth {
	public $shibboleth_uid = null;
	public function __construct() {

	}

	public function Init() {
		//if(!isset($_SESSION['user']) && isset($_SERVER['persistent-id']) && !isset($_SESSION['shibboleth_logout'])) {
		if(!isset($_SESSION['user']) && isset($_SERVER['persistent-id'])) {

			// check user exist with this shibboleth `persistent-id` var
			$arrExplode = explode('!',$_SERVER['persistent-id']);
			if(is_array($arrExplode) && count($arrExplode)==3) {
				$arrExplodeLast = explode('@',$arrExplode[2]);
				if(is_array($arrExplodeLast) && count($arrExplodeLast)==2) {
					$this->shibboleth_uid = $arrExplodeLast[0];
					if($user_uid=$this->isShibbolethUserExist()) {
						if(is_numeric($user_uid) && $user_uid > 0) {
						$objUser = new user($user_uid);
						$objUser->load();
						$error = false;
						if($objUser->get_access_allowed() == 0) {
							$error = true;
						}
						if($objUser->get_deleted()	== 1) {
							$error = true;
						}
						if($objUser->get_is_admin() ==	0){
							if($objUser->has_active_subscription()	== false) {
							$error = true;
							}
						}
						if($error===false) {
							$objUser->login();
							/*
							$redirectTo = $objUser->login(true);
							output::redirect(config::url($redirectTo));
							*/
						} else {
							
						}
		}
					}
				}
			}
		}
	}

	private function isShibbolethUserExist() {
		if($this->shibboleth_uid!=null) {
			$query ="SELECT ";
			$query.="`uid` ";
			$query.="FROM ";
			$query.="`user` ";
			$query.="WHERE ";
			$query.="`shibboleth_uid` = '".mysql_real_escape_string($this->shibboleth_uid)."' ";
			$query.="LIMIT 0,1";
			$result=database::query($query);
			if(mysql_error() == '' && mysql_num_rows($result)) {
				$row = mysql_fetch_array($result);
				return $row['uid'];
			}
			return false;
		}
		return false;
	}

	public function updateUserWithShibbolethId() {
		if(isset($_SESSION['user']['uid']) && isset($_SERVER['persistent-id'])) {
			// check user exist with this shibboleth `persistent-id` var
			$arrExplode = explode('!',$_SERVER['persistent-id']);
			if(is_array($arrExplode) && count($arrExplode)==3) {
				$arrExplodeLast = explode('@',$arrExplode[2]);
				if(is_array($arrExplodeLast) && count($arrExplodeLast)==2) {
					$this->shibboleth_uid = $arrExplodeLast[0];
					if($this->isShibbolethUserExist()===false) {
						$query ="UPDATE ";
						$query.="`user` ";
						$query.="SET ";
						$query.="`shibboleth_uid` = '".$this->shibboleth_uid."' ";
						$query.="WHERE ";
						$query.="`uid`='".$_SESSION['user']['uid']."'";
						$result = database::query($query);
					}
				}
			}
		}
	}

}

?>