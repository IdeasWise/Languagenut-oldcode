<?php

	class admin_game_translations extends Controller {

		private $token				= 'list';

		private $arrTokens			= array (
		'list',
		'edit',
		'add',
		'delete'
		);

		private $parts				= array();

		private $objGame			= null;
		private $objGameTranslation	= null;

		public function __construct () {
			parent::__construct();
			$this->parts = config::get('paths');

			if(isset($this->parts[2]) && in_array($this->parts[2], $this->arrTokens)) {
				$this->token =  $this->parts[2];
			}

			if(in_array($this->token,$this->arrTokens)) {
				$method = 'do' . ucfirst($this->token);
				$this->$method();
			}
		}

		protected function doAdd() {
			$skeleton = new xhtml ('skeleton.admin');
			$skeleton->load();

			$body = new xhtml('body.admin.games.add');
			$body->load();

			if(count($_POST) > 0) {
				$this->objGame = new game();
				if(($response=$this->objGame->isValidCreate($_POST))===true) {
					output::redirect(config::url('account/games/list/'));
				} else {
					$body->assign($response);
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

			$skeleton = new xhtml ('skeleton.admin');
			$skeleton->load();

			$body = new xhtml('body.admin.games.edit');
			$body->load();

			$game_uid = (isset($this->parts[3]) && (int)$this->parts[3] > 0) ? $this->parts[3] : '';

			if($game_uid != '') {

				$this->objGame = new game($game_uid);
				$this->objGame->load();

				$arrGame = $this->objGame->getFields();

				if(count($arrGame) > 0) {

					if(count($_POST) > 0) {
						$_POST['game_uid'] = $game_uid;
						if(($arrGame = $this->objGame->isValidUpdate($_POST))===true) {
							output::redirect(config::url('account/games/list/'));
						}
					}

					$body->assign('game_uid',$game_uid);
					$body->assign('name',$arrGame['name']);
					$body->assign('trynow_yes',($arrGame['active_trynow']==1 ? ' checked="checked"' : ''));
					$body->assign('trynow_no',($arrGame['active_trynow']==1 ? '' : ' checked="checked"'));
					$body->assign('subscription_yes',($arrGame['active_subscription']==1 ? ' checked="checked"' : ''));
					$body->assign('subscription_no',($arrGame['active_subscription']==1 ? '' : ' checked="checked"'));
				}

			} else {
				output::redirect(config::url('account/games/list/'));
			}

			$skeleton->assign (
			array (
			'body' => $body
			)
			);
			output::as_html($skeleton,true);
		}

		protected function doDelete() {

			if(isset($this->parts[3]) && (int)$this->parts[3] > 0) {
				$objGame = new game($this->parts[3]);
				$objGame->delete();
				$objGame->redirectTo('account/games/list/');
			} else {
				output::redirect(config::url('account/games/list/'));
			}

		}

		protected function doList () {

			if(count($_POST) > 0) {
				foreach($_POST as $key=>$val) {
					$name = explode('_',$key);
					if(count($name)==3 && $name[0]=='game') {
						$game_uid = (int)$name[1];
						$language_uid = (int)$name[2];

						$query = "SELECT COUNT(`uid`) FROM `game_translation` WHERE `game_uid`='".$game_uid."' AND `language_uid`='".$language_uid."' LIMIT 1";
						$result = database::query($query,1);

						if($result && mysql_error()=='') {
							$row = mysql_fetch_array($result);

							if($row[0] > 0) {
								$query = "UPDATE `game_translation` SET `name`='".mysql_real_escape_string($val)."' WHERE `language_uid`='".$language_uid."' AND `game_uid`='".$game_uid."'";
								$result = database::query($query,1);
								echo mysql_error();
							} else {
								$query = "INSERT INTO `game_translation` (`game_uid`,`language_uid`,`name`) VALUES ('".$game_uid."','".$language_uid."', '".mysql_real_escape_string($val)."')";
								$result = database::query($query,1);
								echo mysql_error();
							}

						}
					}
				}
				output::redirect(config::url('account/game_translations/list/'));
			}

			$arrLocales = profile_translator::getPrefixes();

			$objGame = new game();
			$arrGames = $objGame->getListByName();

			$tabs_li = array();
			$tabs_div= array();

			if(count($arrLocales) > 0) {
				foreach($arrLocales as $uid=>$arrData) {

					$localeLi = new xhtml('admin.locale.li');
					$localeLi->load();

					$localeLi->assign("tab_id", "tab-");
					$localeLi->assign("uid", $uid);
					$localeLi->assign("prefix", $arrData['prefix']);

					$tabs_li[] = $localeLi->get_content();

					$arrTranslations = game_translation::getByLanguageUid($uid);

					$arrHtml = array();
					$arrHtml[] = '<table width="100%" border="0" cellspacing="0" cellpadding="10" class="table_main"><tr><th>Game Name</th><th>'.$arrData['name'].'</th></tr>';
					foreach($arrGames as $game_uid=>$arrGame) {
						$arrHtml[] = '<tr><td>'.$arrGame['name'].'</td>';
						$found = false;
						foreach($arrTranslations as $translation_uid=>$arrTranslation) {
							if($arrTranslation['game_uid']==$game_uid) {
								$arrHtml[] = '<td><input type="text" name="game_'.$game_uid.'_'.$uid.'" id="game_'.$game_uid.'_'.$uid.'" value="'.$arrTranslation['name'].'" class="box" /></td>';
								$found = true;
							}
						}
						if(!$found) {
							$arrHtml[] = '<td><input type="text" name="game_'.$game_uid.'_'.$uid.'" id="game_'.$game_uid.'_'.$uid.'" value="" class="box" /></td>';
						}
						$arrHtml[] = '</tr>';
					}
					$arrHtml[] = '</table>';
					$tabs_div[] = '<div id="tab-'.$uid.'">'.implode('',$arrHtml).'</div>';
				}
			}

			$skeleton	= new xhtml ('skeleton.account.translator');
			$skeleton->load();

			$body		= new xhtml('body.account.game_translations.list');
			$body->load();

			$body->assign(
			array(
			'tabs'	=> implode('',$tabs_div),
			'locales'=>implode('',$tabs_li),
			'form.action'	=> config::url('account/game_translations/')
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