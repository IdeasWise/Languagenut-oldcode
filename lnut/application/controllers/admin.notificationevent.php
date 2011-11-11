<?php

	class admin_notificationevent extends Controller {

		private $token = 'list';
		private $arrTokens = array(
		'list',
		'edit',
		'add',
		'delete'

		);
		private $parts = array();
		private $objnotificationevent = null;

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

			$body = new xhtml('body.admin.notificationevent.add');
			$body->load();

			if (count($_POST) > 0) {
				$this->objnotificationevent = new notificationevent();
				if (($response = $this->objnotificationevent->isValidCreate($_POST)) === true) {
					output::redirect(config::url('admin/notificationevent/list/'));
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
					$messageVal = (isset($_POST['message_' . $arrData['prefix']])) ? $_POST['message_' . $arrData['prefix']] : "";

					$localeLi = new xhtml('admin.locale.li');
					$localeLi->load();

					$localeLi->assign("tab_id", "tab-");
					$localeLi->assign("uid", $uid);
					$localeLi->assign("prefix", $arrData['prefix']);

					$lang_form = new xhtml('admin.activity.langform');
					$lang_form->load();
					$lang_form->assign("uid", $uid);
					$lang_form->assign("prefix", $arrData['prefix']);
					$lang_form->assign("name_value", $nameVal);
					$lang_form->assign("message_value", $messageVal);

					$tabs_li[] = $localeLi->get_content();
					$tabs_div[] = $lang_form->get_content();


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

			$body = new xhtml('body.admin.notificationevent.edit');
			$body->load();

			$notification_event_uid = (isset($this->parts[3]) && (int) $this->parts[3] > 0) ? $this->parts[3] : '';

			if ($notification_event_uid != '') {

				$this->objnotificationevent = new notificationevent($notification_event_uid);
				$this->objnotificationevent->load();

				$arrqaetopic = $this->objnotificationevent->getFields();


				if (count($arrqaetopic) > 0) {

					if (count($_POST) > 0) {
						$_POST['notification_event_uid'] = $notification_event_uid;
						if (($arrqaetopic = $this->objnotificationevent->isValidUpdate($_POST)) === true) {
							output::redirect(config::url('admin/notificationevent/list/'));
						} else {
							$body->assign($arrqaetopic);
						}
					}

					$body->assign('notificationevent_uid', $notification_event_uid);
					$body->assign('name', $arrqaetopic['name']);
					$body->assign('token', $arrqaetopic['token']);
					$body->assign('description', $arrqaetopic['description']);

				}
			} else {
				output::redirect(config::url('admin/notificationevent/list/'));
			}

			$tabs_li = array();
			$tabs_div = array();

			$arrLocales = language::getPrefixes();

			if (count($arrLocales) > 0) {
				foreach ($arrLocales as $uid => $arrData) {
					$notificationeventData = $this->objnotificationevent->getListByLocale($notification_event_uid, $uid);
					$lang_form = "";

					$nameVal = (isset($_POST['name_' . $arrData['prefix']])) ? $_POST['name_' . $arrData['prefix']] : $notificationeventData["name"];
					$messageVal = (isset($_POST['message_' . $arrData['prefix']])) ? $_POST['message_' . $arrData['prefix']] : $notificationeventData["message"];

					$localeLi = new xhtml('admin.locale.li');
					$localeLi->load();

					$localeLi->assign("tab_id", "tab-");
					$localeLi->assign("uid", $uid);
					$localeLi->assign("prefix", $arrData['prefix']);

					$lang_form = new xhtml('admin.activity.langform');
					$lang_form->load();
					$lang_form->assign("uid", $uid);
					$lang_form->assign("prefix", $arrData['prefix']);
					$lang_form->assign("name_value", $nameVal);
					$lang_form->assign("message_value", $messageVal);

					$tabs_li[] = $localeLi->get_content();
					$tabs_div[] = $lang_form->get_content();


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
				$objnotificationevent = new notificationevent($this->parts[3]);

				$sql = "DELETE FROM `notification_event_translation`";
				$sql.=" WHERE ";
				$sql.=" notification_event_uid='{$this->parts[3]}'";
				$res = database::query($sql);

				$objnotificationevent->delete();

				$objnotificationevent->redirectTo('admin/notificationevent/list/');
			} else {
				output::redirect(config::url('admin/notificationevent/list/'));
			}
		}

		protected function doList() {
			$skeleton = new xhtml('skeleton.admin');
			$skeleton->load();
			$hide = 'style="visibility:hidden;"';
			$body = new xhtml('body.admin.notificationevent.list');
			$body->load();

			$this->objnotificationevent = new notificationevent();
			$arrnotificationevent = $this->objnotificationevent->getListByname();
			$i = 0;
			if ($arrnotificationevent && count($arrnotificationevent) > 0) {
				$rows = array();
				foreach ($arrnotificationevent as $notification_event_uid => $arrData) {
					$i++;

					$row = new xhtml('body.admin.notificationevent.list.row');
					$row->load();
					$row->assign(array(
					'notificationevent_skill_uid' => $notification_event_uid,
					'name' => stripslashes($arrData['name']),

					));
					$rows[] = $row->get_content();
				}
				$body->assign('rows', implode('', $rows));
			}
			$page_display_name = $this->objnotificationevent->get_page_name('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
			$page_navigation = $this->objnotificationevent->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>') . $this->objnotificationevent->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>', ' &raquo ') . $this->objnotificationevent->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');
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