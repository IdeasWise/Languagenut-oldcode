<?php

class admin_article_content extends Controller {

	private $token			= 'list';
	private $arrTokens		= array (
		'list',
		'edit',
		'add',
		'delete',
		'translation',
		'groups'
	);
	private $arrPaths		= array();
	private $article_uid		=null;
	private $article_page_uid	=null;

	public function __construct () {
		parent::__construct();
		$this->arrPaths = config::get('paths');

		//$this->isValidArticleUid();
		$this->isValidArticlePageUid();

		if(isset($this->arrPaths[4]) && in_array($this->arrPaths[4], $this->arrTokens)) {
			$this->token =  $this->arrPaths[4];
		}
		if(in_array($this->token,$this->arrTokens)) {
			$method = 'do' . ucfirst($this->token); 
			$this->$method();
		}
	}

	private function isValidArticleUid() {
		if(isset($this->arrPaths[3]) && is_numeric($this->arrPaths[3]) && $this->arrPaths[3] > 0) {
			$objArticle = new article($this->arrPaths[3]);
			if($objArticle->get_valid()) {
				$this->article_uid=$this->arrPaths[3];
			} else {
				// redirect back to article list
				output::redirect(config::url('admin/article/list/'));
			}
		} else {
			// redirect back to article list
			output::redirect(config::url('admin/article/list/'));
		}
	}

	private function isValidArticlePageUid() {
		if(isset($this->arrPaths[3]) && is_numeric($this->arrPaths[3]) && $this->arrPaths[3] > 0) {
			$objArticlePage = new article_page($this->arrPaths[3]);
			if($objArticlePage->get_valid()) {
				$objArticlePage->load();
				$this->article_page_uid=$objArticlePage->get_uid();
				$this->article_uid=$objArticlePage->get_article_uid();
			} else {
				// redirect back to article list
				output::redirect(config::url('admin/article/list/'));
			}
		} else {
			// redirect back to article list
			output::redirect(config::url('admin/article/list/'));
		}
	}

	private function doGroups() {
		$skeleton		= make::tpl('skeleton.admin');
		$body			= make::tpl('body.admin.article.content.group-content.list');
		$objarticleGroupContent	= new article_group_content();
		$article_content_uid = (isset($this->arrPaths[5]) && is_numeric($this->arrPaths[5]))?$this->arrPaths[5]:null; 
		$arrGroups	= $objarticleGroupContent->getContentGroupList($this->article_uid,$article_content_uid);

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
				'article_uid'=>$this->article_uid
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
		$body		= make::tpl('body.admin.article.content.add');
		$support_language_uid	= 0;
		$arrLearnable			= array();
		if(count($_POST) > 0) {
			$objArticleContent = new article_content();
			if(($response=$objArticleContent->isValidCreate())===true) {
				output::redirect(config::url('admin/article/content/'.$this->article_page_uid.'/list/'));
			} else {
				$body->assign($objArticleContent->arrForm);
			}
		}
		$body->assign(
			array(
				'article_uid'		=>$this->article_uid,
				'article_page_uid'	=>$this->article_page_uid
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
		$body			= make::tpl('body.admin.article.content.edit');
		$arrBody		= array();
		$uid			= (isset($this->arrPaths[5]) && (int)$this->arrPaths[5] > 0) ? $this->arrPaths[5] : '';

		if($uid != '') {
			$objArticleContent = new article_content($uid);
			$objArticleContent->load();
			$arrBody['uid'] = $uid;
			if(count($_POST) > 0) {
				if(($response = $objArticleContent->isValidUpdate())===true) {
					output::redirect(config::url('admin/article/content/'.$this->article_page_uid.'/list/'));
				} else {
					if(isset($objArticleContent->arrForm['item_type_uid']) && $objArticleContent->arrForm['item_type_uid'] == 2) {
						$objArticleContent->arrForm['type_text_checked'] = 'checked="checked"';
					}
					if(isset($objArticleContent->arrForm['accept_content']) && $objArticleContent->arrForm['accept_content'] == 0) {
					$objArticleContent->arrForm['accept_content_no'] = 'checked="checked"';
				}
					$body->assign($objArticleContent->arrForm);
				}
			} else {
				foreach( $objArticleContent->TableData as $idx => $val ){
					$arrBody[$idx] = $val['Value'];
				}
				if(isset($arrBody['item_type_uid']) && $arrBody['item_type_uid'] == 2) {
					$arrBody['type_text_checked'] = 'checked="checked"';
				}
				if(isset($arrBody['accept_content']) && $arrBody['accept_content'] == 0) {
					$arrBody['accept_content_no'] = 'checked="checked"';
				}
				$body->assign($arrBody);
			}

		} else {
			output::redirect(config::url('admin/article/content/'.$this->article_page_uid.'/list/'));
		}

		$skeleton->assign (
			array (
				'body' => $body
			)
		);
		output::as_html($skeleton,true);
	}


	protected function doList () {
		$skeleton		= make::tpl('skeleton.admin');
		$body			= make::tpl('body.admin.article.content.list');
		$objArticleContent	= new article_content();
		$arrTemplateContent	= $objArticleContent->getList($this->article_uid);

		if($arrTemplateContent && count($arrTemplateContent) > 0) {
			$rows = array ();
			foreach($arrTemplateContent as $uid=>$arrData) {
				$rows[] = make::tpl('body.admin.article.content.list.row')->assign($arrData)->get_content();
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
				'article_uid'		=>$this->article_uid,
				'article_page_uid'	=>$this->article_page_uid
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
			$objArticleContent = new article_content($this->arrPaths[5]);
			$objArticleContent->delete();
			//$objTemplateTranslation = new template_translation();
			//$objTemplateTranslation->DeleteTemplateTranslation($this->arrPaths[3]);
			
			$objArticleContent->redirectTo('admin/article/content/'.$this->article_page_uid.'/list/');
		} else {
			output::redirect(config::url('admin/article/content/'.$this->article_page_uid.'/list/'));
		}
	}
}

?>