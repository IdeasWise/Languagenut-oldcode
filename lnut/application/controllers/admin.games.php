<?php

class admin_games extends Controller {

	private $token				= 'list';
	private $arrTokens			= array (
		'list',
		'edit',
		'add',
		'delete',
		'move'
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

	protected function doMove() {
		if( isset($this->parts[3]) && is_numeric($this->parts[3]) && $this->parts[3] > 0 && isset($this->parts[4]) ) {
			$objGame = new game($this->parts[3]);
			$objGame->load();

			if(trim($this->parts[4]) == 'up' && $objGame->get_game_number() != 1 ) {
				$query ="SELECT ";
				$query.="`uid` ";
				$query.="FROM ";
				$query.="`game` ";
				$query.="WHERE ";
				$query.="`game_number` = '".($objGame->get_game_number()-1)."' ";
				$query.="LIMIT 0,1";

				$result = database::query($query);
				if($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
					$row = mysql_fetch_array( $result );
					$query ="UPDATE ";
					$query.="`game` ";
					$query.="SET ";
					$query.="`game_number` = '".$objGame->get_game_number()."' ";
					$query.="WHERE ";
					$query.="`uid` = '".$row ['uid']."'";
					$query.="LIMIT 1";
					database::query( $query );
					$objGame->set_game_number($objGame->get_game_number()-1);
					$objGame->save();
				}
			}

			if(trim($this->parts[4]) == 'down' ) {
				$query ="SELECT ";
				$query.="`uid`, ";
				$query.="(";
					$query.="SELECT ";
					$query.="max(`game_number`) ";
					$query.="FROM ";
					$query.="`game`";
				$query.=") AS `mx` ";
				$query.="FROM ";
				$query.="`game` ";
				$query.="WHERE ";
				$query.="`game_number` = '".($objGame->get_game_number()+1)."' ";
				$query.="LIMIT 0,1";
				$result = database::query($query);
				if($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
					$row = mysql_fetch_array( $result );

					if($row['mx'] != $objGame->get_game_number() ) {
						$query = "UPDATE ";
						$query.="`game` ";
						$query.="SET ";
						$query.="`game_number` = '".($objGame->get_game_number())."' ";
						$query.="WHERE ";
						$query.="`uid` = '".$row ['uid']."'";
						$query.="LIMIT 1 ";
						database::query( $query );
						$objGame->set_game_number($objGame->get_game_number()+1);
						$objGame->save();
					}
				}
			}
		}
		output::redirect(config::url('admin/games/list/'));
	}

	protected function doAdd() {
		$skeleton	= make::tpl ('skeleton.admin');
		$body		= make::tpl('body.admin.games.add');

		if(count($_POST) > 0) {
			$objGame = new game();
			if(($response=$objGame->isValidCreate($_POST))===true) {
				output::redirect(config::url('admin/games/list/'));
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
		$skeleton	= make::tpl ('skeleton.admin');
		$body		= make::tpl ('body.admin.games.edit');
		$game_uid	= (isset($this->parts[3]) && (int)$this->parts[3] > 0) ? $this->parts[3] : '';

		if($game_uid != '') {
			$objGame = new game($game_uid);
			$objGame->load();
			$arrGame = $objGame->getFields();

			if(count($arrGame) > 0) {
				if(count($_POST) > 0) {
					$_POST['game_uid'] = $game_uid;
					if(($arrGame = $objGame->isValidUpdate($_POST))===true) {
						output::redirect(config::url('admin/games/list/'));
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
			output::redirect(config::url('admin/games/list/'));
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
			$objGame->redirectTo('admin/games/list/');
		} else {
			output::redirect(config::url('admin/games/list/'));
		}
	}

	protected function doList () {
		$skeleton	= make::tpl ('skeleton.admin');
		$hide		= 'style="visibility:hidden;"';
		$body		= make::tpl ('body.admin.games.list');
		$objGame	= new game();
		$arrGames	= $objGame->getListByName('game_number');
		$i			= 0;
		if($arrGames && count($arrGames) > 0) {
			$arrRows = array ();
			foreach($arrGames as $game_uid=>$arrData) {
				$i++;
				$styleUp = '';
				$styleDown = '';
				if($i == 1) $styleUp = $hide;
				if( $i == count($arrGames)  )  $styleDown = $hide;

				$arrRows[] = make::tpl('body.admin.games.list.row')->assign(array(
					'styleUp'		=> $styleUp,
					'styleDown'		=> $styleDown,
					'game_uid'		=> $game_uid,
					'name'			=> stripslashes($arrData['name']),					
					'trynow'		=> format::to_yesno_graphic($arrData['trynow']),
					'subscription'	=> format::to_yesno_graphic($arrData['subscription'])
				))->get_content();
			}
			$body->assign('rows',implode('',$arrRows));
		}

		$skeleton->assign (
			array (
				'body' => $body
			)
		);
		output::as_html($skeleton,true);
	}
}

?>