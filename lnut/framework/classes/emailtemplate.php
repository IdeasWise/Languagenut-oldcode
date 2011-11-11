<?php

/**
* Email Template to read in via tagname
*/
class emailtemplate {

	public function __construct($name='') {
		// fetch the template
		database::connect();
		$query = "SELECT `from`, `from_name`, `subject`, `bcc`, `html`, `text` FROM `email_template` WHERE `slug`='".mysql_real_escape_string($name)." LIMIT 1";
		$result = database::query($query);

		if($result && mysql_error() == '') {
			$row = mysql_fetch_assoc($result);
			$this->set_from($row['from']);
			$this->set_from_name($row['from_name']);
			$this->set_subject($row['subject']);
			$this->set_html($row['html']);
			$this->set_text($row['text']);
			$this->set_bcc($row['Bcc']);
		} else {
			echo mysql_error();
		}
	}
	public function get_from () {
		return $this->from;
	}
	public function get_subject () {
		return $this->subject;
	}
	public function get_text () {
		return $this->text;
	}
	public function get_html () {
		return $this->html;
	}
	public function get_bcc () {
		return $this->bcc;
	}
	public function replace($array=array()) {
		if(is_array($array) && count($array) > 0) {
			foreach($array as $key=>$value) {
				$keys[] = $key;
				$values[] = $value;
			}
			foreach($keys as $index=>$key) {
				$keys[$index] = '[' . trim($key) . ']';
			}
			$this->html = str_replace( $keys, $values, $this->html );
			$this->text = str_replace( $keys, $values, $this->text );
		}
	}
	public function send () {  
		$header ="Content-Transfer-Encoding: 8bit";
		$header .="\nContent-Type: text/html; charset=iso-8859-15";
		$header .="\nFrom: ".$this->get_from();
		if(strlen($this->get_cc()) > 0) {
			$header .= "\nCc: ".$this->get_cc();
		}
		if(strlen($this->get_bcc()) > 0) {
			$header .= "\nBcc: ".$this->get_bcc();
		}
		$header .= "\nReturn-Path: ".$this->return_path();

		$message = $this->get_html();
		$message = str_replace(array("<br>","<br />","<p>","</p>"),array("<br />\n","<br />\n","","\n\n"),$this->get_text());

		$headers .= "Disposition-Notification-To: ".$this->get_from_name()."<".$this->get_from().">\n";
		mail($email,$subject,$message, $header); 
	}
}

?>