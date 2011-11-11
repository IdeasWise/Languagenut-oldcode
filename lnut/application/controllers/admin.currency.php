<?php

class admin_currency extends Controller {

	private $token		= 'list';
	private $arrTokens	= array (
		'list',
		'edit',
		'add',
		'delete',
		'pricing'
	);
	private $parts		= array();

	public function __construct () {
		parent::__construct();
		$this->parts = config::get('paths');

		if(isset($this->parts[2]) && in_array($this->parts[2], $this->arrTokens)) {
			$this->token =  $this->parts[2];
		}

		if(in_array($this->token,$this->arrTokens)) {
			$method = 'do' . ucfirst($this->token); 
			$this->$method();
		}
	}

	protected function doPricing() {
		$skeleton	= config::getUserSkeleton();
		$objTabs	= new tabs();
		$skeleton->assign ( $objTabs->get_tabs_and_contents_of_pricing() );
		output::as_html($skeleton,true);
	}

	protected function doAdd() {
		$skeleton			= make::tpl('skeleton.admin');
		$body				= make::tpl('body.admin.currency.add.edit');
		$arrBody			= array();
		$arrBody['title']	= 'Add Currency';
		$arrBody['btnval']	= 'Add';

		if(isset($_POST['form_submit_button'])){
			$objCurrencies = new currencies();
			if($objCurrencies->doSave() ){
				// redirect to currency list if all does well;
				$objCurrencies->redirectTo('admin/currency/list');
			} else {
				if($objCurrencies->arrForm['position'] == 'after' ) {
					$objCurrencies->arrForm['after'] = 'checked="checked"';
				}
				$body->assign($objCurrencies->arrForm );
			}
		}

		$body->assign( $arrBody );

		$skeleton->assign (
			array (
				'body' => $body
			)
		);
		output::as_html($skeleton,true);
	}

	protected function doEdit() {

		$skeleton			= make::tpl('skeleton.admin');
		$body				= make::tpl('body.admin.currency.add.edit');
		$arrBody			= array();
		$arrBody['title']	= 'Update Currency';
		$arrBody['btnval']	= 'Update';

		if(isset($_POST['form_submit_button'])){
			$objCurrencies = new currencies();
			if($objCurrencies->doSave() ){
				// redirect to currency list if all does well;
				$objCurrencies->redirectTo('admin/currency/list');
			} else {
				if($objCurrencies->arrForm['position'] == 'after' ) {
					$objCurrencies->arrForm['after'] = 'checked="checked"';
				}
				$body->assign( $objCurrencies->arrForm );
			}
		} else {
			if($this->parts[3] > 0){
				$objCurrencies = new currencies($this->parts[3]);
				$objCurrencies->load();
				
				foreach( $objCurrencies->TableData as $idx => $val ){
					$arrBody[$idx] = $val['Value'];
				}
				$arrBody['uid'] = $this->parts[3];
				if($arrBody['position'] == 'after' ) {
					$arrBody['after'] = 'checked="checked"';
				}
			}
		}
		$body->assign( $arrBody );

		$skeleton->assign (
			array (
				'body' => $body
			)
		);
		output::as_html($skeleton,true);
	}

	protected function doDelete() {
		if($this->parts[3] > 0){
			$objCurrencies = new currencies($this->parts[3]);
			$objCurrencies->load();
			$objCurrencies->delete();
			$objCurrencies->redirectTo('admin/currency/list/'); // redirect to currency list if all does well;
			exit;
		}
	}

	protected function doList () {
		$skeleton	= make::tpl('skeleton.admin');
		$body		= make::tpl('body.admin.currency.list');

		$arrCurrencies	= array();
		$objCurrencies	= new currencies();
		$arrCurrencies	= $objCurrencies->getList();
		$arrRows		= array();

		if(!empty($arrCurrencies)) {
			foreach($arrCurrencies as $uid=>$data) {
				$data['position'] = ucfirst($data['position']);
				$arrRows[] = make::tpl('body.admin.currency.list.row')->assign($data)->get_content();
			}
		}

		$page_display_title		= $objCurrencies->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
		$page_navigation		= $objCurrencies->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>').$objCurrencies->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ').$objCurrencies->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

		$body->assign('page.display.title'	, $page_display_title);
		$body->assign('page.navigation'		, $page_navigation);
		$body->assign('list.rows'			, implode('',$arrRows));

		$skeleton->assign (
			array (
				'body' => $body
			)
		);

		output::as_html($skeleton,true);
	}
}
?>