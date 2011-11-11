<?php

	class admin_referencematerialtype extends Controller {

		private $token = 'list';
		private $arrTokens = array(
		'list',
		'edit',
		'add',
		'delete'
		);
		private $parts = array();
		private $objreferencematerialtype = null;

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

			$body = new xhtml('body.admin.referencematerialtype.add');
			$body->load();
			$body->assign('title', 'Activities > <a href="{{ uri }}admin/referencematerialtype/list/">Material Type</a> > Add');
			if (count($_POST) > 0) {
				$this->objreferencematerialtype = new referencematerialtype();
				if (($response = $this->objreferencematerialtype->isValidCreate($_POST)) === true) {
					output::redirect(config::url('admin/referencematerialtype/list/'));
				} else {
					$body->assign($response);
				}
			}


			$tabs_li = array();
			$tabs_div = array();

			$arrLocales = language::getPrefixes();
			if (count($arrLocales) > 0) {
				foreach ($arrLocales as $uid => $arrData) {
					$lang_form = "";
					$nameVal = (isset($_POST['name_' . $arrData['prefix']])) ? $_POST['name_' . $arrData['prefix']] : "";

					$localeLi = new xhtml('admin.locale.li');
					$localeLi->load();

					$localeLi->assign("tab_id", "tab-");
					$localeLi->assign("uid", $uid);
					$localeLi->assign("prefix", $arrData['prefix']);

					$lang_form = new xhtml('admin.referencematerialtype.langform');
					$lang_form->load();
					$lang_form->assign("uid", $uid);
					$lang_form->assign("prefix", $arrData['prefix']);
					$lang_form->assign("name_value", $nameVal);

					$tabs_li[] = $localeLi->get_content();
					$tabs_div[] =$lang_form->get_content();

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

			$body = new xhtml('body.admin.referencematerialtype.edit');
			$body->load();
			$body->assign('title', 'Activities > <a href="{{ uri }}admin/referencematerialtype/list/">Material Type</a> > Update');
			$reference_material_type_uid = (isset($this->parts[3]) && (int) $this->parts[3] > 0) ? $this->parts[3] : '';

			if ($reference_material_type_uid != '') {

				$this->objreferencematerialtype = new referencematerialtype($reference_material_type_uid);
				$this->objreferencematerialtype->load();

				$arrqaetopic = $this->objreferencematerialtype->getFields();


				if (count($arrqaetopic) > 0) {

					if (count($_POST) > 0) {
						$_POST['reference_material_type_uid'] = $reference_material_type_uid;
						if (($arrqaetopic = $this->objreferencematerialtype->isValidUpdate($_POST)) === true) {
							output::redirect(config::url('admin/referencematerialtype/list/'));
						} else {
							$body->assign($arrqaetopic);
						}
					}

					$body->assign('referencematerialtype_uid', $reference_material_type_uid);
					$body->assign('name', $arrqaetopic['name']);
					$body->assign('token', $arrqaetopic['token']);
					$body->assign('available_select_0', ($arrqaetopic['available'] == "0") ? 'selected="selected"' : '');
					$body->assign('available_select_1', ($arrqaetopic['available'] == "1") ? 'selected="selected"' : '');
				}
			} else {
				output::redirect(config::url('admin/referencematerialtype/list/'));
			}

			$tabs_li = array();
			$tabs_div = array();

			$arrLocales = language::getPrefixes();

			if (count($arrLocales) > 0) {
				foreach ($arrLocales as $uid => $arrData) {
					$referencematerialtypeData = $this->objreferencematerialtype->getListByLocale($reference_material_type_uid, $uid);
					$lang_form = "";

					$nameVal = (isset($_POST['name_' . $arrData['prefix']])) ? $_POST['name_' . $arrData['prefix']] : $referencematerialtypeData["name"];

					$localeLi = new xhtml('admin.locale.li');
					$localeLi->load();

					$localeLi->assign("tab_id", "tab-");
					$localeLi->assign("uid", $uid);
					$localeLi->assign("prefix", $arrData['prefix']);

					$lang_form = new xhtml('admin.referencematerialtype.langform');
					$lang_form->load();
					$lang_form->assign("uid", $uid);
					$lang_form->assign("prefix", $arrData['prefix']);
					$lang_form->assign("name_value", $nameVal);

					$tabs_li[] = $localeLi->get_content();
					$tabs_div[] =$lang_form->get_content();

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
				$objreferencematerialtype = new referencematerialtype($this->parts[3]);

				$sql = "DELETE FROM `reference_material_type_translation`";
				$sql.=" WHERE ";
				$sql.=" reference_material_type_uid='{$this->parts[3]}'";
				$res = database::query($sql);

				$objreferencematerialtype->delete();

				$objreferencematerialtype->redirectTo('admin/referencematerialtype/list/');
			} else {
				output::redirect(config::url('admin/referencematerialtype/list/'));
			}
		}

		protected function doList() {
			$skeleton = new xhtml('skeleton.admin');
			$skeleton->load();
			$hide = 'style="visibility:hidden;"';
			$body = new xhtml('body.admin.referencematerialtype.list');
			$body->load();

			$this->objreferencematerialtype = new referencematerialtype();
			$arrreferencematerialtype = $this->objreferencematerialtype->getListByname();
			$i = 0;
			if ($arrreferencematerialtype && count($arrreferencematerialtype) > 0) {
				$rows = array();
				foreach ($arrreferencematerialtype as $reference_material_type_uid => $arrData) {
					$i++;

					$row = new xhtml('body.admin.referencematerialtype.list.row');
					$row->load();
					$row->assign(array(
					'referencematerialtype_skill_uid' => $reference_material_type_uid,
					'name' => stripslashes($arrData['name']),
					'available' => ($arrData['available'] == '0') ? 'No' : 'Yes'
					));
					$rows[] = $row->get_content();
				}
				$body->assign('rows', implode('', $rows));
			}
			$page_display_name = $this->objreferencematerialtype->get_page_name('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
			$page_navigation = $this->objreferencematerialtype->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>') . $this->objreferencematerialtype->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>', ' &raquo ') . $this->objreferencematerialtype->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');
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