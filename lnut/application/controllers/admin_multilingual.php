<?php

class admin_multilingual extends Controller {
	private $token		= 'list';
	private $arrTokens	= array(
		'list',
		'edit',
		'add',
		'delete'
	);
	private $paths		= array();

	public function __construct () {
		parent::__construct();
		$this->paths = config::get('paths');
		
		if(isset($this->paths[2]) && in_array($this->paths[2], $this->arrTokens)) {
			$this->token = str_replace('-', '', $this->paths[2]);
		}
		if(in_array($this->token, $this->arrTokens)) {
			$method = 'do' . ucfirst($this->token);
			$this->$method();
		}
	}

	private function doList() {
		$skeleton			= config::getUserSkeleton();
		$body				= make::tpl ('admin.translation.layout');
		$objAdminMessages	= new admin_messages();
		$arrList			= $objAdminMessages->getList();
		$arrRows			= array();
		if(!empty($arrList)) {
			foreach($arrList as $uid=>$data) {
				$arrRows[] = make::tpl ('admin.translation.layout.row')->assign($data)->get_content();
			}
		}

		$page_display_title		= $objAdminMessages->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
		$page_navigation		= $objAdminMessages->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>').$objAdminMessages->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ').$objAdminMessages->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');
		$body->assign('page.display.title'	, $page_display_title);
		$body->assign('page.navigation'		, $page_navigation);
		$body->assign('list.rows'			, implode('',$arrRows));
		
		$skeleton->assign(
			array(
				'body'=>$body
			)
		);
		output::as_html($skeleton,true);
	}

	private function doEdit() {
		if(isset($this->paths[3]) && is_numeric($this->paths[3]) && $this->paths[3] > 0 ) {
			$objAdminMessages = new admin_messages($this->paths[3]);
			$objAdminMessages->load();			
			$skeleton	= config::getUserSkeleton();
			$body		= $objAdminMessages->get_translation_tab($objAdminMessages);
			$skeleton->assign(
				array(
					'body'=>$body
				)
			);
			output::as_html($skeleton,true);
		}
	}

	private function doAdd() {
		$objAdminMessages	= new admin_messages();
		$skeleton			= config::getUserSkeleton();
		$body				= $objAdminMessages->get_translation_addForm();
		$skeleton->assign(
			array(
				'body'=>$body
			)
		);
		output::as_html($skeleton,true);
	}

	private function doDelete() {
		output::redirect(config::url('/admin/multilingual/list/'));
	}
}

?>