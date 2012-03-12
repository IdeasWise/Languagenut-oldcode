<?php

class users extends Controller {

	private $token = 'list';
	private $arrTokens = array(
		'list',
		'edit',
		'add',
		'delete'
	);

	private $parts = array();

	public function __construct() {
		parent::__construct();
		$this->parts = config::get('paths');
		if (isset($this->parts[2]) && in_array($this->parts[2], $this->arrTokens)) {
			$this->token = str_replace(array('user', '-'), array('', ''), $this->parts[2]);
		}
		if (in_array($this->token, $this->arrTokens)) {
			$method = 'do' . ucfirst($this->token);
			$this->$method();
		}
		
	}

	protected function doDelete() {
		if (isset($this->parts[3]) && is_numeric($this->parts[3])) {
			$objUser = new user($this->parts[3]);
			$objUser->load();
			$objUser->set_deleted(1);
			$objUser->save();
			$objUser->redirectToDynamic('/barcode-users/list/'); // redirect to user list
		}
	}

	protected function doAdd() {
		$skeleton = config::getUserSkeleton();
		$body = make::tpl('body.admin.barcode.users.add');
		if (isset($_POST['submit'])) {
			$objUser = new user();
			if(($response=$objUser->AddEditBarcodeUser())===true) {
				output::redirect(config::url('admin/barcode-users/list/'));
			} else {
				$body->assign($objUser->arrForm);
			}
		}
		$skeleton->assign(
				array(
					'body' => $body
				)
		);
		output::as_html($skeleton, true);
	}

	protected function doEdit() {
		$uid= (isset($this->parts[3]) && (int)$this->parts[3] > 0) ? $this->parts[3] : 0;
		if(is_numeric($uid) && $uid > 0){
			$objUser = new user($uid);
			if($objUser->get_valid()) {
				$objUser->load();
				$skeleton = config::getUserSkeleton();
				$body = make::tpl('body.admin.barcode.users.edit');
				if (isset($_POST['submit'])) {
					$objUser = new user();
					if(($response=$objUser->AddEditBarcodeUser())===true) {
						if(!isset($_SESSION['success_message'])) {
							$_SESSION['success_message'] = component_message::success('Record has been updated successfully.');
						}
						output::redirect(config::url('admin/barcode-users/edit/'.$uid));
					} else {
						$body->assign($objUser->arrForm);
					}
				}
				$body->assign(
					array(
						'uid'					=>$objUser->get_uid(),
						'barcode_username'		=>$objUser->get_barcode_username(),
						'barcode_ip_address'	=>$objUser->get_barcode_ip_address(),
						'success_message'	=> (isset($_SESSION['success_message']))?$_SESSION['success_message']:''
					)
				);
				if(isset($_SESSION['success_message'])) {
					unset($_SESSION['success_message']);
				}
				$skeleton->assign(
						array(
							'body' => $body
						)
				);
				output::as_html($skeleton, true);
			} else {
				output::redirect(config::url('admin/barcode-users/list/'));
			}
		} else {
			output::redirect(config::url('admin/barcode-users/list/'));
		}
	}

	protected function doList() {
		$skeleton = config::getUserSkeleton();
		$arrBody = array();

		$objUser = new user();
		$arrUsers = $objUser->get_barcode_users();
		$arrRows = array();
		$page_display_title="";
		$page_navigation="";
		if (!empty($arrUsers)) {
			foreach ($arrUsers as $uid => $data) {
				$panel = make::tpl('body.admin.barcode.users.row');
				$panel->assign($data);
				$arrRows[] = $panel->get_content();
			}

			$page_display_title = $objUser->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');

			$page_navigation = $objUser->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>');
			$page_navigation .= $objUser->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>', ' &raquo ');
			$page_navigation .= $objUser->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');
		}


		$body = make::tpl('body.admin.barcode.users.list');
		$body->assign('page.display.title', $page_display_title);
		$body->assign('page.navigation', $page_navigation);
		$body->assign('users.rows', implode('', $arrRows));

		$skeleton->assign(
				array(
					'body' => $body
				)
		);
		output::as_html($skeleton, true);
	}

	private function getSortinQueryString() {
		$queryString = '';
		if (isset($this->parts[3]) && language::CheckLocale($this->parts[3], false) != false) {
			$queryString .= $this->parts[3] . '/';
		}
		$queryString .='?';
		if (isset($_GET['find'])) {
			$queryString .= "find=" . $_GET['find'] . "&";
		}
		$arrSort = array(
			'sort_email' => 'email',
			'sort_registered_dts' => 'registered_dts',
			'sort_school' => 'school',
			'sort_username_open' => 'username_open'
		);
		foreach ($arrSort as $index => $value) {
			$order = 'asc';
			if (isset($_GET['column']) && $_GET['column'] == $value && isset($_GET['order']) && $_GET['order'] == 'asc') {
				$order = 'desc';
			}
			$arrSort[$index] = $queryString . "column=" . $value . "&order=" . $order;
		}
		return $arrSort;
	}


}

?>