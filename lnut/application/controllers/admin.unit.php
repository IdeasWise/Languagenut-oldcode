<?php

class admin_unit extends Controller {

	private $token		= 'list';
	private $arrTokens	= array (
		'list',
		'edit',
		'add',
		'delete',
		'deletetranslation'
	);
	private $arrPaths		= array();

	public function __construct () {
		parent::__construct();
		$this->arrPaths = config::get('paths');
		if(isset($this->arrPaths[2]) && in_array($this->arrPaths[2], $this->arrTokens)) {
			$this->token =  $this->arrPaths[2];
		}

		if(in_array($this->token,$this->arrTokens)) {
			$method = 'do' . ucfirst($this->token);
			$this->$method();
		}
	}

	protected function doAdd() {
		$skeleton	= make::tpl ('skeleton.admin');
		$body		= make::tpl ('body.admin.unit.add');
		$objYear	= new years();
		$arrBody	= array();

		if(isset($_POST['form_submit_button'])){
			$objUnit = new units();
			if($objUnit->doSave()){
				// redirect to invoice list if all does well;
				$objUnit->redirectTo('admin/unit/list');
			} else {
				$objUnit->arrForm['year_uid'] = $objYear->YearSelectBox('year_uid', $objUnit->arrForm['year_uid']);
				$body->assign( $objUnit->arrForm );
			}
		} else {
			$arrBody['year_uid'] = $objYear->YearSelectBox('year_uid');
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
		$skeleton	= make::tpl ('skeleton.admin');
		$body		= make::tpl ('body.admin.unit.edit');
		$objYear	= new years();
		$arrBody	= array();

		$arrBody['uid'] = $this->arrPaths[3];

		if( isset($_POST['form_submit_button'])){
			$objUnit = new units();
			if( $objUnit->doSave() ){
				// redirect to invoice list if all does well;
				$objUnit->redirectTo('admin/unit/list');
			} else {
				$objUnit->arrForm['year_uid'] = $objYear->YearSelectBox('year_uid', $objUnit->arrForm['year_uid']);
				$body->assign( $objUnit->arrForm );
			}
		} else {
			if($this->arrPaths[3] > 0){
				$objUnit = new units($this->arrPaths[3]);
				$objUnit->load();
				foreach( $objUnit->TableData as $idx => $val ){
					$arrBody[$idx] = $val['Value'];
				}
				$arrBody['year_uid'] = $objYear->YearSelectBox('year_uid', $arrBody['year_uid']);
				if($arrBody['active'] == 0 ) {
					$arrBody['active'] =  'checked="checked"';
				}
			}
		}


		$arrBody['tab.translations'] = $this->doUnitTranslationsList($arrBody['uid']);
		$body->assign( $arrBody );

		$skeleton->assign (
			array (
				'body' => $body
			)
		);
			output::as_html($skeleton,true);
	}

	protected function doUnitTranslationsList( $unit_id ) {
		$body			= make::tpl ('body.admin.unit.translation.list');
		$arrBody		= array();
		$objLanguage	= new language();
		$arrBody['language_id'] = $objLanguage->LanguageSelectBox('language_id');

		if(isset($_POST['add_translation'])){
			$objUnitsTranslations = new units_translations();
			if( $objUnitsTranslations->doSave() ){
			} else {
				if($objUnitsTranslations->arrForm['uid'] > 0 ){
					$objUnitsTranslations->arrForm['uid'] = $_POST['uid'];
					$objUnitsTranslations->arrForm['button_lable'] = 'Update';
					$objUnitsTranslations->arrForm['cancel_button'] = 'display:block;';
				}
				$objUnitsTranslations->arrForm['language_id'] = $objLanguage->LanguageSelectBox('language_id', $objUnitsTranslations->arrForm['language_id']);
				$body->assign( $objUnitsTranslations->arrForm );
			}
		}

		$objUnit	= new units();
		$arrList	= array();
		$arrRows	= array();
		$arrList	= $objUnit->unitTranslationsList($unit_id);
		if(!empty($arrList)) {
			foreach($arrList as $uid=>$data) {
				$arrRows[] = make::tpl ('body.admin.unit.translation.row')->assign($data)->get_content();
			}
		} else {
			$arrRows[] = 'Translation not available with this unit.';
		}

		$arrBody['uid']				= 0;
		$arrBody['button_lable']	= 'Add';
		$arrBody['cancel_button']	= 'display:none;';
		$arrBody['unit_id']			= $unit_id;
		$arrBody['frm_action']		= $_SERVER['REQUEST_URI'].'#tab-2';


		$body->assign('list.rows',implode('',$arrRows));
		$body->assign($arrBody);
		return $body->get_content();
	}

	protected function doDelete() {
		if( $this->arrPaths[3] > 0){
			$objUnit = new units($this->arrPaths[3]);
			$objUnit->delete();
			$objUnit->redirectTo('admin/unit/list');
		}
	}

	protected function doDeletetranslation() { 
		if( $this->arrPaths[4] > 0){
			$objUnitsTranslations = new units_translations($this->arrPaths[4]);
			$objUnitsTranslations->delete();
			$objUnitsTranslations->redirectTo('admin/unit/edit/'.$this->arrPaths[3].'#tab-2'); // redirect to invoice list if all does well;
		}
	}

	protected function doList () {
		$skeleton	= make::tpl ('skeleton.admin');
		$body		= make::tpl ('body.admin.unit.list');

		$arrUnits	= array();
		$objUnit	= new units();
		$arrUnits	= $objUnit->getList();
		$arrRows	= array();
		if(!empty($arrUnits)) {
			foreach($arrUnits as $uid=>$arrUnit) {
				$arrRows[] =make::tpl ('body.admin.unit.list.row')->assign($data)->get_content();
			}
		}

		$page_display_title		=   $objUnit->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
		$page_navigation		=   $objUnit->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>').$objUnit->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ').$objUnit->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

		$body->assign('page.display.title'	, $page_display_title);
		$body->assign('page.navigation'		, $page_navigation);
		$body->assign('list.rows'			, implode('',$arrRows));

		$skeleton->assign (
			array(
				'body'=> $body
			)
		);
		output::as_html($skeleton,true);
	}

}

?>