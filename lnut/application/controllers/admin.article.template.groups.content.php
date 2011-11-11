<?php

class admin_article_template_groups_content extends Controller {

	private $token				= 'list';
	private $arrTokens			= array (
		'list',
		'edit',
		'delete'
	);
	private $arrPaths			= array();
	private $template_uid		= null;
	private $template_group_uid = null;

	public function __construct () {
		parent::__construct();
		$this->arrPaths = config::get('paths');

		$this->isValidTemplateUid();
		$this->isValidTemplateGroupUid();

		if(isset($this->arrPaths[6]) && in_array($this->arrPaths[6], $this->arrTokens)) {
			$this->token =  $this->arrPaths[6];
		}
		if(in_array($this->token,$this->arrTokens)) {
			$method = 'do' . ucfirst($this->token); 
			$this->$method();
		}
	}

	private function isValidTemplateUid() {
		if(isset($this->arrPaths[3]) && is_numeric($this->arrPaths[3]) && $this->arrPaths[3] > 0) {
			$objTemplate = new template($this->arrPaths[3]);
			if($objTemplate->get_valid()) {
				$this->template_uid=$this->arrPaths[3];
			} else {
				// redirect back to article-template list
				output::redirect(config::url('admin/article-template/list/'));
			}
		} else {
			// redirect back to article-template list
			output::redirect(config::url('admin/article-template/list/'));
		}
	}

	private function isValidTemplateGroupUid() {
		if(isset($this->arrPaths[5]) && is_numeric($this->arrPaths[5]) && $this->arrPaths[5] > 0) {
			$objTemplateGroup = new template_group($this->arrPaths[5]);
			if($objTemplateGroup->get_valid()) {
				$this->template_group_uid=$this->arrPaths[5];
			} else {
				// redirect back to article-template list
				output::redirect(config::url('admin/article-template/groups/'.$this->template_uid.'/list/'));
			}
		} else {
			// redirect back to article-template list
			output::redirect(config::url('admin/article-template/groups/'.$this->template_uid.'/list/'));
		}
	}

	protected function doEdit() {

		$skeleton		= make::tpl('skeleton.admin');
		$body			= make::tpl('body.admin.article.template.group.content.edit');
		$arrBody		= array();
		$uid			= (isset($this->arrPaths[7]) && (int)$this->arrPaths[7] > 0) ? $this->arrPaths[7] : '';

		if($uid != '') {
			$objTemplateGroupContent = new template_group_content($uid);
			$objTemplateGroupContent->load();
			$arrBody['uid'] = $uid;
			if(count($_POST) > 0) {
				if(($response = $objTemplateGroupContent->isValidUpdate())===true) {
					output::redirect(config::url('admin/article-template/groups/'.$this->template_uid.'/content/'.$this->template_group_uid.'/'));
				} else {
					$body->assign($objTemplateGroupContent->arrForm);
				}
			} else {
				foreach( $objTemplateGroupContent->TableData as $idx => $val ){
					$arrBody[$idx] = $val['Value'];
				}
				$body->assign($arrBody);
			}

		} else {
			output::redirect(config::url('admin/article-template/groups/'.$this->template_uid.'/content/'.$this->template_group_uid.'/'));
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
		$body			= make::tpl('body.admin.article.template.group.content.list');
		$objTemplateGroupContent	= new template_group_content();
		$arrGroups	= $objTemplateGroupContent->getList($this->template_uid,$this->template_group_uid);

		if(count($arrGroups) > 0) {
			$rows = array ();
			foreach($arrGroups as $uid=>$arrData) {
				$rows[] = make::tpl('body.admin.article.template.group.content.list.row')->assign($arrData)->get_content();
			}

			$page_display_title		= $objTemplateGroupContent->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');

			$page_navigation = $objTemplateGroupContent->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>');
			$page_navigation .= $objTemplateGroupContent->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>', ' &raquo ');
			$page_navigation .= $objTemplateGroupContent->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

			$body->assign('page.display.title'	, $page_display_title);
			$body->assign('page.navigation'		, $page_navigation);
			$body->assign('rows',implode('',$rows));
		}
		$body->assign(
			array(
				'template_uid'=>$this->template_uid
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
			$objTemplateGroupContent	= new template_group_content($this->arrPaths[7]);
			$objTemplateGroupContent->delete();
			$objTemplateGroupContent->redirectTo('admin/article-template/groups/'.$this->template_uid.'/content/'.$this->template_group_uid.'/');
		} else {
			output::redirect(config::url('admin/article-template/groups/'.$this->template_uid.'/content/'.$this->template_group_uid.'/'));
		}
	}
}

?>