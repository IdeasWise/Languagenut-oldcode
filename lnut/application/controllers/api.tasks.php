<?php

/**
 * api.tasks.php
 */

class API_Tasks extends Controller {

	public function __construct () {
		parent::__construct();
		$arrPaths = config::get('paths');
		$method = 'getInvalidLink';
		if(isset($arrPaths[2]) && isset($arrPaths[3]) && !empty($arrPaths[2]) && !empty($arrPaths[3])) {
			$newMethod = $arrPaths[2].ucfirst($arrPaths[3]);
			if(method_exists($this,$newMethod)) {
				$method = $newMethod;
			}
		}
		$this->$method();
	}

	private function getInvalidLink() {
		die('Invalid Link!!!');
	}

	private function getDataTranslations() {
		//die('get data!!!');
		if(isset($_REQUEST['language_uid']) && is_numeric($_REQUEST['language_uid'])) {

			$query ="SELECT ";
			$query.="`uid`, ";
			$query.="`name`, ";
			$query.="`difficulty_level_uid` ";
			$query.="FROM ";
			$query.="`difficulty_level_translation` ";
			$query.="WHERE ";
			$query.="`language_uid`='".$_REQUEST['language_uid']."'";
			$result = database::query($query);
			$arrDifficulty = array();
			if(mysql_error() == '' && mysql_num_rows($result)) {
				while($arrRow = mysql_fetch_array($result)) {
					$arrDifficulty[] = array(
						'uid'					=>	$arrRow['uid'],
						'name'					=>	$arrRow['name'],
						'difficulty_level_uid'	=>	$arrRow['difficulty_level_uid']
					);
				}
			}

			$query ="SELECT ";
			$query.="`uid`, ";
			$query.="`name`, ";
			$query.="`activity_skill_uid` ";
			$query.="FROM ";
			$query.="`activity_skill_translation` ";
			$query.="WHERE ";
			$query.="`language_uid`='".$_REQUEST['language_uid']."'";
			$result = database::query($query);
			$arrSkill = array();
			if(mysql_error() == '' && mysql_num_rows($result)) {
				while($arrRow = mysql_fetch_array($result)) {
					$arrSkill[] = array(
						'uid'				=>	$arrRow['uid'],
						'name'				=>	$arrRow['name'],
						'activity_skill_uid'=>	$arrRow['activity_skill_uid']
					);
				}
			}

			$query ="SELECT ";
			$query.="`uid`, ";
			$query.="`name`, ";
			$query.="`exercise_type_uid` ";
			$query.="FROM ";
			$query.="`exercise_type_translation` ";
			$query.="WHERE ";
			$query.="`language_uid`='".$_REQUEST['language_uid']."'";
			$result = database::query($query);
			$arrExerciseType = array();
			if(mysql_error() == '' && mysql_num_rows($result)) {
				while($arrRow = mysql_fetch_array($result)) {
					$arrExerciseType[] = array(
						'uid'				=>	$arrRow['uid'],
						'name'				=>	$arrRow['name'],
						'exercise_type_uid'	=>	$arrRow['exercise_type_uid']
					);
				}
			}

			$query ="SELECT ";
			$query.="`uid`, ";
			$query.="`name`, ";
			$query.="`reference_material_type_uid` ";
			$query.="FROM ";
			$query.="`reference_material_type_translation` ";
			$query.="WHERE ";
			$query.="`language_uid`='".$_REQUEST['language_uid']."'";
			$result = database::query($query);
			$arrReferenceTypes = array();
			if(mysql_error() == '' && mysql_num_rows($result)) {
				while($arrRow = mysql_fetch_array($result)) {
					$arrReferenceTypes[] = array(
						'uid'	=>	$arrRow['uid'],
						'name'	=>	$arrRow['name'],
						'reference_material_type_uid'	=>	$arrRow['reference_material_type_uid']
					);
				}
			}

			echo json_encode(
				array(
					'difficulties'	=> $arrDifficulty,
					'skills'		=> $arrSkill,
					'exerciseTypes'	=> $arrExerciseType,
					'referenceTypes'=> $arrReferenceTypes
				)
			);
		} else {
			die('Please provide valid language_uid');
		}
	}


	private function getData() {
		//die('get data!!!');
		$query ="SELECT ";
		$query.="`uid`, ";
		$query.="`name`, ";
		$query.="`token`, ";
		$query.="`reference_support`, ";
		$query.="`question_support`, ";
		$query.="`answer_support` ";
		$query.="FROM ";
		$query.="`difficulty_level` ";
		$result = database::query($query);
		$arrDifficulty = array();
		if(mysql_error() == '' && mysql_num_rows($result)) {
			while($arrRow = mysql_fetch_array($result)) {
				$arrDifficulty[] = array(
					'uid'				=>	$arrRow['uid'],
					'name'				=>	$arrRow['name'],
					'token'				=>	$arrRow['token'],
					'referenceSupport'	=>	($arrRow['reference_support'])?'true':'false',
					'questionSupport'	=>	($arrRow['question_support'])?'true':'false',
					'answerSupport'		=>	($arrRow['answer_support'])?'true':'false'
				);
			}
		}

		$query ="SELECT ";
		$query.="`uid`, ";
		$query.="`name`, ";
		$query.="`token` ";
		$query.="FROM ";
		$query.="`activity_skill` ";
		$result = database::query($query);
		$arrSkill = array();
		if(mysql_error() == '' && mysql_num_rows($result)) {
			while($arrRow = mysql_fetch_array($result)) {
				$arrSkill[] = array(
					'uid'	=>	$arrRow['uid'],
					'name'	=>	$arrRow['name'],
					'token'	=>	$arrRow['token']
				);
			}
		}

		$query ="SELECT ";
		$query.="`uid`, ";
		$query.="`name`, ";
		$query.="`token` ";
		$query.="FROM ";
		$query.="`exercise_type` ";
		$result = database::query($query);
		$arrExerciseType = array();
		if(mysql_error() == '' && mysql_num_rows($result)) {
			while($arrRow = mysql_fetch_array($result)) {
				$arrExerciseType[] = array(
					'uid'	=>	$arrRow['uid'],
					'name'	=>	$arrRow['name'],
					'token'	=>	$arrRow['token']
				);
			}
		}

		$query ="SELECT ";
		$query.="`uid`, ";
		$query.="`name`, ";
		$query.="`token` ";
		$query.="FROM ";
		$query.="`reference_material_type` ";
		$result = database::query($query);
		$arrReferenceTypes = array();
		if(mysql_error() == '' && mysql_num_rows($result)) {
			while($arrRow = mysql_fetch_array($result)) {
				$arrReferenceTypes[] = array(
					'uid'	=>	$arrRow['uid'],
					'name'	=>	$arrRow['name'],
					'token'	=>	$arrRow['token']
				);
			}
		}

		echo json_encode(
			array(
				'difficulties'	=> $arrDifficulty,
				'skills'		=> $arrSkill,
				'exerciseTypes'	=> $arrExerciseType,
				'referenceTypes'=> $arrReferenceTypes
			)
		);
	}
}
?>