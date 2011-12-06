<?php

/**
 * api.qae.php
 */

class API_QAE extends Controller {

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

	private function getQae() {
		if(isset($_REQUEST['qae_uid']) && is_numeric($_REQUEST['qae_uid']) && $_REQUEST['qae_uid']>0) {
			$objQaeTopic = new qae_topic($_REQUEST['qae_uid']);
			if($objQaeTopic->get_valid()) {
				$objQaeTopic->load();
				$arrJson= array(
					'qae_uid'	=>$objQaeTopic->get_uid(),
					'title'		=>$objQaeTopic->get_title(),
				);
				$arrQuestions = qae_topic_content_question::getQuestionsByQaeTopic($objQaeTopic->get_uid());
				if(is_array($arrQuestions) && count($arrQuestions)) {

					$objQaeAnswer = new qae_topic_content_question_options();
					$arrJson['questions']=array();

					foreach($arrQuestions as $arrQuestion) {
						$arrJson['questions'][]=array(
							'question_uid'		=>$arrQuestion['uid'],
							'focus'				=>$arrQuestion['focus'],
							'correct_answer_uid'=>$arrQuestion['correct_answer_uid'],
							'answers'			=>$objQaeAnswer->getAnswerUids($arrQuestion['uid'])
						);
					}
				}
				//echo '<pre>';
				//print_r($arrJson);
				echo json_encode($arrJson);
			} else {
				echo json_encode(
					array(
						'success'=>'false',
						'message'=>'Invalid qae_uid.'
					)
				);
			}
		} else {
			echo json_encode(
					array(
						'success'=>'false',
						'message'=>'please provide qae_uid or valid qae_uid.'
					)
				);
		}
	}

	private function getQaeTranslation() {
		if(isset($_REQUEST['qae_uid']) && is_numeric($_REQUEST['qae_uid']) && $_REQUEST['qae_uid'] > 0 && isset($_REQUEST['language_uid']) && is_numeric($_REQUEST['language_uid']) && $_REQUEST['language_uid'] > 0) {
			$objQaeTopicTranslation = new qae_topic_translation();
			$arrQae = $objQaeTopicTranslation->getQaeTranslation(
				$_REQUEST['qae_uid'],
				$_REQUEST['language_uid']
			);
			if(is_array($arrQae)) {
				$arrJson = array(
					'qae_uid'	=>$arrQae['qae_uid'],
					'title'		=>addslashes($arrQae['qae_uid'])
				);
				$arrQuestions = qae_topic_content_question::getQuestionsTranslationsByQaeTopic(
					$_REQUEST['qae_uid'],
					$_REQUEST['language_uid']
				);
				if(is_array($arrQuestions) && count($arrQuestions)) {
					$objQaeAnswer = new qae_topic_content_question_options();
					$arrJson['questions']=array();
					foreach($arrQuestions as $arrQuestion) {
						$arrJson['questions'][]=array(
							'question_uid'		=>$arrQuestion['qae_topic_content_question_uid'],
							'focus'				=>addslashes($arrQuestion['focus']),
							'question'			=>addslashes($arrQuestion['question']),
							'answers'			=>$objQaeAnswer->getAnswerTranslations(
								$arrQuestion['qae_topic_content_question_uid'],
								$_REQUEST['language_uid']
							)
						);
					}
				}
				echo json_encode($arrJson);
			} else {
				echo json_encode(
					array(
						'success'=>'false',
						'message'=>'Invalid qae_uid.'
					)
				);
			}
		} else {
			echo json_encode(
					array(
						'success'=>'false',
						'message'=>'please provide qae_uid or valid qae_uid.'
					)
				);
		}
	}

	public function submitQae() {
		/*
		$arrJson = array(
			'task_uid'				=>1,
			'unit_uid'				=>1,
			'task_difficulty_uid'	=>1,
			'difficulty_uid'		=>1,
			'qae_uid'				=>1,
			'learning_language_uid'	=>14,
			'support_language_uid'	=>14,
			'questions'				=>array(
				array(
					'correct'		=>'true',
					'time'			=>rand(1000,4000),
					'answer_uid'	=>1,
					'question_uid'	=>1
				),
				array(
					'correct'		=>'true',
					'time'			=>rand(1000,4000),
					'answer_uid'	=>2,
					'question_uid'	=>7
				),
				array(
					'correct'		=>'false',
					'time'			=>rand(1000,4000),
					'answer_uid'	=>3,
					'question_uid'	=>12
				)
			)
		);

		echo '<pre>';
		print_r($arrJson);
		echo '</pre>';


		//		echo json_encode($arrJson);
		//		$objJson = json_decode(json_encode($arrJson));
		//		echo '<pre>';
		//		print_r($objJson);
		//		echo '</pre>';
		*/
		$objJson = false;
		if(isset($_REQUEST['data'])) {
			$objJson = json_decode($_REQUEST['data']);
		}

		if(isset($objJson->questions) && count($objJson->questions)) {
			$score					= 0;
			$total_question			= count($objJson->questions);
			$total_correct_answers	= 0;

			$objQaeScore = new qae_score();
			$objQaeScore->set_task_uid($objJson->task_uid);
			$objQaeScore->set_task_difficulty_uid($objJson->task_difficulty_uid);
			$objQaeScore->set_difficulty_level_uid($objJson->difficulty_uid);
			$objQaeScore->set_unit_uid($objJson->unit_uid);
			$objQaeScore->set_qae_uid($objJson->qae_uid);
			$objQaeScore->set_learning_language_uid($objJson->learning_language_uid);
			$objQaeScore->set_support_language_uid($objJson->support_language_uid);
			$objQaeScore->set_user_uid((isset($_SESSION['user']['uid'])?$_SESSION['user']['uid']:1));
			$objQaeScore->set_recorded_dts(date('Y-m-d H:i:s'));
			$qae_score_uid = $objQaeScore->insert();

			$objQaeScoreDetail = new qae_score_detail();
			foreach($objJson->questions as $arrQuestion) {
				$objQaeScoreDetail->set_qae_score_uid($qae_score_uid);
				$objQaeScoreDetail->set_question_uid($arrQuestion->question_uid);
				$objQaeScoreDetail->set_answer_uid($arrQuestion->answer_uid);
				$objQaeScoreDetail->set_time($arrQuestion->time);
				if($arrQuestion->correct === 'true') {
					$objQaeScoreDetail->set_is_correct_answer(1);
					$total_correct_answers++;
				} else {
					$objQaeScoreDetail->set_is_correct_answer(0);
				}
				$objQaeScoreDetail->insert();
			}
			if($total_correct_answers > 0 && $total_question > 0) {
				$score = floor(($total_correct_answers*100)/$total_question);
			}
			$objQaeScore = new qae_score($qae_score_uid);
			$objQaeScore->load();
			$objQaeScore->set_score($score);
			$objQaeScore->save();

			$medalToken='';
			if($score >= 85) {
				// get gold phrase
				$medalToken='gold.medal';
			} else if($score >= 70) {
				// get silver phrase
				$medalToken='silver.medal';
			} else if($score >= 50) {
				// get bronze phrase
				$medalToken='bronze.medal';
			}

			echo json_encode(
				array(
					'success'				=> 'true',
					'task_uid'				=> $objJson->task_uid,
					'task_difficulty_uid'	=> $objJson->task_difficulty_uid,
					'learning_language_uid'	=> $objJson->learning_language_uid,
					'support_language_uid'	=> $objJson->support_language_uid,
					'score'					=> $score,
					'medal'					=> $medalToken
			));

		} else {
			echo '{"success":"false"}';
		}
	}
}




	/*
	api/qae/get/qae?qae_uid=1
{
    qae_uid : 1,
  title : "QAE 1",
    questions :[ {  question_uid : 1,
        correct_answer_uid : 1,
        focus : "group1",
        answers : [ { answer_uid : 1 },
           { answer_uid : 2 },
           { answer_uid : 3 } ]
        },
        {  question_uid : 2,
        correct_answer_uid : 5,
        focus : "group1",
        answers : [ { answer_uid : 4 },
           { answer_uid : 5 },
           { answer_uid : 6 } ]
        },
        {  question_uid : 3,
        correct_answer_uid : 8,
        focus : "group1",
        answers : [ { answer_uid : 7 },
           { answer_uid : 8 },
           { answer_uid : 9 } ]
        },
        {  question_uid : 4,
        correct_answer_uid : 12,
        focus : "group1",
        answers : [ { answer_uid : 10 },
           { answer_uid : 11 },
           { answer_uid : 12 } ]
        }
      ]
}

api/qae/get/qaeTranslation?qae_uid=1&language_uid=14
{
    qae_uid : 1,
 language_uid : 3,
 title : "QÁE",
 questions :[ {  question_uid : 1,
        answers : [ { answer_uid : 1, answer : "answer 1", response : "yes, thats right" },
           { answer_uid : 2, answer : "answer 2", response : "no, thats not right"  },
           { answer_uid : 3, answer : "answer 3", response : "no, thats really not right"  } ]
        },
        {
         ...........
        }
         ]


}
	*/
?>