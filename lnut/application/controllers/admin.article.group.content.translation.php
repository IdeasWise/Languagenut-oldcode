<?php

class admin_article_group_content_translation extends Controller {

	private $token						= 'list';
	private $arrTokens					= array (
		'list',
		'edit',
		'add',
		'delete'
	);
	private $arrPaths					= array();
	private $article_translation_uid	= null;
	private $article_uid				= null;
	private $article_language			= null;

	public function __construct () {
		parent::__construct();
		$this->arrPaths = config::get('paths');

		$this->isValidarticleTranslationUid();

		if(isset($this->arrPaths[4]) && in_array($this->arrPaths[4], $this->arrTokens)) {
			$this->token =  $this->arrPaths[4];
		}
		if(in_array($this->token,$this->arrTokens)) {
			$method = 'do' . ucfirst($this->token); 
			$this->$method();
		}
	}

	private function isValidarticleTranslationUid() {
		if(isset($this->arrPaths[3]) && is_numeric($this->arrPaths[3]) && $this->arrPaths[3] > 0) {
			$objarticleTranslation = new article_translations($this->arrPaths[3]);
			if($objarticleTranslation->get_valid()) {
				$objarticleTranslation->load();
				$this->article_translation_uid=$this->arrPaths[3];
				$this->article_uid = $objarticleTranslation->get_article_uid();
				$objLanguage = new language($objarticleTranslation->get_language_uid());
				$objLanguage->load();
				$this->article_language = $objLanguage->get_name();

			} else {
				// redirect back to article list
				output::redirect(config::url('admin/article/list/'));
			}
		} else {
			// redirect back to article list
			output::redirect(config::url('admin/article/list/'));
		}
	}

	protected function doEdit() {

		$skeleton		= make::tpl('skeleton.admin');
		$body			= make::tpl('body.admin.article.content.translation.edit');
		$arrBody		= array();
		$uid			= (isset($this->arrPaths[5]) && (int)$this->arrPaths[5] > 0) ? $this->arrPaths[5] : '';

		if($uid != '') {
			$objarticleContent = new article_content_translations($uid);
			$objarticleContent->load();
			$arrBody['uid'] = $uid;
			if(count($_POST) > 0) {
				if(($response = $objarticleContent->isValidUpdate())===true) {
					output::redirect(config::url('admin/article/content-translation/'.$this->article_translation_uid.'/list/'));
				} else {
					if(isset($objarticleContent->arrForm['item_type_uid']) && $objarticleContent->arrForm['item_type_uid'] == 2) {
						$objarticleContent->arrForm['type_text_checked'] = 'checked="checked"';
					}
					$body->assign($objarticleContent->arrForm);
				}
			} else {
				foreach( $objarticleContent->TableData as $idx => $val ){
					$arrBody[$idx] = $val['Value'];
				}
				if(isset($arrBody['item_type_uid']) && $arrBody['item_type_uid'] == 2) {
					$arrBody['type_text_checked'] = 'checked="checked"';
				}
				$body->assign($arrBody);
			}

		} else {
			output::redirect(config::url('admin/article/content-translation/'.$this->article_translation_uid.'/list/'));
		}
		
		$body->assign(
			array(
				'article_translation_uid'	=>$this->article_translation_uid,
				'article_uid'				=>$this->article_uid,
				'article_language'			=>$this->article_language
			)
		);

		$skeleton->assign (
			array (
				'body' => $body
			)
		);
		output::as_html($skeleton,true);
	}


	protected function doList () {

		$skeleton		= make::tpl('skeleton.admin');
		$body			= make::tpl('body.admin.article.group.content.translation.list');
		$objarticleGroupContent	= new article_group_content_translations();
		$arrGroups	= $objarticleGroupContent->getList($this->article_translation_uid);

		if(count($arrGroups) > 0) {
			$rows = array ();
			foreach($arrGroups as $uid=>$arrData) {
				$rows[] = make::tpl('body.admin.article.group.content.list.row')->assign($arrData)->get_content();
			}

			$page_display_title		= $objarticleGroupContent->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');

			$page_navigation = $objarticleGroupContent->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>');
			$page_navigation .= $objarticleGroupContent->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>', ' &raquo ');
			$page_navigation .= $objarticleGroupContent->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

			$body->assign('page.display.title'	, $page_display_title);
			$body->assign('page.navigation'		, $page_navigation);
			$body->assign('rows',implode('',$rows));
		}

		$body->assign(
			array(
				'article_translation_uid'	=>$this->article_translation_uid,
				'article_uid'				=>$this->article_uid,
				'article_language'			=>$this->article_language
			)
		);

		$skeleton->assign (
			array (
				'body' => $body
			)
		);
		output::as_html($skeleton,true);

	}

		protected function doDelete() {
		if(isset($this->arrPaths[5]) && (int)$this->arrPaths[5] > 0) {
			$objarticleContent = new article_content_translations($this->arrPaths[5]);
			$objarticleContent->delete();
			//$objarticleTranslation = new article_translation();
			//$objarticleTranslation->DeletearticleTranslation($this->arrPaths[3]);
			
			$objarticleContent->redirectTo('admin/article/content-translation/'.$this->article_translation_uid.'/list/');
		} else {
			output::redirect(config::url('admin/article/content-translation/'.$this->article_translation_uid.'/list/'));
		}
	}
}

?>