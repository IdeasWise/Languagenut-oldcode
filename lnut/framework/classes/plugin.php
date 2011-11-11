<?php

abstract class plugin {
    //put your code here
    protected $body   =   null;
    protected $data   =   array();

    abstract public function  __construct();

    abstract public function  run();

    abstract public function get_class_name();

    public function parseResponse($response) {
        if(is_array($response) && count($response) > 0 && isset($response['fields'])) {
            foreach($response['fields'] as $uid => $data) {
                if(is_array($data) && count($data) > 0) {
                    foreach($data as $key => $val) {
                        switch ($key) {
                            case "default":
                                break;
                            case "message":
                                break;
                            case "highlight":
                                break;
                            case "error":
                                break;
                            case "value":
                                break;
                        }
                    }
                }
            }
        }
        $this->body->assign("message", format::to_error($response['message']));
    }

    public function get_setting($key = "") {
        $class = $this->get_class_name();
        $value = "";
        $key = mysql_real_escape_string($key);
        $sql = "SELECT `value` FROM `".$class."_settings` WHERE `key` = '$key' LIMIT 1";
        $result =   database::query($sql);
        if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
            $rec = mysql_fetch_assoc($result);
            $value = $rec['value'];
        }
    }

    public static function set_setting($key = "",$val = "") {
        $class = $this->get_class_name();
        $update = false;
        $key = mysql_real_escape_string($key);
        $val = mysql_real_escape_string($val);
        $sql = "UPDATE `".$class."_settings` SET `value` = '$val' WHERE `key` = '$key' LIMIT 1";
        $result =   database::query($sql);
        if($result && mysql_error()=='' && mysql_affected_rows() > 0) {
            $update = true;
        }
        return $update;
    }    
}
?>