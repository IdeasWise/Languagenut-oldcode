<?php

class admin_article_category extends Controller {

	private $token		= 'list';

	private $arrTokens	= array (
		'list',
		'edit',
		'add',
		'delete',
		'content'
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

	protected function doContent() {
		if(isset($this->arrPaths[3]) && is_numeric($this->arrPaths[3])) {
			$objArticleCategory = new article_category($this->arrPaths[3]);
			if($objArticleCategory->get_valid()) {
				$objArticleCategory->load();
				$skeleton	= config::getUserSkeleton();
				$objTabs	= new tabs();
				$body		= $objTabs->GetArticleTabs(
					'body',
					'article_translations',
					'content',
					'body.admin.article.content.form',
					$objArticleCategory->get_uid(),
					$objArticleCategory
				);
				$skeleton->assign ($body);
				output::as_html($skeleton,true);
			} else {
				output::redirect(config::url('admin/article-category/list/'));
			}
		} else {
			output::redirect(config::url('admin/article-category/list/'));
		}
	}

	protected function doAdd() {
		$skeleton	= config::getUserSkeleton();
		$body		= make::tpl('body.admin.article.category.add');
		if(count($_POST) > 0) {
			$objArticleCategory = new article_category();
			if(($response=$objArticleCategory->isValidCreate())===true) {
				output::redirect(config::url('admin/article-category/list/'));
			} else {
				$body->assign($objArticleCategory->arrForm);
			}
		}
		$skeleton->assign (
			array (
				'body' => $body
			)
		);
		output::as_html($skeleton,true);
	}

	protected function doEdit() {

		$skeleton		= config::getUserSkeleton();
		$body			= make::tpl('body.admin.article.category.edit');
		$arrBody		= array();
		$uid			= (isset($this->arrPaths[3]) && (int)$this->arrPaths[3] > 0) ? $this->arrPaths[3] : '';
		if($uid != '') {
			$objArticleCategory = new article_category($uid);
			$objArticleCategory->load();
			$arrBody['uid'] = $uid;
			if(count($_POST) > 0) {
				if(($response = $objArticleCategory->isValidUpdate())===true) {
					output::redirect(config::url('admin/article-category/list/'));
				} else {
					$body->assign($objArticleCategory->arrForm);
				}
			} else {
				foreach( $objArticleCategory->TableData as $idx => $val ){
					$arrBody[$idx] = $val['Value'];
				}
				$body->assign($arrBody);
			}
		} else {
			output::redirect(config::url('admin/article-category/list/'));
		}

		$skeleton->assign (
			array (
				'body' => $body
			)
		);
		output::as_html($skeleton,true);
	}

	protected function doDelete() {
		if(isset($this->arrPaths[3]) && (int)$this->arrPaths[3] > 0) {
			$objArticleCategory = new article_category($this->arrPaths[3]);
			$objArticleCategory->delete();
			$objArticleCategory->redirectTo('admin/article-category/list/');
		} else {
			output::redirect(config::url('admin/article-category/list/'));
		}
	}

	protected function doList () {
		$skeleton		= config::getUserSkeleton();
		$body			= make::tpl('body.admin.article.category.list');
		$objArticleCategory		= new article_category();
		$arrArticle		= $objArticleCategory->getList();

		if($arrArticle && count($arrArticle) > 0) {
			$rows = array ();
			foreach($arrArticle as $uid=>$arrData) {
				$row = make::tpl('body.admin.article.category.list.row')->assign($arrData);
				$rows[] = $row->get_content();
			}
			$body->assign('rows',implode('',$rows));
			$page_display_title		= $objArticleCategory->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');

			$page_navigation = $objArticleCategory->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>');
			$page_navigation .= $objArticleCategory->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>', ' &raquo ');
			$page_navigation .= $objArticleCategory->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

			$body->assign('page.display.title'	, $page_display_title);
			$body->assign('page.navigation'		, $page_navigation);
		}
		$skeleton->assign (
			array (
				'body' => $body
			)
		);
		output::as_html($skeleton,true);
	}
}

?>
