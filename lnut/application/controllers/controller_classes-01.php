<?php

class controller_classes extends Controller {

	public $token		= 'list';

	public $arrTokens	= array (
		'list',
		'edit',
		'add',
		'delete',
		'getstudentsbyname',
		'addstudent',
		'removestudent',
		'gamescores',
		'viewstudents',
		'showstudents',
		'unitgamescores',
		'getcertificate',
		'getstudentlogins'
	);

	public $arrPaths		= array();
	
	public function __construct () {
		parent::__construct();
		$this->arrPaths = config::get('paths');

		if(isset($this->arrPaths[2])) {
			$this->arrPaths[2] = str_replace('-', '', $this->arrPaths[2]);
		}
		if(isset($this->arrPaths[2]) && in_array($this->arrPaths[2], $this->arrTokens)) {
			 $this->token =  $this->arrPaths[2];
		}
		if(in_array($this->token,$this->arrTokens)) {
			$method = 'do' . ucfirst($this->token);
			$this->$method();
		}
	}

	protected function doViewstudents() {
		// new pdf style
		$this->doGetstudentlogins();
	}

	protected function doGetstudentlogins() {
		$arrMessages = array();
		if($this->arrPaths[3] > 0){
			$objClasses = new classes($this->arrPaths[3]);
			$objClasses->load(); 

			$objGameScore = new gamescore();
			$data = $objGameScore->getStudentsByClass($objClasses->get_uid());
			$objViewClassStudents = new view_class_students();

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
				$query.="`CMT`.`locale` = '".$_SESSION['user']['prefix']."' ";
				$query.="LIMIT 1 ";
			$query.=") AS `text` ";
			$query.="FROM ";
			$query.="`certificate_messages` AS `CM`";
			$query.="WHERE ";
			$query.="`CM`.`tag` IN ('tag.name','tag.username','tag.password') ";
			$rows = database::arrQuery($query);
			for($i = 0;  $i < count($rows); $i++ ) {
				$arrMessages[$rows[$i]['tag']] = iconv("UTF-8", "cp1252", stripslashes($rows[$i]['text']));;
			}
			$objViewClassStudents->generate_password_pdf($data, $arrMessages);
		}
	}

	protected function doShowstudentsLogin() {
		if($this->arrPaths[3] > 0){
			$objClasses = new classes($this->arrPaths[3]);
			$objClasses->load();
			if( $objClasses->get_school_id() > 0 ){
				$objGameScore = new gamescore();
				$data['students'] = $objGameScore->getStudentsByClass($objClasses->get_uid());
			}
		}
	}

	protected function doAdd() {
		$objSchool	= new users_schools();
		$arrSchool	= $objSchool->getSchool();
		$skeleton	= config::getUserSkeleton();
		$body		= make::tpl ('body.admin.classes.add-edit');
		$arrBody	= array();

		$arrBody['add_students_tab']	= 'style="display:none;"';
		$arrBody['title']				= 'Add Class';
		$arrBody['btnval']				= 'Add';

		if(isset($_POST['form_submit_button'])){
			$objClasses = new classes();
			if($objClasses->doSave()){
				// redirect to invoice list if all does well;
				$objClasses->redirectToDynamic('/classes/list');
			} else{
				$objClasses->arrForm['school_id'] = format::to_select(array("name" => "school_id","id" => "school_id","options_only" => false), $arrSchool , $objClasses->arrForm['school_id']);
				$body->assign( $objClasses->arrForm );
			}
		} else{
			$arrBody['school_id'] = format::to_select(array("name" => "school_id","id" => "school_id","options_only" => false), $arrSchool , NULL);
		}

		$body->assign( $arrBody );

		$skeleton->assign (
			array (
				'body' => $body
			)
		);
		output::as_html($skeleton,true);
	}
	
	protected function breadCrumb($class_uid, $class_name) {
		if(isset($_SESSION['user']['admin']) && $_SESSION['user']['admin'] == 1) {
			$html = '<h2>Edit Class</h2><p class="breadcrumb"><a href="'.config::admin_uri().'classes/">Classes List</a> ›› <a href="'.config::admin_uri().'classes/edit/'.$class_uid.'/">'.$class_name.'</a></p>';
		} else {
			$html = '<h2>Edit Class</h2><p class="breadcrumb"><a href="'.config::admin_uri().'classes/">My Classes</a> ›› <a href="'.config::admin_uri().'classes/edit/'.$class_uid.'/">'.$class_name.'</a></p>';
		}
		return $html;
	}

	protected function doEdit() {
		$objSchool	= new users_schools();
		$arrSchool	= $objSchool->getSchool();
		$skeleton	= config::getUserSkeleton();
		
		if(isset($_SESSION['user']['admin']) && $_SESSION['user']['admin'] == 1) {
			$body = make::tpl('body.admin.classes.add-edit');
		} else {
			$body = make::tpl('body.account.classes.add-edit');
		}

		$arrBody = array();

		$arrBody['title']	= 'Update Class';
		$arrBody['btnval']	= 'Update';

		if(isset($this->arrPaths[3]) && $this->arrPaths[3] > 0){
			$objGameScore	= new gamescore();
			$arrStudents	= array();
			$arrStudents	= $objGameScore->getStudentsByClass($this->arrPaths[3]);
			$arrRows		= array();
			$i = 1;
			if(!empty($arrStudents)) {
				foreach($arrStudents as $uid=>$data) {
					$panel = make::tpl ('body.admin.class.students.row');
					$data['order'] = $i++;
					$panel->assign($data);
					$arrRows[] = $panel->get_content();
				}
				$arrBody['list.students.logins']	= implode('',$arrRows);
				$arrBody['students.logins.action']	= $_SERVER['REQUEST_URI'].'#tab-6';
			}
		}

		//if(@$_SESSION['user']['admin'] == 0)
		//	$arrBody['show_school'] = 'style="display:none;"';

		if( isset($_POST['form_submit_button'])){
			$objClasses = new classes();
			if( $objClasses->doSave() ){
				// redirect to invoice list if all does well;
				$objClasses->redirectToDynamic('/classes/list');
			} else {
				if(isset($_SESSION['user']['admin']) && $_SESSION['user']['admin'] == 1) {
					$objClasses->arrForm['school_id'] = format::to_select(
						array(
							"name" => "school_id",
							"id" => "school_id",
							"options_only" => false
						), 
						$arrSchool , 
						$objClasses->arrForm['school_id']
					);
				}
				$body->assign($objClasses->arrForm);
			}
		} else {
			if($this->arrPaths[3] > 0){
				$objClasses = new classes($this->arrPaths[3]);
				$objClasses->load();
				foreach( $objClasses->TableData as $idx => $val ) {
					$arrBody[$idx] = $val['Value'];
				}
				$arrBody['s_id'] = $arrBody['school_id'];
				if(isset($_SESSION['user']['admin']) && $_SESSION['user']['admin'] == 1) {
					$arrBody['school_id'] = format::to_select(
						array(
							"name"			=> "school_id",
							"id"			=> "school_id",
							"options_only"	=> false
						),
						$arrSchool,
						$arrBody['school_id']
					);
				}
				$arrBody['uid'] = $this->arrPaths[3];
			}
		}

		/**
		 * Following funtion shows old  ass student module
		 * $arrBody['tab.add_students'] = $this->Getstudentsbyname($this->arrPaths[3], $arrBody['s_id']);
		 * $arrBody['current.student'] = $this->doShowcurrentstudents( $this->arrPaths[3] );
		 */
		$arrBody['tab.add_students'] = $this->AddStudents( $objClasses->get_school_id(), $objClasses->get_uid() );
		$body->assign( $arrBody );
		$body->assign( 'breadcrumb' , $this->breadCrumb(@$arrBody['uid'], @$arrBody['name']) );

		$skeleton->assign (
			array (
				'body' => $body
			)
		);
		output::as_html($skeleton,true);
	}

	protected function doAddstudent() {
		$query ="INSERT INTO ";
		$query.="`classes_student` ";
		$query.="SET ";
		$query.="`class_uid` = '".$this->arrPaths[4]."', ";
		$query.="`student_uid` = '".$this->arrPaths[3]."' ";
		$result = database::query($query);
		echo $this->doShowcurrentstudents( $this->arrPaths[4] );
		$this->doShowcurrentstudents();
	}
	
	protected function userClassandSchoolLimitRestriction( $school_id, $class_uid ) {
		$message = '';
		if(isset($_POST['last'])) {
			/**
			 * FOLLOWING CODE WILL CHECK 30 USER LIMIT FOR ON CLASS. WE'VE A RULE THAT ONE 
			 * CLASS CAN NOT HAVE MORE THEN 30 STUDENTS.
			 */

			if( (count($_POST['last'])) > 30 ) {
				$messgae = '<p>Please correct the errors below:</p>';
				$messgae.= '<ul>';
				$messgae.= '<li>';
				$messgae.= 'you can not add more then 30 student at a time.';
				$messgae.= '</li>';
				$messgae.= '</ul>';
				return $messgae;
			}
			$message = $this->CheckSchoolStudentLimitRestriction( $school_id );
		}
		return $message;
	}

	/**
	* CHECK `user_limi` FIELD IN SCHOOL PROFILE DO IT HAVE USER LIMIT IF YES THEN CHECK THAT SCHOOL IS UNDER ANY RESELLER.
	* IF YSE THEN CHECK IS THAT RESSLER HAVE SET USER LIMIT REACHED MESSAGE 
	* IF YSE THEN BRING THAT MESSAGE FROM RESELLER PROFILE AND DISPLAY IT ON THE PAGE 
	* ELSE DISPLAY DEFAULT USER LIMIT REACHERD MESSAGE ON SCREEN.
	*/
	public function CheckSchoolStudentLimitRestriction( $school_id ) {
		$message = '';
		if( isset($school_id) && is_numeric($school_id) && $school_id > 0 ) {
			$objSchool = new users_schools( $school_id );
			$objSchool->load();
			if($objSchool->get_user_limit() > 0 ) {
				$student_count = $this->getSchoolStudentCount( $school_id );
				if( ($student_count + count($_POST['last'])) > $objSchool->get_user_limit() ) {
					
					$message = $this->getUserLimitReachedMessageFromReseller( $school_id );
					if(trim($message) == '') {
						$message = '<p>Please correct the errors below:</p>';
						$message.= '<ul>';
						$message.= '<li>';
						$message.= 'Your subscription permits only '.$objSchool->get_user_limit().' accounts to be created. Please contact us about increasing this limit.';
						$message.= '</li>';
						$message.= '</ul>';
					}
				}
			}
		}
		return $message;
	}

	public function getSchoolStudentCount( $school_id ) {
		$student_count = 0;
		if( isset($school_id) && is_numeric($school_id) && $school_id > 0 ) {
			$query = "SELECT ";
			$query .="count(`uid`) as `tot` ";
			$query .= "FROM ";
			$query .= "`profile_student` ";
			$query .= "WHERE ";
			$query .= "`school_id` = '".$school_id."' ";
			
			$result = database::query( $query );
			if( $result && mysql_error() == '' ) {
				$row = mysql_fetch_array( $result );
				$student_count =  $row['tot'];
			}
		}
		return $student_count;
	}

	public function getUserLimitReachedMessageFromReseller( $school_id ) {
		$message = '';
		if( isset($school_id) && is_numeric($school_id) && $school_id > 0 ) {
			$query = "SELECT ";
			$query .="`user_limit_reached` ";
			$query .= "FROM ";
			$query .= "`profile_reseller` AS `PR`, ";
			$query .= "`reseller_sale` AS `RS` ";
			$query .= "WHERE ";
			$query .= "`PR`.`iuser_uid` = `reseller_user_uid` ";
			$query .= "AND ";
			$query .= "`sold_user_uid` = '".$school_id."'";
			$query .= "AND ";
			$query .= "`user_limit_reached` != '' ";

			$result = database::query( $query );
			if( $result && mysql_error() == '' && mysql_num_rows( $result ) ) {
				$row		= mysql_fetch_array( $result );
				$message	= $row['user_limit_reached'];
			}
		}
		return $message;
	}
	
	protected function AddStudents( $school_id, $class_uid ) {
		$student_rows			= array();
		$final_check			= false;
		$objClassesStudent		= new classes_student();
		$messgae				= '';
		$assign					= array();

		$assign['student.frm.action']	= $_SERVER['REQUEST_URI'].'#tab-1';
		$assign['school_id']			= $school_id;
		$assign['class_uid']			= $class_uid;
		$assign['student_count']		= 0;
		$assign['submit-button']		= 'add_students';
		
		if(isset($_POST['add_students']) || isset($_POST['add_students_final'])) {
			$messgae = $this->userClassandSchoolLimitRestriction( $school_id, $class_uid );
		}

		if(isset($_POST['add_students'])) {
			foreach($_POST['last'] as $index=>$value) {
				if(strlen($value) < 1 && strlen($_POST['first'][$index]) < 1) {
					unset($_POST['last'][$index]);
					unset($_POST['first'][$index]);
				}
			}

			$arrStudents = new profile_student();
			list( $final_check, $student_rows ) = $arrStudents->CheckAllStudents( $this ); // passing Class Object
			
			if($final_check == true){
				$assign['submit-button'] = 'add_students_final';
				if(count($student_rows) == 0 && trim($messgae) == '') {
					$arrStudents->SaveNow();
				}
			}

		} else if(isset($_POST['add_students_final'])) {

			$arrStudents = new profile_student();
			list($final_check, $student_rows ) = $arrStudents->CheckAllStudents( $this, true ); // passing Class Object

			if($final_check == true  && trim($messgae) == '') {
				$arrStudents->SaveNow();
			} else {
				list( $final_check, $student_rows ) = $arrStudents->CheckAllStudents( $this ); // passing Class Object
			}
		} else {
			$student_rows[] = $this->CreateStudentsRow();
		}

		$assign['student.rows']		= implode('',$student_rows);
		$assign['error_message']	= $messgae;
		$add_students				= make::tpl('tab.add_students');
		$add_students->assign($assign);
		return $add_students->get_content();
	}

	public function CreateStudentsRow( $last = array('value'=>'', 'error'=>'', 'option'=>'', 'index'=>''), $first = array('value'=>'', 'error'=>'', 'option'=>'', 'index'=>'') ) {

		$html = '<tr>';
		if(is_array($last['option'])){
			$html .= '<td><input type="radio" name="radio['.$last['index'].']" value="0" checked="checked" /></td>';
		} else {
			$html .= '<td>&nbsp;</td>';
		}

		$html .= '<td><input type="text" name="last[]" class="inputClass '.$last['error'].'" value="'.$last['value'].'" /></td>
		<td><input type="text" name="first[]" class="inputClass '.$first['error'].'" value="'.$first['value'].'" /></td>';
		if(is_array($last['option'])){
			$html .= '<td  class="message_error">Create this record</td>';
		} else if( !empty($last['error']) && !empty($first['error'])) {
			$html .= '<td class="message_error">Please enter names.</td>';
		} else if( !empty($last['error']) ) {
			$html .= '<td class="message_error">Please enter last name.</td>';
		} else if( !empty($first['error']) ) {
			$html .= '<td class="message_error">Please enter first name.</td>';
		} else {
			$html .= '<td>&nbsp;</td>';
		}
		$html .= '</tr>';

		if(is_array($last['option'])){
			foreach( $last['option'] as $data ) {
			$html.='<tr>';
			$html.='<td>';
			$html.='<input type="radio" name="radio['.$last['index'].']" value="'.$data['uid'].'" />';
			$html.='</td>';
			$html.='<td>'.$data['vlastname'].'</td>';
			$html.='<td>'.$data['vfirstname'].'</td>';
			$html.='<td  class="message_error">Choose this student</td>';
			$html.='</tr>';
			}
		}
		return $html;
	}

	protected function doRemovestudent() {
		$query ="DELETE ";
		$query.="FROM ";
		$query.="`classes_student` ";
		$query.="WHERE ";
		$query.="`class_uid` = '".$this->arrPaths[4]."' ";
		$query.="AND ";
		$query.="`student_uid` = '".$this->arrPaths[3]."' ";
		$result = database::query($query);
		echo $this->doShowcurrentstudents( $this->arrPaths[4] );
	}

	protected function doGetstudentsbyname() {
		$ClassObject	= new classes($this->arrPaths[3]);
		$ClassObject->load();
		echo $this->Getstudentsbyname(
				$this->arrPaths[3],
				$ClassObject->get_school_id(),
				$this->arrPaths[4]
			);
	}

	protected function Getstudentsbyname($class_id, $school_id, $letter = 'a' ) {
		$html = '';
		$sql = "SELECT ";
		$sql.= "`uid`, ";
		$sql.= "CONCAT(`vfirstname`, ' ', `vlastname`) AS Name, ";
		$sql.= "(";
			$sql.= "SELECT ";
			$sql.= "COUNT(`uid`) ";
			$sql.= "FROM ";
			$sql.= "`classes_student` ";
			$sql.= "WHERE ";
			$sql.= "`class_uid` = '".$class_id."' ";
			$sql.= "AND `student_uid` = `profile_student`.`uid`";
		$sql.= ") AS `count` ";
		$sql.= "FROM ";
		$sql.= "`profile_student` ";
		$sql.= "WHERE ";
		$sql.= "`school_id` = '".$school_id."' ";
		$sql.= "AND `vfirstname` LIKE '".$letter."%' ";

		$result = database::query( $sql );

		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			$html = '<table border="0" cellpadding="1" cellspacing="3" width="100%" align="center"><tr>';
			while($row=mysql_fetch_assoc($result)) {
				$html .='<tr><td><input type="checkbox" '.($row['count'] > 0 ? 'checked="checked"' : "").' name="student_uid[]" value="'.$row['uid'].'" class="add-students" title="'.$row['Name'].'" />'.$row['Name'].'</td></tr>';
			}
			$html .='</table>';
		} else {
			$html = '<div>No Students starting with '.strtoupper($letter).'.</div>';
		}
		return $html;
	}

	protected function doShowcurrentstudents( $class_id = 0 ) {
		$query = "SELECT ";
		$query.="`S`.`uid`, ";
		$query.="CONCAT(`vfirstname`, ' ', `vlastname`) AS `Name`, ";
		$query.="`U`.`email` ";
		$query.="FROM ";
		$query.="`profile_student` AS `S`, ";
		$query.="`classes_student` AS `SC`, ";
		$query.="`user` AS `U` ";
		$query.="WHERE ";
		$query.="`U`.`uid` = `S`.`iuser_uid` ";
		$query.="AND ";
		$query.="`S`.`uid` = `SC`.`student_uid` ";
		$query.="AND ";
		$query.="`SC`.`class_uid` = '".$class_id."' ";

		$result		= database::query($query);
		$body		= make::tpl ('body.admin.student');
		$arrRows	= array();

		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			while($row=mysql_fetch_assoc($result)) {
				$arrRows[] = make::tpl('body.admin.student.row')->assign($row)->get_content();
			}
			$body->assign('users.rows' , implode('',$arrRows));
		}
		return $body->get_content();
	}

	protected function doDelete() {
		if($this->arrPaths[3] > 0){
			$objClasses = new classes($this->arrPaths[3]);
			$objClasses->delete();
			// redirect to invoice list if all does well;
			$objClasses->redirectToDynamic('/classes/list');
		}
	}

	protected function doList () {
		$skeleton	= config::getUserSkeleton();
		$body		= make::tpl ('body.account.classes.list');
		$arrClasses	= array();
		$objClasses	= new classes();
		$arrClasses	= $objClasses->getList();
		$arrRows	= array();
		if(!empty($arrClasses)) {
			foreach($arrClasses as $uid=>$arrClass) {
				
				$arrRows[] = make::tpl('body.account.classes.list.row')->assign($arrClass)->get_content();
			}
		}

		$page_display_title		=   $objClasses->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
		$page_navigation		=   $objClasses->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>').$objClasses->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ').$objClasses->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

		$body->assign('page.display.title'	, $page_display_title);
		$body->assign('page.navigation'		, $page_navigation);
		$body->assign('list.rows'			, implode('',$arrRows));
		$body->assign('page.title'			, (isset($_SESSION['user']['admin']) && $_SESSION['user']['admin'] == 1)?'Classes &gt; List':'My Classes');

		$skeleton->assign (
			array (
				'body'=> $body
			)
		);
		output::as_html($skeleton,true);
	}

	protected function getBreadcrumb($mode = '') {
		$link = '<a href="'.config::url().'account/classes/game-scores/">Game Scores</a>';
		if(isset($this->arrPaths[3]) && strstr($this->arrPaths[3], 'class-')) {
			$class_uid	= str_replace('class-','',$this->arrPaths[3]);
			$objClasses	= new classes($class_uid);
			$objClasses->load();
			$link .= ' &rsaquo;&rsaquo; ';
			$link .= '<a href="'.config::url().'account/classes/game-scores/class-'.$class_uid.'/">'.$objClasses->get_name().'</a>';
		}

		if(isset($this->arrPaths[4]) && is_numeric($this->arrPaths[4]) == false) {		
			$query ="SELECT ";
			$query.="`name` ";
			$query.="FROM ";
			$query.="`language` ";
			$query.="WHERE ";
			$query.="`prefix` = '".$this->arrPaths[4]."'";
			$result = database::query($query);

			if(mysql_num_rows($result)) {
				$row = mysql_fetch_array($result);
				$link .= ' &rsaquo;&rsaquo; ';
				$link .= '<a href="'.config::url().'account/classes/game-scores/class-'.$class_uid.'/'.$this->arrPaths[4].'/">'.$row['name'].'</a>';
			}
		}
		return $link;
	}

	protected function doGamescores( ) {
		$class_uid = 0;
		if(isset($this->arrPaths[3]) && strstr($this->arrPaths[3], 'class-')) {
			$class_uid = str_replace('class-','',$this->arrPaths[3]);
		}
		$skeleton		= config::getUserSkeleton();
		$body			= make::tpl ('class.language.units');

		$arrLanguage	= array();
		$language_uid	= '';
		$objLanguage	= new language();
		$arrFields		= array('uid','prefix','name');
		$arrWhere		= array('is_learnable'=>1);
		$arrLanguage	= $objLanguage->search($arrFields, $arrWhere, 'Order By `name`');
		if(count($arrLanguage)) {
			$language_uid = $arrLanguage[0]['uid'];
		}
		if(isset($this->arrPaths[4])) {
			$language_uid = $this->arrPaths[4];
		}

		// units list
		$arrUnits	= array();
		$unit_uid	= null;
		$objUnit	= new units();
		$arrUnits	= $objUnit->unitLit();
		if(count($arrUnits)) {
			$unit_uid = $arrUnits[0]['uid'];
		}
		if(isset($this->arrPaths[5])) {
			$unit_uid = $this->arrPaths[5];
		}
		
		$arrRows  = array();
		if(!empty($arrLanguage)) {
			foreach($arrLanguage as $uid=>$data) {
				$url = config::admin_uri('classes/game-scores/class-'.$class_uid.'/'.$data['uid'].'/'.$unit_uid.'/');
				$objClasses = '';
				if($language_uid == $data['uid']) {
					$objClasses = ' ui-tabs-selected ui-state-active';
				}
				$arrRows[]	= ' <li class="ui-state-default ui-corner-top '.$objClasses.'"><a href="'.$url.'"><span>'.$data['name'].'</span></a></li>';
			}
			$body->assign('language.li',implode('',$arrRows));
		}

		$arrRows  = array();
		if(!empty($arrUnits)) {
			foreach($arrUnits as $uid=>$data) {
				$url  = config::admin_uri('classes/game-scores/class-'.$class_uid.'/'.$language_uid.'/'.$data['uid'].'/');
				$objClasses = '';
				if($unit_uid == $data['uid']) {
					$objClasses = ' ui-tabs-selected ui-state-active';
				}
				$arrRows[] = ' <li class="ui-state-default ui-corner-top '.$objClasses.'"  title="'.$data['name'].'"><a href="'.$url.'" title="'.$data['name'].'"><span>'.$data['unit_number'].'</span></a></li>';
			}
			$body->assign('units.li',implode('',$arrRows));
		}

		$gameHtml = '';
		$objGame = new game();
		$html = $objGame->getGameScoreHeader();

		$sections = array();
		$sectionObj = new sections();
		$sections = $sectionObj->SectionList($unit_uid);
		$arrRows  = array();
		if(!empty($sections)) {
			foreach($sections as $uid=>$data) {
				$gameHtml	.= $html;
				$arrRows[]	= '<th title="'.$data['name'].'" colspan="4">Section '.$data['section_number'].'</th>';
			}
			$arrRows[]	= '<th title="Unit Score" colspan="4">Unit</th>';
			$gameHtml	.= $html;
			$body->assign('th.section',implode('',$arrRows));
		}
		$body->assign('th.games',$gameHtml);
		$objGameScore = new gamescore();
		$body->assign('names.scores.rows',$objGameScore->getClassUsersAndScores(
			$class_uid,
			$language_uid,
			$unit_uid,
			$sections
			)
		);

		$body->assign('class_uid'		, $class_uid);
		$body->assign('section.active'	, 'ui-tabs-selected ui-state-active');

		$arrClasses = array();
		$objClasses = new classes();
		$arrClasses = $objClasses->search(array('name'),array('uid'=>$class_uid));
		if(isset($arrClasses['name'])) {
			$body->assign('breadcrumb',$this->breadCrumb(
					$class_uid,
					$arrClasses['name']
				)
			);
		}
		$skeleton->assign (
			array (
				'body' => $body
			)
		);
		output::as_html($skeleton,true);
	}

	protected function doUnitgamescores() {
		$class_uid = 0;
		if(isset($this->arrPaths[3]) && strstr($this->arrPaths[3], 'class-')) {
			$class_uid = str_replace('class-','',$this->arrPaths[3]);
		}
		$skeleton		= config::getUserSkeleton();
		$body			= make::tpl ('view.unit.score');
		$arrLanguage	= array();
		$objLanguage	= new language();
		$arrFields		= array('uid','prefix','name');
		$arrWhere		= array('is_learnable'=>1);
		$arrLanguage	= $objLanguage->search($arrFields, $arrWhere, 'Order By `name`');
		if(count($arrLanguage)) {
			$language_uid = $arrLanguage[0]['uid'];
		}
		if(isset($this->arrPaths[4])) {
			$language_uid = $this->arrPaths[4];
		}

		// units list
		$arrUnits = array();
		$Allunits = array();
		$unit_uid = null;
		$objUnit = new units();
		$Allunits = $objUnit->unitLit();

		$arrUnits[1]['uid']  = 1;
		$arrUnits[1]['name'] = '1-6';
		$arrUnits[2]['uid']  = 2;
		$arrUnits[2]['name'] = '7-12';
		$arrUnits[3]['uid']  = 3;
		$arrUnits[3]['name'] = '13-18';
		$arrUnits[4]['uid']  = 4;
		$arrUnits[4]['name'] = '19-24';
		if(count($arrUnits)) {
			$unit_uid = $arrUnits[1]['uid'];
		}
		if(isset($this->arrPaths[5])) {
			$unit_uid = $this->arrPaths[5];
		}

		$arrRows  = array();
		if(!empty($arrLanguage)) {
			foreach($arrLanguage as $uid=>$data) {
					$url = config::admin_uri('classes/unit-game-scores/class-'.$class_uid.'/'.$data['uid'].'/'.$unit_uid.'/');
					$objClasses = '';
					if($language_uid == $data['uid']) {
						$objClasses = ' ui-tabs-selected ui-state-active';
					}
					$arrRows[] = ' <li class="ui-state-default ui-corner-top '.$objClasses.'"><a href="'.$url.'"><span>'.$data['name'].'</span></a></li>';
			}
			$body->assign('language.li' , implode('',$arrRows));
		}

		$arrRows = array();
		if(!empty($arrUnits)) {
			foreach($arrUnits as $uid=>$data) {
					$url = config::admin_uri('classes/unit-game-scores/class-'.$class_uid.'/'.$language_uid.'/'.$data['uid'].'/');
					$objClasses = '';
					if($unit_uid == $data['uid']) {
						$objClasses = ' ui-tabs-selected ui-state-active';
					}
					$arrRows[] = ' <li class="ui-state-default ui-corner-top '.$objClasses.'"  title="'.$data['name'].'"><a href="'.$url.'" title="'.$data['name'].'"><span>'.$data['name'].'</span></a></li>';
			}
			$body->assign('units.li',implode('',$arrRows));
		}

		$gameHtml = '';
		$objGame = new game();
		$html = $objGame->getGameScoreHeader();

		$arrRows = array();
		$i = 0;
		if($unit_uid > 4) {
			$unit_uid = 1;
		}
		$from		= ($unit_uid * 6) - 5; 
		$to			= ($unit_uid * 6);
		$units_uids	= array();

		if(!empty($Allunits)) {
			foreach($Allunits as $uid=>$data) {
				$i++;
				if($i < $from  || $i > $to) {
					continue;
				}
				$units_uids[]	= $data;
				$gameHtml		.= $html;
				$arrRows[]		= '<th title="'.$data['name'].'" colspan="4">Unit '.$data['unit_number'].'</th>';
			}
			$body->assign('th.section' , implode('',$arrRows));
		}

		$body->assign('th.games',$gameHtml);

		$objGameScore = new gamescore();
		$body->assign('names.scores.rows',$objGameScore->getClassUsersAndScoresForUnit(
				$class_uid,
				$language_uid,
				$units_uids
			)
		);

		$body->assign('class_uid'	,$class_uid);
		$body->assign('unit.active'	, 'ui-tabs-selected ui-state-active');

		$arrClasses = array();
		$objClasses = new classes();
		$arrClasses = $objClasses->search(array('name'),array('uid'=>$class_uid));
		if(isset($arrClasses['name'])) {
			$body->assign('breadcrumb' , $this->breadCrumb($class_uid, $arrClasses['name']));
		}
		$skeleton->assign (
			array (
				'body' => $body
			)
		);
		output::as_html($skeleton,true);	
	}

	protected function doGetcertificate() {
		$class_uid		= 0;
		$class_data		= array();
		$gamescore_uid	= 0;
		$game_data		= array();
		$language_uid	= 0;
		$cs_data		= array();
		$data			= array();

		if(isset($this->arrPaths[3]) && is_numeric($this->arrPaths[3]) && $this->arrPaths[3] > 0) {
			$class_uid = $this->arrPaths[3];
		}
		
		if(isset($this->arrPaths[5]) && is_numeric($this->arrPaths[5]) && $this->arrPaths[5] > 0) {
			$gamescore_uid = $this->arrPaths[5];
		}
		
		if(isset($this->arrPaths[4]) && is_numeric($this->arrPaths[4]) && $this->arrPaths[4] > 0) {
			$language_uid = $this->arrPaths[4];
		}
		
		// IF SCHOOL TEACHER IS LOGGED IN THEN CHECK DOES HE HAS RIGHTS ?
		if(isset($_SESSION['user']['userRights']) && $_SESSION['user']['userRights'] == 'schoolteacher' ) {
			$arrFields = array('uid', 'name', 'school_id');
			$arrWhere = array('uid' => $class_uid, 'class_user_uid' => $_SESSION['user']['uid']);
			$objClasses = new classes();
			$class_data = $objClasses->search($arrFields, $arrWhere);
			if(count($class_data) == 0) {
				$this->PrintAccessDenide();
			}
		} else {
			$arrFields = array('uid', 'name', 'school_id');
			$arrWhere = array('uid' => @$class_uid);
			$objClasses = new classes();
			$class_data = $objClasses->search($arrFields, $arrWhere);
			if(count($class_data) == 0) {
				$this->PrintAccessDenide();
			}
		}
		// IF USER IS NOT TEACHER AND SITE ADMIN SO HE/SHE MAY BE SCHOOL OR SCHOOL ADMIN SO WE NEED TO CHECK DOES HE/SHE HAS RIGHTS
		if(isset($_SESSION['user']['school_uid']) && isset($_SESSION['user']['admin']) && $class_data['school_id'] != $_SESSION['user']['school_uid'] && $_SESSION['user']['admin'] != 1  ) {
			$this->PrintAccessDenide();	
		}
	
		if( count($class_data) > 0) {
			// GET GAME DETAILS
			$objGameScore = new gamescore();
			$game_data = $objGameScore->getGameScoreDetail ( $gamescore_uid, $language_uid );
			if(count($game_data) == 0 ) {
				$this->PrintAccessDenide();
			}


			// CHECK STUDENT IS IN THIS CLASSS IF NOT IN THE CLASS SHOW 'Access denide!'
			$arrFields = array('uid');
			$arrWhere = array(
				'class_uid'		=> $class_uid,
				'student_uid'	=>$game_data[0]['student_uid']
			);
			$objClassesStudent = new classes_student();
			$cs_data = $objClassesStudent->search($arrFields, $arrWhere);
			if(count($cs_data) == 0) { 
				$this->PrintAccessDenide();
			}
			$game_data = $game_data[0];
			$game_data['class_name'] = $class_data['name'];

			/*echo '<pre>';
			print_r($game_data);
			echo '</pre>';
			exit;*/
			$objCertificate = new certificate ();
			$objCertificate->generate($game_data);
		
		} else {
			$this->PrintAccessDenide();
		}
	}

	private function PrintAccessDenide() {
		die('Access denied!');
	}
	
}

?>