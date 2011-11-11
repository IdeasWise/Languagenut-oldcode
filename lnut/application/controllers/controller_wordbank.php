<?php

class controller_wordbank extends Controller {

	public function __construct () {
		parent::__construct();
		$this->index();
	}

	protected function index() {
		$this->objWordBank = new wordbank();
		if(count($_POST) > 0) {
			if(isset($_POST['add']) && ($response=$this->objWordBank->isValidCreate($_POST))===true) {
				output::redirect(config::admin_uri('wordbank/'));
			}
			if(isset($_POST['save']) && ($response=$this->objWordBank->isValidUpdate($_POST))===true) {
				output::redirect(config::admin_uri('wordbank/'));
			}
		}

		$arrLocales		= language::getPrefixes();
		$arrTabs_li		= array();
		$arrTabs_div	= array();
		$arrLanguages	= array();

		if(count($arrLocales) > 0) {
			foreach($arrLocales as $uid=>$arrData) {
				$arrTabs_li[] = make::tpl('body.admin.tabs.li')->assign(
					array(
						'tab_id'	=>$uid,
						'lable'		=>$arrData['prefix']
					)
				)->get_content();

				$arrHtml						= array ();
				//$arrHtml[]						= '<table width="100%" border="0" cellspacing="0" cellpadding="10" class="table_main"><tr><th>Terms in '.$arrData['name'].'</th></tr>';

				$arrWords	= $this->objWordBank->getByLanguageUid($uid);

				foreach($arrWords as $word_uid=>$arrWord) {
					$Html = make::tpl('body.admin.wordbank.row')->assign(
						array(
							'word_uid'			=>$word_uid,
							'uid'				=>$uid,
							'term'				=>$arrWord['term']
						)
					);
					//$arrHtml[] = '<tr><td><input type="text" name="word_'.$word_uid.'_'.$uid.'" id="word_'.$word_uid.'_'.$uid.'" value="'.$arrWord['term'].'" class="box" /></td></tr>';
					$arrHtml[] = $Html->get_content();
				}

				//$arrHtml[]		= '</table>';
				
				$WordBankTable=make::tpl('body.wordbank.table')->assign(
					array(
						'name'			=>$arrData['name'],
						'table_content'	=>implode("",$arrHtml)
					)
				);
				$arrTabs_div[]	= make::tpl('body.admin.tabs.div')->assign(
					array(
						'tab_id'		=>$uid,
						'tab_content'	=>$WordBankTable->get_content()
					)
				)->get_content();
			}
		}

		if(isset($_SESSION['user']['admin']) && $_SESSION['user']['admin'] == 1) {
			$skeleton	= make::tpl ('skeleton.admin');
			$body		= make::tpl('body.admin.wordbank.list');
		} else {
			$skeleton	= make::tpl ('skeleton.account.translator');
			$body		= make::tpl('body.account.wordbank.list');
		}

		$translator=new profile_translator();
		$local_rights=$translator->GetLocaleRights($_SESSION["user"]["uid"]);
		$languages_rights=language::getPrefixesByLocale($local_rights);
		$combo="";
		foreach($languages_rights as $languages_right){
			$combo.='<option value="'.$languages_right["uid"].'">'.$languages_right["name"].' ('.$languages_right["prefix"].')</option>';
		}
		ksort($arrLanguages);

		$body->assign(
			array(
				'tabs'				=> implode('',$arrTabs_div),
				'locales'			=>implode('',$arrTabs_li),
				'language_options'	=>$combo,
				'form.action'		=> config::admin_uri('wordbank/')
			)
		);

		$skeleton->assign (
			array (
				'body' => $body
			)
		);

		output::as_html($skeleton,true);
	}
}

?>