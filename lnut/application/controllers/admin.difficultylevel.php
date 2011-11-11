<?php

	class admin_difficultylevel extends Controller {

		private $token = 'list';
		private $arrTokens = array(
		'list',
		'edit',
		'add',
		'delete'
		);
		private $parts = array();
		private $objdifficultylevel = null;

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

			$body = new xhtml('body.admin.difficultylevel.add');
			$body->load();
			$body->assign('title', 'Activities > <a href="{{ uri }}admin/difficultylevel/list/">Difficulty Levels</a> > Add');
			if (count($_POST) > 0) {
				$this->objdifficultylevel = new difficultylevel();
				if (($response = $this->objdifficultylevel->isValidCreate($_POST)) === true) {
					output::redirect(config::url('admin/difficultylevel/list/'));
				} else {
					$body->assign($response);
				}
			}

			$tabs_li = array();
			$tabs_div = array();

			$arrLocales = language::getPrefixes();
			if (count($arrLocales) > 0) {
				foreach ($arrLocales as $uid => $arrData) {
					//				$lang_form = "";
					$nameVal = (isset($_POST['name_' . $arrData['prefix']])) ? $_POST['name_' . $arrData['prefix']] : "";

					$localeLi = new xhtml('admin.locale.li');
					$localeLi->load();

					$localeLi->assign("tab_id", "tab-");
					$localeLi->assign("uid", $uid);
					$localeLi->assign("prefix", $arrData['prefix']);

					$lang_form = new xhtml('admin.difficultylevel.langform');
					$lang_form->load();
					$lang_form->assign("uid", $uid);
					$lang_form->assign("prefix", $arrData['prefix']);
					$lang_form->assign("name_value", $nameVal);

					$tabs_li[] = $localeLi->get_content();
					$tabs_div[] =$lang_form->get_content();

					//				$tabs_li[] = '<li><a href="#tab-' . $uid . '"><span>' . $arrData['prefix'] . '</span></a></li>';
					//				$lang_form.='<table>';
					//				$lang_form.='<tr>';
					//				$lang_form.='<td>';
					//				$lang_form.='name';
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

			$body = new xhtml('body.admin.difficultylevel.edit');
			$body->load();
			$body->assign('title', 'Activities > <a href="{{ uri }}admin/difficultylevel/list/">Difficulty Levels</a> > Update');
			$difficulty_level_uid = (isset($this->parts[3]) && (int) $this->parts[3] > 0) ? $this->parts[3] : '';

			if ($difficulty_level_uid != '') {

				$this->objdifficultylevel = new difficultylevel($difficulty_level_uid);
				$this->objdifficultylevel->load();

				$arrqaetopic = $this->objdifficultylevel->getFields();


				if (count($arrqaetopic) > 0) {

					if (count($_POST) > 0) {
						$_POST['difficulty_level_uid'] = $difficulty_level_uid;
						if (($arrqaetopic = $this->objdifficultylevel->isValidUpdate($_POST)) === true) {
							output::redirect(config::url('admin/difficultylevel/list/'));
						} else {
							$body->assign($arrqaetopic);
						}
					}

					$body->assign('difficultylevel_uid', $difficulty_level_uid);
					$body->assign('name', $arrqaetopic['name']);
					$body->assign('token', $arrqaetopic['token']);
					$body->assign('available_select_0', ($arrqaetopic['available'] == "0") ? 'selected="selected"' : '');
					$body->assign('available_select_1', ($arrqaetopic['available'] == "1") ? 'selected="selected"' : '');
				}
			} else {
				output::redirect(config::url('admin/difficultylevel/list/'));
			}

			$tabs_li = array();
			$tabs_div = array();

			$arrLocales = language::getPrefixes();

			if (count($arrLocales) > 0) {
				foreach ($arrLocales as $uid => $arrData) {
					$difficultylevelData = $this->objdifficultylevel->getListByLocale($difficulty_level_uid, $uid);
					$lang_form = "";

					$nameVal = (isset($_POST['name_' . $arrData['prefix']])) ? $_POST['name_' . $arrData['prefix']] : $difficultylevelData["name"];

					$localeLi = new xhtml('admin.locale.li');
					$localeLi->load();

					$localeLi->assign("tab_id", "tab-");
					$localeLi->assign("uid", $uid);
					$localeLi->assign("prefix", $arrData['prefix']);

					$lang_form = new xhtml('admin.difficultylevel.langform');
					$lang_form->load();
					$lang_form->assign("uid", $uid);
					$lang_form->assign("prefix", $arrData['prefix']);
					$lang_form->assign("name_value", $nameVal);

					$tabs_li[] = $localeLi->get_content();
					$tabs_div[] =$lang_form->get_content();

					//				$tabs_li[] = '<li><a href="#tab-' . $uid . '"><span>' . $arrData['prefix'] . '</span></a></li>';
					//				$lang_form.='<table>';
					//				$lang_form.='<tr>';
					//				$lang_form.='<td>';
					//				$lang_form.='name';
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
				$objdifficultylevel = new difficultylevel($this->parts[3]);

				$sql = "DELETE FROM `difficulty_level_translation`";
				$sql.=" WHERE ";
				$sql.=" difficulty_level_uid='{$this->parts[3]}'";
				$res = database::query($sql);

				$objdifficultylevel->delete();

				$objdifficultylevel->redirectTo('admin/difficultylevel/list/');
			} else {
				output::redirect(config::url('admin/difficultylevel/list/'));
			}
		}

		protected function doList() {
			$skeleton = new xhtml('skeleton.admin');
			$skeleton->load();
			$hide = 'style="visibility:hidden;"';
			$body = new xhtml('body.admin.difficultylevel.list');
			$body->load();

			$this->objdifficultylevel = new difficultylevel();
			$arrdifficultylevel = $this->objdifficultylevel->getListByname();
			$i = 0;
			if ($arrdifficultylevel && count($arrdifficultylevel) > 0) {
				$rows = array();
				foreach ($arrdifficultylevel as $difficulty_level_uid => $arrData) {
					$i++;

					$row = new xhtml('body.admin.difficultylevel.list.row');
					$row->load();
					$row->assign(array(
					'difficultylevel_skill_uid' => $difficulty_level_uid,
					'name' => stripslashes($arrData['name']),
					'available' => ($arrData['available'] == '0') ? 'No' : 'Yes'
					));
					$rows[] = $row->get_content();
				}
				$body->assign('rows', implode('', $rows));
			}
			$page_display_name = $this->objdifficultylevel->get_page_name('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
			$page_navigation = $this->objdifficultylevel->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>') . $this->objdifficultylevel->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>', ' &raquo ') . $this->objdifficultylevel->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');
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