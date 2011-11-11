<?php

class admin_article_groups extends Controller {

	private $token = 'list';
	private $arrTokens = array(
		'list',
		'content',
		'delete'
	);
	private $arrPaths = array();
	private $article_uid = null;
	private $article_group_uid = null;

	public function __construct() {
		parent::__construct();
		$this->arrPaths = config::get('paths');

		$this->isValidarticleUid();

		if (isset($this->arrPaths[4]) && in_array($this->arrPaths[4], $this->arrTokens)) {
			$this->token = $this->arrPaths[4];
		}
		if (in_array($this->token, $this->arrTokens)) {
			$method = 'do' . ucfirst($this->token);
			$this->$method();
		}
	}

	private function isValidarticleUid() {
		if (isset($this->arrPaths[3]) && is_numeric($this->arrPaths[3]) && $this->arrPaths[3] > 0) {
			$objarticle = new article($this->arrPaths[3]);
			if ($objarticle->get_valid()) {
				$this->article_uid = $this->arrPaths[3];
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
		if (isset($this->arrPaths[5]) && is_numeric($this->arrPaths[5]) && $this->arrPaths[5] > 0) {
			$objarticleGroup = new article_group($this->arrPaths[5]);
			if ($objarticleGroup->get_valid()) {
				$this->article_group_uid = $this->arrPaths[5];
			} else {
				// redirect back to article list
				output::redirect(config::url('admin/article/groups/' . $this->article_uid . '/list/'));
			}
		} else {
			// redirect back to article list
			output::redirect(config::url('admin/article/groups/' . $this->article_uid . '/list/'));
		}
	}

	private function doContent() {
		$this->load_controller('admin.article.groups.content');
	}

	protected function doEdit() {

		$skeleton = make::tpl('skeleton.admin');
		$body = make::tpl('body.admin.article.group.content.edit');
		$arrBody = array();
		$uid = (isset($this->arrPaths[7]) && (int) $this->arrPaths[7] > 0) ? $this->arrPaths[7] : '';

		if ($uid != '') {
			$objarticleGroupContent = new article_group_content($uid);
			$objarticleGroupContent->load();
			$arrBody['uid'] = $uid;
			if (count($_POST) > 0) {
				if (($response = $objarticleGroupContent->isValidUpdate()) === true) {
					output::redirect(config::url('admin/article/groups/' . $this->article_uid . '/content/' . $this->article_group_uid . '/'));
				} else {
					$body->assign($objarticleGroupContent->arrForm);
				}
			} else {
				foreach ($objarticleGroupContent->TableData as $idx => $val) {
					$arrBody[$idx] = $val['Value'];
				}
				$body->assign($arrBody);
			}
		} else {
			output::redirect(config::url('admin/article/groups/' . $this->article_uid . '/content/' . $this->article_group_uid . '/'));
		}

		$skeleton->assign(
				array(
					'body' => $body
				)
		);
		output::as_html($skeleton, true);
	}

	protected function doList() {
		$skeleton = make::tpl('skeleton.admin');
		$body = make::tpl('body.admin.article.group.list');
		$objarticleGroup = new article_group();
		$arrGroups = $objarticleGroup->getList($this->article_uid);

		if (count($arrGroups) > 0) {
			$rows = array();
			foreach ($arrGroups as $uid => $arrData) {
				$rows[] = make::tpl('body.admin.article.group.list.row')->assign($arrData)->get_content();
			}

			$page_display_title = $objarticleGroup->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');

			$page_navigation = $objarticleGroup->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>');
			$page_navigation .= $objarticleGroup->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>', ' &raquo ');
			$page_navigation .= $objarticleGroup->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

			$body->assign('page.display.title', $page_display_title);
			$body->assign('page.navigation', $page_navigation);
			$body->assign('rows', implode('', $rows));
		}
		$body->assign(
				array(
					'article_uid' => $this->article_uid
				)
		);
		$skeleton->assign(
				array(
					'body' => $body
				)
		);
		output::as_html($skeleton, true);
	}

	protected function doDelete() {
		if (isset($this->arrPaths[5]) && (int) $this->arrPaths[5] > 0) {
			$objarticleGroup = new article_group($this->arrPaths[5]);
			$objarticleGroup->delete();
			//$objarticleTranslation = new article_translation();
			//$objarticleTranslation->DeletearticleTranslation($this->arrPaths[3]);

			$objarticleGroup->redirectTo('admin/article/groups/' . $this->article_uid . '/list/');
		} else {
			output::redirect(config::url('admin/article/groups/' . $this->article_uid . '/list/'));
		}
	}

}

?>