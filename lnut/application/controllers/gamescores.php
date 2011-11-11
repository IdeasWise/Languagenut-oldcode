<?php

/**
 * gamescores.php
 */

class GameScores extends Controller {

	public $testmode	= false;

	public function __construct () {
		parent::__construct();

		//$this->testmode = true;

		if(true===$this->testmode) {
			$_SESSION['user']['uid'] = 24;
		}

		if(isset($_GET['getscores'])) {
			$this->fetchScoresBySession();
		} else {
			$this->parseScoreJson();
		}
	}

	protected function fetchScoresBySession() {
		if(isset($_SESSION['user']) && isset($_SESSION['user']['uid'])) {
			$user_uid		= $_SESSION['user']['uid'];
			$game_uid		= (isset($_GET['game_uid']) && (int)$_GET['game_uid'] > 0) ? $_GET['game_uid'] : '';
			$language_uid	= (isset($_GET['language_uid']) && (int)$_GET['language_uid'] > 0) ? $_GET['language_uid'] : '';

			if($user_uid != '' && $game_uid != '' && $language_uid != '') {
				$objGameScore	= new gamescore();
				$arrScores		= $objGameScore->getTopByUserGameLanguage($user_uid,$game_uid,$language_uid);
				echo json_encode($arrScores);
			} else {
				echo json_encode(array('result'=>'insufficient data'));
			}
		} else {
			echo json_encode(array('result'=>'no scores'));
		}
	}

	protected function parseScoreJson () {

		//mail('workstation@mystream.co.uk','json parse request',print_r($_POST,true),'From: developer@languagenut.com');

		if(isset($_POST['scoreData'])||isset($_POST['gameData'])) {
			$jsonScore = (isset($_POST['scoreData']) ? $_POST['scoreData'] : $_POST['gameData']);

			$objScore = json_decode($jsonScore);

			$objGameScore = new gamescore();

			if(($response=$objGameScore->create($objScore))!==false) {

				// calculate score value based on the game uid
				$scoreNum = -1;
				if($response['game_uid']==1) {
					$scoreNum = ($response['score_right']*100/($response['score_right']+$response['score_wrong']));
				} else if($response['game_uid']==2) {
					$scoreNum = ($response['score_right']*100/($response['score_right']+$response['score_wrong']));
				} else if($response['game_uid']==3) {
					$scoreNum = ($response['score_right']*100/($response['score_right']+$response['score_wrong']));
				} else if($response['game_uid']==4) {
					$scoreNum = ($response['score_right']*100/($response['score_right']+$response['score_wrong']));
				} else {
					$scoreNum = -1;
				}
				$scoreNum = number_format($scoreNum,2);

				$locale = config::get('locale');

				$query = "SELECT ";
				$query.= "(SELECT `text` FROM `certificate_messages_translations` WHERE `message_uid`=1 AND `locale`='$locale' LIMIT 1) AS `goldmedal`, ";
				$query.= "(SELECT `text` FROM `certificate_messages_translations` WHERE `message_uid`=2 AND `locale`='$locale' LIMIT 1) AS `silvermedal`, ";
				$query.= "(SELECT `text` FROM `certificate_messages_translations` WHERE `message_uid`=3 AND `locale`='$locale' LIMIT 1) AS `bronzemedal` ";

				$result = database::query($query);

				$goldMedal = '';
				$silverMedal = '';
				$bronzeMedal = '';

				//mail('workstation@mystream.co.uk','lnut-gamescores.php',$query,'From: developer@languagenut.com');

				if($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
					while($row = mysql_fetch_assoc($result)) {
						$goldMedal = stripslashes($row['goldmedal']);
						$silverMedal = stripslashes($row['silvermedal']);
						$bronzeMedal = stripslashes($row['bronzemedal']);
					}
				} else {
					echo mysql_error();
				}
				$phrase = '';
				$medalNum='';
				if($scoreNum >= 85) {
					// get gold phrase
					$phrase = $goldMedal;
					$medalNum = 3;
				} else if($scoreNum >= 70) {
					// get silver phrase
					$phrase = $silverMedal;
					$medalNum = 2;
				} else if($scoreNum >= 50) {
					// get bronze phrase
					$phrase = $bronzeMedal;
					$medalNum = 1;
				} else {
					// no phrase
					$phrase = '';
					$medalNum = 0;
				}
				echo json_encode(array(
					'success'		=> 'true',
					'game_uid'		=> $response['game_uid'],
					'language_uid'	=> $response['language_uid'],
					'user_uid'		=> $response['user_uid'],
					'time'			=> $response['time'],
					'score_for'		=> $response['score_right'],
					'score_against'	=> $response['score_wrong'],
					'medal'			=> $medalNum,
					'medalText'		=> $phrase,
					'score'			=> $scoreNum,
					'vocabulary'	=> $response['vocab']
				));
			} else {
				echo json_encode(array(
					'success'		=> 'false'
				));
			}
		} else {
			echo json_encode(array(
				'success'		=> 'false'
			));
		}

	}
}

?>