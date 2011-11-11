<?php

class admin_article_content_translation extends Controller {

	private $token						= 'list';
	private $arrTokens					= array (
		'list',
		'edit',
		'add',
		'delete',
		'groups'
	);
	private $arrPaths					= array();
	private $article_translation_uid	= null;
	private $article_uid				= null;
	private $article_language			= null;

	public function __construct () {
		parent::__construct();
		$this->arrPaths = config::get('paths');

		$this->isValidArticleTranslationUid();

		if(isset($this->arrPaths[4]) && in_array($this->arrPaths[4], $this->arrTokens)) {
			$this->token =  $this->arrPaths[4];
		}
		if(in_array($this->token,$this->arrTokens)) {
			$method = 'do' . ucfirst($this->token); 
			$this->$method();
		}
	}

	private function isValidArticleTranslationUid() {
		if(isset($this->arrPaths[3]) && is_numeric($this->arrPaths[3]) && $this->arrPaths[3] > 0) {
			$objArticleTranslation = new article_translations($this->arrPaths[3]);
			if($objArticleTranslation->get_valid()) {
				$objArticleTranslation->load();
				$this->article_translation_uid=$this->arrPaths[3];
				$this->article_uid = $objArticleTranslation->get_article_uid();
				$objLanguage = new language($objArticleTranslation->get_language_uid());
				$objLanguage->load();
				$this->article_language = $objLanguage->get_name();

			} else {
				// redirect back to article-template list
				output::redirect(config::url('admin/article/list/'));
			}
		} else {
			// redirect back to article list
			output::redirect(config::url('admin/article/list/'));
		}
	}

	protected function doGroups() {
		$skeleton		= make::tpl('skeleton.admin');
		$body			= make::tpl('body.admin.article.group.content.translation.list');
		
		$article_content_translation_uid = (isset($this->arrPaths[5]) && is_numeric($this->arrPaths[5]))?$this->arrPaths[5]:null;

		$objarticleGroupContent	= new article_group_content_translations();
		$arrGroups	= $objarticleGroupContent->getContentGroupList($this->article_translation_uid,$article_content_translation_uid);

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
	protected function doAdd() {
		$skeleton	= make::tpl('skeleton.admin');
		$body		= make::tpl('body.admin.article.content.translation.add');
		$support_language_uid	= 0;
		$arrLearnable			= array();
		if(count($_POST) > 0) {
			$objTemplateContent = new article_content_translations();
			if(($response=$objTemplateContent->isValidCreate())===true) {
				output::redirect(config::url('admin/article/content-translation/'.$this->article_translation_uid.'/list/'));
			} else {
				$body->assign($objTemplateContent->arrForm);
			}
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

	protected function doEdit() {

		$skeleton		= make::tpl('skeleton.admin');
		$body			= make::tpl('body.admin.article.template.content.translation.edit');
		$arrBody		= array();
		$uid			= (isset($this->arrPaths[5]) && (int)$this->arrPaths[5] > 0) ? $this->arrPaths[5] : '';

		if($uid != '') {
			$objTemplateContent = new article_content_translations($uid);
			$objTemplateContent->load();
			$arrBody['uid'] = $uid;
			if(count($_POST) > 0) {
				if(($response = $objTemplateContent->isValidUpdate())===true) {
					output::redirect(config::url('admin/article/content-translation/'.$this->article_translation_uid.'/list/'));
				} else {
					if(isset($objTemplateContent->arrForm['item_type_uid']) && $objTemplateContent->arrForm['item_type_uid'] == 2) {
						$objTemplateContent->arrForm['type_text_checked'] = 'checked="checked"';
					}
					$body->assign($objTemplateContent->arrForm);
				}
			} else {
				foreach( $objTemplateContent->TableData as $idx => $val ){
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
		$body			= make::tpl('body.admin.article.content.translation.list');
		$objArticleContent	= new article_content_translations();
		$arrTemplateContent	= $objArticleContent->getList($this->article_translation_uid);

		if($arrTemplateContent && count($arrTemplateContent) > 0) {
			$rows = array ();
			foreach($arrTemplateContent as $uid=>$arrData) {
				$rows[] = make::tpl('body.admin.article.content.translation.list.row')->assign($arrData)->get_content();
			}

			$page_display_title		= $objArticleContent->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');

			$page_navigation = $objArticleContent->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>');
			$page_navigation .= $objArticleContent->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>', ' &raquo ');
			$page_navigation .= $objArticleContent->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

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
			$objArticleContent = new article_content_translations($this->arrPaths[5]);
			$objArticleContent->delete();
			//$objArticleTranslation = new template_translation();
			//$objArticleTranslation->DeleteTemplateTranslation($this->arrPaths[3]);
			
			$objArticleContent->redirectTo('admin/article/content-translation/'.$this->article_translation_uid.'/list/');
		} else {
			output::redirect(config::url('admin/article/content-translation/'.$this->article_translation_uid.'/list/'));
		}
	}
}

?>