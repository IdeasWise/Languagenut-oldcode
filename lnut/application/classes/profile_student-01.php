<?php

class profile_student extends generic_object {

    public $arrForm = array();
    public function __construct($uid = 0) {
        parent::__construct($uid, __CLASS__, true);
    }
    public function doSave ()
    {
        $response = true;
        $response = $this->isValidate();
        if( count( $response ) == 0 ){
            
            $this->arrFields['itime']['Value'] = time();
            if( $_POST['uid'] > 0)
                $this->save ();
            else{               
                $insert = $this->insert();
                $this->arrForm['uid'] = $insert;
                $sql = database::query("UPDATE user SET user_type = CONCAT(user_type , ',student') where uid = '".$_POST['iuser_uid']."' ");
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
         if( trim($_POST['vfirstname']) == '' ) {            
            $message['error_vfirstname']      =   "Please provide your first name";
        }
        if( trim($_POST['vlastname']) == '' ) {
            $message['error_vlastname']      =   "Please provide your last name";
        }
        if( trim($_POST['school_id']) == '' ) {
            $message['error_school_id']      =   "Please choose your school name";
        }        
        foreach( $_POST as $idx => $val )   {
            $this->arrForm[$idx] = $val;
            if( in_array($idx,array('uid', 'submit-edit-profile')) ) continue;
            $this->arrFields[$idx]['Value'] = $val;
        }       
        return $message;
    }

}
?>