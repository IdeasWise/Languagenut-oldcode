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

			$query ="SELECT ";
			$query.="`uid`, ";
			$query.="`name`, ";
			$query.="`task_category_uid` ";
			$query.="FROM ";
			$query.="`task_category_translation` ";
			$query.="WHERE ";
			$query.="`language_uid`='".$_REQUEST['language_uid']."'";
			$result = database::query($query);
			$arrCategory = array();
			if(mysql_error() == '' && mysql_num_rows($result)) {
				while($arrRow = mysql_fetch_array($result)) {
					$arrCategory[] = array(
						'uid'	=>	$arrRow['uid'],
						'name'	=>	$arrRow['name'],
						'task_category_uid'	=>	$arrRow['task_category_uid']
					);
				}
			}

			echo json_encode(
				array(
					'difficulties'	=> $arrDifficulty,
					'skills'		=> $arrSkill,
					'activityTypes'	=> $arrExerciseType,
					'referenceTypes'=> $arrReferenceTypes,
					'taskCategories'=> $arrCategory
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

		$query ="SELECT ";
		$query.="`uid`, ";
		$query.="`name`, ";
		$query.="`token` ";
		$query.="FROM ";
		$query.="`task_category` ";
		$result = database::query($query);
		$arrCategory = array();
		if(mysql_error() == '' && mysql_num_rows($result)) {
			while($arrRow = mysql_fetch_array($result)) {
				$arrCategory[] = array(
					'uid'	=>	$arrRow['uid'],
					'name'	=>	$arrRow['name'],
					'token'	=>	$arrRow['token']
				);
			}
		}


		/*
		echo '<pre>';
		print_r(array(
				'difficulties'	=> $arrDifficulty,
				'skills'		=> $arrSkill,
				'activityTypes'	=> $arrExerciseType,
				'referenceTypes'=> $arrReferenceTypes
		));
		echo '</pre>';
		*/

		echo json_encode(
			array(
				'difficulties'	=> $arrDifficulty,
				'skills'		=> $arrSkill,
				'activityTypes'	=> $arrExerciseType,
				'referenceTypes'=> $arrReferenceTypes,
				'taskCategories'=> $arrCategory
			)
		);

	}
	//-> /api/tasks/get/tasksForUnitSkill/?skill_uid=XX&unit_uid=XX
	private function gettasksForUnitSkill() {
		if(isset($_REQUEST['unit_uid']) && isset($_REQUEST['skill_uid'])) {
			$query ="SELECT ";
			$query.="`uid` ";
			$query.="FROM ";
			$query.="`task` ";
			$query.="WHERE ";
			$query.="`unit_uid`='".mysql_real_escape_string($_REQUEST['unit_uid'])."' ";
			$query.="AND ";
			$query.="`skill_uid`='".mysql_real_escape_string($_REQUEST['skill_uid'])."' ";
			$query.="AND ";
			$query.="`active`='1' ";
			$result = database::query($query);
			$arrTasks = array();
			if(mysql_error()=='' && mysql_num_rows($result)) {
				while($arrRow = mysql_fetch_array($result)) {
					$arrTasks[] = $arrRow['uid'];
				}
			}
			$arrJson = array(
				'skill_uid'	=> $_REQUEST['skill_uid'],
				'unit_uid'	=> $_REQUEST['unit_uid'],
				'tasks'		=> $arrTasks
			);
			echo json_encode($arrJson);
		} else {
			echo json_encode(
				array('success'=>'false')
			);
		}

	}
	// -> /api/tasks/get/task/?task_uid=XX
	private function getTask() {
		$arrJson = array('success'=>'false');
		if(isset($_REQUEST['task_uid']) && is_numeric($_REQUEST['task_uid'])) {
			$query ="SELECT ";
			$query.="* ";
			$query.="FROM ";
			$query.="`task` ";
			$query.="WHERE ";
			$query.="`uid`='".mysql_real_escape_string($_REQUEST['task_uid'])."' ";
			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				$arrTask = mysql_fetch_array($result);
				$arrJson = array();
				$arrJson['task_uid']			= $arrTask['uid'];
				$arrJson['title']				= $arrTask['name'];
				$arrJson['task_category_uid']	= $arrTask['task_category_uid'];
				$arrJson['unit_uid']			= $arrTask['unit_uid'];
				$arrJson['skill_uid']			= $arrTask['skill_uid'];
				$arrJson['activity_type_uid']	= $arrTask['activity_type_uid'];
				$arrJson['reference_type_uid']	= $arrTask['reference_type_uid'];

				$query ="SELECT ";
				$query.="`token` ";
				$query.="FROM ";
				$query.="`reference_material_type` ";
				$query.="WHERE ";
				$query.="`uid`='".$arrTask['reference_type_uid']."' ";
				$result2 = database::query($query);
				$arrReference = array();
				if(mysql_error()=='' && mysql_num_rows($result2)) {
					$arrReference = mysql_fetch_array($result2);
				}

				if(isset($arrReference['token']) && ($arrReference['token']=='song' || $arrReference['token']=='story')) {
					$arrJson['reference_data']	= array(
						'unit_uid'	=>$arrTask['unit_uid'],
						'key'		=>$arrTask['reference_key']
					);
				} else if($arrTask['reference_data_uid']>0) {
					$arrJson['reference_data']	= $arrTask['reference_data_uid'];
				}
				$arrJson['exercises']			= array();

				/*
				$query ="SELECT ";
				$query.="`TD`.*, ";
				$query.="`QAE`.`qae_uid` ";
				$query.="FROM ";
				$query.="`task_difficulty` AS `TD`, ";
				$query.="`task_exercise_qae_topic` AS `QAE` ";
				$query.="WHERE ";
				$query.="`TD`.`task_uid` = '".$arrTask['uid']."' ";
				$query.="AND ";
				$query.="`TD`.`uid` = `task_difficulty_uid` ";
				$query.="ORDER BY ";
				$query.="`difficulty_uid` ";
				*/
				$query ="SELECT ";
				$query.="* ";
				$query.="FROM ";
				$query.="`task_difficulty` ";
				$query.="WHERE ";
				$query.="`task_uid` = '".$arrTask['uid']."' ";
				$query.="ORDER BY ";
				$query.="`difficulty_uid` ";

				$resultTaskDifficulty = database::query($query);
				if(mysql_error()=='' && mysql_num_rows($resultTaskDifficulty)) {
					while($arrRow=mysql_fetch_array($resultTaskDifficulty)) {
						$arrJson['exercises'][] = array(
							'difficulty_uid'		=>$arrRow['difficulty_uid'],
							'task_difficulty_uid'	=>$arrRow['uid'],
							'activity_data'			=>$arrRow['activity_data']
						);
					}
				}
			}
		}
		echo json_encode($arrJson);
	}
	private function getTask_old() {
		$arrJson = array(
			'task_uid'		=> 1,
			'title'			=> 'Reading 1',
			'refereneType'	=> 'article',
			'referenceData'	=> 2,
			'skill_uid'		=> 1,
			'unit_uid'		=> 2,
			'exercises'		=> array(
				array(
					'difficulty_uid'	=> 1,
					'activity_type'		=> 'qae',
					'activity_data'		=> 1
				),
				array(
					'difficulty_uid'	=> 2,
					'activity_type'		=> 'qae',
					'activity_data'		=> 2
				),
				array(
					'difficulty_uid'	=> 3,
					'activity_type'		=> 'qae',
					'activity_data'		=> 3
				)
			)
		);
		echo json_encode($arrJson);
	}
}

?>