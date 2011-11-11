<?php

class component_htaccess {

    public static function generate_get_htaccess() {
        $myFile = config::get("site").".htaccess";
        $fh = fopen($myFile, 'r');
        $theData = fread($fh, filesize($myFile));
        fclose($fh);
        return $theData;
    }

    public static function generate_update_htaccess() {
        $html_body              =   self::generate_get_htaccess();
        if($html_body != "") {
            $ip_ban             =   array();
            $setting_ip         =   new settings_ip_ban();
            $ip_ban             =   $setting_ip->get_list();
            $rewrite_rule       =   "order allow,deny\n";
            foreach($ip_ban as $uid => $data) {
                $ip_rule        =   $data['rule'];
                $start_ip       =   long2ip($data['start_ip']);
                $end_ip         =   long2ip($data['end_ip']);
                if(strpos($ip_rule,"*") !== false) {
                    $ip_array   =   explode(".",$ip_rule);
                    foreach($ip_array as $key => $val) {
                        if($val == "*") {
                            unset($ip_array[$key]);
                        }
                    }
                    $rewrite_rule   .=   "deny from ".implode(".",$ip_array)."\n";
                }
                elseif(strpos($ip_rule,"-") !== false) {
                    $rewrite_rule   .=   "deny from ".$start_ip."/".$end_ip."\n";
                }
                else {
                    $rewrite_rule   .=   "deny from ".$ip_rule."\n";
                }
            }
            $rewrite_rule   .=   "allow from all\n";
            $html_array     =   explode("\n",$html_body);

            $start          =   false;
            $html_body      =   "";
            foreach($html_array as $key => $val) {
                switch(trim($val)) {
                    case "#### IP Manager [BOF] ####":
                        $html_body .= $val."\n".$rewrite_rule;
                        $start  =   true;
                        break;
                    case "#### IP Manager [EOF] ####":
                        $html_body .= $val."\n";
                        $start  =   false;
                        break;
                    default:
                        if($start == false)
                            $html_body .= $val."\n";
                        break;
                }
            }
            $html_body  =   trim($html_body,"\n");
            self::generate_set_htaccess($html_body);
        }
    }

    public static function generate_set_htaccess($body = "") {
        $myFile = config::get("site").".htaccess";
        $fh     = fopen($myFile, 'w');
        fwrite($fh, $body);
        fclose($fh);
        return true;
    }
}
?>
