<?php

class controller_message_translations extends Controller {

	public function __construct () {
		$this->index();
	}

	protected function index() {
		if(isset($_POST['form_submit_button'])) {
			page_messages::updatePageMessageTranslations();
			output::redirect(config::admin_uri('message_translations/'));
		}

		if(isset($_SESSION['user']['admin']) && $_SESSION['user']['admin'] == 1) {
			$skeleton	= make::tpl ('skeleton.admin');
		} else {
			$skeleton	= make::tpl ('skeleton.account.translator');
		}

		$array		= array();
		$page_rows	= array();
		$body		= make::tpl ('body.admin.message_translations.list');

		/**
		 * Fetch Locales
		 */
		$arrPrefixes = language::getPrefixes();

		$arrTabs_li = array();
		$arrTabs_div= array();

		if(count($arrPrefixes) > 0) {
			foreach($arrPrefixes as $uidPrefix=>$arrPrefix) {
				$arrTabs_li[] = make::tpl('body.admin.tabs.li')->assign(
					array(
						'tab_id'	=>$uidPrefix,
						'lable'		=>$arrPrefix['prefix']
					)
				)->get_content();

				/**
				 * Fetch Translations by Locale
				 */

				$arrForm = array();
				$query  = "SELECT ";
				$query .= "`uid` AS `messageID`, ";
				$query .= "`tag`, ";
//				$query .= "`description`, ";
				$query .= "( ";
							$query .= "SELECT ";
							$query .= "`text` ";
							$query .= "FROM ";
							$query .= "`page_messages_translations` ";
							$query .= "WHERE ";
							$query .= "`locale` = '".$arrPrefix['prefix']."' ";
							$query .= "AND ";
							$query .= "`message_uid` = `messageID` ";
				$query .= " ) AS `text` ";
				$query .= "FROM ";
				$query .= "`page_messages` ";
				
				$arrRes = database::arrQuery($query);
				/*echo '<pre>';
				print_r($arrRes);
				exit;*/
				$loopCount = count($arrRes);
				for( $i=0; $i < $loopCount; $i++ ) {
					$arrForm[] = make::tpl('body.message.translations.form.row')->assign(
						array(
							'tag'		=>$arrRes[$i]['tag'],
							'messageID'	=>$arrRes[$i]['messageID'],
							'text'		=>stripslashes($arrRes[$i]['text'])
						)
					)->get_content();
				}
				$MessageTable=make::tpl('body.message.translations.form')->assign(
					array(
						'prefix'		=>$arrPrefix['prefix'],
						'language_name'	=>$arrPrefix['name'],
						'table_content'	=>implode('',$arrForm)
					)
				);
				//$arrTabs_div[] = '<div id="tab-'.$uidPrefix.'">'.implode('',$arrTags).'</div>';
				$arrTabs_div[] = make::tpl('body.admin.tabs.div')->assign(
					array(
						'tab_id'		=>$uidPrefix,
						'tab_content'	=>$MessageTable->get_content()
					)
				)->get_content();
			}
		}

		$body->assign(
			array(
				'tabs'	=> implode('',$arrTabs_div),
				'locales'=>implode('',$arrTabs_li),
				'form.action'	=> config::admin_uri('message_translations/')
			)
		);

		$skeleton->assign (
			array (
				'body' => $body
			)
		);

		output::as_html($skeleton,true);
	}
}
?>