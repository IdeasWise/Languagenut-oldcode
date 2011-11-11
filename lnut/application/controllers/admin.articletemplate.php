<?php

	class admin_articletemplate extends Controller {

		private $token = 'list';
		private $arrTokens = array(
		'list',
		'edit',
		'add',
		'delete'

		);
		private $parts = array();
		private $objarticletemplate = null;

		public function __construct() {

			parent::__construct();

			$this->parts = config::get('paths');

			if (isset($this->parts[2]) && in_array($this->parts[2], $this->arrTokens)) {
				$this->token = $this->parts[2];
			}

			if (in_array($this->token, $this->arrTokens)) {
				$method = 'do' . ucfirst($this->token);
				$this->$method();
			}
		}

		protected function doAdd() {
			$skeleton = new xhtml('skeleton.admin');
			$skeleton->load();

			$body = new xhtml('body.admin.articletemplate.add');
			$body->load();
			$body->assign('title', 'Activities > <a href="{{ uri }}admin/articletemplate/list/">Article Template</a> > Add');
			if (count($_POST) > 0) {
				$this->objarticletemplate = new articletemplate();
				if (($response = $this->objarticletemplate->isValidCreate($_POST)) === true) {
					output::redirect(config::url('admin/articletemplate/list/'));
				} else {
					$body->assign($response);
				}
			}


			$tabs_li = array();
			$tabs_div = array();

			$arrLocales = language::getPrefixes();
			if (count($arrLocales) > 0) {
				foreach ($arrLocales as $uid => $arrData) {

					$nameVal = (isset($_POST['name_' . $arrData['prefix']])) ? $_POST['name_' . $arrData['prefix']] : "";

					$localeLi = new xhtml('admin.locale.li');
					$localeLi->load();

					$localeLi->assign("tab_id", "tab-");
					$localeLi->assign("uid", $uid);
					$localeLi->assign("prefix", $arrData['prefix']);

					$tabs_li[] = $localeLi->get_content();

					$lang_form = new xhtml('admin.activity.langform');
					$lang_form->load();
					$lang_form->assign("uid", $uid);
					$lang_form->assign("prefix", $arrData['prefix']);
					$lang_form->assign("name_value",$nameVal);
					$tabs_div[] = $lang_form->get_content();

					//				$tabs_li[] = '<li><a href="#tab-' . $uid . '"><span>' . $arrData['prefix'] . '</span></a></li>';
					//				$lang_form.='<table>';
					//				$lang_form.='<tr>';
					//				$lang_form.='<td>';
					//				$lang_form.='Title';
					//				$lang_form.='</td>';
					//				$lang_form.='<td>';
					//				$lang_form.='<input type="text" name="name_' . $arrData['prefix'] . '" value="' . $nameVal . '" >';
					//				$lang_form.='</td>';
					//				$lang_form.='</tr>';
					//
					//				$lang_form.='</table>';
					//				$tabs_div[] = '<div id="tab-' . $uid . '">' . $lang_form . '</div>';
				}
			}

			$body->assign(
			array(
			'tabs' => implode('', $tabs_div),
			'locales' => implode('', $tabs_li),
			)
			);
			$skeleton->assign(
			array(
			'body' => $body
			)
			);
			output::as_html($skeleton, true);
		}

		protected function doEdit() {

			$skeleton = new xhtml('skeleton.admin');
			$skeleton->load();

			$body = new xhtml('body.admin.articletemplate.edit');
			$body->load();
			$body->assign('title', 'Activities > <a href="{{ uri }}admin/articletemplate/list/">Article Template</a> > Update');
			$article_template_uid = (isset($this->parts[3]) && (int) $this->parts[3] > 0) ? $this->parts[3] : '';

			if ($article_template_uid != '') {

				$this->objarticletemplate = new articletemplate($article_template_uid);
				$this->objarticletemplate->load();

				$arrqaetopic = $this->objarticletemplate->getFields();


				if (count($arrqaetopic) > 0) {

					if (count($_POST) > 0) {
						$_POST['article_template_uid'] = $article_template_uid;
						if (($arrqaetopic = $this->objarticletemplate->isValidUpdate($_POST)) === true) {
							output::redirect(config::url('admin/articletemplate/list/'));
						} else {
							$body->assign($arrqaetopic);
						}
					}

					$body->assign('articletemplate_uid', $article_template_uid);
					$body->assign('name', $arrqaetopic['title']);
					$body->assign('token', $arrqaetopic['token']);
					$body->assign('available_select_0', ($arrqaetopic['available']=="0")?'selected="selected"':'');
					$body->assign('available_select_1', ($arrqaetopic['available']=="1")?'selected="selected"':'');

				}
			} else {
				output::redirect(config::url('admin/articletemplate/list/'));
			}

			$tabs_li = array();
			$tabs_div = array();

			$arrLocales = language::getPrefixes();

			if (count($arrLocales) > 0) {
				foreach ($arrLocales as $uid => $arrData) {
					$articletemplateData = $this->objarticletemplate->getListByLocale($article_template_uid, $uid);
					//				$lang_form = "";

					$nameVal = (isset($_POST['name_' . $arrData['prefix']])) ? $_POST['name_' . $arrData['prefix']] : $articletemplateData["name"];

					$localeLi = new xhtml('admin.locale.li');
					$localeLi->load();

					$localeLi->assign("tab_id", "tab-");
					$localeLi->assign("uid", $uid);
					$localeLi->assign("prefix", $arrData['prefix']);

					$tabs_li[] = $localeLi->get_content();

					$lang_form = new xhtml('admin.activity.langform');
					$lang_form->load();
					$lang_form->assign("uid", $uid);
					$lang_form->assign("prefix", $arrData['prefix']);
					$lang_form->assign("name_value",$nameVal);
					$tabs_div[] = $lang_form->get_content();

					//				$tabs_li[] = '<li><a href="#tab-' . $uid . '"><span>' . $arrData['prefix'] . '</span></a></li>';
					//				$lang_form.='<table>';
					//				$lang_form.='<tr>';
					//				$lang_form.='<td>';
					//				$lang_form.='Title';
					//				$lang_form.='</td>';
					//				$lang_form.='<td>';
					//				$lang_form.='<input type="text" name="name_' . $arrData['prefix'] . '" value="' . $nameVal . '" >';
					//				$lang_form.='</td>';
					//				$lang_form.='</tr>';
					////
					//				$lang_form.='</table>';
					//				$tabs_div[] = '<div id="tab-' . $uid . '">' . $lang_form . '</div>';
				}
			}

			$body->assign(
			array(
			'tabs' => implode('', $tabs_div),
			'locales' => implode('', $tabs_li),
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

			if (isset($this->parts[3]) && (int) $this->parts[3] > 0) {
				$objarticletemplate = new articletemplate($this->parts[3]);

				$sql = "DELETE FROM `article_template_translation`";
				$sql.=" WHERE ";
				$sql.=" article_template_uid='{$this->parts[3]}'";
				$res = database::query($sql);

				$objarticletemplate->delete();

				$objarticletemplate->redirectTo('admin/articletemplate/list/');
			} else {
				output::redirect(config::url('admin/articletemplate/list/'));
			}
		}

		protected function doList() {
			$skeleton = new xhtml('skeleton.admin');
			$skeleton->load();
			$hide = 'style="visibility:hidden;"';
			$body = new xhtml('body.admin.articletemplate.list');
			$body->load();

			$this->objarticletemplate = new articletemplate();
			$arrarticletemplate = $this->objarticletemplate->getListByname();
			$i = 0;
			if ($arrarticletemplate && count($arrarticletemplate) > 0) {
				$rows = array();
				foreach ($arrarticletemplate as $article_template_uid => $arrData) {
					$i++;

					$row = new xhtml('body.admin.articletemplate.list.row');
					$row->load();
					$row->assign(array(
					'articletemplate_skill_uid' => $article_template_uid,
					'title' => stripslashes($arrData['title']),
					'available' => ($arrData['available']=='0')?'No':'Yes'
					));
					$rows[] = $row->get_content();
				}
				$body->assign('rows', implode('', $rows));
			}
			$page_display_name = $this->objarticletemplate->get_page_name('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
			$page_navigation = $this->objarticletemplate->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>') . $this->objarticletemplate->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>', ' &raquo ') . $this->objarticletemplate->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');
			$body->assign('page.display.name', $page_display_name);
			$body->assign('page.navigation', $page_navigation);
			$skeleton->assign(
			array(
			'body' => $body
			)
			);

			output::as_html($skeleton, true);
		}

	}

?>