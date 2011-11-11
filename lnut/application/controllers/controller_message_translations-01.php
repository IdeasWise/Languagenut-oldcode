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
				$arrTabs_li[] = '<li><a href="#tab-'.$uidPrefix.'"><span>'.$arrPrefix['prefix'].'</span></a></li>';

				/**
				 * Fetch Translations by Locale
				 */


				$arrTags = array();
				$arrTags[] = '<form action="'.config::admin_uri('message_translations/').'" method="post">';
				$arrTags[] = '<input type="hidden" name="locale" id="locale" value="'.$arrPrefix['prefix'].'" />';
				$arrTags[] = '<table width="100%" border="0" cellspacing="0" cellpadding="10" class="table_main"><tr><th>Short Code</th><th>'.$arrPrefix['name'].'</th></tr>';
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
					$arrTags[] = '<tr>';
					$arrTags[] = '<td>'.$arrRes[$i]['tag'].'</td>';
					$arrTags[] = '<td><input type="text" name="tags['.$arrRes[$i]['messageID'].']" value="'.stripslashes($arrRes[$i]['text']).'" class="box" size="65"/></td></tr>';
				}
				$arrTags[] = '</table>';
				$arrTags[] = '<div>
						<p style="text-align:center;">
							<input type="submit" class="com_btn" value="Save" name="form_submit_button" />
						</p>
					</div>
				</form>';
				
				$arrTabs_div[] = '<div id="tab-'.$uidPrefix.'">'.implode('',$arrTags).'</div>';
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