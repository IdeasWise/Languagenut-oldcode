<?php

class sections extends generic_object {

    public $arrForm = array();

    public function __construct($uid = 0) {
        parent::__construct($uid, __CLASS__);
    }
	
	public function SectionList( $unit_uid = null, $OrderBy = 'section_number') {
		
		$where = '';
		if( $unit_uid != null )
			$where = " and `unit_uid` = '".$unit_uid."'";
		$query = "SELECT * From `sections` WHERE 1 = 1 " . $where . " ORDER BY ". $OrderBy;

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
            $where = ' where S.unit_uid = U.uid ';
            foreach($data as $idx => $val ){
                $where .= " AND " .  $idx . "='" . $val . "'";
            }
           if($all == false) {
            $result = database::query('SELECT COUNT(S.uid) FROM units U, sections S '.$where);
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
            
            $result = database::query("SELECT S.*, U.name as UnitName FROM  units U, sections S ".$where." ORDER BY S.name  LIMIT ".$this->get_limit());

        }
        else {
            $result = database::query("SELECT S.*, U.name as UnitName FROM  units U, sections S ".$where." ORDER BY S.name" );
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
            $message['error_name']      =   "Please provide section name.";
        }
        if( trim($_POST['unit_uid']) == '' ||  $_POST['unit_uid'] == '0' ) {
            $message['error_unit_uid']      =   "Please choose unit name.";
        }
        
        foreach( $_POST as $idx => $val )   {
            $this->arrForm[$idx] = $val;
            if( in_array($idx,array('uid', 'form_submit_button')) ) continue;
            $this->arrFields[$idx]['Value'] = $val;
        }
        return $message;
    }

     public function sectionTranslationsList( $section_uid )
    {
        $sql = "SELECT ST.*, L.name as language  FROM language L, sections_translations ST WHERE L.uid = ST.language_id and section_uid = '".$section_uid."'";
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

     public function SectionSelectBox($inputName, $selctedValue = NULL)
    {
        $sql = "SELECT uid, name FROM sections ORDER BY name";
        $result = database::query($sql);
        $data = array();
        $data[0] = 'Section Name';
        if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
            while($row=mysql_fetch_assoc($result)) {
                $data[$row['uid']] = $row['name'];
            }
        }
        return format::to_select(array("name" => $inputName,"id" => $inputName,"options_only" => false), $data , $selctedValue);
    }

    public function getSectionTranslations( $language_id, $unit_ids )
    {
        $sections = array();
        $query = "SELECT ";
        $query.="`st`.`section_uid`, ";
        $query.="`st`.`name`, ";
        $query.="`sections`.`unit_uid` ";
        $query.="FROM ";
        $query.="`sections`, ";
        $query.="`sections_translations` AS `st` ";
        $query.="WHERE ";
        $query.="`st`.`language_id`=$language_id ";
        $query.="AND `st`.`section_uid`=`sections`.`uid` ";
        $query.="AND `sections`.`unit_uid` IN (".implode(',',$unit_ids).") ";
        $query.="ORDER BY ";
        $query.="`st`.`section_uid` ASC";
        $result = database::query($query);
        if($result) {

                if(mysql_num_rows($result) > 0) {
                        while($row = mysql_fetch_assoc($result)) {
                                $sections[$row['section_uid']] = array(
                                        'unit_id'	=>$row['unit_uid'],
                                        'name'		=>stripslashes($row['name'])
                                );
                        }
                }
        } else {
                echo mysql_error();
        }

        return $sections;
    }

    // used on printable controller
    public function getSectionUnitandId($section_uid)
    {
        $row = array();
         $query = "SELECT `units`.`uid`, `sections`.`name` as section_name, `units`.`name` as unit_name FROM `sections`, `units` WHERE `sections`.`unit_uid`=`units`.`uid` AND `sections`.`uid`=$section_uid";
            $result = database::query($query);
            if($result) {
            if(mysql_num_rows($result) > 0) {
                    $row = mysql_fetch_assoc($result);                    
           }
        }
        return $row;
    }


   
   
}
?>