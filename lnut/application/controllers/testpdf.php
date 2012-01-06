<?php
/**
 * testpdf.php
 */

class Links extends Controller {

	public function __construct () {
		parent::__construct();
		$this->page();
	}

	protected function page () {
		$arrMessages = array();
		/*
			$objClasses = new classes(762);
			$objClasses->load();

			$objGameScore = new gamescore();
			$data = $objGameScore->getStudentsByClass($objClasses->get_uid());
			*/
			$objViewClassStudents = new view_class_students();
/*
			// GET CERTIFICATE MESSAGE TRANSLATIONS
			$query = "SELECT ";
			$query.="`CM`.`tag`, ";
			$query.="( ";
				$query.="SELECT ";
				$query.="`text` ";
				$query.="FROM ";
				$query.="`certificate_messages_translations` AS `CMT`";
				$query.="WHERE ";
				$query.="`CMT`.`message_uid` = `CM`.`uid` ";
				$query.="AND ";
				$query.="`CMT`.`locale` = '" . $_SESSION['user']['prefix'] . "' ";
				$query.="LIMIT 1 ";
			$query.=") AS `text` ";
			$query.="FROM ";
			$query.="`certificate_messages` AS `CM`";
			$query.="WHERE ";
			$query.="`CM`.`tag` IN ('tag.name','tag.username','tag.password') ";
			$rows = database::arrQuery($query);
			for ($i = 0; $i < count($rows); $i++) {
				$arrMessages[$rows[$i]['tag']] = iconv("UTF-8", "cp1252", stripslashes($rows[$i]['text']));
				;
			}
			*/
			$data = array(
				array(
					'uid'=>1,
					'iuser_uid'=>1,
					'Name'=>'shailesh',
					'email'=>'sh123vi',
					'locale'=>'en',
					'wordbank_word'=>'12345'
				)
			);
			$arrMessages = array(
				'tag.name'=>'Name:',
				'tag.password'=>'Password:',
				'tag.username'=>'Username:'
			);
			/*
			echo '<pre>';
			print_r($data);
			print_r($arrMessages);
			echo '</pre>';
			*/
			/*echo '<pre>';
				print_r(debug_backtrace());
			echo '</pre>';*/
			$objViewClassStudents->generate_password_pdf($data, $arrMessages);
		
	}
	
}

?>