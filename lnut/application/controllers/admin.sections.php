<?php

class admin_sections extends Controller {

	private $token		= 'list';

	private $arrTokens	= array (
		'list',
		'edit',
		'add',
		'delete',
		'deletetranslation'
	);
	private $arrPaths	= array();

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
		$skeleton	= make::tpl('skeleton.admin');
		$body		= make::tpl('body.admin.section.add');
		$objUnit	= new units();
		$arrBody	= array();

		if(isset($_POST['form_submit_button'])){
			$objSection	= new sections();
			if($objSection->doSave() ){
				// redirect to invoice list if all does well;
				$objSection->redirectTo('admin/section/list');
			}
			else{
				$objSection->arrForm['unit_uid'] = $objUnit->UnitSelectBox('unit_uid', $objSection->arrForm['unit_uid']);
				$body->assign( $objSection->arrForm );
			}
		} else {
			$arrBody['unit_uid'] = $objUnit->UnitSelectBox('unit_uid');
		}

		$body->assign($arrBody);
		$skeleton->assign (
			array (
				'body' => $body
			)
		);
		output::as_html($skeleton,true);
	}

	protected function doEdit() {
		$skeleton		= make::tpl('skeleton.admin');
		$body			= new xhtml('body.admin.section.edit');
		$objUnit		= new units();
		$arrBody		= array();
		$arrBody['uid'] = $this->arrPaths[3];

		if(isset($_POST['form_submit_button'])){
			$objSection	= new sections();
			if($objSection->doSave() ){
				// redirect to invoice list if all does well;
				$objSection->redirectTo('admin/section/list');
			} else{
				$objSection->arrForm['unit_uid'] = $objUnit->UnitSelectBox('unit_uid', $objSection->arrForm['unit_uid']);
				$body->assign( $objSection->arrForm );
			}
		} else {
			if($this->arrPaths[3] > 0){
				$EditObject = new sections($this->arrPaths[3]);
				$EditObject->load();
				foreach($EditObject->TableData as $idx => $val ){
					$arrBody[$idx] = $val['Value'];
				}
				$arrBody['unit_uid'] = $objUnit->UnitSelectBox('unit_uid', $arrBody['unit_uid']);
				if($arrBody['active'] == 0 ) {
					$arrBody['active'] =  'checked="checked"';
				}
			}
		}

		$arrBody['tab.translations'] = $this->doTranslationsList($arrBody['uid']);
		$body->assign($arrBody);

		$skeleton->assign (
			array (
				'body' => $body
			)
		);
		output::as_html($skeleton,true);
	}

	protected function doTranslationsList( $section_uid ) {
		$body			= make::tpl('body.admin.section.translation.list');
		$bodyArr		= array();
		$objLanguage	= new language();

		$bodyArr['language_id'] = $objLanguage->LanguageSelectBox('language_id');

		if(isset($_POST['add_translation'])){
			$objSectionTranslations = new sections_translations();
			if( $objSectionTranslations->doSave() ){

			} else {
				if($objSectionTranslations->arrForm['uid'] > 0 ){
					$objSectionTranslations->arrForm['uid']				= $_POST['uid'];
					$objSectionTranslations->arrForm['button_lable']	= 'Update';
					$objSectionTranslations->arrForm['cancel_button']	= 'display:block;';
					}
					$objSectionTranslations->arrForm['language_id'] =  $objLanguage->LanguageSelectBox('language_id', $objSectionTranslations->arrForm['language_id']);
					$body->assign( $objSectionTranslations->arrForm );
				}
		}
		$objSection		= new sections();
		$arrSections	= array();
		$arrRows		= array();
		$arrSections	= $objSection->sectionTranslationsList($section_uid);
		if(!empty($arrSections)) {
			foreach($arrSections as $uid=>$data) {
				$arrRows[] = make::tpl('body.admin.section.translation.row')->assign($data)->get_content();
			}
		} else {
			$arrRows[] = 'Translations are not available with this section.';
		}

		$bodyArr['uid']				= 0;
		$bodyArr['button_lable']	= 'Add';
		$bodyArr['cancel_button']	= 'display:none;';
		$bodyArr['section_uid']		= $section_uid;
		$bodyArr['frm_action']		= $_SERVER['REQUEST_URI'].'#tab-2';

		$body->assign('list.rows',implode('',$arrRows));
		$body->assign($bodyArr);

		return $body->get_content();
	}

	protected function doDelete() {
		if($this->arrPaths[3] > 0){
			$objSection = new sections($this->arrPaths[3]);
			$objSection->delete();
			$objSection->redirectTo('admin/section/list');
		}
	}

	protected function doDeletetranslation() {
		if($this->arrPaths[4] > 0){
			$objSectionTranslations = new sections_translations($this->arrPaths[4]);
			$objSectionTranslations->delete();
			$objSectionTranslations->redirectTo('admin/section/edit/'.$this->arrPaths[3].'#tab-2'); // redirect to invoice list if all does well;
		}
	}

	protected function doList () {
		$skeleton			= make::tpl('skeleton.admin');
		$body				= make::tpl('body.admin.section.list');
		$arrSections		= array();
		$objSection			= new sections();
		$arrSections		= $objSection->getList();
		$arrRows			= array();
		if(!empty($arrSections)) {
			foreach($arrSections as $uid=>$data) {
				$arrRows[] = new xhtml('body.admin.section.list.row')->assign($data)->get_content();
			}
		}

		$page_display_title			= $objSection->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
		$page_navigation			=   $objSection->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>').$objSection->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ').$objSection->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

		$body->assign('page.display.title'	, $page_display_title);
		$body->assign('page.navigation'		, $page_navigation);
		$body->assign('list.rows'			, implode('',$arrRows));

		$skeleton->assign (
			array (
				'body'	=> $body
			)
		);
		output::as_html($skeleton,true);
	}

}

?>