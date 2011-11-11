<?php

class schools extends generic_object {

    public function __construct($uid = 0) {
        parent::__construct($uid, __CLASS__);
    }

    public function getSchool()
    {
        $sql = "SELECT id, school_name FROM schools ORDER BY school_name";
        $result = database::query( $sql );
        $data = array();
        $data[''] = "School Name";
        if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
               while( $row = mysql_fetch_array($result) ){
                   $data[$row['id']] = $row['school_name'];
               }
         }
         return $data;
    }
}
?>