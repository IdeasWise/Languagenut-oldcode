<?php

class view_logs extends Controller {

	public function __construct () {
		parent::__construct();
		$this->index();
	}
	
	private function index() {
		$arrPaths = config::get('paths');
		if(isset($arrPaths[2]) && is_numeric($arrPaths[2])) {
			$objLoginAccess	= new logging_access();
			$arrList		= $objLoginAccess->getList($arrPaths[2]);
			$skeleton		= config::getUserSkeleton();
			$body			= make::tpl('body.user.logged.list');
			$base_url		= 'http://www.languagenut.com';
			$arrRows		= array();

			if(!empty($arrList)) {
				foreach($arrList as $uid=>$data) {
					$data['base_url'] = $base_url;
					$arrRows[] = make::tpl('body.user.logged.list.row')->assign($data)->get_content();
				}
			}

			$page_display_title		= $objLoginAccess->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
			$page_navigation		=   $objLoginAccess->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>').$objLoginAccess->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ').$objLoginAccess->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

			$body->assign('page.display.title'	, $page_display_title);
			$body->assign('page.navigation'		, $page_navigation);
			$body->assign('list.rows'			, implode('',$arrRows));

			$skeleton->assign (
					array(
					'body'=>$body
					)
			);
			output::as_html($skeleton,true);
		} else {
			if(isset($_SESSION['user']['admin']) && $_SESSION['user']['admin'] == 1){
				output::redirect(config::url('admin/users/school/'));
			} else {
				output::redirect(config::url('account/'));
			}

		}
	}
}

?>