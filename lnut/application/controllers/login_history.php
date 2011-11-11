<?php

class login_history extends Controller {

	public function __construct () {
		parent::__construct();
		$this->ShowPage();
	}

	private function ShowPage() {
		$paths = config::get('paths');

		if( isset( $paths[3] ) && is_numeric($paths[3]) && $paths[3] > 0 ) {
			$this->SchoolUsersLogs( $paths[3] );
			exit;
		}

		$objLoggingAccess	= new logging_access();
		$arrList			= $objLoggingAccess->getLoginStates();
		$skeleton			= config::getUserSkeleton();
		$body				= make::tpl ('body.admin.login-history.list');

		$body->assign(
			array (
				'component-search-form' => component_search::form(
					array (
						'section' => 'login_history'
					)
				)
			)
		);

		$arrRow = array();
		if(!empty($arrList)) {
			foreach($arrList as $uid=>$data) {
				$arrRow[] = make::tpl ('body.admin.login-history.list.row')->assign($data)->get_content();
			}
		}

		$page_display_title		=   $objLoggingAccess->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
		$page_navigation		=   $objLoggingAccess->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>').$objLoggingAccess->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ').$objLoggingAccess->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

		$body->assign('page.display.title'	, $page_display_title);
		$body->assign('page.navigation'		, $page_navigation);
		$body->assign('rows'				, implode('',$arrRow));

		$skeleton->assign (
			array (
				'body'=> $body
			)
		);
		output::as_html($skeleton,true);		
	}

	private function SchoolUsersLogs( $school_uid ) {
		$objLoggingAccess	= new logging_access();
		$arrList			= $objLoggingAccess->getSchoolUserLoginStates( $school_uid );

		$skeleton			= config::getUserSkeleton();
		$body				= make::tpl ('body.admin.login-history-school-users.list');

		$body->assign(
			array (
				'component-search-form' => component_search::form(
					array (
						'section'		=>	'login_history',
						'school_uid'	=>	$school_uid
					)
				)
			)
		);

		$arrRow = array();
		if(!empty($arrList)) {
			foreach($arrList as $uid=>$data) {
				$arrRow[] = make::tpl ('body.admin.login-history-school-users.list.row')->assign($data)->get_content();
			}
		}

		$page_display_title			= $objLoggingAccess->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
		$page_navigation			= $objLoggingAccess->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>').$objLoggingAccess->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ').$objLoggingAccess->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');
		
		$body->assign('page.display.title'	, $page_display_title);
		$body->assign('page.navigation'		, $page_navigation);
		$body->assign('rows'				, implode('',$arrRow));

		$skeleton->assign (
			array (
				'body'=> $body
			)
		);
		output::as_html($skeleton,true);
	}
}

?>