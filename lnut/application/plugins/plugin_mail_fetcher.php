<?php

class plugin_mail_fetcher extends plugin {

    private $hostname   = "";
    private $username   = "";
    private $password   = "";
    private $mainEmail  = "";
    private $port       = "";
    private $protocol   = "";
    private $encryption = "";
    private $serverstr  = "";
    private $to         = array();
    private $cc         = array();
    private $mbox       = null;

    private $charset    = 'UTF-8';

    public function __construct($data = array()) {
        // when the data comes dynamic we assign all properties here
    }

    public function get_class_name() {
        return __CLASS__;
    }

    public function run() {
        $this->fetch_email();
    }

    protected function fetch_email($username = "mailboxtest@mystream.co.uk",$password = "mailboxtest",$hostname = "mail.mystream.co.uk",$port = "110",$protocol = "POP3",$encryption='') {
        $this->hostname     =   $hostname;
        $this->username     =   $username;
        $this->password     =   $password;
        $this->protocol     =   strtolower($protocol);
        $this->port         =   $port;
        $this->encryption   =   $encryption;

        $this->serverstr=sprintf('{%s:%d/%s',$this->hostname,$this->port,strtolower($this->protocol));
        if(!strcasecmp($this->encryption,'SSL')) {
            $this->serverstr.='/ssl';
        }
        $this->serverstr.='/novalidate-cert}INBOX'; //add other flags here as needed.

        //echo $this->serverstr;
        //Charset to convert the mail to.
        $this->charset='UTF-8';
        //Set timeouts
        if(function_exists('imap_timeout'))
            imap_timeout(1,20); //Open timeout.

        if($this->connect()) {
            $this->fetchEmails();
            $this->close_connection();
        }
    }

    protected function connect() {
        return $this->open_connection()?true:false;
    }

    protected function open_connection() {
        //echo $this->serverstr;
        if(isset($this->mbox)) {
            if($this->mbox && imap_ping($this->mbox))
                return $this->mbox;
        }
        else {
            $this->mbox = @imap_open($this->serverstr,$this->username,$this->password);
        }
        return $this->mbox;
    }

    protected function close_connection() {
        imap_close($this->mbox,CL_EXPUNGE);
    }

    protected function fetchEmails($fetch = 30,$deleteEmail = false) { // main function to fetch emails
        $total  =   imap_num_msg($this->mbox);
        // get the reverse messages last first
        $msgs   =   0;
        for($i=$total; $i>0; $i--) {
            if($this->insertEmail($i) != null) {
                imap_setflag_full($this->mbox, imap_uid($this->mbox,$i), "\\Seen", ST_UID);
                @imap_expunge($this->mbox);
                if($deleteEmail) {
                    imap_delete($this->mbox,$i);
                }
                $msgs++;
            }
            if($msgs>=$fetch) { // stop after limited emails
                break;
            }
        }
    }

    protected function insertEmail($mid = 0) { // insert email in the db from the email account

        $error      =   false;
        $headerinfo =   imap_headerinfo($this->mbox,$mid);
        $this->setToAndCC($headerinfo);
        $sender     =   $headerinfo->from[0];

        //Parse what we need...
        $mailinfo   =   array(
                'from'   =>array(
                        'name'  =>@$sender->personal,
                        'email' =>strtolower($sender->mailbox).'@'.$sender->host
                ),
                'subject'=>@$headerinfo->subject,
                'mid'    =>$headerinfo->message_id
        );

        if(user_messages_email::email_exists($mailinfo['mid'])) {
            $error   =   true;
        }
        else {
            $var['name']    =   imap_utf8($mailinfo['from']['name']);
            $var['email']   =   $mailinfo['from']['email'];
            $var['subject'] =   $mailinfo['subject']?imap_utf8($mailinfo['subject']):'[No Subject]';
            $var['message'] =   $this->getBody($mid);
            $var['header']  =   imap_fetchheader($this->mbox, $mid, FT_PREFETCHTEXT);
            $var['name']    =   $var['name']?$var['name']:$var['email'];
            $var['mid']     =   $mailinfo['mid'];

            $user_message_email = new user_messages_email();
            $user_message_email->set_direction("in");
            $user_message_email->set_email_message_id($var['mid']);
            $user_message_email->set_to($this->to);
            $user_message_email->set_from($var['email']);
            $user_message_email->set_cc($this->cc);
            $user_message_email->set_subject($var['subject']);
            $user_message_email->set_body($var['message']);

            $insert_id      =   null;
            if(($insert_id = $user_message_email->insert()) !== false) {
                if(($struct = imap_fetchstructure($this->mbox,$mid)) && $struct->parts) {
                    //We've got something...do a search
                    foreach($struct->parts as $k=>$part) {
                        if($part && $part->ifdparameters && ($filename=$part->dparameters[0]->value)) { //attachment
                            $data=$this->decode($part->encoding, imap_fetchbody($this->mbox,$mid,$k+1));
                            $this->saveAttachment($filename,$data,$insert_id,$mailinfo['mid']);
                        }
                        else {
                            $data=$this->decode($part->encoding, imap_fetchbody($this->mbox,$mid,$k+1));
                            $refId = $this->saveAttachment($part->description,$data,$insert_id,$mailinfo['mid']);
//                            if($refId) {
//                                $name = $this->getAttachmentName($refId,"M");
//                                $this->replaceImageAttachment($part->id,$name,$msgid);
//                            }
                        }
                    }
                }
            }
        }
    }

    protected function setToAndCC($header = array()) {
        $this->to = array();
        $this->cc = array();

        if(isset($header->to) && is_array($header->to)) {
            foreach($header->to as $to) {
                $email = strtolower($to->mailbox).'@'.$to->host;
                if(!in_array($email,$this->to)) {
                    $this->to[] = $email;
                }
            }
        }
        if(isset($header->cc) && is_array($header->cc)) {
            foreach($header->cc as $cc) {
                $email = strtolower($cc->mailbox).'@'.$cc->host;
                if(!in_array($email,$this->cc) && strtolower($this->mainEmail) != strtolower($email)) {
                    $this->cc[] = $email;
                }
            }
        }
    }

    protected function getBody($mid = "") {
        $body ='';
        if(($body = $this->getPart($mid,'TEXT/HTML',$this->charset)) !== false) {
            return $body;
        }
        else if(($body = $this->getpart($mid,'TEXT/PLAIN',$this->charset)) !== false) {
            if(($body = $this->getPart($mid,'TEXT/HTML',$this->charset)) !== false) {
                return $body;
            }
        }
        return $body;
    }

    protected function getPart($mid,$mimeType,$encoding=false,$struct=null,$partNumber=false) {

        $prefix = "";
        if($struct == null) {
            $struct = imap_fetchstructure($this->mbox, $mid);
        }
        //Match the mime type.
        if(isset($struct) && strcasecmp($mimeType,$this->getMimeType($struct))==0) {
            $partNumber=$partNumber?$partNumber:1;
            if(($text=imap_fetchbody($this->mbox, $mid, $partNumber))) {
                if($struct->encoding==3 || $struct->encoding==4) {//base64 and qp decode.
                    $text=$this->decode($struct->encoding,$text);
                }
                $charset=null;
                if(isset($encoding)) { //Convert text to desired mime encoding...
                    if(isset($struct->ifparameters)) {
                        if(!strcasecmp($struct->parameters[0]->attribute,'CHARSET') && strcasecmp($struct->parameters[0]->value,'US-ASCII')) {
                            $charset=trim($struct->parameters[0]->value);
                        }
                    }
                    $text=$this->mime_encode($text,$charset,$encoding);
                }
                return $text;
            }
        }
        //Do recursive search
        if(isset($struct) && isset($struct->parts)) {
            while(list($i, $substruct) = each($struct->parts)) {
                if($partNumber)
                    $prefix = $partNumber . '.';
                if(($text=$this->getPart($mid,$mimeType,$encoding,$substruct,$prefix.($i+1))))
                    return $text;
            }
        }
        return false;
    }

    protected function getMimeType($struct) { // get mime type
        $mimeType = array('TEXT', 'MULTIPART', 'MESSAGE', 'APPLICATION', 'AUDIO', 'IMAGE', 'VIDEO', 'OTHER');
        if(!$struct || !$struct->subtype)
            return 'TEXT/PLAIN';

        return $mimeType[(int) $struct->type].'/'.$struct->subtype;
    }

    protected function decode($encoding,$text) { // decode message from its type calling particular imap function

        switch($encoding) {
            case 1:
                $text=imap_8bit($text);
                break;
            case 2:
                $text=imap_binary($text);
                break;
            case 3:
                $text=imap_base64($text);
                break;
            case 4:
                $text=imap_qprint($text);
                break;
            case 5:
            default:
                $text=$text;
        }
        return $text;
    }

    protected function mime_encode($text,$charset=null,$enc='uft-8') { //Thank in part to afterburner

        $encodings=array('UTF-8','WINDOWS-1251', 'ISO-8859-5', 'ISO-8859-1','KOI8-R');
        if(function_exists("iconv") && $text != "") {
            if($charset)
                return iconv($charset,$enc.'//IGNORE',$text);
            elseif(function_exists("mb_detect_encoding"))
                return iconv(mb_detect_encoding($text,$encodings),$enc,$text);
        }

        return utf8_encode($text);
    }

    protected function saveAttachment($name = "",$data = "",$email_message_id = 0,$mid = "") {
        $folder_name        =   $this->get_setting("email_attachment");
        $folder_path        =   config::get("uploads").$folder_name;
        if(!is_dir($folder_path)) {
            if (!mkdir($folder_path, 0, true)) {
                die('Failed to create folders...');
            }
        }
        $folder_path .= "/".format::to_string($mid);
        if(!is_dir($folder_path)) {
            if (!mkdir($folder_path, 0, true)) {
                die('Failed to create folders...');
            }
        }
        $filename = $folder_path."/".$name;
        if(($fp=fopen($filename,'w'))) {
            fwrite($fp,$data);
            fclose($fp);
            $size=@filesize($filename);

            $user_email_attachment = new user_messages_email_attachment();
            $user_email_attachment->set_user_message_email_uid($email_message_id);
            $user_email_attachment->set_file_name($name);
            $user_email_attachment->set_file_location($folder_path);
            $user_email_attachment->set_file_size($size);
            if(!$user_email_attachment->insert()) {
                @unlink($filename);
            }
        }
    }
}
?>