<?php

class units extends generic_object {

    public $arrForm = array();

    public function __construct($uid = 0) {
        parent::__construct($uid, __CLASS__);
    }

	public function unitLit($orderBy = 'unit_number') {
		$query = "SELECT `uid`, `name`, `unit_number` FROM `units` ORDER BY ".$orderBy;
		$result = database::query($query);
		$this->data		= array();
        if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
            while($row=mysql_fetch_assoc($result)) {
                $this->data[] = $row;
            }
        }
        return $this->data;
	}

    public function getList( $data = array(),  $all = false )
    {
            $parts = config::get('paths');
            $where = ' where Y.uid = U.year_uid ';
            foreach($data as $idx => $val ){
                $where .= " AND " .  $idx . "='" . $val . "'";
            }
           if($all == false) {
            $result = database::query('SELECT COUNT(U.uid) FROM units U, years Y '.$where);
            $max = 0;
            if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
                $row = mysql_fetch_array($result);
                $max = $row[0];
            }
            $pageId = '';
            if($pageId=='') {                
                $n = count($parts) - 1;
                
                if(isset($parts[$n]) && is_numeric($parts[$n]) && $parts[$n] > 0) {
                    $pageId = $parts[$n];
                } else {
                    $pageId = 1;
                }
            }

            $this->pager(
                    $max,						//see above
                    config::get("pagesize"),	//how many records to display at one time
                    $pageId,
                    array("php_self" => "")
            );
            
            $this->set_range(10);
            $result = database::query("SELECT U.*, Y.name as years FROM  units U, years Y ".$where." ORDER BY U.name  LIMIT ".$this->get_limit());

        }
        else {
            $result = database::query("SELECT U.*, Y.name as years FROM  units U, years Y ".$where." ORDER BY U.name"  );
        }
        $this->data		= array();
        if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
            while($row=mysql_fetch_assoc($result)) {
                $this->data[] = $row;
            }
        }
        return $this->data;
    }

    public function yearTranslationsList( $year_id )
    {
        $sql = "SELECT YT.*, L.name as language  FROM language L, years_translations YT WHERE L.uid = YT.language_id and year_id = '".$year_id."'";
        $result = database::query($sql);
        
        $this->data		= array();
        if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
            while($row=mysql_fetch_assoc($result)) {
                if($row['active'])
                    $row['active_yes_no'] = 'Yes';
                else
                    $row['active_yes_no'] = 'No';
                $this->data[] = $row;
            }
        }
        return $this->data;
    }
    public function doSave ()
    {
        $response = true;
        $response = $this->isValidate();
        if( count( $response ) == 0 ){
            
            if( $_POST['uid'] > 0)
                $this->save ();
            else{
                $insert = $this->insert();
                $this->arrForm['uid'] = $insert;                
            }
        }
        else{
            $msg  = NULL;
            foreach( $response as $idx => $val ){
                $this->arrForm[$idx] = 'label_error';
                $msg .= '<li>'.$val.'</li>';
            }
            if($msg != NULL)
                $this->arrForm['message_error'] = '<p>Please correct the errors below:</p><ul>'.$msg.'</ul>';
        }        
        if( count( $response ) > 0 )
            return false;
        else
            return true;
    }
    public function isValidate()
    {

        if(is_numeric($_POST['uid']) && $_POST['uid'] > 0) {
            parent::__construct($_POST['uid'],__CLASS__);
            $this->load();
        }
        $message            =   array();
         if( trim($_POST['name']) == '' ) {
            $message['error_name']      =   "Please provide unit name.";
        }
        if( trim($_POST['year_uid']) == '' ||  $_POST['year_uid'] == '0' ) {
            $message['error_year_uid']      =   "Please provide year.";
        }
        
        foreach( $_POST as $idx => $val )   {
            $this->arrForm[$idx] = $val;
            if( in_array($idx,array('uid', 'form_submit_button')) ) continue;
            $this->arrFields[$idx]['Value'] = $val;
        }
        return $message;
    }

     public function unitTranslationsList( $unit_id )
    {
        $sql = "SELECT UT.*, L.name as language  FROM language L, units_translations UT WHERE L.uid = UT.language_id and unit_id = '".$unit_id."'";
        $result = database::query($sql);

        $this->data		= array();
        if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
            while($row=mysql_fetch_assoc($result)) {
                if($row['active'])
                    $row['active_yes_no'] = 'Yes';
                else
                    $row['active_yes_no'] = 'No';
                $this->data[] = $row;
            }
        }
        return $this->data;
    }

     public function UnitSelectBox($inputName, $selctedValue = NULL)
    {
        $sql = "SELECT uid, name FROM units ORDER BY name";
        $result = database::query($sql);
        $data = array();
        $data[0] = 'Unit Name';
        if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
            while($row=mysql_fetch_assoc($result)) {
                $data[$row['uid']] = $row['name'];
            }
        }
        return format::to_select(array("name" => $inputName,"id" => $inputName,"options_only" => false), $data , $selctedValue);
    }

    public function getUnitTransArray( $language_id, $year_id, $locale )
    {
        $path = '/home/language/public_html';
    //	$story	= '/swf/story/[locale]/[locale]_u[unit_id]/[locale]_u[unit_id]_story0[section_id]/[locale]_u[unit_id]_s[section_id]_story.xml';
        $story = '/swf/story/[locale]/[locale]_u[unit_id]/[locale]_u[unit_id]_s[story_id]_story.xml';
        $karaoke= '/swf/karaoke/[locale]/[locale]_u[unit_id]/[locale]_u[unit_id]_s[story_id]_karaoke.xml';

        $units = array();
        
        $query = "SELECT ";
        $query.="`ut`.`unit_id`, ";
        $query.="`ut`.`name` ";
        $query.="FROM ";
        $query.="`units`, ";
        $query.="`units_translations` AS `ut` ";
        $query.="WHERE ";
        $query.="`ut`.`language_id`=$language_id ";
        $query.="AND `ut`.`unit_id`=`units`.`uid` ";
        $query.="AND `units`.`year_uid`=$year_id ";
        $query.="ORDER BY ";
        $query.="`ut`.`unit_id` ASC";
        $result = database::query($query);
        if($result) {

                if(mysql_num_rows($result) > 0) {
                        while($row = mysql_fetch_assoc($result)) {
                                $u = ((int)$row['unit_id'] < 10) ? '0'.$row['unit_id'] : $row['unit_id'];
                                $s = 1;

                                $units[$row['unit_id']] = array(
                                        'name'=>stripslashes($row['name']),
                                        'story'		=> (
                                                file_exists(
                                                        str_replace(
                                                                array('[unit_id]','[story_id]','[locale]'),
                                                                array($u,$s,($locale=='es' ? 'sp' : $locale)),
                                                                $path.$story
                                                        )
                                                ) ? '1' : '0'
                                        ),
                                        'karaoke'	=> (
                                                file_exists(
                                                        str_replace(
                                                                array('[unit_id]','[story_id]','[locale]'),
                                                                array($u,$s,($locale=='es' ? 'sp' : $locale)),
                                                                $path.$karaoke
                                                        )
                                                ) ? '1' : '0'
                                        )
                                );
                        }
                }
        }

        return $units;
    }
}
?>