<?php

class admin_article_groups_content extends Controller {

	private $token				= 'list';
	private $arrTokens			= array (
		'list',
		'edit',
		'delete'
	);
	private $arrPaths			= array();
	private $article_uid		= null;
	private $article_group_uid = null;

	public function __construct () {
		parent::__construct();
		$this->arrPaths = config::get('paths');

		$this->isValidarticleUid();
		$this->isValidarticleGroupUid();

		if(isset($this->arrPaths[6]) && in_array($this->arrPaths[6], $this->arrTokens)) {
			$this->token =  $this->arrPaths[6];
		}
		if(in_array($this->token,$this->arrTokens)) {
			$method = 'do' . ucfirst($this->token); 
			$this->$method();
		}
	}

	private function isValidarticleUid() {
		if(isset($this->arrPaths[3]) && is_numeric($this->arrPaths[3]) && $this->arrPaths[3] > 0) {
			$objarticle = new article($this->arrPaths[3]);
			if($objarticle->get_valid()) {
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

	private function isValidarticleGroupUid() {
		if(isset($this->arrPaths[5]) && is_numeric($this->arrPaths[5]) && $this->arrPaths[5] > 0) {
			$objarticleGroup = new article_group($this->arrPaths[5]);
			if($objarticleGroup->get_valid()) {
				$this->article_group_uid=$this->arrPaths[5];
			} else {
				// redirect back to article list
				output::redirect(config::url('admin/article/groups/'.$this->article_uid.'/list/'));
			}
		} else {
			// redirect back to article list
			output::redirect(config::url('admin/article/groups/'.$this->article_uid.'/list/'));
		}
	}

	protected function doEdit() {

		$skeleton		= make::tpl('skeleton.admin');
		$body			= make::tpl('body.admin.article.group.content.edit');
		$arrBody		= array();
		$uid			= (isset($this->arrPaths[7]) && (int)$this->arrPaths[7] > 0) ? $this->arrPaths[7] : '';

		if($uid != '') {
			$objarticleGroupContent = new article_group_content($uid);
			$objarticleGroupContent->load();
			$arrBody['uid'] = $uid;
			if(count($_POST) > 0) {
				if(($response = $objarticleGroupContent->isValidUpdate())===true) {
					output::redirect(config::url('admin/article/groups/'.$this->article_uid.'/content/'.$this->article_group_uid.'/'));
				} else {
					$body->assign($objarticleGroupContent->arrForm);
				}
			} else {
				foreach( $objarticleGroupContent->TableData as $idx => $val ){
					$arrBody[$idx] = $val['Value'];
				}
				$body->assign($arrBody);
			}

		} else {
			output::redirect(config::url('admin/article/groups/'.$this->article_uid.'/content/'.$this->article_group_uid.'/'));
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
		$body			= make::tpl('body.admin.article.group.content.list');
		$objarticleGroupContent	= new article_group_content();
		$arrGroups	= $objarticleGroupContent->getList($this->article_uid,$this->article_group_uid);

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

		protected function doDelete() {
		if(isset($this->arrPaths[7]) && (int)$this->arrPaths[7] > 0) {
			$objarticleGroupContent	= new article_group_content($this->arrPaths[7]);
			$objarticleGroupContent->delete();
			$objarticleGroupContent->redirectTo('admin/article/groups/'.$this->article_uid.'/content/'.$this->article_group_uid.'/');
		} else {
			output::redirect(config::url('admin/article/groups/'.$this->article_uid.'/content/'.$this->article_group_uid.'/'));
		}
	}
}

?>